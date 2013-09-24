/**
 * 标签选项卡
 *
 * @file            tabs.js
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-09-21 11:41:50
 * @lastmodify      $Date$ $Author$
 */

define('tabs', ['base', 'tree'], function(require, exports, module) {
    var Base    = require('base');
    var Tabs    = Base.extend({
        /**
         * var {object} _el 标签栏jquery对象
         */
        _el: null,

        /**
         * var {array} _recentTabs 最近操作
         */
        _recentTabs: [],

        /**
         * var {object} _currentTab 当前标签
         */
        _currentTab: {},

         /**
         * @var {object} _tabCache 标签缓存{controller: controller, action: action}
         *
         */
        _tabCache: {},

        /**
         * @var {object} _controllerObj 控制器对象
         *
         */
        _controllerObj: {},

        /**
         * @var {object} _ligerTab ligerTab对象
         *
         */
        _ligerTab: null,

        /**
         * 检测所有标签中是否包含有指定标签
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-08-03 09:45:22
         *
         * @param {string} controller 标签controller
         *
         * @return {bool} true存在，否则false
         */
        _hasTab: function(controller) {
            return $.in_array(controller, this.tabs);
        },

        /**
         * 设置活跃面板
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-08-30 09:03:43
         *
         * return {void} 无返回值
         */
        _setActivePanel: function() {
            var tabs    = this.getSelected(),
                id      = C + A;

            tabs
                .children()
                .hide()
            .end()
            .find('#' + id)
            .show();

            if ('list' == A) {
                var grid = tabs.find('#grid-' + id);
                grid.length && grid.parents('div.panel.datagrid:first').show();
            }
        },

        /**
         * 设置最近操作
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-09-10 15:02:39
         *
         * @return {void} 无返回值
         */
        _setRecentTabs: function(controller, action) {
            var me = this;

            $.each(this._recentTabs, function(index, item) {
                //干掉标签栏显示的
                if (item.controller == controller && item.action == action) {
                    me._recentTabs.splice(index, 1);
                    return false;
                }
            });
        },

        /**
         * 添加标签
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-08-03 09:46:32
         *
         * @param {int} menu_id 菜单id
         *
         * @return {void} 无返回值
         */
        addTab: function(controller, action) {
            C = controller;
            A = action;
            ID = C + A;
            TREE_DATA = require('tree').getData(controller, action);

            Alert('加载中，请稍后...', 'loading', false, false);
            var controller  = TREE_DATA.controller,
                action      = TREE_DATA.action;

            if (this._ligerTab.isTabItemExist(controller)) {
                this._ligerTab.setHeader(controller, TREE_DATA.menu_name);
                this._ligerTab.selectTabItem(controller);
            }
            else {
                this._ligerTab.addTabItem({
                    tabid: controller,
                    text: TREE_DATA.menu_name
                });
            }

            /*this._setRecentTabs(controller, action);

            //相同控制器不同操作，加入最近操作
            if (this._tabCache[controller] && this._tabCache[controller].action != action){
                this._recentTabs.unshift(this._tabCache[controller]);
            }

            this._tabCache[controller] = {
                controller: controller,
                action: action,
                id: TREE_DATA.menu_id
            };*/

            this.loadScript();
        },

        /**
         * 启动
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-08-01 15:21:41
         *
         * return {void} 无返回值
         */
        bootstrap: function() {
            var me = this;

            this._el = $('#tabs');
            this._el.ligerTab({
                height: $('.l-layout-center').height(),
                contextmenu: false,
                onBeforeSelectTabItem: function() {
                    this._prevSelectedTabId = this.selectedTabId;
                    log('onBeforeSelectTabItem' + this.selectedTabId);
                },
                onAfterSelectTabItem: function(selectedTabId) {
                    log('onAfterSelectTabItem');
                    var router = require('router');

                    if (!global('clickTree') && TREE_DATA) {

                        if ('index' != selectedTabId) {
                            require('tree').get('_ligerTree').tree.find('li[menu_id=' + TREE_DATA.menu_id + ']')
                            .parents('ul.l-children:not(:visible)')
                            .prev('div.l-body')
                            .children('.l-expandable-close')
                            .click();

                            getHash() != object2querystring(TREE_DATA.queryParams) && router.navigate(object2querystring(TREE_DATA.queryParams), true);
                        }
                        else if(this._prevSelectedTabId != selectedTabId) {
                            router.index();
                        }
                    }
                }
            });
            this._ligerTab = this._el.ligerGetTabManager();
        },//end bootstrap

        /**
         * 获取选中tab
         *
         * @author      mrmsl <msl-138@163.com>
         * @date        2013-08-01 17:07:10
         *
         * return {function} $.fn.tabs
         */
        getSelected: function() {
            return this._el.tabs('getSelected');
        },

        /**
         * 构造函数
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-08-01 15:43:06
         *
         * return {void} 无返回值
         */
        hideSelected: function() {
            this.getSelected().hide();
        },

        /**
         * 构造函数
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-08-01 15:43:06
         *
         * return {void} 无返回值
         */
        loadScript: function(controller, action) {
            controller  = controller || C;
            action      = action || A;
            Q2O         = querystring2object(getHash()),
            selected    = this._ligerTab.getSelected();

            var me          = this,
                //selected    = this.getSelected(),
                callback    = function(o, method) {
                    if (o[method]) {
                        o[method]();
                    }
                    else if (o.__call){
                        o.__call();
                    }
                    else {
                        return $.ligerDialog.error('控制器{0}方法{1}不存在'.format(TEXT.red(controller), TEXT.red(action)));
                    }

                    require('router').setPageTitle();
                    Alert(null, null, true);
                };

            seajs.use(controller, function(o) {
                if (!o) {
                    var url = require.resolve(controller)
                    return $.ligerDialog.error('加载' + TEXT.red(url.split('?').shift()) + '失败')
                }

                me._controllerObj = o;
                var method      = action + 'Action',
                    contentItem = me._ligerTab.tab.content;//.children('.l-tab-content-item[tabid=' + selected.attr('tabid') + ']');

                if (contentItem.children('#' + controller + action).length) {
                    callback(o, method);
                }
                else {
                    $.get('http://localhost/ligerui/yablog/action.php?controller={0}&action={1}'.format(controller, action), function(data) {
                        //$('<div id="' + controller + action + '"></div>').html(data).appendTo(selected);
                        contentItem.append(data);
                        global('FIRST_LOAD', true);
                        callback(o, method);
                        global('FIRST_LOAD', false);
                    });
                }

            });
        }//end loadScript
    });

    module.exports = new Tabs();
});