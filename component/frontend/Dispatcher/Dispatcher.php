<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\Dispatcher;

defined('_JEXEC') or die;

class Dispatcher extends \FOF30\Dispatcher\Dispatcher
{
	/** @var   string  The name of the default view, in case none is specified */
	public $defaultView = 'Categories';

	public function onBeforeDispatch()
	{
		if (!@include_once(JPATH_ADMINISTRATOR . '/components/com_docimport/version.php'))
		{
			define('DOCIMPORT_VERSION', 'dev');
			define('DOCIMPORT_DATE', date('Y-m-d'));
		}

		// Load common CSS and JavaScript
		\JHtml::_('jquery.framework');
		$this->container->template->addCSS('media://com_docimport/css/frontend.css', $this->container->mediaVersion);
	}
}
