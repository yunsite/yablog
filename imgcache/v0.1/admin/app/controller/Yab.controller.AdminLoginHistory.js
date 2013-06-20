/**
 * 管理员登陆历史控制器
 *
 * @file            app/controller/Yab.controller.AdminLoginHistory.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-06-17 21:51:45
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.controller.AdminLoginHistory', {
    extend: 'Yab.controller.Base',
    /**
     * @cfg {String}
     * 主键
     */
    idProperty: 'login_id',
    /**
     * @cfg {String}
     * 查询字段
     */
    queryField: 'sort,order,date_start,date_end,keyword,column,page,match_mode',//查询字段

    constructor: function() {//构造函数
        this.defineModel().defineStore();
    },

    /**
     * @inheritdoc Yab.controller.Base#listAction
     */
    listAction: function(data) {
        this.defineModel().defineStore();
        data.sort = data.sort || this.idProperty;//排序字段
        data.order = data.order || 'DESC';//排序
        data.date_start = data.date_start || '';
        data.date_end = data.date_end || '';
        data.keyword = data.keyword || '';
        data.admin_id = intval(data.admin_id) || 0;
        data.username = data.username || '';
        data.realname = data.realname || '';
        data.page = intval(data.page) || 1;//页
        data.column = data.column || 'username';
        data.match_mode = data.match_mode || 'eq';//匹配模式

        this.callParent([data]);//通用列表
    },

    /**
     * @inheritdoc Yab.controller.Admin#getListColumns
     */
    getListColumns: function() {
        var me = this;

        return [{
            text: lang('LOGIN') + 'id',//登陆id
            width: 70,
            align: 'center',
            dataIndex: this.idProperty
        }, {
            header: lang('USERNAME'),//用户名
            width: 120,
            dataIndex: 'username',
            renderer: function(v) {
                return me.searchReplaceRenderer(v, 'username');
            },
            sortable: false
        }, {
            header: lang('REALNAME'),//真实姓名
            width: 100,
            dataIndex: 'realname',
            renderer: function(v) {
                return me.searchReplaceRenderer(v, 'realname');
            },
            sortable: false
        }, {
            hidden: true,
            header: lang('LOGIN') + 'ip',//管理员ip
            dataIndex: 'login_ip',
            width: 120,
            sortable: false
        }, {
            header: lang('LOGIN') + 'ip',//最后登陆ip
            dataIndex: 'login_ip',
            width: 120,
            sortable: false
        }, {
            header: lang('LOG,TIME'),//登陆时间
            dataIndex: 'login_time',
            renderer: function(v) {
                var data = Ext.Object.fromQueryString(Ext.History.getToken()),
                    datetime = me.renderDatetime(v);

                return data.date_start || data.date_end ? TEXT.red(datetime) : datetime;
            },
            width: 140,
            sortable: false
        }, {//操作列
            flex: 1,
            xtype: 'appactioncolumn',
            items: [
                this.deleteColumnItem()//删除
            ]
        }];
    },//end getListColumns

    /**
     * @inheritdoc Yab.controller.Admin#pagingBar
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

        if (!this._store) {//未创建

            this._store = Ext.create('Yab.store.AdminLoginHistory');
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
     * @inheritdoc Yab.controller.Admin#tbar
     */
    tbar: function(data) {
        var me = this, extField = Yab.Field.field();

        return {
            xtype: 'toolbar',
            dock: 'top',
            items: [this.deleteItem(), '-', lang('LOGIN,TIME,CN_CONG'),
                extField.dateField({itemId: 'date_start'}),
                lang('TO'), extField.dateField({itemId: 'date_end'}), '-',
            {
                xtype: 'combobox',//搜索字段
                width: 80,
                itemId: 'column',
                store: [
                    ['username', lang('USERNAME')],
                    ['realname', lang('REALNAME')],
                    ['admin_id', lang('CONTROLLER_NAME_ADMIN') + 'id'],
                ],
                value: data.column,
                editable: false
            }, Yab.Field.combo().matchMode(), '-', //匹配模式 by mrmsl on 2012-07-28 17:58:59
            extField.keywordField(data.keyword),//关键字输入框
            this.btnSearch(function() {
                var ownerCt = this.ownerCt;
                var hash = Ext.util.History.getToken();
                var data = Ext.Object.fromQueryString(hash);
                data.sort = data.sort || this.idProperty;
                data.order = data.order || 'DESC';
                data = me.getQueryData(ownerCt, data);
                me.store(me.setHistory(data)).loadPage(1);
            })]
        };
    },//end tbar

    //放到最后定义，否则，jsduck后，上面的方法将属于Yab.store.Login或Yab.model.Login
    /**
     * @inheritdoc Yab.controller.Field#defineModel
     */
    defineModel: function() {
        /**
         * 系统登陆数据模型
         */
        Ext.define('Yab.model.AdminLoginHistory', {
            extend: 'Ext.data.Model',
            /**
             * @cfg {Array}
             * 字段
             */
            fields: [this.idProperty, 'username', 'realname', 'login_time', 'login_ip'],
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
     * @member Yab.controller.Login
     */
    defineStore: function() {
        /**
         * 系统登陆数据容器
         */
        Ext.define('Yab.store.AdminLoginHistory', {
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
            model: 'Yab.model.AdminLoginHistory',
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
Ext.data.JsonP.Yab_controller_AdminLoginHistory(Yab.controller.AdminLoginHistory);