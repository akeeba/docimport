<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\DocImport\Administrator\Controller;

defined('_JEXEC') || die;

use Akeeba\Component\DocImport\Administrator\Model\XslModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;

class CategoryController extends FormController
{
	protected $text_prefix = 'COM_DOCIMPORT_CATEGORY';

	public function rebuild()
	{
		$url = Route::_('index.php?option=com_docimport&view=categories');

		if (!class_exists('XSLTProcessor'))
		{
			$messageType = 'error';
			$message     = Text::_('COM_DOCIMPORT_XSL_ERROR_NOEXTENSION');

			$this->setRedirect($url, $message, $messageType);

			return;
		}

		$message     = Text::_('COM_DOCIMPORT_CATEGORIES_REBUILT');
		$messageType = null;

		/** @var XslModel $model */
		$model = $this->getModel('Xsl', 'Administrator');
		$id    = $this->input->getInt('docimport_category_id', 0);

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