<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Admin\Model;

use FOF30\Date\Date;
use FOF30\Model\Model;
use JText, JLoader, JFolder, JFile, JFactory, JApplicationHelper;
use DOMDocument, XSLTProcessor;

defined('_JEXEC') or die();

class Xsl extends Model
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
		/** @var Categories $category */
		$category = $this->container->factory->model('Categories')->tmpInstance();

		try
		{
			$category->findOrFail($category_id);
		}
		catch (\Exception $e)
		{
			throw new \RuntimeException(JText::sprintf('COM_DOCIMPORT_XSL_ERROR_NOCATEGORY', $category_id), 500);
		}

		// Check if directories exist
		JLoader::import('joomla.filesystem.folder');

		$cparams        = $this->container->params;
		$configuredRoot = $cparams->get('mediaroot', 'com_docimport/books');
		$configuredRoot = trim($configuredRoot, " \t\n\r/\\");
		$configuredRoot = empty($configuredRoot) ? 'com_docimport/books' : $configuredRoot;

		$dir_src    = JPATH_ROOT . '/media/' . $configuredRoot . '/' . $category->slug;
		$dir_output = JPATH_ROOT . '/media/' . $configuredRoot . '/' . $category->slug . '/output';

		if ( !JFolder::exists($dir_src))
		{
			$dir_src    = JPATH_ROOT . '/media/com_docimport/' . $category->slug;
			$dir_output = JPATH_ROOT . '/media/com_docimport/' . $category->slug . '/output';
		}

		if ( !JFolder::exists($dir_src))
		{
			$dir_src    = JPATH_ROOT . '/media/com_docimport/books/' . $category->slug;
			$dir_output = JPATH_ROOT . '/media/com_docimport/books/' . $category->slug . '/output';
		}

		if ( !JFolder::exists($dir_src))
		{
			throw new \RuntimeException(JText::sprintf('COM_DOCIMPORT_XSL_ERROR_NOFOLDER', $category->slug), 500);
		}

		// Clear the output directory
		if (JFolder::exists($dir_output))
		{
			JFolder::delete($dir_output);
		}

		// Regenerate the output directory
		if ( !JFolder::exists($dir_output))
		{
			$result = JFolder::create($dir_output);

			if ( !$result)
			{
				throw new \RuntimeException(JText::sprintf('COM_DOCIMPORT_XSL_ERROR_CANTCREATEFOLDER', $category->slug . '/output'), 500);
			}

			JLoader::import('joomla.filesystem.file');
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

			JFile::write($dir_output . '/.htaccess', $content);
		}

		// Find the XML file
		$xmlfiles = JFolder::files($dir_src, '\.xml$', false, true);

		// If we have many files, let's filter out only articles and books
		$hasManySourceFiles = count($xmlfiles) > 1;

		if ($hasManySourceFiles)
		{
			$files    = $xmlfiles;
			$xmlfiles = array();

			foreach ($files as $file_xml)
			{
				$xmlDoc = new DOMDocument();

				if ( !$xmlDoc->load($file_xml))
				{
					continue;
				}

				$tagName = $xmlDoc->documentElement->tagName;

				if (in_array($tagName, array('article', 'book')))
				{
					$xmlfiles[] = $file_xml;
				}

				unset($xmlDoc);
			}
		}

		$xslt_filename = ($hasManySourceFiles) ? 'onechunk.xsl' : 'chunk.xsl';

		if (($xmlfiles === false) || (empty($xmlfiles)))
		{
			throw new \RuntimeException(JText::_('COM_DOCIMPORT_XSL_ERROR_NOXMLFILES'), 500);
		}

		$file_xsl = JPATH_ADMINISTRATOR . '/components/com_docimport/assets/dbxsl/xhtml/' . $xslt_filename;

		// Load the XSLT filters
		$xslDoc = new DOMDocument();

		if ( !$xslDoc->load($file_xsl))
		{
			throw new \RuntimeException(JText::_('COM_DOCIMPORT_XSL_ERROR_NOLOADXSL'));
		}

		$timestamp = 0;

		foreach ($xmlfiles as $file_xml)
		{
			// Load the XML document
			$xmlDoc = new DOMDocument();

			if ( !$xmlDoc->load($file_xml, LIBXML_DTDATTR | LIBXML_NOENT | LIBXML_NONET | LIBXML_XINCLUDE))
			{
				throw new \RuntimeException(JText::_('COM_DOCIMPORT_XSL_ERROR_NOLOADXML'));
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

			$parameters = array(
				'base.dir'            => rtrim($dir_output, '/') . '/' . (empty($filesprefix) ? '' : $filesprefix . '-'),
				'img.src.path'        => $path_src,
				'admon.graphics.path' => '/media/com_docimport/admonition/',
				'admon.graphics'      => 1,
				'use.id.as.filename'  => 1,
				'toc.section.depth'   => 5,
				'chunk.section.depth' => 3,
				'highlight.source'    => 1,
			);

			if ($hasManySourceFiles)
			{
				$parameters['use.id.as.filename'] = 0;
				$parameters['base.dir'] = rtrim($dir_output, '/');
				$parameters['root.filename'] = (empty($filesprefix) ? '' : $filesprefix . '-') . 'index.html';
			}

			$xslt       = new XSLTProcessor();
			$xslt->importStylesheet($xslDoc);

			if ( !$xslt->setParameter('', $parameters))
			{
				throw new \RuntimeException(JText::_('COM_DOCIMPORT_XSL_ERROR_NOLOADPARAMETERS'), 500);
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
				throw new \RuntimeException(JText::_('COM_DOCIMPORT_XSL_ERROR_FAILEDTOPROCESS'), 500);
			}

			$timestamp_local = @filemtime($file_xml);

			if ($timestamp_local > $timestamp)
			{
				$timestamp = $timestamp_local;
			}

			if ( !empty($filesprefix))
			{
				$fname   = rtrim($dir_output, '/') . "/$filesprefix-index.html";
				$renamed = rtrim($dir_output, '/') . "/$filesprefix.html";

				if (@file_exists($fname))
				{
					JFile::move($fname, $renamed);
				}
			}
		}

		// Update the database record with the file's/files' timestamp
		$category->save(array(
			'last_timestamp' => $timestamp,
		));
	}

	/**
	 * Scans the output directory of the given category for new HTML files and updates the database.
	 *
	 * @param   int   $category_id  The ID of the category to scan
	 *
	 * @return  void
	 */
	public function processFiles($category_id)
	{
		// Get the category record
		// Get the category record
		/** @var Categories $category */
		$category = $this->container->factory->model('Categories')->tmpInstance();

		try
		{
			$category->findOrFail($category_id);
		}
		catch (\Exception $e)
		{
			throw new \RuntimeException(JText::sprintf('COM_DOCIMPORT_XSL_ERROR_NOCATEGORY', $category_id), 500);
		}

		// Check if directories exist
		JLoader::import('joomla.filesystem.folder');
		JLoader::import('cms.component.helper');

		$cparams        = $this->container->params;
		$configuredRoot = $cparams->get('mediaroot', 'com_docimport/books');
		$configuredRoot = trim($configuredRoot, " \t\n\r/\\");
		$configuredRoot = empty($configuredRoot) ? 'com_docimport/books' : $configuredRoot;

		$dir_src    = JPATH_ROOT . '/media/' . $configuredRoot . '/' . $category->slug;
		$dir_output = JPATH_ROOT . '/media/' . $configuredRoot . '/' . $category->slug . '/output';

		if ( !JFolder::exists($dir_src))
		{
			$dir_src    = JPATH_ROOT . '/media/com_docimport/' . $category->slug;
			$dir_output = JPATH_ROOT . '/media/com_docimport/' . $category->slug . '/output';
		}

		if ( !JFolder::exists($dir_src))
		{
			$dir_src    = JPATH_ROOT . '/media/com_docimport/books/' . $category->slug;
			$dir_output = JPATH_ROOT . '/media/com_docimport/books/' . $category->slug . '/output';
		}

		if ( !JFolder::exists($dir_src))
		{
			throw new \RuntimeException(JText::sprintf('COM_DOCIMPORT_XSL_ERROR_NOFOLDER', $category->slug), 500);
		}

		if ( !JFolder::exists($dir_output))
		{
			throw new \RuntimeException(JText::sprintf('COM_DOCIMPORT_XSL_ERROR_NOFOLDER', $category->slug . '/output'), 500);
		}

		// Load the list of articles in this category
		$db    = $this->container->db;

		$query = $db->getQuery(true)
		            ->from($db->quoteName('#__docimport_articles'))
		            ->select(array(
			            $db->quoteName('docimport_article_id') . ' AS ' . $db->quoteName('id'),
			            $db->quoteName('slug'),
			            $db->quoteName('last_timestamp'),
			            $db->quoteName('enabled'),
		            ))
		            ->where($db->quoteName('docimport_category_id') . ' = ' . $db->quote($category_id));
		$articles = $db->setQuery($query)->loadObjectList('slug');

		if (empty($articles))
		{
			$articles = array();
		}

		// Get a list of existing files
		$files = JFolder::files($dir_output, '\.html$');

		// And now, turn the files into a list of slugs
		$slugs = array();

		if ( !empty($files))
		{
			foreach ($files as $filename)
			{
				$slugs[] = basename($filename, '.html');
			}
		}

		// First pass: find articles pointing to files no longer existing
		if ( !empty($articles))
		{
			foreach ($articles as $slug => $article)
			{
				if ( !in_array($slug, $slugs))
				{
					/** @var Articles $articleModel */
					$articleModel = $this->container->factory->model('Articles')->tmpInstance();

					try
					{
						$articleModel->findOrFail($article->id)->unpublish();
					}
					catch (\Exception $e)
					{
						// Ignore errors at this stable
					}
				}
			}
		}

		// Second pass: add articles which are not already there
		if ( !empty($slugs))
		{
			foreach ($slugs as $slug)
			{
				if ( !array_key_exists($slug, $articles))
				{
					JLoader::import('joomla.utilities.date');

					$jNow = new Date();

					$user_id = $this->container->platform->getUser()->id;

					if (empty($user_id))
					{
						$user_id = 42;
					}

					$filepath = $dir_output . '/' . $slug . '.html';
					$filedata = $this->_getHTMLFileData($filepath);

					/** @var Articles $articleModel */
					$articleModel = $this->container->factory->model('Articles')->tmpInstance();
					$articleModel->create([
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
		if ( !empty($slugs) && !empty($articles))
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

					JLoader::import('joomla.utilities.date');

					$jNow = new Date();

					$user_id = $this->container->platform->getUser()->id;

					if (empty($user_id))
					{
						$user_id = 42;
					}

					$filedata = $this->_getHTMLFileData($filepath);

					/** @var Articles $articleModel */
					$articleModel = $this->container->factory->model('Articles')->tmpInstance();

					try
					{
						$articleModel->findOrFail($article->id)->save([
							'title'          => $filedata->title,
							'fulltext'       => $filedata->contents,
							'last_timestamp' => $filedata->timestamp,
							'enabled'        => 1,
							'locked_on'      => '0000-00-00 00:00:00',
							'locked_by'      => 0,
							'modified_on'    => $jNow->toSql(),
							'modified_by'    => $user_id,
						]);
					}
					catch (\Exception $e)
					{
						// Um, if we couldn't load that article let's create it instead
						$articleModel->create([
							'title'          => $filedata->title,
							'fulltext'       => $filedata->contents,
							'last_timestamp' => $filedata->timestamp,
							'enabled'        => 1,
							'locked_on'      => '0000-00-00 00:00:00',
							'locked_by'      => 0,
							'modified_on'    => $jNow->toSql(),
							'modified_by'    => $user_id,
						]);
					}
				}
			}
		}

		// Fourth pass: Load a list of enabled articles (IDs and slugs)
		$db    = $this->container->db;
		$query = $db->getQuery(true)
		            ->from($db->qn('#__docimport_articles'))
		            ->select(array(
			            $db->qn('docimport_article_id', 'id'),
			            $db->qn('slug'),
		            ))
		            ->where($db->qn('docimport_category_id') . ' = ' . $db->q($category_id))
		            ->where($db->qn('enabled') . ' = ' . $db->q(1));
		$rawlist   = $db->setQuery($query)->loadObjectList();

		$mapSlugID = array();

		if ( !empty($rawlist))
		{
			foreach ($rawlist as $rawItem)
			{
				$mapSlugID[$rawItem->slug] = $rawItem->id;
			}
		}

		unset($rawlist);

		// Fifth pass: Load the index page and determine ordering of slugs
		$mapSlugOrder    = array();
		$mapFilesToSlugs = array();
		$maxOrder        = 0;

		if (JFile::exists($dir_output . '/index.html'))
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
				if ( !empty($anchors))
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
						$slug         = JApplicationHelper::stringURLSafe($originalslug);

						if ( !array_key_exists($originalslug, $mapFilesToSlugs))
						{
							$mapFilesToSlugs[$originalslug] = $slug;
						}

						if ( !array_key_exists($slug, $mapSlugID))
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

		if ( !empty($allIds))
		{
			foreach ($allIds as $id)
			{
				// Load the article

				/** @var Articles $article */
				$article = $this->container->factory->model('Articles')->tmpInstance();
				$article->findOrFail($id);

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
						$url = 'index.php?option=com_docimport&view=category&id=' . $category_id;
					}
					else
					{
						$id  = $mapSlugID[$slug];
						$url = 'index.php?option=com_docimport&view=article&id=' . $id;
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
				$article->save(array(
					'fulltext' => $fulltext,
					'ordering' => $ordering,
				));

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
		$db    = $this->container->db;
		$query = $db->getQuery(true)
		            ->from($db->qn('#__docimport_categories'))
		            ->select(array(
			            $db->qn('docimport_category_id') . ' AS ' . $db->qn('id'),
			            $db->qn('slug'),
		            ));
		$categories = $db->setQuery($query)->loadObjectList('slug');

		// Get a list of subdirectories, except the built-in ones
		JLoader::import('joomla.filesystem.folder');

		// -- Configured root
		JLoader::import('cms.component.helper');

		$cparams        = $this->container->params;
		$configuredRoot = $cparams->get('mediaroot', 'com_docimport/books');
		$configuredRoot = trim($configuredRoot, " \t\n\r/\\");
		$configuredRoot = empty($configuredRoot) ? 'com_docimport/books' : $configuredRoot;

		$path    = JPATH_ROOT . '/media/' . $configuredRoot;
		$folders = JFolder::folders($path, '.', false, false);
		$folders = (empty($folders) || !is_array($folders)) ? array() : $folders;

		// -- media/com_docimport (legacy, very early versions)
		$path         = JPATH_ROOT . '/media/com_docimport';
		$folders_bare = JFolder::folders($path, '.', false, false, array('admonition', 'css', 'js', 'images', 'books'));
		$folders_bare = (empty($folders_bare) || !is_array($folders_bare)) ? array() : $folders_bare;

		// -- media/com_docimport/books (legacy)
		if (JFolder::exists($path . '/books'))
		{
			$folders_books = JFolder::folders($path . '/books', '.', false, false, array('admonition', 'css', 'js', 'images'));
			$folders_books = (empty($folders_books) || !is_array($folders_books)) ? array() : $folders_books;
		}
		else
		{
			$folders_books = array();
		}

		$folders = array_merge($folders, $folders_bare, $folders_books);
		$folders = array_unique($folders);

		// If a subdirectory doesn't exist, create a new category
		if ( !empty($folders))
		{
			foreach ($folders as $folder)
			{
				if ( !array_key_exists($folder, $categories))
				{
					/** @var Categories $model */
					$model = $this->container->factory->model('Categories')->tmpInstance();

					$model->create([
						'title'       => JText::sprintf('COM_DOCIMPORT_XSL_DEFAULT_TITLE', $folder),
						'slug'        => $folder,
						'description' => JText::_('COM_DOCIMPORT_XSL_DEFAULT_DESCRIPTION'),
						'ordering'    => 0,
					]);
				}
			}
		}
	}

	/**
	 * Parse an HTML output file of DocBook XSLT transformation
	 *
	 * @param string $filepath The full path to the file
	 *
	 * @return object
	 */
	private function _getHTMLFileData($filepath)
	{
		$ret = (object)array(
			'title'     => '',
			'contents'  => '',
			'timestamp' => 0,
		);

		$ret->timestamp = @filemtime($filepath);

		JLoader::import('joomla.filesystem.file');
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
