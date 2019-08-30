<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\Helper;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Joomla\CMS\Menu\AbstractMenu;
use Joomla\Registry\Registry;

/**
 * Routing helper
 *
 * Used by the component's SEF router.
 */
abstract class Routing
{
	/**
	 * Menu items pointing to DocImport
	 *
	 * @var  array
	 */
	private static $docImportMenus = null;

	/**
	 * DocImport menu item IDs by type
	 *
	 * @var  array
	 */
	private static $docImportMenuIDByType = [
		'categories' => [],
	    'category'   => [],
	    'article'    => [],
	    'search'     => [],
	];

	/**
	 * Caches the slugs of categories by category
	 *
	 * @var   array
	 */
	private static $categorySlugs = null;

	/**
	 * Caches the slugs of articles by article ID
	 *
	 * @var   array
	 */
	private static $articleSlugs = null;

	/**
	 * Caches the category ID per article ID
	 *
	 * @var   array
	 */
	private static $articleToCategory = null;

	/**
	 * Get all menu items pointing to DocImport
	 *
	 * @return   \stdClass[]
	 */
	public static function &getDocImportMenuItems()
	{
		if (is_null(self::$docImportMenus))
		{
			// Initialize
			self::$docImportMenus = [];
			self::$docImportMenuIDByType = [
				'categories' => [],
				'category'   => [],
				'article'    => [],
				'search'     => [],
			];

			// Get all of the site's menu items
			$allMenuItems = AbstractMenu::getInstance('site')->getItems([], [], false);

			// Loop menu items to find the DocImport ones
			foreach ($allMenuItems as $item)
			{
				// Rule out the obvious items without a component query
				if (!isset($item->query) || !is_array($item->query) || empty($item->query))
				{
					continue;
				}

				// Make sure the option in the URL query is com_docimport
				/** @var  array  $query */
				$query = $item->query;

				if (!isset($query['option']))
				{
					continue;
				}

				if ($query['option'] != 'com_docimport')
				{
					continue;
				}

				// Add the menu item to the list
				self::$docImportMenus[$item->id] = $item;

				// Sort the menu item to one of the by-type lists
				$view = isset($query['view']) ? strtolower($query['view']) : 'categories';
				$view = empty($view) ? 'categories' : $view;

				switch ($view)
				{
					case 'categories':
						self::$docImportMenuIDByType['categories'][] = $item->id;
						break;

					case 'category':
					case 'articles':
						self::$docImportMenuIDByType['category'][] = $item->id;
						break;

					case 'article':
						self::$docImportMenuIDByType['article'][] = $item->id;
						break;

					case 'search':
						self::$docImportMenuIDByType['search'][] = $item->id;
						break;
				}
			}
		}

		return self::$docImportMenus;
	}

	/**
	 * @param   string  $type  The menu type: categories, category, article, search
	 *
	 * @return  \stdClass[]
	 */
	public static function getDocImportMenuItemsByType($type)
	{
		$items    = [];
		$allItems = self::getDocImportMenuItems();

		if (empty($allItems))
		{
			return $items;
		}

		$type = strtolower($type);
		$type = in_array($type, ['categories', 'category', 'article', 'search']) ? $type : 'categories';
		$ids  = self::$docImportMenuIDByType[$type];

		if (empty($ids))
		{
			return $items;
		}

		foreach ($ids as $id)
		{
			if (!isset($allItems[$id]))
			{
				continue;
			}

			$items[$id] = $allItems[$id];
		}

		return $items;
	}

	/**
	 * Find a menu item which points exactly to this category. If none is found null is returned.
	 *
	 * @param   int  $categoryID
	 *
	 * @return  \stdClass
	 */
	public static function findExactCategoryMenu($categoryID)
	{
		$catItems = self::getDocImportMenuItemsByType('category');

		if (!empty($catItems))
		{
			foreach ($catItems as $item)
			{
				$catId = 0;

				// DocImport 2.x menu items have the category ID as a query parameter (normally set as catid)
				if (isset($item->query) && is_array($item->query))
				{
					$catId = isset($item->query['catid']) ? $item->query['catid'] : null;

					if (empty($catId) && isset($item->query['id']))
					{
						$catId = $item->query['id'];
					}
				}

				// Legacy DocImport 1.x menu items have the category ID as a menu item parameter
				if (empty($catId) && ($item->params instanceof Registry))
				{
					$catId = $item->params->get('catid', null);
				}

				// No category ID? WTF!
				if (empty($catId))
				{
					continue;
				}

				// No match?
				if ($catId != $categoryID)
				{
					continue;
				}

				return $item;
			}
		}

		return null;
	}

