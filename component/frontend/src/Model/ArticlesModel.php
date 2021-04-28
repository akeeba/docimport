<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\DocImport\Site\Model;

defined('_JEXEC') || die;

use Akeeba\Component\DocImport\Administrator\Model\ArticlesModel as AdminArticlesModel;
use Akeeba\Component\DocImport\Administrator\Table\CategoryTable;
use Akeeba\Component\DocImport\Site\Model\Mixin\FrontendFilterAware;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;

class ArticlesModel extends AdminArticlesModel
{
	use FrontendFilterAware;

	public function getIndex()
	{
		$db    = $this->getDbo();
		$query = $this->getListQuery()
			->where($db->quoteName('a.slug') . ' = ' . $db->quote('index'))
			->setLimit(0, 1);

		return $db->setQuery($query)->loadObject() ?: null;
	}

	public function getCategory(): ?CategoryTable
	{
		/** @var CategoryTable $category */
		$category = $this->getMVCFactory()->createTable('Category', 'Administrator');

		if ($category->load($this->getState('catid')))
		{
			return $category;
		}

		return null;
	}

	protected function populateState($ordering = null, $direction = null)
	{
		unset($this->populateFilters['enabled']);
		$this->setState('enabled', 1);

		unset($this->populateFilters['cat_enabled']);
		$this->setState('cat_enabled', 1);

		unset($this->populateFilters['catid']);
		/** @var CMSApplication $app */
		$app = Factory::getApplication();
		$this->setState('catid', $app->input->getInt('id'));

		unset($this->populateFilters['access']);
		$this->setState('access', $this->getAccessFilter());

		unset($this->populateFilters['language']);
		$this->setState('language', $this->getLanguageFilter());

		parent::populateState($ordering, $direction);
	}
}