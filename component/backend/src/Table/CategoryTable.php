<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\DocImport\Administrator\Table;

defined('_JEXEC') || die();

use Akeeba\Component\DocImport\Administrator\Table\Mixin\CreateModifyAware;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\Event;
use RuntimeException;

/**
 * @property int         $docimport_category_id Category ID
 * @property string      $title                 Category title
 * @property string      $slug                  Category alias
 * @property string      $description           Category description (intro text)
 * @property string|null $image                 Category image file
 * @property int         $process_plugins       Should I run content plugins in the articles of this category?
 * @property int         $last_timestamp        Last HTML generation UNIX timestamp
 * @property int         $enabled               Is this category published?
 * @property int         $ordering              Ordering
 * @property string      $created_on            Created date and time
 * @property int         $created_by            User ID which created this category
 * @property string      $modified_on           Last modified date and time
 * @property int         $modified_by           User ID which last modified this category
 * @property string      $locked_on             Row lock date and time
 * @property int         $locked_by             User ID which locked this category
 * @property string      $language              Applicable language, '*' for all
 * @property int         $access                View access level for this category and its articles
 */
class CategoryTable extends Table
{
	use CreateModifyAware;

	public const MISSING = 'missing';

	public const MODIFIED = 'modified';

	public const UNMODIFIED = 'unmodified';

	/**
	 * The state of this category.
	 *
	 * @var string
	 */
	public $state = self::UNMODIFIED;

	public function __construct(DatabaseDriver $db)
	{
		parent::__construct('#__docimport_categories', 'docimport_category_id', $db);

		$this->setColumnAlias('id', 'docimport_category_id');
		$this->setColumnAlias('alias', 'slug');
		$this->setColumnAlias('published', 'enabled');
		$this->setColumnAlias('checked_out', 'locked_by');
		$this->setColumnAlias('checked_out_time', 'locked_on');

		$this->created_on      = Factory::getDate()->toSql();
		$this->process_plugins = 0;
		$this->access          = 1;
		$this->language        = '*';
		$this->state           = self::UNMODIFIED;

		// Add event listeners
		$dispatcher = $this->getDispatcher();

		if (!$dispatcher->hasListener([$this, 'onTableAfterDelete'], 'onTableAfterDelete'))
		{
			$dispatcher->addListener('onTableAfterDelete', [$this, 'onTableAfterDelete']);
		}
	}

	public function __destruct()
	{
		// Remove event listeners
		$dispatcher = $this->getDispatcher();

		if ($dispatcher->hasListener([$this, 'onTableAfterDelete'], 'onTableAfterDelete'))
		{
			$dispatcher->removeListener('onTableAfterDelete', [$this, 'onTableAfterDelete']);
		}
	}

	public function reset()
	{
		parent::reset();

		$this->state = self::UNMODIFIED;
	}

	public function bind($src, $ignore = [])
	{
		parent::bind($src, $ignore);

		$this->state = self::getStatusFor($this);

		return true;
	}

	/**
	 * Determine the status for a given item.
	 *
	 * @param   object  $item
	 *
	 * @return  string 'missing', 'modified' or 'unmodified'
	 */
	public static function getStatusFor($item)
	{
		$status = self::MISSING;

		// First get the configured root directory
		$cparams        = ComponentHelper::getParams('com_docimport');
		$configuredRoot = $cparams->get('mediaroot', 'com_docimport/books');
		$configuredRoot = trim($configuredRoot, " \t\n\r/\\");
		$configuredRoot = empty($configuredRoot) ? 'com_docimport/books' : $configuredRoot;

		$folder = JPATH_ROOT . '/media/' . $configuredRoot . '/' . $item->slug;

		if (!Folder::exists($folder))
		{
			$folder = JPATH_ROOT . '/media/com_docimport/' . $item->slug;
		}

		if (!Folder::exists($folder))
		{
			$folder = JPATH_ROOT . '/media/com_docimport/books/' . $item->slug;
		}

		if (!Folder::exists($folder))
		{
			return $status;
		}

		$xmlfiles = Folder::files($folder, '\.xml$', false, true);

		if (empty($xmlfiles))
		{
			return $status;
		}

		$timestamp = 0;

		foreach ($xmlfiles as $filename)
		{
			clearstatcache($filename);
			$my_timestamp = @filemtime($filename);

			if ($my_timestamp > $timestamp)
			{
				$timestamp = $my_timestamp;
			}
		}

		if ($timestamp != $item->last_timestamp)
		{
			return self::MODIFIED;
		}

		return self::UNMODIFIED;
	}

	public function check()
	{
		if (!parent::check())
		{
			return false;
		}

		// Remove leading/trailing whitespace from the title and slug
		$this->title = trim($this->title ?? '');
		$this->slug = trim($this->slug ?? '');

		// We need a non-empty title
		if (empty($this->title))
		{
			throw new RuntimeException('COM_DOCIMPORT_ERR_CATEGORY_TITLE');
		}

		// No slug? Create one from the title.
		$this->slug = trim(ApplicationHelper::stringURLSafe($this->slug ?: $this->title));

		// We need a non-empty slug
		if (empty($this->slug))
		{
			throw new RuntimeException('COM_DOCIMPORT_ERR_CATEGORY_SLUG');
		}

		// Look for a similar slug in any item OTHER than ourselves.
		$db = $this->getDbo();
		$pk = $this->getId();
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($this->getTableName())
			->where($db->quoteName('slug') . ' = :slug')
			->bind(':slug', $this->slug);

		if ($pk > 0)
		{
			$query->where($db->quoteName($this->getKeyName()) . ' != :pk')
				->bind(':pk', $pk);
		}

		if ((int) ($db->setQuery($query)->loadResult() ?: 0) > 0)
		{
			$this->slug .= '_' . (new Date())->toUnix();
		}

		// If no language is set we'll use the "All languages" default value.
		if (empty($this->language))
		{
			$this->language = '*';
		}

		// If no access is set we'll use view access level 1 (by default that's public access)
		if (empty($this->access))
		{
			$this->access = 1;
		}

		return true;
	}

	public function store($updateNulls = false)
	{
		$this->onBeforeStore();

		return parent::store($updateNulls);
	}

	/**
	 * Cascades the deleting of articles when a category is deleted.
	 *
	 * @param   Event  $event
	 */
	public function onTableAfterDelete(Event $event)
	{
		$table = $event->getArgument('subject');
		$pk    = $event->getArgument('pk');

		if ($table !== $this)
		{
			return;
		}

		if (empty($pk))
		{
			return;
		}

		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName('docimport_article_id'))
			->from($db->quoteName('#__docimport_articles'))
			->where($db->quoteName('docimport_category_id') . ' = :pk')
			->bind(':pk', $pk['docimport_category_id']);
		$articleIDs = $db->setQuery($query)->loadColumn();

		if (empty($articleIDs))
		{
			return;
		}

		$article = new ArticleTable($db);

		foreach ($articleIDs as $aid)
		{
			$article->delete($aid);
		}
	}
}