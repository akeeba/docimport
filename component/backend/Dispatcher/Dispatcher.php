<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Admin\Dispatcher;

use FOF30\Container\Container;
use FOF30\Dispatcher\Mixin\ViewAliases;

defined('_JEXEC') or die;

class Dispatcher extends \FOF30\Dispatcher\Dispatcher
{
	use ViewAliases {
		onBeforeDispatch as onBeforeDispatchViewAliases;
	}

	/** @var   string  The name of the default view, in case none is specified */
	public $defaultView = 'Categories';

	public function __construct(Container $container, array $config)
	{
		parent::__construct($container, $config);

		$this->viewNameAliases = [
			'cpanel'             => 'ControlPanel',
		];
	}

	public function onBeforeDispatch()
	{
		$this->onBeforeDispatchViewAliases();

		if (!$this->container->platform->authorise('core.manage', 'com_docimport'))
		{
			throw new \RuntimeException(\JText::_('JERROR_ALERTNOAUTHOR'), 404);
		}

		@include_once(JPATH_ADMINISTRATOR . '/components/com_docimport/version.php');

		if (!defined('DOCIMPORT_VERSION'))
		{
			define('DOCIMPORT_VERSION', 'dev');
			define('DOCIMPORT_DATE', date('Y-m-d'));
		}

		// Renderer options (0=none, 1=frontend, 2=backend, 3=both)
		$useFEF   = $this->container->params->get('load_fef', 3);
		$fefReset = $this->container->params->get('fef_reset', 3);

		if (!in_array($useFEF, [2,3]))
		{
			$this->container->rendererClass = '\\FOF30\\Render\\Joomla3';
		}

		$this->container->renderer->setOption('load_fef', in_array($useFEF, [2,3]));
		$this->container->renderer->setOption('fef_reset', in_array($fefReset, [2,3]));

		// Render submenus as drop-down navigation bars powered by Bootstrap
		$this->container->renderer->setOption('linkbar_style', 'classic');

		/** @var \Akeeba\DocImport\Admin\Model\ControlPanel $model */
		$model = $this->container->factory->model('ControlPanel')->tmpInstance();

		// Update the db structure if necessary (once per session at most)
		$lastVersion = $this->container->platform->getSessionVar('magicParamsUpdateVersion', null, 'com_docimport');

		if ($lastVersion != DOCIMPORT_VERSION)
		{
			$model->checkAndFixDatabase();
			$this->container->platform->setSessionVar('magicParamsUpdateVersion', DOCIMPORT_VERSION, 'com_docimport');
		}

		// Update magic parameters if necessary
		$model
			->updateMagicParameters();


		// Render submenus as drop-down navigation bars powered by Bootstrap
		//$this->container->renderer->setOption('linkbar_style', 'classic');

		// Load common CSS and JavaScript
		\JHtml::_('jquery.framework');
		$this->container->template->addCSS('media://com_docimport/css/backend.css', $this->container->mediaVersion);
	}
}
