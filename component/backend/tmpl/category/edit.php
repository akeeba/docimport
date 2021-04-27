<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Akeeba\Component\DocImport\Administrator\View\Category\HtmlView $this */

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');
?>
<form action="<?php echo Route::_('index.php?option=com_docimport&view=category&layout=edit&docimport_category_id=' . (int) $this->item->docimport_category_id); ?>"
      aria-label="<?php echo Text::_('COM_DOCIMPORT_TITLE_CATEGORIES_' . ( (int) $this->item->docimport_category_id === 0 ? 'ADD' : 'EDIT'), true); ?>"
      class="form-validate" id="profile-form" method="post" name="adminForm">

	<div class="row">
		<div class="col-lg">
			<div class="card mb-3">
				<div class="card-header">
					<h3><?= Text::_('COM_DOCIMPORT_CATEGORY_BASIC_TITLE') ?></h3>
				</div>
				<div class="card-body">
					<?php foreach ($this->form->getFieldset('basic') as $field) {
						echo $field->renderField();
					} ?>
				</div>
			</div>
		</div>
		<div class="col-lg">
			<div class="card mb-3">
				<div class="card-header">
					<h3><?= Text::_('COM_DOCIMPORT_CATEGORY_DETAILS_TITLE') ?></h3>
				</div>
				<div class="card-body">
					<?php foreach ($this->form->getFieldset('details') as $field):
					?>
						<div class="mb-3" style="clear: both">
							<?= $field->label ?>
							<?= $field->input ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>

	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
