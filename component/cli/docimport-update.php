<?php
/**
 * @package   DocImport3
 * @copyright Copyright (c)2011-2017 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
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
 *
 *  --
 *
 *  Command-line script to schedule the documentation rebuild
 */

use \Joomla\Registry\Registry;

// Define ourselves as a parent file
define('_JEXEC', 1);

// Setup and import the base CLI script
$minphp = '5.4.0';
$curdir = __DIR__;

require_once __DIR__ . '/../components/com_docimport/Helper/Cli.php';

class AppDocupdate extends AkeebaCliBase
{
	/**
	 * The main entry point of the application
	 *
	 * @return  void
	 */
	public function execute()
	{
		// Load the language files
		$paths = [JPATH_ADMINISTRATOR, JPATH_ROOT];
		$jlang = JFactory::getLanguage();
		$jlang->load('com_docimport', $paths[0], 'en-GB', true);
		$jlang->load('com_docimport', $paths[1], 'en-GB', true);
		$jlang->load('com_docimport' . '.override', $paths[0], 'en-GB', true);
		$jlang->load('com_docimport' . '.override', $paths[1], 'en-GB', true);

		$debugmessage = '';

		if ($this->getOption('debug', -1, 'int') != -1)
		{
			if (function_exists('ini_set'))
			{
				ini_set('display_errors', 1);
			}

			if (function_exists('error_reporting'))
			{
				error_reporting(E_ALL);
			}

			$debugmessage = "*** DEBUG MODE ENABLED ***\n";
		}

		// Get basic information
		$version        = DOCIMPORT_VERSION;
		$date           = DOCIMPORT_DATE;
		$year           = gmdate('Y');
		$phpversion     = PHP_VERSION;
		$phpenvironment = PHP_SAPI;
		$memusage       = $this->memUsage();
		$jVersion       = JVERSION;
		$start_time     = time();

		echo <<<ENDBLOCK
Akeeba DocImport³ CLI $version ($date)
Copyright (C) 2010-$year Akeeba Ltd / Nicholas K. Dionysopoulos
-------------------------------------------------------------------------------
Akeeba DocImport is Free Software, distributed under the terms of the GNU
General Public License version 3 or, at your option, any later version.
This program comes with ABSOLUTELY NO WARRANTY as per sections 15 & 16 of the
license. See http://www.gnu.org/licenses/gpl-3.0.html for details.
-------------------------------------------------------------------------------
You are using Joomla! $jVersion on PHP $phpversion ($phpenvironment)
$debugmessage
Starting documentation category rebuild

Current memory usage: $memusage

ENDBLOCK;

		// Load Joomla! classes
		JLoader::import('joomla.filesystem.folder');
		JLoader::import('joomla.filesystem.file');
		JLoader::import('joomla.environment.request');
		JLoader::import('joomla.environment.uri');

		// Get the component container
		$container               = \FOF30\Container\Container::getInstance('com_docimport', [], 'admin');
		$container->factoryClass = '\\FOF30\\Factory\\SwitchFactory';

		// Force the server root URL
		$rootURL = $container->params->get('siteurl', '');

		if (!empty($rootURL))
		{
			$rootURL = rtrim($rootURL, '/') . '/';
			define('DOCIMPORT_SITEURL', $rootURL);
		}

		$rootPath = $container->params->get('sitepath', '');

		if (!empty($rootPath))
		{
			$rootPath = rtrim($rootPath, '/') . '/';
			define('DOCIMPORT_SITEPATH', $rootPath);
		}

		// Scan for any missing categories
		/** @var \Akeeba\DocImport\Admin\Model\Xsl $xslModel */
		$xslModel = $container->factory->model('Xsl')->tmpInstance();
		$xslModel->scanCategories();

		// List all categories
		/** @var \Akeeba\DocImport\Admin\Model\Categories $categoriesModel */
		$categoriesModel = $container->factory->model('Categories')->tmpInstance();

		$categories = $categoriesModel
			->limit(0)
			->limitstart(0)
			->enabled(1)
			->get();

		/** @var \Akeeba\DocImport\Admin\Model\Categories $cat */
		foreach ($categories as $cat)
		{
			$this->out("Processing \"$cat->title\"");

			/** @var \Akeeba\DocImport\Admin\Model\Xsl $model */
			$model = $xslModel->tmpInstance();

			try
			{
				$this->out("\tProcessing XML to HTML...");
				$model->processXML($cat->docimport_category_id);

				$this->out("\tGenerating articles...");
				$model->processFiles($cat->docimport_category_id);

				$this->out("\tSuccess!");
			}
			catch (\RuntimeException $e)
			{
				$this->out("\tFAILED: " . $e->getMessage());
			}
		}

		$this->out('');
		$this->out('Documentation processing finished after approximately ' . $this->timeAgo($start_time, time(), '', false));
		$this->out('');
		$this->out("Peak memory usage: " . $this->peakMemUsage());
	}
}

// Load the version file
require_once JPATH_ADMINISTRATOR . '/components/com_docimport/version.php';

// Instantiate and run the application
AkeebaCliBase::getInstance('AppDocupdate')->execute();
