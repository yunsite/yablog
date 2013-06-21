/**
 * 语言包模块控制器
 *
 * @file            app/controller/Yab.controller.LanguageModules.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-06-20 11:10:33
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.controller.LanguageModules', {
    extend: 'Yab.controller.Base',
    /**
     * @cfg {String}
     * 主键
     */
    idProperty: 'module_id',
    /**
     * @cfg {String}
     * 名称字段
     */
    nameColumn: 'module_name',//名称字段

    /**
     * 获取表单域
     *
     * @author       mrmsl <msl-138@163.com>
     * @date         2013-06-20 11:12:06
     *
     * @param {Object} data 数据
     *
     * @return {Array} 表单域
     */
    formField: function(data) {
        var me = this, extField = Yab.Field.field();

        return [
            extField.fieldContainer(['LANGUAGE_MODULE_NAME', [//名称
                [null, this.nameColumn, 'PLEASE_ENTER,LANGUAGE_MODULE_NAME'],
                lang('LT_BYTE').format(20) + '，' + lang('CN_TO_BYTE') + lang('%。,TO_EDIT_TIP')
            ]]),
            extField.hiddenField(),//module_id
            extField.hiddenField('parent_id'),//父级菜单id
            {
                xtype: 'treepicker',
                fieldLabel: TEXT.red() + lang('PARENT_LANGUAGEMODULES'),
                name: '_parent_name',
                value: data.parent_id,
                singleSelectValueField: 'parent_id',
                emptyText: lang('PARENT_LANGUAGEMODULES'),
                displayField: this.nameColumn,
                pickerIdProperty: this.idProperty,
                store: Ext.create('Yab.store.LanguageModules', {
                    folderSort: false,
                    url: this.getActionUrl(false, 'list', 'unshift&combo&add&module_id={0}&parent_id={1}'.format(data[this.idProperty], data.parent_id))
                }),
                storeOnLoad: function(store) {//添加指定菜单子菜单，设置指定菜单相关信息
                    var data = store.proxy.reader.rawData;

                    if (data && data.parent_data) {
                        data = data.parent_data;
                         var form = this.up('form').getForm();
                         form.setValues({
                             parent_id: data[me.idProperty],//父级id
                         });
                         me.loadEditDataSuccess(form, {//其它信息，包括父级名称，及初始化权限
                             result: {
                                 data: data
                             }
                         });
                     }
                }
            },
            extField.sortOrderField(),//排序
            extField.memoField(),//备注
            extField.textareaComment(lang('LT_BYTE').format(60)),//备注提示
            this.btnSubmit()//通用提交按钮
        ];
    },//end formField

    /**
     * @inheritdoc Yab.controller.Admin#getListColumns
     */
    getListColumns: function() {
        var me = this;

        return [{
            xtype: 'treecolumn',
            header: lang('LANGUAGE_MODULE_NAME'),//名称
            flex: 2,
            dataIndex: this.nameColumn,
            sortable: false
        }, {
            text: lang('MODULE') + 'id',//菜单id
            width: 50,
            dataIndex: this.idProperty,
            sortable: false
        }, {
            header: lang('ORDER'),//排序
            dataIndex: 'sort_order',
            width: 50,
            align: 'center',
            sortable: false
        }, {
            header: lang('MEMO'),//备注
            dataIndex: 'memo',
            width: 200,
            sortable: false
        }, {//操作列
            flex: 1,
            xtype: 'appactioncolumn',
            items: [{
                text: lang('ADD,CHILD,MODULE'),//添加子菜单
                handler: function(grid, rowIndex, cellIndex) {
                    var record = grid.getStore().getAt(rowIndex);
                    me.edit(record, false, '{0}=0&parent_id={1}'.format(me.idProperty, record.get(me.idProperty)));
                }
            }, {
                text: lang('ADD,LANGUAGE_ITEM'),//添加语言项 by mrmsl on
                handler: function(grid, rowIndex, cellIndex) {
                    var record = grid.getStore().getAt(rowIndex);
                    me.edit(record, false, me.getAction('LanguageItems', 'add') + '&{0}=0&parent_id={1}'.format(me.idProperty, record.get(me.idProperty)));
                }
            },
            this.editColumnItem(true),//编辑
            this.deleteColumnItem(this.nameColumn)//操作
            ]
        }];
    },//end getListColumns

    /**
     * @inheritdoc Yab.controller.Base#listAction
     */
    listAction: function(data) {
        this.callParent([data, { xtype: 'treepanel' }]);
    },

    /**
     * @inheritdoc Yab.controller.Field#loadEditDataSuccess
     */
    loadEditDataSuccess: function(form, action) {
        var data = action.result.data;
        form.findField('_parent_name').setRawValue(data.parent_name);
    },

    /**
     * @inheritdoc Yab.controller.Admin#pagingBar
     */
    pagingBar: function() {
        return this.treegridbbar();
    },

    /**
     * @inheritdoc Yab.controller.Admin#store
     */
    store: function() {

        if (!this._store) {//未创建
            this._store = Ext.create('Yab.store.LanguageModules');
            this.setTreeStoreOnLoad(this._store);//设置总数信息

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
            items: this.deleteItem()
        }
    }//end tbar
});

//放到最后，以符合生成jsduck类说明
Ext.data.JsonP.Yab_controller_LanguageModules(['Yab.store.LanguageModules', Yab.controller.LanguageModules]);