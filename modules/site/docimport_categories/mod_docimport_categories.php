<?php
/**
 * @package      akeebasubs
 * @copyright    Copyright (c)2011-2017 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license      GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 * @version      $Id$
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// no direct access
defined('_JEXEC') or die;

if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php'))
{
	throw new RuntimeException('FOF 3.0 is not installed', 500);
}

// Load the language files
$lang = JFactory::getLanguage();
$lang->load('mod_docimport_search', JPATH_SITE, 'en-GB', true);
$lang->load('mod_docimport_search', JPATH_SITE, null, true);
$lang->load('com_docimport', JPATH_SITE, 'en-GB', true);
$lang->load('com_docimport', JPATH_SITE, null, true);

$id = $params->get('id', 0);

$container = FOF30\Container\Container::getInstance('com_docimport', [
	'tempInstance' => true,
	'input'        => [
		'savestate' => 0,
		'option'    => 'com_docimport',
		'view'      => 'Category',
		'layout'    => 'item',
		'task'      => 'read',
	    'id'        => $id
	],
]);

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');

?>
<div id="mod-docimport-category-<?php echo $module->id ?>" class="mod-docimport-categories <?php echo $moduleclass_sfx ?>">
	<?php $container->dispatcher->dispatch(); ?>
</div>
