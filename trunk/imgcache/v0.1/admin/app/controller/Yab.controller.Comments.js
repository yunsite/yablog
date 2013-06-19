/**
 * 留言评论控制器
 *
 * @file            app/controller/Yab.controller.Comments.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-05-28 11:25:45
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.controller.Comments', {
    extend: 'Yab.controller.Base',
    /**
     * @cfg {String}
     * 主键
     */
    idProperty: 'comment_id',
    /**
     * @property {Array}
     * 类型
     */
    typeArr: [
        ['0', lang('GUESTBOOK')],
        ['1', lang('BLOG,COMMENT')],
        ['2', lang('MINIBLOG,COMMENT')]
    ],
    /**
     * @property {Array}
     * 回复类型
     */
    replyArr: [
        ['0', lang('CN_WEI,REPLY')],
        ['1', lang('CN_YI,REPLY')],
        ['2', lang('MODULE_NAME_ADMIN,REPLY')]
    ],
    /**
     * @property {Array}
     * 状态
     */
    statusArr: [TEXT.gray(lang('CN_WEI,AUDITING'), null), TEXT.green(lang('CN_YI,PASS')), TEXT.red(lang('CN_WEI,PASS'))],
    /**
     * @cfg {String}
     * 查询字段
     */
    queryField: 'sort,order,date_start,date_end,column,keyword,type,auditing,admin_reply_type,page,match_mode',//查询字段

    /**
     * @inheritdoc Yab.controller.Base#addAction
     */
    addAction: function (data) {
        var me = this,
        options = {
            listeners: {
                submitsuccess: function (form, action) {
                     me.store().load();
                }
            }
        };

        if (this.ueditor) {
            return me.superclass.addAction.apply(this, [data, options]);
        }

        seajs.use(['ueditor', 'ueditorConfig'], function() {
            Ext.require('Yab.ux.Ueditor', function () {
                me.ueditor = true;
                me.superclass.addAction.apply(this, [data, options]);
            }, me);
        });
    },

    /**
     * 登陆查看面板后置操作
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-05 09:05:06
     *
     * @private
     *
     * @param {String} viewData 面板数据，用于判断是否为相同评论id
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
     * 审核
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-17 14:52:21
     *
     * @private
     *
     * @param {Mixed}  record      record数据或id串
     * @param {Number} status      状态
     *
     * @return {void} 无返回值
     */
    auditing: function(record, status) {
        var pkValue;
        var controller = this.getControllerName();

        if (Ext.isString(record)) {//选中删除
            pkValue = record;
            var confirmText = 'SELECTED';
        }
        else {//点击删除
            pkValue = record.get(this.idProperty);
            var confirmText = 'CN_CI';
        }

        var options = {
            action: this.getActionUrl(false, 'auditing'),
            data: this.idProperty + '=' + pkValue + '&status=' + status,
            confirmText: lang('YOU_CONFIRM') + this.statusArr[status] + lang(confirmText + ',RECORD'),
            failedMsg: lang('AUDITING,FAILURE'),
            scope: this,
            store: this.store()
        };

        this.myConfirm(options);
    },//end auditing

    constructor: function() {//构造函数
        this.defineModel().defineStore();
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
    formField: function(data) {
        var me = this, extField = Yab.Field.field(), extCombo = Yab.Field.combo();

        return [
            extField.fieldContainer(['USERNAME', [//用户名
                [null, 'username', 'PLEASE_ENTER,USERNAME'],
                lang('LT_BYTE').format(20) + '，' + lang('CN_TO_BYTE')
            ]]),
            extField.fieldContainer(['email', [//邮箱
                [null, 'email', '', false, '', {width: 200}],
                lang('LT_BYTE').format(50)
            ], true]),
            extField.fieldContainer(['HOMEPAGE', [//用户主页
                [null, 'user_homepage', '', false, '', {width: 200}],
                lang('LT_BYTE').format(50)
            ], true]),
            {//内容
                xtype: 'ueditor',
                name: 'content',
                value: lang('PLEASE_ENTER,CONTENT'),
                fieldLabel: lang('CONTENT')
            },
            extField.hiddenField(),//comment_id
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
            width: 50,
            dataIndex: this.idProperty
        }, {
            header: lang('USERNAME'),//用户
            width: 80,
            dataIndex: 'username',
            renderer: function(v, cls, record) {
                return record.get('user_homepage') ? '<a href="{0}" target="_blank" class="link">{1}</a>'.format(record.get('user_homepage'), me.searchReplaceRenderer(v, 'username')) : me.searchReplaceRenderer(v, 'username');
            },
            sortable: false
        }, {
            header: lang('CONTENT'),//内容
            flex: 1,
            minWidth: 300,
            dataIndex: 'content',
            renderer: function (v, cls, record) {
                var data = Ext.Object.fromQueryString(Ext.History.getToken());

                return data.keyword && 'content' == data.column ? me.searchReplaceRenderer(strip_tags(v), 'content') : (record.get('parent_id') > 0 ? v.replace('@<a class="link"', '@<a class="link" onclick="return false"') : v);
            },
            sortable: false
        }, {
            header: lang('EMAIL'),//邮箱
            align: 'center',
            width: 120,
            dataIndex: 'email',
            renderer: function (v) {
                return me.searchReplaceRenderer(v, 'email');
            },
            sortable: false
        }, {
            header: 'ip' + lang('MODULE_NAME_AREA'),//ip地址
            width: 120,
            dataIndex: 'user_ip',
            renderer: function (v, a, record) {
                var province = record.get('province'), city = record.get('city');

                return 'ip:' + v + '<br />' + province + (province == city ? '' : city);
            },
            sortable: false
        }, {
            header: lang('ADD,TIME'),//添加时间
            align: 'center',
            dataIndex: 'add_time',
            width: 140,
            renderer: this.renderDatetime,
            sortable: false
        }, {
            header: lang('type'),//类型
            align: 'center',
            dataIndex: 'type',
            width: 80,
            renderer: function(v, cls, record) {
                var title = record.get('title'),
                replyType = record.get('admin_reply_type'),
                html = me.typeArr[v][1];

                html = title ? '<a href="{0}" target="_blank" class="link" title="{1}">{2}</a>'.format(record.get('link_url'), title, html) : html;
                html += '<br />';

                if (2 == replyType) {
                    html += TEXT.gray(me.replyArr[2][1]);
                }
                else if (1 == replyType) {
                    html += TEXT.green(me.replyArr[1][1]);
                }
                else {
                    html += TEXT.red(me.replyArr[0][1]);
                }

                return html;
            },
            sortable: false
        }, {
            header: lang('STATUS'),//状态
            align: 'center',
            dataIndex: 'status',
            width: 60,
            renderer: function(v, cls, record) {
                return me.statusArr[v];
            },
            sortable: false
        }, {//操作列
            width: 160,
            xtype: 'appactioncolumn',
            items: [{//编辑,查看,删除
                renderer: function(v, meta, record) {
                    var html = [''];

                    html.push('<p class="appactioncolumn">');
                    html.push('   <span class="appactioncolumn appactioncolumn-', this, '" data-action="edit">', lang('EDIT'), '</span>| ');
                    html.push('   <span class="appactioncolumn appactioncolumn-', this, '" data-action="view">', lang('CN_CHAKAN,CN_YU,REPLY'), '</span>| ');
                    html.push('   <span class="appactioncolumn appactioncolumn-', this, '" data-action="delete">', lang('DELETE'), '</span>');
                    html.push('   </p><p>');
                    html.push('   <span class="appactioncolumn appactioncolumn-', this, '" data-action="pass" data-status="1">', lang('PASS'), '</span>| ');
                    html.push('   <span class="appactioncolumn appactioncolumn-', this, '" data-action="pass" data-status="2">', lang('NO,PASS'), '</span>| ');
                    html.push('   <span class="appactioncolumn appactioncolumn-', this, '" data-action="pass" data-status="0">', lang('CN_WEI,AUDITING'), '</span>');
                    html.push('</p>');

                    return html.join('');
                },
                handler: function(grid, rowIndex, cellIndex, options, event) {
                    var element = Ext.get(event.getTarget()),
                        action = element.getAttribute('data-action'),
                        record = grid.getStore().getAt(rowIndex),
                        id = record.get(me.idProperty);

                    switch (action) {

                        case 'edit'://编辑
                            me.edit(record);
                            break;

                        case 'delete'://删除
                            me['delete'](record, lang('CN_CI,' + (0 == record.get('type') ? 'GUESTBOOK' : 'COMMENT')));
                            break;

                        case 'view'://查看
                            Yab.History.push('controller=comments&action=view&comment_id={0}&add_time={1}&back={2}'.format(id, record.get('add_time'), encodeURIComponent(location.href)));
                            break;

                        case 'pass'://通过,不通过,未审核

                            if (record.get('status') == element.getAttribute('data-status')) {
                                info(lang('CN_CI,RECORD,DO_NOT_NEED_TO_BE_UPDATE'), null, true);
                                return;
                            }

                            me.auditing(record, element.getAttribute('data-status'));
                            break;
                    }
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
        data.column = data.column || 'username';
        data.match_mode = data.match_mode || 'eq';//匹配模式
        data.type = Ext.valueFrom(data.type, '-1');//类型
        data.auditing = Ext.valueFrom(data.auditing, '-1');//审核状态
        data.admin_reply_type = Ext.valueFrom(data.admin_reply_type, '-1');//回复状态
        data.page = intval(data.page) || 1;//页

        var options = {
            //onItemContextMenu: function () {log('onItemContextMenu', arguments);},
            onItemClick: function(view, record, element, index, event) {//列表点击事件
                //me.listitemclick(record, event, 'is_restrict');
                //me.listitemclick(record, event, 'is_lock');//锁定 by mrmsl on 2012-09-05 17:35:48
            }
        };
        this.callParent([data, options]);//通用列表
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
     * 重新获取ip地区信息
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-17 14:52:21
     *
     * @private
     *
     * @param {Mixed}  record      record数据或id串
     *
     * @return {void} 无返回值
     */
    afreshIp: function(record) {
        var data, _lang;
        var controller = this.getControllerName();

        if (Ext.isString(record)) {//选中删除
            data = record;
            _lang  = 'SELECTED';
        }
        else {//点击删除
            data = record.get(this.idProperty) + '|' + record.get('user_ip');
            _lang = 'CN_CI';
        }

        var options = {
            action: this.getActionUrl(false, 'afreshIp'),
            data:'data=' + data,
            confirmText: lang('YOU_CONFIRM,AFRESH,GET,' + _lang + ',RECORD,%ip,AREA'),
            failedMsg: lang('AFRESH,GET,%ip,AREA,FAILURE'),
            scope: this,
            store: this.store()
        };

        this.myConfirm(options);
    },//end afreshIp

    /**
     * @inheritdoc Yab.controller.Admin#formField
     */
    replyFormFields: function () {
        var me = this, extField = Yab.Field.field();
        //global('app_labelWidth', 60);//提交按钮labelWidth
        global('app_btnText', lang('REPLY'));//提交按钮文字

        return [
            {//内容
                xtype: 'ueditor',
                name: 'content',
                value: lang('PLEASE_ENTER,CONTENT'),
                height: 100,
                width: 800,
                fieldLabel: lang('REPLY,CONTENT')
            },
            this.btnSubmit(false),//通用提交按钮
            extField.hiddenField(me.idProperty),
            extField.hiddenField('add_time')
        ];
    },

    /**
     * @inheritdoc Yab.controller.Base#formPanel
     */
    replyFormPanel: function() {
        var me = this;

        me._replyForm = me._replyForm || Ext.create('Yab.ux.Form', {
            height: 400,
            controller: this,
            url: this.getActionUrl(false, 'reply'),
            bodyStyle: 'border: none',
            items: this.replyFormFields()
        });

        return me._replyForm;
    },//end replyFormPanel

    /**
     * 列表页store
     *
     * @return {Object} Ext.data.Store
     */
    store: function(data) {

        this._store = this._store || Ext.create('Yab.store.Comments');

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
        var me = this, extField = Yab.Field.field(), extCombo = Yab.Field.combo(), typeArr = Ext.Array.clone(me.typeArr), replyArr = Ext.Array.clone(me.replyArr);
        typeArr.unshift(['-1', lang('TYPE')]);
        replyArr.unshift(['-1', lang('REPLY,STATUS')]);

        return {
            xtype: 'toolbar',
            dock: 'top',
            items: [{
                text: lang('OPERATE'),
                itemId: 'btn',
                menu: [this.deleteItem(), {
                    text: lang('PASS'),
                    handler: function() {
                        var selection = me.hasSelect(me.selectModel, ['status', ['0', '2']]);
                        selection.length && me.auditing(selection[0], 1);
                    }
                }, {
                    text: lang('NO,PASS'),
                    handler: function() {
                        var selection = me.hasSelect(me.selectModel, ['status', ['0', '1']]);
                        selection.length && me.auditing(selection[0], 2);
                    }
                }, {
                    text: lang('CN_WEI,AUDITING'),
                    handler: function() {
                        var selection = me.hasSelect(me.selectModel, ['status', ['1', '2']]);
                        selection.length && me.auditing(selection[0], 0);
                    }
                }, {
                    text: lang('AFRESH,GET,%ip,AREA'),
                    handler: function() {
                        var selection = me.hasSelect(me.selectModel, me.idProperty + ',user_ip');
                        selection.length && me.afreshIp(selection[0]);
                    }
                }]
            }, '-', lang('ADD,TIME,CN_CONG'),
            extField.dateField({itemId: 'date_start'}), lang('TO'),
            extField.dateField({itemId: 'date_end'}), '-',
            extCombo.base({//类型
                width: 80,
                itemId: 'type',
                value: '-1',
                store: typeArr
            }),
            extCombo.auditing(),//审核状态
            {
                xtype: 'combobox',//回复状态
                width: 90,
                itemId: 'admin_reply_type',
                store: replyArr
            }, {
                xtype: 'combobox',//搜索字段
                width: 80,
                itemId: 'column',
                store: [
                    ['username', lang('USERNAME')],
                    ['email', lang('EMAIL')],
                    ['content', lang('CONTENT')],
                    ['blog_title', lang('BLOG,TITLE')],
                    ['blog_content', lang('BLOG,CONTENT')],
                    ['blog_id', lang('BLOG') + 'id'],
                    ['miniblog_id', lang('MINIBLOG') + 'id']
                ],
                value: data.column,
                editable: false
            },
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

    /**
     * 查看留言评论
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-01 15:30:41
     *
     * @private
     *
     * @param {Object} data 当前标签数据
     *
     * @return {void} 无返回值
     */
    viewAction: function (data) {
        var me = this,
            pkValue = intval(data[me.idProperty]),
            addTime = intval(data['add_time']),
            title = lang('CN_CHAKAN,GUESTBOOK_COMMENTS'),
            viewData = 'comment_id={0}&add_time={1}'.format(pkValue, addTime);
        data['text'] = title;
        Ext.get(data.controller).update(title);
        Yab.cmp.viewport.setPageTitle(data.controller, 'list');
        Yab.cmp.viewport.setPageTitle(data.controller, 'list', title + System.sys_show_title_separator + document.title);

        me._viewStore = me._viewStore || Ext.create('Yab.store.Comments', {
            url: me.getActionUrl(false, 'view', viewData),
            listeners: {
                load: function (store) {
                    var data = store.proxy.reader.rawData;
                    me.replyFormPanel().getForm().setValues(data.msg);
                }
            }
        });

        if (me._viewPanel) {
            me.afterViewPanel(viewData);
        }
        else {
            me.viewPanel(viewData);
        }
    },//end viewComments

    /**
     * 登陆面板
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-05 09:06:59
     *
     * @private
     *
     * @param {String} viewData 面板数据，用于判断是否为相同评论id
     *
     * @return {void} 无返回值
     */
    viewPanel: function (viewData) {
        var me = this;

        seajs.use(['Yab.ux.Ueditor', 'ueditor', 'ueditorConfig'], function () {

            me._viewPanel = Ext.create('Ext.Panel', {
                autoScroll: true,
                _viewData: viewData,
                items: [
                    Ext.create('Ext.view.View', {
                        style: 'padding: 8px',
                        store: me._viewStore,
                        tpl: [
                            '<tpl for=".">',
                                '{% out.push(this.loop(values)); %}',
                            '</tpl>',
                            {
                                loop: function (data, isReply) {
                                    var html = [];
                                    html.push('<div class="comment-detail', isReply ? ' comment-reply' : '' ,'" id="comment-', data.comment_id, '">');
                                    html.push('    <img class="float-left avatar avatar-level-', data.level, '" alt="" src="http://imgcache.yablog.com/common/images/guest.png" />');
                                    html.push('    <div class="comment-body">');
                                    html.push('        <p class="font-gray">');
                                    html.push('            <span class="float-right">', me.renderDatetime(data.add_time), '</span>');

                                    data.user_homepage && html.push('<a href="{0}" class="link" target="_blank">{1}</a>'.format(data.user_homepage, data.username));

                                    html.push('            ip: ', data.user_ip, '[', data.province, data.province == data.city ? '' : data.city, ']');
                                    html.push('        </p>');
                                    html.push('        ', data.parent_id ? data.content.replace('@<a class="link"', '@<a class="link" onclick="return false"') : data.content);

                                    if (data.data) {
                                        Ext.Array.each(data.data, function(item) {
                                            html.push(this.loop(item, true));
                                        }, this);
                                    }

                                    html.push('</div></div>');

                                    return html.join('');
                                }
                            }
                        ]
                    }),
                    me.replyFormPanel()
                ]
            });

            me._viewStore.load();
            me.afterViewPanel(viewData);
        });//end seajs.use
    },//end viewPanel

    //放到最后定义，否则，jsduck后，上面的方法将属于Yab.store.Blog或Yab.model.Blog
    /**
     * @inheritdoc Yab.controller.Field#defineModel
     */
    defineModel: function() {
        /**
         * 留言评论数据模型
         */
        Ext.define('Yab.model.Comments', {
            extend: 'Ext.data.Model',
            /**
             * @cfg {Array}
             * 字段
             */
            fields: [this.idProperty, 'blog_id', 'content', 'add_time', 'last_reply_time', 'username', 'user_ip', 'username', 'email','user_homepage', 'status', 'type', 'at_email', 'province', 'city', 'is_admin', 'title', 'link_url', 'data', 'admin_reply_type', 'parent_id'],
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
     * @member Yab.controller.Blog
     */
    defineStore: function() {
        /**
         * 博客数据容器
         */
        Ext.define('Yab.store.Comments', {
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
            model: 'Yab.model.Comments',
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
            //增加排序，以防点击列表头部排序时，多余传参，出现不必要的错误
            /**
             * @cfg {Object}
             * sorters
             */
            sorters: {
                property : this.idProperty,
                direction: 'DESC'
            },
            constructor: function(config) {//构造函数

                if (config && config.url) {
                    this.proxy.url = config.url;
                };

                this.callParent([config || {}]);
            }
        });

        return this;
    }//end defineStore
});

//放到最后，以符合生成jsduck类说明
Ext.data.JsonP.Yab_controller_Comments(Yab.controller.Comments);