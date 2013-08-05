define('base', ['router'], function(require, exports, module) {
    var BASE = Base.extend({
        /**
         * var {object} _self 本类实例引用
         */
        _self: null,

        /**
         * 构造函数
         *
         * @author      mrmsl <msl-138@163.com>
         * @date        2013-08-01 15:37:08
         *
         * return {void} 无返回值
         */
        constructor: function() {
            this._self = this;
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