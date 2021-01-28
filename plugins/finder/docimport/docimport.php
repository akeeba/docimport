<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

use Joomla\CMS\Router\Router;
use Joomla\CMS\Uri\Uri;

// Load the base adapter.
require_once JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/adapter.php';

/**
 * DocImport Smart Search plugin
 */
class plgFinderDocimport extends FinderIndexerAdapter
{
	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 */
	protected $context = 'Documentation';

	/**
	 * The extension name.
	 *
	 * @var    string
	 */
	protected $extension = 'com_docimport';

	/**
	 * The sub-layout to use when rendering the results.
	 *
	 * @var    string
	 */
	protected $layout = 'article';

	/**
	 * The type of content that the adapter indexes.
	 *
	 * @var    string
	 */
	protected $type_title = 'Documentation';

	/**
	 * The table name.
	 *
	 * @var    string
	 */
	protected $table = '#__docimport_articles';

	/**
	 * The field the published state is stored in.
	 *
	 * @var    string
	 */
	protected $state_field = 'enabled';

	/**
	 * Constructor
	 *
	 * @param   object &$subject The object to observe
	 * @param   array  $config   An array that holds the plugin configuration
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$this->loadLanguage();
	}

	/**
	 * Method to update the item link information when the item category is
	 * changed. This is fired when the item category is published or unpublished
	 * from the list view.
	 *
	 * @param   string  $extension The extension whose category has been updated.
	 * @param   array   $pks       A list of primary key ids of the content that has changed state.
	 * @param   integer $value     The value of the state that the content has been changed to.
	 *
	 * @return  void
	 */
	public function onFinderCategoryChangeState($extension, $pks, $value)
	{
		// Make sure we're handling com_content categories
		if ($extension == 'com_docimport')
		{
			$this->categoryStateChange($pks, $value);
		}
	}

	/**
	 * Method to remove the link information for items that have been deleted.
	 *
	 * @param   string $context The context of the action being performed.
	 * @param   JTable $table   A JTable object containing the record to be deleted
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public function onFinderAfterDelete($context, $table)
	{
		if ($context == 'com_docimport.article')
		{
			$id = $table->docimport_article_id;
		}
		elseif ($context == 'com_finder.index')
		{
			$id = $table->link_id;
		}
		else
		{
			return true;
		}

		// Remove the items.
		return $this->remove($id);
	}

	/**
	 * Method to determine if the access level of an item changed.
	 *
	 * @param   string  $context The context of the content passed to the plugin.
	 * @param   JTable  $row     A JTable object
	 * @param   boolean $isNew   If the content has just been created
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public function onFinderAfterSave($context, $row, $isNew)
	{
		// We only want to handle posts here
		if ($context == 'com_docimport.articles')
		{
			$this->reindex($row->docimport_article_id);
		}

		return true;
	}

	/**
	 * Method to update the link information for items that have been changed
	 * from outside the edit screen. This is fired when the item is published,
	 * unpublished, archived, or unarchived from the list view.
	 *
	 * @param   string  $context The context for the content passed to the plugin.
	 * @param   array   $pks     A list of primary key ids of the content that has changed state.
	 * @param   integer $value   The value of the state that the content has been changed to.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function onFinderChangeState($context, $pks, $value)
	{
		// We only want to handle posts here
		if ($context == 'com_docimport.article')
		{
			$this->itemStateChange($pks, $value);
		}
		// Handle when the plugin is disabled
		if ($context == 'com_plugins.plugin' && $value === 0)
		{
			$this->pluginDisable($pks);
		}
	}

	/**
	 * Method to index an item. The item must be a FinderIndexerResult object.
	 *
	 * @param   FinderIndexerResult  $item    The item to index as an FinderIndexerResult object.
	 * @param   string               $format  The item format
	 *
	 * @return  void
	 *
	 * @throws  \Exception on database error.
	 */
	protected function index(FinderIndexerResult $item, $format = 'html')
	{
		// Check if the extension is enabled
		if (JComponentHelper::isEnabled($this->extension) == false)
		{
			return;
		}

		// Build the necessary route and path information.
		$item->url   = 'index.php?option=com_docimport&view=Article&id=' . $item->id;
		$item->route = $item->url;
		$item->path  = $this->getContentPath($item->route);

		// Translate the state. Articles should only be published if the category is published.
		$item->state = $this->translateState($item->enabled, $item->cat_state);

		$item->summary = $item->body;

		// Add the type taxonomy data.
		$item->addTaxonomy('Type', 'Documentation');

		// Add the author taxonomy data.
		if (!empty($item->author))
		{
			$item->addTaxonomy('Author', $item->author);
		}

		// Add the category taxonomy data.
		$item->addTaxonomy('Category', $item->category, $item->cat_state, $item->cat_access);

		// Index the item.
		$this->indexer->index($item);
	}

