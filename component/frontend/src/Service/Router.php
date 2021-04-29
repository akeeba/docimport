<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\DocImport\Site\Service;

defined('_JEXEC') || die;

use Akeeba\Component\DocImport\Administrator\Table\ArticleTable;
use Akeeba\Component\DocImport\Administrator\Table\CategoryTable;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\Menu\MenuItem;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryAwareTrait;
use Joomla\CMS\MVC\Model\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

class Router extends RouterView
{
	use MVCFactoryAwareTrait;
	use DatabaseAwareTrait;

	private static $articleToCategory;

	/**
	 * The db
	 *
	 * @var DatabaseInterface
	 *
	 * @since  4.0.0
	 */
	private $db;

	public function __construct(SiteApplication $app = null, AbstractMenu $menu = null, DatabaseInterface $db, MVCFactory $factory)
	{
		$this->setDbo($db);
		$this->setMVCFactory($factory);

		$search = new RouterViewConfiguration('search');
		$this->registerView($search);

		$categories = new RouterViewConfiguration('categories');
		$this->registerView($categories);

		$category = new RouterViewConfiguration('category');
		$category->setKey('id');
		$category->setParent($categories);
		$this->registerView($category);

		$article = new RouterViewConfiguration('article');
		$article->setKey('id')
			->setParent($category, 'catid');
		$this->registerView($article);

		parent::__construct($app, $menu);

		$this->attachRule(new MenuRules($this));
		$this->attachRule(new StandardRules($this));
		$this->attachRule(new NomenuRules($this));
	}

	public function build(&$query)
	{
		// Don't let the controller be set in a SEF URL.
		if (isset($query['controller']))
		{
			unset ($query['controller']);
		}

		// Support viewName.taskName tasks.
		if (isset($query['task']) && (strpos($query['task'], '.') !== false))
		{
			[$view, $task] = $query['task'];

			$query['view'] = $view;
			$query['task'] = $task;
		}

		// Lowercase the view; addresses Formal case views in the previous versions.
		if (isset($query['view']))
		{
			$query['view'] = strtolower($query['view']);
		}

		// For the purposes of the router, the "articles" view is parsed as "category"
		if (isset($query['view']) && ($query['view'] === 'articles'))
		{
			$query['view'] = 'category';
		}

		// Lowercase the menu item's view, if defined; addresses Formal case views in the previous versions.
		$item = $this->menu->getItem($query['Itemid'] ?? null) ?: null;
		$this->migrateMenuItem($item);

		// Article view without catid
		if ((($query['view'] ?? '') === 'article') && !empty($query['id'] ?? '') && empty($query['catid'] ?? ''))
		{
			$query['catid'] = $this->getArticleCategoryId($query['id']);
		}

		return parent::build($query);
	}

	public function parse(&$segments)
	{
		// Lowercase the active menu item's view, if defined; addresses Formal case views in the previous versions.
		$active = $this->menu->getActive() ?: null;
		$this->migrateMenuItem($active);

		$query = parent::parse($segments);

		if (isset($query['view']))
		{
			$query['view'] = strtolower($query['view']);
		}

		if (($query['view'] ?? '') === 'category')
		{
			$query['view'] = 'articles';
		}

		return $query;
	}

	public function getCategorySegment($id, $query)
	{
		/** @var CategoryTable $category */
		$category = $this->getMVCFactory()->createTable('Category', 'Administrator');

		if (!$category->load($id))
		{
			return [];
		}

		return [$category->slug];
	}

	public function getCategoriesSegment($id, $query)
	{
		return $this->getCategorySegment($id, $query);
	}

	public function getArticleSegment($id, $query)
	{
		$id = (int) $id;

		/** @var ArticleTable $article */
		$article = $this->getMVCFactory()->createTable('Article', 'Administrator');

		if (!$article->load($id))
		{
			return [];
		}

		return [$id => $article->slug];
	}

	public function getCategoryId($segment, $query)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('docimport_category_id'))
			->from($db->quoteName('#__docimport_categories'))
			->where($db->quoteName('slug') . ' = :slug')
			->bind(':slug', $segment);

		return $db->setQuery($query)->loadResult() ?: false;
	}

	public function getCategoriesId($segment, $query)
	{
		return $this->getCategoryId($segment, $query);
	}

	public function getArticleId($segment, $query)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('docimport_article_id'))
			->from($db->quoteName('#__docimport_articles'))
			->where($db->quoteName('slug') . ' = :slug')
			->where($db->quoteName('docimport_category_id') . ' = :catid')
			->bind(':slug', $segment)
			->bind(':catid', $query['id'], ParameterType::INTEGER);

		return $db->setQuery($query)->loadResult() ?: false;
	}

	/**
	 * Backwards compatibility for older versions of the component.
	 *
	 * 1. Older versions had Formal case views (e.g. Categories instead of categories) which cause the Joomla View
	 *    Router to choke and die when building and parsing routes. This fixes that problem.
	 *
	 * 2. Older versions used a 'Category' or 'category' view to display the articles of a category. Due to the way
	 *    the Joomla View Router is meant to be used it makes far more sense to use the view name 'articles' instead.
	 *    This will transparently convert the view name of existing Category/category menu items to articles.
	 *
	 * This method must be used TWICE in a router:
	 * a. Building a route, if there is a detected menu item; and
	 * b. Parsing a route, if there is an active menu item.
	 *
	 * @param   MenuItem|null  $item  The menu item to address or null if there's no menu item.
	 */
	private function migrateMenuItem(?MenuItem $item)
	{
		if (
			empty($item)
			|| ($item->component !== 'com_' . $this->getName())
			|| empty($item->query['view'] ?? '')
		)
		{
			return;
		}

		// Lowercase the view name
		$item->query['view'] = strtolower($item->query['view']);

		// For the purposes of the router, the "articles" view is parsed as "category"
		if ($item->query['view'] === 'articles')
		{
			$item->query['view'] = 'category';
		}

		// Migration: "Category" view used to set catid instead of id
		if ($item->query['view'] === 'category')
		{
			$item->query['id'] = $item->query['id'] ?? ($item->query['catid'] ?? 0);
		}
	}

	private function getArticleCategoryId($id): ?int
	{
		if (empty(self::$articleToCategory))
		{
			self::$articleToCategory = $this->getArticleToCategoryMap();
		}

		return self::$articleToCategory[$id] ?? null;
	}

	private function getArticleToCategoryMap(): array
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select([
				$db->quoteName('docimport_article_id', 'id'),
				$db->quoteName('docimport_category_id', 'catid'),
			]);

		return $db->setQuery($query)->loadAssocList('id', 'catid') ?? [];
	}
}