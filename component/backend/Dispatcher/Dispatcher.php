<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
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

		if (!@include_once(JPATH_ADMINISTRATOR . '/components/com_docimport/version.php'))
		{
			define('DOCIMPORT_VERSION', 'dev');
			define('DOCIMPORT_DATE', date('Y-m-d'));
		}

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
