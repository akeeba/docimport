<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2011-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 *  @license GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\Controller;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\DocImport\Admin\Model\Articles;
use FOF30\Controller\DataController;
use FOF30\Controller\Exception\ItemNotFound;
use Joomla\Registry\Registry;

class Category extends DataController
{
	public function onBeforeBrowse()
	{
		$this->getModel()->filter_order('ordering')->filter_order_Dir('ASC')->limit(0)->limitstart(0);
	}

	protected function getCrudTask()
	{
		$id = $this->input->getInt('id', 0);
		$catid = $this->input->getInt('catid', 0);

		$menuItem = \JFactory::getApplication()->getMenu()->getActive();

		if (is_object($menuItem))
		{
			if ($menuItem->params instanceof Registry)
			{
				$catid = $menuItem->params->get('catid', $catid);
			}
		}

		if (!$id && $catid)
		{
			$this->input->set('id', $catid);
		}

		$task = parent::getCrudTask();

		if ($task == 'edit')
		{
			$task = 'read';
		}

		return $task;
	}
}
