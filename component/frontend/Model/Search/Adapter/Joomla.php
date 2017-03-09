<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2011-2017 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\Model\Search\Adapter;

// Protect from unauthorized access
defined('_JEXEC') or die();

use JDatabaseQuery;

/**
 * Joomla articles search adapter class
 */
class Joomla extends AbstractAdapter
{
	/** @var  string  The class for the result objects, must implement ResultInterface */
	protected $resultClass = '\\Akeeba\\DocImport\\Site\\Model\\Search\\Result\\JoomlaArticle';

	/**
	 * Gets the database query used to search and produce the count of search results
	 *
	 * @param   string  $search     The search terms
	 * @param   bool    $onlyCount  If try, return a COUNT(*) query instead of a results selection query
	 *
	 * @return  JDatabaseQuery  The query to execute
	 */
	protected function getQuery($search, $onlyCount)
	{
		// Get the db object
		$db = $this->container->db;

		// Get the authorized user access levels
		$accessLevels = \JFactory::getUser()->getAuthorisedViewLevels();
		$accessLevels = array_map([$db, 'quote'], $accessLevels);

		// Sanitize categories
		$categories = array_map(function ($c) use ($db)
		{
			$c = trim($c);
			$c = (int)$c;

			return $db->q($c);
		}, $this->categories);
		$categories = array_unique($categories);

		// Search the content table
		$query = $db->getQuery(true)
					->select([
						$db->qn('a.id'),
						$db->qn('a.title'),
						$db->qn('a.alias'),
						$db->qn('a.catid'),
						$db->qn('c.title', 'catname'),
						$db->qn('c.alias', 'catalias'),
						$db->qn('a.introtext'),
						$db->qn('a.fulltext'),
						$db->qn('a.language'),
						$db->qn('a.created'),
						$db->qn('a.modified'),
					])
					->from($db->qn('#__content', 'a'))
					->innerJoin($db->qn('#__categories', 'c') . ' ON (' . $db->qn('c.id') . ' = ' . $db->qn('a.catid') . ')')
					->where($db->qn('a.state') . ' = ' . $db->q('1'))
					->where($db->qn('c.published') . ' = ' . $db->q('1'))
					->where($db->qn('a.access') . ' IN(' . implode(',', $accessLevels) . ')')
					->where($db->qn('a.catid') . ' IN(' . implode(',', $categories) . ')')
					->where('(' .
						$db->qn('a.title') . ' LIKE ' . $db->q("%$search%") .
						' OR ' . $db->qn('a.introtext') . ' LIKE ' . $db->q("%$search%") .
						' OR ' . $db->qn('a.fulltext') . ' LIKE ' . $db->q("%$search%") .
						' OR ' . $db->qn('c.title') . ' LIKE ' . $db->q("%$search%") .
						' OR ' . $db->qn('c.description') . ' LIKE ' . $db->q("%$search%") .
						')');

		// Filter query by language
		$this->filterByLanguage($query);

		if ($onlyCount)
		{
			$query->clear('select');
			$query->clear('order');
			$query->select('COUNT(*)');
		}

		return $query;
	}

	/**
	 * Override parent search function to run plugins on the results before returning them.
	 *
	 * @param string $search
	 * @param int    $limitstart
	 * @param int    $limit
	 *
	 * @return \Akeeba\DocImport\Site\Model\Search\Result\ResultInterface[]
	 */
	public function search($search, $limitstart, $limit)
	{
		$results = parent::search($search, $limitstart, $limit);

		if ($results)
		{
			\JPluginHelper::importPlugin('content');

			foreach ($results as $result)
			{
				// Currently we are working only vs the introtext, since it's the only text displayed in search results.
				// Otherwise we would have to trigger the same event twice, impacting the performance
				$fake_article = (object) array(
					'text' => $result->introtext
				);

				\JFactory::getApplication()->triggerEvent('onContentPrepare', array('com_docimport.search.joomlafulltext', &$fake_article));

				$result->introtext = $fake_article->text;
			}
		}

		return $results;
	}
}