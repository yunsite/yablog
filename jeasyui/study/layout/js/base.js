define('base', ['router'], function(require, exports, module) {
    var BASE = Base.extend({
        /**
         * var {object} _pageTitle 网站标题缓存
         */
        _pageTitle: {},

        /**
         * var {object} _self 本类实例引用
         */
        _self: null,

        /**
         * datagrid列表
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-09-05 14:37:16
         *
         * return {void} 无返回值
         */
        _datagrid: function(defaults, callback) {
            var tabs        = require('tabs'),
                selectedTab = tabs.getSelected(),
                grid        = tabs.get('_el').find('#grid-' + C + A);

            TREE_DATA._prevQueryParams = _.clone(TREE_DATA.queryParams);

            var page        = intval(Q2O.page || 1),
                pagesize    = {
                pageNumber: page,
                pageSize: Q2O.page_size || 20
            };

            $.extend(TREE_DATA.queryParams, defaults);

            if (global('FIRST_LOAD')) {
                $.extend(this._datagridOptions, pagesize);
                $.extend(this._datagridOptions.queryParams, TREE_DATA.queryParams);
                grid.data('data-options', this._datagridOptions);
                this._setToolbar(selectedTab);
                grid.datagrid();

                grid.datagrid('getPager').pagination({
                    onSelectPage: function(page, pageSize) {
                        $.extend(grid.datagrid('options'), {
                            pageNumber: page
                        });
                        $.extend(grid.datagrid('getPager').pagination('options'), {
                            pageNumber: page
                        });

                        $.extend(TREE_DATA.queryParams, {
                            page: page
                        });

                        require('router').navigate(object2querystring(TREE_DATA.queryParams));
                        grid.datagrid('reload');
                    },
                    showPageList: false
                });
            }
            else if(object2querystring(TREE_DATA._prevQueryParams) != object2querystring(TREE_DATA.queryParams)) {
                log('prev', TREE_DATA._prevQueryParams, TREE_DATA.queryParams);
                $.extend(grid.datagrid('options'), pagesize);
                $.extend(grid.datagrid('options').queryParams, TREE_DATA.queryParams);
                $.extend(grid.datagrid('getPager').pagination('options'), pagesize);
                grid.datagrid('getPager').pagination('select', pagesize.pageNumber);
            }

            callback && callback();
        },//end _datagrid

        /**
         * 设置页面标题，参数大于2个将手动设置标题
         *
         * @author      mrmsl <msl-138@163.com>
         * @date        2013-08-01 15:37:08
         *
         * @param {string} [controller=C] 控制器
         * @param {string} [action==A] 操作方法
         *
         * @return {void} 无返回值
         */
        _setPageTitle: function(controller, action) {
            controller  = controller || C || _GET('controller');
            action      = action || A || _GET('action');

            if (arguments[2]) {//手动设置标题
                document.title = arguments[2];
                //添加 => 编辑
                this._pageTitle[controller + action] = this._pageTitle[controller + action].replace("lang('ADD')", "lang('EDIT')");
            }
            else {

                if (!this._pageTitle[controller + action]) {
                    var tree        = require('tree'),
                        treeData    = tree.get('_treeData'),
                        node        = tree.get('_el').tree('findByControllerAction', [controller, action]),
                        title       = [];

                    if (node) {
                        $.each(node.node.split(','), function(index, item) {
                            title.push(treeData[item].text);
                        });
                    }

                    title = title.reverse().join(' - ');
                    title = strip_tags(title);
                    this._pageTitle[controller + action] = title;
                }

                this._origTitle = this._origTitle ? this._origTitle : document.title;
                //编辑 => 添加
                document.title = this._pageTitle[controller + action] ? (this._pageTitle[controller + action].replace("lang('EDIT')", "lang('ADD')") + ' - ' + this._origTitle) : this._origTitle;
            }

            var title = document.title.split(' - ');
            title.pop();
            title = title.reverse().join(' &raquo; ');

            log(require('tabs').getSelected().find('.panel-title').html(title));
        },//end _setPageTitle

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