	/**
	 * Find a menu item which points exactly to this item. If none is found null is returned.
	 *
	 * @param   int  $articleID
	 *
	 * @return  \stdClass
	 */
	public static function findExactArticleMenu($articleID)
	{
		$articleItems = self::getDocImportMenuItemsByType('article');

		if (!empty($articleItems))
		{
			foreach ($articleItems as $item)
			{
				$aID = 0;

				// DocImport 2.x menu items have the category ID as a query parameter (normally set as catid)
				if (isset($item->query) && is_array($item->query))
				{
					$aID = isset($item->query['id']) ? $item->query['id'] : null;
				}

				// Legacy DocImport 1.x menu items have the category ID as a menu item parameter
				if (empty($aID) && ($item->params instanceof Registry))
				{
					$aID = $item->params->get('id', null);
				}

				// No article ID? WTF!
				if (empty($aID))
				{
					continue;
				}

				// No match?
				if ($aID != $articleID)
				{
					continue;
				}

				return $item;
			}
		}

		return null;
	}

	/**
	 * Get information about a Joomla! ItemID (as long as it's a DocImport menu item). If the returned type is empty
	 * then the menu item ID is invalid (it no longer exists or is not a DocImport menu item).
	 *
	 * @param   int  $itemID  The Joomla! menu item ID
	 *
	 * @return  array  'type': categories, category, article, search. 'id': the category or article ID for category and
	 *                 article menu types respectively. Other types have a null id for obvious reasons.
	 */
	public static function getMenuItemInfo($itemID)
	{
		$ret = [
			'type' => '',
			'id'   => null,
		];

		self::getDocImportMenuItems();

		if (!isset(self::$docImportMenus[$itemID]))
		{
			return $ret;
		}

		foreach (self::$docImportMenuIDByType as $type => $ids)
		{
			if (!in_array($itemID, $ids))
			{
				continue;
			}

			$ret['type'] = $type;
			$item        = self::$docImportMenus[$itemID];

			switch ($type)
			{
				case 'category':
					$catId = 0;

					// DocImport 2.x menu items have the category ID as a query parameter (normally set as catid)
					if (isset($item->query) && is_array($item->query))
					{
						$catId = isset($item->query['catid']) ? $item->query['catid'] : null;

						if (empty($catId) && isset($item->query['id']))
						{
							$catId = $item->query['id'];
						}
					}

					// Legacy DocImport 1.x menu items have the category ID as a menu item parameter
					if (empty($catId) && ($item->params instanceof Registry))
					{
						$catId = $item->params->get('catid', null);
					}

					$ret['id'] = empty($catId) ? null : $catId;
					break;

				case 'article':
					$aID = 0;

					// DocImport 2.x menu items have the category ID as a query parameter (normally set as catid)
					if (isset($item->query) && is_array($item->query))
					{
						$aID = isset($item->query['id']) ? $item->query['id'] : null;
					}

					// Legacy DocImport 1.x menu items have the category ID as a menu item parameter
					if (empty($aID) && ($item->params instanceof Registry))
					{
						$aId = $item->params->get('id', null);
					}

					$ret['id'] = empty($aId) ? null : $aId;
					break;
			}

			break;
		}

		return $ret;
	}

	/**
	 * Pre-conditions the SEF URL segments reported by Joomla!.
	 *
	 * Joomla! has the bad habit of converting the first dash in a URL segment to a colon. That's because Joomla! uses
	 * the internal convention of ID:slug in its SEF URLs. Some other times the segment would be passed as an array but
	 * I think that's probably to do with third party SEF extensions...
	 *
	 * This method converts all segment slugs in the expected format, i.e. from foo:bar-baz to foo-bar-baz.
	 *
	 * @param   array  $segments  The segments to precondition
	 *
	 * @return  array  The preconditioned segments.
	 */
	public static function preconditionSegments($segments)
	{
		$newSegments = [];

		if (empty($segments))
		{
			return $newSegments;
		}

		foreach ($segments as $segment)
		{
			if (is_array($segment))
			{
				$segment = implode('-', $segment);
			}

			$segment = str_replace(':', '-', $segment);

			$newSegments[] = $segment;
		}

		return $newSegments;
	}

