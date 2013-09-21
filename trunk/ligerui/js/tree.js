/**
 * nf
 *
 * @file            tree.js
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-09-21 11:45:37
 * @lastmodify      $Date$ $Author$
 */

define('tree', ['base'], function(require, exports, module) {
    var Base    = require('base');
    var Tree    = Base.extend({
        /**
         * var {object} [_el=null] 标签栏jquery对象
         */
        _el: null,

        /**
         * var {object} [_treeData={}] 菜单节点数据
         */
        _treeData: {},

        /**
         * 点击事件
         *
         * @author      mrmsl <msl-138@163.com>
         * @date        2013-08-01 17:06:45
         *
         * @param {object} data
         *
         * return {void} 无返回值
         */
        _onClick: function(data) {
            var data        = node.data,
                controller  = data.controller
                action      = data.action;

            /*$(node.target)
            .parents('ul.l-children:not(:visible)')
            .prev('div.l-body')
            .find('.l-expandable-close')
            .click();*/

            if ('#' == action) {
            }
            else {
                addTab(controller + action, data.menu_name, data.url);
            }
            global('clickMenu', true);

            var target  = v.target,
                isLeaf  = this._el.tree('isLeaf', target);

            if (isLeaf) {
                var id          = v.id,
                    data        = this._treeData[id],
                    router      = require('router');

                router.navigate(object2querystring(data.queryParams), true);
                //router.router(data.controller, data.action);
            }
            else {
                this._el.tree('toggle', target);
            }

            global('clickMenu', false);
        },

        /**
         * 启动
         *
         * @author      mrmsl <msl-138@163.com>
         * @date        2013-08-01 15:21:41
         *
         * return {void} 无返回值
         */
        bootstrap: function() {
            var me = this;

            this._el = $('#tree');

            this._el.ligerTree({
                isExpand: false,
                url: '../get_tree.php',
                textFieldName: 'menu_name',
                idFieldName: 'menu_id',
                parentIDFieldName: 'parent_id',
                btnClickToToggleOnly: false,
                attribute: ['menu_id', 'controller', 'action'],
                needCancel: false,
                nodeWidth: 120,
                single: true,
                checkbox: false,
                height: 120,
                onClick: function (node) {
                    me._onClick(node.data, node.target);
                }
            });
        },

        /**
         *
         *
         * @author      mrmsl <msl-138@163.com>
         * @date        2013-08-01 17:07:10
         *
         * return {function} $.fn.tree
         */
        getSelected: function() {
            return this._el.tree('getSelected');
        }
    });

    module.exports = new Tree();
});