<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\DocImport\Site\Model\Mixin;

defined('_JEXEC') || die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;

trait FrontendFilterAware
{
	protected function getLanguageFilter(): array
	{
		$ret = ['*'];

		if (!Multilanguage::isEnabled())
		{
			return $ret;
		}

		$ret[] = Factory::getApplication()->getLanguage()->getTag();

		return $ret;
	}

	protected function getAccessFilter(): array
	{
		$user = Factory::getApplication()->getIdentity() ?: Factory::getUser();

		return $user->getAuthorisedViewLevels();
	}
}