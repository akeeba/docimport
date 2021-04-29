<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\DocImport\Administrator\View\Categories;

defined('_JEXEC') || die;

use Akeeba\Component\DocImport\Administrator\Model\CategoriesModel;
use Akeeba\Component\DocImport\Administrator\View\Mixin\LoadAnyTemplate;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

class HtmlView extends BaseHtmlView
{
	use LoadAnyTemplate;

	/**
	 * The search tools form
	 *
	 * @var    Form
	 * @since  1.6
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var    array
	 * @since  1.6
	 */
	public $activeFilters = [];

	/**
	 * An array of items
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $items = [];

	/**
	 * The pagination object
	 *
	 * @var    Pagination
	 * @since  1.6
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var    Registry
	 * @since  1.6
	 */
	protected $state;

	public function display($tpl = null): void
	{
		$this->document->getWebAssetManager()
			->useStyle('com_docimport.backend');

		/** @var CategoriesModel $model */
		$model               = $this->getModel();
		$this->items         = $model->getItems();
		$this->pagination    = $model->getPagination();
		$this->state         = $model->getState();
		$this->filterForm    = $model->getFilterForm();
		$this->activeFilters = $model->getActiveFilters();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	protected function addToolbar(): void
	{
		$user = Factory::getApplication()->getIdentity() ?: Factory::getUser();

		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		ToolbarHelper::title(Text::_('COM_DOCIMPORT_TITLE_CATEGORIES'), 'docimport');

		$canCreate    = $user->authorise('core.create', 'com_docimport');
		$canDelete    = $user->authorise('core.delete', 'com_docimport');
		$canEditState = $user->authorise('core.edit.state', 'com_docimport');

		if ($canCreate)
		{
			ToolbarHelper::addNew('categories.add');
		}

		if ($canDelete || $canEditState)
		{
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('icon-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			if ($canEditState)
			{
				$childBar->publish('categories.publish')
					->icon('fa fa-check-circle')
					->text('JTOOLBAR_PUBLISH')
					->listCheck(true);

				$childBar->unpublish('categories.unpublish')
					->icon('fa fa-times-circle')
					->text('JTOOLBAR_UNPUBLISH')
					->listCheck(true);

				$childBar->checkin('categories.checkin')->listCheck(true);
			}

			if ($canDelete)
			{
				$childBar->delete('categories.delete')
					->message('JGLOBAL_CONFIRM_DELETE')
					->listCheck(true);
			}
		}

		ToolbarHelper::link(
			Route::_('index.php?option=com_docimport&view=articles'),
			'COM_DOCIMPORT_TITLE_ARTICLES',
			'copy'
		);

		ToolbarHelper::link(
			Route::_('index.php?option=com_docimport&task=categories.scan'),
			'COM_DOCIMPORT_CATEGORY_SCAN',
			'search'
		);

		ToolbarHelper::preferences('com_docimport');
	}
}