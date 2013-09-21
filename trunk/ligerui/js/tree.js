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
         * @var {object} _ligerTree ligerTree对象
         *
         */
        _ligerTree: null,

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

            if (data.children) {
            }
            else {
                require('router').navigate(object2querystring(data.queryParams), true);
            }
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
                onSuccess: function(data) {
                    $.each(data, function(index, item) {
                        var controller  = item.controller,
                            action      = item.action;

                        item.queryParams = {
                            controller: controller,
                            action: action
                        };
                        me._treeData[item['menu_id']] = item;
                        me._treeData[controller + action] = item['menu_id'];
                    });

                    require('router').notifyTreeLoaded();
                },
                onClick: function (node) {
                    global('clickTree', true);
                    me._onClick(node.data, node.target);
                    global('clickTree', false);
                },
                onAfterAppend: function (node) {
                }
            });

            this._ligerTree = this._el.ligerGetTreeManager();
        },

        /**
         * 获取树节点数据
         *
         * @author      mrmsl <msl-138@163.com>
         * @date        2013-09-21 15:33:49
         *
         * @param {mixed} index 索引
         *
         * return {object} index为空,返回整个_treeData;index为数字或controller+arguments[1],返回指定索引数据
         */
        getData: function(index) {

            if (!index) {//整个_treeData
                return this._treeData;
            }
            else if (intval(index)) {//id
                return this._treeData[index];

            }
            else if (2 == arguments.length) {
                index += arguments[1];
            }

            return this._treeData[this._treeData[index]];
        }
    });

    module.exports = new Tree();
});