<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

// Load FOF if not already loaded
if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php'))
{
	throw new RuntimeException('This extension requires FOF 3.0.');
}

class Mod_docimport_searchInstallerScript extends FOF30\Utils\InstallScript\Module
{
	/**
	 * The modules's name, e.g. mod_foobar. Auto-filled from the class name.
	 *
	 * @var   string
	 */
	protected $moduleName = 'mod_docimport_search';

}
