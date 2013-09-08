define('blog', ['fields'], function(require, exports, module) {
    var Base    = require('base');
    var Blog   = Base.extend({
        _datagridOptions: {
            columns: [[
                {checkbox: true},
                {title: 'id', field: 'blog_id', width: 50},
                {title: '标题', field: 'title', width: 200},
                {title: '点击', field: 'hits', width: 50, fixed: true}
            ]],
            toolbar: '#tb-' + ID,
            url: 'get_blogs.php',
            sortName: 'blog_id',
            sortOrder: 'desc',
            _createContextMenu: function() {

                if (!$('#contextmenu' + ID).length) {
                    var o = $('<div id="contextmenu' + ID + '"></div>')
                    .appendTo($('body'))
                    .menu()
                    .menu('appendItems', [{
                        text: '删除',
                        name: 'delete',
                        iconCls: 'icon-remove'
                    }, {
                        text: '编辑',
                        iconCls: 'icon-edit'
                    }]);
                }
            },
            onRowContextMenu: function(e, index, data) {
                var options = $(this).datagrid('options');

                if (!options._contextmenu) {
                    options._createContextMenu();
                    options._contextmenu = true;
                }

                var o = $('#contextmenu' + ID)
                .menu('show', {
                    left: e.pageX,
                    top: e.pageY
                });

                $.extend(o.menu('options'), {
                    onClick: function() {
                        $.messager.confirm('系统提示', '您确定要删除 ' + data.title + data.title + data.title + data.title + '？', function() {
                            log(arguments);
                        });
                    }
                });

                e.preventDefault();
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
            toolbar.children('input[data-name=keyword]')
                .data('data-options', {
                    prompt: '关键字',
                    searcher: function(keyword) {
                        var values = {};

                        $.each(toolbar.children('input[data-jeasyui]'), function(index, item) {
                            var me = $(this);
                            values[me.attr('data-name')] = me[me.attr('data-jeasyui')]('getValue');
                        });

                        $.extend(TREE_DATA.queryParams, values);
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
            .children('input[data-name=match_mode]')
                .data('data-options', require('fields').matchMode)
                .combobox()
            .end()
            .children('input[data-name=cate_id]')
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
            .children('#blog-menulist')
                .data('data-options', {
                    onClick: function() {
                        log(arguments);
                        //log(selectedTab.find('#grid-' + C + A).datagrid('getChecked'));
                    }
                })
                //.find('div > select').data('data-options', {}).combobox()
                //.end()
            .end()
            .children('#blog-operate')
                .data('data-options', {
                    menu:'#blog-menulist'
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
            var grid = tabs.get('_el').find('#blogchangePassword');

            if (!grid.length) {
                $('<div id="blogchangePassword">changePassword</div>')
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
                .children('input[data-name=start_date]')
                    .datebox('setValue', Q2O.start_date)
                .end()
                .children('input[data-name=end_date]')
                    .datebox('setValue', Q2O.end_date)
                .end()
                .children('input[data-name=match_mode]')
                    .combobox('setValue', Q2O.match_mode || 'eq')
                .end()
                .children('input[data-name=keyword]')
                    .searchbox('setValue', Q2O.keyword)
            };

            this._datagrid(defaults, callback);
        }
    });

    var blog = new Blog();
    module.exports = blog;
});