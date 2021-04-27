<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\DocImport\Administrator\Controller;

defined('_JEXEC') || die;

use Joomla\CMS\MVC\Controller\AdminController;

class ArticlesController extends AdminController
{
	protected $text_prefix = 'COM_DOCIMPORT_ARTICLES';

	public function getModel($name = 'Category', $prefix = 'Administrator', $config = ['ignore_request' => true])
	{
		return parent::getModel($name, $prefix, $config);
	}
}