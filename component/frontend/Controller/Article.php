<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\Controller;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\DocImport\Admin\Model\Articles;
use FOF30\Controller\DataController;
use FOF30\Controller\Exception\ItemNotFound;

class Article extends DataController
{
	public function onBeforeRead()
	{
		/** @var Articles $article */
		$article = $this->getModel()->savestate(false);

		// If there is no record loaded, try loading a record based on the id passed in the input object
		if (!$article->getId())
		{
			$ids = $this->getIDsFromRequest($article, true);

			if ($article->getId() != reset($ids))
			{
				throw new ItemNotFound(\JText::_('COM_DOCIMPORT_ERR_NOTFOUND'), 404);
			}
		}
		
		// Is the category enabled?
		if (!$article->enabled || !$article->category->enabled)
		{
			return false;
		}

		// Is the category access within those allowed to our user?
		$views = $this->container->platform->getUser()->getAuthorisedViewLevels();

		return in_array($article->category->access, $views);
	}

	protected function getCrudTask()
	{
		$task = parent::getCrudTask();

		if ($task == 'edit')
		{
			$task = 'read';
		}

		return $task;
	}
}
