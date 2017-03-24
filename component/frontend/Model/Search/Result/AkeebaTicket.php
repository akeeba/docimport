<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2011-2017 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\Model\Search\Result;

// Protect from unauthorized access
defined('_JEXEC') or die();

use JRoute;
use ContentHelperRoute;

/**
 * Search result class for ATS posts
 */
class AkeebaTicket extends AbstractResult
{
	/** @var  string  Ticket slug */
	public $slug;

	/** @var  string  Post's full text */
	public $fulltext;

	/** @var  string  Article's created date */
	public $created_on;

	/** @var  string  Article's modified date */
	public $modified_on;

	/** @var  int  Post ID */
	public $pid;

	/** @var  int  Category ID */
	public $catid;

	/** @var  string  Category name */
	public $catname;

	/** @var  string  Category slug */
	public $catslug;

	/** @var  string  Category's language */
	public $language;


	/**
	 * Get the URL to access the post
	 *
	 * @return  string
	 */
	public function getLink()
	{
		return JRoute::_('index.php?option=com_ats&view=Ticket&id=' . $this->id);
	}

	/**
	 * Get the URL to access the category
	 *
	 * @return  string
	 */
	public function getCategoryLink()
	{
		return JRoute::_('index.php?option=com_ats&view=Tickets&category=' . $this->catid);
	}
}