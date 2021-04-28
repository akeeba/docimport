<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\DocImport\Site\View\Categories;

defined('_JEXEC') || die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
	protected $showPageHeading = false;

	protected $items;

	protected $pageHeading;

	public function display($tpl = null)
	{
		$this->items = $this->get('Items');

		$app = Factory::getApplication();

		// Check for errors.
		if (\count($errors = $this->get('Errors')))
		{
			$app->enqueueMessage($errors, 'error');

			return;
		}

		$this->prepareDocument();

		parent::display($tpl);
	}

	protected function prepareDocument()
	{
		/** @var SiteApplication $app */
		$app    = Factory::getApplication();
		$menu   = Factory::getApplication()->getMenu()->getActive();
		$params = $app->getParams();

		if ($menu)
		{
			$params->def('page_heading', $params->get('page_title', $menu->title));
		}
		else
		{
			$params->def('page_heading', Text::_($this->pageHeading));
		}

		$this->setDocumentTitle($params->get('page_title', $menu->title));

		$this->showPageHeading = $params->get('show_page_heading', 0) == 1;
		$this->pageHeading     = $params->get('page_heading');
	}

}