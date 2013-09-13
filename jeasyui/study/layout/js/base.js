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
         * var {object} _self 本类实例引用
         */
        _self: null,

        /**
         * var {object} [_datagridOptions=null] _datagrid options
         */
        _datagridOptions: null,

        /**
         * datagrid列表
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-09-05 14:37:16
         *
         * @param {object} defaults 默认datagrid options
         * @param {mixed} callback 回调,callback=true将设置toolbar搜索值
         *
         * return {void} 无返回值
         */
        _datagrid: function(defaults, callback) {
            var tabs        = require('tabs'),
                selectedTab = tabs.getSelected(),
                toolbar     = selectedTab.find('#tb-' + ID),
                grid        = tabs.get('_el').find('#grid-' + ID);

            TREE_DATA._prevQueryParams = _.clone(TREE_DATA.queryParams);

            var page        = intval(Q2O.page || 1),
                pagesize    = {
                pageNumber: page,
                pageSize: Q2O.page_size || 20
            };

            $.extend(TREE_DATA.queryParams, defaults);

            if (global('FIRST_LOAD')) {
                $.extend(this._datagridOptions, pagesize, {sortName: defaults.sort, sortOrder: defaults.order});
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
                $.extend(grid.datagrid('options'), pagesize, {sortName: defaults.sort, sortOrder: defaults.order});
                $.extend(grid.datagrid('options').queryParams, TREE_DATA.queryParams);
                $.extend(grid.datagrid('getPager').pagination('options'), pagesize);
                grid.datagrid('getPager').pagination('select', pagesize.pageNumber);

                grid.data('datagrid').dc.header2
                .find('tr.datagrid-header-row')
                    .find('div.datagrid-cell')
                    .removeClass('datagrid-sort-asc datagrid-sort-desc')
                .end()
                .find('td[field=' + defaults.sort + ']')
                    .find('div.datagrid-cell')
                    .addClass('datagrid-sort-' + defaults.order);
            }
            else if (global('contextmenu_refresh')) {
                grid.datagrid('reload');
            }

            if (callback && !global('contextmenu_refresh')) {

                if (true === callback) {

                    $.each(toolbar.children('input[data-jeasyui]'), function(index, item) {//搜索框y值
                        var me      = $(this),
                            name    = me.attr('data-name'),
                            type    = me.attr('data-jeasyui');

                        if (me.attr('data-multiple')) {
                            me[type]('setValues', defaults[name].split(','));
                        }
                        else {
                            me[type]('setValue', defaults[name]);
                        }
                    });
                }
                else {
                    callback();
                }
            }
        },//end _datagrid

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

            require('tabs').getSelected().find('.panel-title').html(title);

            return this;
        }//end setPageTitle
    });

    module.exports = BASE;
});