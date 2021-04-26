<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Admin\Model;

use FOF40\Container\Container;
use FOF40\Model\DataModel;
use JLoader, JFolder;

defined('_JEXEC') or die();

/**
 * Model class for the Articles
 *
 * Fields:
 *
 * @property int    $docimport_category_id
 * @property string $title
 * @property string $slug
 * @property string $description
 * @property string $image
 * @property int    $process_plugins
 * @property int    $last_timestamp
 * @property string $language
 * @property int    $access
 *
 * Filters / state:
 *
 * @method  $this  docimport_article_id()   docimport_article_id(int $v)
 * @method  $this  docimport_category_id()  docimport_category_id(int $v)
 * @method  $this  category()               category(int $v)
 * @method  $this  slug()                   slug(string|array $v)
 * @method  $this  title()                  title(string|array $v)
 * @method  $this  search()                 search(string|array $v)
 * @method  $this  language()               language(array $v)
 *
 * Relations:
 *
 * @property-read  DataModel\Collection	 $articles  The Articles of this DocImport Category
 */
class Categories extends DataModel
{
	/**
	 * Public constructor. Adds behaviours and sets up the behaviours and the relations
	 *
	 * @param   Container  $container
	 * @param   array      $config
	 */
	public function __construct(Container $container, array $config = array())
	{
		parent::__construct($container, $config);

		// Add the filtering behaviour
		$this->addBehaviour('Filters');

		$this->blacklistFilters([
			'title',
		    'description',
		    'image',
		    'process_plugins',
		    'last_timestamp',
		]);

		// Other front-end behaviours
		if ($container->platform->isFrontend())
		{
			$this->addBehaviour('Access');
			$this->addBehaviour('Langauge');
		}

		// Set up relations
		$this->hasMany('articles', 'Articles', 'docimport_category_id', 'docimport_category_id');

		// Calculated fields
		$this->addKnownField('status', 0, 'int');
	}

	/**
	 * Apply custom filters
	 *
	 * @param   \JDatabaseQuery  $query
	 */
	protected function onBeforeBuildQuery(\JDatabaseQuery &$query)
	{
		$db = $this->getDbo();

		// Search filter, use either "title" or "search" model state.
		$fltSearch = $this->getState('title', null, 'string');
		$fltSearch = $this->getState('search', $fltSearch, 'string');

		if (!empty($fltSearch))
		{
			$query->where(
				$db->quoteName('a') . '.' . $db->quoteName('title') . ' LIKE ' . $db->quote('%' . $fltSearch . '%')
			);
		}

		// Set the default ordering by ID, descending
		if (is_null($this->getState('filter_order', null, 'cmd')) && is_null($this->getState('filter_order_Dir', null, 'cmd')))
		{
			$this->setState('filter_order', $this->getIdFieldName());
			$this->setState('filter_order_Dir', 'DESC');
		}
	}

	/**
	 * Runs after loading a new record. Populates calculated fields
	 *
	 * @param   bool   $result  Did the load succeed?
	 * @param   mixed  $keys    The PK we were asked to load or an associative array of (filter) keys
	 *
	 * @return  void
	 */
	protected function onAfterLoad($result, &$keys)
	{
		if (!$result)
		{
			return;
		}

		$this->setFieldValue('status', $this->getStatusFor($this));
	}

	/**
	 * Post process the items to get the values of the calculated fields
	 *
	 * @param   Categories[]  $items
	 */
	protected function onAfterGetItemsArray(array &$items)
	{
		foreach ($items as $item)
		{
			$item->status = $this->getStatusFor($item);
		}
	}

	/**
	 * Determine the status for a given item
	 *
	 * @param   Categories  $item
	 *
	 * @return  string
	 */
	protected function getStatusFor(Categories $item)
	{
		$status = 'missing';

		// First get the configured root directory
		JLoader::import('joomla.filesystem.folder');
		JLoader::import('cms.component.helper');
		$cparams        = $this->container->params;
		$configuredRoot = $cparams->get('mediaroot', 'com_docimport/books');
		$configuredRoot = trim($configuredRoot, " \t\n\r/\\");
		$configuredRoot = empty($configuredRoot) ? 'com_docimport/books' : $configuredRoot;

		$folder = JPATH_ROOT . '/media/' . $configuredRoot . '/' . $item->slug;

		if (!JFolder::exists($folder))
		{
			$folder = JPATH_ROOT . '/media/com_docimport/' . $item->slug;
		}

		if (!JFolder::exists($folder))
		{
			$folder = JPATH_ROOT . '/media/com_docimport/books/' . $item->slug;
		}

		if (!JFolder::exists($folder))
		{
			return $status;
		}

		$xmlfiles = JFolder::files($folder, '\.xml$', false, true);

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
			return 'modified';
		}

		return 'unmodified';
	}

	/**
	 * Runs before saving data. We use it to remove the computed fields.
	 *
	 * @param  $data
	 *
	 * @return  void
	 */
	protected function onBeforeSave(&$data)
	{
		// This is just a fake record field, it must not be present when saving the record
		if (isset($this->recordData['status']))
		{
			unset($this->recordData['status']);
		}
	}

	/**
	 * Cascade deletion of category to its included articles
	 *
	 * @param   int  $id  The ID of the category being deleted
	 */
	protected function onAfterDelete(&$id)
	{
		$this->articles->each(function($item){
			/** @var Articles $item */
			$item->delete();
		});
	}

	/**
	 * Sanitize data before save
	 *
	 * @return  void
	 */
	public function check()
	{
		// Create a new or sanitise an existing slug
		if (empty($this->slug))
		{
			// Auto-fetch a slug
			$this->slug = \JApplicationHelper::stringURLSafe($this->title);
		}
		else
		{
			// Make sure nobody adds crap characters to the slug
			$this->slug = \JApplicationHelper::stringURLSafe($this->slug);
		}

		// Look for a similar slug
		/** @var self $existingItems */
		$existingItems = $this->getClone()->setIgnoreRequest(true)->savestate(false)
		                      ->slug([
			                      'method' => 'exact',
			                      'value' => $this->slug
		                      ])
		                      ->get(true);

		if ($existingItems->count())
		{
			$count = 0;
			$k     = $this->getKeyName();

			foreach ($existingItems as $item)
			{
				if ($item->$k != $this->$k)
				{
					$count ++;
				}
			}

			if ($count != 0)
			{
				$this->slug .= ' ' . $this->container->platform->getDate()->toUnix();
			}
		}

		if (empty($this->language))
		{
			$this->language = '*';
		}

		if (empty($this->access))
		{
			$this->access = 1;
		}
	}
}
