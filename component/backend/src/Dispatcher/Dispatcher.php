<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\DocImport\Administrator\Dispatcher;

defined('_JEXEC') or die;

use Akeeba\Component\DocImport\Administrator\Dispatcher\Mixin\TriggerEvent;
use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Throwable;

class Dispatcher extends ComponentDispatcher
{
	use TriggerEvent;

	protected $defaultController = 'categories';

	public function dispatch()
	{
		// Check the minimum supported PHP version
		$minPHPVersion = '7.2.0';
		$softwareName  = 'Akeeba DocImport<sup>3</sup>';

		if (!@require_once __DIR__ . '/../../tmpl/common/wrongphp.php')
		{
			return;
		}

		$jLang = $this->app->getLanguage();
		$jLang->load($this->option, JPATH_ADMINISTRATOR, null, true, true);
		$jLang->load($this->option, JPATH_SITE, null, true, true);

		try
		{
			// Apply the view and controller from the request, falling back to the default view/controller if necessary
			$this->applyViewAndController();

			// Dispatch the component
			$this->triggerEvent('onBeforeDispatch');

			parent::dispatch();

			// This will only execute if there is no redirection set by the Controller
			$this->triggerEvent('onAfterDispatch');
		}
		catch (Throwable $e)
		{
			$title = 'Akeeba DocImport<sup>3</sup>';
			$isPro = false;

			if (!(include_once __DIR__ . '/../../tmpl/common/errorhandler.php'))
			{
				throw $e;
			}
		}
	}

	private function applyViewAndController(): void
	{
		// Handle a custom default controller name
		$view       = $this->input->getCmd('view', $this->defaultController);
		$controller = $this->input->getCmd('controller', $view);
		$task       = $this->input->getCmd('task', 'main');

		// Check for a controller.task command.
		if (strpos($task, '.') !== false)
		{
			// Explode the controller.task command.
			[$controller, $task] = explode('.', $task);
		}

		$this->input->set('view', $controller);
		$this->input->set('controller', $controller);
		$this->input->set('task', $task);
	}

}