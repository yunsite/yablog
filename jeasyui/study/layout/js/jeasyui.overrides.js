/**
 * jeasyui重写
 *
 * @file            jeasyui.overrides.js
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-08-06 10:42:10
 * @lastmodify      $Date$ $Author$
 */

if ($.fn.datagrid) {
    $.extend($.fn.datagrid.defaults, {
        onSelect: function(index) {
            var tr = $(this).datagrid('options').finder.getTr(this, index);

            if (!tr.find('div.datagrid-cell-check input[type=checkbox]').prop('checked')) {
                tr.removeClass('datagrid-row-selected');
            }
        },
        onUnselect: function(index) {
            var tr = $(this).datagrid('options').finder.getTr(this, index);

            if (tr.find('div.datagrid-cell-check input[type=checkbox]').prop('checked')) {
                tr.addClass('datagrid-row-selected');
            }
        }
    });
}

if ($.fn.combobox) {
    $.extend($.fn.datagrid.defaults, {
        editable: false,
        options: {}
    });
    $.extend($.fn.datagrid.defaults.options, {
        matchMode: {
            width: 100,
            panelHeight: 100,
            data: [{
                text: '完全匹配',
                value: 'eq',
                selected: true
            }, {
                text: '左匹配',
                value: 'leq'
            }, {
                text: '右匹配',
                value: 'req'
            }, {
                text: '模糊匹配',
                value: 'like'
            }]
        }
    });
}

$.extend($.fn, {
    fields: {
        matchMode: {
            width: 100,
            panelHeight: 100,
            data: [{
                text: '完全匹配',
                value: 'eq',
                selected: true
            }, {
                text: '左匹配',
                value: 'leq'
            }, {
                text: '右匹配',
                value: 'req'
            }, {
                text: '模糊匹配',
                value: 'like'
            }]
        }
    }
});