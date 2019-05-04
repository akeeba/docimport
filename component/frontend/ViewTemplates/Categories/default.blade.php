<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/** @var \Akeeba\DocImport\Site\View\Categories\Html $this */
/** @var \Akeeba\DocImport\Site\Model\Categories $item */

?>
<div class="docimport docimport-page-categories">
    @if ($this->showPageHeading)
        <h2>{{ $this->pageHeading }}</h2>
    @endif

    @forelse ($this->items as $item)
        <div class="docimport-category akeeba-panel--info">
            <header class="docimport-category-title akeeba-block-header">
                <h3>
                    <a href="@route('index.php?option=com_docimport&view=Category&id=' . $item->getId())">
		                {{{ $item->title }}}
                    </a>
                </h3>
            </header>

            @if ($item->image)
                <div class="docimport-category-image">
                    <img src="{{ $item->image }}" >
                </div>
            @endif

            <div class="docimport-category-description">
                <div class="docimport-category-description-inner">
                    @jhtml('content.prepare', $item->description)
                </div>
            </div>

            <div class="docimport-category-readon">
                <a class="akeeba-btn--primary" href="@route('index.php?option=com_docimport&view=Category&id=' . $item->getId())">
			        @lang('COM_DOCIMPORT_CATEGORIES_GOTOINDEX')
                </a>
            </div>
        </div>
    @empty
        <p class="akeeba-block--warning">
            @lang('COM_DOCIMPORT_CATEGORIES_NONE')
        </p>
    @endforelse

</div>
