<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\Model\Search\Adapter;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\DocImport\Site\Model\Search\Result\ResultInterface;
use FOF30\Container\Container;
use JDatabaseQuery;

/**
 * Abstract search adapter class
 */
abstract class AbstractAdapter implements AdapterInterface
{
	/** @var  array  Categories to search into */
	protected $categories = [];

	/** @var  Container  The container of the component we belong to */
	protected $container;

	/** @var  string  The class for the result objects, must implement ResultInterface */
	protected $resultClass = '\\Akeeba\\DocImport\\Site\\Model\\Search\\Result\\AbstractResult';

	/**
	 * Search adapter constructor.
	 *
	 * @param   Container  $container   The component's container
	 * @param   array      $categories  Optional. The categories we'll be searching in.
	 */
	public function __construct(Container $container, array $categories = [])
	{
		$this->container = $container;

		$this->setCategories($categories);
	}

	/**
	 * Set the categories to be searched
	 *
	 * @param   array  $categories  The categories to be searched
	 *
	 * @return  void
	 */
	public function setCategories(array $categories)
	{
		$this->categories = $categories;
	}

	/**
	 * @param   string  $search      The search query
	 * @param   int     $limitstart  Pagination start for results
	 * @param   int     $limit       Number of items to return
	 *
	 * @return  ResultInterface[]
	 */
	public function search($search, $limitstart, $limit)
	{
		// Initialize return
		$ret = [];

		// We need to be told which categories to search in
		if (empty($this->categories))
		{
			return $ret;
		}

		// We need to search for something
		if (empty($search))
		{
			return $ret;
		}

		// Get the db object
		$db = $this->container->db;

		$query = $this->getQuery($search, false);

		// Search the database
		try
		{
			$ret = $db->setQuery($query, $limitstart, $limit)->loadObjectList('', $this->resultClass);

			return empty($ret) ? [] : $ret;
		}
		catch (\Exception $e)
		{
			return [];
		}
	}

	/**
	 * Total number of results yielded by the search query
	 *
	 * @param   string  $search  The search query
	 *
	 * @return  int
	 */
	public function count($search)
	{
		// We need to be told which categories to search in
		if (empty($this->categories))
		{
			return 0;
		}

		// We need to search for something
		if (empty($search))
		{
			return 0;
		}

		// Get the db object
		$db = $this->container->db;

		$query = $this->getQuery($search, true);

		// Search the database
		try
		{
			return (int)($db->setQuery($query)->loadResult());
		}
		catch (\Exception $e)
		{
			return 0;
		}
	}

	/**
	 * Gets the database query used to search and produce the count of search results
	 *
	 * @param   string  $search     The search terms
	 * @param   bool    $onlyCount  If try, return a COUNT(*) query instead of a results selection query
	 *
	 * @return  JDatabaseQuery  The query to execute
	 */
	abstract protected function getQuery($search, $onlyCount);

	/**
	 * Filters a query by front-end language
	 *
	 * @param   \JDatabaseQuery  $query          The query to filter
	 * @param   string           $languageField  The name of the language field in the query, default is "language"
	 *
	 * @return  void  The $query object is modified directly
	 *
	 * @throws  \Exception
	 */
	protected function filterByLanguage(\JDatabaseQuery &$query, $languageField = 'language')
	{
		// Make sure the field actually exists AND we're not in CLI
		if ($this->container->platform->isCli())
		{
			return;
		}

		/** @var \JApplicationSite $app */
		$app               = \JFactory::getApplication();
		$hasLanguageFilter = method_exists($app, 'getLanguageFilter');

		if ($hasLanguageFilter)
		{
			$hasLanguageFilter = $app->getLanguageFilter();
		}

		if (!$hasLanguageFilter)
		{
			return;
		}

		// Ask Joomla for the plugin only if we don't already have it in the container. Useful for tests
		if (isset($this->container['lang_filter_plugin']) && is_object($this->container['lang_filter_plugin'])
			&& ($this->container['lang_filter_plugin'] instanceof \JPlugin))
		{
			$lang_filter_plugin = $this->container['lang_filter_plugin'];
		}
		else
		{
			$lang_filter_plugin = \JPluginHelper::getPlugin('system', 'languagefilter');
		}

		$lang_filter_params = new \JRegistry($lang_filter_plugin->params);

		$languages = array('*');

		if ($lang_filter_params->get('remove_default_prefix'))
		{
			// Get default site language
			$platform    = $this->container->platform;
			$lg          = $platform->getLanguage();
			$languages[] = $lg->getTag();
		}
		else
		{
			// We have to use JInput since the language fragment is not set in the $_REQUEST, thus we won't have it in our model
			$languages[] = \JFactory::getApplication()->input->getCmd('language', '*');
		}

		// Filter out double languages
		$languages = array_unique($languages);

		// And filter the query output by these languages
		$languages = array_map(array($query, 'quote'), $languages);
		$query->where($query->qn($languageField) . ' IN(' . implode(',', $languages) . ')');
	}

}
