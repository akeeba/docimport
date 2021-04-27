<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\DocImport\Administrator\Controller;

defined('_JEXEC') || die;

use Akeeba\Component\DocImport\Administrator\Model\XslModel;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;

class CategoriesController extends AdminController
{
	protected $text_prefix = 'COM_DOCIMPORT_CATEGORIES';

	public function getModel($name = 'Category', $prefix = 'Administrator', $config = ['ignore_request' => true])
	{
		return parent::getModel($name, $prefix, $config);
	}

	public function scan()
	{
		$url = Route::_('index.php?option=com_docimport&view=categories');

		/** @var XslModel $model */
		$model = $this->getModel('Xsl', 'Administrator');
		$model->scanCategories();

		$this->setRedirect($url);
	}
}