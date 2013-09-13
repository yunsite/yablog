define('blog', ['fields'], function(require, exports, module) {
    var Base    = require('base');
    var Blog    = Base.extend({
        /**
         * var {object} [_ueditor=null] ueditor实例,格式{instance: ueditorObj, id: ueditorId}
         */
        _ueditor: {},

        /**
         * 设置datagrid options
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-09-13 09:14:55
         *
         * return {void} 无返回值
         */
        _setDatagridOptions: function() {

            if (!this._datagridOptions) {

                this._datagridOptions = {
                    queryParams: {},//查询参数
                    columns: [[//列
                        {checkbox: true},//复选框
                        {title: 'id', field: 'blog_id', width: 50, fixed: true, sortable: true},//博客id
                        {title: '标题', field: 'title', width: 200, sortable: true},//标题
                        {title: '所属分类', field: 'cate_name', width: 100, fixed: true},//所属分类
                        {title: '添加时间', field: 'add_time', width: 140, fixed: true, formatter: this._renderDateTime, sortable: true},//添加时间
                        {title: '评论', field: 'comments', width: 50, fixed: true, sortable: true},//评论
                        {title: '点击', field: 'hits', width: 50, fixed: true, sortable: true}//点击
                    ]],
                    toolbar: '#tb-' + ID,//toolbar id
                    url: 'get_blogs.php',//url
                    sortName: 'blog_id',//默认排序字段
                    sortOrder: 'desc',//默认排序
                    onClickRow: function() {//点击行
                        log('row', arguments);
                    },
                    onClickCell: function() {//点击单元格
                        log('cell', arguments);
                    },
                    _createContextMenu: function() {//生成右键菜单

                        if (!$('#contextmenu' + ID).length) {
                            var o = $('<div id="contextmenu' + ID + '"></div>')
                            .appendTo($('body'))
                            .menu()
                            .menu('appendItems', [{
                                text: '删除',
                                name: 'delete',
                                iconCls: 'icon-remove'
                            }, {
                                text: '编辑',
                                iconCls: 'icon-edit'
                            }]);
                        }
                    },//end _createContextMenu
                    onRowContextMenu: function(e, index, data) {//右键
                        var options = $(this).datagrid('options');

                        if (!options._contextmenu) {
                            options._createContextMenu();
                            options._contextmenu = true;
                        }

                        var o = $('#contextmenu' + ID)
                        .menu('show', {
                            left: e.pageX,
                            top: e.pageY
                        });

                        $.extend(o.menu('options'), {
                            onClick: function() {
                                $.messager.confirm('系统提示', '您确定要删除 ' + data.title + '？', function() {
                                    log(arguments);
                                });
                            }
                        });

                        e.preventDefault();
                    }//end onRowContextMenu

                };//end _datagridOptions

            }//end if
        },//end _setDatagridOptions

        /**
         * toolbar
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-08-04 17:18:31
         *
         * return {void} 无返回值
         */
        _setToolbar: function(selectedTab) {
            var toolbar = selectedTab.children('#tb-' + ID);
            toolbar.children('input[data-name=keyword]')//关键字
                .data('data-options', {
                    prompt: '关键字',
                    searcher: function(keyword) {
                        var values = {};

                        $.each(toolbar.children('input[data-jeasyui]'), function(index, item) {//搜索框取值
                            var me = $(this);
                            values[me.attr('data-name')] = me[me.attr('data-jeasyui')]('getValue' + (me.attr('data-multiple') ? 's' : ''));
                        });

                        $.extend(TREE_DATA.queryParams, values);

                        var grid = selectedTab.find('#grid-' + ID);

                        $.extend(grid.datagrid('options').queryParams, TREE_DATA.queryParams);
                        grid.datagrid('getPager').pagination('select', 1);
                    }
                }).searchbox()
            .end()
            .children('input[data-jeasyui=datebox]')//时间
                .data('data-options', require('fields').datetime)
                .datebox()
            .end()
            .children('input[data-name=match_mode]')//匹配模式
                .data('data-options', require('fields').matchMode)
                .combobox()
            .end()
            .children('input[data-name=cate_id]')//所属分类
                .data('data-options', {
                    url: 'categories.php',
                    valueField: 'cate_id',
                    textField: 'cate_name',
                    onLoadSuccess: function(data) {

                        if (data && data.length) {
                            $(this).combobox('setValue', Q2O.cate_id || 0);
                        }
                    }
                })
                .combobox()
            .end()
            .children('#blog-menulist')//操作菜单
                .data('data-options', {
                    onClick: function() {
                        log(arguments);
                        //log(selectedTab.find('#grid-' + ID).datagrid('getChecked'));
                    }
                })
                //.find('div > select').data('data-options', {}).combobox()
                //.end()
            .end()
            .children('#blog-operate')//操作
                .data('data-options', {
                    menu:'#blog-menulist'
                })
                .menubutton()
            .end()
            .children('input[data-name=combotree]')
                .data('data-options', {
                    url: '../get_tree.php',
                    lines: false,
                    multiple: true
                })
                .combotree()
            .end();
        },//end _setToolbar

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
            this._setDatagridOptions();
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
            var me          = this,
                firstLoad   = global('FIRST_LOAD');

            seajs.use('ueditor', function() {
                var value,
                    ueditorId = 'ueditor-' + ID;

                if (me._ueditor[ueditorId]) {
                    value = me._ueditor[ueditorId].getContent();
                    UE.delEditor(ueditorId);
                    me._ueditor[ueditorId] = null;
                }

                me._ueditor[ueditorId] = UE.getEditor(ueditorId, {
                    onready: function() {

                        if (value) {
                            this.setContent(value);
                        }
                    }
                });


                var tabs        = require('tabs'),
                    selectedTab = tabs.getSelected(),
                    form         = tabs.get('_el').find('#' + ID);

                if (firstLoad) {
                    form.form({
                        onSubmit: function() {

                            if (!me._ueditor[ueditorId].hasContents()) {
                                me._ueditor[ueditorId].focus();
                                Alert('请输入内容', false);
                                return false;
                            }

                            me._ueditor[ueditorId].sync();
                            return $(this).form('validate');
                        },
                        onLoadError: function() {
                            log('error', arguments);
                        },
                        url: 'form.php',
                        success: function() {
                            log('success', arguments);
                        }
                    })
                    .find('.validatebox')
                        .validatebox()
                    .end()
                    .find('input[name=cate_id]')
                        .data('data-options', {
                            url: 'categories.php',
                            valueField: 'cate_id',
                            textField: 'cate_name',
                            required: true
                        })
                        .combobox();
                }
                else {
                    //cc.combobox('setValue', 'like');
                }
            });
        },//end addAction

        /**
         * 修改密码
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-08-01 17:01:56
         *
         * return {void} 无返回值
         */
        changePasswordAction: function() {log(ID);return;
            this._setActivePanel();
            var tabs = require('tabs');
            var grid = tabs.get('_el').find('#blogchangePassword');

            if (!grid.length) {
                $('<div id="blogchangePassword">changePassword</div>')
                .appendTo(tabs.getSelected())
            }
            else {
                //log(grid.datagrid('reload'));
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
            var defaults = {combotree: Q2O.combotree || '',
                sort: Q2O.sort || 'blog_id',//排序字段
                order: Q2O.order || 'desc',//排序
                start_date: Q2O.start_date || '',//添加时间,开始
                end_date: Q2O.end_date || '',//添加时间,结束
                keyword: Q2O.keyword || '',//关键字
                role_id: Q2O.role_id || '',//角色id
                cate: Q2O.cate_id || '',//角色id
                column: Q2O.column || 'username',//搜索字段
                match_mode: Q2O.match_mode || 'eq',//匹配模式
                is_lock: undefined === Q2O.is_lock ? -1 : Q2O.is_lock,//锁定状态
                is_restrict: undefined === Q2O.is_restrict ? -1 : Q2O.is_restrict,//绑定登陆状态
                page: Q2O.page || 1//页
            };

            this._datagrid(defaults, true);
        }//end listAction
    });

    var blog = new Blog();
    module.exports = blog;
});