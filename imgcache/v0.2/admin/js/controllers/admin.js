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
                role_id: Q2O.role_id || '',//角色id
                column: Q2O.column || 'username',//搜索字段
                match_mode: Q2O.match_mode || 'eq',//匹配模式
                is_lock: undefined === Q2O.is_lock ? -1 : Q2O.is_lock,//锁定状态
                is_restrict: undefined === Q2O.is_restrict ? -1 : Q2O.is_restrict,//绑定登陆状态
                page: Q2O.page || 1,//页
                page_size: Q2O.page_size || 20//每页大小
            };

            var queryParams = require('core/tree').getData(C, A).queryParams;

            var prevQueryParams = _.clone(queryParams);

            queryParams.sort && $.extend(queryParams, defaults);

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
                    selectRowButtonOnly: true,
                    frozen: false,
                    height: '100%',
                    pageParmName: 'page',
                    pagesizeParmName: 'page_size',
                    sortnameParmName: 'sort',
                    sortorderParmName: 'order',
                    width: 0,
                    onSuccess: function() {
                        var options = this.options;
                        this.gridheader.find('.l-grid-hd-cell-sort').remove()
                        .end().find('td[columnname=' + options.sortName + ']').children('div').append('<span class="l-grid-hd-cell-sort l-grid-hd-cell-sort-' + options.sortOrder.toLowerCase() + '">&nbsp;&nbsp;</span>');
                        this.toolbar.find('select').val(options.pageSize);
                        //log(this.toolbar.find('select'));
                    },
                    onChangeSort: function(sort, order) {log(require('core/tree').getData(C, A).queryParams.sort);
                        $.extend(queryParams, {
                            sort: sort,
                            order: order
                        });
                        require('core/router').navigate(o2q(queryParams));log(require('core/tree').getData(C, A).queryParams.sort);
                    },
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
                    url: this._getActionUrl(),
                    rownumbers: true,
                    checkbox: true,
                    onChangePage: function(page) {
                        this.options.parms[this.options.pageParmName] = page;
                        require('core/router').navigate(o2q(this.options.parms));
                    },
                    onRendered: function() {
                        var g       = this,
                            grid    = this.grid;
                            html    = [];

                    html.push(' <div class="l-panel-topbar">');
                    html.push('     <div class="l-panel-bbar-inner">');
                    html.push('         <div class="l-bar-group  l-bar-message"><span class="l-bar-text"></span></div>');
                    html.push('         <div class="l-bar-group operate toolbar"></div>');
                    html.push('         <div class="l-bar-separator"></div>');
                    html.push('         <div class="l-bar-group">添加时间从</div>');
                    html.push('         <div class="l-bar-group"><input type="text" data-type="datetime" name="start_date" data-ligerui="dateEditor" size="8" /></div>');
                    html.push('         <div class="l-bar-group">到</div>');
                    html.push('         <div class="l-bar-group"><input type="text" data-type="datetime" name="end_date" data-ligerui="dateEditor" size="8" /></div>');
                    html.push('         <div class="l-bar-separator"></div>');
                    html.push('         <div class="l-bar-group combotree"><input type="text" name="menu_id" data-ligerui="comboBox" /></div>');
                    html.push('         <div class="l-bar-group role"><input type="text" name="role_id" data-ligerui="comboBox" /></div>');
                    html.push('         <div class="l-bar-group keyword"><input type="text" name="keyword" nullText="关键字" data-ligerui="textBox" /></div>');
                    html.push('     </div>');
                    html.push(' </div>');
                            grid.children('.l-grid-loading').after(html.join(''));
                    grid.topbar = grid.children('.l-panel-topbar').children('.l-panel-bbar-inner');
                    grid.topbar.find('input[data-type=datetime]').ligerDateEditor({
                        format: 'yyyy-MM-dd hh:mm',
                        showTime: true
                    });
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
                        initValue: 0,
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

                                $.each(o.grid.topbar.find('input[data-ligerui]'), function() {
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
                                require('core/router').navigate(o2q(queryParams), true);
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
            }
        }
    });

    var admin = new Admin();
    module.exports = admin;
});