<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\Model;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\DocImport\Site\Model\Search\CategoriesConfiguration;
use Akeeba\DocImport\Site\Model\Search\SearchSection;
use FOF30\Model\Model;
use JApplicationCms;
use JPagination;

class Search extends Model
{
	/**
	 * The search results. It is a hash array of arrays in the format:
	 * [ 'sectionName' => ['items' => ResultInterface[], 'count' => int], ... ]
	 *
	 * @var  array
	 */
	public $searchResults = [];

	/**
	 * The cached support areas configuration for this model
	 *
	 * @var  CategoriesConfiguration
	 */
	private $categoriesConfiguration = null;

	/**
	 * Returns the cached support areas configuration for this model, creating it if it doesn't exist
	 *
	 * @return  CategoriesConfiguration
	 */
	public function getCategoriesConfiguration()
	{
		if (is_null($this->categoriesConfiguration))
		{
			$this->categoriesConfiguration = new CategoriesConfiguration($this->container);
		}

		return $this->categoriesConfiguration;
	}

	/**
	 * Run the search and save the results array in $this->searchResutls. The results array is in the following format:
	 * [
	 * 		"sectionName" => [
	 * 			"items" => ResultInterface[],
	 * 			"count" => integer
	 * 		],
	 * 		...
	 * ]
	 *
	 * @return  void
	 */
	public function produceSearchResults()
	{
		$query      = $this->getState('search', '', 'string');
		$areas      = $this->getState('areas', [], 'array');
		$limitStart = $this->getState('start', 0, 'int');
		$limit      = $this->getState('limit', 10, 'int');

		$this->searchResults = [];

		$catConfig    = $this->getCategoriesConfiguration();
		$sectionNames = SearchSection::getSections();

		foreach ($sectionNames as $sectionName)
		{
			$section = new SearchSection($this->container, $sectionName, $areas, $catConfig);

			$this->searchResults[ $sectionName ] = [
				'items' => $section->getItems($query, $limitStart, $limit),
				'count' => $section->getCount($query),
			];
		}
	}

	/**
	 * Get the pagination results for the composite search query
	 *
	 * @param   string          $prefix
	 * @param   JApplicationCms $app
	 *
	 * @return  JPagination
	 */
	public function getPagination($prefix = '', $app = null)
	{
		$limitStart = $this->getState('start', 0, 'int');
		$limit      = $this->getState('limit', 10, 'int');

		// Find the maximum number of items
		$maxCount = 0;

		foreach ($this->searchResults as $sectionName => $sectionResults)
		{
			$maxCount = max($maxCount, $sectionResults['count']);
		}

		return new \JPagination($maxCount, $limitStart, $limit, $prefix, $app);
	}

	/**
	 * Method to set model state variables
	 *
	 * @param   string $property The name of the property.
	 * @param   mixed  $value    The value of the property to set or null.
	 *
	 * @return  mixed  The previous value of the property or null if not set.
	 */
	public function setState($property, $value = null)
	{
		if ($this->_savestate)
		{
			$key = $this->getHash() . $property;
			\JFactory::getApplication()->setUserState($key, $value);
		}

		if (is_null($this->state))
		{
			$this->state = new \stdClass();
		}

		return $this->state->$property = $value;
	}
}
