/**
 * 视图标签栏选项卡
 *
 * @file            app/view/Yab.view.Tabs.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-03-17 09:13:44
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.view.Tabs', {
    extend: 'Ext.container.Container',
    alias: 'widget.apptabs',
    /**
     * @cfg {Number}
     * 标签动画效果持续时间，单位：毫秒
     */
    animDuration: 150,
    /**
     * @cfg {Number}
     * 视图最小宽度
     */
    minWidth: 500,
    /**
     * @cfg {String}
     * componentCls
     */
    componentCls: 'apptabs',
    /**
     * @cfg {String}
     * id
     */
    id: 'appTabs',
    /**
     * @cfg {String}
     * margin
     */
    margin: '0 0 0 200',
    /**
     * @cfg {Number}
     * 选项卡最大宽度
     */
    maxTabWidth: 120,
    /**
     * @cfg {Number}
     * 选项卡最小宽度
     */
    minTabWidth: 50,
    /**
     * @cfg {Array}
     * 最近操作[data]
     */
    recentTabs: [],//最近操作[data] by mrmsl on 2012-08-09 12:48:28
    /**
     * @cfg {Array}
     * 固定标签
     */
    staticTabs: [],
     /**
     * @cfg {Object}
     * 标签缓存{controller: data}
     */
    tabCache: {},
    /**
     * @cfg {Object}
     * 所有标签数据{controller_action: data}
     */
    tabData: {},
    /**
     * @cfg {Array}
     * 标签[controller]
     */
    tabs: [],
    /**
     * @cfg {Array}
     * 在标签栏内标签 [controller]
     */
    tabsInBar: [],

    /**
     * 添加标签
     *
     * @param {Object} data 标签数据
     * @param {Object} opt  控制是否高亮，动画
     *
     * @return {Object} this
     */
    addTab: function(data, opt) {
        var controller = data.controller;
        this.tabData[controller + '_' + data.action] = data;
        this.setRecentTabs(data);//最近操作 by mrmsl on 2012-08-09 12:48:40

        if (!this.hasTab(controller)) {//还没加入标签栏
            this.tabs.push(controller);//加入标签栏
            this.roomForNewTab() && this.addTabToBar(data, opt);
        }
        //相同控制器不同操作，加入最近操作by mrmsl on 2012-08-09 12:48:53
        else if (this.tabCache[controller].controller == controller && this.tabCache[controller].action != data.action){
            this.recentTabs.unshift(this.tabCache[controller]);
        }

        this.tabCache[controller] = data;//加入缓存
        opt.active && this.setActiveTab(controller);

        return this;
    },

    /**
     * 添加标签至标签栏中
     *
     * @private
     *
     * @param {String} data 标签数据
     * @param {Object} opt  控制是否高亮，动画
     *
     * @return {void} 无返回值
     */
    addTabToBar: function(data, opt) {
        !this.inTabBar(data.controller) && this.tabsInBar.push(data.controller);

        var f = Ext.get(this.tabTpl.append(this.el.dom, data));

        if (opt.animate) {
            f.setStyle({
                width: 10
            }).animate({
                to: {
                    width: this.tabWidth()
                }
            })
        }

        this.resizeTabs(opt);
    },

    /**
     * 关闭所有标签
     *
     * @param {String} [controller] 保留标签，标签页右键菜单关闭其它标签页时调到 by mrmsl on 2012-07-31 17:19:37
     *
     * @return {void} 无返回值
     */
    closeAllTabs: function(controller) {
        var me = this;

        Ext.each(this.tabs, function(item) {
            controller != item && me.removeTabFromBar(item);
        });

        //一定不能this.tabs = this.tabsInBar，这相当于php的引用 by mrmsl on 2012-07-31 21:38:13
        if (controller) {
            this.tabs = [controller];
            this.tabsInBar = [controller];
            this.resizeTabs();
        }
        else {
            this.tabs = [];
            this.tabsInBar = []
        }
    },

    /**
     * 获取活跃标签信息
     *
     * @param {String} [controller=当前活跃标签] 标签controller
     *
     * @return {Object} 标签信息
     */
    getActiveTab: function(controller) {
        return controller == 'index' ? this.staticTabs[0] : this.tabCache[controller || this.activeTab];
    },

    /**
     * 获取活跃标签信息
     *
     * @param {String} controller 标签controller
     * @param {String} action     标签action
     *
     * @return {Object} 标签信息
     */
    getTabData: function(controller, action) {
        return this.tabData[controller + '_' + action];
    },

    /**
     * 获取标签html元素
     *
     * @private
     *
     * @param {String} controller 标签controller
     *
     * @return {Mixed} 如果获取成功，则返回html元素，否则返回false
     */
    getTabEl: function(controller) {
        var d = Ext.query('.apptab a[id=' + controller + ']', this.el.dom);

        if (d && d[0]) {
            return Ext.get(d[0]).up('.apptab');
        }

        return false;
    },

    /**
     * 检测所有标签中是否包含有指定标签
     *
     * @private
     *
     * @param {String} controller 标签controller
     *
     * @return {Boolean} 存在返回true，否则返回false
     */
    hasTab: function(controller) {
        return Ext.Array.contains(this.tabs, controller);
    },

    /**
     * 初始化组件
     *
     * @private
     *
     * @return {void} 无返回值
     */
    initComponent: function() {
        this.staticTabs.push({
            href: '#',
            controller: 'index',
            active: 'active',
            text: lang('MANAGE_CENTER'),
        });

        var tpl = '<div style="width: ' + this.maxTabWidth + 'px" class="apptab{[values.active ? (" active") : ""]}">' +
        '<div class="l"></div>' +
        '<div class="m">' +
            '<a class="tabUrl" href="{href}" id="{controller}" title="{text}">{text}</a>' +
        '</div>' +
        '<div class="r">@closable</div>' +
        '</div>';//<tpl if="closable !== false"><a class="close" href="#" title="' + lang('CLOSE') + '">&nbsp;</a></tpl>竟然不行？

        this.html = Ext.create('Ext.XTemplate', '<tpl for=".">' + tpl.replace('@closable', '') + '</tpl>').applyTemplate(this.staticTabs);

        //动态标签
        this.tabTpl = Ext.create("Ext.XTemplate", tpl.replace('@closable', '<a class="close" href="#" title="' + lang('CLOSE') + '">&nbsp;</a>'));

        this.callParent();
    },//end initComponent

    /**
     * 检测链接是否在标签栏中
     *
     * @private
     *
     * @param {String} controller 标签控制器名称
     *
     * @return {Boolean} 存在返回true，否则返回false
     */
    inTabBar: function(controller) {
        return Ext.Array.contains(this.tabsInBar, controller);
    },

    /**
     * 计算标签栏最多可容纳标签标数
     *
     * @private
     *
     * @return {Number} 可容纳标签标数
     */
    maxTabsInBar: function() {
        return Math.floor(this.tabBarWidth() / this.minTabWidth);
    },
    /**
     * 检测是否标签栏是否还有空间容纳新标签
     *
     * @private
     *
     * @return {Boolean} 可容纳返回true，否则返回false
     */
    roomForNewTab: function() {
        return this.tabsInBar.length < this.maxTabsInBar();
    },

    /**
     * 移除标签
     *
     * @private
     *
     * @param {String} controller 标签controller
     *
     * @return {void} 无返回值
     */
    removeTab: function(controller) {

        if (!this.hasTab(controller)) {
            return true;
        }

        var index = Ext.Array.indexOf(this.tabs, controller);

        index != -1 && Ext.Array.erase(this.tabs, index, 1);//删除标签缓存

        index = Ext.Array.indexOf(this.tabsInBar, controller);

        index != -1 && Ext.Array.erase(this.tabsInBar, index, 1);//删除标签栏

        this.tabs[this.tabsInBar.length] && this.tabsInBar.push(this.tabs[this.tabsInBar.length]);

        if (this.activeTab === controller) {//当前活跃标签

            if (this.tabs.length === 0) {//无标签了，加载固定标签
                getController('Index').loadIndex();
            }
            else {

                if (index === this.tabs.length) {//最后一个，前面一个标签变成活跃标签
                    index -= 1;
                }

                this.setActiveTab(this.tabs[index]);
                Yab.History.push(Ext.get(this.tabs[index]).getAttribute('href'));
            }
        }

        this.removeTabFromBar(controller);
        //this.saveTabs();
    },//end removeTab

    /**
     * 从标签栏中移除指定标签
     *
     * @private
     *
     * @param {String} controller 标签controller
     *
     * @return {void} 无返回值
     */
    removeTabFromBar: function(controller) {

        if (controller == 'index') {
            return;
        }

        this.recentTabs.unshift(this.tabCache[controller]);//加入最近操作 by mrmsl on 2012-08-09 12:51:24

        var el = this.getTabEl(controller);
        el.dom.removed = true;
        el.animate({
            to: {
                top: 30
            },
            duration: this.animDuration
        }).animate({
            to: {
                width: 10
            },
            duration: this.animDuration,
            listeners: {
                afteranimate: function() {
                    el.remove();
                    //this.shouldResize = true;
                    this.resizeTabs();
                },
                scope: this
            }
        });
    },//end removeTabFromBar

    /**
     * 重置标签宽度
     *
     * @param {Object} animate 控制是否动画
     * @param {Boolean} [rePosition=undefined] 是否重置标签栏位置，如最小化功能菜单
     *
     * @return {void}无回返值
     */
    resizeTabs: function(animate, rePosition) {

        if (this.resizing) {//重置中 by mrmsl on 2012-12-04 13:10:34
            return;
        }

        if (rePosition) {//重新定位，因为功能树最小化，调整窗口大小，会恢复原来位置 by mrmsl on 2012-12-04 12:36:32
            var treePanel = Yab.cmp.tree;
            var position = this.getPosition();
            this.setPosition(treePanel.isHidden() ? 0 : treePanel.getWidth(), position[1]);
        }

        animate = animate ||
        {
            animate: true
        };

        Ext.Array.each(Ext.query('.apptab', Ext.get('appTabs').dom), function(a, i, me) {
            var el = Ext.get(a);

            if (me.length - 1 == i) {//已经最后一个
                this.resizing = false;
            }

            if (!el) {
                return;
            }

            if (!el.dom.removed && !el.hasCls('overview')) {
                if (animate.animate) {
                    el.animate({
                        to: {
                            width: this.tabWidth()
                        }
                    });
                }
            }
        }, this);
    },//end resizeTabs

     /**
     * 活跃指定标签
     *
     * @private
     *
     * @param {String} controller 标签controller
     *
     * @return {void} 无返回值
     */
    setActiveTab: function(controller) {
        this.activeTab = controller;

        Ext.Array.each(Ext.query('.apptab a[class=tabUrl]', Ext.get('appTabs').dom), function(a) {
            Ext.get(a).up('.apptab').removeCls(['active', 'highlight'])
        });

        var e = Ext.get(controller);

        if (this.tabCache[controller]) {
            var cache = this.tabCache[controller];
            e.update(cache.text);
            e.dom.href = '#' + Ext.Object.toQueryString(cache);//;Ext.String.format('#controller={0}&action={1}{2}', cache.controller, cache.action, cache.data ? '&data=' + cache.data: '');
            e.dom.title = cache.text;//更新title属性 by mrmsl on 2012-08-20 12:29:41

        }

        e.up('.apptab').addCls('active');
    },

    /**
     * 设置最近操作
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-09 12:50:18
     * @lastmodify      2013-01-14 16:35:35 by mrmsl
     *
     * @return {void} 无返回值
     */
    setRecentTabs: function(data) {
        Ext.each(this.recentTabs, function(item, index) {
            //干掉标签栏显示的
            if (item.controller == data.controller && item.action == data.action) {
                Ext.Array.erase(this, index, 1);
                return false;
            }
        }, this.recentTabs);
    },

    /**
     * 获取标签栏宽度
     *
     * @private
     *
     * @return {Number} 标签栏宽度
     */
    tabBarWidth: function() {
        return this.getWidth();
    },

    /**
     * 获取标签宽度
     *
     * @private
     *
     * @return {Number} 标签宽度
     */
    tabWidth: function() {
        var b = Math.floor(this.tabBarWidth() / (this.tabsInBar.length + this.staticTabs.length));// + 6;

        if (b > this.maxTabWidth) {
            return this.maxTabWidth;
        }
        else {

            if (b < this.minTabWidth) {
                return this.minTabWidth;
            }
            else {
                return b;
            }
        }
    }
});