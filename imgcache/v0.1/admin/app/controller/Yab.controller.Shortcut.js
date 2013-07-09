/**
 * 快捷方式控制器
 *
 * @file            app/controller/Yab.controller.Shortcut.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-03-17 10:43:58
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.controller.Shortcut', {
    extend: 'Yab.controller.Base',
    /**
     * @cfg {String}
     * 主键
     */
    idProperty: 'short_id',

    /**
     * @inheritdoc Yab.controller.Base#listAction
     */
    listAction: function(data) {
        Yab.cmp.card.layout.setActiveItem(this.listgrid(data));
        global('app_contextmenu_refresh') && this.store().load();//标签页右键刷新
        return this;
    },

    /**
     * @inheritdoc Yab.controller.Base#addAction
     */
    addAction: function (data) {
        var me = this,
        options = {
            listeners: {
                submitsuccess: function () {
                    me._listgrid && me._store.load();
                }
            }
        };

        this.callParent([data, options]);
    },

    /**
     * @inheritdoc Yab.controller.Admin#formField
     */
    formField: function(data) {
        var me = this, extField = Yab.Field.field();

        return [
            extField.hiddenField('menu_id'),//所属菜单id
            {
                xtype: 'treepicker',
                fieldLabel: TEXT.red() + lang('BELONG_TO,MENU'),
                name: '_menu_name',
                value: data.menu_id,
                emptyText: lang('PLEASE_SELECT'),
                displayField: 'menu_name',
                pickerIdProperty: 'menu_id',
                store: Ext.create('Yab.store.Tree', {
                    folderSort: false,
                    url: this.getActionUrl('menu', 'publicTree', 'menu_id={0}&parent_id={1}'.format(data['menu_id'], data.parent_id))
                }),
                storeOnLoad: function(store) {//添加指定菜单子菜单，设置指定菜单相关信息 by mrmsl on 2012-08-21 13:44:41
                    var data = store.proxy.reader.rawData;

                    if (data && data.parent_data) {
                        data = data.parent_data;
                         var form = this.up('form').getForm();
                         me.loadEditDataSuccess(form, {
                             result: {
                                 data: data
                             }
                         });
                     }
                }
            },
            extField.fieldContainer(['ADDITIONAL_PARAM', [//附加参数
                [null, 'additional_param', '', '', undefined, {width: 300}],
                lang('LT_BYTE').format(100)
            ], true]),
            extField.sortOrderField(),//排序
            extField.memoField(),//备注
            extField.textareaComment(lang('LT_BYTE').format(60)),//备注提示
            extField.hiddenField(),//short_id
            this.btnSubmit()//通用提交按钮
        ];
    },

    /**
     * @inheritdoc Yab.controller.Admin#getListColumns
     */
    getListColumns: function() {
        var me = this;

        return [{
            text: lang('SHORTCUT') + 'id',//角色id
            width: 100,
            dataIndex: this.idProperty,
            sortable: false
        }, {
            header: lang('BELONG_TO,MENU'),//所属菜单
            width: 120,
            dataIndex: 'menu_name',
            sortable: false,
            renderer: function (v, cls, record) {
                return '<a class="link" href="{0}">{1}</a>'.format(record.get('href'), v);
            }
        }, {
            header: lang('ADDITIONAL_PARAM'),//附加参数
            dataIndex: 'additional_param',
            width: 200,
            align: 'center',
            sortable: false
        }, {
            header: lang('ORDER'),//排序
            dataIndex: 'sort_order',
            width: 50,
            align: 'center',
            sortable: false
        }, {
            header: lang('MEMO'),//备注
            flex: 1,
            dataIndex: 'memo',
            sortable: false
        }, {//操作列
            width: 220,
            xtype: 'appactioncolumn',
            items: [{//编辑
                text: lang('EDIT'),
                handler: function(grid, rowIndex, cellIndex) {
                    me.edit(grid.getStore().getAt(rowIndex));
                }
            }, me.deleteColumnItem()]
        }];
    },//end getListColumns

   /**
     * @inheritdoc Yab.controller.Field#loadEditDataSuccess
     */
    loadEditDataSuccess: function(form, action) {
        var data = action.result.data;
        var field = form.findField('_menu_name');
        field.setRawValue(data.menu_name);
    },

    /**
     * @inheritdoc Yab.controller.Admin#store
     */
    store: function() {

        if (!this._store) {//未创建
            this._store = Ext.create('Yab.store.Shortcut', {
                autoLoad: true
            });
        }

        return this._store;
    },//end store

    /**
     * @inheritdoc Yab.controller.Admin#tbar
     */
    tbar: function() {
        var me = this;

        return {
            xtype: 'toolbar',
            dock: 'top',
            items: [this.deleteItem(), '->', {
                xtype: 'tool',
                tooltip: lang('REFRESH'),
                type: 'refresh',
                handler: function() {
                    me._listgrid.getStore().load();
                }
            }]
        }
    }//end tbar
});

//放到最后，以符合生成jsduck类说明
Ext.data.JsonP.Yab_controller_Shortcut(Yab.controller.Shortcut);