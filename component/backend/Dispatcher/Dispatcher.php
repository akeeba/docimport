<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Admin\Dispatcher;

use FOF30\Container\Container;
use FOF30\Database\Installer;
use FOF30\Dispatcher\Mixin\ViewAliases;

defined('_JEXEC') or die;

class Dispatcher extends \FOF30\Dispatcher\Dispatcher
{
	use ViewAliases
	{
		onBeforeDispatch as onBeforeDispatchViewAliases;
	}

	/** @var   string  The name of the default view, in case none is specified */
	public $defaultView = 'Categories';

	public function __construct(Container $container, array $config)
	{
		parent::__construct($container, $config);

		$this->viewNameAliases = [
			'cpanel' => 'ControlPanel',
		];
	}

	public function onBeforeDispatch()
	{
		$this->checkAndFixDatabase();
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
		$useFEF   = in_array($this->container->params->get('load_fef', 3), [2, 3]);
		$fefReset = $useFEF && in_array($this->container->params->get('fef_reset', 3), [2, 3]);

		if (!$useFEF)
		{
			$this->container->rendererClass = '\\FOF30\\Render\\Joomla3';
		}

		$darkMode = $this->container->params->get('dark_mode_backend', -1);

		$customCss = 'media://com_docimport/css/backend.css';

		if ($darkMode != 0)
		{
			$customCss .= ', media://com_docimport/css/backend_dark.css';
		}

		$this->container->renderer->setOptions([
			'load_fef'      => $useFEF,
			'fef_reset'     => $fefReset,
			'fef_dark'      => $useFEF ? $darkMode : 0,
			'custom_css'    => $customCss,
			// Render submenus as drop-down navigation bars powered by Bootstrap
			'linkbar_style' => 'classic',
		]);

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
	}

	/**
	 * Checks the database for missing / outdated tables and runs the appropriate SQL scripts if necessary.
	 *
	 * @return  void
	 */
	private function checkAndFixDatabase()
	{
		$db          = $this->container->platform->getDbo();
		$dbInstaller = new Installer($db, JPATH_ADMINISTRATOR . '/components/' . $this->container->componentName . '/sql/xml');

		try
		{
			$dbInstaller->updateSchema();
		}
		catch (\Exception $e)
		{
		}
	}
}
