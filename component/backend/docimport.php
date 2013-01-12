<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

// Include the component versioning
require_once JPATH_COMPONENT_ADMINISTRATOR.'/version.php';

// Include FOF
if(!defined('FOF_INCLUDED')) {
	include_once JPATH_LIBRARIES.'/fof/include.php';
}

if(!defined('FOF_INCLUDED') || !class_exists('FOFForm', true)) {?>
<h1>Akeeba DocImport<sup>3</sup></h1>
<h2>Incomplete installation detected</h2>
<p>
	Please consult the documentation
</p>
<?php return; }

// PHP version check
if(defined('PHP_VERSION')) {
	$version = PHP_VERSION;
} elseif(function_exists('phpversion')) {
	$version = phpversion();
} else {
	$version = '5.0.0'; // all bets are off!
}
if(!version_compare($version, '5.3.0', '>=')) {?>
<h1>Akeeba DocImport<sup>3</sup></h1>
<h2>Incompatible PHP version</h2>
<p>
	You need PHP 5.3.0 or later to run this component
</p>
<?php return; }

// Dispatch
FOFDispatcher::getAnInstance('com_docimport')->dispatch();