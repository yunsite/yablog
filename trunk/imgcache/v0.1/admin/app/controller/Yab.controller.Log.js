/**
 * 系统日志控制器
 *
 * @file            app/controller/Yab.controller.Log.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-06-10 20:31:22
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.controller.Log', {
    extend: 'Yab.controller.Base',
    /**
     * @cfg {String}
     * 主键
     */
    idProperty: 'log_id',
    /**
     * @cfg {String}
     * 查询字段
     */
    queryField: 'sort,order,date_start,date_end,keyword,log_type,page,match_mode',//查询字段 by mrmsl on 2012-07-27 16:15:37

    /**
     * 类似php魔术方法，引用不存在的方法时调用
     *
     * @author           mrmsl <msl-138@163.com>
     * @date             2012-09-10 13:29:03
     * @lastmodify       2013-01-14 13:38:57 by mrmsl
     *
     * @param {Object} data 当前标签数据
     *
     * @return {void} 无返回值
     */
    __call: function(data) {

        var hackAction =  {
            admin: lang('LOG_TYPE_ADMIN_OPERATE'),//管理员操作日志
            adminLogin: lang('LOG_TYPE_ADMIN_LOGIN_INFO'),//后台登陆日志
            crontab: lang('LOG_TYPE_CRONTAB'),//定时任务
            form: lang('LOG_TYPE_VALIDATE_FORM_ERROR'),//表单自动验证错误
            loadScriptTime: lang('LOG_TYPE_SCRIPT_TIME'),//加载css及js时间记录
            param: lang('LOG_TYPE_INVALID_PARAM'),//非法参数
            permission: lang('LOG_TYPE_NO_PERMISSION'),//无权限
            sql: lang('LOG_TYPE_SQL_ERROR'),//sql错误
            system: lang('LOG_TYPE_SYSTEM_ERROR'),//系统错误
            verifyCode: lang('LOG_TYPE_VERIFYCODE_ERROR'),//验证码错误
            slowquery: lang('LOG_TYPE_SLOWQUERY'),//慢查询 by mrmsl on 2012-09-13 13:04:57
            rollbackSql: lang('LOG_TYPE_ROLLBACK_SQL')//事务回滚sql by mrmsl on 2013-02-07 14:38:50
        };
        data.log_type = hackAction[data.action];

        this.listAction(data);
    },

    constructor: function() {//构造函数
        global('SYSTEM_LOG_ARR', [
            [lang('LOG_TYPE_ALL'), lang('ALL,LOG')],
            [lang('LOG_TYPE_ADMIN_OPERATE'), lang('ADMIN_LOG')],
            [lang('LOG_TYPE_SQL_ERROR'), lang('SQL_ERROR')],
            [lang('LOG_TYPE_SYSTEM_ERROR'), lang('SYSTEM,ERROR')],
            [lang('LOG_TYPE_NO_PERMISSION'), lang('NOT_HAS,PERMISSION')],
            [lang('LOG_TYPE_INVALID_PARAM'), lang('INVALID_PARAM')],
            [lang('LOG_TYPE_ADMIN_LOGIN_INFO'), lang('ADMIN_LOGIN_LOG')],
            [lang('LOG_TYPE_CRONTAB'), lang('CRONTAB')],
            [lang('LOG_TYPE_VALIDATE_FORM_ERROR'), lang('VALIDATE_FORM_ERROR')],
            [lang('LOG_TYPE_VERIFYCODE_ERROR'), lang('VERIFYCODE_ERROR')],
            [lang('LOG_TYPE_LOAD_SCRIPT_TIME'), lang('LOG_TYPE_LOAD_SCRIPT_TIME')],
            [lang('LOG_TYPE_SLOWQUERY'), lang('SLOWQUERY')],
            [lang('LOG_TYPE_ROLLBACK_SQL'), lang('ROLLBACK_SQL')],
        ]),
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
        data.log_type = data.log_type || lang('LOG_TYPE_ALL');
        data.page = intval(data.page) || 1;//页
        data.match_mode = data.match_mode || 'like';//匹配模式 by mrmsl on 2012-07-28 16:50:43

        this.callParent([data]);//通用列表 by mrmsl on 2012-08-02 13:43:32
    },

    /**
     * @inheritdoc Yab.controller.Admin#getListColumns
     */
    getListColumns: function() {
        var me = this;

        return [{
            text: lang('LOG') + 'id',//日志id
            width: 70,
            align: 'center',
            dataIndex: this.idProperty
        }, {
            header: lang('LOG,CONTENT'),//日志内容
            flex: 1,
            minWidth: 300,
            dataIndex: 'content',
            sortable: false
        }, {
            header: lang('LOG_PAGE'),//日志页面
            width: 300,
            dataIndex: 'page_url'
        }, {
            header: lang('REFERER_PAGE'),//来源页面
            width: 200,
            hidden: true,
            dataIndex: 'referer_url'
        }, {
            header: lang('CONTROLLER_NAME_ADMIN'),//管理员姓名
            dataIndex: 'admin_name',
            sortable: false
        }, {
            hidden: true,
            header: lang('CONTROLLER_NAME_ADMIN') + 'ip',//管理员ip
            dataIndex: 'user_ip',
            width: 120,
            sortable: false
        }, {
            header: lang('LOG,TIME'),//日志时间
            dataIndex: 'log_time',
            renderer: this.renderDatetime,
            width: 140
        }, {//操作列
            width: 100,
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

            this._store = Ext.create('Yab.store.Log');
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
            items: [this.deleteItem(), '-', lang('LOG,TIME,CN_CONG'),
                extField.dateField({itemId: 'date_start'}),
                extField.dateField({itemId: 'date_end'}), lang('TO'), '-',
            {
                xtype: 'combobox',
                itemId: 'log_type',
                store: eval(lang('SYSTEM_LOG_ARR')),
                value: data.log_type,
                editable: false
            }, Yab.Field.combo().matchMode(), '-', //匹配模式 by mrmsl on 2012-07-28 17:58:59
            extField.keywordField(data.keyword),//关键字输入框
            this.btnSearch(function() {
                var ownerCt = this.ownerCt;
                var hash = Ext.util.History.getToken();
                var data = Ext.Object.fromQueryString(hash);
                data.sort = data.sort || this.idProperty;
                data.action = 'list';
                data.order = data.order || 'DESC';
                data = me.getQueryData(ownerCt, data);
                me.store(me.setHistory(data)).loadPage(1);
            })]
        };
    },//end tbar

    //放到最后定义，否则，jsduck后，上面的方法将属于Yab.store.Log或Yab.model.Log
    /**
     * @inheritdoc Yab.controller.Field#defineModel
     */
    defineModel: function() {
        /**
         * 系统日志数据模型
         */
        Ext.define('Yab.model.Log', {
            extend: 'Ext.data.Model',
            /**
             * @cfg {Array}
             * 字段
             */
            fields: [this.idProperty, 'content', 'log_time', 'page_url', 'referer_url', 'user_ip', 'admin_name'],
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
     * @member Yab.controller.Log
     */
    defineStore: function() {
        /**
         * 系统日志数据容器
         */
        Ext.define('Yab.store.Log', {
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
            model: 'Yab.model.Log',
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
Ext.data.JsonP.Yab_controller_Log(Yab.controller.Log);