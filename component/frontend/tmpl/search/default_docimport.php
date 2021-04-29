<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/** @var  \Akeeba\Component\DocImport\Site\Model\Search\Result\DocImportArticle[]  $items */
/** @var  int $count */
/** @var \Akeeba\Component\DocImport\Site\View\Search\HtmlView $this */

use Joomla\CMS\Language\Text;

extract($this->sectionData ?? []);

if (empty($items)):
?>
<div class="alert alert-info">
	<?php if (empty($count)): ?>
		<?= Text::_('COM_DOCIMPORT_SEARCH_ERR_NO_DOCIMPORT'); ?>
	<?php else: ?>
		<?= Text::_('COM_DOCIMPORT_SEARCH_ERR_NOMORE_DOCIMPORT'); ?>
	<?php endif; ?>
</div>
<?php return; endif; ?>

<?php foreach($items as $item): ?>
<div class="dius-result dius-result-docimport">
	<h5 class="dius-result-title dius-result-title-docimport">
		<a href="<?= $item->link ?>" rel="nofollow" target="_blank">
			<?= $item->title ?>
		</a>
	</h5>
	<div class="dius-result-category dius-result-category-docimport">
		<span class="glyphicon glyphicon-book"></span>
		<a href="<?= $item->catlink ?>" rel="nofollow" target="_blank">
			<?= $item->cattitle ?>
		</a>
	</div>
	<div class="dius-result-synopsis dius-result-synopsis-docimport">
		<?= $item->synopsis ?>
	</div>
</div>
<?php endforeach; ?>
