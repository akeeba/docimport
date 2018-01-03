<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2011-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 *  @license GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\Model\Search\Result;

// Protect from unauthorized access
defined('_JEXEC') or die();

use JRoute;
use ContentHelperRoute;

/**
 * Search result class for DocImport content articles
 */
class DocImportArticle extends AbstractResult
{
	/** @var  string  Article slug */
	public $slug;

	/** @var  int  Category ID */
	public $catid;

	/** @var  string  Category name */
	public $cattitle;

	/** @var  string  Category slug */
	public $catslug;

	/** @var  string  Article's full text (after the Read More) */
	public $fulltext;

	/** @var  string  Article's created date */
	public $created_on;

	/** @var  string  Article's modified date */
	public $modified_on;

	/** @var  float  Relevance score (0 to 1) */
	public $score;

	/**
	 * Get the URL to access the article
	 *
	 * @return  string
	 */
	public function getLink()
	{
		return JRoute::_('index.php?option=com_docimport&view=Article&id=' . $this->id);
	}

	/**
	 * Get the URL to access the category
	 *
	 * @return  string
	 */
	public function getCategoryLink()
	{
		return JRoute::_('index.php?option=com_docimport&view=Category&id=' . $this->catid);
	}
}
