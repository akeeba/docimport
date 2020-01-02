<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();
?>

The Common folder
================================================================================

This folder contains view templates and view template fragments which are used throughout the component. Generally
speaking, we have the following files here:

* browse.blade.php  A prototype for Browse views. Override its sections to customize when replacing default.form.xml.
* edit.blade.php    A prototype for Edit / Add views. Override its sections to customize when replacing form.form.xml.

If you want to do serious changes to the formatting of the component's backend you will need to override these files
using standard Joomla template overrides. The target folder for your overridden files is
administrator/templates/YOUR_TEMPLATE/html/com_ats/Common
