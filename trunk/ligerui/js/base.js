/**
 * 底层库
 *
 * @file            base.js
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-09-21 11:41:50
 * @lastmodify      $Date$ $Author$
 */

define('base', ['router'], function(require, exports, module) {
    var BASE = Base.extend({
        /**
         * var {array} _images 是与否小图片路径
         */
        _images: [
            'http://localhost/jeasyui/themes/icons/no.png',
            'http://localhost/jeasyui/themes/icons/ok.png'
        ],
        /**
         * var {object} _pageTitle 网站标题缓存
         */
        _pageTitle: {},

        /**
         * 渲染时间
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-09-13 08:53:20
         *
         * @param {int} time 时间戳
         *
         * return {string} 时间字符串
         */
        _renderDateTime: function(time) {
            return date();
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
            var data = querystring2object(getHash());

            if (data.keyword && [data.column == column || !column]) {
                return (stripTags ? strip_tags(v) : v).replace(new RegExp('(' + data.keyword + ')', 'gi'), '<span style="color: red">$1</span>');
            }

            return v;
        },


        /**
         * 渲染是与否小图片
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-09-13 17:05:20
         *
         * @param {int}     v       值
         * @param {string} [cls]    图片class
         *
         * @return {string} 图片<img 标签
         */
        _renderYesNoImg: function(v, cls) {
            return '<img alt="" src="{0}" class="img-yesno{1}" />'.format(this._images[v], cls ? ' ' + cls : '');
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
        },

        /**
         * 设置页面标题，参数大于2个将手动设置标题
         *
         * @author      mrmsl <msl-138@163.com>
         * @date        2013-08-01 15:37:08
         *
         * @param {string} [controller=C] 控制器
         * @param {string} [action==A] 操作方法
         *
         * @return {object} this
         */
        setPageTitle: function(controller, action) {
            controller  = controller || C;
            action      = action || A;

            if (arguments[2]) {//手动设置标题
                document.title = arguments[2];
                //添加 => 编辑
                this._pageTitle[controller + action] = this._pageTitle[controller + action].replace("lang('ADD')", "lang('EDIT')");
            }
            else {

                if (!this._pageTitle[controller + action]) {

                    if ('index' != controller) {
                        var treeData    = require('tree').getData();
                            title       = [];
                        $.each(TREE_DATA.node.split(','), function(index, item) {
                            title.push(treeData[item].menu_name);
                        });

                        title = title.reverse().join(' - ');
                        title = strip_tags(title);
                        this._pageTitle[controller + action] = title;
                    }
                }

                this._origTitle = this._origTitle ? this._origTitle : document.title;
                //编辑 => 添加
                document.title = this._pageTitle[controller + action] ? (this._pageTitle[controller + action].replace("lang('EDIT')", "lang('ADD')") + ' - ' + this._origTitle) : this._origTitle;
            }

            var title = document.title.split(' - ');
            title.pop();
            title = title.reverse().join(' &raquo; ');

            //require('tabs').getSelected().find('.panel-title').html(title);

            return this;
        }//end setPageTitle
    });

    module.exports = BASE;
});