<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\DocImport\Site\Model;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\Component\DocImport\Site\Model\Search\CategoriesConfiguration;
use Akeeba\Component\DocImport\Site\Model\Search\SearchSection;
use Joomla\CMS\Application\CMSApplication as JApplicationCms;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Pagination\Pagination as JPagination;

class SearchModel extends BaseDatabaseModel
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
			$this->categoriesConfiguration = new CategoriesConfiguration();
		}

		return $this->categoriesConfiguration;
	}

	/**
	 * Run the search and save the results array in $this->searchResults. The results array is in the following format:
	 * [
	 *        "sectionName" => [
	 *            "items" => ResultInterface[],
	 *            "count" => integer
	 *        ],
	 *        ...
	 * ]
	 *
	 * @return  void
	 */
	public function produceSearchResults()
	{
		$query      = $this->getState('search', '');
		$areas      = $this->getState('areas', []) ?: [];
		$limitStart = (int) $this->getState('start', 0);
		$limit      = (int) $this->getState('limit', 10);

		$this->searchResults = [];

		$catConfig    = $this->getCategoriesConfiguration();
		$sectionNames = SearchSection::getSections();

		foreach ($sectionNames as $sectionName)
		{
			$section = new SearchSection($sectionName, $areas, $catConfig);

			$this->searchResults[$sectionName] = [
				'items' => $section->getItems($query, $limitStart, $limit),
				'count' => $section->getCount($query),
			];
		}
	}

	/**
	 * Get the pagination results for the composite search query
	 *
	 * @param   string           $prefix
	 * @param   JApplicationCms  $app
	 *
	 * @return  JPagination
	 */
	public function getPagination($prefix = '', $app = null)
	{
		$limitStart = (int) $this->getState('start', 0);
		$limit      = (int) $this->getState('limit', 10);

		// Find the maximum number of items
		$maxCount = 0;

		foreach ($this->searchResults as $sectionName => $sectionResults)
		{
			$maxCount = max($maxCount, $sectionResults['count']);
		}

		return new JPagination($maxCount, $limitStart, $limit, $prefix, $app);
	}
}
