<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

// Load FOF if not already loaded
if (!defined('FOF40_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof40/include.php'))
{
	throw new RuntimeException('FOF 4.0 is not installed');
}

class Mod_docimport_categoriesInstallerScript extends FOF40\InstallScript\Module
{
	/**
	 * The modules's name, e.g. mod_foobar. Auto-filled from the class name.
	 *
	 * @var   string
	 */
	protected $moduleName = 'mod_docimport_categories';

}
