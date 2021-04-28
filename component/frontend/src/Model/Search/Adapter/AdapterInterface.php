<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\DocImport\Site\Model\Search\Adapter;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\Component\DocImport\Site\Model\Search\Result\ResultInterface;

/**
 * Search adapter interface
 */
interface AdapterInterface
{
	/**
	 * Search adapter constructor.
	 *
	 * @param   array  $categories  Optional. The categories we'll be searching in.
	 */
	public function __construct(array $categories = []);

	/**
	 * Set the categories to be searched
	 *
	 * @param   array  $categories  The categories to be searched
	 *
	 * @return  void
	 */
	public function setCategories(array $categories);

	/**
	 * @param   string  $search      The search query
	 * @param   int     $limitstart  Pagination start for results
	 * @param   int     $limit       Number of items to return
	 *
	 * @return  ResultInterface[]
	 */
	public function search($search, $limitstart, $limit);

	/**
	 * Total number of results yielded by the search query
	 *
	 * @param   string  $search  The search query
	 *
	 * @return  int
	 */
	public function count($search);
}
