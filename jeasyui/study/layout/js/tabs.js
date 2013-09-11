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
         * @var {object} _tabData 所有标签数据{controller_action: data}
         *
         */
        _tabData: {},

        /**
         * var {array} _tabs 标签[controller]
         */
        _tabs: [],

        /**
         * var {array} {Array} 在标签栏内标签[controller]
         */
        _tabsInBar: [],

        /**
         * 继承$.fn.tabs.methods
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-08-01 15:06:08
         *
         * return {void} 无返回值
         */
        _extendMethods: function() {
            var me = this;

            $.extend($.fn.tabs.methods, {

                /**
                 * 返回控制器controller标签在标签栏中的索引位置
                 *
                 * @author      mrmsl <msl-138@163.com>
                 * @date        2013-08-01 15:06:08
                 *
                 * return {int} 标签存在,返回索引位置,否则-1
                 */
                tabIndex: function(jq, controller) {
                    var index = -1;

                    $.each(me._el.tabs('tabs'), function(k, v) {
                        var options = v.panel('options').options;

                        if (options && options.controller == controller) {
                            index = k;
                            return false;
                        }
                    });

                    return index;
                },

                newOnSelect: function(jq, navigation) {
                    var options = me.getSelected().panel('options').options;

                    if (options) {
                        var queryParams = require('tree').get('_treeData')[options.id].queryParams;
                        require('router').navigate(object2querystring(queryParams), navigation);
                    }
                    else {
                        require('router').index();
                    }
                }
            });
        },

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

            this._extendMethods();
            this._el.data('data-options', {
                onSelect: function() {
                    var flag = global('from_onClose');

                    if (flag) {
                        global('from_onClose', false);
                        $(this).tabs('newOnSelect', true);
                    }
                },
                onClose: function() {
                    global('from_onClose', true);
                    me._recentTabs.unshift(me._tabCache[me._currentTab.controller]);
                },
                _createContextMenu: function(index) {
                    var options     = $(me._el).tabs('getTab', index).panel('options').options,
                        recentTabs  = me._recentTabs,
                        tabs        = $(me._el).data('tabs').tabs,
                        _0          = 1 == tabs.length,
                        id          = 'tab-contextmenu';

                    this._contextmenu && $('#' + id).menu('destroy');
                    this._contextmenu = id;

                    var o = $('<div id="' + id + '"></div>')
                    .appendTo($('body'))
                    .menu()
                    .menu('appendItems', [{
                        disabled: !options,//首页标签卡禁用
                        text: '刷新',//刷新
                        handler: function() {log('refresh');
                        }
                    }, {
                        disabled: !options,
                        text: '关闭<div class="menu-sep"></div>',//关闭
                        handler: function() {
                            log('close');
                        }
                    }, {
                        text: '关闭其它',//关闭其它
                        disabled: !options && _0 || options && 'index' != options.controller && 2 == tabs.length,
                        handler: function() {
                            log('close other');
                        }
                    }, {
                        text: '全部关闭',//全部关闭
                        disabled: _0,
                        handler: function() {
                            log('close all');
                        }
                    }, {
                        text: '最近操作',//最近操作
                        disabled: 0 == recentTabs.length
                    }]);

                    if (0 != recentTabs.length) {
                        var parent      = o.menu('findItem', '最近操作'),
                            recentItems = [];//最近操作

                        $.each(me._recentTabs, function(index, item) {//最近操作
                            o.menu('appendItem', {
                                name: item.id,
                                parent: parent.target,
                                text: (index + 1) + '. ' + me._pageTitle[item.controller + item.action],
                                handler: function() {
                                    require('router').navigate(object2querystring(require('tree').get('_treeData')[item.id].queryParams), true);
                                }
                            });
                        });
                    }
                },
                onContextMenu: function(e, title, index) {
                    var options = $(this).tabs('options');

                    options._createContextMenu(index);

                    var o = $('#' + options._contextmenu)
                    .menu('show', {
                        left: e.pageX,
                        top: e.pageY
                    });

                    e.preventDefault();
                }
            })
            .tabs()
            .children('div.tabs-header')
                .find('ul.tabs')
                .on('click', 'li', function() {
                    $(me._el).tabs('newOnSelect');
                });
        },//end bootstrap

        /**
         * 构造函数
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-08-01 15:43:06
         *
         * return {void} 无返回值
         */
        constructor: function() {
            this.base();
            this._el = $('#tabs');
        },

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
                };

            seajs.use(controller, function(o) {
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

    var tabs = new Tabs();

    tabs.bootstrap();

    module.exports = tabs;
});