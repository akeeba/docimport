<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

/** @var  \Akeeba\DocImport\Admin\View\Categories\Html $this */

?>
@extends('admin:com_docimport/Common/browse')

@section('browse-filters')
    {{-- Title --}}
    <div class="akeeba-filter-element akeeba-form-group">
        @searchfilter('title', 'title', 'COM_DOCIMPORT_CATEGORIES_FIELD_TITLE')
    </div>

    {{-- Published --}}
    <div class="akeeba-filter-element akeeba-form-group">
        {{ \FOF30\Utils\FEFHelper\BrowseView::publishedFilter('enabled', 'JENABLED') }}
    </div>

    {{-- Language --}}
    <div class="akeeba-filter-element akeeba-form-group">
        @selectfilter('language', \FOF30\Utils\SelectOptions::getOptions('languages'))
    </div>

    {{-- Access --}}
    <div class="akeeba-filter-element akeeba-form-group">
        @selectfilter('access', \FOF30\Utils\SelectOptions::getOptions('access'))
    </div>
@stop

@section('browse-table-header')
    <tr>
        <td width="50px">
            @sortgrid('ordering', '<i class="icon-menu-2"></i>')
        </td>
        <th width="20px">
            @jhtml('FEFHelper.browse.checkall')
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
{{--  TODO  --}}
@stop