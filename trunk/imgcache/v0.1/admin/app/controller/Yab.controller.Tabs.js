/**
 * 标签选项卡控制器
 *
 * @file            app/controller/Yab.controller.Tabs.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-03-17 10:21:44
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.controller.Tabs', {
    extend: 'Yab.controller.Base',
    /**
     * @cfg {Object}
     * @private
     * 依赖js
     */
    requireScript: {},//依赖js by mrmsl on 2012-12-10 14:55:17
     /**
     * @cfg {Object}
     * @private
     * 控制器实例缓存
     */
    cache: {},

    /**
     * 控制器方法不存在
     *
     * @private
     *
     * @param {String} controller 控制器名
     * @param {String} action     方法名
     *
     * @return {void} 无返回值
     */
    actionNotExist: function(controller, action) {
        error(Ext.String.format(lang('CONTROLLER') + '<span class="font-red">{0}</span> ' + lang('METHOD') + '<span class="font-red">{1}</span>', controller, action) + lang('NOT_EXIST'));
    },

    /**
     * 添加标签
     *
     * @param {Object} data 当前标签数据
     * @param {Object} [opt] 控制是否高亮，动画
     *
     * @return {void} 无返回值
     */
    addTabFromTree: function(data, opt) {
        opt = opt ||
        {
            animate: true,
            active: true
        };
        Yab.cmp.tabs.addTab(data, opt);
    },

    /**
     * 标签关闭事件监听
     *
     * @private
     *
     * @param {Object} view 标签视图
     *
     * @return {Object} this
     */
    addTabIconListeners: function(view) {
        view.el.on({
            click: function(e, f) {
                view.justClosed = true;//标志关闭，防止冒泡
                var id = Ext.get(f).up('.apptab').down('.tabUrl').getAttribute('id');
                Yab.cmp.tabs.removeTab(id);
            },
            mouseover: function(d, a) {
                Ext.get(a).addCls('ovr')
            },
            mouseout: function(d, a) {
                Ext.get(a).removeCls('ovr')

            },
            preventDefault: true,
            delegate: '.close'
        });

        return this;
    },

    /**
     * 标签事件监听
     *
     * @private
     *
     * @param {Object} view 标签视图
     *
     * @return {void} 无返回值
     */
    addTabListeners: function(view) {
        view.el.on({
            click: function(e, f) {

                if (view.justClosed) {
                    view.justClosed = false;
                    return true;
                }

                var el = Ext.get(f);
                if (!el.hasCls('active')) {
                    var a = el.down('.tabUrl').getAttribute('href');
                    Yab.History.push(a);
                }
            },
            contextmenu: function(e, element) {//右键菜单 by mrmsl on 2012-07-28 16:39:36
                var me = this;
                var el = Ext.get(element).down('.tabUrl');
                var controller = el.dom.id;//控制器
                var tabs = Yab.cmp.tabs;//标签容器
                tabs.activeTab != controller && Yab.History.push(el.getAttribute('href'));//活跃标签
                var recentTabs = tabs.recentTabs;//最近操作
                var recentItems = [];//最近操作 Ext.menu.items

                Ext.each(recentTabs, function(item, index) {//最近操作
                    recentItems.push({
                        text: (index + 1) + '. ' + Yab.cmp.viewport.cache[item.controller + item.action],
                        handler: function() {
                            Yab.History.push(item);
                        }
                    })
                });

                this._menu && this._menu.destroy();
                this._menu = Ext.create('Ext.menu.Menu', {
                    items: [{
                        disabled: controller == 'index',//首页标签卡禁用
                        text: lang('REFRESH'),//刷新
                        handler: function() {
                            global('app_contextmenu_refresh', true);//开启刷新标识
                            var data = tabs.getActiveTab(controller);//标签数据
                            var Class = me.cache[Ext.String.capitalize(controller)];
                            var method = Class[data.action + 'Action'] || Class.__call;//by mrmsl on 2012-09-10 21:38:23
                            method.call(Class, data);
                            global('app_contextmenu_refresh', false);////关闭刷新标识
                        }
                    }/*, {
                        disabled: controller == 'index',
                        text: lang('OPEN_IN_NEW_WINDOW'),//在新窗口中打开
                        handler: function() {
                            tabs.removeTab(controller);
                            window.open('#' + Ext.Object.toQueryString(tabs.tabCache[controller]));
                        }
                    }*/, {
                        disabled: controller == 'index',
                        text: lang('CLOSE'),//关闭
                        handler: function() {
                            tabs.removeTab(controller);
                        }
                    }, {
                        text: lang('CLOSE,OTHER'),//关闭其它
                        disabled: controller == 'index' && tabs.tabs.length == 0 || controller != 'index' && tabs.tabs.length == 1,
                        handler: function() {
                            tabs.closeAllTabs(controller);
                        }
                    }, {
                        text: lang('ALL,CLOSE'),//全部关闭
                        disabled: !tabs.tabs.length,
                        handler: function() {
                            tabs.closeAllTabs();
                            getController('Index').loadIndex()
                        }
                    }, {
                        xtype: 'menuseparator'//分割线 by mrmsl on 2012-08-09 12:59:33
                    }, {
                        text: lang('RECENT_ACTION'),//最近操作 by mrmsl on 2012-08-09 12:53:36
                        disabled: !recentItems.length,
                        menu: {
                            items: recentItems
                        }
                    }]
                });

                this._menu.showAt(e.getXY());
            },//end contextmenu
            preventDefault: true,
            delegate: '.apptab',
            scope: this
        });

        view.el.on({
            click: Ext.emptyFn,
            delegate: 'a.tabUrl',
            preventDefault: true
        });
    },//end addTabListeners

    /**
     * 执行javascript脚本
     *
     * @private
     *
     * @param {String} Class 类名
     * @param {Object} data  数据，Ext.Object.fromQueryString(location.hash)
     *
     * @return {Boolean} 成功执行，返回true，否则返回false
     */
    execScript: function(Class, data) {
        setLoading(false);

        if (Class === 'loading') {
            return true;
        }

        var controller = data.controller, action = data.action, method = Class[action + 'Action'] || Class.__call;

        if (!method) {//方法不存在
            this.actionNotExist('Yab.controller.' + Ext.String.capitalize(controller), action + 'Action');
            return false;
        }

        var store = Yab.cmp.tree.selectUrl(controller, action);//高亮选中菜单

        data.text = store ? store.get('menu_name') : lang('NO_TITLE');

        Yab.cmp.viewport.setPageTitle(controller, action);//设置页面标题
        this.addTabFromTree(data);//添加标签
        method.call(Class, data);//执行方法

        return true;
    },//end execScript

    /**
     * 初始化
     *
     * @private
     *
     * @return {void} 无返回值
     */
    init: function() {
        var me = this;

        this.control({
            'container [componentCls=apptabs]': {

                /**
                 * 完成渲染
                 *
                 * @param {Object} view 标签视图
                 *
                 * @return {void} 无返回值
                 */
                afterrender: function(view) {
                    this.addTabIconListeners.call(me, view).addTabListeners.call(me, view);
                },
                scope: me
            }
        });
    },


    /**
     * 加载javascript脚本
     *
     * @param {String} hash location.hash
     *
     * @return {Boolean}
     */
    loadScript: function(hash, nav) {
        hash = hash.indexOf('#') == 0 ? hash.substr(1) : hash;
        var data = Ext.Object.fromQueryString(hash, true);//fromQueryString(hash, true)，确保重复参数只取最后一个 by mrmsl on 2012-08-23 12:50:49
        var controller = data.controller || 'index', action = data.action || 'index';

        nav || Yab.History.push(hash, true);

        if (!checkPriv(controller, action)) {//权限判断by mrmsl on 2012-07-12 16:35:19
            return false;
        }

        if (controller == 'index' && action == 'index' ) {//首页
            getController('Index').loadIndex();
            return true;
        }

        var activeTab = Yab.cmp.tabs.getActiveTab();

        if (activeTab && activeTab.controller == controller && activeTab.action == action) {//正显示
            //this.addTabFromTree(data);
            //return true;
        }

        controller = Ext.String.capitalize(controller);

        var viewport = Yab.cmp.viewport, controllerClassName = 'Yab.controller.' + controller;;

        setLoading(lang('LOADING'));//设置加载中提示

        //data.controllerClassName = controllerClassName;

        if (this.cache[controller]) {//已经加载
            this.execScript(this.cache[controller], data);
        }
        else {
            this.cache[controller] = 'loading';
            //本地环境，加载未压缩js
            var url = System.IS_LOCAL ? Ext.Loader.getPath('Yab#controller') + controllerClassName + '.js' : Ext.Loader.getPath('Yab#pack') + controllerClassName + '.pack.js';

            var request = Ext.data.JsonP.request({
                url: url,
                disableCaching: System.IS_LOCAL,//不禁用缓存 by mrmsl on 2012-08-30 12:18:46
                timeout: 3000,
                callbackName: controllerClassName.replace(/\./g, '_'),

                /**
                 * 加载成功回调函数
                 *
                 * @param {String} className 类名
                 *
                 * @return {void} 无返回值
                 */
                success: function(require, className) {
                    if (Ext.isFunction(require)) {//无依赖js by mrmsl on 2012-12-10 14:56:37
                        this.onScriptLoaded(require, controller, data);
                    }
                    else {//[['Yab.store.Admin', 'Yab.store.store.Role'], Ext.define('Yab.controller.Admin') {}]
                        className = require[1];
                        require = require[0];
                        require = Ext.isString(require) ? require.split(',') : require;

                        Ext.each(require, function(item) {//循环加载js脚本

                            Ext.require(item, Ext.bind(function(item, className, controller, data, require) {
                                this.requireScript[item] = true;
                                this.onScriptLoaded(className, controller, data, require);
                            }, this, [item, className, controller, data, require]));
                        }, this);
                    }
                },

                /**
                 * 加载完成回调函数
                 *
                 * @return {void} 无返回值
                 */
                callback: function() {
                    setLoading(false);
                },

                /**
                 * 加载失败回调函数
                 *
                 * @param {String} error 错误代号
                 *
                 * @return {void} 无返回值
                 */
                failure: function(err) {
                    error(Ext.String.format('<span class="font-red">{0}</span>{1}<br />{2}：<span class="font-red">{3}</span>', url, lang('LOAD,FAILURE'), lang('ERROR,INFO'),  err));
                },
                scope: this
            });
        }

        return true;
    },//end loadScript

    /**
     * 启动
     *
     * @private
     *
     * @return {void} 无返回值
     */
    onLaunch: function() {
        Yab.History.notifyTabsLoaded();
    },

    /**
     * 控制器加载完成回调
     *
     * @private
     *
     * @param {Function} className  类
     * @param {String}   controller 控制器
     * @param {Object}   data       控制器数据
     * @param {Mixed}    [require]  依赖js数组，务必Ext.Loader.setPath()已设有脚本前缀，如'Yab.store.Admin': System.sys_base_js_url + 'app/store/Yab.store.Admin.js'
     *
     * @return {void} 无返回值
     */
    onScriptLoaded: function(className, controller, data, require) {

        if (require) {
            var result = true;

            Ext.each(require, function(item) {//检测依赖脚本是否已经全部加载完

                if (!this.requireScript[item]) {
                    result = false;
                    return false;
                }
            }, this);

            if (!result) {//依赖脚本未全部加载完，100毫秒后再次检测
                return Ext.defer(this.onScriptLoaded, 100, this, arguments);
            }
        }

        this.cache[controller] = Ext.create(className);
        this.execScript(this.cache[controller], data);
    }
});