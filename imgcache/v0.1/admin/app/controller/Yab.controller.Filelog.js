/**
 * 文件日志控制器
 *
 * @file            app/controller/Yab.controller.Filelog.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-06-27 13:34:06
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.controller.Filelog', {
    extend: 'Yab.controller.Base',
    /**
     * @cfg {String}
     * 查询字段
     */
    queryField: 'sort,order,path,column,keyword,page,match_mode',//查询字段
    /**
     * @cfg {String}
     * 主键
     */
    idProperty: 'filename',

    /**
     * 查看面板后置操作
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-28 10:03:50
     *
     * @private
     *
     * @param {String} viewData 面板数据，用于判断是否为相同文件
     *
     * @return {void} 无返回值
     */
    afterViewPanel: function (viewData) {

        if (global('app_contextmenu_refresh')) {
            this._viewStore.load();
        }
        else if (this._viewPanel._viewData != viewData) {
            this._viewPanel._viewData = viewData;
            this._viewStore.proxy.url = this.getActionUrl(false, 'view', viewData);
            this._viewStore.load();
        }

        Yab.cmp.card.layout.setActiveItem(this._viewPanel);
    },

    /**
     * @inheritdoc Yab.controller.Base#listAction
     */
    listAction: function(data) {
        data.path = data.path  || date('Y/md/');
        data.sort = data.sort || 'time';//排序字段
        data.order = data.order || 'DESC';//排序
        data.keyword = data.keyword || '';
        data.column = data.column || 'filename';
        data.match_mode = data.match_mode || 'eq';
        data.page = 1;
        this.callParent([data]);//通用列表
    },
    /**
     * @inheritdoc Yab.controller.Admin#getListColumns
     */
    getListColumns: function() {
        var me = this;

        return [{
            text: lang(me.idProperty),//文件名
            flex: 1,
            dataIndex: me.idProperty,
            renderer: function(v, cls, record) {
                var data = me.searchReplaceRenderer(v, me.idProperty);

                return '--' == record.get('time') ? '<a class="link" href="#controller=filelog&action=list&path={0}">{1}</a>'.format(v, data) : '<a class="link" href="#controller=filelog&action=view&filename={0}">{1}</a>'.format(v, data);
            }
        }, {
            header: lang('FILESIZE'),//文件大小
            width: 120,
            dataIndex: 'size',
            renderer: function(v) {
                return '--' == v ? v : formatSize(v);
            }
        }, {
            header: lang('LAST,UPDATE,TIME'),//最后更新时间
            width: 140,
            dataIndex: 'time'
        }, {//操作列
            width: 100,
            xtype: 'appactioncolumn',
            items: [{//删除
                text: lang('DELETE'),
                handler: function(grid, rowIndex, cellIndex) {
                    var record = grid.getStore().getAt(rowIndex);
                    me['delete'](record, '<span class="font-red">{0}</span>'.format(htmlspecialchars(record.get(me.idProperty))));
                }
            }]
        }];
    },//end getListColumns

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
    },

    /**
     * @inheritdoc Yab.controller.Admin#store
     */
    store: function(data) {

        this._store = this._store || Ext.create('Yab.store.Filelog', {
            //autoLoad: true
        });

        if (data) {
            var sorters = this._store.sorters.getAt(0);//排序

            //排序不一致，重新设置
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
     * @inheritdoc Yab.controller.Admin#tbar
     */
    tbar: function(data) {
        var me = this;

        return {
            xtype: 'toolbar',
            dock: 'top',
            items: [this.deleteItem(), '-', {
                xtype: 'combo',
                store: Ext.create('Yab.store.Filelog', {
                    //autoLoad: true,
                    url: me.getActionUrl(false, 'combo')
                }),
                itemId: 'path',
                value: data.path,
                displayField: me.idProperty,
                valueField: me.idProperty
            },
            Yab.Field.combo().matchMode(),//匹配模式
            Yab.Field.field().keywordField(data.keyword, {width: 120}),//关键字输入框
            this.btnSearch(function() {//搜索按钮
                var ownerCt = this.ownerCt;
                var hash = Ext.util.History.getToken();
                var data = Ext.Object.fromQueryString(hash);
                data.sort = data.sort || 'time';
                data.order = data.order || 'DESC';
                data = me.getQueryData(ownerCt, data);

                me.store(me.setHistory(data)).loadPage(1);
            })]
        }
    },//end tbar

    /**
     * 查看日志
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-28 09:59:27
     *
     * @private
     *
     * @param {Object} data 当前标签数据
     *
     * @return {void} 无返回值
     */
    viewAction: function (data) {
        var me = this,
            title = lang('CN_CHAKAN,LOG'),
            viewData = 'filename=' + data.filename;
        data['text'] = title;
        Ext.get(data.controller).update(title);
        Yab.cmp.viewport.setPageTitle(data.controller, 'list');
        Yab.cmp.viewport.setPageTitle(data.controller, 'list', title + System.sys_show_title_separator + document.title);

        me._viewStore = me._viewStore || Ext.create('Yab.store.Filelog', {
            url: me.getActionUrl(false, 'view', viewData)
        });

        if (me._viewPanel) {
            me.afterViewPanel(viewData);
        }
        else {
            me.viewPanel(viewData);
        }
    },//end viewAction

    /**
     * 查看日志面板
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-28 09:59:33
     *
     * @private
     *
     * @param {String} viewData 面板数据，用于判断是否为相同日志文件
     *
     * @return {void} 无返回值
     */
    viewPanel: function (viewData) {
        var me = this;

        me._viewPanel = Ext.create('Ext.Panel', {
            autoScroll: true,
            _viewData: viewData,
            items: [
                Ext.create('Ext.view.View', {
                    style: 'padding: 8px',
                    store: me._viewStore,
                    tpl: ['<tpl for=".">',
                            '<h1 class="viewlog">{filename}</h1>',
                            '<div class="viewlog">{content}</div>',
                        '</tpl>'
                    ]
                })//end Ext.create('Ext.view.View'
            ]
        });

        me._viewStore.load();
        me.afterViewPanel(viewData);
    }//end viewPanel
});

//放到最后，以符合生成jsduck类说明
Ext.data.JsonP.Yab_controller_Filelog(['Yab.store.Filelog', Yab.controller.Filelog]);