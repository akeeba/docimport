<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\DocImport\Administrator\Model;

defined('_JEXEC') || die;

use Akeeba\Component\DocImport\Administrator\Table\ArticleTable;
use Akeeba\Component\DocImport\Administrator\Table\CategoryTable;
use DOMDocument;
use Exception;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use RuntimeException;
use XSLTProcessor;

class XslModel extends BaseDatabaseModel
{
	/**
	 * Runs the XML to HTML file conversion step for a given category
	 *
	 * @param   int  $category_id  The ID of the category to process
	 *
	 * @return  void
	 */
	public function processXML($category_id)
	{
		// Get the category record
		/** @var CategoryTable $category */
		$category = $this->getMVCFactory()->createTable('Category');

		try
		{
			if (!$category->load($category_id))
			{
				throw new RuntimeException();
			}
		}
		catch (Exception $e)
		{
			throw new RuntimeException(Text::sprintf('COM_DOCIMPORT_XSL_ERROR_NOCATEGORY', $category_id), 500);
		}

		// Check if directories exist
		$cparams        = ComponentHelper::getParams($this->option);
		$configuredRoot = $cparams->get('mediaroot', 'com_docimport/books');
		$configuredRoot = trim($configuredRoot, " \t\n\r/\\");
		$configuredRoot = empty($configuredRoot) ? 'com_docimport/books' : $configuredRoot;

		$dir_src    = JPATH_ROOT . '/media/' . $configuredRoot . '/' . $category->slug;
		$dir_output = JPATH_ROOT . '/media/' . $configuredRoot . '/' . $category->slug . '/output';

		if (!Folder::exists($dir_src))
		{
			$dir_src    = JPATH_ROOT . '/media/com_docimport/' . $category->slug;
			$dir_output = JPATH_ROOT . '/media/com_docimport/' . $category->slug . '/output';
		}

		if (!Folder::exists($dir_src))
		{
			$dir_src    = JPATH_ROOT . '/media/com_docimport/books/' . $category->slug;
			$dir_output = JPATH_ROOT . '/media/com_docimport/books/' . $category->slug . '/output';
		}

		if (!Folder::exists($dir_src))
		{
			throw new RuntimeException(Text::sprintf('COM_DOCIMPORT_XSL_ERROR_NOFOLDER', $category->slug), 500);
		}

		// Clear the output directory
		if (Folder::exists($dir_output))
		{
			Folder::delete($dir_output);
		}

		// Regenerate the output directory
		if (!Folder::exists($dir_output))
		{
			$result = Folder::create($dir_output);

			if (!$result)
			{
				throw new RuntimeException(Text::sprintf('COM_DOCIMPORT_XSL_ERROR_CANTCREATEFOLDER', $category->slug . '/output'), 500);
			}

			$content = <<< HTACCESS
<IfModule !mod_authz_core.c>
Order deny,allow
Deny from all
</IfModule>
<IfModule mod_authz_core.c>
  <RequireAll>
    Require all denied
  </RequireAll>
</IfModule>

HTACCESS;

			File::write($dir_output . '/.htaccess', $content);
		}

		// Find the XML file
		$xmlfiles = Folder::files($dir_src, '\.xml$', false, true);

		// If we have many files, let's filter out only articles and books
		$hasManySourceFiles = count($xmlfiles) > 1;

		if ($hasManySourceFiles)
		{
			$files    = $xmlfiles;
			$xmlfiles = [];

			foreach ($files as $file_xml)
			{
				$xmlDoc = new DOMDocument();

				if (!$xmlDoc->load($file_xml))
				{
					continue;
				}

				$tagName = $xmlDoc->documentElement->tagName;

				if (in_array($tagName, ['article', 'book']))
				{
					$xmlfiles[] = $file_xml;
				}

				unset($xmlDoc);
			}
		}

		$xslt_filename = ($hasManySourceFiles) ? 'onechunk.xsl' : 'chunk.xsl';

		if (($xmlfiles === false) || (empty($xmlfiles)))
		{
			throw new RuntimeException(Text::_('COM_DOCIMPORT_XSL_ERROR_NOXMLFILES'), 500);
		}

		$file_xsl = JPATH_ADMINISTRATOR . '/components/com_docimport/assets/dbxsl/xhtml/' . $xslt_filename;

		// Load the XSLT filters
		$xslDoc = new DOMDocument();

		if (!$xslDoc->load($file_xsl))
		{
			throw new RuntimeException(Text::_('COM_DOCIMPORT_XSL_ERROR_NOLOADXSL'));
		}

		$timestamp = 0;

		foreach ($xmlfiles as $file_xml)
		{
			// Load the XML document
			$xmlDoc = new DOMDocument();

			if (!$xmlDoc->load($file_xml, LIBXML_DTDATTR | LIBXML_NOENT | LIBXML_NONET | LIBXML_XINCLUDE))
			{
				throw new RuntimeException(Text::_('COM_DOCIMPORT_XSL_ERROR_NOLOADXML'));
			}

			//$xmlDoc->documentURI = $file_xml;
			$xmlDoc->xinclude(LIBXML_DTDATTR | LIBXML_NOENT | LIBXML_NONET | LIBXML_XINCLUDE);

			/**
			 * Pre-process <imagedata> tags
			 *
			 * DocBook XML sources need to have a width attribute set up in <imagedata> tags to specify the optimal size for
			 * images in print media. When dealing with the web, however, it's best to let Bootstrap figure out how to render
			 * responsive images.
			 */
			$imageDataTags = $xmlDoc->getElementsByTagName('imagedata');

			if ($imageDataTags->length)
			{
				/** @var \DOMElement $tag */
				foreach ($imageDataTags as $tag)
				{
					$tag->removeAttribute('width');
				}
			}

			$filesprefix = '';

			if ($hasManySourceFiles)
			{
				$filesprefix = basename($file_xml, '.xml');
			}

			// Setup the XSLT processor
			$path_src = substr($dir_src, strlen(JPATH_ROOT));
			$path_src = trim($path_src, '/');
			$path_src = str_replace('\\', '/', $path_src);
			$path_src = '/' . ltrim($path_src, '/') . '/';

			$parameters = [
				'base.dir'            => rtrim($dir_output, '/') . '/' . (empty($filesprefix) ? '' : $filesprefix . '-'),
				'img.src.path'        => $path_src,
				'admon.graphics.path' => '/media/com_docimport/admonition/',
				'admon.graphics'      => 1,
				'use.id.as.filename'  => 1,
				'toc.section.depth'   => 5,
				'chunk.section.depth' => 3,
				'highlight.source'    => 1,
			];

			if ($hasManySourceFiles)
			{
				$parameters['use.id.as.filename'] = 0;
				$parameters['base.dir']           = rtrim($dir_output, '/');
				$parameters['root.filename']      = (empty($filesprefix) ? '' : $filesprefix . '-') . 'index.html';
			}

			$xslt = new XSLTProcessor();
			$xslt->importStylesheet($xslDoc);

			if (!$xslt->setParameter('', $parameters))
			{
				throw new RuntimeException(Text::_('COM_DOCIMPORT_XSL_ERROR_NOLOADPARAMETERS'), 500);
			}

			// Process it!
			set_time_limit(0);

			$errorsetting = error_reporting(0);

			if (version_compare(PHP_VERSION, '5.4', "<"))
			{
				$oldval = ini_set("xsl.security_prefs", XSL_SECPREF_NONE);
			}
			else
			{
				$oldval = $xslt->setSecurityPrefs(XSL_SECPREF_NONE);
			}

			$result = $xslt->transformToXml($xmlDoc);

			error_reporting($errorsetting);

			if (version_compare(PHP_VERSION, '5.4', "<"))
			{
				ini_set("xsl.security_prefs", $oldval);
			}
			else
			{
				$xslt->setSecurityPrefs($oldval);
			}

			unset($xslt);

			if ($result === false)
			{
				throw new RuntimeException(Text::_('COM_DOCIMPORT_XSL_ERROR_FAILEDTOPROCESS'), 500);
			}

			$timestamp_local = @filemtime($file_xml);

			if ($timestamp_local > $timestamp)
			{
				$timestamp = $timestamp_local;
			}

			if (!empty($filesprefix))
			{
				$fname   = rtrim($dir_output, '/') . "/$filesprefix-index.html";
				$renamed = rtrim($dir_output, '/') . "/$filesprefix.html";

				if (@file_exists($fname))
				{
					File::move($fname, $renamed);
				}
			}
		}

		// Update the database record with the file's/files' timestamp
		$category->save([
			'last_timestamp' => $timestamp,
		]);
	}

