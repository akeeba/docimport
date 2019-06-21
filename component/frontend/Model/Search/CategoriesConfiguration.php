<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\Model\Search;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\DocImport\Site\Model\Search\Exception\SearchAreaNotFound;
use Akeeba\DocImport\Site\Model\Search\Exception\SearchSectionNotFound;
use FOF30\Container\Container;

class CategoriesConfiguration
{
	/** @var  Container  The container of the component we belong in */
	protected $container;

	protected $config = null;

	/**
	 * CategoriesConfiguration constructor.
	 *
	 * @param   Container          $container  The container of the component we belong to
	 * @param   string|array|null  $config     A JSON string or an array holding the configuration. Null to load from
	 *                                         component's options.
	 */
	public function __construct(Container $container, $config = null)
	{
		$this->container = $container;

		if (is_null($config))
		{
			$this->loadFromComponent();
		}
		elseif (is_string($config))
		{
			$this->loadFromRawJson($config);
		}
		else
		{
			$this->config = $config;
		}
	}

	/**
	 * Get the category IDs for the specified search area and section
	 *
	 * @param   string  $searchArea  The search area, as configured in the component, e.g. 'foobar'
	 * @param   string  $section     The support section, as set up in SearchSections.json, e.g. 'joomla'
	 *
	 * @return  array  Array of categories when $section is defined, hash array (keyed per area) of category arrays
	 *                 otherwise.
	 */
	public function getCategoriesFor($searchArea, $section = null)
	{
		if ($searchArea == '*')
		{
			$result = [];

			foreach (array_keys($this->config) as $area)
			{
				$moreResults = $this->getCategoriesFor($area, $section);
				$result = array_merge($result, $moreResults);
			}

			return $result;
		}

		if (!isset($this->config[$searchArea]))
		{
			throw new SearchAreaNotFound($searchArea);
		}

		if (!is_null($section))
		{
			$sectionMap = SearchSection::getMap($section);
			$configSection = $sectionMap['config'];

			if (!isset($this->config[$searchArea][$configSection]))
			{
				throw new SearchSectionNotFound($section);
			}

			return $this->config[$searchArea][$configSection];
		}

		return $this->config[$searchArea];
	}

	/**
	 * Get the keys of all search areas, as configured in the component
	 *
	 * @return  string[]
	 */
	public function getAllAreas()
	{
		return array_keys($this->config);
	}

	/**
	 * Returns the titles of all search areas in a format usable by JHtmlSelect::options
	 *
	 * @return  array  Format: [ ['text' => 'Title 1', 'value' => 'slug1'], ... ]
	 */
	public function getAllAreaTitles()
	{
		$ret = [];

		foreach ($this->config as $slug => $item)
		{
			$title = isset($item['title']) ? $item['title'] : $slug;
			$ret[] = [
				'text' => $title,
				'value' => $slug
			];
		}

		return $ret;
	}

	/**
	 * Load the configuration from a raw JSON string. This method expects to get the bass ackwards JSON produced by
	 * Joomla's "repeatable" field. Instead of creating one array per row it creates one array per column. This is an
	 * array format that is directly usable in EXACTLY ZERO PROGRAMMING LANGUAGES, so we have to waste CPU time to
	 * normalize it. Sigh.
	 *
	 * @param   string  $json  The JSON string
	 *
	 * @return  void
	 */
	private function loadFromRawJson($json)
	{
		$this->config = [];

		$rawConfig = json_decode($json, true);

		if (!is_array($rawConfig) || !count($rawConfig))
		{
			return;
		}

		$totalItems = count($rawConfig['title']);
		$keys       = array_keys($rawConfig);

		for ($i = 0; $i < $totalItems; $i++)
		{
			$newEntry = [];

			foreach ($keys as $key)
			{
				$newEntry[ $key ] = $rawConfig[ $key ][ $i ];
			}

			$this->config[ $rawConfig['slug'][ $i ] ] = $newEntry;
		}
	}

	/**
	 * Load the configuration from the comoponent's Options
	 *
	 * @return  void
	 */
	private function loadFromComponent()
	{
		$this->loadFromRawJson($this->container->params->get('search_areas', '{}'));
	}
}
