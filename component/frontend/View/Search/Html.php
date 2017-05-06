<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2011-2017 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\View\Search;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\DocImport\Site\Model\Search;
use FOF30\View\View as BaseView;
use JHtml;
use JPagination;
use JText;

class Html extends BaseView
{
	/** @var   string[]  Support areas being searched */
	public $areas = [];

	/** @var   array  JHtml options for selecting support areas */
	public $areaOptions = [];

	/** @var   array  Search results */
	public $items = null;

	/** @var   int  Results offset */
	public $limitStart = 0;

	/** @var   JPagination  Pagination for search results */
	public $pagination;

	/** @var   string  The search query */
	public $search;

	/** @var   string  The header text to display above the search box. Set by the docimport_search module. */
	public $headerText;

	/** @var   string  The quick troubleshooter links to display below the search box. Set by the docimport_search module. */
	public $troubleshooterLinks;

	protected function onBeforeMain()
	{
		/**
		 * Load Javascript. The document ready does some non-obvious things:
		 * – The labelAllSections needs to be translated, hence we push it here into the object
		 * – We need to trigger sectionsChange to display the correct areas we are searching in
		 * – We need the timeout before adding the collapse class because Chosen needs to kick in before it's hidden.
		 *   Otherwise Chosen can't figure out the width of the containing DIV and renders the section box too narrow.
		 */
		$allAreasLabel = JText::_('COM_DOCIMPORT_SEARCH_LBL_ALLAREAS', true);
		$js = <<< JS

akeeba.jQuery(document).ready(function()
{
	akeeba.DocImport.Search.labelAllSections = "$allAreasLabel";
	akeeba.DocImport.Search.sectionsChange();
	setTimeout(function(){
		akeeba.jQuery('#dius-searchutils-collapsible').addClass('collapse');
	}, 10);
});

JS;

		$this->addJavascriptFile('media://com_docimport/js/search.js');
		$this->addJavascriptInline($js);

		$this->addCssFile('media://com_docimport/css/search.css');

		/** @var Search $model */
		$model = $this->getModel();

		// Get all possible support search sections in JHtml-compatible format.
		$allAreas = $model->getCategoriesConfiguration()->getAllAreaTitles();
		array_unshift($allAreas, [
			'text'  => JText::_('COM_DOCIMPORT_SEARCH_LBL_ALLAREAS'),
			'value' => '*'
		]);

		// Push everything to the view
		$this->items       = $model->searchResults;
		$this->pagination  = $model->getPagination();
		$this->search      = $model->getState('search', '', 'string');
		$this->limitStart  = $model->getState('start', 0, 'int');
		$this->areas       = $model->getState('areas', [], 'array');
		$this->areaOptions = array_map(function ($area) {
			return JHtml::_('select.option', $area['value'], $area['text']);
		}, $allAreas);

		// Push search parameters from the module configuration
		if ($this->container->offsetExists('_search_params'))
		{
			$this->headerText = $this->container->_search_params['headerText'];
			$this->troubleshooterLinks = $this->container->_search_params['troubleshooterLinks'];
		}
	}
}