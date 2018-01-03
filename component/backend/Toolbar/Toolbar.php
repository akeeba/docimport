<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Admin\Toolbar;

use Akeeba\DocImport\Admin\Model\Articles;
use FOF30\Model\DataModel;
use JToolBarHelper;
use JText;

defined('_JEXEC') or die;

class Toolbar extends \FOF30\Toolbar\Toolbar
{
	public function onCategoriesBrowse()
	{
		parent::onBrowse();

		JToolBarHelper::divider();
		JToolBarHelper::preferences($this->container->componentName);
	}

	public function onArticlesBrowse()
	{
		$this->renderSubmenu();

		// Set toolbar title
		$subtitle_key = $this->container->componentName . '_TITLE_ARTICLES';
		JToolBarHelper::title(JText::_($this->container->componentName) . ' &ndash; <small>' . JText::_($subtitle_key) . '</small>', $this->container->bareComponentName);

		// Add toolbar buttons
		if ($this->perms->edit)
		{
			JToolBarHelper::editList();
		}

		if ($this->perms->editstate)
		{
			JToolBarHelper::divider();
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
		}

		if ($this->perms->delete)
		{
			JToolBarHelper::divider();
			JToolBarHelper::deleteList();
		}

		JToolBarHelper::divider();
		JToolBarHelper::preferences($this->container->componentName);
	}
}
