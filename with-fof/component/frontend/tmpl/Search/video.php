<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/** @var  \Akeeba\Component\DocImport\Site\Model\Search\Result\JoomlaArticle[]  $items */
/** @var  int $count */
/** @var \Akeeba\DocImport\Site\View\Search\Html $this */

$Itemid = $this->getContainer()->params->get('force_menuid', null);

if (empty($items)):
?>
<div class="alert alert-info">
	<?php if (empty($count)): ?>
		<?php echo JText::_('COM_DOCIMPORT_SEARCH_ERR_NO_VIDEO'); ?>
	<?php else: ?>
		<?php echo JText::_('COM_DOCIMPORT_SEARCH_ERR_NOMORE_VIDEO'); ?>
	<?php endif; ?>
</div>
<?php return; endif; ?>

<?php foreach($items as $item): ?>
	<div class="dius-result dius-result-video">
		<div class="xs-hide col-sm-6 col-md-8">
			<h5 class="dius-result-title dius-result-title-video">
				<a href="<?php echo $item->getLink($Itemid) ?>" rel="nofollow" target="_blank">
					<?php echo $item->title ?>
				</a>
			</h5>
			<div class="dius-result-category dius-result-category-video">
				<span class="glyphicon glyphicon-book"></span>
				<a href="<?php echo $item->catlink ?>" rel="nofollow" target="_blank">
					<?php echo $item->catname ?>
				</a>
			</div>
			<div class="dius-result-synopsis dius-result-synopsis-video">
				<?php echo $item->introtext ?>
			</div>
		</div>
		<div class="xs-hide col-sm-6 col-md-4">
			<div class="embed-responsive embed-responsive-16by9">
				<?php echo $item->getYouTubeIframe('embed-responsive-item') ?>
			</div>
		</div>
		<div class="clearfix"></div>
	</div>
<?php endforeach; ?>
