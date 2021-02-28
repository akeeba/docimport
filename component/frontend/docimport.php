<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

define('AKEEBA_COMMON_WRONGPHP', 1);
$minPHPVersion         = '7.2.0';
$recommendedPHPVersion = '7.4';
$softwareName          = 'Akeeba DocImport';

if (!require_once(JPATH_COMPONENT_ADMINISTRATOR . '/tmpl/ErrorPages/wrongphp.php'))
{
	return;
}

if (!defined('FOF40_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof40/include.php'))
{
	throw new RuntimeException('FOF 4.0 is not installed', 500);
}

FOF40\Container\Container::getInstance('com_docimport')->dispatcher->dispatch();