/**
 * 生成静态页管理控制器
 *
 * @file            app/controller/Yab.controller.Html.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-05-18 12:07:58
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.controller.Html', {
    extend: 'Yab.controller.Base',
    /**
     * @cfg {String}
     * 主键
     */
    idProperty: 'html_id',
    /**
     * @cfg {String}
     * 查询字段
     */
    queryField: 'sort,order,page',//,sort_order,tpl_name,html_name,memo',//查询字段

    /**
     * @inheritdoc Yab.controller.Base#addAction
     */
    addAction: function (data) {
        var me = this,
        options = {
            listeners: {
                submitsuccess: function (form, action) {
                    me._listgrid && form.findField(me.idProperty).getValue() == 0 && me.store().load();
                }
            }
        };

        this.callParent([data, options]);
    },

    constructor: function() {//构造函数
        this.defineModel().defineStore();
    },

    /**
     * 生成html
     *
     * @private
     *
     * @param {String} html html_id串
     *
     * @return {void} 无返回值
     */
    build: function(html_id) {
        this.commonAction({
            action: this.getActionUrl(false, 'build'),
            data: 'html_id=' + html_id,
            scope: this,
            store: this.store()
        });
    },

    /**
     * 获取表单域
     *
     * @author       mrmsl <msl-138@163.com>
     * @date         2012-09-24 15:18:32
     * @lastmodify   2012-12-14 11:48:46 by mrmsl
     *
     * @param {Object} data 当前标签数据
     *
     * @return {Array} 表单域
     */
    formField: function(data) {global('app_labelWidth', 200);
        var me = this;
        var extField = Yab.Field.field();

        return [
            extField.fieldContainer(['TPL_NAME', [//模板名，不包括后缀
                [null, 'tpl_name', 'PLEASE_ENTER,TPL_NAME'],
                lang('TPL_NAME_TIP,%，,EXCLUDE_SUFFIX') + '。' + lang('LT_BYTE').format(30)
            ]]),
            extField.fieldContainer(['HTML_NAME,', [//生成html文件名，不包括后缀
                [null, 'html_name', 'PLEASE_ENTER,HTML_NAME'],
                lang('RELATIVE,WEBSITE,WWWROOT,%，,EXCLUDE_SUFFIX') + '。' + lang('LT_BYTE').format(30)
            ]]),
            extField.sortOrderField(),//排序
            extField.memoField(),//备注
             extField.hiddenField(),//html_id
            this.btnSubmit()//通用提交按钮
        ]
    },

    /**
     * 获取数据列
     *
     * @return {Array} 数据列配置
     */
    getListColumns: function() {
        var me = this;

        return [{
            text: 'id',//id
            width: 60,
            dataIndex: this.idProperty
        }, {
            header: lang('TPL_NAME'),//模板文件名
            width: 150,
            dataIndex: 'tpl_name',
            renderer: function(v) {
                return me.searchReplaceRenderer(v, 'tpl_name');
            }
        }, {
            header: lang('HTML_NAME'),//生成html文件名
            width: 150,
            dataIndex: 'html_name',
            renderer: function(v) {
                return me.searchReplaceRenderer(v, 'html_name');
            }
        }, {
            header: lang('ORDER'),//排序
            dataIndex: 'sort_order',
            width: 50,
            align: 'center'
        }, {
            header: lang('LAST,BUILD,TIME'),//最后生成html文件时间
            dataIndex: 'last_build_time',
            width: 140,
            renderer: this.renderDatetime
        }, {
            header: lang('MEMO'),//备注
            dataIndex: 'memo',
            flex: 1,
            sortable: false
        }, {//操作列
            flex: 1,
            xtype: 'appactioncolumn',
            items: [
            me.editColumnItem(true),
            {//生成html
                renderer: function(v, meta, record) {
                    return '<span class="appactioncolumn appactioncolumn-'+ this +'">' + lang('BUILD,STATIC_PAGE') + '</span>';
                },
                handler: function(grid, rowIndex, cellIndex) {
                    var record = grid.getStore().getAt(rowIndex);
                    me.build(record.get(me.idProperty));
                }
            }, {//删除
                renderer: function(v, meta, record) {
                    return '<span class="appactioncolumn appactioncolumn-'+ this +'">' + lang('DELETE') + '</span>';
                },
                handler: function(grid, rowIndex, cellIndex) {
                    var record = grid.getStore().getAt(rowIndex);
                    me['delete'](record, '<span class="font-red">{0}</span>(<span class="font-bold font-666">{1}</span>)'.format(htmlspecialchars(record.get('tpl_name')), htmlspecialchars(record.get('html_name'))));
                }
            }]
        }];
    },//end getListColumns

    /**
     * @inheritdoc Yab.controller.Base#listAction
     */
    listAction: function(data) {
        data.sort = data.sort || 'sort_order';//排序字段
        data.order = data.order || 'ASC';//排序
        data.page = intval(data.page) || 1;//页
        this.callParent([data]);//通用列表
    },

    /**
     * 分页条
     *
     * @param {Object} data 当前标签数据
     *
     * @return {Object} Ext.toolbar.Paging配置项
     */
    pagingBar: function(data) {
        var me = this;

        return {
            xtype: 'pagingtoolbar',
            dock: 'bottom',
            store: this.store(),
            displayInfo: true,
            listeners: {

                /**
                 * 分页前
                 *
                 * @ignore
                 *
                 * @param {Object} paging 分页条
                 * @param {Number} page      将分至页
                 *
                 * @return {void} 无返回值
                 */
                beforechange: function(paging, page) {
                    this.changed = true;
                },

                /**
                 * 分页后
                 *
                 * @ignore
                 *
                 * @param {Object} grid     列表grid
                 * @param {Object} pageData 分类数据
                 *
                 * @return {void} 无返回值
                 */
                change: function(grid, pageData) {
                    if (pageData && !isNaN(pageData.pageCount) && this.changed) {//保证经过beforechange
                        data = {
                            page: pageData.currentPage,
                            sort: data.sort,
                            order: data.order
                        };
                        data.page != _GET('page') && me.store(me.setHistory(data));
                        this.changed = false;
                    }
                }
            }
        };
    },//end pagingBar

    /**
     * @inheritdoc Yab.controller.Admin#store
     */
    store: function(data) {
        this._store = this._store || Ext.create('Yab.store.Html');

        if (data) {
            var sorters = this._store.sorters.getAt(0);//排序

            //排序不一致，重新设置 by mrmsl on 2012-07-27 15:45:18
            if (sorters.property != data.sort || sorters.direction != data.order) {
                this._store.sorters.clear();
                this._store.sorters.add(new Ext.util.Sorter({
                    property: data.sort,
                    direction: data.order
                }));
            }


            this._store._data = this.httpBuildQuery(data, this.queryField);
            //this._store.proxy.url = this.getActionUrl(false, 'list');
        }

        return this._store;
    },//end store

    /**
     * 列表顶部工具栏
     *
     * @return {Object} Ext.tool.Toolbar工具栏配置项
     */
    tbar: function(data) {
        var me = this, extField = Yab.Field.field(), extCombo = Yab.Field.combo();

        return {
            xtype: 'toolbar',
            dock: 'top',
            items: [{
                text: lang('OPERATE'),
                itemId: 'btn',
                menu: [
                    this.deleteItem(), {
                    text: lang('BUILD,STATIC_PAGE'),
                    handler: function() {
                        var html = me.hasSelect(me.selectModel);
                        html && me.build(html);
                    }
                }]
            }]
        };
    },//end tbar

    //放到最后定义，否则，jsduck后，上面的方法将属于Yab.store.Html.model.Html
    /**
     * @inheritdoc Yab.controller.Field#defineModel
     */
    defineModel: function() {
        /**
         * 系统日志数据模型
         */
        Ext.define('Yab.model.Html', {
            extend: 'Ext.data.Model',
            /**
             * @cfg {Array}
             * 字段
             */
            fields: [this.idProperty, 'tpl_name', 'html_name', 'last_build_time', 'memo', 'sort_order'],
            /**
             * @cfg {String}
             * 主键
             */
            idProperty: this.idProperty
        });

        return this;
    },

    /**
     * @inheritdoc Yab.controller.Field#defineStore
     * @member Yab.controller.Html
     */
    defineStore: function() {
        /**
         * 系统日志数据容器
         */
        Ext.define('Yab.store.Html', {
            extend: 'Ext.data.Store',
            /**
             * @cfg {Boolean}
             * 自动消毁
             */
            autoDestroy: true,
            /**
             * @cfg {Boolean}
             * 服务器端排序
             */
            remoteSort: true,
            /**
             * @cfg {Object/String}
             * 模型
             */
            model: 'Yab.model.Html',
            /**
             * @cfg {Object}
             * proxy
             */
            proxy: {
                type: C.dataType,
                url: this.getActionUrl(false, 'list'),
                reader: C.dataReader(),
                listeners: exception(),//捕获异常 by mrmsl on 2012-07-08 21:44:36
                messageProperty: 'msg',
                simpleSortMode: true
            },
            //增加排序，以防点击列表头部排序时，多余传参，出现不必要的错误 by mrmsl on 2012-07-27 16:21:54
            /**
             * @cfg {Object}
             * sorters
             */
            sorters: {
                property : 'sort_order',
                direction: 'ASC'
            },
            constructor: function(config) {//构造函数
                this.callParent([config || {}]);
            }
        });

        return this;
    }//end defineStore
});

//放到最后，以符合生成jsduck类说明
Ext.data.JsonP.Yab_controller_Html(Yab.controller.Html);