<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\Model\Search\Adapter;

// Protect from unauthorized access
defined('_JEXEC') or die();

use JDatabaseQuery;

/**
 * DocImport articles search adapter class
 */
class DocImport extends AbstractAdapter
{
	/** @var  string  The class for the result objects, must implement ResultInterface */
	protected $resultClass = '\\Akeeba\\DocImport\\Site\\Model\\Search\\Result\\DocImportArticle';

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
		$db    = $this->container->db;

		// Get the authorized user access levels
		$accessLevels = $this->container->platform->getUser()->getAuthorisedViewLevels();
		$accessLevels = array_map([$db, 'quote'], $accessLevels);

		// Sanitize categories
		$categories = array_map(function ($c) use ($db)
		{
			$c = trim($c);
			$c = (int)$c;

			return $db->q($c);
		}, $this->categories);
		$categories = array_unique($categories);

		// Get the search query
		$query = $db->getQuery(true)
					->select(array(
						$db->qn('docimport_article_id', 'id'),
						$db->qn('a.title'),
						$db->qn('a.slug'),
						$db->qn('docimport_category_id', 'catid'),
						$db->qn('c.title', 'cattitle'),
						$db->qn('c.slug', 'catslug'),
						$db->qn('a.fulltext'),
						$db->qn('a.created_on'),
						$db->qn('a.modified_on'),
						'MATCH(' . $db->qn('fulltext') . ') AGAINST (' . $db->q($search) . ') as ' . $db->qn('score')
					))
					->from($db->qn('#__docimport_articles', 'a'))
					->innerJoin($db->qn('#__docimport_categories', 'c') . ' USING(' . $db->qn('docimport_category_id') . ')')
					->where($db->qn('a.enabled') . ' = ' . $db->q('1'))
					->where($db->qn('c.enabled') . ' = ' . $db->q('1'))
					->where($db->qn('c.access') . ' IN (' . implode(',', $accessLevels) . ')')
					->where($db->qn('docimport_category_id') . ' IN (' . implode(',', $categories) . ')')
					->where('MATCH(' . $db->qn('fulltext') . ') AGAINST (' . $db->q($search) . ')')
					->order($db->qn('score') . ' DESC');

		$this->filterByLanguage($query);

		if ($onlyCount)
		{
			$query->clear('select');
			$query->clear('order');
			$query->select('COUNT(*)');
		}

		return $query;
	}
}
