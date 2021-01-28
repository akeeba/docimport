<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// no direct access
use Joomla\CMS\Installer\Adapter\ComponentAdapter;

defined('_JEXEC') or die();

// Load FOF if not already loaded
if (!defined('FOF40_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof40/include.php'))
{
	throw new RuntimeException('This component requires FOF 3.0.');
}

class Com_DocimportInstallerScript extends \FOF40\InstallScript\Component
{
	/**
	 * The component's name
	 *
	 * @var   string
	 */
	public $componentName = 'com_docimport';

	/**
	 * The title of the component (printed on installation and uninstallation messages)
	 *
	 * @var string
	 */
	protected $componentTitle = 'DocImport<sup>3</sup>';

	/**
	 * The minimum PHP version required to install this extension
	 *
	 * @var   string
	 */
	protected $minimumPHPVersion = '5.6.0';

	/**
	 * The minimum Joomla! version required to install this extension
	 *
	 * @var   string
	 */
	protected $minimumJoomlaVersion = '3.8.0';

	/**
	 * Obsolete files and folders to remove from both paid and free releases. This is used when you refactor code and
	 * some files inevitably become obsolete and need to be removed.
	 *
	 * @var   array
	 */
	protected $removeFilesAllVersions = [
		'files'   => [
			// Obsolete CLI script
			'cli/docimport-update.php',

			// Obsolete update information
			'cache/com_docimport.updates.php',
			'cache/com_docimport.updates.ini',
			'administrator/components/com_docimport/controllers/urls.php',
			'administrator/components/com_docimport/helpers/jsonlib.php',
			'administrator/components/com_docimport/models/urls.php',
			'administrator/cache/com_docimport.updates.php',
			'administrator/cache/com_docimport.updates.ini',
			'components/com_docimport/controllers/article.php',

			// Upgrade to FOF 3
			'cli/docimport-upgrade.php',
			'administrator/components/com_docimport/dispatcher.php',
			'administrator/components/com_docimport/toolbar.php',
			'components/com_docimport/dispatcher.php',

			// Upgrade to FEF
			'administrator/components/com_docimport/View/eaccelerator.php',

			// CLI helper moved to FOF
			'components/com_docimport/Helper/Cli.php',

			// Obsolete CSS files
			'media/com_docimport/css/backend.min.css',
			'media/com_docimport/css/frontend.min.css',
			'media/com_docimport/css/search.min.css',

			'administrator/components/com_docimport/ViewTemplates/Common/browse.blade.php',
			'administrator/components/com_docimport/ViewTemplates/Common/form.blade.php',
		],
		'folders' => [
			'administrator/components/com_docimport/controllers',
			'administrator/components/com_docimport/models',
			'administrator/components/com_docimport/helpers',
			'administrator/components/com_docimport/tables',
			'administrator/components/com_docimport/views/article',
			'administrator/components/com_docimport/views/categories',
			'administrator/components/com_docimport/views/category',

			// Upgrade to FEF
			'administrator/components/com_docimport/View/Articles/tmpl',
			'administrator/components/com_docimport/View/Categories/tmpl',
			'components/com_docimport/View/Article/tmpl',
			'components/com_docimport/View/Categories/tmpl',
		],
	];

	public function preflight(string $type, ComponentAdapter $parent): bool
	{
		if (parent::preflight($type, $parent) === false)
		{
			return false;
		}

		if (!class_exists('XSLTProcessor'))
		{
			$msg = "<p>You need PHP the PHP XSL extension to install this component.</p>";

			$this->log($msg);

			return false;

		}

		return true;
	}

	public function postflight(string $type, ComponentAdapter $parent): void
	{
		// Remove the update sites for this component on installation.
		$this->removeObsoleteUpdateSites($parent);

		// Call the parent method
		parent::postflight($type, $parent);
	}

