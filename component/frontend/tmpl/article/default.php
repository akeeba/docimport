<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<div class="docimport docimport-page-article">
	<?php if($this->showPageHeading): ?>
	<div class="page-header">
		<h2>
			<?= Text::_($this->pageHeading) ?>
		</h2>
	</div>
	<?php endif ?>

	<?= $this->contentPrepare ? HTMLHelper::_('content.prepare', $this->item->fulltext) : $this->item->fulltext ?>
</div>
