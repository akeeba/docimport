<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// no direct access
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Log\Log;

class Com_DocimportInstallerScript
{
	protected $minimumPHPVersion = '7.2.0';

	protected $minimumJoomlaVersion = '4.0.0.b1';

	protected $maximumJoomlaVersion = '4.0.999';

	/**
	 * Obsolete files and folders to remove from both paid and free releases. This is used when you refactor code and
	 * some files inevitably become obsolete and need to be removed.
	 *
	 * @var   array
	 */
	protected $removeFiles = [
		'files'   => [
			// Obsolete CLI script
			'cli/docimport-update.php',

			// Obsolete update information
			'cache/com_docimport.updates.php',
			'cache/com_docimport.updates.ini',
			'administrator/cache/com_docimport.updates.php',
			'administrator/cache/com_docimport.updates.ini',

			// Obsolete CSS files
			'media/com_docimport/css/backend.min.css',
			'media/com_docimport/css/frontend.min.css',
			'media/com_docimport/css/search.min.css',
		],
		'folders' => [
		],
	];

	private $tableIndices = [
		'#__content'    => [
			'introtext',
			'fulltext',
			'title',
		],
		'#__categories' => [
			'description',
			'title',
		],
	];

	/**
	 * Joomla! pre-flight event. This runs before Joomla! installs or updates the component. This is our last chance to
	 * tell Joomla! if it should abort the installation.
	 *
	 * @param   string                      $type    Installation type (install, update, discover_install)
	 * @param   JInstallerAdapterComponent  $parent  Parent object
	 *
	 * @return  boolean  True to let the installation proceed, false to halt the installation
	 */
	public function preflight($type, $parent)
	{
		// Check the minimum PHP version
		if (!version_compare(PHP_VERSION, $this->minimumPHPVersion, 'ge'))
		{
			$msg = "<p>You need PHP $this->minimumPHPVersion or later to install this component</p>";

			Log::add($msg, Log::WARNING, 'jerror');

			return false;
		}

		// Check the minimum Joomla! version
		if (!version_compare(JVERSION, $this->minimumJoomlaVersion, 'ge'))
		{
			$msg = "<p>You need Joomla! $this->minimumJoomlaVersion or later to install this component</p>";

			Log::add($msg, Log::WARNING, 'jerror');

			return false;
		}

		// Check the maximum Joomla! version
		if (!version_compare(JVERSION, $this->maximumJoomlaVersion, 'le'))
		{
			$msg = "<p>You need Joomla! $this->maximumJoomlaVersion or earlier to install this component</p>";

			Log::add($msg, Log::WARNING, 'jerror');

			return false;
		}

		return true;
	}

	/**
	 * Runs after install, update or discover_update. In other words, it executes after Joomla! has finished installing
	 * or updating your component. This is the last chance you've got to perform any additional installations, clean-up,
	 * database updates and similar housekeeping functions.
	 *
	 * @param   string                      $type    install, update or discover_update
	 * @param   JInstallerAdapterComponent  $parent  Parent object
	 */
	function postflight($type, $parent)
	{
		// Add custom com_content indices
		$this->addComContentIndices();

		// Remove obsolete files and folders
		$this->removeFilesAndFolders($this->removeFiles);

		// Always reset the OPcache if it's enabled. Otherwise there's a good chance the server will not know we are
		// replacing .php scripts. This is a major concern since PHP 5.5 included and enabled OPcache by default.
		if (function_exists('opcache_reset'))
		{
			opcache_reset();
		}
	}

	function uninstall($type)
	{
		// Remove custom com_content indices
		$this->removeComContentIndices();
	}

	/**
	 * Removes obsolete files and folders
	 *
	 * @param   array  $removeList  The files and directories to remove
	 */
	private function removeFilesAndFolders($removeList)
	{
		foreach ($removeList['files'] ?? [] as $file)
		{
			$f = JPATH_ROOT . '/' . $file;

			@is_file($f) && File::delete($f);
		}

		foreach ($removeList['folders'] ?? [] as $folder)
		{
			$f = JPATH_ROOT . '/' . $folder;

			@is_dir($f) && Folder::delete($f);
		}
	}

	private function addComContentIndices()
	{
		$db = Factory::getDbo();

		foreach ($this->tableIndices as $table => $columns)
		{
			foreach ($columns as $column)
			{
				try
				{
					$query    = $db->getQuery(true)
						->select('COUNT(*)')
						->from($db->quoteName('INFORMATION_SCHEMA.STATISTICS'))
						->where($db->quoteName('table_schema') . ' = DATABASE()')
						->where($db->quoteName('table_name') . ' = :table')
						->where($db->quoteName('column_name') . ' = :column')
						->where($db->quoteName('index_type') . ' = ' . $db->quote('FULLTEXT'))
						->bind(':table', $table)
						->bind(':column', $column);
					$hasIndex = (int) ($db->setQuery($query)->loadResult() ?: 0);
					if ($hasIndex > 0)
					{
						continue;
					}

					// e.g. #__idx_content_search_introtext
					$indexName = $db->quoteName(str_replace('#__', '#__idx_', $table) . '_search_' . $column);
					$table     = $db->quoteName($table);
					$column    = $db->quoteName($column);
					$query     = <<< SQL
ALTER TABLE $table ADD FULLTEXT INDEX $indexName ($column)
SQL;
					$db->setQuery($query)->execute();
				}
				catch (Exception $e)
				{
					continue;
				}
			}
		}
	}

	private function removeComContentIndices()
	{
		$db = Factory::getDbo();

		foreach ($this->tableIndices as $table => $columns)
		{
			foreach ($columns as $column)
			{
				try
				{
					$indexName = str_replace('#__', '#__idx_', $table) . '_search_' . $column;

					$query    = $db->getQuery(true)
						->select('COUNT(*)')
						->from($db->quoteName('INFORMATION_SCHEMA.STATISTICS'))
						->where($db->quoteName('table_schema') . ' = DATABASE()')
						->where($db->quoteName('table_name') . ' = :table')
						->where($db->quoteName('index_name') . ' = :index')
						->bind(':table', $table)
						->bind(':index', $indexName);

					$hasIndex = (int) ($db->setQuery($query)->loadResult() ?: 0);

					if ($hasIndex < 1)
					{
						continue;
					}

					$indexName = $db->quoteName($indexName);
					$table     = $db->quoteName($table);
					$query     = <<< SQL
ALTER TABLE $table DROP INDEX $indexName;
SQL;
					$db->setQuery($query)->execute();
				}
				catch (Exception $e)
				{
					continue;
				}
			}
		}
	}
}
