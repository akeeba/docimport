<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\DocImport\Site\View\Article;

defined('_JEXEC') || die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
	public $showPageHeading = false;

	public $pageHeading = '';

	public $item;

	public $contentPrepare = false;

	public function display($tpl = null)
	{
		$this->item = $this->get('item');

		$app = Factory::getApplication();

		// Check for errors.
		if (\count($errors = $this->get('Errors')))
		{
			foreach ($errors as $error)
			{
				$app->enqueueMessage($error, 'error');
			}

			return;
		}

		$this->contentPrepare = $this->item->process_plugins == 1;

		$this->prepareDocument();

		parent::display($tpl);
	}

	protected function prepareDocument()
	{
		HTMLHelper::_('script', '//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.2.0/highlight.min.js');
		HTMLHelper::_('stylesheet', '//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.2.0/styles/default.min.css');

		$js = <<< JS
	(function ($) {
		$(document).ready(function () {
			$('pre.programlisting').each(function (i, e) {
				language = $(e).attr('data-language');
				content = $(e).text();

				if (!language) {
					return;
				}

				result = hljs.highlight(language, content);
				$(e).html(result.value);
			});
		});
	})(window.jQuery);
JS;


		$this->document->getWebAssetManager()->addInlineScript($js);

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