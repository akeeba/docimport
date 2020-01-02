<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\Controller;

// Protect from unauthorized access
defined('_JEXEC') or die();

use FOF30\Controller\Controller;

class Search extends Controller
{
	public function main()
	{
		/** @var \Akeeba\DocImport\Site\Model\Search $model */
		$model = $this->getModel()->savestate(1)->setIgnoreRequest(false);

		// Get the limit start from the GET query. If it's defined we have hit a pagination link which does NOT submit
		// a search query and support areas. Also, JOOMLA USES THE QUERY STRING PARAMETER limitstart FOR THE "Start"
		// LINK IN THE TOOLBAR AND start FOR EVERYTHING ELSE. Inconsistency FTW.
		$limitStart = $this->input->get->getInt('start', null);

		// Because Joomla! and consistency are at odds(see above)
		if (is_null($limitStart))
		{
			$limitStart = $this->input->get->getInt('limitstart', null);
		}

		// If the limit start is defined in the request we should retrieve default values from the model's saved state.
		// Otherwise we use the hard-coded defaults.
		$defaultLimit  = !is_null($limitStart) ? $model->getState('limit', 10) : 10;
		$defaultSearch = !is_null($limitStart) ? $model->getState('search', '') : '';
		$defaultAreas  = !is_null($limitStart) ? $model->getState('areas', []) : [];
		$defaultReturn = !is_null($limitStart) ? $model->getState('returnurl', '') : '';

		// If "start" wasn't part of the GET query maybe it's in the POST request instead?
		if (is_null($limitStart))
		{
			$limitStart = $this->input->post->getInt('start', null);

			// Because Joomla! and consistency are at odds(see above)
			if (is_null($limitStart))
			{
				$limitStart = $this->input->post->getInt('limitstart', null);
			}
		}

		// Final sanity checks for limit start. It needs to be numeric and non-zero.
		$limitStart = is_null($limitStart) ? 0 : (int)$limitStart;
		$limitStart = max(0, $limitStart);

		// Make sure the limit is always between 5 and 100 (prevents abuse)
		$limit = $this->input->post->getInt('limit', $defaultLimit);
		$limit = max($limit, 5);
		$limit = min($limit, 100);

		// Get query string parameters
		$supportAreas = $this->input->post->get('areas', $defaultAreas, 'array');
		$returnURL    = $this->input->post->getBase64('returnurl', $defaultReturn);
		// $search is read from model state (if the query string parameter "start" is defined in the query)
		$search = $this->input->post->getString('search', $defaultSearch);
		// whereas $rawSearch is ALWAYS read from the request
		$rawSearch = $this->input->post->getString('search', null);

		// If a search query was submitted through POST make sure there's a CSRF token (prevents abuse)
		if (!is_null($rawSearch))
		{
			$this->csrfProtection();
		}

		// Set the model state and perform the actual search
		$model->setState('search', $search);
		$model->setState('areas', $supportAreas);
		$model->setState('limit', $limit);
		$model->setState('start', $limitStart);
		$model->setState('returnURL', $returnURL);

		if ($search)
		{
			$model->produceSearchResults();
		}

		// Render the view (non-cacheable)
		$this->display(false);
	}
}
