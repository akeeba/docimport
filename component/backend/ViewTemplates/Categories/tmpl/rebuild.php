<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

/** @var  \Akeeba\DocImport\Admin\View\Categories\Html $this */
/** @var  \Akeeba\DocImport\Admin\Model\Categories $model */

$tooltip = JText::_('COM_DOCIMPORT_CATEGORIES_STATUS_' . $model->status);
?>
<span class="hasTooltip" title="<?php echo $tooltip?>">
<?php if ($model->status == 'missing'): ?>
	<span class="badge badge-important">
		<span class="icon-white icon-remove"></span>
	</span>
<?php elseif ($model->status == 'modified'): ?>
	<span class="badge badge-warning">
		<span class="icon-white icon-warning"></span>
	</span>
<?php else: ?>
	<span class="icon-ok"></span>
<?php endif; ?>
</span>
&nbsp;
<button
	onclick="window.location='<?php echo JUri::base() ?>index.php?option=com_docimport&view=categories&task=rebuild&id=<?php echo $model->docimport_category_id ?>';return false;"
	class="btn <?php echo ($model->status == 'modified') ? 'btn-primary btn-mini' : 'btn-inverse btn-mini' ?>"
	title="<?php echo JText::_('COM_DOCIMPORT_CATEGORIES_REBUILD') ?>">
	<i class="icon-white icon-refresh"></i>
</button>
