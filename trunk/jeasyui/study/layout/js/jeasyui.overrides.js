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
}