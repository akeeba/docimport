<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\View\Categories;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\DocImport\Site\Model\Categories;
use Akeeba\DocImport\Site\Model\Articles;
use FOF30\View\DataView\Html as BaseView;
use JFactory;

class Html extends BaseView
{
	/** @var   bool  Should I display page headings? */
	public $showPageHeading = false;

	/** @var   string  The page heading to display */
	public $pageHeading = '';

	/** @var   Articles  The index article of the category */
	public $index = null;

	/** @var   Categories  The record loaded (read, edit, add views) */
	protected $item = null;

	protected function onBeforeBrowse()
	{
		parent::onBeforeBrowse();

		$this->setupPageHeading();
	}

	protected function onBeforeRead()
	{
		parent::onBeforeRead();

		/** @var   Articles  $articles */
		$articles = $this->container->factory->model('Articles')->tmpInstance();

		// Look for an index article
		try
		{
			$this->index = $articles->category($this->item->getId())
						->slug('index')
						->enabled(1)
						->firstOrFail();
		}
		catch (\Exception $e)
		{
			$this->index = null;
			$this->items = $articles
				->clearState()
				->category($this->item->getId())
				->enabled(1)
				->filter_order('ordering')
				->filter_order('ASC')
				->limit(0)
				->limitstart(0)
				->get();
		}

		$this->setupPageHeading();
	}

	protected function setupPageHeading()
	{
		// Get the title of the active menu item, if present
		/** @var \JApplicationSite $app */
		$app       = JFactory::getApplication();
		$menus     = $app->getMenu();
		$menu      = $menus->getActive();
		$menuTitle = is_null($menu) ? '' : $menu->title;

		// Do I have to show page headings per menu item configuration?
		$pageParams            = $app->getParams();
		$this->showPageHeading = $pageParams->get('show_page_heading');

		if ($this->showPageHeading)
		{
			// Find our the heading text to display. If there's none, don't display the heading at all.
			$this->pageHeading     = $this->escape($pageParams->get('page_heading', $menuTitle));
			$this->showPageHeading = !empty($this->pageHeading);
		}
	}


}
