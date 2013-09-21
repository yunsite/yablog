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
        addTab: function(menu_id) {
            Alert('加载中，请稍后...', 'loading', false, false);
            var menuData    = require('tree').get('_treeData')[menu_id],
                controller  = menuData.controller,
                action      = menuData.action,
                tabIndex    = this._el.tabs('tabIndex', controller);

            this._currentTab = menuData;

            if (-1 == tabIndex) {
                this._el.tabs('add', {
                    //onLoad: function() {log(arguments);},
                    title: menuData.menu_name,
                    closable: true,
                    content: '',
                    //href: 'http://localhost/jeasyui/yablog/study/layout/action.php?c={0}&a={1}'.format(controller, action),
                    //id: controller + action,
                    style: {
                        padding: '8px'
                    },
                    options: {
                        controller: controller,
                        action: action,
                        id: menuData.id
                    }
                });
            }
            else {

                if (!this._el.tabs('exists', menuData.menu_name)) {
                    var tab = this._el.tabs('getTab', tabIndex);

                    $.extend(tab.panel('options').options, {
                        action: action,
                        id: menuData.menu_id
                    });

                    this._el.tabs('update', {
                        tab: this._el.tabs('getTab', tabIndex),
                        options: {
                            //href: 'http://localhost/jeasyui/yablog/study/layout/action.php?c={0}&a={1}'.format(controller, action),
                            //content: menuData.menu_name,
                            title: menuData.menu_name
                        }
                    });

                    this._setActivePanel();
                }
                this._el.tabs('select', menuData.menu_name);
            }

            this._setRecentTabs(controller, action);

            //相同控制器不同操作，加入最近操作
            if (this._tabCache[controller] && this._tabCache[controller].action != action){
                this._recentTabs.unshift(this._tabCache[controller]);
            }

            this._tabCache[controller] = {
                controller: controller,
                action: action,
                id: menuData.menu_id
            };

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
                    log('onBeforeSelectTabItem' + this.selectedTabId);
                },
                onAfterSelectTabItem: function() {log(this.getSelectedTabItemID());
                    log('onAfterSelectTabItem' + this.selectedTabId);
                }
            });
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
            Q2O         = querystring2object(getHash());

            var me          = this,
                selected    = this.getSelected(),
                callback    = function(o, method) {
                    o[method]();
                    me.setPageTitle();
                    Alert(null, null, true);
                };

            seajs.use(controller, function(o) {
                me._controllerObj = o;
                var method = action + 'Action';

                if (selected.children('#' + controller + action).length) {
                    callback(o, method);
                }
                else {
                    $.get('http://localhost/jeasyui/yablog/study/layout/action.php?controller={0}&action={1}'.format(controller, action), function(data) {
                        //$('<div id="' + controller + action + '"></div>').html(data).appendTo(selected);
                        selected.append(data);
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