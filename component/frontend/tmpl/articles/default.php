<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var \Akeeba\Component\DocImport\Site\View\Articles\HtmlView $this */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<div class="docimport docimport-page-category">
	<?php if($this->showPageHeading): ?>
	<div class="page-header">
		<h2>
			<?= $this->pageHeading ?>
		</h2>
	</div>
	<?php endif; ?>

	<?php if (is_object($this->index)): ?>
		<div class="docimport-category-index">
			<?= $this->category->process_plugins ? HTMLHelper::_('content.prepare', $this->index->fulltext) : $this->index->fulltext ?>
		</div>

	<?php else: ?>

		<?= $this->loadTemplate('category') ?>
		<ul class="docimport-category-list">
			<?php foreach($this->items as $item): ?>
			<li class="docimport-article-link">
				<a href="<?= \Joomla\CMS\Router\Route::_(sprintf('index.php?option=com_docimport&view=article&cid=%d&id=%d', $this->category->docimport_category_id, $item->docimport_article_id)) ?>">
					<?= $item->title ?>
				</a>
			</li>
			<?php endforeach; ?>

			<?php if (empty($this->items)): ?>
			<p class="alert alert-warning">
				<?= Text::_('COM_DOCIMPORT_CATEGORY_EMPTY') ?>
			</p>
			<?php endif ?>
		</ul>

	<?php endif; ?>
</div>
