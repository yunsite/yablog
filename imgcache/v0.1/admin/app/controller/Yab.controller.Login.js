/**
 * 管理员登陆控制器
 *
 * @file            app/controller/Yab.controller.Login.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-07-05 17:52:31
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.controller.Login', {
    extend: 'Yab.controller.Base',
    /**
     * @cfg {String}
     * 验证码id
     */
    imgCodeId: 'img-code-login',//验证码id by mrmsl on 2012-07-14 12:31:32
    /**
     * @cfg {String}
     * 验证码模块
     */
    verifycodeModule: 'module_admin',//验证码模块 by mrmsl on 2012-09-28 17:33:20

    /**
     * @inheritdoc Yab.controller.Admin#formField
     */
    formField: function () {
        var extField = Yab.Field.field();
        global('app_labelWidth', 60);//提交按钮labelWidth by mrmsl on 2012-08-28 12:50:33
        global('app_btnText', lang('LOGIN'));//提交按钮文字 by mrmsl on 2012-08-31 08:57:28

        return [
            //用户名
            extField.textField('username', 'PLEASE_ENTER,USERNAME', 'USERNAME', undefined, {fieldCls: 'x-form-field txt-username'}),
            //密码
            extField.passwordField('password', 'PLEASE_ENTER,PASSWORD', 'PASSWORD', {fieldCls: 'x-form-field txt-password'}),
            //验证码
            extField.verifyCode(this.imgCodeId, this.verifycodeModule),
            this.btnSubmit(),//通用提交按钮 by mrmsl on 2012-08-28 10:04:22
            {//提示信息 by mrmsl on 2012-07-12 13:56:18
                xtype: 'component',
                hidden: true,
                componentCls: 'msg',
                style: 'color: red'
            },
            extField.hiddenField('load_css_time', {value: (LOAD_CSS_TIME - START_TIME) / 1000}),//加载css时间
            extField.hiddenField('load_ext_time', {value: (LOAD_EXT_TIME - LOAD_CSS_TIME) / 1000}),//加载extjs时间
            extField.hiddenField('load_js_time', {value: (LOAD_JS_TIME - LOAD_EXT_TIME) / 1000})//加载其它js时间
        ];
    },

    /**
     * @inheritdoc Yab.controller.Base#formPanel
     */
    formPanel: function() {
        var me = this;

        return Ext.create('Yab.ux.Form', {
            controller: this,
            url: this.getActionUrl(false, 'login'),
            plain: true,
            height: 150,
            fieldDefaults: {
                labelAlign: 'right',
                labelSeparator: '：',
                allowBlank: false,
                labelPad: 0,
            },
            listeners: {
                submitfailure: function (form, action) {
                    this.down('component[componentCls=msg]').show().update(action.result.msg || lang('LOGIN,FAILURE'));
                    return false;
                },
                submitsuccess: function (form, action) {
                    this.down('component[componentCls=msg]').hide();
                    this.down('component[name=submit]').setText(lang('LOGIN'));

                    var el = Ext.get(me.imgCodeId);

                    if (el) {
                        el.hide();
                    }

                    form.reset();

                    if ('undefined' == typeof LOGIN) {//正进行操作，登陆超时
                       me._win.hide();
                    }
                    else {//登陆页
                        location.replace(System.sys_base_admin_entry);
                        Alert(lang('LOGIN_SUCCES_TIP'), undefined, false, false);
                    }

                    return false;
                }
            }
        });
    },//end formPanel

    /**
     * 登陆框弹出窗口
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-07-05 17:53:33
     * @lastmodify      2013-01-14 13:53:51 by mrmsl
     *
     * @return {void} 无返回值
     */
    win: function() {

       if (!this._win) {//未定义

           this._win = Ext.create('Ext.window.Window', {
               title: lang('CONTROLLER_NAME_ADMIN,LOGIN'),
               border: false,
               modal: true,
               constrain: true,
               closable: false,
               closeAction: 'hide',
               width: 350,
               items: this.formPanel()
           });
       }

       this._win.show();
    }
});