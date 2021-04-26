<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\Model\Search\Result;

// Protect from unauthorized access
defined('_JEXEC') or die();

use JRoute;
use ContentHelperRoute;

/**
 * Search result class for Joomla! content articles
 */
class JoomlaArticle extends AbstractResult
{
	/** @var  string  Article slug */
	public $alias;

	/** @var  int  Category ID */
	public $catid;

	/** @var  string  Category name */
	public $catname;

	/** @var  string  Category slug */
	public $catalias;

	/** @var  string  Article's intro text (before the Read More) */
	public $introtext;

	/** @var  string  Article's full text (after the Read More) */
	public $fulltext;

	/** @var  string  Article's language */
	public $language;

	/** @var  string  Article's created date */
	public $created;

	/** @var  string  Article's modified date */
	public $modified;

	/**
	 * Get the URL to access the article
	 *
	 * @param   int  $Itemid  Because Joomla! can't find the menu item on its own when you do not use a Categories / Blog layout. ARGH!
	 *
	 * @return  string
	 */
	public function getLink($Itemid = null)
	{
		if (!class_exists('ContentHelperRoute'))
		{
			require_once JPATH_SITE . '/components/com_content/helpers/route.php';
		}

		$link = ContentHelperRoute::getArticleRoute($this->id, $this->catid, $this->language);

		if (!empty($Itemid))
		{
			$jLink = new \JUri($link);
			$jLink->setVar('Itemid', $Itemid);
			$link = $jLink->toString();
		}

		return JRoute::_($link);
	}

	/**
	 * Get the URL to access the category
	 *
	 * @return  string
	 */
	public function getCategoryLink()
	{
		if (!class_exists('ContentHelperRoute'))
		{
			require_once JPATH_SITE . '/components/com_content/helpers/route.php';
		}

		return JRoute::_(ContentHelperRoute::getCategoryRoute($this->catid, $this->language));
	}
}
