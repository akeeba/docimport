<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\DocImport\Site\Model;

defined('_JEXEC') || die;

use Akeeba\Component\DocImport\Administrator\Model\CategoriesModel as AdminCategoriesModel;
use Akeeba\Component\DocImport\Site\Model\Mixin\FrontendFilterAware;

class CategoriesModel extends AdminCategoriesModel
{
	use FrontendFilterAware;

	protected function populateState($ordering = null, $direction = null)
	{
		// In the front-end I force-set these three filters, regardless of the request and/or user state
		unset($this->populateFilters['enabled']);
		$this->setState('enabled', 1);

		unset($this->populateFilters['access']);
		$this->setState('access', $this->getAccessFilter());

		unset($this->populateFilters['language']);
		$this->setState('language', $this->getLanguageFilter());

		parent::populateState($ordering, $direction);
	}
}