	/**
	 * Scans the output directory of the given category for new HTML files and updates the database.
	 *
	 * @param   int  $category_id  The ID of the category to scan
	 *
	 * @return  void
	 */
	public function processFiles($category_id)
	{
		// Get the category record
		// Get the category record
		/** @var CategoryTable $category */
		$category = $this->getMVCFactory()->createTable('Category');

		try
		{
			if (!$category->load($category_id))
			{
				throw new RuntimeException();
			}
		}
		catch (Exception $e)
		{
			throw new RuntimeException(Text::sprintf('COM_DOCIMPORT_XSL_ERROR_NOCATEGORY', $category_id), 500);
		}

		// Check if directories exist
		$cparams = ComponentHelper::getParams($this->option);;
		$configuredRoot = $cparams->get('mediaroot', 'com_docimport/books');
		$configuredRoot = trim($configuredRoot, " \t\n\r/\\");
		$configuredRoot = empty($configuredRoot) ? 'com_docimport/books' : $configuredRoot;

		$dir_src    = JPATH_ROOT . '/media/' . $configuredRoot . '/' . $category->slug;
		$dir_output = JPATH_ROOT . '/media/' . $configuredRoot . '/' . $category->slug . '/output';

		if (!Folder::exists($dir_src))
		{
			$dir_src    = JPATH_ROOT . '/media/com_docimport/' . $category->slug;
			$dir_output = JPATH_ROOT . '/media/com_docimport/' . $category->slug . '/output';
		}

		if (!Folder::exists($dir_src))
		{
			$dir_src    = JPATH_ROOT . '/media/com_docimport/books/' . $category->slug;
			$dir_output = JPATH_ROOT . '/media/com_docimport/books/' . $category->slug . '/output';
		}

		if (!Folder::exists($dir_src))
		{
			throw new RuntimeException(Text::sprintf('COM_DOCIMPORT_XSL_ERROR_NOFOLDER', $category->slug), 500);
		}

		if (!Folder::exists($dir_output))
		{
			throw new RuntimeException(Text::sprintf('COM_DOCIMPORT_XSL_ERROR_NOFOLDER', $category->slug . '/output'), 500);
		}

		// Load the list of articles in this category
		$db = $this->getDbo();

		$query    = $db->getQuery(true)
			->from($db->quoteName('#__docimport_articles'))
			->select([
				$db->quoteName('docimport_article_id') . ' AS ' . $db->quoteName('id'),
				$db->quoteName('slug'),
				$db->quoteName('last_timestamp'),
				$db->quoteName('enabled'),
			])
			->where($db->quoteName('docimport_category_id') . ' = ' . $db->quote($category_id));
		$articles = $db->setQuery($query)->loadObjectList('slug');

		if (empty($articles))
		{
			$articles = [];
		}

		// Get a list of existing files
		$files = Folder::files($dir_output, '\.html$');

		// And now, turn the files into a list of slugs
		$slugs = [];

		if (!empty($files))
		{
			foreach ($files as $filename)
			{
				$slugs[] = basename($filename, '.html');
			}
		}

		// First pass: find articles pointing to files no longer existing
		if (!empty($articles))
		{
			foreach ($articles as $slug => $article)
			{
				if (!in_array($slug, $slugs))
				{
					/** @var ArticleTable $articleTable */
					$articleTable = $this->getMVCFactory()->createTable('Article');

					try
					{
						if (!$articleTable->load($article->id))
						{
							throw new RuntimeException();
						}

						$articleTable->save([
							'enabled' => 0,
						]);
					}
					catch (Exception $e)
					{
						// Ignore errors at this stable
					}
				}
			}
		}

		// Second pass: add articles which are not already there
		$user    = Factory::getApplication()->getIdentity() ?: Factory::getUser();
		$user_id = $user->id;

		if (empty($user_id))
		{
			$user_id = 42;
		}

		if (!empty($slugs))
		{
			foreach ($slugs as $slug)
			{
				if (!array_key_exists($slug, $articles))
				{
					$jNow = new Date();

					$filepath = $dir_output . '/' . $slug . '.html';
					$filedata = $this->_getHTMLFileData($filepath);

					/** @var ArticleTable $articleTable */
					$articleTable = $this->getMVCFactory()->createTable('Article');
					$articleTable->reset();
					$articleTable->save([
						'docimport_category_id' => $category_id,
						'title'                 => $filedata->title,
						'slug'                  => $slug,
						'fulltext'              => $filedata->contents,
						'last_timestamp'        => $filedata->timestamp,
						'enabled'               => 1,
						'created_on'            => $jNow->toSql(),
						'created_by'            => $user_id,
					]);
				}
			}
		}

		// Third pass: update existing articles
		if (!empty($slugs) && !empty($articles))
		{
			foreach ($articles as $article)
			{
				if (in_array($article->slug, $slugs))
				{
					// Do we have to update?
					$filepath = $dir_output . '/' . $article->slug . '.html';

					if (@filemtime($filepath) == $article->last_timestamp)
					{
						continue;
					}

					$jNow     = new Date();
					$filedata = $this->_getHTMLFileData($filepath);

					/** @var ArticleTable $articleModel */
					$articleTable = $this->getMVCFactory()->createTable('Article');

					if (!$articleTable->load($article->id))
					{
						$articleTable->reset();
					}

					$articleTable->save([
						'title'          => $filedata->title,
						'fulltext'       => $filedata->contents,
						'last_timestamp' => $filedata->timestamp,
						'enabled'        => 1,
						'locked_on'      => null,
						'locked_by'      => 0,
						'modified_on'    => $jNow->toSql(),
						'modified_by'    => $user_id,
					]);
				}
			}
		}

		// Fourth pass: Load a list of enabled articles (IDs and slugs)
		$db      = $this->getDbo();
		$query   = $db->getQuery(true)
			->from($db->qn('#__docimport_articles'))
			->select([
				$db->qn('docimport_article_id', 'id'),
				$db->qn('slug'),
			])
			->where($db->qn('docimport_category_id') . ' = ' . $db->q($category_id))
			->where($db->qn('enabled') . ' = ' . $db->q(1));
		$rawlist = $db->setQuery($query)->loadObjectList();

		$mapSlugID = [];

		if (!empty($rawlist))
		{
			foreach ($rawlist as $rawItem)
			{
				$mapSlugID[$rawItem->slug] = $rawItem->id;
			}
		}

		unset($rawlist);

		// Fifth pass: Load the index page and determine ordering of slugs
		$mapSlugOrder    = [];
		$mapFilesToSlugs = [];
		$maxOrder        = 0;

		if (File::exists($dir_output . '/index.html'))
		{
			$file_data = file_get_contents($dir_output . '/index.html');
			$domdoc    = new DOMDocument();
			$success   = $domdoc->loadXML($file_data);

			unset($file_data);

			if ($success)
			{
				// Get a list of anchor elements (<a href="...">)
				$anchors = $domdoc->getElementsByTagName('a');

				/** @var \DOMNodeList $anchors */
				if (!empty($anchors))
				{
					/** @var \DOMElement $anchor */
					foreach ($anchors as $anchor)
					{
						// Grab the href
						$href = $anchor->getAttribute('href');
						// Kill any page anchors from the URL, e.g. #some-anchor
						$hashlocation = strpos($href, '#');

						if ($hashlocation !== false)
						{
							$href = substr($href, 0, $hashlocation);
						}

						// Only precess if this page is not already found
						$originalslug = basename($href, '.html');
						$slug         = ApplicationHelper::stringURLSafe($originalslug);

						if (!array_key_exists($originalslug, $mapFilesToSlugs))
						{
							$mapFilesToSlugs[$originalslug] = $slug;
						}

						if (!array_key_exists($slug, $mapSlugID))
						{
							continue;
						}

						if (array_key_exists($slug, $mapSlugOrder))
						{
							continue;
						}

						$mapSlugOrder[$slug] = ++$maxOrder;
					}
				}
			}
		}

		// Sixth pass: Load each article, replace links and modify ordering
		$allIds = array_values($mapSlugID);

		// Reverse sort the slugs. Think about href="foobar.html" and slugs "foo" and "foobar". We need this to
		// be handled by slug "foobar", NOT by slug "foo". This is only possible with reverse alpha sorting of the
		// slugs.
		arsort($mapFilesToSlugs, SORT_STRING);

		if (!empty($allIds))
		{
			foreach ($allIds as $id)
			{
				// Load the article

				/** @var ArticleTable $article */
				$article = $this->getMVCFactory()->createTable('Article');

				if (!$article->load($id))
				{
					continue;
				}

				// Replace links
				$fulltext = $article->fulltext;

				foreach ($mapFilesToSlugs as $realfile => $slug)
				{
					if (empty($realfile) || empty($slug))
					{
						$realfile = 'index';
						$slug     = 'index';
					}

					if ($slug == 'index')
					{
						$url = sprintf("index.php?option=com_docimport&view=category&id=%d", $category_id);
					}
					else
					{
						$id  = $mapSlugID[$slug];
						$url = sprintf("index.php?option=com_docimport&view=article&catid=%d&id=%d", $category_id, $id);
					}

					// With .html, without leading slash
					$fulltext = str_replace('href="' . $realfile . '.html', 'href="' . $url . '', $fulltext);
					// With .html, with leading slash
					$fulltext = str_replace('href="/' . $realfile . '.html', 'href="' . $url . '', $fulltext);
					// Without .html, without leading slash
					$fulltext = str_replace('href="' . $realfile, 'href="' . $url . '', $fulltext);
					// Without .html, with leading slash
					$fulltext = str_replace('href="/' . $realfile, 'href="' . $url . '', $fulltext);
				}

				// Replace ordering
				$ordering = $article->ordering;

				if (array_key_exists($article->slug, $mapSlugOrder))
				{
					$ordering = $mapSlugOrder[$article->slug];
				}

				// Apply changes
				$article->save([
					'fulltext' => $fulltext,
					'ordering' => $ordering,
				]);

				unset($fulltext);
				unset($article);
			}
		}
	}

