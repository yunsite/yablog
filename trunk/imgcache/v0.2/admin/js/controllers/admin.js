/**
 * 管理员
 *
 * @file            admin.js
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-09-28 17:39:18
 * @lastmodify      $Date$ $Author$
 */

define('admin', [], function(require, exports, module) {
    var Base    = require('core/base');
    var Admin   = Base.extend({
        /**
         * 顶部工具栏
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-10-13 21:51:16
         *
         * return {array} 工具栏组件配置
         */
        _toolbar: function() {
            var me = this;

            return [{//操作
                cls: 'operate toolbar',
                text: ''
            }, true, lang('ADD,TIME,CN_CONG'), {
                name: 'start_date',//添加时间,开始
                attrs: {
                    'data-type': 'datetime',
                    'data-ligerui': 'dateEditor'
                }
            }, lang('TO'), {
                name: 'end_date',//添加时间,结束
                attrs: {
                    'data-type': 'datetime',
                    'data-ligerui': 'dateEditor'
                }
            }, true, {
                name: 'menu_id',
                cls: 'combotree',
                attrs: {
                    'data-ligerui': 'comboBox'
                }
            }, {
                cls: 'role',
                name: 'role_id',//所属角色
                attrs: {
                    'data-ligerui': 'comboBox'
                }
            }, {
                cls: 'keyword',
                name: 'keyword',//关键字
                attrs: {
                    nullText: lang('KEYWORD'),
                    'data-ligerui': 'textBox'
                }
            }];
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
            var tabs        = require('core/tabs'),
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
            var tabs = require('core/tabs');
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

            var defaults = {
                sort: Q2O.sort || 'admin_id',//排序字段
                order: (Q2O.order || 'desc').toLowerCase(),//排序
                start_date: Q2O.start_date || '',//添加时间,开始
                end_date: Q2O.end_date || '',//添加时间,结束
                keyword: Q2O.keyword || '',//关键字
                role_id: Q2O.role_id || 0,//角色id
                column: Q2O.column || 'username',//搜索字段
                match_mode: Q2O.match_mode || 'eq',//匹配模式
                is_lock: undefined === Q2O.is_lock ? -1 : Q2O.is_lock,//锁定状态
                is_restrict: undefined === Q2O.is_restrict ? -1 : Q2O.is_restrict,//绑定登陆状态
                page: Q2O.page || 1,//页
                page_size: Q2O.page_size || 20//每页大小
            };

            var queryParams = require('core/tree').getData(C, A).queryParams;

            var prevQueryParams = _.clone(queryParams);

            $.extend(queryParams, defaults);

            var options = {
                parms: queryParams,
                sortName: defaults.sort,
                sortOrder: defaults.order,
                page: defaults.page,
                newPage: defaults.page,
                pageSize: defaults.page_size,
            };

            var element = require('core/tabs').get('_ligerTab').tab.content.children('#adminlist');

            if (!this._listgrid) {
                this._listgrid = element.ligerGrid($.extend(options, {

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
                    url: this._getActionUrl(),
                    onChangePage: function(page) {
                        this.options.parms[this.options.pageParmName] = page;
                        require('core/router').navigate(o2q(this.options.parms));
                    },
                    topBar: this._toolbar(),
                    onRendered: function() {
                        var g       = this,
                            grid    = this.grid;

                    grid.topbar.find('input[data-type=datetime]').ligerDateEditor();
                    grid.topbar.children('.combotree').children('input').ligerComboBox({
                        tree: {
                            checkbox: false,
                            needCancel: false,
                            isExpand: false,
                            url: '../get_tree.php',
                            textFieldName: 'menu_name',
                            idFieldName: 'menu_id',
                            parentIDFieldName: 'parent_id'
                        },
                        treeLeafOnly: false,
                        isMultiSelect: true,
                        isShowCheckBox: false,
                        //slide: true,
                        valueField: 'menu_id',
                        textField:'menu_name',
                        selectBoxWidth: 250,
                        selectBoxHeight: 400,
                        onSelected: function() {
                            this._toggleSelectBox(true);
                        }
                    });
                    grid.topbar.children('.role').children('input').ligerComboBox({
                        initValue: 1,
                        data: [{
                            id: 0,
                            text: '所属角色'
                        }, {
                            text: '站长',
                            id: 1
                        }, {
                            text: '超级管理员',
                            id: 2
                        }]
                    });
                    grid.topbar
                    .children('.keyword')
                        .children('input')
                        .on('keypress', function(e) {

                            if (10 == e.keyCode || 13 == e.keyCode) {
                                var values = {};

                                $.each(grid.topbar.find('input[data-ligerui]'), function() {
                                    var g           = $(this),
                                        plugin      = g.attr('data-ligerui').capitalize(),
                                        name        = g.attr('name');

                                    if ('DateEditor' == plugin) {
                                        values[name] = g.val();
                                    }
                                    else {
                                        values[name] = g['ligerGet' + plugin + 'Manager']().getValue()
                                    }
                                });

                                $.extend(queryParams, values);
                                $.extend(me._listgrid.options.parms, queryParams);
                                me._listgrid.reload();
                                require('core/router').navigate(o2q(queryParams));
                            }
                        })
                        .ligerTextBox();
                    grid.topbar.children('.operate').ligerMenuBar({
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
                    this.toolbar.find('select').unbind('change').change(function() {
                        g.changePageSize(this.value, queryParams);
                    });

                            /*var toolbar = grid.children('.l-panel-topbar');
                            toolbar.children('.menubar').ligerMenuBar({
                                items: [{text: '文件'}]
                            });
        */
                            //grid.find('.role').ligerComboBox({width: 50});
                            //grid.children('.l-panel-topbar').children('input').ligerTextBox();

                            grid.find('#adminlistgrid').bind('click', function(e) {log(e);
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
                    }));
            }
            else if(o2q(prevQueryParams) != o2q(queryParams)) {
                $.extend(this._listgrid.options, options);
                this._listgrid.reload();
            }o2q(prevQueryParams) != o2q(queryParams);
        }
    });

    var admin = new Admin();
    module.exports = admin;
});