<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\DocImport\Site\Model\Search;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\Component\DocImport\Site\Model\Search\Exception\SearchAreaNotFound;
use Akeeba\Component\DocImport\Site\Model\Search\Exception\SearchSectionNotFound;
use Joomla\CMS\Component\ComponentHelper;

class CategoriesConfiguration
{
	protected $config = null;

	/**
	 * CategoriesConfiguration constructor.
	 *
	 * @param   string|array|null  $config     A JSON string or an array holding the configuration. Null to load from
	 *                                         component's options.
	 */
	public function __construct($config = null)
	{
		$this->loadFromComponent();
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
				$result      = array_merge($result, $moreResults);
			}

			return $result;
		}

		if (!isset($this->config[$searchArea]))
		{
			throw new SearchAreaNotFound($searchArea);
		}

		if (!is_null($section))
		{
			$sectionMap    = SearchSection::getMap($section);
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
				'text'  => $title,
				'value' => $slug,
			];
		}

		return $ret;
	}

	/**
	 * Load the configuration from the comoponent's Options
	 *
	 * @return  void
	 */
	private function loadFromComponent()
	{
		$cParams      = ComponentHelper::getParams('com_docimport');
		$searchAreas  = (array) $cParams->get('search_areas', []);
		$this->config = [];

		foreach ($searchAreas as $area)
		{
			$this->config[$area->slug] = [
				'title'  => $area->title ?? '',
				'jcat'   => $area->jcat ?? [],
				'dicat'  => $area->dicat ?? [],
				'atscat' => $area->atscat ?? [],
			];
		}
	}
}
