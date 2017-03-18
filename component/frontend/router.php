<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2017 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php'))
{
	throw new RuntimeException('FOF 3.0 is not installed', 500);
}

function docimportBuildRoute(&$query)
{
	$segments = array();

	//If there is only the option and Itemid, let Joomla! decide on the naming scheme
	if (isset($query['option']) && isset($query['Itemid']) &&
	    !isset($query['view']) && !isset($query['task']) &&
	    !isset($query['layout']) && !isset($query['id'])
	)
	{
		return $segments;
	}

	// Load the site's menus
	$menus = JMenu::getInstance('site');

	// Get some interesting variables
	$view        = DocimportRouterHelper::getAndPop($query, 'view', 'Categories');
	$task        = DocimportRouterHelper::getAndPop($query, 'task', 'browse');
	$id          = DocimportRouterHelper::getAndPop($query, 'id');
	$queryItemid = DocimportRouterHelper::getAndPop($query, 'Itemid');

	$catSlugs = DocimportRouterHelper::getCategorySlugs();

	// Fix the View/Task variables
	switch ($view)
	{
		case 'category':
		case 'Category':
			if (($task == 'browse') && !empty($id))
			{
				$task = 'read';
			}
			elseif (empty($id))
			{
				$view = 'Categories';
				$task = 'browse';
			}
			break;

		case 'Categories':
		case 'categories':
			$task = 'browse';
			break;

		case 'article':
		case 'Article':
			if (empty($id))
			{
				$view = 'Categories';
				$task = 'browse';
			}
			else
			{
				$task = 'read';
			}
			break;

		case 'search':
		case 'Search':
			$view = 'Search';
			$task = 'main';
			break;

		default:
			$view = 'Categories';
			$task = 'browse';
			break;
	}

	$qoptions = array('view' => $view, 'id' => $id, 'option' => 'com_docimport');

	switch ($view)
	{
		case 'Categories':
		case 'categories':
			// Find a suitable Itemid
			$menu   = DocimportRouterHelper::findMenu($qoptions);
			$Itemid = empty($menu) ? null : $menu->id;

			if (empty($Itemid))
			{
				$qoptions['view'] = strtolower($qoptions['view']);
				$menu   = DocimportRouterHelper::findMenu($qoptions);
				$Itemid = empty($menu) ? null : $menu->id;
			}

			if (!empty($Itemid))
			{
				// Joomla! will let the menu item naming work its magic
				$query['Itemid'] = $Itemid;
			}
			else
			{
				if ($queryItemid)
				{
					$menu  = $menus->getItem($queryItemid);
					$mView = isset($menu->query['view']) ? $menu->query['view'] : 'Categories';
					// No, we have to find another root
					if (!in_array($menu, ['categories', 'Categories']))
					{
						$Itemid = null;
					}
				}
			}

			break;

		case 'Search':
		case 'search':
			// Find a suitable Itemid
			$menu   = DocimportRouterHelper::findMenu($qoptions);
			$Itemid = empty($menu) ? null : $menu->id;

			if (empty($Itemid))
			{
				$qoptions['view'] = strtolower($qoptions['view']);
				$menu   = DocimportRouterHelper::findMenu($qoptions);
				$Itemid = empty($menu) ? null : $menu->id;
			}

			if (!empty($Itemid))
			{
				// Joomla! will let the menu item naming work its magic
				$query['Itemid'] = $Itemid;
			}
			else
			{
				if (!empty($Itemid))
				{
					// Push the Itemid and category alias
					$query['Itemid'] = $Itemid;
				}
				else
				{
					// Push the browser layout and category alias
					$segments[] = 'Search';
				}
			}

			if (empty($Itemid) && !empty($queryItemid))
			{
				$menu  = $menus->getItem($queryItemid);
				$mView = isset($menu->query['view']) ? $menu->query['view'] : 'Search';
				// No, we have to find another root
				if (!in_array($menu, ['search', 'Search']))
				{
					$Itemid = null;
				}
			}

			break;

		case 'category':
		case 'Category':
			// Get category slug
			$slug = array_key_exists($id, $catSlugs) ? $catSlugs[$id] : '';

			// Try to find a menu item for this category
			$options = $qoptions;
			unset($options['id']);
			$params = array('catid' => $id);
			$menu   = DocimportRouterHelper::findMenu($options, $params);
			$Itemid = empty($menu) ? null : $menu->id;

			if (empty($itemId))
			{
				$options['view'] = strtolower($options['view']);
				$menu   = DocimportRouterHelper::findMenu($options, $params);
				$Itemid = empty($menu) ? null : $menu->id;
			}

			if (!empty($Itemid))
			{
				// A category menu item found, use it
				$query['Itemid'] = $Itemid;
			}
			else
			{
				// Not found. Try fetching a browser menu item
				$options = array('view' => 'Categories', 'option' => 'com_docimport');
				$menu    = DocimportRouterHelper::findMenu($options);
				$Itemid  = empty($menu) ? null : $menu->id;

				// Legacy menu item search
				if (empty($Itemid))
				{
					$options = array('view' => 'categories', 'option' => 'com_docimport');
					$menu    = DocimportRouterHelper::findMenu($options);
					$Itemid  = empty($menu) ? null : $menu->id;
				}

				if (!empty($Itemid))
				{
					// Push the Itemid and category alias
					$query['Itemid'] = $menu->id;
					$segments[]      = $slug;
				}
				else
				{
					// Push the browser layout and category alias
					$segments[] = 'Categories';
					$segments[] = $slug;
				}
			}


			// Do we have a category menu?
			if (empty($Itemid) && !empty($queryItemid))
			{
				$itemId = $queryItemid;
				$menu   = $menus->getItem($Itemid);
				$mView  = isset($menu->query['view']) ? $menu->query['view'] : 'Categories';
				// No, we have to find another root
				if (($mView == 'category') || ($mView == 'Category'))
				{
					$params = ($menu->params instanceof JRegistry) ? $menu->params : $menus->getParams($Itemid);
					if ($params->get('catid', 0) == $id)
					{
						$query['Itemid'] = $Itemid;
					}
				}
			}
			break;

		case 'article':
		case 'Article':
			// Get article info
			$articleSlugs = DocimportRouterHelper::getArticleSlugs();

			$catId = 0;
			$articleSlug = '';

			if (isset($articleSlugs[$id]))
			{
				$article = $articleSlugs[$id];
				$catId = $article['catid'];
				$articleSlug = $article['slug'];
			}

			// Get slug
			$slug = array_key_exists($catId, $catSlugs) ? $catSlugs[$catId] : '';

			// Try to find a category menu item
			$options = array('view' => 'Category', 'option' => 'com_docimport');
			$params  = array('catid' => $catId);
			$menu    = DocimportRouterHelper::findMenu($options, $params);
			$Itemid  = null;

			if (empty($menu))
			{
				$options['view'] = strtolower($options['view']);
				$menu    = DocimportRouterHelper::findMenu($options, $params);
				$Itemid  = null;
			}

			if (!empty($menu))
			{
				// Found it! Just append the article slug
				$Itemid          = $menu->id;
				$query['Itemid'] = $menu->id;
				$segments[]      = $articleSlug;
			}
			else
			{
				// Nah. Let's find a categories menu item.
				$options = array('view' => 'Categories', 'option' => 'com_docimport');
				$menu    = DocimportRouterHelper::findMenu($options);

				if (empty($menu))
				{
					$options = array('view' => 'categories', 'option' => 'com_docimport');
					$menu    = DocimportRouterHelper::findMenu($options);
				}

				if (!empty($menu))
				{
					// We must add the category and article slug.
					$Itemid          = $menu->id;
					$query['Itemid'] = $menu->id;
					$segments[]      = $slug;
					$segments[]      = $articleSlug;
				}
				else
				{
					// I must add the full path
					$segments[] = 'Categories';
					$segments[] = $slug;
					$segments[] = $articleSlug;
				}
			}

			// Do we have a "category" menu?
			if (!$Itemid && $queryItemid)
			{
				$Itemid = $queryItemid;
				$menu   = $menus->getItem($Itemid);
				$mView  = isset($menu->query['view']) ? $menu->query['view'] : 'Categories';

				if (in_array($mView, ['categories', 'Categories']))
				{
					// No. It is a categories menu item. We must add the category and article slug.
					$query['Itemid'] = $Itemid;
				}
				if (in_array($mView, ['category', 'Category']))
				{
					// Yes! Is it the category we want?
					$params = ($menu->params instanceof JRegistry) ? $menu->params : $menus->getParams($Itemid);
					if ($params->get('catid', 0) == $catId)
					{
						// Cool! Just append the article slug
						$query['Itemid'] = $Itemid;
					}
				}
			}

			break;
	}

	return $segments;
}

