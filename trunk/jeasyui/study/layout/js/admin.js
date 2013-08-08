define('admin', ['fields'], function(require, exports, module) {
    var Base    = require('base');
    var Admin   = Base.extend({
        _datagridOptions: {
            columns: [[
                {checkbox: true},
                {title: 'id', field: 'blog_id', width: 50},
                {title: '标题', field: 'title', width: 200},
                {title: '点击', field: 'hits', width: 50, fixed: true}
            ]],
            queryParams: {},
            toolbar: '#tb-adminlist',
            url: 'get_blogs.php',
            striped: true,
            fitColumns: true,
            pagination: true,
            fit: true,
            sortName: 'blog_id',
            sortOrder: 'desc',
            showFooter: true,
            loadFilter: function(data) {
                return {
                    rows: data.data,
                    total: data.total
                };
            },
            selectOnCheck: true,
            checkOnSelect: false,
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
            },
            checkbox: true
        },

        /**
         * 列表datagrid
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-08-01 17:03:31
         *
         * return {void} 无返回值
         */
        _datagrid: function() {
            var tabs        = require('tabs'),
                selectedTab = tabs.getSelected(),
                dg          = tabs.get('_el').find('#dg-' + C + A);

            TREE_DATA._prevQueryParams = _.clone(TREE_DATA.queryParams);

            var pagesize = {
                pageNumber: intval(Q2O.page || 1),
                pageSize: Q2O.page_size || 20
            };

            TREE_DATA.queryParams = Q2O;

            if (!dg.length) {
                $.extend(this._datagridOptions, pagesize);
                $.extend(this._datagridOptions.queryParams, Q2O);
            }
            /*else {
                $.extend(dg.datagrid('options'), pagesize);
                $.extend(dg.datagrid('options').queryParams, Q2O);
            }*/

            if (!dg.length) {
                dg = $('<table id="dg-' + C + A + '" class="easyui-datagrid"></table>')
                .appendTo(selectedTab)
                .data('data-options', this._datagridOptions);
                this._setToolbar(selectedTab);
                dg.datagrid();

                dg.datagrid('getPager').pagination({
                    onSelectPage: function(page, pageSize) {
                        $.extend(dg.datagrid('options'), {
                            pageNumber: page
                        });
                        $.extend(dg.datagrid('getPager').pagination('options'), {
                            pageNumber: page
                        });

                        $.extend(TREE_DATA.queryParams, {
                            page: page
                        });
                        require('router').navigate('' + MENU_ID + '&' + object2querystring(TREE_DATA.queryParams));
                        dg.datagrid('reload');
                    },
                    onChangePageSize: log,
                    showPageList: false
                });
            }
            else if(object2querystring(TREE_DATA._prevQueryParams) != object2querystring(TREE_DATA.queryParams)) {
                $.extend(dg.datagrid('options'), pagesize);
                $.extend(dg.datagrid('options').queryParams, TREE_DATA.queryParams);
                $.extend(dg.datagrid('getPager').pagination('options'), pagesize);
                dg.datagrid('getPager').pagination('select', pagesize.pageNumber);
                //dg.datagrid('reload');log(dg.datagrid('getPager').pagination('options'))
            }

            Q2O.keyword && selectedTab.find('#tb-' + C + A).find('#admin-keyword').searchbox('setValue', Q2O.keyword);
            selectedTab.find('#tb-' + C + A).find('#admin-start_date').datetimebox('setValue', Q2O.start_date);
            selectedTab.find('#tb-' + C + A).find('#admin-end_date').datetimebox('setValue', Q2O.end_date);
            selectedTab.find('#tb-' + C + A).find('#admin-match_mode').combobox('setValue', Q2O.match_mode || 'eq');
        },
        /**
         * 设置活跃面板
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-08-02 20:06:50
         *
         * return {void} 无返回值
         */
        _setActivePanel: function() {
            var tabs        = require('tabs').getSelected();

            tabs.children().hide();

            if ('list' == A) {
                tabs.children('.datagrid').show();
            }
            else {
                var id  = C + A;
                tabs.find('#' + id).show();
            }
        },

        /**
         * toolbar
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-08-04 17:18:31
         *
         * return {void} 无返回值
         */
        _setToolbar: function(selectedTab) {
            var me      = this,
                html    = '<div id="tb-' + C + A + '">';
                html   += '<a href="javascript:void(0)"  class="easyui-menubutton" id="admin-operate"\
        data-options="menu:\'#mm\',iconCls:\'icon-edit\'">操作</a>\
<div id="mm" style="width:150px;">\
    <div data-options="iconCls:\'icon-undo\'">删除</div>\
    <div data-options="iconCls:\'icon-redo\'">Redo</div>\
    <div class="menu-sep"></div>\
    <div>Cut</div>\
    <div>Copy</div>\
    <div>Paste</div>\
    <div class="menu-sep"></div>\
    <div data-options="iconCls:\'icon-remove\'">Delete</div>\
    <div>Select All</div>\
</div>';
                html   += '添加时间<input id="admin-start_date" class="datetime" /> - ';
                html   += '<input id="admin-end_date" class="datetime" /> ';
                html   += '<input id="admin-cate_id" /> ';
                html   += '<input id="admin-match_mode" class="match_mode" /> ';
                html   += '<input id="admin-keyword" />';
                html   += '</div>';

            selectedTab.append(html)
            .find('#admin-keyword')
                .data('data-options', {
                    prompt: '关键字',
                    searcher: function(keyword) {
                        $.extend(TREE_DATA.queryParams, {
                            keyword: keyword,
                            start_date: selectedTab.find('#admin-start_date').datetimebox('getValue'),
                            end_date: selectedTab.find('#admin-end_date').datetimebox('getValue'),
                            match_mode: selectedTab.find('#admin-match_mode').combobox('getValue')
                        });
                        var dg = selectedTab.find('#dg-' + C + A);
                        $.extend(dg.datagrid('options').queryParams, TREE_DATA.queryParams);
                        dg.datagrid('getPager').pagination('select', 1);
                    }
                }).searchbox()
            .end()
            .find('.datetime')
                .data('data-options', {
                    width: 140,
                    formatter: function(d) {
                        return date(null, d);
                    }
                })
                .datetimebox()
            .end()
            .find('#admin-match_mode')
                .data('data-options', require('fields').matchMode)
                .combobox()
            .end()
            .find('#admin-cate_id')
                .data('data-options', {
                    url: 'categories.php',
                    valueField: 'cate_id',
                    textField: 'cate_name'
                })
                .combobox()
            .end()
            .find('#mm')
                .data('data-options', {
                    onClick: function() {
                        log(selectedTab.find('#dg-' + C + A).datagrid('getChecked'));
                    }
                })
            .end()
            .find('#admin-operate')
                .menubutton();
        },

        /**
         * 构造函数
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-08-01 15:23:08
         *
         * return {void} 无返回值
         */
        constructor: function() {
            this.base();
        },

        /**
         * 添加
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-08-01 17:01:26
         *
         * return {void} 无返回值
         */
        addAction: function() {
            this._setActivePanel();
            var tabs        = require('tabs'),
                selectedTab = tabs.getSelected(),
                cc          = tabs.get('_el').find('#cc-' + C + A);

            if (!cc.length) {
                $('<div id="' + C + A + '"><input id="cc-' + C + A + '" class="match_mode" /></div>')
                .appendTo(selectedTab);
                cc = tabs.get('_el').find('#cc-' + C + A)
                .data('data-options', require('fields').matchMode)
                .combobox();
            }
            else {
                cc.combobox('setValue', 'like');
            }
        },

        /**
         * 修改密码
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-08-01 17:01:56
         *
         * return {void} 无返回值
         */
        changePasswordAction: function() {
            this._setActivePanel();
            var tabs = require('tabs');
            var dg = tabs.get('_el').find('#adminchangePassword');

            if (!dg.length) {
                $('<div id="adminchangePassword">changePassword</div>')
                .appendTo(tabs.getSelected())
            }
            else {
                //log(dg.datagrid('reload'));
            }
        },

        /**
         * 列表
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-08-01 16:50:26
         *
         * return {void} 无返回值
         */
        listAction: function() {
            this._setActivePanel();
            this._datagrid();
        }
    });

    var admin = new Admin();
    module.exports = admin;
});