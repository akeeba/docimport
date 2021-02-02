<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\DocImport\Site\Helper\Routing;
use FOF40\Container\Container;
use Joomla\CMS\Component\Router\RouterBase;
use Joomla\CMS\Factory;

// Make sure FOF 3 can be loader
if (!defined('FOF40_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof40/include.php'))
{
	return;
}

// Make sure the autoloader is registered for this component
Container::getInstance('com_docimport');

/**
 * Akeeba DocImport router.
 *
 * This used to be separate functions for building and parsing routes. It has been converted to a router class since it
 * is necessary for Joomla! 4. Router classes are supported since Joomla! 3.3, so no lost compatibility there.
 *
 * @since       2.1.2
 */
class DocimportRouter extends RouterBase
{
	/**
	 * Precondition the query.
	 *
	 * This adds an Itemid if one is not already specified.
	 *
	 * @param   array  $query
	 *
	 * @return  array
	 *
	 * @throws  Exception
	 * @since   3.3.1
	 */
	public function preprocess($query)
	{
		// First, try to find a suitable Itemid using our legacy route building method
		$temp = array_merge($query);
		$this->build($temp);

		$itemid = isset($temp['Itemid']) ? $temp['Itemid'] : null;

		// If I've found an Itemid adjust the query
		if ($itemid)
		{
			$query['Itemid'] = $itemid;
		}

		return $query;
	}


	/**
	 * Build method for URLs
	 * This method is meant to transform the query parameters into a more human
	 * readable form. It is only executed when SEF mode is switched on.
	 *
	 * @param array  &$query An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @throws Exception
	 * @since   2.1.2
	 */
	public function build(&$query)
	{
		// Initialize the segments
		$segments = [];

		// Get some basic query string parameters
		$option = Routing::getAndPop($query, 'option', null);

		if ($option != 'com_docimport')
		{
			$query['option'] = $option;

			return $segments;
		}

		$view   = Routing::getAndPop($query, 'view', null);
		$task   = Routing::getAndPop($query, 'task', null);
		$itemID = Routing::getAndPop($query, 'Itemid', null);
		$view   = is_string($view) ? strtolower($view) : $view;
		$task   = is_string($task) ? strtolower($task) : $task;

		// If we are not asked for a specific view AND there is a menu item THEN return no segments; Joomla! already uses the menu item alias.
		if (empty($view) && $itemID)
		{
			$query = [
				'option' => $option,
				'Itemid' => $itemID
			];

			return $segments;
		}

		switch ($view)
		{
			case 'categories':
			default:
				// Do I already have a menu item pointing to Categories?
				if ($itemID)
				{
					$info = Routing::getMenuItemInfo($itemID);

					if ($info['type'] == 'categories')
					{
						$query = [
							'option' => $option,
							'Itemid' => $itemID
						];

						return $segments;
					}
				}

				// Nope. I need a different menu item.
				$searchMenuItems  = Routing::getDocImportMenuItemsByType('categories');

				if (empty($searchMenuItems))
				{
					$query = [
						'option' => $option,
						'view'   => 'Categories',
					];

					return $segments;
				}

				$item = array_shift($searchMenuItems);
				$query = [
					'option' => $option,
					'Itemid' => $item->id
				];

				return $segments;

				break;

			case 'category':
				$catId = Routing::getAndPop($query, 'id', null);
				$catId = Routing::getAndPop($query, 'catid', $catId);

				// Do I have a menu item pointing to this category?
				if ($itemID)
				{
					$info = Routing::getMenuItemInfo($itemID);

					if (($info['type'] == 'category') && ($info['id'] == $catId))
					{
						$query = [
							'option' => $option,
							'Itemid' => $itemID
						];

						return $segments;
					}
				}

				// In any other case I have to first look for a menu item to this category
				$item = Routing::findExactCategoryMenu($catId);

				if (!empty($item))
				{
					$query = [
						'option' => $option,
						'Itemid' => $item->id
					];

					return $segments;
				}

				// Hm, maybe there is a Categories menu item I can build upon?
				$searchMenuItems = Routing::getDocImportMenuItemsByType('categories');

				if (!empty($searchMenuItems))
				{
					$item       = array_shift($searchMenuItems);
					$query      = [
						'option' => $option,
						'Itemid' => $item->id
					];
					$segments[] = Routing::getCategorySlug($catId);

					return $segments;
				}

				// Nope, there's no routable menu item for this category
				$query = [
					'option' => 'com_docimport',
					'view'   => 'Category',
					'id'     => $catId
				];

				break;

			case 'article':
				$aId = Routing::getAndPop($query, 'id', null);

				// Do I have a menu item pointing to this article?
				if ($itemID)
				{
					$info = Routing::getMenuItemInfo($itemID);

					if (($info['type'] == 'article') && ($info['id'] == $aId))
					{
						$query = [
							'option' => $option,
							'Itemid' => $itemID
						];

						return $segments;
					}
				}

				// Look for a menu item to this article
				$item = Routing::findExactArticleMenu($aId);

				if (!empty($item))
				{
					$query = [
						'option' => $option,
						'Itemid' => $item->id
					];

					return $segments;
				}

				// Look for a menu item to the category of the article
				$cId   = Routing::getArticleCategoryId($aId);
				$aSlug = Routing::getArticleSlug($aId);
				$item  = Routing::findExactCategoryMenu($cId);

				if (!empty($item))
				{
					$query = [
						'option' => $option,
						'Itemid' => $item->id
					];

					$segments[] = $aSlug;

					return $segments;
				}

				// Look for a menu item to Categories
				$cSlug           = Routing::getArticleCategorySlug($aId);
				$searchMenuItems = Routing::getDocImportMenuItemsByType('categories');

				if (!empty($searchMenuItems))
				{
					$item  = array_shift($searchMenuItems);
					$query = [
						'option' => $option,
						'Itemid' => $item->id
					];

					$segments[] = $cSlug;
					$segments[] = $aSlug;

					return $segments;
				}

				// Nope, there's no routable menu item for this category
				$query = [
					'option' => 'com_docimport',
					'view'   => 'Article',
					'id'     => $aId
				];
				break;
		}

		return $segments;
	}

