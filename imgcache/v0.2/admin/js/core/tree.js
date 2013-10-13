/**
 * 导航树
 *
 * @file            tree.js
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-09-28 17:42:10
 * @lastmodify      $Date$ $Author$
 */

define('core/tree', ['core/base'], function(require, exports, module) {
    var Base    = require('core/base');
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
                require('core/router').navigate(o2q(data.queryParams), true);
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
                url: this._getActionUrl('menu', 'publicTree'),
                textFieldName: 'menu_name',
                idFieldName: 'menu_id',
                parentIDFieldName: 'parent_id',
                btnClickToToggleOnly: false,
                attribute: ['menu_id', 'controller', 'action', 'node'],
                needCancel: false,
                single: true,
                checkbox: false,
                height: 120,
                onSuccess: function(data) {
                    $.each(data, function(index, item) {
                        var controller  = item.controller,
                            action      = item.action;

                        item.queryParams = {//查询参数
                            controller: controller,
                            action: action
                        };
                        me._treeData[item['menu_id']] = item;
                        me._treeData[controller + action] = item['menu_id'];
                    });

                    var hash = getHash();

                    if (hash) {//url中含有查询参数，覆盖默认树节点数据queryParams
                        hash = q2o(hash);
                        me._treeData[me._treeData[hash.controller + hash.action]].queryParams = hash;
                    }

                    require('core/router').notifyTreeLoaded();
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