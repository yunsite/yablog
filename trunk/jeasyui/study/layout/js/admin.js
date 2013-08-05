define('admin', function(require, exports, module) {
    var Base    = require('base');
    var Admin   = Base.extend({
        _listdatagridOptions: {
            columns: [[
                {checkbox: true},
                {title: '标题', field: 'title', width: 200},
                {title: '点击', field: 'hits', width: 50, fixed: true}
            ]],
            queryParams: {},
            toolbar: '#tb-adminlist',
            url: 'get_blogs.php',
            striped: true,
            fitColumns: true,
            pagination: true,
            fit: true,
            checkbox: true,
            checkOnSelect: false
        },

        /**
         * 列表datagrid
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-08-01 17:03:31
         *
         * return {void} 无返回值
         */
        _listdatagrid: function() {
            var tabs        = seajs.require('tabs'),
                selectedTab = tabs.getSelected(),
                dg          = tabs.get('_el').find('#dg-' + C + A);

            TREE_DATA._prevQueryParams = _.clone(TREE_DATA.queryParams);

            var pagesize = {
                pageNumber: intval(Q2O.page || 1),
                pageSize: Q2O.page_size || 20
            };

            TREE_DATA.queryParams = Q2O;

            if (!dg.length) {
                $.extend(this._listdatagridOptions, pagesize);
                $.extend(this._listdatagridOptions.queryParams, Q2O);
            }
            /*else {
                $.extend(dg.datagrid('options'), pagesize);
                $.extend(dg.datagrid('options').queryParams, Q2O);
            }*/

            if (!dg.length) {
                dg = $('<table id="dg-' + C + A + '" class="easyui-datagrid"></table>')
                .appendTo(selectedTab)
                .data('data-options', this._listdatagridOptions);
                this._setToolbar(selectedTab);
                dg.datagrid();

                dg.datagrid('getPager').pagination({
                    onSelectPage: function(page, pageSize) {
                        $.extend(dg.datagrid('options'), {
                            pageNumber: page
                        });
                        $.extend(dg.datagrid('getPager').pagination('options'), {
                            pageNumber: page
                        });

                        $.extend(TREE_DATA.queryParams, {
                            page: page
                        });
                        seajs.require('router').navigate('' + MENU_ID + '&' + object2querystring(TREE_DATA.queryParams));
                        dg.datagrid('reload');
                    },
                    onChangePageSize: log,
                    showPageList: false
                });
            }
            else if(object2querystring(TREE_DATA._prevQueryParams) != object2querystring(TREE_DATA.queryParams)) {
                $.extend(dg.datagrid('options'), pagesize);
                $.extend(dg.datagrid('options').queryParams, TREE_DATA.queryParams);
                $.extend(dg.datagrid('getPager').pagination('options'), pagesize);
                dg.datagrid('getPager').pagination('select', pagesize.pageNumber);
                //dg.datagrid('reload');log(dg.datagrid('getPager').pagination('options'))
            }

            Q2O.keyword && selectedTab.find('#tb-' + C + A).find('#admin-keyword').searchbox('setValue', Q2O.keyword);
            selectedTab.find('#tb-' + C + A).find('#admin-start_date').datetimebox('setValue', Q2O.start_date);
            selectedTab.find('#tb-' + C + A).find('#admin-end_date').datetimebox('setValue', Q2O.end_date);
        },
        /**
         * 设置活跃面板
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-08-02 20:06:50
         *
         * return {void} 无返回值
         */
        _setActivePanel: function(action) {
            var tabs        = seajs.require('tabs').getSelected();

            tabs.children().hide();

            if ('list' == action) {
                tabs.children('.datagrid').show();
            }
            else {
                var id  = 'admin' + action;
                tabs.find('#' + 'admin' + action).show();
            }
        },

        /**
         * toolbar
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-08-04 17:18:31
         *
         * return {void} 无返回值
         */
        _setToolbar: function(selectedTab) {
            var me      = this,
                html    = '<div id="tb-' + C + A + '">';
                html   += '添加时间<input id="admin-start_date" class="datetime" /> - <input id="admin-end_date" class="datetime" /> <input id="admin-keyword" />';
                html   += '</div>';

            selectedTab.append(html)
            .find('#admin-keyword')
                .data('data-options', {
                    prompt: '关键字',
                    searcher: function(keyword) {
                        $.extend(TREE_DATA.queryParams, {
                            keyword: keyword,
                            start_date: selectedTab.find('#admin-start_date').datetimebox('getValue'),
                            end_date: selectedTab.find('#admin-end_date').datetimebox('getValue')
                        });
                        var dg = selectedTab.find('#dg-' + C + A);
                        $.extend(dg.datagrid('options').queryParams, TREE_DATA.queryParams);
                        dg.datagrid('getPager').pagination('select', 1);
                    }
                }).searchbox()
            .end()
            .find('.datetime')
                .data('data-options', {
                    formatter: function(d) {
                        return date(null, d);
                    }
                })
                .datetimebox();
        },

        /**
         * 构造函数
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-08-01 15:23:08
         *
         * return {void} 无返回值
         */
        constructor: function() {
            this.base();
        },

        /**
         * 添加
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-08-01 17:01:26
         *
         * return {void} 无返回值
         */
        addAction: function() {
            this._setActivePanel('add');
            var tabs = seajs.require('tabs');
            var dg = tabs.get('_el').find('#adminadd');

            if (!dg.length) {
                $('<div id="adminadd">addAction</div>')
                .appendTo(tabs.getSelected())
            }
            else {
                //log(dg.datagrid('reload'));
            }
        },

        /**
         * 修改密码
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-08-01 17:01:56
         *
         * return {void} 无返回值
         */
        changePasswordAction: function() {
            this._setActivePanel('changePassword');
            var tabs = seajs.require('tabs');
            var dg = tabs.get('_el').find('#adminchangePassword');

            if (!dg.length) {
                $('<div id="adminchangePassword">changePassword</div>')
                .appendTo(tabs.getSelected())
            }
            else {
                //log(dg.datagrid('reload'));
            }
        },

        /**
         * 列表
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-08-01 16:50:26
         *
         * return {void} 无返回值
         */
        listAction: function() {
            this._setActivePanel('list');
            this._listdatagrid();
        }
    });

    var admin = new Admin();
    module.exports = admin;
});