<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
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

		// Renderer options (0=none, 1=frontend, 2=backend, 3=both)
		$useFEF   = $this->container->params->get('load_fef', 3);
		$fefReset = $this->container->params->get('fef_reset', 3);

		if (!in_array($useFEF, [1,3]))
		{
			$this->container->rendererClass = '\\FOF30\\Render\\Joomla3';
		}

		$this->container->renderer->setOption('load_fef', in_array($useFEF, [1,3]));
		$this->container->renderer->setOption('fef_reset', in_array($fefReset, [1,3]));
		$this->container->renderer->setOption('linkbar_style', 'classic');


		// Load common CSS and JavaScript
		\JHtml::_('jquery.framework');
		$this->container->template->addCSS('media://com_docimport/css/frontend.css', $this->container->mediaVersion);
	}
}
