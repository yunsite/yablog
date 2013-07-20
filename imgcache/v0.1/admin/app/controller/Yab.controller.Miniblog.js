/**
 * 微博控制器
 *
 * @file            app/controller/Yab.controller.Miniblog.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-04-15 10:38:06
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.controller.Miniblog', {
    extend: 'Yab.controller.Base',
    /**
     * @cfg {String}
     * 主键
     */
    idProperty: 'blog_id',
    /**
     * @cfg {String}
     * 查询字段
     */
    queryField: 'sort,order,date_start,date_end,keyword,page,match_mode',//查询字段

    constructor: function() {//构造函数
        this.defineModel().defineStore();
    },

    /**
     * @inheritdoc Yab.controller.Base#addAction
     */
    addAction: function (data) {
        var me = this,
        options = {
            listeners: {
                submitsuccess: function (form, action) {
                    me._listgrid && form.findField(me.idProperty).getValue() == 0 && me.store().load();//新增
                }
            }
        };

        if (this.ueditor) {
            return me.superclass.addAction.apply(this, [data, options]);
        }

        seajs.use(USE_UEDITOR, function() {
            Ext.require('Yab.ux.Ueditor', function () {
                me.ueditor = true;
                me.superclass.addAction.apply(this, [data, options]);
            }, me);
        });
    },

    /**
     * 获取表单域
     *
     * @author       mrmsl <msl-138@163.com>
     * @date         2013-04-15 10:40:01
     *
     * @param {Object} data 当前标签数据
     *
     * @return {Array} 表单域
     */
    formField: function(data) {
        var me = this, extField = Yab.Field.field(), extCombo = Yab.Field.combo();

        return [
            extField.fieldContainer('ADD,TIME', [//添加时间
                extField.dateField({name: 'add_time', value: new Date()}),
            ]),
            {//内容
                xtype: 'ueditor',
                name: 'content',
                value: lang('PLEASE_ENTER,CONTENT'),
                fieldLabel: lang('CONTENT')
            },
            extField.hiddenField(),//blog_id
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
            text: lang('CONTROLLER_NAME_MINIBLOG') + 'id',//微博id
            width: 60,
            dataIndex: this.idProperty
        }, {
            header: lang('CONTENT'),//内容
            flex: 1,
            dataIndex: 'content',
            renderer: function(v, cls, record) {
                return _GET('keyword') ? me.searchReplaceRenderer(v, null, true) : v.replace(/<img /g, '<img style="display: none" onload="imgScale(this)" ', v);
            }
        }, {
            header: lang('ADD,TIME'),//添加时间
            dataIndex: 'add_time',
            width: 140,
            renderer: this.renderDatetime
        }, {
            header: lang('HITS'),//点击次数
            dataIndex: 'hits',
            width: 80
        }, {
            header: lang('COMMENTS'),//评论次数
            dataIndex: 'comments',
            width: 80,
            align: 'center',
            renderer: function(v, b, record) {
                var a = '<a href="#controller=comments&action=list&column=miniblog_id&type=2&auditing=1&keyword={0}" class="link">{1}</a>'.format(record.get(me.idProperty), v),
                    b = '<a href="#controller=comments&action=list&column=miniblog_id&type=2&keyword={0}" class="link"><span class="font-red">{1}</span></a>'.format(record.get(me.idProperty), record.get('total_comments'));

                return a + '/' + b;
            }
        }, {//操作列
            width: 170,
            xtype: 'appactioncolumn',
            items: [{//编辑
                renderer: function(v, meta, record) {
                    return '<span class="appactioncolumn appactioncolumn-'+ this +'">' + lang('EDIT') + '</span>';
                },
                handler: function(grid, rowIndex, cellIndex) {
                    var record = grid.getStore().getAt(rowIndex);
                    me.edit(record, true, 'cate_id=' + record.get('cate_id'));
                }
            }, {//删除
                renderer: function(v, meta, record) {
                    return '<span class="appactioncolumn appactioncolumn-'+ this +'">' + lang('DELETE') + '</span>';
                },
                handler: function(grid, rowIndex, cellIndex) {
                    var record = grid.getStore().getAt(rowIndex);
                    me['delete'](record);
                }
            }, {//删除静态页
                renderer: function(v, meta, record) {
                    return '<span class="appactioncolumn appactioncolumn-'+ this +'">' + lang('DELETE,STATIC_PAGE') + '</span>';
                },
                handler: function(grid, rowIndex, cellIndex) {
                    var record = grid.getStore().getAt(rowIndex);
                    me.deleteBlogHtml(record, lang('CN_CI,MINIBLOG'));
                }
            }]
        }];
    },//end getListColumns

    /**
     * @inheritdoc Yab.controller.Base#listAction
     */
    listAction: function(data) {
        var me = this;

        data.sort = data.sort || this.idProperty;//排序字段
        data.order = data.order || 'DESC';//排序
        data.date_start = data.date_start || '';
        data.date_end = data.date_end || '';
        data.keyword = data.keyword || '';
        data.match_mode = data.match_mode || 'like';//匹配模式
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

        if (!this._store) {//未创建

            this._store = Ext.create('Yab.store.Miniblog');
        }

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
            this._store.proxy.url = this.getActionUrl(false, 'list', this.httpBuildQuery(data, this.queryField.split(',').slice(2)));
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
            layout: {
                overflowHandler: 'Menu'
            },
            dock: 'top',
            items: [{
                text: lang('OPERATE'),
                itemId: 'btn',
                menu: [this.deleteItem(), {
                    text: lang('DELETE,STATIC_PAGE'),
                    handler: function() {
                        var selection = me.hasSelect(me.selectModel);
                        selection.length && me.deleteBlogHtml(selection);
                    }
                }]
            }, '-', lang('ADD,TIME,CN_CONG'),
            extField.dateField({itemId: 'date_start'}), lang('TO'),
            extField.dateField({itemId: 'date_end'}),
            extCombo.matchMode(),//匹配模式
            extField.keywordField(data.keyword, {width: 120}),//关键字输入框
            this.btnSearch(function() {//搜索按钮
                var ownerCt = this.ownerCt;
                var hash = Ext.util.History.getToken();
                var data = Ext.Object.fromQueryString(hash);
                data.sort = data.sort || me.idProperty;
                data.order = data.order || 'DESC';
                data = me.getQueryData(ownerCt, data);

                me.store(me.setHistory(data)).loadPage(1);
            })]
        };
    },//end tbar

    //放到最后定义，否则，jsduck后，上面的方法将属于Yab.store.Miniblog或Yab.model.Miniblog
    /**
     * @inheritdoc Yab.controller.Field#defineModel
     */
    defineModel: function() {
        /**
         * 微博数据模型
         */
        Ext.define('Yab.model.Miniblog', {
            extend: 'Ext.data.Model',
            /**
             * @cfg {Array}
             * 字段
             */
            fields: [this.idProperty, 'content', 'add_time', 'hits', 'comments', 'total_comments', 'link_url'],
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
     * @member Yab.controller.Miniblog
     */
    defineStore: function() {
        /**
         * 微博数据容器
         */
        Ext.define('Yab.store.Miniblog', {
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
            model: 'Yab.model.Miniblog',
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
                property : this.idProperty,
                direction: 'DESC'
            },
            constructor: function(config) {//构造函数
                this.callParent([config || {}]);
            }
        });

        return this;
    }//end defineStore
});

//放到最后，以符合生成jsduck类说明
Ext.data.JsonP.Yab_controller_Miniblog(Yab.controller.Miniblog);