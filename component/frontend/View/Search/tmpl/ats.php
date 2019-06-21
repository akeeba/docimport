<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/** @var  \Akeeba\DocImport\Site\Model\Search\Result\AkeebaTicket[]  $items */
/** @var  int $count */

if (empty($items)):
?>
<div class="alert alert-info">
	<?php if (empty($count)): ?>
		<?php echo JText::_('COM_DOCIMPORT_SEARCH_ERR_NO_ATS'); ?>
	<?php else: ?>
		<?php echo JText::_('COM_DOCIMPORT_SEARCH_ERR_NOMORE_ATS'); ?>
	<?php endif; ?>
</div>
<?php return; endif; ?>

<?php foreach($items as $item): ?>
	<div class="dius-result dius-result-ats">
		<h5 class="dius-result-title dius-result-title-ats">
			<a href="<?php echo $item->link ?>" rel="nofollow" target="_blank">
				<?php echo $item->title ?>
			</a>
		</h5>
		<div class="dius-result-category dius-result-category-ats">
			<span class="glyphicon glyphicon-book"></span>
			<a href="<?php echo $item->catlink ?>" rel="nofollow" target="_blank">
				<?php echo $item->catname ?>
			</a>
		</div>
		<div class="dius-result-synopsis dius-result-synopsis-ats">
			<?php echo $item->synopsis ?>
		</div>
	</div>
<?php endforeach; ?>
