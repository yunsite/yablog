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
        /**
         * var {string} cbCls 复选框checkbox class
         */
        cbCls: 'div.datagrid-cell-check input[type=checkbox]',

        /**
         * var {string} title 标题
         */
        title: '<span class="bread">bread</span>',

        /**
         * var {object} queryParams 查询参数
         */
        queryParams: {},

        /**
         * var {bool} [striped=true]
         */
        striped: true,

        /**
         * var {bool} [fitColumns=true] true固定列宽
         */
        fitColumns: true,

        /**
         * var {bool} [pagination=true] true带分页条
         */
        pagination: true,

        /**
         * var {bool} [fit=true] true充满区域
         */
        fit: true,

        /**
         * var {function} loadFilter 处理后端返回数据格式
         */
        loadFilter: function(data) {
            return {
                rows: data.data,
                total: data.total
            };
        },

        /**
         * var {bool} [selectOnCheck=true] true勾选时选中行
         */
        selectOnCheck: true,

        /**
         * var {bool} [selectOnCheck=false] true点击行时勾选复选框
         */
        checkOnSelect: false,

        /**
         * var {function} onSelect 选择行后
         */
        onSelect: function(index) {
            var options = $(this).datagrid('options'),
                tr      = options.finder.getTr(this, index);

            if (!tr.find(options.cbCls).prop('checked')) {//非勾选
                tr.removeClass('datagrid-row-selected');
            }
        },

        /**
         * var {function} onUnselect 取消选择行后
         */
        onUnselect: function(index) {
            var options = $(this).datagrid('options'),
                tr      = options.finder.getTr(this, index);

            if (tr.find(options.cbCls).prop('checked')) {//已勾选
                tr.addClass('datagrid-row-selected');
            }
        },

        /**
         * var {function} onSortColumn 排序后
         */
        onSortColumn: function(sort, order) {
            $(this).datagrid('options')._onSortColumn.call(this, sort, order);
        },

        /**
         * var {function} onSortColumn 排序后
         */
        _onSortColumn: function(sort, order) {
            var grid        = $(this),
                pagesize    = {
                    pageNumber: intval(Q2O.page || 1),
                    pageSize: Q2O.page_size || 20
                };

            $.extend(TREE_DATA.queryParams, {
                sort: sort,
                order: order
            });

            $.extend(grid.datagrid('options'), pagesize, {sortName: sort, sortOrder: order});
            seajs.require('router').navigate(object2querystring(TREE_DATA.queryParams));
        }
    });
}

if ($.fn.combobox) {
    $.extend($.fn.datagrid.defaults, {
        /**
         * var {bool} [editable=false] true可输入
         */
        editable: false
    });
}

if ($.fn.datebox) {
    $.extend($.fn.datebox.defaults, {
        /**
         * var {bool} [width=140] 宽度
         */
        width: 140
    });
}

if ($.fn.datetimebox) {
    $.extend($.fn.datebox.defaults, {
        /**
         * var {bool} [width=140] 宽度
         */
        width: 140
    });
}

if ($.fn.menu) {
    $.extend($.fn.menu.methods, {
        /**
         * 批量添加菜单项
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-09-07 21:29:20
         *
         * @param {object} jq jquery对象
         * @param {array} 菜单项
         *
         * return {object} this
         */
        appendItems: function(jq, items) {
            $.each(items, function(index, item) {
                jq.menu('appendItem', item);
            });

            return jq;
        }
    });
}