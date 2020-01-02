<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/** @var \Akeeba\DocImport\Site\View\Categories\Html $this */

?>
@section('index_document')
	<div class="docimport-category-index">
		@if ($this->item->process_plugins)
			@jhtml('content.prepare', $this->index->fulltext)
		@else
			{{ $this->index->fulltext }}
		@endif
	</div>
@stop

@section('page_list')
	@include('site:com_docimport/Categories/item_category')

	<div class="docimport-category-list">
		@forelse($this->items as $item)
			<div class="docimport-article-link">
				<a href="@route('index.php?option=com_docimport&view=Article&id=' . $item->getId())">
					{{{ $item->title }}}
				</a>
			</div>
		@empty
			<p class="akeeba-block--warning">
				@lang('COM_DOCIMPORT_CATEGORY_EMPTY')
			</p>
		@endforelse
	</div>
@stop

<div class="docimport docimport-page-category">
	@if($this->showPageHeading)
		<div class="page-header">
			<h2>
				{{ $this->pageHeading }}
			</h2>
		</div>
	@endif

	@if (is_object($this->index))
		@yield('index_document')
	@else
		@yield('page_list')
	@endif

</div>
