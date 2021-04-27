<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\DocImport\Administrator\Table;

defined('_JEXEC') || die();

use Akeeba\Component\DocImport\Administrator\Table\Mixin\CreateModifyAware;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use RuntimeException;

/**
 * @property int    $docimport_article_id  Article ID
 * @property int    $docimport_category_id Category ID
 * @property string $title                 Article title
 * @property string $slug                  Article slug
 * @property string $fulltext              Article HTML text
 * @property string $meta_description      Description for article meta
 * @property string $meta_tags             Article meta tags
 * @property int    $last_timestamp        Last updated timestamp
 * @property int    $enabled               Is this article published
 * @property string $created_on            Created date and time
 * @property int    $created_by            User ID which created this article
 * @property string $modified_on           Last modified date and time
 * @property int    $modified_by           User ID which last modified this article
 * @property string $locked_on             Row lock date and time
 * @property int    $locked_by             User ID which locked this article
 * @property int    $ordering              Ordering
 */
class ArticleTable extends Table
{
	use CreateModifyAware;

	public function __construct(DatabaseDriver $db)
	{
		parent::__construct('#__docimport_articles', 'docimport_article_id', $db);

		$this->setColumnAlias('id', 'docimport_article_id');
		$this->setColumnAlias('alias', 'slug');
		$this->setColumnAlias('published', 'enabled');
		$this->setColumnAlias('checked_out', 'locked_by');
		$this->setColumnAlias('checked_out_time', 'locked_on');

		$this->created_on = Factory::getDate()->toSql();
	}

	public function check()
	{
		if (!parent::check())
		{
			return false;
		}

		// Remove leading/trailing whitespace from the title and slug
		$this->title = trim($this->title ?? '');
		$this->slug  = trim($this->slug ?? '');

		// We need a non-empty title
		if (empty($this->title))
		{
			throw new RuntimeException('COM_DOCIMPORT_ERR_ARTICLE_TITLE');
		}

		// No slug? Create one from the title.
		$this->slug = trim(ApplicationHelper::stringURLSafe($this->slug ?: $this->title));

		/**
		 * WARNING! DO NOT SEARCH FOR SIMILAR SLUGS, DO NOT CHANGE THE SLUG IN ANY OTHER WAY.
		 *
		 * The slugs come from the DocBook XML. They are the XML IDs of other sections, paragraphs etc. They are used
		 * as link targets in internal link references. When you change the article slug DocImport cannot translate the
		 * internal references to  DocImport article IDs. As a result it produces broken non-SEF links inside the
		 * article source. When these are parsed in the front-end you get all sorts of weird behavior.
		 */

		return true;
	}

	public function store($updateNulls = false)
	{
		$this->onBeforeStore();

		return parent::store($updateNulls);
	}
}