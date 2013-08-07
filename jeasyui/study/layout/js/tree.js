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
         * 继承$.fn.tree.methods
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-08-01 15:06:08
         *
         * return {void} 无返回值
         */
        _extendMethods: function() {
            var me = this;

            $.extend($.fn.tree.methods, {
            });
        },

        /**
         * 点击事件
         *
         * @author      mrmsl <msl-138@163.com>
         * @date        2013-08-01 17:06:45
         *
         * return {void} 无返回值
         */
        _onClick: function(v) {
            global('clickMenu', true);

            var target  = v.target,
                isLeaf  = this._el.tree('isLeaf', target);

            if (isLeaf) {
                var id  = v.id;
                var queryParams = object2querystring(this._treeData[id].queryParams);
                var router = require('router');
                router.navigate('' + id + (queryParams ? '&' + queryParams : ''));
                router.router(id);
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

            this._extendMethods();

            $('#tree-panel').append('<ul id="tree"></ul>');
            this._el = $('#tree');
            this._el.addClass('easyui-tree')
            .data('data-options', {
                url: '../get_tree.php',
                lines: false,
                onClick: function(v) {
                    me._onClick(v);
                },
                formatter: function(node) {
                    node.queryParams = {};
                    me._treeData[node.menu_id] = node;
                    return node.menu_name;
                },
                onLoadSuccess: function() {
                    require('router').notifyTreeLoaded();
                }
            }).tree();
        },

        /**
         * 构造函数
         *
         * @author      mrmsl <msl-138@163.com>
         * @date        2013-08-01 15:23:08
         *
         * return {void} 无返回值
         */
        constructor: function() {
            this.base();
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

    var tree = new Tree();

    tree.bootstrap();

    module.exports = tree;
});