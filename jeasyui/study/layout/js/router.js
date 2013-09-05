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
        _routerRegexp: /^$|controller=(\w+)&action=(\w+)/,

        /**
         * var {bool} [treeLoaded=false] true导航树已经加载完成
         */
        treeLoaded: false,

        /**
         * 首页
         *
         * @author      mrmsl <msl-138@163.com>
         * @date        2013-09-04 17:24:02
         *
         * return {void} 无返回值
         */
        index: function() {log('index');
            require('tabs').get('_el').tabs('select', 0);
            require('tree').get('_el').tree('select', '__none__');
            this.navigate('');
        },

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
                    this.index();
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
                node    = el.tree('findByControllerAction', [controller, action]);

            if (node) {log('here');
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

                require('tabs').addTab(node.id);
            }
            else {
                this.index()
            }
        }//end router
    });

    Backbone.history.start();

    module.exports = new Router();
});