/**
 * 表单扩展
 *
 * @file            app/ux/Yab.ux.Form.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-12-11 13:42:34
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.ux.Form', {
    extend: 'Ext.form.Panel',
    alias: ['widget.appform'],
    /**
     * @cfg {Boolean}
     * 设置自动滚动条
     */
    autoScroll: true,
    /**
     * @cfg {Object} (required)
     * 所属控制器
     */
    controller: null,
    /**
     * @cfg {Object} (required)
     * 标签数据
     */
    tabData: null,
    /**
     * @cfg {Object} [backControllerName=所属控制器名称]
     * 提交后返回控制器名称
     */
    backControllerName: null,
    /**
     * @cfg {String}
     * 提交后返回操作方法名称
     */
    backActionName: 'list',//提交后返回操作方法名称
    /**
     * @cfg {String} [watiMsg=PROCESSING语言项]
     * 提交中提示
     */
    waitMsg: lang('PROCESSING'),
    /**
     * @cfg {Number}
     * padding
     */
    bodyPadding: 5,
    /**
     * @cfg {String}
     * 默认文本框类型
     */
    defaultType: 'textfield',

    /**
     * ctrl+enter 提交表单
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-12-11 15:28:53
     * @lastmodify      2013-01-12 20:40:50 by mrmsl
     *
     * @private
     *
     * @return {void} 无返回值
     */
    bindSubmitForm: function() {
        var me = this,
        el = this.el,
        btn = this.down('component[name=submit]');

        Ext.each(['input', 'textarea'], function(item) {
            el.on({
                keydown: function(e) {
                    e.ctrlKey && e.getKey() == e.ENTER && btn && !btn.isDisabled() && me.onSubmit(btn);
                },
                delegate: item
            })
        });
    },

    errorReader: {//errorReader 自定义处理返回结果 by mrmsl on 2012-10-30 21:51:11
        read: function(response) {log('Ext.form.Base.errorReader');
            var response = response.responseText, result = Ext.decode(response, true), success = false;

            if (result) {
                return result;
            }

            if (-1 != response.indexOf('"success":true')) {
                success = true;
            }

            return {
                success: false,
                response: response
            };
        }
    },

    reader: {//reader 自定义处理返回结果 by mrmsl on 2012-10-30 22:25:50
        read: function(response) {
            var result = Ext.decode(response.responseText, true);
            return result ? {records: [{data: result.data}], success: true} : {};
        }
    },

    /**
     * 初始化组件
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-12-11 14:06:53
     * @lastmodify      2013-01-12 20:41:00 mrmsl
     *
     * @private
     *
     * @return {void} 无返回值
     */
    initComponent: function() {
        var controller = this.controller, data = this.tabData;
        this.url = this.url || controller.getActionUrl(data.controller, 'add');

        if (data) {
            this.id = data.controller + data.action;
        }

        this.items = 'null' === this.items ? null : this.items  || controller.formField(data);
        this.addEvents('beforesubmit', 'submitsuccess', 'submitfailure');
        this.callParent();
        this.on({
            render: this.bindSubmitForm,
            scope: this
        });
    },

    /**
     * 提交失败
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-12-15 12:38:10
     * @lastmodify      2013-01-12 20:41:09 by mrmsl
     *
     * @private
     *
     * @param {Object} form   Ext.form.Panel
     * @param {Object} action Ext.form.Action
     *
     * @return {void} 无返回值
     */
    onFailure: function(form, action) {

        if (action.result && !action.result.msg && action.response && action.response.responseText) {
            var result = Ext.decode(action.response.responseText, true);

            if (result && result.msg) {
                action.result.msg = result.msg;
            }
        }

        if (false == this.fireEvent('submitfailure', form, action)) {
            return true;
        }

        commonFailure(action);
    },

    /**
     * 提交表单
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-12-11 14:12:12
     * @lastmodify      2013-01-12 20:42:05 by mrmsl
     *
     * @private
     *
     * @param {Object} btn 提交按钮
     *
     * @return {void} 无返回值
     */
    onSubmit: function(btn) {
        var me = this, form = this.getForm();
        btn = btn || this.down('component[name=submit]');

        if (form.isValid()) {

            if (false !== this.fireEvent('beforesubmit', this)) {

                form.submit({
                    url: this.url,
                    btn: btn,
                    waitMsg: this.waitMsg,
                    success: this.onSuccess,
                    failure: this.onFailure,
                    scope: this
                });
            }
        }
    },

    /**
     * 提交成功
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-12-15 12:36:49
     * @lastmodify      2012-12-15 12:36:49 by mrmsl
     *
     * @private
     *
     * @return {void} 无返回值
     */
    onSuccess: function(form, action) {
        var me = this;

        if (false === this.fireEvent('submitsuccess', form, action)) {
            return true;
        }

        Alert(action.result.msg);

        var _editRecord = this.controller.getEditRecord();

        if (_editRecord) {//编辑record，更新 by mrmsl on 2012-08-11 17:00:34
            form.updateRecord(_editRecord);
            this.controller.setEditRecord(null);
        }

        var back = _GET('back');
        Yab.History.push(back ? decodeURIComponent(back) : this.controller.getAction(this.backControllerName, this.backActionName));

        //销毁表单，像菜单管理，地区管理，如果不销毁表单，所属上级不重新加载，将不包含新增记录 by mrmsl on 2012-08-16 10:52:10
        if (global('app_destroy_formpanel') && this.controller._formpanel) {
            this.controller._formpanel.destroy();
            this.controller._formpanel = null;
            global('app_destroy_formpanel', false);
        }
    }
});