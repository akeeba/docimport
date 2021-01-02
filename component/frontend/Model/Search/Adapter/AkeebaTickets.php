<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\Model\Search\Adapter;

// Protect from unauthorized access
defined('_JEXEC') or die();

use JDatabaseQuery;

/**
 * Akeeba Ticket System posts search adapter class
 */
class AkeebaTickets extends AbstractAdapter
{
	/** @var  string  The class for the result objects, must implement ResultInterface */
	protected $resultClass = '\\Akeeba\\DocImport\\Site\\Model\\Search\\Result\\AkeebaTicket';

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
						$db->qn('t.ats_ticket_id', 'id'),
						$db->qn('t.title'),
						$db->qn('t.alias', 'slug'),
						$db->qn('p.content_html', 'fulltext'),
						$db->qn('p.created_on'),
						$db->qn('p.modified_on'),
						$db->qn('p.ats_post_id', 'pid'),
						$db->qn('t.catid'),
						$db->qn('c.title', 'catname'),
						$db->qn('c.alias', 'catslug'),
						$db->qn('c.language', 'language'),
						'MATCH(' . $db->qn('content_html') . ') AGAINST (' . $db->q($search) . ') ' .
						'* (730 - DATEDIFF(NOW(), ' . $db->qn('t.created_on') . ')) AS ' . $db->qn('score')
					))
					->from($db->qn('#__ats_posts', 'p'))
					->innerJoin($db->qn('#__ats_tickets', 't') . ' USING(' . $db->qn('ats_ticket_id') . ')')
					->innerJoin($db->qn('#__categories', 'c') . ' ON(' . $db->qn('c.id') . ' = ' . $db->qn('t.catid') . ')')
					->where($db->qn('c.id') . ' IN (' . implode(',', $categories) . ')')
					->where($db->qn('c.published') . ' = ' . $db->q('1'))
					->where($db->qn('c.access') . ' IN (' . implode(',', $accessLevels) . ')')
					->where($db->qn('t.enabled') . ' = ' . $db->q('1'))
					->where($db->qn('t.public') . ' = ' . $db->q('1'))
					->where($db->qn('t.status') . ' IN(' . $db->q('P') . ',' . $db->q('C') . ')')
					->where($db->qn('t.created_on') . ' >= NOW() - INTERVAL 2 YEAR')
					->where($db->qn('p.enabled') . ' = ' . $db->q('1'))
					->where('MATCH(' . $db->qn('content_html') . ') AGAINST (' . $db->q($search) . ')')
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
