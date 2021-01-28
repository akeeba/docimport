<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\Model;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\DocImport\Admin\Model\Categories as AdminCategories;
use FOF40\Container\Container;

class Categories extends AdminCategories
{
	public function __construct(Container $container, array $config)
	{
		parent::__construct($container, $config);

		$this->addBehaviour('Access');
		$this->addBehaviour('Enabled');
		$this->addBehaviour('Language');
	}
}
