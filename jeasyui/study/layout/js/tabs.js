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
         * var {array} _staticTabs 固定标签
         */
        _staticTabs: [],

         /**
         * @var {object} _tabCache 标签缓存{controller: data}
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
                tabIndex    = this._el.tabs('tabIndex', menuData.controller);

            if (-1 == tabIndex) {
                this._el.tabs('add', {
                    //onLoad: function() {log(arguments);},
                    title: menuData.menu_name,
                    closable: true,
                    content: '',
                    //href: 'http://localhost/jeasyui/yablog/study/layout/action.php?c={0}&a={1}'.format(menuData.controller, menuData.action),
                    //id: menuData.controller + menuData.action,
                    style: {
                        padding: '8px'
                    },
                    options: {
                        controller: menuData.controller,
                        action: menuData.action,
                        id: menuData.id
                    }
                });
            }
            else {

                if (!this._el.tabs('exists', menuData.menu_name)) {
                    var tab = this._el.tabs('getTab', tabIndex);

                    $.extend(tab.panel('options').options, {
                        action: menuData.action,
                        id: menuData.menu_id
                    });

                    this._el.tabs('update', {
                        tab: this._el.tabs('getTab', tabIndex),
                        options: {
                            //href: 'http://localhost/jeasyui/yablog/study/layout/action.php?c={0}&a={1}'.format(menuData.controller, menuData.action),
                            //content: menuData.menu_name,
                            title: menuData.menu_name
                        }
                    });

                    this._setActivePanel();
                }

                this._el.tabs('select', menuData.menu_name);
            }

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
                onSelect: function (title, index) {
                    var tab = me._el.tabs('getTab', index);
                    var options = tab.panel('options').options;
                    //log(tab, options, TREE_DATA);

                    //options && me.loadScript(options.controller, options.action);
                },
                onBeforeClose: function(title, index) {
                    log($(this).tabs('getTab', index).options);
                },
                onClose: function() {
                    log($(this).data('tabs').selectHis);log($(this).tabs('getSelected'));
                },
                _createContextMenu: function() {

                    if (!$('#tab-contextmenu').length) {
                        var o = $('<div id="tab-contextmenu"></div>')
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
                onContextMenu: function(e, title, index) {
                    var options = $(this).tabs('options');

                    if (!options._contextmenu) {
                        options._createContextMenu();
                        options._contextmenu = true;
                    }

                    var o = $('#tab-contextmenu')
                    .menu('show', {
                        left: e.pageX,
                        top: e.pageY
                    });

                    $.extend(o.menu('options'), {
                        onClick: function() {
                            log(arguments);
                        }
                    });

                    e.preventDefault();
                }
            })
            .tabs()
            .children('div.tabs-header')
                .find('ul.tabs')
                .on('click', 'li',  function() {
                    var options = me.getSelected().panel('options').options;

                    if (options) {
                        var queryParams = require('tree').get('_treeData')[options.id].queryParams;
                        require('router').navigate(object2querystring(queryParams));
                    }
                    else {
                        require('router').index();
                    }
                });
        },

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
                    me._setPageTitle();
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