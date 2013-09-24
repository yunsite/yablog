define('admin', ['fields'], function(require, exports, module) {
    var Base    = require('base');
    var Admin   = Base.extend({
        _datagridOptions: {
            queryParams: {},//查询参数
            columns: [[
                {checkbox: true},
                {title: 'id', field: 'admin_id', width: 50},
                {title: 'username', field: 'username', width: 200},
                {title: 'realname', field: 'realname', width: 100, fixed: true}
            ]],
            toolbar: '#tb-' + ID,
            url: 'get_admin.php',
            sortName: 'admin_id',
            sortOrder: 'desc',
            _createContextMenu: function() {

                if (!$('#contextmenu' + ID).length) {
                    var o = $('<div id="contextmenu' + ID + '"></div>')
                    .appendTo($('body'))
                    .menu()
                    .menu('appendItem', {text: '删除'});
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
                        log(data);
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
            .children('#admin-menulist')
                .data('data-options', {
                    onClick: function() {
                        log(arguments);
                        //log(selectedTab.find('#grid-' + C + A).datagrid('getChecked'));
                    }
                })
                //.find('div > select').data('data-options', {}).combobox()
                //.end()
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
            require('tabs').get('_ligerTab').tab.content.children('#adminlist').ligerGrid({
                switchPageSizeApplyComboBox: true,
                selectRowButtonOnly: true,
                frozen: false,
                height: '100%',
                pageParmName: 'page',
                pagesizeParmName: 'page_size',
                sortnameParmName: 'sort',
                sortorderParmName: 'order',
                sortName: 'admin_id',
                sortOrder: 'DESC',
                width: 0,
                columns: [
                    { display: '用户id', name: 'admin_id', align: 'left', width: 100, minWidth: 60 },
                    { display: '用户名', name: 'username', minWidth: 120 },
                    { display: '真实姓名', name: 'realname', minWidth: 140 }
                ],
                url: '../get_admin.php',
                pageSize: 30,
                rownumbers: true,
                checkbox: true,
                title: 'admin list',
                toolbar: {
                    items: [
                        { text: '增加', click: log, icon: 'add' },
                        { line: true },
                        { text: '修改', click: log, icon: 'modify' },
                        { line: true },
                        { text: '删除', click: log }
                    ]
                }
            });
        }
    });

    var admin = new Admin();
    module.exports = admin;
});