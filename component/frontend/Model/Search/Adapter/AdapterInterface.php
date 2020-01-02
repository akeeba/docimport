<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\Model\Search\Adapter;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\DocImport\Site\Model\Search\Result\ResultInterface;
use FOF30\Container\Container;

/**
 * Search adapter interface
 */
interface AdapterInterface
{
	/**
	 * Search adapter constructor.
	 *
	 * @param   Container  $container   The component's container
	 * @param   array      $categories  Optional. The categories we'll be searching in.
	 */
	public function __construct(Container $container, array $categories = []);

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
