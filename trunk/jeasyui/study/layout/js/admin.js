define('admin', ['fields'], function(require, exports, module) {
    var Base    = require('base');
    var Admin   = Base.extend({
        _datagridOptions: {
            title: '<span class="bread">bread</span>',
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
            var toolbar = selectedTab.children('#tb-' + C + A);
            toolbar.children('#' + C + '-keyword')
                .data('data-options', {
                    prompt: '关键字',
                    searcher: function(keyword) {
                        $.extend(TREE_DATA.queryParams, {
                            keyword: keyword,
                            start_date: toolbar.children('#' + C + '-start_date').datebox('getValue'),
                            end_date: toolbar.children('#' + C + '-end_date').datebox('getValue'),
                            match_mode: toolbar.children('#' + C + '-match_mode').combobox('getValue'),
                            cate_id: toolbar.children('#' + C + '-cate_id').combobox('getValue')
                        });
                        var grid = selectedTab.find('#grid-' + C + A);
                        $.extend(grid.datagrid('options').queryParams, TREE_DATA.queryParams);
                        grid.datagrid('getPager').pagination('select', 1);
                    }
                }).searchbox()
            .end()
            .children('input[data-jeasyui=datebox]')
                .data('data-options', require('fields').datetime)
                .datebox()
            .end()
            .children('#' + C + '-match_mode')
                .data('data-options', require('fields').matchMode)
                .combobox()
            .end()
            .children('#' + C + '-cate_id')
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
            .children('#admin-menulist')
                .data('data-options', {
                    onClick: function() {
                        log(arguments);
                        //log(selectedTab.find('#grid-' + C + A).datagrid('getChecked'));
                    }
                })
            .end()
            .children('#admin-operate')
                .data('data-options', {
                    menu:'#admin-menulist'
                })
                .menubutton()
            .end();
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
            var tabs        = require('tabs'),
                selectedTab = tabs.getSelected(),
                form         = tabs.get('_el').find('#' + C + A);

            if (global('FIRST_LOAD')) {
                form.form({
                    onLoadError: function() {
                        log('error', arguments);
                    },
                    url: 'form.php',
                    success: function() {
                        log('success', arguments);
                    }
                }).find('.validatebox').validatebox();
            }
            else {
                //cc.combobox('setValue', 'like');
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
        changePasswordAction: function() {log(C + A);return;
            this._setActivePanel();
            var tabs = require('tabs');
            var grid = tabs.get('_el').find('#adminchangePassword');

            if (!grid.length) {
                $('<div id="adminchangePassword">changePassword</div>')
                .appendTo(tabs.getSelected())
            }
            else {
                //log(grid.datagrid('reload'));
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
            var defaults = {
                sort: Q2O.sort || 'blog_id',//排序字段
                order: Q2O.order || 'DESC',//排序
                start_date: Q2O.start_date || '',//添加时间,开始
                end_date: Q2O.end_date || '',//添加时间,结束
                keyword: Q2O.keyword || '',//关键字
                role_id: Q2O.role_id || '',//角色id
                column: Q2O.column || 'username',//搜索字段
                match_mode: Q2O.match_mode || 'eq',//匹配模式
                is_lock: undefined === Q2O.is_lock ? -1 : Q2O.is_lock,//锁定状态
                is_restrict: undefined === Q2O.is_restrict ? -1 : Q2O.is_restrict,//绑定登陆状态
                page: Q2O.page || 1//页
            };
            var callback = function() {
                require('tabs').getSelected().find('#tb-' + C + A)
                .children('#' + C + '-start_date')
                    .datebox('setValue', Q2O.start_date)
                .end()
                .children('#' + C + '-end_date')
                    .datebox('setValue', Q2O.end_date)
                .end()
                .children('#' + C + '-match_mode')
                    .combobox('setValue', Q2O.match_mode || 'eq')
                .children('#' + C + '-keyword').searchbox('setValue', Q2O.keyword)
            };

            this._datagrid(defaults, callback);
        }
    });

    var admin = new Admin();
    module.exports = admin;
});