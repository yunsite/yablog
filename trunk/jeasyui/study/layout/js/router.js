/**
 * 路由
 *
 * @file            router.js
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-08-02 20:40:47
 * @lastmodify      $Date$ $Author$
 */

define('router', [], function(require, exports, module) {
    var Router = Backbone.Router.extend({
        /**
         * var {bool} [treeLoaded=false] true导航树已经加载完成
         */
        treeLoaded: false,

        /**
         * 初始化
         *
         * @author      mrmsl <msl-138@163.com>
         * @date        2013-08-03 15:30:45
         *
         * return {void} 无返回值
         */
        initialize: function(options) {
            this.route(/^(\d+)(&.*)?|^$/, 'router');
        },

        /**
         * 导航树已经加载完成
         *
         * @author      mrmsl <msl-138@163.com>
         * @date        2013-08-03 15:43:06
         *
         * return {void} 无返回值
         */
        notifyTreeLoaded: function() {

            if (!this.treeLoaded) {
                this.treeLoaded = true;
                this.router(null);
            }
        },

        /**
         * 路由
         *
         * @author      mrmsl <msl-138@163.com>
         * @date        2013-08-03 15:31:15
         *
         * @param {int} menu_id 菜单id
         *
         * return {void} 无返回值
         */
        router: function(menu_id, navigate) {
            var hash = getHash();

            if (null === menu_id) {

                if ('' === hash) {
                    return;
                }

                var match = hash.match(/^(\d+)/);

                if (!match) {
                    return;
                }

                menu_id = match[1];
            }

            MENU_ID = menu_id;

            var tree        = require('tree');

            if (menu_id && 0 != menu_id.indexOf(0)) {
                var el          = tree.get('_el'),
                    selected    = el.tree('getSelected'),
                    node        = el.tree('find', menu_id);

                if (node) {
                    TREE_DATA   = tree.get('_treeData')[menu_id];
                    C = TREE_DATA.controller;
                    A = TREE_DATA.action;
                    el.tree('select', node.target);
                    el.tree('expandTo', node.target);

                    if (!global('clickMenu')) {
                        global('clickMenu', false);
                        $('#tree-panel').animate({
                            scrollTop: $(node.target).offset().top - 30
                        }, 500);
                    }

                    /*$.extend(TREE_DATA.queryParams, querystring2object(hash));
                    hash = object2querystring(TREE_DATA.queryParams);
                    hash = '' + menu_id + (hash ? '&' + hash : '');

                    this.navigate(hash);*/

                    require('tabs').addTab(menu_id);
                }
                else {
                    tree.tree('getSelected', '__none__');
                }
            }
            else {
                require('tabs').get('_el').tabs('select', 0);
                tree.get('_el').tree('select', '__none__');
            }
        }//end router
    });

    Backbone.history.start();

    module.exports = new Router();
});