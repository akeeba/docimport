<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Admin\Model;

defined('_JEXEC') or die;

use FOF30\Database\Installer;
use FOF30\Model\Model;
use JRegistry;
use JFactory;

class ControlPanel extends Model
{
	/**
	 * Checks the database for missing / outdated tables using the $dbChecks
	 * data and runs the appropriate SQL scripts if necessary.
	 *
	 * @return  $this
	 */
	public function checkAndFixDatabase()
	{
		$db = $this->container->platform->getDbo();

		$dbInstaller = new Installer($db, JPATH_ADMINISTRATOR . '/components/com_docimport/sql/xml');
		$dbInstaller->updateSchema();

		return $this;
	}

	/**
	 * Save some magic variables we need
	 *
	 * @return  $this
	 */
	public function updateMagicParameters()
	{
		$params = $this->container->params;

		$siteURL_stored = $params->get('siteurl', '');
		$siteURL_target = str_replace('/administrator', '', \JUri::base());

		$sitePath_stored = $params->get('sitepath', '');
		$sitePath_target = str_replace('/administrator', '', \JUri::base(true));

		if (($siteURL_target != $siteURL_stored) || ($sitePath_target != $sitePath_stored))
		{
			$params->set('siteurl', $siteURL_target);
			$params->set('sitepath', $sitePath_target);
			$params->save();
		}

		return $this;
	}

}
