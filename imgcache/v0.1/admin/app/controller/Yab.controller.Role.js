/**
 * 模块设置控制器
 *
 * @file            app/controller/Yab.controller.Role.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-03-17 10:43:58
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.controller.Role', {
    extend: 'Yab.controller.Base',
    /**
     * @cfg {String}
     * 主键
     */
    idProperty: 'role_id',
    /**
     * @cfg {String}
     * 名称字段
     */
    nameColumn: 'role_name',//名称字段 by mrmsl on 2012-08-23 14:50:31

    /**
     * @inheritdoc Yab.controller.Base#listAction
     */
    listAction: function(data) {
        Yab.cmp.card.layout.setActiveItem(this.listgrid(data));
        global('app_contextmenu_refresh') && this.store().load();//标签页右键刷新 by mrmsl on 2012-08-15 09:10:17
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
            extField.fieldContainer(['MODULE_NAME_ROLE,NAME', [//角色名称
                [null, this.nameColumn, 'PLEASE_ENTER,MODULE_NAME_ROLE,NAME'],
                lang('LT_BYTE').format(30) + '，' + lang('CN_TO_BYTE')
            ]]), {
                xtype: 'treepicker',
                fieldLabel: lang('PERMISSION'),
                emptyText: lang('PLEASE_SELECT'),
                name: '_priv',
                multiSelect: true,
                bubbleCheck: true,
                displayField: 'menu_name',
                pickerIdProperty: 'menu_id',
                onCollapse: function() {
                    this.up('form').getForm().findField('_priv_id').setValue(this.getValue());
                },
                store: Ext.create('Yab.store.Tree', {
                    url: this.getActionUrl('menu', 'publicPriv', 'role_id=' + data[this.idProperty])
                })
            },
            extField.sortOrderField(),//排序
            extField.memoField(),//备注
            extField.textareaComment(lang('LT_BYTE').format(60)),//备注提示
            extField.hiddenField(),//role_id
            extField.hiddenField('_priv_id'),//权限菜单id串
            this.btnSubmit()//通用提交按钮 by mrmsl on 2012-08-28 09:34:08
        ];
    },

    /**
     * @inheritdoc Yab.controller.Admin#getListColumns
     */
    getListColumns: function() {
        var me = this;

        return [{
            text: lang('MODULE_NAME_ROLE') + 'id',//角色id
            width: 50,
            dataIndex: this.idProperty,
            sortable: false
        }, {
            header: lang('MODULE_NAME_ROLE,NAME'),//角色名
            width: 120,
            dataIndex: this.nameColumn,
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
            items: [{
                renderer: function(v, meta, record) {//管理员列表 by mrmsl on 2012-08-14 12:59:48
                    return '<a href="#' + me.getAction('admin', 'list&role_id=' + record.get(me.idProperty)) + '"><span class="appactioncolumn">' + lang('MODULE_NAME_ADMIN,LIST') + '</span></a>';
                }
            }, {
                renderer: function(v, meta, record) {//添加管理员 by mrmsl on 2012-08-21 13:50:18
                    return '<a href="#' + me.getAction('admin', 'add&role_id=' + record.get(me.idProperty)) + '"><span class="appactioncolumn">' + lang('ADD,MODULE_NAME_ADMIN') + '</span></a>';
                }
            }, {
                renderer: function(v, meta, record) {//编辑
                    return record.get(me.idProperty) == ADMIN_ROLE_ID && ADMIN_INFO.roleId != ADMIN_ROLE_ID ? '' : '<span class="appactioncolumn appactioncolumn-'+ this +'">' + lang('EDIT') + '</span>';
                },
                handler: function(grid, rowIndex, cellIndex) {
                    me.edit(grid.getStore().getAt(rowIndex));
                }
            }, {
                renderer: function(v, meta, record) {//删除
                    return record.get(me.idProperty) == ADMIN_ROLE_ID && ADMIN_INFO.roleId != ADMIN_ROLE_ID ? '' : '<span class="appactioncolumn appactioncolumn-'+ this +'">' + lang('DELETE') + '</span>';
                },
                handler: function(grid, rowIndex, cellIndex) {
                    var record = grid.getStore().getAt(rowIndex);
                    me['delete'](record, lang('DELETE_TIP_ROLE') + '<span class="font-bold font-red">' + htmlspecialchars(record.get(me.nameColumn)) + '</span>');
                }
            }]
        }];
    },//end getListColumns

   /**
     * @inheritdoc Yab.controller.Field#loadEditDataSuccess
     */
    loadEditDataSuccess: function(form, action) {
        var data = action.result.data;
        var field = form.findField('_priv');
        field.setRawValue(data.priv);//setValue => setRawValue by mrmsl on 2012-08-21 13:49:35
        field.selectValue = data._priv_id.split(',');//设置已选中值
    },

    /**
     * @inheritdoc Yab.controller.Admin#store
     */
    store: function() {

        if (!this._store) {//未创建
            this._store = Ext.create('Yab.store.Role', {
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
Ext.data.JsonP.Yab_controller_Role(Yab.controller.Role);