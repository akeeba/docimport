<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();
?>
<div class="docimport-category akeeba-panel--info">
	<header class="akeeba-block-header docimport-category-title">
		<h3>
			{{{ $this->item->title }}}
		</h3>
	</header>

	@if ($this->item->image)
		<div class="docimport-category-image">
			<img src="{{ $this->item->image }}" >
		</div>
	@endif

	<div class="docimport-category-description">
		<div class="docimport-category-description-inner">
			@jhtml('content.prepare', $this->item->description)
		</div>
	</div>
</div>
