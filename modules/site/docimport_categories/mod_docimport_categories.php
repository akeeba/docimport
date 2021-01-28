<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// no direct access
defined('_JEXEC') or die;

if (!defined('FOF40_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof40/include.php'))
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

$container = FOF40\Container\Container::getInstance('com_docimport', [
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