	/**
	 * Scans for the existence of new categories
	 */
	public function scanCategories()
	{
		// Load a list of categories
		$db         = $this->getDbo();
		$query      = $db->getQuery(true)
			->from($db->qn('#__docimport_categories'))
			->select([
				$db->qn('docimport_category_id') . ' AS ' . $db->qn('id'),
				$db->qn('slug'),
			]);
		$categories = $db->setQuery($query)->loadObjectList('slug');

		// Get a list of subdirectories, except the built-in ones
		// -- Configured root
		$cparams        = ComponentHelper::getParams('com_docimport');
		$configuredRoot = $cparams->get('mediaroot', 'com_docimport/books');
		$configuredRoot = trim($configuredRoot, " \t\n\r/\\");
		$configuredRoot = empty($configuredRoot) ? 'com_docimport/books' : $configuredRoot;

		$path    = JPATH_ROOT . '/media/' . $configuredRoot;
		$folders = Folder::folders($path, '.', false, false);
		$folders = (empty($folders) || !is_array($folders)) ? [] : $folders;

		// -- media/com_docimport (legacy, very early versions)
		$path         = JPATH_ROOT . '/media/com_docimport';
		$folders_bare = Folder::folders($path, '.', false, false, ['admonition', 'css', 'js', 'images', 'books']);
		$folders_bare = (empty($folders_bare) || !is_array($folders_bare)) ? [] : $folders_bare;

		// -- media/com_docimport/books (legacy)
		if (Folder::exists($path . '/books'))
		{
			$folders_books = Folder::folders($path . '/books', '.', false, false, [
				'admonition', 'css', 'js', 'images',
			]);
			$folders_books = (empty($folders_books) || !is_array($folders_books)) ? [] : $folders_books;
		}
		else
		{
			$folders_books = [];
		}

		$folders = array_merge($folders, $folders_bare, $folders_books);
		$folders = array_unique($folders);

		// If a subdirectory doesn't exist, create a new category
		if (!empty($folders))
		{
			foreach ($folders as $folder)
			{
				if (!array_key_exists($folder, $categories))
				{
					/** @var CategoryTable $catTable */
					$catTable = $this->getMVCFactory()->createTable('Category');

					$catTable->reset();
					$catTable->save([
						'title'       => Text::sprintf('COM_DOCIMPORT_XSL_DEFAULT_TITLE', $folder),
						'slug'        => $folder,
						'description' => Text::_('COM_DOCIMPORT_XSL_DEFAULT_DESCRIPTION'),
						'ordering'    => 0,
					]);
				}
			}
		}
	}

	/**
	 * Parse an HTML output file of DocBook XSLT transformation
	 *
	 * @param   string  $filepath  The full path to the file
	 *
	 * @return object
	 */
	private function _getHTMLFileData($filepath)
	{
		$ret = (object) [
			'title'     => '',
			'contents'  => '',
			'timestamp' => 0,
		];

		$ret->timestamp = @filemtime($filepath);

		$filedata = file_get_contents($filepath);

		$startOfTitle = strpos($filedata, '<title>') + 7;
		$endOfTitle   = strpos($filedata, '</title>');
		$ret->title   = substr($filedata, $startOfTitle, $endOfTitle - $startOfTitle);

		// Extract the body
		$startOfContent = strpos($filedata, '<body>') + 6;
		$endOfContent   = strpos($filedata, '</body>');
		$ret->contents  = substr($filedata, $startOfContent, $endOfContent - $startOfContent);

		return $ret;
	}

}