<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\View\Article;

// Protect from unauthorized access
defined('_JEXEC') or die();

use FOF30\View\DataView\Html as BaseView;

class Html extends BaseView
{
	/** @var   bool  Should I display page headings? */
	public $showPageHeading = false;

	/** @var   string  The page heading to display */
	public $pageHeading = '';

	protected function onBeforeRead()
	{
		parent::onBeforeRead();

		$document = $this->container->platform->getDocument();

		// Load highlight.js
		$this->addJavascriptFile('//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.2.0/highlight.min.js', 'text/javascript', false, false);
		$this->addCssFile('//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.2.0/styles/default.min.css');

		$document->setTitle($this->item->category->title . ' :: ' . $this->item->title);

		$this->contentPrepare = $this->item->category->process_plugins;

		$this->setupPageHeading();
	}

	protected function setupPageHeading()
	{
		// Get the title of the active menu item, if present
		/** @var \JApplicationSite $app */
		$app       = \JFactory::getApplication();
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