function docimportParseRoute(&$segments)
{
	$segments = DocimportRouterHelper::preconditionSegments($segments);

	$query = array();
	$menus = JMenu::getInstance('site');
	$menu  = $menus->getActive();

	if (is_null($menu))
	{
		// No menu. The segments are categories/category_slug/article_slug
		switch (count($segments))
		{
			case 1:
				// Categories or Search view
				$query['view'] = 'Categories';
				$slug = array_pop($segments); // Remove the "categories" thingy

				if (strtolower($slug) == 'search')
				{
					$query['view'] = 'Search';
				}

				break;

			case 2:
				// Category view
				$query['view'] = 'Category';
				$slug          = array_pop($segments);
				array_pop($segments); // Remove the "categories" thingy

				// Load the category
				/** @var \Akeeba\DocImport\Site\Model\Categories $category */
				$category = FOF30\Container\Container::getInstance('com_docimport')->factory->model('Categories')->tmpInstance();
				$category
					->slug($slug)
					->removeBehaviour('Enabled')
					->firstOrNew();

				if (empty($category))
				{
					$query['view'] = 'Categories';
				}
				else
				{
					$query['id'] = $category->docimport_category_id;
				}
				break;

			case 3:
				// Article view
				$query['view'] = 'Article';
				$slug_article  = array_pop($segments);
				$slug_category = array_pop($segments);
				array_pop($segments); // Remove the "Categories" thingy

				// Load the category
				/** @var \Akeeba\DocImport\Site\Model\Categories $category */
				$category = FOF30\Container\Container::getInstance('com_docimport')->factory->model('Categories')->tmpInstance();
				$category
					->slug($slug_category)
					->removeBehaviour('Enabled')
					->firstOrNew();

				// Load the article
				/** @var \Akeeba\DocImport\Site\Model\Articles $article */
				$article = FOF30\Container\Container::getInstance('com_docimport')->factory->model('Articles')->tmpInstance();
				$article
					->category($category->docimport_category_id)
				    ->slug($slug_article)
					->removeBehaviour('Enabled')
				    ->firstOrNew();

				if (empty($article->docimport_article_id))
				{
					$query['view'] = 'Categories';
				}
				else
				{
					$query['id'] = $article->docimport_article_id;
				}

				break;
		}

		return $query;
	}

	// A menu item is defined
	$view          = $menu->query['view'];
	$slug_article  = null;
	$slug_category = null;

	/** @var JRegistry $menuparams */
	$menuparams = $menu->params;
	$catid = $menuparams->get('catid', null);

	if (($view == 'Categories') || ($view == 'categories'))
	{
		switch (count($segments))
		{
			case 1:
				// Category view
				$query['view'] = 'Category';
				$view          = 'Category';
				$slug_category = array_pop($segments);
				break;

			case 2:
				// Article view
				$query['view'] = 'Article';
				$view          = 'Article';
				$slug_article  = array_pop($segments);
				$slug_category = array_pop($segments);
				break;
		}
	}
	elseif (($view == 'Search') || ($view == 'search'))
	{
		$query['view'] = 'Search';
		$view          = 'Search';
	}
	elseif (empty($view) || count($segments))
	{
		switch (count($segments))
		{
			case 0:
				// Category view
				$query['view'] = 'Category';
				$view          = 'Category';
				break;

			case 1:
				// Article view
				$query['view'] = 'Article';
				$view          = 'Article';
				$slug_article  = array_pop($segments);
				break;
		}
	}
	else
	{
		$query['view'] = 'Article';
	}

	if (!is_null($slug_category))
	{
		/** @var \Akeeba\DocImport\Site\Model\Categories $categoriesModel */
		$categoriesModel = FOF30\Container\Container::getInstance('com_docimport')->factory->model('Categories')->tmpInstance();
		$category        = $categoriesModel
			->slug($slug_category)
			->removeBehaviour('Enabled')
			->firstOrNew();
		$catid = $category->getId();
	}

	if (!is_null($slug_article))
	{
		// Load the article

		/** @var \Akeeba\DocImport\Site\Model\Articles $articlesModel */
		$articlesModel = FOF30\Container\Container::getInstance('com_docimport')->factory->model('Articles')->tmpInstance();
		$article       = $articlesModel
			->category((int) $catid)
			->slug($slug_article)
			->removeBehaviour('Enabled')
			->firstOrNew();

		if (empty($article->docimport_article_id))
		{
			$query['view'] = 'Category';
			$query['id']   = $catid;
		}
		else
		{
			$query['id'] = $article->docimport_article_id;
		}
	}
	elseif (!in_array($view, ['categories', 'Categories', 'search', 'Search']))
	{
		// Load the category
		/** @var \Akeeba\DocImport\Site\Model\Categories $categoriesModel */
		$categoriesModel = FOF30\Container\Container::getInstance('com_docimport')->factory->model('Categories')->tmpInstance();
		$category        = $categoriesModel->find($catid);

		if (empty($category->docimport_category_id))
		{
			$query['view'] = 'Categories';
		}
		else
		{
			$query['view'] = 'Category';
			$query['id']   = $catid;
		}
	}
	elseif (in_array($view, ['search', 'Search']))
	{
		$query['view'] = 'Search';
	}
	else
	{
		$query['view'] = 'Categories';
	}

	return $query;
}

