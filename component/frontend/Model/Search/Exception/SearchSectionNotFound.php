<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\Model\Search\Exception;

// Protect from unauthorized access
defined('_JEXEC') or die();

class SearchSectionNotFound extends \RuntimeException
{
	/**
	 * SearchSectionNotFound constructor.
	 *
	 * @param   string  $section  The search section whcih does not exist
	 */
	public function __construct($section)
	{
		parent::__construct("Search results section $section is not defined", 500);
	}
}
