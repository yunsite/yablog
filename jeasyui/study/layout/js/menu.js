define('menu', ['fields'], function(require, exports, module) {
    var Base    = require('base');
    var Admin   = Base.extend({
        _treegridOptions: {
            idField: 'menu_id',
            treeField: 'menu_name',
            columns: [[
                {checkbox: true},
                //{title: 'id', field: 'menu_id', width: 50},
                {title: 'name', field: 'menu_name', width: 200},
                {title: '点击', field: 'controller', width: 50, fixed: true}
            ]],
            queryParams: {},
            toolbar: '#tb-' + C + 'list',
            url: '../get_tree.php',
            striped: true,
            fitColumns: true,
            //pagination: true,
            fit: true,
            singleSelect: false,
            sortName: 'blog_id',
            sortOrder: 'desc',
            showFooter: true,
            checkOnSelect: false,
            onSelect: function(index) {log(arguments);return;
                var tr = $(this).treegrid('options').finder.getTr(this, index);

                if (!tr.find('div.datagrid-cell-check input[type=checkbox]').prop('checked')) {
                    tr.removeClass('datagrid-row-selected');
                }
            },
            onUnselect: function(index) {log(arguments);return;
                var tr = $(this).treegrid('options').finder.getTr(this, index);

                if (tr.find('div.datagrid-cell-check input[type=checkbox]').prop('checked')) {
                    tr.addClass('datagrid-row-selected');
                }
            },
            checkbox: true
        },

        /**
         * 列表treegrid
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-08-01 17:03:31
         *
         * return {void} 无返回值
         */
        _treegrid: function() {
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
                $.extend(this._treegridOptions, pagesize);
                $.extend(this._treegridOptions.queryParams, Q2O);
            }
            /*else {
                $.extend(dg.treegrid('options'), pagesize);
                $.extend(dg.treegrid('options').queryParams, Q2O);
            }*/

            if (!dg.length) {
                dg = $('<table id="dg-' + C + A + '" class="easyui-treegrid"></table>')
                .appendTo(selectedTab)
                .data('data-options', this._treegridOptions);
                this._setToolbar(selectedTab);
                dg.treegrid();

                dg.treegrid('getPager').pagination({
                    onSelectPage: function(page, pageSize) {
                        $.extend(dg.treegrid('options'), {
                            pageNumber: page
                        });
                        $.extend(dg.treegrid('getPager').pagination('options'), {
                            pageNumber: page
                        });

                        $.extend(TREE_DATA.queryParams, {
                            page: page
                        });
                        require('router').navigate('' + MENU_ID + '&' + object2querystring(TREE_DATA.queryParams));
                        dg.treegrid('reload');
                    },
                    onChangePageSize: log,
                    showPageList: false
                });
            }
            else if(object2querystring(TREE_DATA._prevQueryParams) != object2querystring(TREE_DATA.queryParams)) {
                $.extend(dg.treegrid('options'), pagesize);
                $.extend(dg.treegrid('options').queryParams, TREE_DATA.queryParams);
                $.extend(dg.treegrid('getPager').pagination('options'), pagesize);
                dg.treegrid('getPager').pagination('select', pagesize.pageNumber);
                //dg.treegrid('reload');log(dg.treegrid('getPager').pagination('options'))
            }

            Q2O.keyword && selectedTab.find('#tb-' + C + A).find('#' + C + '-keyword').searchbox('setValue', Q2O.keyword);
            selectedTab.find('#tb-' + C + A)
            .find('#' + C + '-start_date')
                .datetimebox('setValue', Q2O.start_date)
            .end()
            .find('#' + C + '-end_date')
                .datetimebox('setValue', Q2O.end_date)
            .end()
            .find('#' + C + '-match_mode')
                .combobox('setValue', Q2O.match_mode || 'eq')
            .find('#' + C + '-cate_id')
                //.combobox('setValue', 3);
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
                tabs.children('.treegrid').show();
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
                html   += '<a href="javascript:void(0)"  class="easyui-menubutton" id="' + C + '-operate"\
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
                html   += '添加时间<input id="' + C + '-start_date" class="datetime" /> - ';
                html   += '<input id="' + C + '-end_date" class="datetime" /> ';
                html   += '<input id="' + C + '-cate_id" /> ';
                html   += '<input id="' + C + '-match_mode" class="match_mode" /> ';
                html   += '<input id="' + C + '-keyword" />';
                html   += '</div>';

            selectedTab.append(html)
            .find('#' + C + '-keyword')
                .data('data-options', {
                    prompt: '关键字',
                    searcher: function(keyword) {
                        $.extend(TREE_DATA.queryParams, {
                            keyword: keyword,
                            start_date: selectedTab.find('#' + C + '-start_date').datetimebox('getValue'),
                            end_date: selectedTab.find('#' + C + '-end_date').datetimebox('getValue'),
                            match_mode: selectedTab.find('#' + C + '-match_mode').combobox('getValue'),
                            cate_id: selectedTab.find('#' + C + '-cate_id').combobox('getValue')
                        });
                        var dg = selectedTab.find('#dg-' + C + A);
                        $.extend(dg.treegrid('options').queryParams, TREE_DATA.queryParams);
                        dg.treegrid('getPager').pagination('select', 1);
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
            .find('#' + C + '-match_mode')
                .data('data-options', require('fields').matchMode)
                .combobox()
            .end()
            .find('#' + C + '-cate_id')
                .data('data-options', {
                    url: 'categories.php',
                    valueField: 'cate_id',
                    textField: 'cate_name',
                    onLoadSuccess: function(data) {

                        if (data && data.length) {
                            $(this).combobox('setValue', Q2O.cate_id || 0);
                        }
                    }
                })
                .combobox()
            .end()
            .find('#mm')
                .data('data-options', {
                    onClick: function() {
                        log(selectedTab.find('#dg-' + C + A).treegrid('getChecked'));
                    }
                })
            .end()
            .find('#' + C + '-operate')
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
            var dg = tabs.get('_el').find('#' + C + 'changePassword');

            if (!dg.length) {
                $('<div id="' + C + 'changePassword">changePassword</div>')
                .appendTo(tabs.getSelected())
            }
            else {
                //log(dg.treegrid('reload'));
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
            this._treegrid();
        }
    });

    var admin = new Admin();
    module.exports = admin;
});