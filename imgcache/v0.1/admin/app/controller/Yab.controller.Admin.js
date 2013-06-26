/**
 * 管理员控制器
 *
 * @file            app/controller/Yab.controller.Admin.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-03-17 10:43:58
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.controller.Admin', {
    extend: 'Yab.controller.Base',
    /**
     * @cfg {String}
     * 主键
     */
    idProperty: 'admin_id',
    /**
     * @cfg {String}
     * 验证码id
     */
    imgCodeId: 'img-code-admin',//验证码id by mrmsl on 2012-07-14 12:35:00
    /**
     * @cfg {String}
     * 验证码模块
     */
    verifycodeModule: 'module_admin',//验证码模块 by mrmsl on 2012-09-28 17:33:05
    /**
     * @cfg {String}
     * 查询字段
     */
    queryField: 'sort,order,date_start,date_end,column,keyword,role_id,is_lock,is_restrict,page,match_mode',//查询字段 by mrmsl on 2012-07-27 15:40:37

    /**
     * @inheritdoc Yab.controller.Base#addAction
     */
    addAction: function (data) {
        var me = this,
        options = {
            listeners: {
                submitsuccess: function (form, action) {
                    if (me.getEditRecord()) {
                        //设置role_name以form.updateRecord()更新所属角色 by mrmsl on 2012-08-13 10:24:17
                        form.findField('role_name').setValue(form.findField('role_id').getDisplayValue());

                        //设置is_lock以form.updateRecord()更新是否锁定 by mrmsl on 2012-09-05 16:31:45
                        //getValue为Date对象，用getRawValue()
                        form.findField('is_lock').setValue(form.findField('lock_start_time').getRawValue() < date() && form.findField('lock_end_time').getRawValue() > date() ? 1 : 0);
                    }

                    me._listgrid && form.findField(me.idProperty).getValue() == 0 && me.store().load();//新增 by mrmsl on 2012-08-14 11:20:33
                }
            }
        };

        this.callParent([data, options]);
    },

    /**
     * 修改密码
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-07-14 12:39:29
     * @lastmodify      2013-01-14 09:29:28 by mrmsl
     *
     * @param {Object} data 当前标签数据
     *
     * @return {void} 无返回值
     */
    changePasswordAction: function(data) {
        var me = this, extField = Yab.Field.field();
        Ext.get(data.controller).update(data.text);

        if (!this._editForm) {
            this._editForm = Ext.create('Yab.ux.Form', {
                url: this.getActionUrl(false, 'changePassword'),
                controller: this,
                tabData: data,
                items: [
                    extField.displayField(ADMIN_INFO.username, {fieldLabel: lang('USERNAME')}),//用户名
                    extField.displayField(ADMIN_INFO.realname, {fieldLabel: lang('REALNAME')}),//真实姓名
                    extField.displayField(ADMIN_INFO.lastLoginTime, {fieldLabel: lang('LAST_TIME,LOGIN,TIME')}),//最后登陆时间
                    extField.displayField(ADMIN_INFO.lastLoginIp, {fieldLabel: lang('LAST_TIME,LOGIN,%ip')}),//最后登陆ip
                    extField.passwordField('_old_password', 'PLEASE_ENTER,CN_YUAN,PASSWORD', 'CN_YUAN,PASSWORD'),//原密码
                    extField.fieldContainer(['NEW,PASSWORD',[//新密码
                        ['passwordField', 'password', 'PLEASE_ENTER,NEW,PASSWORD'],
                        lang('BETWEEN_BYTE').format(6, 16)
                    ]]),
                    extField.fieldContainer(['CONFIRM_PASSWORD',[//确认密码
                        ['passwordField', '_password_confirm', 'PLEASE_ENTER,CONFIRM_PASSWORD'],
                        lang('BETWEEN_BYTE').format(6, 16)
                    ]]),
                    extField.verifyCode(me.imgCodeId, me.verifycodeModule),//验证码
                    this.btnSubmit()
                ],
                listeners: {
                    submitsuccess: function(form, action) {
                        Alert(action.result.msg);
                        form.reset();

                        var el = Ext.get(me.imgCodeId);

                        el && el.hide();

                        return false;
                    }
                }
            });
        }

        Yab.cmp.card.layout.setActiveItem(this._editForm);

        return this;
    },//end changePasswordAction

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
        global('app_labelWidth', 130);
        var me = this;
        var extField = Yab.Field.field();

        return [
            extField.fieldContainer(['USERNAME', [//用户名
                [null, 'username', 'PLEASE_ENTER,USERNAME'],
                lang('LT_BYTE').format(15) + '，' + lang('CN_TO_BYTE')
            ]]),
            extField.fieldContainer(['REALNAME', [//真实姓名
                [null, 'realname', 'PLEASE_ENTER,REALNAME'],
                lang('LT_BYTE').format(30)
            ]]),
            extField.fieldContainer(['PASSWORD', [//密码
                ['passwordField', 'password', data[this.idProperty] ? '' : 'PLEASE_ENTER,PASSWORD'],
                lang('BETWEEN_BYTE').format(6, 16)
            ], data[this.idProperty]]),
            extField.fieldContainer(['CONFIRM_PASSWORD', [//确认密码
                ['passwordField', '_password_confirm', data[this.idProperty] ? '' : 'PLEASE_ENTER,CONFIRM_PASSWORD'],
                lang('BETWEEN_BYTE').format(6, 16) + lang(',%。,KEEP_BLANK_NOT_MODIFY_PASSWORD')
            ], data[this.idProperty]]),
            extField.fieldContainer('BELONG_TO_ROLE', {//所属角色
                xtype: 'rolecombo',
                value: data.role_id,
                allowBlank: false,
                blankText: lang('PLEASE_SELECT,BELONG_TO_ROLE')
            }),
            extField.fieldContainer(['BACKEND,LOGIN,VERIFY_CODE_ORDER', [
                [null, 'verify_code_order', 'PLEASE_ENTER,VERIFY_CODE_ORDER', '', '-1', {width: 120, maxLength: 10}],
                lang('VERIFY_CODE_ORDER_TIP,%。<br />-1,MEAN,CN_QU') + '<a class="a-font-000" href="#' + me.getAction('module', 'admin') + '">' + lang('CONTROLLER_NAME_ADMIN,MODULE,VERIFY_CODE_ORDER') + '</a>'
            ], false, {vertical: true}]),
            //绑定登陆
            extField.checkbox('is_restrict', data.is_restrict, 'CN_BANGDING,LOGIN', '%' + TEXT.gray(lang('CN_BANGDING_TIP'))),
            extField.fieldContainer('LOCK,TIME', [//锁定时间
                extField.dateField({name: 'lock_start_time'}),
                extField.dateField({name: 'lock_end_time', maxValue: null})
            ], true),
            extField.textarea('lock_memo', '', 'LOCK,MEMO'),//锁定备注
            extField.textareaComment(lang('LT_BYTE').format(60)),//60字节以内提示
            extField.hiddenField(),//role_id
            extField.hiddenField('role_name'), //增加role_name以form.updateRecord()更新所属角色 by mrmsl on 2012-08-13 10:26:06
            extField.hiddenField('is_lock'), //增加is_lock以form.updateRecord()更新是否锁定 by mrmsl on 2012-09-05 16:30:57
            this.btnSubmit()//通用提交按钮 by mrmsl on 2012-08-28 10:08:44
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
            text: lang('USER') + 'id',//用户id
            width: 60,
            dataIndex: this.idProperty
        }, {
            header: lang('USERNAME'),//用户名
            width: 90,
            dataIndex: 'username',
            renderer: function(v) {
                return me.searchReplaceRenderer(v, 'username');
            }
        }, {
            header: lang('REALNAME'),//真实姓名
            width: 90,
            dataIndex: 'realname',
            renderer: function(v) {
                return me.searchReplaceRenderer(v, 'realname');
            },
            sortable: false
        }, {
            header: lang('BELONG_TO_ROLE'),//所属角色
            width: 90,
            dataIndex: 'role_name',
            sortable: false
        }, {
            header: lang('ADD,TIME'),//添加时间
            dataIndex: 'add_time',
            width: 140,
            renderer: this.renderDatetime
        }, {
            header: lang('LAST,LOGIN,TIME'),//最后登陆时间
            dataIndex: 'last_login_time',
            width: 140,
            renderer: this.renderDatetime
        }, {
            header: lang('LAST,LOGIN') + 'ip',//最后登陆ip
            dataIndex: 'last_login_ip',
            width: 120,
            sortable: false
        }, {
            header: lang('BACKEND,LOGIN,VERIFY_CODE_ORDER'),//后台登陆验证码顺序
            dataIndex: 'verify_code_order',
            width: 120,
            sortable: false
        }, {
            header: lang('LOGIN,CN_CISHU'),//登陆次数
            dataIndex: 'login_num',
            width: 60
        }, {
            header: lang('CN_BANGDING,LOGIN'),//绑定登陆
            align: 'center',
            dataIndex: 'is_restrict',
            width: 60,
            renderer: function(v) {
                return me.renderYesNoImg(v, 'is_restrict');
            }
        }, {
            header: lang('LOCK'),//锁定
            align: 'center',
            dataIndex: 'is_lock',
            width: 50,
            renderer: function(v) {
                return me.renderYesNoImg(v, 'is_lock');
            }
        }, {//操作列
            flex: 1,
            xtype: 'appactioncolumn',
            items: [{//编辑
                renderer: function(v, meta, record) {
                    return record.get(me.idProperty) == ADMIN_ID && ADMIN_INFO.id != ADMIN_ID ? '' : '<span class="appactioncolumn appactioncolumn-'+ this +'">' + lang('EDIT') + '</span>';
                },
                handler: function(grid, rowIndex, cellIndex) {
                    var record = grid.getStore().getAt(rowIndex);
                    me.edit(record, true, 'role_id=' + record.get('role_id'));
                }
            }, {//删除
                renderer: function(v, meta, record) {
                    return record.get(me.idProperty) == ADMIN_ID ? '' : '<span class="appactioncolumn appactioncolumn-'+ this +'">' + lang('DELETE') + '</span>';
                },
                handler: function(grid, rowIndex, cellIndex) {
                    var record = grid.getStore().getAt(rowIndex);
                    me['delete'](record, '<span class="font-red">{0}</span>(<span class="font-bold font-666">{1}</span>)'.format(htmlspecialchars(record.get('username')), htmlspecialchars(record.get('realname'))));
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
        data.role_id = data.role_id || '';
        data.column = data.column || 'username';
        data.match_mode = data.match_mode || 'eq';//匹配模式 by mrmsl on 2012-07-28 16:53:57
        data.is_lock = Ext.valueFrom(data.is_lock, '-1');//锁定状态 by mrmsl on 2012-09-15 11:22:40
        data.is_restrict = Ext.valueFrom(data.is_restrict, '-1');//绑定登陆状态 by mrmsl on 2012-09-15 11:45:15
        data.page = intval(data.page) || 1;//页

        var options = {
            onItemClick: function(view, record, element, index, event) {//列表点击事件
                me.listitemclick(record, event, 'is_restrict');
                me.listitemclick(record, event, 'is_lock');//锁定 by mrmsl on 2012-09-05 17:35:48
            }
        };
        this.callParent([data, options]);//通用列表 by mrmsl on 2012-08-02 13:42:12
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
     * 列表页store
     *
     * @return {Object} Ext.data.Store
     */
    store: function(data) {

        this._store = this._store || Ext.create('Yab.store.Admin');

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
            dock: 'top',
            items: [{
                text: lang('OPERATE'),
                itemId: 'btn',
                menu: [this.deleteItem(), {
                    text: lang('CN_BANGDING,LOGIN'),
                    handler: function() {
                        var selection = me.hasSelect(me.selectModel, ['is_restrict', 0]);
                        selection.length && me.setOneOrZero(selection[0], 1, 'is_restrict', lang('YOU_CONFIRM,CN_BANGDING,LOGIN,SELECTED,RECORD'), selection[1]);
                    }
                }, {
                    text: lang('RELEASE,CN_BANGDING,LOGIN'),
                    handler: function() {
                        var selection = me.hasSelect(me.selectModel, ['is_restrict', 1]);
                        selection.length && me.setOneOrZero(selection[0], 0, 'is_restrict', lang('YOU_CONFIRM,RELEASE,CN_BANGDING,LOGIN,SELECTED,RECORD'), selection[1]);
                    }
                }, {
                    text: lang('MOVE'),
                    menu: {
                        items: {
                            xtype: 'rolecombo',
                            listeners: {
                                select: function(combo, record) {
                                    this.ownerCt.parentMenu.hide();//隐藏菜单 by mrmsl on 2012-08-14 12:40:47
                                    record = record[0];
                                    var selection = me.hasSelect(me.selectModel, me.idProperty);

                                    if (selection) {
                                        me.myConfirm({
                                            action: me.getActionUrl(false, 'move'),
                                            data: {
                                                admin_id: selection[0],
                                                role_id: record.get('role_id')
                                            },
                                            confirmText: lang('YOU_CONFIRM,MOVE,SELECTED,RECORD,TO') + '<strong style="font-weight: bold; color: red">' + record.get('role_name') + '</strong>',
                                            failedMsg: lang('MOVE,FAILURE'),
                                            scope: me,
                                            store: me.store()
                                        });
                                    }
                                }
                            }
                        }
                    }
                }]
            }, '-', lang('ADD,TIME,CN_CONG'),
            extField.dateField({itemId: 'date_start'}), lang('TO'),
            extField.dateField({itemId: 'date_end'}), '-', lang('BELONG_TO_ROLE'), {
                xtype: 'rolecombo',
                url: this.getActionUrl('role', 'list', 'unshift'),
                value: data.role_id
            }, extCombo.base({//绑定登陆状态 by mrmsl on 2012-09-15 11:53:38
                width: 80,
                itemId: 'is_restrict',
                value: '-1',
                store: [
                    ['-1', lang('CN_BANGDING,STATUS')],
                    ['0', lang('CN_WEI,CN_BANGDING')],
                    ['1', lang('CN_YI,CN_BANGDING')]
                ]
            }), extCombo.base({//锁定状态 by mrmsl on 2012-09-15 11:28:14
                width: 80,
                itemId: 'is_lock',
                value: '-1',
                store: [
                    ['-1', lang('LOCK,STATUS')],
                    ['0', lang('CN_WEI,LOCK')],
                    ['1', lang('CN_YI,LOCK')]
                ]
            }), {
                xtype: 'combobox',//搜索字段
                width: 80,
                itemId: 'column',
                store: [
                    ['username', lang('USERNAME')],
                    ['realname', lang('REALNAME')]
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
    }//end tbar
});

//放到最后，以符合生成jsduck类说明
Ext.data.JsonP.Yab_controller_Admin(Yab.controller.Admin);