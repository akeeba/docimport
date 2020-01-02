<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\Model\Search\Exception;

// Protect from unauthorized access
defined('_JEXEC') or die();

class SearchAreaNotFound extends \RuntimeException
{
	/**
	 * SearchSectionNotFound constructor.
	 *
	 * @param   string  $section  The search section whcih does not exist
	 */
	public function __construct($section)
	{
		parent::__construct("Search area (support section) $section does not exist", 500);
	}
}
