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
         * var {object} _routerRegexp 导航正则
         */
        _routerRegexp: /controller=(\w+)&action=(\w+)/,

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
            this.route(this._routerRegexp, 'router');
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
        router: function(controller, action) {
            var hash = getHash();

            if (null === controller) {

                if ('' === hash) {
                    return;
                }

                var match = hash.match(this._routerRegexp);

                if (!match) {
                    return;
                }

                controller = match[1];
                action = match[2];
            }

            var tree    = require('tree'),
                el      = tree.get('_el'),
                node    = el.tree('findByControllerAction', {controller: controller, action: action});

            if (node) {
                TREE_DATA   = node;
                C = controller;
                A = action;
                node = el.tree('find', node.menu_id);
                el.tree('select', node.target);
                el.tree('expandTo', node.target);

                if (!global('clickMenu')) {
                    global('clickMenu', false);
                    /*$('#tree-panel').animate({
                        scrollTop: $(node.target).offset().top - 30
                    }, 500);*/
                }

                /*$.extend(TREE_DATA.queryParams, querystring2object(hash));
                hash = object2querystring(TREE_DATA.queryParams);
                hash = '' + menu_id + (hash ? '&' + hash : '');

                this.navigate(hash);*/

                require('tabs').addTab(node.id);
            }
            /*else {
                require('tabs').get('_el').tabs('select', 0);
                tree.get('_el').tree('select', '__none__');
            }*/
        }//end router
    });

    Backbone.history.start();

    module.exports = new Router();
});