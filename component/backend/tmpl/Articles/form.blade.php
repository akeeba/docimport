<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

/** @var  \Akeeba\DocImport\Admin\View\Articles\Html $this */
/** @var \Akeeba\DocImport\Admin\Model\Articles $item */
$item = $this->getItem();

?>
@extends('any:lib_fof40/Common/edit')

@section('edit-form-body')
    <div class="akeeba-panel--teal">
        <header class="akeeba-block-header">
            <h3>
                @lang('COM_DOCIMPORT_ARTICLES_BASIC_TITLE')
            </h3>
        </header>

        <div class="akeeba-form-group">
            <label for="title">@fieldtitle('title')</label>

            <input type="text" name="title" id="title" value="{{{ $item->title }}}" />
        </div>

        <div class="akeeba-form-group">
            <label for="slug">@fieldtitle('slug')</label>

            <input type="text" name="slug" id="slug" value="{{{ $item->slug }}}" />
        </div>

        <div class="akeeba-form-group">
            <label for="docimport_category_id">@lang('COM_DOCIMPORT_ARTICLES_FIELD_CATEGORY')</label>

            {{ \FOF40\Html\FEFHelper\BrowseView::modelSelect('docimport_category_id', 'Categories', $item->docimport_category_id, ['fof.autosubmit' => false, 'translate' => false]) }}
        </div>

        <div class="akeeba-form-group">
            <label for="enabled">@lang('JPUBLISHED')</label>

            @jhtml('FEFHelp.select.booleanswitch', 'enabled', $item->enabled)
        </div>
    </div>


    <div class="akeeba-panel--info">
        <header class="akeeba-block-header">
            <h3>
                @lang('COM_DOCIMPORT_ARTICLES_FIELD_FULLTEXT')
            </h3>
        </header>

        <div class="akeeba-noreset">
            @jhtml('FEFHelp.edit.editor', 'fulltext', $this->getItem()->fulltext)
        </div>

    </div>
@stop