	/**
	 * Renders the post-installation message
	 */
	protected function renderPostInstallation(ComponentAdapter $parent): void
	{
		$this->warnAboutJSNPowerAdmin();
		?>
		<h1>Akeeba DocImport</h1>

		<img src="../media/com_docimport/images/docimport-48.png" width="48" height="48" alt="Akeeba DocImport"
		     align="left" />
		<h2 style="font-size: 14pt; font-weight: bold; padding: 0; margin: 0 0 0.5em;">&nbsp;Welcome to Akeeba
			DocImport!</h2>
		<span>
			The easiest way to provide up-to-date documentation
		</span>
		<?php
	}

	protected function renderPostUninstallation(ComponentAdapter $parent): void
	{
		?>
		<h2 style="font-size: 14pt; font-weight: black; padding: 0; margin: 0 0 0.5em;">&nbsp;Akeeba DocImport
			Uninstallation</h2>
		<p>We are sorry that you decided to uninstall Akeeba DocImport.</p>

		<?php
	}

	/**
	 * Removes obsolete update sites created for the component (we are no longer providing update; also, we are now
	 * using the "package" extension type).
	 *
	 * @param   JInstallerAdapterComponent  $parent  The parent installer
	 */
	protected function removeObsoleteUpdateSites($parent)
	{
		$db = $parent->getParent()->getDbo();

		$query = $db->getQuery(true)
			->select($db->qn('extension_id'))
			->from($db->qn('#__extensions'))
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->where($db->qn('name') . ' = ' . $db->q($this->componentName));
		$db->setQuery($query);
		$extensionId = $db->loadResult();

		if (!$extensionId)
		{
			return;
		}

		$query = $db->getQuery(true)
			->select($db->qn('update_site_id'))
			->from($db->qn('#__update_sites_extensions'))
			->where($db->qn('extension_id') . ' = ' . $db->q($extensionId));
		$db->setQuery($query);

		$ids = $db->loadColumn(0);

		if (!is_array($ids) && empty($ids))
		{
			return;
		}

		foreach ($ids as $id)
		{
			$query = $db->getQuery(true)
				->delete($db->qn('#__update_sites'))
				->where($db->qn('update_site_id') . ' = ' . $db->q($id));
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (\Exception $e)
			{
				// Do not fail in this case
			}
		}
	}

	/**
	 * The PowerAdmin extension makes menu items disappear. People assume it's our fault. JSN PowerAdmin authors don't
	 * own up to their software's issue. I have no choice but to warn our users about the faulty third party software.
	 */
	private function warnAboutJSNPowerAdmin()
	{
		$db            = JFactory::getDbo();
		$query         = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->qn('#__extensions'))
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->where($db->qn('element') . ' = ' . $db->q('com_poweradmin'))
			->where($db->qn('enabled') . ' = ' . $db->q('1'));
		$hasPowerAdmin = $db->setQuery($query)->loadResult();

		if (!$hasPowerAdmin)
		{
			return;
		}

		$query         = $db->getQuery(true)
			->select('manifest_cache')
			->from($db->qn('#__extensions'))
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->where($db->qn('element') . ' = ' . $db->q('com_poweradmin'))
			->where($db->qn('enabled') . ' = ' . $db->q('1'));
		$paramsJson    = $db->setQuery($query)->loadResult();
		$jsnPAManifest = new JRegistry();
		$jsnPAManifest->loadString($paramsJson, 'JSON');
		$version = $jsnPAManifest->get('version', '0.0.0');

		if (version_compare($version, '2.1.2', 'ge'))
		{
			return;
		}

		echo <<< HTML
<div class="well" style="margin: 2em 0;">
<h1 style="font-size: 32pt; line-height: 120%; color: red; margin-bottom: 1em">WARNING: Menu items for {$this->componentName} might not be displayed on your site.</h1>
<p style="font-size: 18pt; line-height: 150%; margin-bottom: 1.5em">
	We have detected that you are using JSN PowerAdmin on your site. This software ignores Joomla! standards and
	<b>hides</b> the Component menu items to {$this->componentName} in the administrator backend of your site. Unfortunately we
	can't provide support for third party software. Please contact the developers of JSN PowerAdmin for support
	regarding this issue.
</p>
<p style="font-size: 18pt; line-height: 120%; color: green;">
	Tip: You can disable JSN PowerAdmin to see the menu items to {$this->componentName}.
</p>
</div>

HTML;

	}

}
