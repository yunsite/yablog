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
            var me = this;

            var o = require('tabs').get('_ligerTab').tab.content.children('#adminlist').ligerGrid({
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
                heightDiff: -30,
                fixedCellHeight: false,
                columns: [
                    { display: '操作', minWidth: 100, render: function() {
                        return '<a href="javascript:void(0);">编辑</a> | <a href="javascript:void(0);">删除</a>';
                    } },
                    { display: '用户id', name: 'admin_id', align: 'left', width: 100, minWidth: 60 },
                    { display: '用户名', name: 'username', minWidth: 120 },
                    { display: '真实姓名', name: 'realname', minWidth: 140 },
                    { display: '添加时间', name: 'add_time', type: 'date' },
                    { display: '绑定登录', name: 'is_restrict', minWidth: 50, type: 'yesno' }
                ],
                url: '../get_admin.php',
                pageSize: 30,
                rownumbers: true,
                checkbox: true,
                //title: 'admin list',
                toolbar0: {
                    items: [{
                        text: '操作',
                        menu: {
                            items: [{
                                text: '删除选中',
                                title: '删除',
                                click: function() {
                                    log('click', arguments);
                                    log(this);
                                }
                            }, {
                                text: '绑定登陆',
                                click: function() {
                                    //log('click', arguments);
                                }
                            }, {
                                text: '解除绑定登陆',
                                click: function() {
                                    //log('click', arguments);
                                }
                            }]
                        }
                    }, {
                        line: true
                    }, {
                        text: '<input type="text" placeholder="关键字" name="keyword" />',
                        click: function() {
                            log(this);
                        }
                    }]
                },
                onRendered: function() {
                    var html = [];

            html.push('         <div class="l-panel-topbar">');
            html.push('            <div class="l-panel-bbar-inner">');
            html.push('                <div class="l-bar-group  l-bar-message"><span class="l-bar-text"></span></div>');
            html.push('            <div class="l-bar-group operate"></div>');
            html.push('                <div class="l-bar-separator"></div>');
            html.push('                <div class="l-bar-group">');
            html.push('                    <div class="l-bar-button l-bar-btnfirst"><span></span></div>');
            html.push('                    <div class="l-bar-button l-bar-btnprev"><span></span></div>');
            html.push('                </div>');
            html.push('                <div class="l-bar-separator"></div>');
            html.push('                <div class="l-bar-group"><span class="pcontrol"> <input type="text" size="4" value="1" style="width:20px" maxlength="3" /> / <span></span></span></div>');
            html.push('                <div class="l-bar-separator"></div>');
            html.push('                <div class="l-bar-group">');
            html.push('                     <div class="l-bar-button l-bar-btnnext"><span></span></div>');
            html.push('                    <div class="l-bar-button l-bar-btnlast"><span></span></div>');
            html.push('                </div>');
            html.push('                <div class="l-bar-separator"></div>');
            html.push('                <div class="l-bar-group">');
            html.push('                     <div class="l-bar-button l-bar-btnload"><span></span></div>');
            html.push('                </div>');
            html.push('                <div class="l-bar-separator"></div>');

            html.push('                <div class="l-clear"></div>');
            html.push('            </div>');
            html.push('            </div>');
                    this.grid.children('.l-grid-loading').after(html.join(''));
            this.grid.topbar = this.grid.children('.l-panel-topbar').children('.l-panel-bbar-inner');
            this.grid.topbar.children('.operate').ligerMenuBar({
                items: [{
                    text: '操作',
                    menu: {
                        items: [{
                            text: '删除选中',
                            title: '删除',
                            click: function() {
                                log('click', arguments);
                            },
                            children: [{
                                text: '删除选中',
                                title: '删除',
                                click: function() {
                                    log('click', arguments);
                                }
                            }, {
                                text: '绑定登陆',
                                click: function() {
                                    //log('click', arguments);
                                }
                            }, {
                                text: '解除绑定登陆',
                                click: function() {
                                    //log('click', arguments);
                                }
                            }]
                        }, {
                            text: '绑定登陆',
                            click: function() {
                                //log('click', arguments);
                            }
                        }, {
                            text: '解除绑定登陆',
                            click: function() {
                                //log('click', arguments);
                            }
                        }]
                    }
                }]
            });
                    /*var toolbar = this.grid.children('.l-panel-topbar');
                    toolbar.children('.menubar').ligerMenuBar({
                        items: [{text: '文件'}]
                    });
*/
                    this.grid.find('.role').ligerComboBox({width: 50});
                    //this.grid.children('.l-panel-topbar').children('input').ligerTextBox();

                    this.grid.find('#adminlistgrid').bind('click', function(e) {log(e);
                        var target = $(e.target);

                        if (target.is('img.img-yesno')) {
                            var next = -1 == target.attr('src').indexOf('yes') ? 'yes' : 'no';
                            target.attr('src', IMAGES['loading']);
                            setTimeout(function() {
                                target.attr('src', IMAGES[next]);
                            }, 1000);
                        }
                    });
                }
            });

            return o;
        }
    });

    var admin = new Admin();
    module.exports = admin;
});