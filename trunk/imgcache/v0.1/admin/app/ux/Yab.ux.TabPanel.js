/**
 *  tabPanel扩展
 *
 * @file            app/ux/Yab.ux.TabPanel.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-01-03 17:12:30
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.ux.TabPanel', {
    extend: 'Ext.tab.Panel',
    alias: ['widget.apptabpanel'],
    /**
     * @cfg {Number}
     * 标签最小宽度
     */
    minTabWidth: 80,
    /**
     * @cfg {Number}
     * padding
     */
    bodyPadding: 5,
    /**
     * @cfg {Object} (required)
     * 所属控制器
     */
    controller: null,//所属控制器
    /**
     * @cfg {Object} (required)
     * 标签数据
     */
    tabData: null,//标签数据

    /**
     * 标签栏配置
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-01-03 17:29:23
     * @lastmodify      2013-01-12 20:55:26 by mrmsl
     *
     * @private
     *
     * @return {Array} 标签栏配置
     */
   tabItems: function() {
        var controller = this.controller.getControllerName();
        var data = global(controller + '_TAB_ARR') || eval(lang(controller + '_TAB_ARR'));
        var items = [];

        Ext.each(data, function(item) {
            items.push({
                title: item[1],
                url: this.getActionUrl('field', 'publicSaveValue'),
                itemId: item[0]
            });
        }, this.controller);

        return items;
    },

    /**
     * 初始化组件
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-01-03 17:25:10
     * @lastmodify      2013-01-12 20:55:56 mrmsl
     *
     * @private
     *
     * @return {void} 无返回值
     */
    initComponent: function() {
        this.defaults = {
            xtype: 'appform',
            controller: this.controller,
            items: 'null',
            listeners: {
                submitsuccess: function (form, action) {
                    Alert(action.result.msg);
                    return false;
                }
            },
            defaults : {
                labelWidth: global('app_labelWidth') || 180
            }
        };
        this.items = this.tabItems(),
        this.callParent();
        this.on('tabchange', function(tabPanel, newTab, oldTab) {
            Yab.History.push(this.controller.getAction(false, newTab.itemId));
        });
    },

    /**
     * 加载表单域
     *
     * @author       mrmsl <msl-138@163.com>
     * @date         2012-08-27 13:41:19
     * @lastmodify   2013-02-07 15:01:23 by mrmsl
     *
     * @return {void} 无返回值
     */
    loadFormField: function(callback) {
        var controller = this.controller;
        controllerName = controller.getControllerName(),
        action = controller.getActionName(),
        viewport = Yab.cmp.viewport;

        setLoading(lang('LOADING'));//加载中提示

        var url = controller.getActionUrl('field', 'publicForm', controller.getAction(controllerName, action));

        Ext.data.JsonP.request({
            url: url,
            timeout: 3000,
            disableCaching: false,//不禁用缓存 by mrmsl on 2012-08-30 12:20:15
            callbackName: 'Yab_form_{0}_{1}'.format(controllerName, action),
            success: function(items) {

                if (Ext.isFunction(callback)) {
                    return callback(items);
                }
                else if (Ext.isObject(callback)) {
                    callback.add(items);
                }
            },
            callback: function() {
                setLoading(false);
            },
            failure: function(err) {
                error(Ext.String.format('{0}<span class="font-red">{1}</span>{2}<br />{3}：<span class="font-red">{4}</span>', lang('LOAD'), lang('MODULE_NAME_FIELD'), lang('FAILURE'), lang('ERROR,INFO'), url, err));
            }
        });
    }//end loadFormField
});