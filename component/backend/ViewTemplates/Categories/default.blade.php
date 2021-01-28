<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

/** @var  \Akeeba\DocImport\Admin\View\Categories\Html $this */

?>
{{-- Old PHP version reminder --}}
@include('admin:com_docimport/Common/phpversion_warning', [
	'softwareName'  => 'Akeeba DocImport',
	'minPHPVersion' => '7.2.0',
])

@extends('any:lib_fof40/Common/browse')

@section('browse-filters')
    {{-- Title --}}
    <div class="akeeba-filter-element akeeba-form-group">
        @searchfilter('title', 'title', 'COM_DOCIMPORT_CATEGORIES_FIELD_TITLE')
    </div>

    {{-- Published --}}
    <div class="akeeba-filter-element akeeba-form-group">
        {{ \FOF40\Html\FEFHelper\BrowseView::publishedFilter('enabled', 'JENABLED') }}
    </div>

    {{-- Language --}}
    <div class="akeeba-filter-element akeeba-form-group">
        @selectfilter('language', \FOF40\Html\SelectOptions::getOptions('languages'))
    </div>

    {{-- Access --}}
    <div class="akeeba-filter-element akeeba-form-group">
        @selectfilter('access', \FOF40\Html\SelectOptions::getOptions('access'))
    </div>
@stop

@section('browse-table-header')
    <tr>
        <td width="50px">
            @sortgrid('ordering', '<i class="icon-menu-2"></i>')
        </td>
        <th width="20px">
            @jhtml('FEFHelp.browse.checkall')
        </th>
        <th>
            @sortgrid('title')
        </th>
        <th>
            @lang('COM_DOCIMPORT_CATEGORIES_FIELD_STATUS')
        </th>
        <th>
            @sortgrid('enabled', 'JENABLED')
        </th>
        <th>
            @sortgrid('language')
        </th>
        <th>
            @sortgrid('access')
        </th>
    </tr>
@stop

@section('browse-table-body-withrecords')
	<?php $i = 0; ?>
    @foreach($this->items as $row)
        <tr>
            <td>
                @jhtml('FEFHelp.browse.order', 'ordering', $row->ordering)
            </td>
            <td>
                @jhtml('FEFHelp.browse.id', ++$i, $row->getId())
            </td>
            <td>
                <a href="@route(\FOF40\Html\FEFHelper\BrowseView::parseFieldTags('index.php?option=com_docimport&view=Categories&task=edit&id=[ITEM:ID]', $row))">
                    {{{ $row->title }}}
                </a>
            </td>
            <td>
                <span class="hasTooltip" title="@lang('COM_DOCIMPORT_CATEGORIES_STATUS_' . $row->status)">
                    @if($row->status == 'missing')
                        <span class="akeeba-label--red">
                            <span class="akion-android-cancel"></span>
                        </span>
                    @elseif ($row->status == 'modified')
                        <span class="akeeba-label--orange">
                            <span class="akion-android-warning"></span>
                        </span>
                    @else
                        <span class="akeeba-label--green">
                            <span class="akion-checkmark"></span>
                        </span>
                    @endif
                </span>
                &nbsp;
                <a href="index.php?option=com_docimport&view=Categories&task=rebuild&id={{ (int)$row->getId() }}"
                   class="akeeba-btn--mini--{{ ($row->status == 'modified' ? 'teal' : 'dark') }}"
                   title="@lang('COM_DOCIMPORT_CATEGORIES_REBUILD')"
                >
                    <span class="akion-refresh"></span>
                </a>
            </td>
            <td>
                @jhtml('FEFHelp.browse.published', $row->enabled, $i)
            </td>
            <td>
                {{{ \FOF40\Html\FEFHelper\BrowseView::getOptionName($row->language, \FOF40\Html\SelectOptions::getOptions('languages', ['none' => 'COM_DOCIMPORT_COMMON_FIELD_LANGUAGE_ALL'])) }}}
            </td>            <td>
                {{{ \FOF40\Html\FEFHelper\BrowseView::getOptionName($row->access, \FOF40\Html\SelectOptions::getOptions('access')) }}}
            </td>
        </tr>
    @endforeach
@stop