	/**
	 * Get the value of a hash array's key and remove taht key from the array at the same time. If the key doesn't exist
	 * the default value is returned.
	 *
	 * @param   array   $query    The hash array containing the values.
	 * @param   string  $key      The key name you want to retrieve and remove from the array.
	 * @param   mixed   $default  The default value returned if the key doesn't exist in the array.
	 *
	 * @return  mixed
	 */
	public static function getAndPop(&$query, $key, $default = null)
	{
		if (!isset($query[$key]))
		{
			return $default;
		}

		$value = $query[$key];
		unset($query[$key]);

		return $value;
	}

	/**
	 * Returns the slug for the specified category ID
	 *
	 * @param   int  $categoryID  The category ID
	 *
	 * @return  string|null  The category slug or null if it's not found
	 */
	public static function getCategorySlug($categoryID)
	{
		self::initCategoryAndArticleCaches();

		if (isset(self::$categorySlugs[$categoryID]))
		{
			return self::$categorySlugs[$categoryID];
		}

		return null;
	}

	/**
	 * Returns the slug for the specified article ID
	 *
	 * @param   int  $articleID  The article ID
	 *
	 * @return  string|null  The article slug or null if it's not found
	 */
	public static function getArticleSlug($articleID)
	{
		self::initCategoryAndArticleCaches();

		if (isset(self::$articleSlugs[$articleID]))
		{
			return self::$articleSlugs[$articleID];
		}

		return null;
	}

	/**
	 * Returns the _category_ ID for the specified article ID
	 *
	 * @param   int  $articleID  The article ID
	 *
	 * @return  int|null  The category ID or null if it's not found
	 */
	public static function getArticleCategoryId($articleID)
	{
		self::initCategoryAndArticleCaches();

		if (isset(self::$articleToCategory[$articleID]))
		{
			return self::$articleToCategory[$articleID];
		}

		return null;
	}

	/**
	 * Returns the _category_ slug for the specified article ID
	 *
	 * @param   int  $articleID  The article ID
	 *
	 * @return  string|null  The _category_ slug or null if it's not found
	 */
	public static function getArticleCategorySlug($articleID)
	{
		$catId = self::getArticleCategoryId($articleID);

		if (empty($catId))
		{
			return '';
		}

		return self::getCategorySlug($catId);
	}

	public static function getCategoryFromSlug($slug)
	{
		self::initCategoryAndArticleCaches();

		if (empty(self::$categorySlugs))
		{
			return null;
		}

		$id = array_search($slug, self::$categorySlugs);

		return empty($id) ? null : $id;
	}

	public static function getArticleFromSlug($categoryId, $slug)
	{
		self::initCategoryAndArticleCaches();

		if (empty(self::$articleSlugs))
		{
			return null;
		}

		foreach (self::$articleToCategory as $aid => $cid)
		{
			if ($cid != $categoryId)
			{
				continue;
			}

			if (self::$articleSlugs[$aid] == $slug)
			{
				return $aid;
			}
		}

		return null;
	}

	/**
	 * Initialize the article and category caches
	 *
	 * @return  void
	 */
	private static function initCategoryAndArticleCaches()
	{
		if (!is_null(self::$categorySlugs))
		{
			return;
		}

		self::$categorySlugs     = [];
		self::$articleSlugs      = [];
		self::$articleToCategory = [];

		$db = \JFactory::getDbo();
		$query = $db->getQuery(true)
		            ->select(array(
			            $db->qn('docimport_category_id') . ' AS ' . $db->qn('id'),
			            $db->qn('slug')
		            ))->from($db->qn('#__docimport_categories'));
		self::$categorySlugs = $db->setQuery($query)->loadAssocList('id', 'slug');

		$query = $db->getQuery(true)
		            ->select(array(
			            $db->qn('docimport_article_id') . ' AS ' . $db->qn('id'),
			            $db->qn('slug'),
			            $db->qn('docimport_category_id') . ' AS ' . $db->qn('catid')
		            ))->from($db->qn('#__docimport_articles'));
		$cache = $db->setQuery($query)->loadAssocList();

		if (!empty($cache))
		{
			foreach ($cache as $item)
			{
				self::$articleToCategory[$item['id']] = $item['catid'];
				self::$articleSlugs[$item['id']] = $item['slug'];
			}
		}
	}
}
