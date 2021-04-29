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
/** @var \Akeeba\Component\DocImport\Site\View\Search\HtmlView $this */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

extract($this->sectionData ?? []);

$cParams = ComponentHelper::getParams('com_docimport');
$Itemid = $cParams->get('force_menuid', null);

if (empty($items)):
?>
<div class="alert alert-info">
	<?php if (empty($count)): ?>
		<?= Text::_('COM_DOCIMPORT_SEARCH_ERR_NO_VIDEO'); ?>
	<?php else: ?>
		<?= Text::_('COM_DOCIMPORT_SEARCH_ERR_NOMORE_VIDEO'); ?>
	<?php endif; ?>
</div>
<?php return; endif; ?>

<?php foreach($items as $item): ?>
	<div class="dius-result dius-result-video">
		<div class="xs-hide col-sm-6 col-md-8">
			<h4 class="dius-result-title dius-result-title-video">
				<a href="<?= $item->getLink($Itemid) ?>" rel="nofollow" target="_blank">
					<?= $item->title ?>
				</a>
			</h4>
			<div class="dius-result-category dius-result-category-video">
				<span class="icon icon-book"></span>
				<a href="<?= $item->catlink ?>" rel="nofollow" target="_blank">
					<?= $item->catname ?>
				</a>
			</div>
			<div class="dius-result-synopsis dius-result-synopsis-video">
				<?= $item->introtext ?>
			</div>
		</div>
		<div class="xs-hide col-sm-6 col-md-4">
			<div class="embed-responsive embed-responsive-16by9">
				<?= $item->getYouTubeIframe('embed-responsive-item') ?>
			</div>
		</div>
		<div class="clearfix"></div>
	</div>
<?php endforeach; ?>
