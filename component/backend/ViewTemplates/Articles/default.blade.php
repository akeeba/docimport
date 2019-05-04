<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

/** @var  \Akeeba\DocImport\Admin\View\Articles\Html $this */

?>
@extends('admin:com_docimport/Common/browse')

@section('browse-filters')
    {{-- Title --}}
    <div class="akeeba-filter-element akeeba-form-group">
        @searchfilter('title', 'title', 'COM_DOCIMPORT_CATEGORIES_FIELD_TITLE')
    </div>

    {{-- Category --}}
    <div class="akeeba-filter-element akeeba-form-group">
        {{ \FOF30\Utils\FEFHelper\BrowseView::modelFilter('docimport_category_id', 'title', 'Categories', 'COM_DOCIMPORT_ARTICLES_FIELD_CATEGORY')  }}
    </div>

    {{-- Published --}}
    <div class="akeeba-filter-element akeeba-form-group">
        {{ \FOF30\Utils\FEFHelper\BrowseView::publishedFilter('enabled', 'JENABLED') }}
    </div>
@stop

@section('browse-table-header')
    <tr>
        <td width="50px">
            @sortgrid('ordering', '<span class="icon-menu-2"></span>')
        </td>
        <th width="20px">
            @jhtml('FEFHelper.browse.checkall')
        </th>
        <th>
            @sortgrid('docimport_category_id', 'COM_DOCIMPORT_ARTICLES_FIELD_CATEGORY')
        </th>
        <th>
            @sortgrid('title')
        </th>
        <th>
            @sortgrid('enabled', 'JENABLED')
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
                <a href="@route(\FOF30\Utils\FEFHelper\BrowseView::parseFieldTags('index.php?option=com_docimport&view=Categories&task=edit&id=[ITEM:DOCIMPORT_CATEGORY_ID]', $row))">
                    {{{  \FOF30\Utils\FEFHelper\BrowseView::modelOptionName($row->docimport_category_id, 'Categories') }}}
                </a>
            </td>
            <td>
                <a href="@route(\FOF30\Utils\FEFHelper\BrowseView::parseFieldTags('index.php?option=com_docimport&view=Articles&task=edit&id=[ITEM:ID]', $row))">
                    {{{ $row->title }}}
                </a>
            </td>
            <td>
                @jhtml('FEFHelper.browse.published', $row->enabled, $i)
            </td>
        </tr>
    @endforeach
@stop