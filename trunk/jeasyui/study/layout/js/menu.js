define('menu', ['fields'], function(require, exports, module) {
    var Base    = require('base');
    var Menu   = Base.extend({
        _treegridOptions: {
            idField: 'menu_id',
            treeField: 'menu_name',
            columns: [[
                {checkbox: true},
                {title: '菜单id', field: 'menu_id', width: 60, fixed: true},
                {title: '菜单名称', field: 'menu_name', width: 200},
                {title: '控制器', field: 'controller', width: 120, fixed: true},
                {title: '操作方法', field: 'action', width: 120, fixed: true},
                {title: '操作', field: 'nofield_op', width: 120, fixed: true, formatter: function() { return ''; }}
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
            onSelect: function(data) {
                var tr      = $(this).treegrid('options').finder.getTr(this, data.menu_id);

                if (!tr.find('div.datagrid-cell-check input[type=checkbox]').prop('checked')) {
                    tr.removeClass('datagrid-row-selected');
                }

                //tr.next('tr.treegrid-tr-tree').find('div.datagrid-cell-check input[type=checkbox]').prop('checked', checked);
            },
            onUnselect: function(data) {
                var tr      = $(this).treegrid('options').finder.getTr(this, data.menu_id);

                if (tr.find('div.datagrid-cell-check input[type=checkbox]').prop('checked')) {
                    tr.addClass('datagrid-row-selected');
                }
            },
            checkEvent: function(data, checked) {
                var tr  = $(this).treegrid('options').finder.getTr(this, data.menu_id),
                    cls = 'div.datagrid-cell-check input[type=checkbox]';

                if (checked) {
                    var method = 'addClass';

                }
                else {
                    var method = 'removeClass';
                    tr.find(cls)
                    .parents('tr.treegrid-tr-tree')
                    .prev('tr[node-id]')
                    .removeClass('datagrid-row-selected')
                    .find(cls).prop('checked', false);
                }

                tr.next('tr.treegrid-tr-tree')
                .find('tr[node-id]')[method]('datagrid-row-selected')
                .end()
                .find(cls)
                    .prop('checked', checked);
            },
            rowStyler: function() {
                return 'cursor: pointer';
            },
            onCheck: function(data) {
                $(this).treegrid('options').checkEvent.call(this, data, true);
            },
            onUncheck: function(data) {
                $(this).treegrid('options').checkEvent.call(this, data, false);
            },
            onClickRow: function() {
                log(arguments, 'onClickRow');
            },
            onContextMenu: function(e, field) {
                log(arguments, 'onRowContextMenu');
                e.preventDefault();
            }
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
                grid          = tabs.get('_el').find('#grid-' + C + A);

            TREE_DATA._prevQueryParams = _.clone(TREE_DATA.queryParams);

            var pagesize = {
                pageNumber: intval(Q2O.page || 1),
                pageSize: Q2O.page_size || 20
            };

            TREE_DATA.queryParams = Q2O;

            //if (!grid.length) {
                $.extend(this._treegridOptions, pagesize);
                $.extend(this._treegridOptions.queryParams, Q2O);
            //}
            /*else {
                $.extend(grid.treegrid('options'), pagesize);
                $.extend(grid.treegrid('options').queryParams, Q2O);
            }*/

            if (global('FIRST_LOAD')) {
                grid.data('data-options', this._treegridOptions);
                this._setToolbar(selectedTab);
                grid.treegrid();

                grid.treegrid('getPager').pagination({
                    onSelectPage: function(page, pageSize) {
                        $.extend(grid.treegrid('options'), {
                            pageNumber: page
                        });
                        $.extend(grid.treegrid('getPager').pagination('options'), {
                            pageNumber: page
                        });

                        $.extend(TREE_DATA.queryParams, {
                            page: page
                        });
                        require('router').navigate('controller={controller}&action={action}&'.format(TREE_DATA) + object2querystring(TREE_DATA.queryParams));
                        grid.treegrid('reload');
                    },
                    onChangePageSize: log,
                    showPageList: false
                });
            }
            else if(object2querystring(TREE_DATA._prevQueryParams) != object2querystring(TREE_DATA.queryParams)) {
                $.extend(grid.treegrid('options'), pagesize);
                $.extend(grid.treegrid('options').queryParams, TREE_DATA.queryParams);
                $.extend(grid.treegrid('getPager').pagination('options'), pagesize);
                grid.treegrid('getPager').pagination('select', pagesize.pageNumber);
                //grid.treegrid('reload');log(grid.treegrid('getPager').pagination('options'))
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
            .end()
            .find('#' + C + '-cate_id')
                //.combobox('setValue', 3);
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
            selectedTab.find('#' + C + '-keyword')
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
                        var grid = selectedTab.find('#grid-' + C + A);
                        $.extend(grid.treegrid('options').queryParams, TREE_DATA.queryParams);
                        grid.treegrid('getPager').pagination('select', 1);
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
            .find('#menu-menulist')
                .data('data-options', {
                    onClick: function() {
                        log(selectedTab.find('#grid-' + C + A).treegrid('getChecked'));
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
        addAction: function() {return;
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
            var grid = tabs.get('_el').find('#' + C + 'changePassword');

            if (!grid.length) {
                $('<div id="' + C + 'changePassword">changePassword</div>')
                .appendTo(tabs.getSelected())
            }
            else {
                //log(grid.treegrid('reload'));
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
            this._treegrid();
        }
    });

    var menu = new Menu();
    module.exports = menu;
});