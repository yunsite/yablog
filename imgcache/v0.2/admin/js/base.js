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

define('base', ['router'], function(require, exports, module) {
    var BASE = Base.extend({

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
            var data = querystring2object(getHash());

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
         * 获取控制器名称
         *
         * @author      mrmsl <msl-138@163.com>
         * @date        2013-08-01 15:37:53
         *
         * return {string} 控制器名称
         */
        getControllerName: function () {
            return this._controllerName;
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