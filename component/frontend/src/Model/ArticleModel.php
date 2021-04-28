<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\DocImport\Site\Model;

defined('_JEXEC') || die;

use Akeeba\Component\DocImport\Site\Model\Mixin\FrontendFilterAware;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

class ArticleModel extends ItemModel
{
	use FrontendFilterAware;

	protected $_context = 'com_docimport.article';

	/**
	 * @inheritDoc
	 */
	public function getItem($pk = null)
	{
		$pk = (int) ($pk ?: $this->getState('article.id'));

		if ($this->_item === null)
		{
			$this->_item = [];
		}

		if (isset($this->_item[$pk]))
		{
			return $this->_item[$pk];
		}

		try
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true)
				->select([
					$db->quoteName('a.*'),
				])
				->from($db->quoteName('#__docimport_articles', 'a'))
				->join('LEFT', $db->quoteName('#__docimport_categories', 'c'), $db->quoteName('c.docimport_category_id') . ' = ' . $db->quoteName('a.docimport_category_id'))
				->where($db->quoteName('a.docimport_article_id') . ' = :pk')
				->where($db->quoteName('a.enabled') . ' = ' . $db->quote('1'))
				->where($db->quoteName('c.enabled') . ' = ' . $db->quote('1'))
				->whereIn($db->quoteName('access'), $this->getAccessFilter())
				->whereIn($db->quoteName('c.language'), $this->getLanguageFilter(), ParameterType::STRING)
				->bind(':pk', $pk)
			;

			$data = $db->setQuery($query)->loadObject();

			if (empty($data))
			{
				throw new \Exception(Text::_('COM_DOCIMPORT_ERR_NOTFOUND'), 404);
			}

			$this->_item[$pk] = $data;
		}
		catch (\Exception $e)
		{
			if ($e->getCode() == 404)
			{
				throw $e;
			}
			else
			{
				$this->setError($e);
				$this->_item[$pk] = false;
			}
		}

		return $this->_item[$pk];
	}

	protected function populateState()
	{
		$app = Factory::getApplication();

		// Load state from the request.
		$pk = $app->input->getInt('id');
		$this->setState('article.id', $pk);
	}
}