<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\DocImport\Administrator\Model;

defined('_JEXEC') || die;

use Akeeba\Component\DocImport\Administrator\Table\CategoryTable;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

class CategoriesModel extends ListModel
{
	public function __construct($config = [], MVCFactoryInterface $factory = null)
	{
		$config['filter_fields'] = ($config['filter_fields'] ?? []) ?: [
			// Used for filtering and/or sorting in the GUI
			'search',
			'process_plugins',
			'enabled',
			'access',
			'language',
			'ordering',
			// Only used for sorting in the GUI
			'title',
			'slug',
			'created_on',
		];

		parent::__construct($config, $factory);
	}

	protected function populateState($ordering = null, $direction = null)
	{
		/** @var CMSApplication $app */
		$app     = Factory::getApplication();
		$filters = [
			'search'          => ['string', ''],
			'process_plugins' => ['int', ''],
			'enabled'         => ['int', ''],
			'access'          => ['int', ''],
			'language'        => ['string', ''],
		];

		foreach ($filters as $filterName => $options)
		{
			[$type, $default] = $options;
			$value = $app->getUserStateFromRequest($this->context . 'filter.' . $filterName, 'filter_' . $filterName, $default, 'string');

			switch ($type)
			{
				case 'string':
					$this->setState('filter.' . $filterName, $value);
					break;

				case 'int':
					$this->setState('filter.' . $filterName, ($value === '') ? $value : (int) $value);
					break;
			}
		}

		parent::populateState($ordering, $direction);
	}

	protected function getStoreId($id = '')
	{
		$id .= ':' . implode(':', [
				$this->getState('filter.search'),
				$this->getState('filter.process_plugins'),
				$this->getState('filter.enabled'),
				$this->getState('filter.access'),
				$this->getState('filter.language'),
			]);

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select([
				$db->quoteName('c') . '.*',
				$db->quoteName('l.title', 'language_title'),
				$db->quoteName('l.image', 'language_image'),
				$db->quoteName('ag.title', 'access_level'),
			])
			->from($db->qn('#__docimport_categories', 'c'))
			->join('LEFT', $db->quoteName('#__viewlevels', 'ag'), $db->quoteName('ag.id') . ' = ' . $db->quoteName('c.access'))
			->join('LEFT', $db->quoteName('#__languages', 'l'), $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('c.language'))
		;

		// Search filter
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$ids = (int) substr($search, 3);
				$query->where($db->quoteName('docimport_category_id') . ' = :id')
					->bind(':id', $ids, ParameterType::INTEGER);
			}
			else
			{
				$search = '%' . $search . '%';
				$query->where(
					'(' .
					$db->qn('title') . ' LIKE :search1 OR ' .
					$db->qn('description') . ' LIKE :search2 OR ' .
					$db->qn('slug') . ' LIKE :search3'
					. ')'
				)
					->bind(':search1', $search)
					->bind(':search2', $search)
					->bind(':search3', $search);
			}
		}

		// Enabled filter
		$enabled = $this->getState('filter.enabled');

		if (is_numeric($enabled))
		{
			$query->where($db->quoteName('enabled') . ' = :enabled')
				->bind(':enabled', $enabled);
		}

		// Process plugins filter
		$plugins = $this->getState('filter.process_plugins');

		if (is_numeric($plugins))
		{
			$query->where($db->quoteName('process_plugins') . ' = :process_plugins')
				->bind(':process_plugins', $plugins);
		}

		// Access filter
		$access = $this->getState('filter.access');

		if (is_numeric($access))
		{
			$query->where($db->quoteName('access') . ' = :access')
				->bind(':access', $access);
		}
		elseif (is_array($access))
		{
			$access = ArrayHelper::toInteger($access);
			$query->whereIn($db->quoteName('access'), $access);
		}

		// Language filter
		$language = $this->getState('filter.language');

		if (!empty($language))
		{
			$query->where($db->quoteName('language') . ' = :language')
				->bind(':language', $language);
		}

		// List ordering clause
		$orderCol  = $this->state->get('list.ordering', 'docimport_category_id');
		$orderDirn = $this->state->get('list.direction', 'ASC');
		$ordering  = $db->escape($orderCol) . ' ' . $db->escape($orderDirn);

		$query->order($ordering);


		return $query;
	}

	protected function _getList($query, $limitstart = 0, $limit = 0)
	{
		if (\is_string($query))
		{
			$query = $this->getDbo()->getQuery(true)->setQuery($query);
		}

		$query->setLimit($limit, $limitstart);
		$this->getDbo()->setQuery($query);

		return array_map(function ($data) {
			$data->status = CategoryTable::getStatusFor($data);

			return $data;
		}, $this->getDbo()->loadObjectList() ?? []);
	}

}