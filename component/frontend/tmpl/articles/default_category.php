<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Akeeba\Component\DocImport\Site\View\Articles\HtmlView $this */

$item = $this->category;
$link = Route::_('index.php?option=com_docimport&view=articles&id=' . $item->docimport_category_id);
?>

<div class="card mb-3 docimport-category docimport-category-<?= $item->docimport_category_id ?>">
	<div class="card-header">
		<h3>
			<?= $item->title ?>
		</h3>
	</div>
	<div class="card-body">
		<div class="row">
			<?php if ($item->image): ?>
				<div class="col-sm-4">
					<img src="<?= $item->image ?>" class="img-fluid" alt="">
				</div>
			<?php endif ?>
			<div class="docimport-category-description col-sm-<?= empty($item->image) ? '12' : '8' ?>">
				<div class="docimport-category-description-inner">
					<?= HTMLHelper::_('content.prepare', $item->description) ?>
				</div>
			</div>
		</div>
	</div>
</div>