<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

/** @var  \Akeeba\DocImport\Admin\View\Categories\Html $this */
/** @var \Akeeba\DocImport\Admin\Model\Categories $item */
$item = $this->getItem();

?>
{{-- Old PHP version reminder --}}
@include('admin:com_docimport/Common/phpversion_warning', [
	'softwareName'  => 'Akeeba DocImport',
	'minPHPVersion' => '5.6.0',
])

@extends('any:lib_fof30/Common/edit')

@section('edit-form-body')
    <div class="akeeba-container--33-66">
        <div class="akeeba-panel--teal">
            <header class="akeeba-block-header">
                <h3>
                    @lang('COM_DOCIMPORT_CATEGORY_BASIC_TITLE')
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
                <label for="image">@fieldtitle('image')</label>

			    <?php
			    /**
			     * Prepare the media manager field JLayout configuration data.
			     *
			     * The fallback Blade renderer, which doesn't use the tokenizer, can only parse one-liners. Hence defining
			     * the field data in a PHP block instead on inlining it in the render() call below.
			     */
			    $fieldData = [
				    'asset'         => 'com_docimport',
				    'authorField'   => 'created_by',
				    'authorId'      => !empty($item->created_by) ? $item->created_by :  $this->getContainer()->platform->getUser()->id,
				    'preview'       => 0,
				    'previewHeight' => 200,
				    'previewWidth'  => 200,
				    'disabled'      => false,
				    'readonly'      => false,
				    'link'          => '',
				    'folder'        => '',
				    'id'            => 'image',
				    'name'          => 'image',
				    'value'         => $item->image,
			    ];
			    ?>
                {{ (new \Joomla\CMS\Layout\FileLayout('joomla.form.field.media'))->render($fieldData) }}
            </div>

            <div class="akeeba-form-group">
                <label for="process_plugins">@fieldtitle('process_plugins')</label>

                @jhtml('FEFHelper.select.booleanswitch', 'process_plugins', $item->process_plugins)
            </div>

            <div class="akeeba-form-group">
                <label for="enabled">@lang('JPUBLISHED')</label>

                @jhtml('FEFHelper.select.booleanswitch', 'enabled', $item->enabled)
            </div>

            <div class="akeeba-form-group">
                <label for="language">@fieldtitle('language')</label>

                {{ \FOF30\Utils\FEFHelper\BrowseView::genericSelect('language', \FOF30\Utils\SelectOptions::getOptions('languages', ['none' => 'COM_DOCIMPORT_COMMON_FIELD_LANGUAGE_ALL']), $this->getItem()->language, ['fof.autosubmit' => false, 'translate' => false]) }}
            </div>

            <div class="akeeba-form-group">
                <label for="access">@fieldtitle('access')</label>

                {{ \FOF30\Utils\FEFHelper\BrowseView::genericSelect('access', \FOF30\Utils\SelectOptions::getOptions('access'), $this->getItem()->access, ['fof.autosubmit' => false, 'translate' => false]) }}
            </div>

        </div>

        <div class="akeeba-panel--info">
            <header class="akeeba-block-header">
                <h3>
                    @lang('COM_DOCIMPORT_CATEGORY_DESCRIPTION_TITLE')
                </h3>
            </header>

            <div class="akeeba-noreset">
                @jhtml('FEFHelper.edit.editor', 'description', $this->getItem()->description)
            </div>

        </div>
    </div>




@stop