<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\Model\Search;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\DocImport\Site\Model\Search\Adapter\AdapterInterface;
use Akeeba\DocImport\Site\Model\Search\CategoriesConfiguration as CatConfig;
use Akeeba\DocImport\Site\Model\Search\Exception\SearchSectionNotFound;
use Akeeba\DocImport\Site\Model\Search\Result\ResultInterface;
use FOF30\Container\Container;

/**
 * Handles a singe search section (e.g. Joomla! articles)
 *
 * @package Akeeba\DocImport\Site\Model\Search
 */
class SearchSection
{
	/**
	 * The search section mapping defined in the SearchSections.json file
	 *
	 * @var  array
	 */
	protected static $map = null;

	/** @var  string  The key of the search section we handle with this object */
	protected $section = '';

	/** @var  AdapterInterface  The search adapter for this section */
	protected $adapter = null;

	/** @var  Container  The container of the component we belong in */
	protected $container = null;

	/** @var  CatConfig  The categories configuration */
	protected $categoriesConfiguration = null;

	protected $cachedItems = null;

	protected $cachedCount = null;

	/**
	 * SearchSection constructor.
	 *
	 * @param   Container  $container    The container of the component we belong in
	 * @param   string     $section      The name of the search section, e.g. 'joomla', 'docimport', ...
	 * @param   array      $searchAreas  The names of the search areas as defined in the Options
	 * @param   CatConfig  $catConfig    OPTIONAL. Categories configuration. If not set we make a default object.
	 *
	 * @throws  SearchSectionNotFound  When the search section does not exist in the map
	 */
	public function __construct(Container $container, $section, array $searchAreas = [], CatConfig $catConfig = null)
	{
		// Make sure the section exists. If it doesn't, getMap will throw an exception
		$map = static::getMap($section);

		// Set the section
		$this->section = $section;

		// Set the container
		$this->container = $container;

		// Set the categories configuration
		if (!is_object($catConfig))
		{
			$catConfig = new CatConfig($container);
		}

		$this->categoriesConfiguration = $catConfig;

		// If no search areas are defined we need to search all areas
		if (empty($searchAreas))
		{
			$searchAreas = $this->categoriesConfiguration->getAllAreas();
		}

		// Get the categories
		$categories = [];

		foreach ($searchAreas as $area)
		{
			$areaCats = $this->categoriesConfiguration->getCategoriesFor($area, $this->section);

			if (!is_array($areaCats))
			{
				continue;
			}

			$categories = array_merge($categories, $areaCats);
		}

		$categories = array_unique($categories);

		// Create the adapter
		$adapterClass = 'Akeeba\\DocImport\\Site\\Model\\Search\\Adapter\\' . $map['adapter'];
		$this->adapter = new $adapterClass($container, $categories);
	}

	/**
	 * Get the search result items
	 *
	 * @param   string  $query       Search string
	 * @param   int     $limitStart  Results starting offset
	 * @param   int     $limit       Maximum number of results returned
	 *
	 * @return  ResultInterface[]
	 */
	public function getItems($query, $limitStart = 0, $limit = 10)
	{
		$this->cachedItems = $this->adapter->search($query, $limitStart, $limit);
	}

	/**
	 * Get the total number of search results
	 *
	 * @param   string  $query  Search string
	 *
	 * @return  int  Total number of search results
	 */
	public function getCount($query)
	{
		$this->cachedItems = $this->adapter->count($query);
	}

	/**
	 * Returns the search section mapping.
	 *
	 * @param   string  $section  The section to get the map from. Leave null to return the whole map.
	 *
	 * @return  array
	 *
	 * @throws  SearchSectionNotFound  If the search section you requested does not exist
	 */
	public static function getMap($section = null)
	{
		// Do I have to load the map?
		if (is_null(static::$map))
		{
			$json = file_get_contents(__DIR__ . '/SearchSections.json');
			static::$map = json_decode($json, true);
		}

		// No section requested, return the whole map
		if (is_null($section))
		{
			return static::$map;
		}

		// Make sure the section exists
		if (!isset(static::$map[$section]))
		{
			throw new SearchSectionNotFound($section);
		}

		// Return the section map
		return static::$map[$section];
	}

	/**
	 * Return a list of all section keys
	 *
	 * @return  string[]
	 */
	public static function getSections()
	{
		$map = static::getMap();

		return array_keys($map);
	}
}