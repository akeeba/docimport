<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

/** @var  \Akeeba\DocImport\Admin\View\Categories\Html $this */

?>
{{-- Old PHP version reminder --}}
@include('admin:com_docimport/Common/phpversion_warning', [
	'softwareName'  => 'Akeeba DocImport',
	'minPHPVersion' => '5.6.0',
])

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
	<?php $i = 0; ?>
    @foreach($this->items as $row)
        <tr>
            <td>
                @jhtml('FEFHelper.browse.order', 'ordering', $row->ordering)
            </td>
            <td>
                @jhtml('FEFHelper.browse.id', ++$i, $row->getId())
            </td>
            <td>
                <a href="@route(\FOF30\Utils\FEFHelper\BrowseView::parseFieldTags('index.php?option=com_docimport&view=Categories&task=edit&id=[ITEM:ID]', $row))">
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
                <button
                        onclick="window.location='index.php?option=com_docimport&view=Categories&task=rebuild&id={{ (int)$row->getId() }}';return false;"
                        class="btn {{ ($row->status == 'modified') ? 'btn-primary btn-mini' : 'btn-inverse btn-mini' }}"
                        title="<?php echo JText::_('COM_DOCIMPORT_CATEGORIES_REBUILD') ?>">
                    <span class="icon-white icon-refresh"></span>
                </button>
            </td>
            <td>
                @jhtml('FEFHelper.browse.published', $row->enabled, $i)
            </td>
            <td>
                {{{ \FOF30\Utils\FEFHelper\BrowseView::getOptionName($row->language, \FOF30\Utils\SelectOptions::getOptions('languages', ['none' => 'COM_DOCIMPORT_COMMON_FIELD_LANGUAGE_ALL'])) }}}
            </td>            <td>
                {{{ \FOF30\Utils\FEFHelper\BrowseView::getOptionName($row->access, \FOF30\Utils\SelectOptions::getOptions('access')) }}}
            </td>
        </tr>
    @endforeach
@stop