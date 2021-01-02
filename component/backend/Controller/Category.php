<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Admin\Controller;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\DocImport\Admin\Model\Xsl;
use FOF30\Controller\DataController;

class Category extends DataController
{
	public function rebuild()
	{
		$url = 'index.php?option=com_docimport&view=categories';

		if (!class_exists('XSLTProcessor'))
		{
			$messageType = 'error';
			$message     = \JText::_('COM_DOCIMPORT_XSL_ERROR_NOEXTENSION');

			$this->setRedirect($url, $message, $messageType);

			return;
		}

		$message     = \JText::_('COM_DOCIMPORT_CATEGORIES_REBUILT');
		$messageType = null;

		/** @var Xsl $model */
		$model = $this->container->factory->model('Xsl')->tmpInstance();
		$id    = $this->input->getInt('id', 0);

		try
		{
			$model->processXML($id);
			$model->processFiles($id);
		}
		catch (\RuntimeException $e)
		{
			$messageType = 'error';
			$message     = $e->getMessage();
		}

		$this->setRedirect($url, $message, $messageType);
	}
}