class DocimportRouterHelper
{
	static function getCategorySlugs()
	{
		static $cache = null;

		if (is_null($cache))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select(array(
					$db->qn('docimport_category_id') . ' AS ' . $db->qn('id'),
					$db->qn('slug')
				))->from($db->qn('#__docimport_categories'));
			$cache = $db->setQuery($query)->loadAssocList('id', 'slug');
		}

		return $cache;
	}

	static function getArticleSlugs()
	{
		static $cache = null;

		if (is_null($cache))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select(array(
					$db->qn('docimport_article_id') . ' AS ' . $db->qn('id'),
					$db->qn('slug'),
					$db->qn('docimport_category_id') . ' AS ' . $db->qn('catid')
				))->from($db->qn('#__docimport_articles'));
			$cache = $db->setQuery($query)->loadAssocList('id');
		}

		return $cache;
	}

	static function getAndPop(&$query, $key, $default = null)
	{
		if (isset($query[ $key ]))
		{
			$value = $query[ $key ];
			unset($query[ $key ]);

			return $value;
		}
		else
		{
			return $default;
		}
	}

	/**
	 * Finds a menu whose query parameters match those in $qoptions
	 *
	 * @param array $qoptions The query parameters to look for
	 * @param array $params   The menu parameters to look for
	 *
	 * @return null|object Null if not found, or the menu item if we did find it
	 */
	static public function findMenu($qoptions = array(), $params = null)
	{
		// Convert $qoptions to an object
		if (empty($qoptions) || !is_array($qoptions))
		{
			$qoptions = array();
		}

		$menus    = JMenu::getInstance('site');
		$menuitem = $menus->getActive();

		// First check the current menu item (fastest shortcut!)
		if (is_object($menuitem))
		{
			if (self::checkMenu($menuitem, $qoptions, $params))
			{
				return $menuitem;
			}
		}

		foreach ($menus->getMenu() as $item)
		{
			if (self::checkMenu($item, $qoptions, $params))
			{
				return $item;
			}
		}

		return null;
	}

	/**
	 * Checks if a menu item conforms to the query options and parameters specified
	 *
	 * @param object $menu     A menu item
	 * @param array  $qoptions The query options to look for
	 * @param array  $params   The menu parameters to look for
	 *
	 * @return bool
	 */
	static public function checkMenu($menu, $qoptions, $params = null)
	{
		$query = $menu->query;
		foreach ($qoptions as $key => $value)
		{
			if (is_null($value))
			{
				continue;
			}
			if (!isset($query[ $key ]))
			{
				return false;
			}
			if ($query[ $key ] != $value)
			{
				return false;
			}
		}

		if (!is_null($params))
		{
			$menus = JMenu::getInstance('site');
			$check = $menu->params instanceof JRegistry ? $menu->params : $menus->getParams($menu->id);

			foreach ($params as $key => $value)
			{
				if (is_null($value))
				{
					continue;
				}
				if ($check->get($key) != $value)
				{
					return false;
				}
			}
		}

		return true;
	}

	static public function preconditionSegments($segments)
	{
		$newSegments = array();
		if (!empty($segments))
		{
			foreach ($segments as $segment)
			{
				if (strstr($segment, ':'))
				{
					$segment = str_replace(':', '-', $segment);
				}
				if (is_array($segment))
				{
					$newSegments[] = implode('-', $segment);
				}
				else
				{
					$newSegments[] = $segment;
				}
			}
		}

		return $newSegments;
	}
}