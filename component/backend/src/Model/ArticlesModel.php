<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\DocImport\Administrator\Model;

defined('_JEXEC') || die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

class ArticlesModel extends ListModel
{
	public function __construct($config = [], MVCFactoryInterface $factory = null)
	{
		$config['filter_fields'] = $config['filter_fields'] ?: [
			// Used for filtering and/or sorting in the GUI
			'search',
			'enabled',
			'cat_enabled',
			'access',
			'language',
			// Only used for sorting in the GUI
			'ordering',
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
			'search'      => ['string', ''],
			'enabled'     => ['int', ''],
			'cat_enabled' => ['int', ''],
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
				$this->getState('filter.enabled'),
			]);

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select([
				$db->quoteName('a') . '.*',
				$db->quoteName('c.title', 'cat_title'),
				$db->quoteName('c.enabled', 'cat_enabled'),
				$db->quoteName('c.access', 'access'),
				$db->quoteName('c.language', 'language'),
				$db->quoteName('l.title', 'language_title'),
				$db->quoteName('l.image', 'language_image'),
				$db->quoteName('ag.title', 'access_level'),
			])
			->from($db->qn('#__docimport_articles', 'a'))
			->join('LEFT', $db->quoteName('#__docimport_categories', 'c'), $db->quoteName('c.docimport_category_id') . ' = ' . $db->quoteName('a.docimport_category_id'))
			->join('LEFT', $db->quoteName('#__viewlevels', 'ag'), $db->quoteName('ag.id') . ' = ' . $db->quoteName('c.access'))
			->join('LEFT', $db->quoteName('#__languages', 'l'), $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('c.language'));

		// Search filter
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$ids = (int) substr($search, 3);
				$query->where($db->quoteName('docimport_article_id') . ' = :id')
					->bind(':id', $ids, ParameterType::INTEGER);
			}
			else
			{
				$search = '%' . $search . '%';
				$query->where(
					'(' .
					$db->qn('title') . ' LIKE :search1 OR ' .
					$db->qn('fulltext') . ' LIKE :search2 OR ' .
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

		// Category enabled filter
		$cat_enabled = $this->getState('filter.cat_enabled');

		if (is_numeric($cat_enabled))
		{
			$query->where($db->quoteName('cat_enabled') . ' = :cat_enabled')
				->bind(':cat_enabled', $cat_enabled);
		}

		// Access filter
		$access = $this->getState('filter.access');

		if (is_numeric($access))
		{
			$query->where($db->quoteName('c.access') . ' = :access')
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
			$query->where($db->quoteName('c.language') . ' = :language')
				->bind(':language', $language);
		}

		// List ordering clause
		$orderCol  = $this->state->get('list.ordering', 'contactus_category_id');
		$orderDirn = $this->state->get('list.direction', 'ASC');
		$ordering  = $db->escape($orderCol) . ' ' . $db->escape($orderDirn);

		$query->order($ordering);


		return $query;
	}

}