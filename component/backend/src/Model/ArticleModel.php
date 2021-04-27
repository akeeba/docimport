<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\DocImport\Administrator\Model;

defined('_JEXEC') || die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;

class ArticleModel extends AdminModel
{
	public function __construct($config = [], MVCFactoryInterface $factory = null, FormFactoryInterface $formFactory = null)
	{
		/**
		 * Well, Joomla 4 core developers seem to be writing buggy a.f. code. The onContentChangeState handler of a core
		 * plugin ASSUMES that if the context contains the word "article" it must be com_content (an idiotic assumption,
		 * since the context contains, dunno, THE COMPONENT NAME?!!!) and generates invalid SQL for our extension. It
		 * looks like something related to workflows but I won't go into debugging THAT.
		 *
		 * Therefore I need to tell the AdminModel code to NOT use onContentChangeState. I can't. I have to tell it to
		 * use something different. So I am using something which describes precisely my problem.
		 */
		$config['event_change_state'] = 'onPleaseStopBreakingMyCodeWithBadlyWrittenCorePluginsForCryingOutLoud';

		parent::__construct($config, $factory, $formFactory);
	}


	public function getForm($data = [], $loadData = true)
	{
		return $this->loadForm(
			'com_docimport.article',
			'article',
			[
				'control'   => 'jform',
				'load_data' => $loadData,
			]
		) ?: false;
	}

	protected function loadFormData()
	{
		$app  = Factory::getApplication();
		$data = $app->getUserState('com_docimport.edit.article.data', []);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		$this->preprocessData('com_docimport.article', $data);

		return $data;
	}

	protected function prepareTable($table)
	{
		$date = Factory::getDate();
		$user = Factory::getApplication()->getIdentity() ?: Factory::getUser();

		if (empty($table->getId()))
		{
			// Set the values
			$table->created_on = $date->toSql();
			$table->created_by = $user->id;
		}
		else
		{
			// Set the values
			$table->modified_on = $date->toSql();
			$table->modified_by = $user->id;
		}
	}
}