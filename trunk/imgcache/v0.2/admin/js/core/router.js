/**
 * 路由
 *
 * @file            router.js
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-09-28 17:41:43
 * @lastmodify      $Date$ $Author$
 */

define('core/router', [], function(require, exports, module) {
    var Router = Backbone.Router.extend({
        /**
         * var {object} _routerRegexp 导航正则
         */
        _routerRegexp: /^$|controller=(\w+)&action=(\w+)/,

        /**
         * var {bool} [_treeLoaded=false] true导航树已经加载完成
         */
        _treeLoaded: false,

        /**
         * var {object} _pageTitle 网站标题缓存
         */
        _pageTitle: {},

        /**
         * 首页
         *
         * @author      mrmsl <msl-138@163.com>
         * @date        2013-09-04 17:24:02
         *
         * return {void} 无返回值
         */
        index: function() {
            var tree    = require('core/tree'),
                tab     = require('core/tabs').get('_ligerTab');

            tree.get('_el').find('div.l-body.l-selected').removeClass('l-selected');
            'index' != tab.getSelected().attr('tabid') && tab.selectTabItem('index');
            C = A = 'index';
            this.setPageTitle('index', 'index');
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

            if (!this._treeLoaded) {
                this._treeLoaded = true;
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

            if (!global('clickTree')) {
                var tree    = require('core/tree'),
                    data    = tree.getData(controller, action);

                if (!data) {
                    return $.ligerDialog.error('非法操作');
                    //return this.index();
                }

                tree.get('_ligerTree').selectNode(data.menu_id);
            }

            require('core/tabs').addTab(controller, action);
        },//end router

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

            var key = controller + action;

            if (arguments[2]) {//手动设置标题
                document.title = arguments[2];
                //添加 => 编辑
                this._pageTitle[key] = this._pageTitle[key].replace("lang('ADD')", "lang('EDIT')");
            }
            else {

                if ('index' != controller && !this._pageTitle[key]) {
                    var treeData    = require('core/tree').getData();
                        title       = [];

                    $.each(TREE_DATA.node.split(','), function(index, item) {
                        title.push(treeData[item].menu_name);
                    });

                    title = title.reverse().join(' - ');
                    title = strip_tags(title);
                    this._pageTitle[key] = title;
                }

                this._origTitle = this._origTitle ? this._origTitle : document.title;
                //编辑 => 添加
                document.title = this._pageTitle[key] ? (this._pageTitle[key].replace("lang('EDIT')", "lang('ADD')") + ' - ' + this._origTitle) : this._origTitle;
            }

            /*var title = document.title.split(' - ');
            title.pop();
            title = title.reverse().join(' &raquo; ');*/

            //require('core/tabs').getSelected().find('.panel-title').html(title);

            return this;
        }//end setPageTitle
    });

    Backbone.history.start();

    module.exports = new Router();
});