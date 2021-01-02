<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\Model;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\DocImport\Admin\Model\Articles as AdminArticles;
use FOF30\Container\Container;

class Articles extends AdminArticles
{
	public function __construct(Container $container, array $config)
	{
		parent::__construct($container, $config);

		$this->addBehaviour('Enabled');
		$this->addBehaviour('Filters');

		$this->with(['category']);
	}

}