	/**
	 * Method to setup the indexer to be run.
	 *
	 * @return  boolean  True on success.
	 */
	protected function setup()
	{
		// Load dependent classes.
		include_once JPATH_SITE . '/components/com_docimport/router.php';

		if (!defined('JDEBUG'))
		{
			$config = JFactory::getConfig();

			define('JDEBUG', $config->get('debug', 0));
		}

		return true;
	}

	/**
	 * Method to get the SQL query used to retrieve the list of content items.
	 *
	 * @param   mixed  $sql  A JDatabaseQuery object or null.
	 *
	 * @return  JDatabaseQuery  A database object.
	 */
	protected function getListQuery($sql = null)
	{
		$db = JFactory::getDbo();
		// Check if we can use the supplied SQL query.
		$sql = ($sql instanceof JDatabaseQuery) ? $sql : $db->getQuery(true);
		$sql->select('a.docimport_article_id AS id, a.title, a.slug as alias, "" AS summary, a.fulltext AS body');
		$sql->select('a.enabled, a.docimport_category_id as catid, a.created_on AS start_date, a.created_by');
		$sql->select('a.modified_on, a.modified_by');
		$sql->select('a.docimport_article_id, a.slug');
		$sql->select('c.title AS category, c.enabled AS cat_state, c.access AS cat_access');
		$sql->select('c.language AS language, c.access AS access');

		$case_when_category_alias = ' CASE WHEN ';
		$case_when_category_alias .= $sql->charLength('c.slug');
		$case_when_category_alias .= ' THEN ';
		$c_id = $sql->castAsChar('c.docimport_category_id');
		$case_when_category_alias .= $sql->concatenate(array($c_id, 'c.slug'), ':');
		$case_when_category_alias .= ' ELSE ';
		$case_when_category_alias .= $c_id . ' END as catslug';
		$sql->select($case_when_category_alias);

		$sql->select('u.name AS author');
		$sql->from('#__docimport_articles AS a');
		$sql->join('LEFT', '#__docimport_categories AS c ON c.docimport_category_id = a.docimport_category_id');
		$sql->join('LEFT', '#__users AS u ON u.id = a.created_by');

		return $sql;
	}

	/**
	 * Method to get the URL for the item. The URL is how we look up the link
	 * in the Finder index.
	 *
	 * @param   integer $id        The id of the item.
	 * @param   string  $extension The extension the category is in.
	 * @param   string  $view      The view for the URL.
	 *
	 * @return  string  The URL of the item.
	 */
	protected function getURL($id, $extension, $view)
	{
		$url = 'index.php?option=' . $extension . '&view=' . $view . '&id=' . $id;

		return $url;
	}

	/**
	 * Method to get a content item to index.
	 *
	 * @param   integer $id The id of the content item.
	 *
	 * @return  FinderIndexerResult|\Joomla\Component\Finder\Administrator\Indexer\Result  A FinderIndexerResult object.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	protected function getItem($id)
	{
		JLog::add('FinderIndexerAdapter::getItem', JLog::INFO);

		// Get the list query and add the extra WHERE clause.
		$sql = $this->getListQuery();
		$sql->where('a.' . $this->db->quoteName('docimport_article_id') . ' = ' . (int)$id);

		// Get the item to index. NOTE: Finder expects database errors to throw exceptions.
		$this->db->setQuery($sql);
		$row = $this->db->loadAssoc();

		// Check for a database error. TODO I don't think we need this in Joomla! 3.3 and later.
		if ($this->db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($this->db->getErrorMsg(), 500);
		}

		// Convert the item to a result object.
		$item = \Joomla\Utilities\ArrayHelper::toObject($row, 'FinderIndexerResult');

		// Set the item type.
		$item->type_id = $this->type_id;

		// Set the item layout.
		$item->layout = $this->layout;

		return $item;
	}

	/**
	 * Method to get the path (SEF route) for a content item.
	 *
	 * @param   string  $url  The non-SEF route to the content item.
	 *
	 * @return  string  The path for the content item.
	 */
	private function getContentPath($url)
	{
		static $router;

		if (method_exists(FinderIndexerHelper::class, 'getContentPath'))
		{
			return FinderIndexerHelper::getContentPath($url);
		}

		// Only get the router once.
		if (!($router instanceof Router))
		{
			$router = Router::getInstance('site');
		}

		// Build the relative route.
		$uri   = $router->build($url);
		$route = $uri->toString(['path', 'query', 'fragment']);
		$route = str_replace(Uri::base(true) . '/', '', $route);

		return $route;
	}
}
