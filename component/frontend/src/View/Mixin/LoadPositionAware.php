<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\DocImport\Site\View\Mixin;

defined('_JEXEC') || die;

use Joomla\CMS\Helper\ModuleHelper;

trait LoadPositionAware
{
	public function loadPosition(string $position, int $style = -2): string
	{
		try
		{
			$renderer = $this->document->loadRenderer('module');

			return implode('', array_map(function ($mod) use ($renderer, $style) {
				$renderer->render($mod, ['style' => $style]);
			}, ModuleHelper::getModules($position)));
		}
		catch (\Throwable $exc)
		{
			return '';
		}
	}

}