<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

use Akeeba\Component\DocImport\Administrator\Extension\DocImportComponent;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

// Load the language files
$app  = Factory::getApplication();
$lang = $app->getLanguage();
$lang->load('mod_docimport_search', JPATH_SITE);
$lang->load('com_docimport', JPATH_SITE);

if (!ComponentHelper::isEnabled('com_docimport'))
{
	return;
}

/** @var DocImportComponent $component */
$component  = $app->bootComponent('com_docimport');
/** @var \Joomla\CMS\Document\HtmlDocument $document */
$document   = $app->getDocument();
$mvcFactory = $component->getMVCFactory();

/** @var \Akeeba\Component\DocImport\Site\Model\ArticlesModel $model */
$model = $mvcFactory->createModel('Articles');
// This causes ArticlesModel::populateState to run. Don't remove!
$model->getState('catid');
// This forces the correct category into the model state
$model->setState('catid', $params->get('id', 0));
// Get the view and push the model to it.
/** @var \Akeeba\Component\DocImport\Site\View\Articles\HtmlView $view */
$view           = $mvcFactory->createView('Articles', '', 'html', [
	'base_path' => JPATH_SITE . '/components/com_docimport',
]);
$view->setModel($model, true);

// Load the asset declaration and attach the document to the view
$document->getWebAssetManager()->getRegistry()->addExtensionRegistryFile('com_docimport');
$view->document = $document;

// Render the view in the module's view template
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');

$layout = $params->get('layout', 'default');
$layoutFile = ModuleHelper::getLayoutPath($module->module, $layout);

require $layoutFile;