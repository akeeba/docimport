<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\DocImport\Site\Controller;

defined('_JEXEC') || die;

use Akeeba\Component\DocImport\Administrator\Table\ArticleTable;
use Akeeba\Component\DocImport\Administrator\Table\CategoryTable;
use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController
{
	public function display($cachable = false, $urlparams = [])
	{
		// This view is cacheable by default.
		$cachable = true;

		// Let's pre-process the view
		$id    = $this->input->getInt('id');
		$catId = $this->input->getInt('catid');
		$vName = $this->input->getCmd('view', 'categories');

		/**
		 * The "category" view is, in fact, the "articles" view. It's just more convenient to use "category" in the
		 * SEF router.
		 */
		if ($vName === 'category')
		{
			$vName = 'articles';
			$catId = $id;

			$this->input->set('view', $vName);
		}
		elseif ($vName === 'articles')
		{
			// This is used to determine whether to cache the articles display for this category.
			$catId = $id;
		}

		// We are asked to display an article but we don't have a category ID. Let's find it.
		if (($vName === 'article') && empty($catId))
		{
			/** @var ArticleTable $article */
			$article = $this->factory->createTable('Article', 'Administrator');

			if ($article->load($id))
			{
				$catId = $article->docimport_category_id;

				$this->input->set('catid', $catId);
			}
		}

		/**
		 * If I am trying to display articles in a category with content plugin processing I will NOT cache the results.
		 *
		 * The idea is that the typical use case is to display per-user content using plugin merge codes. If I tried to
		 * cache the results per user we'd end up with too many cached items without substantial performance gains in
		 * most cases. It's better to not use caching, instead enabled the System – Page Cache plugin to have the
		 * browser handle caching client-side instead.
		 */
		if (in_array($vName, ['articles', 'article']))
		{

			/** @var CategoryTable $category */
			$category = $this->factory->createTable('Category', 'Administrator');

			if ($category->load($catId))
			{
				$cachable = $category->process_plugins != 1;
			}
		}

		// Get the URL parameters which participate in Joomla caching.
		$urlparams = array_merge_recursive($urlparams, [
			'catid'            => 'INT',
			'id'               => 'INT',
			'limit'            => 'UINT',
			'limitstart'       => 'UINT',
			'filter_order'     => 'CMD',
			'filter_order_Dir' => 'CMD',
			'lang'             => 'CMD',
			'Itemid'           => 'INT',
		]);

		return parent::display($cachable, $urlparams);
	}

}