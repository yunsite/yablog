/**
 * 表单域控制器
 *
 * @file            app/controller/Yab.controller.Field.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-08-01 10:51:24
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.controller.Field', {
    extend: 'Yab.controller.Base',
    /**
     * @cfg {String}
     * 主键
     */
    idProperty: 'field_id',//主键 by mrmsl on 2012-08-06 21:46:36
    /**
     * @cfg {String}
     * 名称字段
     */
    nameColumn: 'field_name',//名称字段 by mrmsl on 2012-08-23 13:59:33
    /**
     * @cfg {String}
     * 查询字段
     */
    queryField: 'sort,order,column,keyword,is_enable,page,menu_id,match_mode',//查询字段 by mrmsl on 2012-08-02 08:52:49

    constructor: function() {//构造函数
        this.defineModel().defineStore();
    },

    /**
     * @inheritdoc Yab.controller.Base#listAction
     */
    listAction: function(data) {
        data.sort = data.sort || this.idProperty;//排序字段
        data.order = data.order || 'DESC';//排序
        data.keyword = data.keyword || '';
        data.menu_id = data.menu_id || '';
        data.column = data.column || this.nameColumn;
        data.match_mode = data.match_mode || 'eq';//匹配模式 by mrmsl on 2012-07-28 16:53:57
        data.is_enable = Ext.valueFrom(data.is_enable, '-1');//是否启用 by mrmsl on 2012-09-15 14:24:23
        data.page = intval(data.page) || 1;//页

        var me = this, options = {
            onItemClick: function(view, record, element, index, event) {//列表点击事件
                me.listitemclick(record, event, 'is_enable');
            }
        };
        this.callParent([data, options]);//通用列表 by mrmsl on 2012-08-02 13:43:06
    },

    /**
     * @inheritdoc Yab.controller.Admin#formField
     */
    formField: function(data) {
        var me = this, extField = Yab.Field.field();
        global('app_labelWidth', 150);
        var field = [extField.hiddenField(),//field_id
        extField.fieldContainer(['FIELD_NAME,%(fieldLabel)', [//表单域名
            [null, this.nameColumn, 'PLEASE_ENTER,FIELD_NAME', '', undefined, {width: 250}],
            lang('LT_BYTE').format(50) + '，' + lang('CN_TO_BYTE')
        ]]),
        extField.fieldContainer(['INPUT_NAME', [//输入框名称
            [null, 'input_name', 'PLEASE_ENTER,INPUT_NAME', '', undefined, {width: 250}],
            lang('LT_BYTE').format(50) + lang('%。,TO_EDIT_TIP')
        ]]),
        {
            allowBlank: false,
            blankText: lang('PLEASE_SELECT,PARENT_FIELD'),
            xtype: 'treepicker',
            fieldLabel: TEXT.red() + lang('PARENT_FIELD'),
            labelWidth: global('app_labelWidth'),
            width: 400,
            name: '_menu_name',
            singleSelectValueField: 'menu_id',
            emptyText: lang('PLEASE_SELECT'),
            displayField: 'menu_name',
            pickerIdProperty: 'menu_id',
            store: Ext.create('Yab.store.Tree', {
                folderSort: false,
                url: this.getActionUrl('menu', 'publicTree', 'parent_id=' + data.parent_id)
            }),
            storeOnLoad: function(store) {//添加指定菜单表单域，设置指定菜单相关信息 by mrmsl on 2012-08-21 15:20:19
                 var data = store.proxy.reader.rawData;

                 if (data && data.parent_data) {
                    data = data.parent_data
                    this.up('form').getForm().findField('menu_id').setValue(data.menu_id);//父级id
                    this.setRawValue(data.parent_name);
                 }
            }
        },
        //js代码
        extField.textarea('field_code', 'PLEASE_ENTER,FIELD_CODE', 'FIELD_CODE', "xtype: 'textfield',\n\
fieldLabel: '@fieldLabel',\n\
name: '@input_name',\n\
value: '@value'", {
            width: 700,
            height: 120
        }),
        extField.textareaComment(TEXT.gray(this.toBeReplaced({//js代码提示
            '@fieldLabel': lang('FIELD_NAME'),
            '@input_name': lang('INPUT_NAME'),
            '@value': lang('MODULE_NAME_FIELD,VALUE')
        }, false, ',')), null),
        extField.textarea('validate_rule', 'PLEASE_ENTER,VALIDATE_RULE', 'VALIDATE_RULE', 'string\n\
#{%PLEASE_ENTER,@field_name}#MUST_VALIDATE#notblank', {
            width: 900,
            height: 150
        }),
        extField.textareaComment(lang('VALIDATE_RULE_TIP'), null),//验证规则提示
        //自动填充
        extField.textarea('auto_operation', false, 'AUTO_OPERATION', '', {
            width: 900,
            height: 50
        }),
        extField.textareaComment(lang('AUTO_OPERATION_TIP'), null),//自动填充提示
        extField.hiddenField('menu_id'),
        extField.checkbox('is_enable', Ext.valueFrom(data.is_enable, 1), 'ENABLE'),
        extField.sortOrderField(),
        extField.numberField('customize_1', 0, 'CUSTOMIZE,COLUMN,%_1', {
            minValue: 0,
            maxValue: 9,
            size: 5
        }),
        extField.memoField({
            width: 500
        }),
        extField.textareaComment(lang('LT_BYTE').format(100), null),
        this.btnSubmit()//通用提交按钮 by mrmsl on 2012-08-28 10:12:16
        ];

        return field;
    },//end formField

    /**
     * @inheritdoc Yab.controller.Admin#getListColumns
     */
    getListColumns: function() {
        var me = this;

        return [{
            text: lang('MODULE_NAME_FIELD') + 'id',//表单域id
            width: 70,
            sortable: true,
            dataIndex: this.idProperty
        }, {
            header: lang('FIELD_NAME'),//表单域名
            width: 150,
            dataIndex: this.nameColumn,
            renderer: function(v) {
                return me.searchReplaceRenderer(v, me.nameColumn);
            },
            sortable: false
        }, {
            header: lang('INPUT_NAME'),//输入框名
            width: 150,
            dataIndex: 'input_name',
            renderer: function(v) {
                return me.searchReplaceRenderer(v, 'input_name');
            }
        }, {
            header: lang('PARENT_FIELD'),//所属菜单
            width: 120,
            dataIndex: 'menu_name',
            sortable: false
        }, {
            header: lang('FIELD_CODE'),//js代码
            dataIndex: 'field_code',
            flex: 1,
            minWidth: 250,
            renderer: function(v) {
                return me.searchReplaceRenderer(v, 'field_code');
            },
            sortable: false
        }, {
            header: lang('VALIDATE_RULE'),//验证规则
            dataIndex: 'validate_rule',
            width: 250,
            hidden: true,
            renderer: function(v) {
                return me.searchReplaceRenderer(v, 'validate_rule');
            },
            sortable: false
        }, {
            header: lang('ORDER'),//排序
            dataIndex: 'sort_order',
            width: 50,
            align: 'center',
            sortable: false
        }, {
            header: lang('ENABLE'),//启用
            align: 'center',
            dataIndex: 'is_enable',
            width: 50,
            renderer: function(v) {
                return me.renderYesNoImg(v, 'is_enable');
            }
        }, {
            header: lang('CUSTOMIZE,COLUMN,%_1'),//自定义字段_1
            align: 'center',
            dataIndex: 'customize_1',
            width: 90,
            hidden: true
        }, {//操作列
            width: 120,
            xtype: 'appactioncolumn',
            items: [{
                    text: lang('CLONE'),//复制 by mrmsl on 2012-09-29 12:56:24
                    handler: function(grid, rowIndex, cellIndex) {
                        var record = grid.getStore().getAt(rowIndex);
                        me.edit(record, false, 'clone=true');
                    }
                },
                this.editColumnItem(true),//编辑
                this.deleteColumnItem(this.nameColumn)//删除
            ]
        }];
    },//end getListColumns

    /**
     * @inheritdoc Yab.controller.Base#loadEditDataSuccess
     */
    loadEditDataSuccess: function(form, action) {
        form.findField('_menu_name').setRawValue(action.result.data.menu_name);
    },

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
     * @inheritdoc Yab.controller.Admin#tbar
     */
    store: function(data) {
        this._store = this._store || Ext.create('Yab.store.Field');

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
            items: [{
                text: lang('OPERATE'),
                itemId: 'btn',
                menu: [this.deleteItem(), {
                    text: lang('ENABLE'),
                    handler: function() {
                        var selection = me.hasSelect(me.selectModel, ['is_enable', 0]);
                        selection.length && me.setOneOrZero(selection[0], 1, 'is_enable', lang('YOU_CONFIRM,ENABLE,SELECTED,RECORD'), selection[1]);
                    }
                }, {
                    text: lang('DISABLED'),
                    handler: function() {
                        var selection = me.hasSelect(me.selectModel, ['is_enable', 1]);
                        selection.length && me.setOneOrZero(selection[0], 0, 'is_enable', lang('YOU_CONFIRM,DISABLED,SELECTED,RECORD'), selection[1]);
                    }
                }]//end button field
            }, '-', Yab.Field.combo().enable(),//显示状态 by mrmsl on 2012-09-15 14:23:08
            /*if (Ext.isString(this.singleSelectValueField)) {//表单域名
                this.singleSelectValueField = form.findField(this.singleSelectValueField);
            }
            else {//前面一个表单域
                this.singleSelectValueField = this.previousSibling();
            }*/
             Yab.Field.field().textField('menu_id', '', '', data.menu_id, {itemId: 'menu_id', hidden: true}),
            {
                xtype: 'treepicker',
                name: '_menu_id',
                value: data.menu_id,
                emptyText: lang('PARENT_FIELD'),
                displayField: 'menu_name',
                pickerIdProperty: 'menu_id',
                width: 200,
                store: Ext.create('Yab.store.Tree', {
                    folderSort: false,
                    url: this.getActionUrl('menu', 'publicTree', 'unshift&include_self&menu_id={0}&parent_id={0}&emptyText={1}'.format(data.menu_id, encodeURIComponent(lang('PARENT_FIELD'))))
                }),
                storeOnLoad: function(store) {//设置所属分类文字
                     var data = store.proxy.reader.rawData;
                     if (data && data.parent_data) {
                         this.setRawValue(data.parent_data.parent_name);
                     }
                }
            }, {
                xtype: 'combobox',//搜索字段 by mrmsl on 2012-07-26 13:34:36
                width: 100,
                itemId: 'column',
                store: [
                    [this.nameColumn, lang('FIELD_NAME')],
                    ['field_code', lang('FIELD_CODE')],
                    ['input_name', lang('INPUT_NAME')],
                    ['validate_rule', lang('VALIDATE_RULE')]
                ],
                value: data.column,
                editable: false
            },
            Yab.Field.combo().matchMode(),//匹配模式
            Yab.Field.field().keywordField(data.keyword),//关键字输入框
            this.btnSearch(function() {
                var ownerCt = this.ownerCt;
                var hash = Ext.util.History.getToken();
                var data = Ext.Object.fromQueryString(hash);
                data.sort = data.sort || me.idProperty;
                data.order = data.order || 'DESC';
                data = me.getQueryData(ownerCt, data);

                me.store(me.setHistory(data)).loadPage(1);
            })]
        };
    },

    //放到最后定义，否则，jsduck后，上面的方法将属于Yab.store.Field或Yab.model.Field
    /**
     * 定义模型
     *
     * @author        mrmsl <msl-138@163.com>
     * @date          2012-08-01 15:29:15
     * @lastmodify    2013-01-14 11:18:45 by mrmsl
     *
     * @private
     *
     * @return {Object} this
     */
    defineModel: function() {
        /**
         * 表单域数据模型
         */
        Ext.define('Yab.model.Field', {
            extend: 'Ext.data.Model',
            /**
             * @cfg {Array}
             * 字段
             */
            fields: [this.idProperty, this.nameColumn, 'menu_id', 'input_name', 'menu_name', 'field_code', 'validate_rule', 'is_enable', 'sort_order', 'memo', 'customize_1'],
            /**
             * @cfg {String}
             * 主键
             */
            idProperty: this.idProperty
        });

        return this;
    },//end defineModel

    /**
     * 定义存储器
     *
     * @author        mrmsl <msl-138@163.com>
     * @date          2012-08-01 15:55:12
     * @lastmodify    2013-01-14 11:20:43 by mrmsl
     *
     * @private
     * @member Yab.controller.Field
     *
     * @return {Object} this
     */
    defineStore: function() {
        /**
         * 表单域数据容器
         */
        Ext.define('Yab.store.Field', {
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
            model: 'Yab.model.Field',
            /**
             * @cfg {Object}
             * proxy
             */
            proxy: {
                type: C.dataType,
                url: this.getActionUrl(false, 'list'),
                listeners: exception(),
                messageProperty: 'msg',
                simpleSortMode: true,
                reader: C.dataReader()
            },

            /**
             * @cfg {Object}
             * sorters
             */
            sorters: { //排序，以防点击列表头部排序时，多余传参，出现不必要的错误
                property : this.idProperty,
                direction: 'DESC'
            },
            constructor: function(config) {
                this.callParent([config || {}]);
            }
        });

        return this;
    }//end defineStore
});

//放到最后，以符合生成jsduck类说明
Ext.data.JsonP.Yab_controller_Field(Yab.controller.Field);