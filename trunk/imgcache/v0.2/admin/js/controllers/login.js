/**
 * 管理员登录
 *
 * @file            login.js
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-10-04 13:42:58
 * @lastmodify      $Date$ $Author$
 */

define('login', ['core/base'], function(require, exports, module) {
    var Base    = require('core/base');
    var Login   = Base.extend({
        /**
         * @cfg {string} _imgCodeId 验证码id
         */
        _imgCodeId: 'img-code-login',

        /**
         * @cfg {string} verifycodeModule 验证码模块
         */
        _verifycodeModule: 'module_admin',

        /**
         * 获取登录表单域
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-10-04 14:47:17
         *
         * @return {array} 表单域
         */
        _formFields: function () {

            return [{
                display: lang('USERNAME'), name: 'username'
            }, {
                display: lang('PASSWORD'), name: 'password'
            }];
        },

        /**
         * 创建ligerForm登录表单
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-10-04 14:46:33
         *
         * @return {void} 无返回值
         */
        _ligerForm: function() {
            this._win.dialog.content.ligerForm({
                //inputWidth: 170, labelWidth: 90, space: 40,
                fields: this._formFields()
            });
        },

        /**
         * 登陆框弹出窗口
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-10-04 14:01:42
         *
         * @return {void} 无返回值
         */
        win: function() {

            if (!this._win) {//未定义

                this._win = $.ligerDialog.open({
                    title: lang('CONTROLLER_NAME_ADMIN,LOGIN'),
                    width: 350,
                    height: 200
                });

                this._ligerForm();

            }
            else {
                this._win.show()
            }
        }
    });

    module.exports = new Login();
});