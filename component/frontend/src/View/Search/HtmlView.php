<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\DocImport\Site\View\Search;

defined('_JEXEC') || die;

use Akeeba\Component\DocImport\Site\Model\SearchModel;
use Akeeba\Component\DocImport\Site\View\Mixin\LoadPositionAware;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;

class HtmlView extends BaseHtmlView
{
	use LoadPositionAware;

	/** @var   string[]  Support areas being searched */
	public $areas = [];

	/** @var   array  JHtml options for selecting support areas */
	public $areaOptions = [];

	/** @var   array  Search results */
	public $items = null;

	/** @var   int  Results offset */
	public $limitStart = 0;

	/** @var   Pagination  Pagination for search results */
	public $pagination;

	/** @var   string  The search query */
	public $search;

	/** @var   string  The header text to display above the search box. Set by the docimport_search module. */
	public $headerText;

	/** @var   string  The quick troubleshooter links to display below the search box. Set by the docimport_search module. */
	public $troubleshooterLinks;

	/** @var array Used by the module */
	public $searchParams = [];

	public function display($tpl = null)
	{
		$this->document->getWebAssetManager()
			->useStyle('com_docimport.frontend')
			->useScript('com_docimport.search');

		/** @var SearchModel $model */
		$model = $this->getModel();

		$allAreas = $model->getCategoriesConfiguration()->getAllAreaTitles();
		array_unshift($allAreas, [
			'text'  => Text::_('COM_DOCIMPORT_SEARCH_LBL_ALLAREAS'),
			'value' => '*',
		]);

		// Push everything to the view
		$this->items       = $model->searchResults;
		$this->pagination  = $model->getPagination();
		$this->search      = $model->getState('search', '');
		$this->limitStart  = (int) $model->getState('start', 0);
		$this->areas       = $model->getState('areas', []);
		$this->areaOptions = array_map(function ($area) {
			return HTMLHelper::_('select.option', $area['value'], $area['text']);
		}, $allAreas);

		// Push search parameters from the module configuration
		$this->headerText          = $this->searchParams['headerText'] ?? '';
		$this->troubleshooterLinks = $this->searchParams['troubleshooterLinks'] ?? [];

		// Push translations to the frontend
		Text::script('COM_DOCIMPORT_SEARCH_LBL_ALLAREAS', true);

		parent::display($tpl);
	}
}