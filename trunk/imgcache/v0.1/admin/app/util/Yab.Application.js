/**
 * 应用程序
 *
 * @file            app/util/Yab.Application.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-03-17 10:14:12
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.Application', {
    extend: 'Ext.app.Application',
    /**
     * @cfg {Array}
     * 控制器
     */
    controllers: ['Yab.controller.Tree', 'Yab.controller.Tabs', 'Yab.controller.Index'],
    /**
     * @cfg {String}
     * 应用名称
     */
    name: 'App',

    /**
     * 启动函数
     *
     * @private
     *
     * @return {void} 无返回值
     */
    launch: function() {
        Yab.App = this;
        var viewport = Ext.create('Yab.view.Viewport');//创建视图

        Yab.cmp = {//组件信息
            tabs: Ext.getCmp('appTabs'),
            tree: Ext.getCmp('appTree'),
            card: Ext.getCmp('appCard'),
            viewport: Ext.getCmp('appViewport')
        };

        //监听窗口大小，重置标签页宽度 by mrmsl on 2012-07-31 22:12:29
        viewport.on('resize', Ext.bind(Yab.cmp.tabs.resizeTabs, Yab.cmp.tabs, [null, true]));
        Yab.History.init();//初始化浏览器导航

        this.logLoadTime();//记录加载css,js时间 by mrmsl on 2012-09-06 17:59:28
    },

    /**
     * 记录加载css,js时间
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-09-06 17:58:40
     * @lastmodify      2013-01-12 21:55:33 by mrmsl
     *
     * @return {void} 无返回值
     */
    logLoadTime: function() {
        Ext.Ajax.request({
            url: getActionUrl('login/logLoadTime'),
            params: {
                load_css_time: (LOAD_CSS_TIME - START_TIME) / 1000,
                load_ext_time: (LOAD_EXT_TIME - LOAD_CSS_TIME) / 1000,
                load_js_time: (LOAD_JS_TIME - LOAD_EXT_TIME) / 1000,
                app_launch_time: (new Date().getTime() - LOAD_JS_TIME) / 1000
            }
        });
    }
});