<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\Dispatcher;

defined('_JEXEC') or die;

class Dispatcher extends \FOF40\Dispatcher\Dispatcher
{
	/** @var   string  The name of the default view, in case none is specified */
	public $defaultView = 'Categories';

	public function onBeforeDispatch()
	{
		@include_once(JPATH_ADMINISTRATOR . '/components/com_docimport/version.php');

		if (!defined('DOCIMPORT_VERSION'))
		{
			define('DOCIMPORT_VERSION', 'dev');
			define('DOCIMPORT_DATE', date('Y-m-d'));
		}

		// Renderer options (0=none, 1=frontend, 2=backend, 3=both)
		$useFEF   = in_array($this->container->params->get('load_fef', 3), [1, 3]);
		$fefReset = $useFEF && in_array($this->container->params->get('fef_reset', 3), [1, 3]);

		if (!$useFEF)
		{
			$this->container->rendererClass = '\\FOF40\\Render\\Joomla3';
		}

		$darkMode = $this->container->params->get('dark_mode_frontend', -1);

		$customCss = 'media://com_docimport/css/frontend.css';

		if ($darkMode != 0)
		{
			$customCss .= ', media://com_docimport/css/frontend_dark.css';
		}

		$this->container->renderer->setOptions([
			'load_fef'      => $useFEF,
			'fef_reset'     => $fefReset,
			'fef_dark'      => $useFEF ? $darkMode : 0,
			'custom_css'    => $customCss,
			// Render submenus as drop-down navigation bars powered by Bootstrap
			'linkbar_style' => 'classic',
		]);
	}
}
