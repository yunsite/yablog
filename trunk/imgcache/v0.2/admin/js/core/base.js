/**
 * 底层库
 *
 * @file            base.js
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-09-21 11:41:50
 * @lastmodify      $Date$ $Author$
 */

define('core/base', ['core/router'], function(require, exports, module) {
    var BASE = Base.extend({
        /**
         * 各种ligerTextBox表单域
         *
         * @author              mrmsl <msl-138@163.com>
         * @date                2013-10-09 08:34:01
         *
         * @return {object} 各种ligerTextBox
         */
        _ligers: function() {
            var me = this;

            return {
                /**
                 * 验证码输入框
                 *
                 * @author          mrmsl <msl-138@163.com>
                 * @date            2013-10-09 08:34:54
                 *
                 * @param {string} [imgCodeId=this._imgCodeId] 验证码图片id
                 * @param {string} [module=this._verifycodeModule] 验证码所属模块
                 *
                 * @return {object} 验证码输入框ligerTextBox配置
                 */
                verifyCode: function(imgCodeId, module) {
                    imgCodeId = imgCodeId || me._imgCodeId;
                    module = module || me._verifycodeModule;
                    var enable = System[module + '_verifycode_enable'];//是否开启验证码

                    if (!enable) {//未开启验证码

                        return {
                            type: 'hidden',
                            name: VERIFY_CODE_KEY
                        };
                    }

                    return {
                        display: lang('VERIFY_CODE'),
                        name: VERIFY_CODE_KEY,
                        width: 50,
                        tip: '<img id="' + imgCodeId + '" data-module="' + module + '" data-src="" style="display: none; valign: absmiddle; cursor: pointer;" title="' + lang('REFRESH_CODE_TIP') + '" onclick="refreshVerifyCode(this)" />',
                        validate: {
                            required: true
                        },
                        options: {
                            onFocus: function() {
                                this._img = this._img || this.inputText.parent().parent().next().find('img#' + imgCodeId);

                                if (!this._img.is(':visible')) {
                                    refreshVerifyCode(this._img);
                                    this._img.show();
                                }
                            }
                        }
                    };
                }//end verifyCode
            };
        },
        /**
         * 获取请求url
         *
         * @author      mrmsl <msl-138@163.com>
         * @date        2013-10-05 16:53:14
         *
         * @param {string} [controller=当前控制器名称] 控制器
         * @param {string} [action=当前操作方法名称] 操作
         * @param {string} [queryString] 查询字符串
         *
         * @return {string} 请求url
         */
        _getActionUrl: function(controller, action, queryString) {
            controller = controller || this._getControllerName();
            action = action || getCA('a');
            var url = controller + '/' + action + (queryString ? '?' + queryString : '');

            return getActionUrl(url);
        },

        /**
         * 获取控制器名称
         *
         * @author      mrmsl <msl-138@163.com>
         * @date        2013-08-01 15:37:53
         *
         * return {string} 控制器名称
         */
        _getControllerName: function () {
            return this._controllerName || getCA();
        },

        /**
         * 渲染并高亮搜索关键字
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-09-13 17:33:21
         *
         * @param {string} v        值
         * @param {string} [column] 字段
         * @param {bool}   [stripTags] strip_tags
         *
         * @return {string} 高亮搜索关键字的字段值
         */
        _renderKeywordColumn: function(v, column, stripTags) {
            var data = q2o(getHash());

            if (data.keyword && [data.column == column || !column]) {
                return (stripTags ? strip_tags(v) : v).replace(new RegExp('(' + data.keyword + ')', 'gi'), '<span style="color: red">$1</span>');
            }

            return v;
        },

        /**
         * 构造函数
         *
         * @author      mrmsl <msl-138@163.com>
         * @date        2013-08-01 15:37:08
         *
         * return {void} 无返回值
         */
        constructor: function() {
            this.bootstrap && this.bootstrap();
        },

        /**
         * 获取指定属性
         *
         * @author      mrmsl <msl-138@163.com>
         * @date        2013-08-01 15:40:33
         *
         * param {string} name 属性名称
         *
         * return {mixed} 属性值
         */
        get: function(name) {
            return this[name];
        },

        /**
         * 设置指定属性
         *
         * @author      mrmsl <msl-138@163.com>
         * @date        2013-08-01 15:40:33
         *
         * param {string} name 属性名称
         * param {mixed} value 属性值
         *
         * return {object} this
         */
        set: function(name, value) {
            return this[name] = value;
        }
    });

    module.exports = BASE;
});