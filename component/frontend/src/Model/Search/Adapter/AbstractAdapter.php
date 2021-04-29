<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\DocImport\Site\Model\Search\Adapter;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\Component\DocImport\Site\Model\Search\Result\AbstractResult;
use Akeeba\Component\DocImport\Site\Model\Search\Result\ResultInterface;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseQuery;
use Joomla\Registry\Registry;

/**
 * Abstract search adapter class
 */
abstract class AbstractAdapter implements AdapterInterface
{
	/** @var  array  Categories to search into */
	protected $categories = [];

	/** @var  string  The class for the result objects, must implement ResultInterface */
	protected $resultClass = AbstractResult::class;

	/**
	 * Search adapter constructor.
	 *
	 * @param   array  $categories  Optional. The categories we'll be searching in.
	 */
	public function __construct(array $categories = [])
	{
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
		$container = Factory::getContainer();
		$db        = ($container->has('database.driver') ? $container->get('database.driver') : null)
			?: Factory::getDbo();

		$query = $this->getQuery($search, false);

		// Search the database
		try
		{
			/**
			 * Joomla 4 is buggy and won't be fixed, see https://github.com/joomla/joomla-cms/issues/24857
			 *
			 * Even though our custom class DOES NOT have a constructor (contrary to what was said in the ticket) we
			 * still get the same obscure error when using a class other than stdClass. The Joomla project is blissfully
			 * unaware of the problem since they do not use any other class name in the core code.
			 */
			$ret = $db->setQuery($query, $limitstart, $limit)->loadAssocList() ?: [];
			$ret = array_map(function (array $arr) {
				$o = new $this->resultClass;

				foreach ($arr as $k => $v)
				{
					$o->{$k} = $v;
				}

				return $o;
			}, $ret);

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
		$container = Factory::getContainer();
		$db        = ($container->has('database.driver') ? $container->get('database.driver') : null)
			?: Factory::getDbo();

		$query = $this->getQuery($search, true);

		// Search the database
		try
		{
			return (int) ($db->setQuery($query)->loadResult());
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
	 * @return  DatabaseQuery  The query to execute
	 */
	abstract protected function getQuery($search, $onlyCount);

	/**
	 * Filters a query by front-end language
	 *
	 * @param   DatabaseQuery  $query          The query to filter
	 * @param   string         $languageField  The name of the language field in the query, default is"language"
	 *
	 * @return  void  The $query object is modified directly
	 *
	 * @throws  \Exception
	 */
	protected function filterByLanguage(DatabaseQuery $query, $languageField = 'language')
	{
		$app = Factory::getApplication();

		if (empty($app) || !is_object($app) || !($app instanceof CMSApplication))
		{
			return;
		}

		if (!Multilanguage::isEnabled($app))
		{
			return;
		}

		$lang_filter_plugin = PluginHelper::getPlugin('system', 'languagefilter');
		$lang_filter_params = new Registry($lang_filter_plugin->params);
		$languages = ['*'];

		if ($lang_filter_params->get('remove_default_prefix'))
		{
			// Get default site language
			$languages[] = $app->getLanguage()->getTag();
		}
		else
		{
			// We have to use JInput since the language fragment is not set in the $_REQUEST, thus we won't have it in our model
			$languages[] = $app->input->getCmd('language', '*');
		}

		// Filter out double languages
		$languages = array_unique($languages);

		// And filter the query output by these languages
		$languages = array_map([$query, 'quote'], $languages);
		$query->where($query->qn($languageField) . ' IN(' . implode(',', $languages) . ')');
	}
}