	/**
	 * Parse method for URLs
	 * This method is meant to transform the human readable URL back into
	 * query parameters. It is only executed when SEF mode is switched on.
	 *
	 * @param array  &$segments The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @throws  Exception
	 * @since   2.1.2
	 */
	public function parse(&$segments)
	{
		// Initialize
		$query = array();

		// Make sure we have SOMETHING to do.
		if (empty($segments))
		{
			return $query;
		}

		// Prepare the segments and get some basic current menu item information
		$segments    = Routing::preconditionSegments($segments);
		$currentItem = Factory::getApplication()->getMenu()->getActive();
		$Itemid      = (is_object($currentItem) && isset($currentItem->id)) ? $currentItem->id : 0;
		$info        = Routing::getMenuItemInfo($Itemid);

		// We have no idea what this menu item is?
		if (empty($info['type']))
		{
			return $query;
		}

		// Is this a menu item which results in no parsable segments?
		if (in_array($info['type'], ['categories', 'article']))
		{
			$query['view'] = ucfirst($info['type']);

			// This causes legacy menu items to not display anything?
			// return $query;
		}

		$categorySlug = null;
		$articleSlug  = null;

		switch ($info['type'])
		{
			case 'categories':
				// How many slugs do I have?
				if (count($segments) >= 2)
				{
					$query['view'] = 'Article';
					$categorySlug  = array_shift($segments);
					$articleSlug   = array_shift($segments);
				}
				elseif (count($segments) == 1)
				{
					$query['view'] = 'Category';
					$categorySlug  = array_shift($segments);
				}
				else
				{
					$query['view'] = 'Categories';
				}
				break;

			case 'category':
				// How many slugs do I have?
				if (count($segments) >= 1)
				{
					$query['view'] = 'Article';
					$articleSlug   = array_shift($segments);
				}
				else
				{
					$query['view'] = 'Category';
				}

				$query['id'] = $info['id'];
				break;
		}

		// Convert a category slug to an ID
		if (!empty($categorySlug))
		{
			$catId       = Routing::getCategoryFromSlug($categorySlug);
			$query['id'] = $catId;
		}

		// Convert an article slug to an ID
		if (!empty($articleSlug))
		{
			$catId       = Routing::getAndPop($query, 'id', 0);
			$aID         = Routing::getArticleFromSlug($catId, $articleSlug);
			$query['id'] = $aID;
		}

		return $query;
	}
}