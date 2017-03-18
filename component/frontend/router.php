<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2017 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\DocImport\Site\Helper\Routing;

// Make sure FOF 3 can be loader
if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php'))
{
	return;
}

function docimportBuildRoute(&$query)
{
	// Initialize the segments
	$segments = [];

	// Get some basic query string parameters
	$option = Routing::getAndPop($query, 'option', 'com_docimport');

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
			$cSlug               = Routing::getArticleCategorySlug($aId);
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

		case 'search':
			// Do I have a menu item pointing to Search?
			if ($itemID)
			{
				$info = Routing::getMenuItemInfo($itemID);

				if (($info['type'] == 'search'))
				{
					$query = [
						'option' => $option,
						'Itemid' => $itemID
					];

					return $segments;
				}
			}

			// No? Get a search menu item
			$searchMenuItems = Routing::getDocImportMenuItemsByType('search');

			if (!empty($searchMenuItems))
			{
				$item  = array_shift($searchMenuItems);

				$query = [
					'option' => $option,
					'Itemid' => $item->id
				];

				return $segments;
			}

			// Nope, there's no routable menu item for this category
			$query = [
				'option' => 'com_docimport',
				'view'   => 'Saerch',
			];

			break;
	}

	return $segments;
}

function docimportParseRoute(&$segments)
{
	die('PARSE ROUTE NOT IMPLEMENTED');
}