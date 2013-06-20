/**
 * 菜单控制器
 *
 * @file            app/controller/Yab.controller.Menu.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-03-17 10:43:58
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.controller.Menu', {
    extend: 'Yab.controller.Base',
    /**
     * @cfg {String}
     * 主键
     */
    idProperty: 'menu_id',
    /**
     * @cfg {String}
     * 名称字段
     */
    nameColumn: 'menu_name',//名称字段 by mrmsl on 2012-08-23 14:07:39
    /**
     * @cfg {String}
     * 查询字段
     */
    queryField: 'column,keyword,match_mode,is_show',//查询字段 by mrmsl on 2012-08-15 11:44:54

    /**
     * 获取表单域
     *
     * @author       mrmsl <msl-138@163.com>
     * @date         2012-08-08 11:50:02
     * @lastmodify  2012-09-25 09:06:29 by mrmsl
     *
     * @param {Object} data 数据
     *
     * @return {Array} 表单域
     */
    formField: function(data) {
        var me = this, extField = Yab.Field.field();

        return [
            extField.fieldContainer(['CONTROLLER_NAME_MENU,NAME', [//菜单名称
                [null, this.nameColumn, 'PLEASE_ENTER,CONTROLLER_NAME_MENU,NAME'],
                lang('LT_BYTE').format(30) + '，' + lang('CN_TO_BYTE') + lang('%。,TO_EDIT_TIP')
            ]]),
            extField.fieldContainer(['CONTROLLER', [//控制器
                [null, 'controller', 'PLEASE_ENTER,CONTROLLER'],
                lang('LT_BYTE').format(20) + lang('%。,TO_EDIT_TIP')
            ]]),
            extField.fieldContainer(['ACTION', [//操作方法
                [null, 'action', 'PLEASE_ENTER,ACTION'],
                lang('LT_BYTE').format(20) + lang('%。,TO_EDIT_TIP')
            ]]),
            extField.hiddenField(),//menu_id
            extField.hiddenField('_priv_id'),//权限角色id串
            extField.hiddenField('parent_id'),//父级菜单id
            {
                xtype: 'treepicker',
                fieldLabel: TEXT.red() + lang('PARENT_MENU'),
                name: '_parent_name',
                value: data.parent_id,
                singleSelectValueField: 'parent_id',
                emptyText: lang('TOP_LEVEL_MENU'),
                displayField: this.nameColumn,
                pickerIdProperty: this.idProperty,
                store: Ext.create('Yab.store.Tree', {
                    folderSort: false,
                    url: this.getActionUrl(false, 'publicTree', 'unshift&menu_id={0}&parent_id={1}'.format(data[this.idProperty], data.parent_id))
                }),
                storeOnLoad: function(store) {//添加指定菜单子菜单，设置指定菜单相关信息 by mrmsl on 2012-08-21 13:44:41
                    var data = store.proxy.reader.rawData;

                    if (data && data.parent_data) {
                        data = data.parent_data;
                         var form = this.up('form').getForm();
                         form.setValues({
                             parent_id: data[me.idProperty],//父级id
                             _priv_id: data._priv_id,//权限id
                             controller: data.controller//控制器
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
            extField.checkbox('is_show', Ext.valueFrom(data.is_show, 1), 'SHOW'),//是否显示
            {
                xtype: 'treepicker',
                fieldLabel: lang('PERMISSION'),
                emptyText: lang('PLEASE_SELECT'),
                multiSelect: true,
                name: '_priv',
                onCollapse: function() {
                    this.up('form').getForm().findField('_priv_id').setValue(this.getValue());
                },
                pickerDockedItems: [{//全选,全不选 by mrmsl on 2012-07-16 22:12:52
                    xtype: 'toolbar',
                    dock: 'top',
                    items: {
                        style: 'font-weight: bold; margin-left: 14px;',
                        xtype: 'checkbox',
                        boxLabel: lang('ALL,SELECT'),
                        handler: function(cb, checked) {
                            cb.up('treepanel').getRootNode().cascadeBy(function(record) {
                                !record.isRoot() && record.set('checked', checked);
                            });

                            Ext.getCmp('menuadd').getForm().findField('_priv').pickerCheckedValue().setValue();
                        }
                    }
                }],
                store: Ext.create('Yab.store.Tree', {
                    url: this.getActionUrl('role', 'publicPriv', 'menu_id=' + (data[this.idProperty] ? data[this.idProperty] : data.parent_id))
                })
            },
            extField.memoField(),//备注
            extField.textareaComment(lang('LT_BYTE').format(60)),//备注提示
            this.btnSubmit()//通用提交按钮 by mrmsl on 2012-08-28 10:21:52
        ];
    },//end formField

    /**
     * @inheritdoc Yab.controller.Admin#getListColumns
     */
    getListColumns: function() {
        var me = this;

        return [{
            xtype: 'treecolumn',
            header: lang('CONTROLLER_NAME_MENU'),//菜单名
            flex: 2,
            dataIndex: this.nameColumn,
            renderer: function(v) {
                return me.searchReplaceRenderer(v, me.nameColumn);
            },
            sortable: false
        }, {
            text: lang('CONTROLLER_NAME_MENU') + 'id',//菜单id
            width: 50,
            dataIndex: this.idProperty,
            sortable: false
        }, {
            header: lang('CONTROLLER'),//控制器
            width: 120,
            dataIndex: 'controller',
            renderer: function(v) {
                return me.searchReplaceRenderer(v, 'model');
            },
            sortable: false
        }, {
            header: lang('ACTION'),//操作方法
            dataIndex: 'action',
            width: 120,
            renderer: function(v) {
                return me.searchReplaceRenderer(v, 'view');
            },
            sortable: false
        }, {
            header: lang('ORDER'),//排序
            dataIndex: 'sort_order',
            width: 50,
            align: 'center',
            sortable: false
        }, {
            header: lang('SHOW'),//显示
            align: 'center',
            dataIndex: 'is_show',
            width: 50,
            renderer: function(v) {
                return me.renderYesNoImg(v, 'is_show');
            },
            sortable: false
        }, {//操作列
            flex: 1,
            xtype: 'appactioncolumn',
            items: [{
                text: lang('ADD,CHILD,CONTROLLER_NAME_MENU'),//添加子菜单
                handler: function(grid, rowIndex, cellIndex) {
                    var record = grid.getStore().getAt(rowIndex);
                    me.edit(record, false, '{0}=0&parent_id={1}'.format(me.idProperty, record.get(me.idProperty)));
                }
            }, {
                text: lang('ADD,CONTROLLER_NAME_FIELD'),//添加表彰域 by mrmsl on
                handler: function(grid, rowIndex, cellIndex) {
                    var record = grid.getStore().getAt(rowIndex);
                    me.edit(record, false, me.getAction('field', 'add') + '&{0}=0&parent_id={1}'.format(me.idProperty, record.get(me.idProperty)));
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
        data.keyword = data.keyword || '';
        data.column = data.column || this.nameColumn;
        data.match_mode = data.match_mode || 'eq';//匹配模式
        data.is_show = Ext.valueFrom(data.is_show, '-1');//是否显示 by mrmsl on 2012-09-15 12:20:18

        var me = this, options = {
            xtype: 'treepanel',
            onItemClick: function(view, record, element, index, event) {//列表点击事件
                me.listitemclick(record, event);
            }
        };

        this.callParent([data, options]);
    },

    /**
     * @inheritdoc Yab.controller.Field#loadEditDataSuccess
     */
    loadEditDataSuccess: function(form, action) {
        var data = action.result.data;
        form.findField('_parent_name').setRawValue(data.parent_name);
        var field = form.findField('_priv');
        field.setRawValue(data.priv);//setValue => setRawValue by mrmsl on 2012-08-21 13:48:14
        field.selectValue = data._priv_id.split(',');//设置已选中值
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
    store: function(data) {
        var queryData = data ? this.httpBuildQuery(data, this.queryField) : '';
        var url = this.getActionUrl(false, 'list', queryData);

        if (!this._store) {//未创建
            this._store = Ext.create('Yab.store.Tree', {
                url: url
            });

            this._store.on('load', function(store, node, childNodes) {
                node.get('checked') && Ext.each(childNodes, function(node) {
                    node.set('checked', true);
                });
            });

            this.setTreeStoreOnLoad(this._store);//设置总数信息 by mrmsl on 2012-08-13 14:12:06

        }

        if (data) {
            this._store._data = queryData;
            this._store.proxy.url = url;
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
                    text: lang('SHOW'),
                    handler: function() {
                        var selection = me.hasSelect(me._listgrid, ['is_show', 0]);
                        selection.length && me.setOneOrZero(selection[0], 1, 'is_show', lang('YOU_CONFIRM,SHOW,SELECTED,RECORD'), selection[1]);
                    }
                }, {
                    text: lang('HIDE'),
                    handler: function() {
                        var selection = me.hasSelect(me._listgrid, ['is_show', 1]);
                        selection.length && me.setOneOrZero(selection[0], 0, 'is_show', lang('YOU_CONFIRM,HIDE,SELECTED,RECORD'), selection[1]);
                    }
                }]//end button menu
            }, '-', Yab.Field.combo().show(),//显示状态 by mrmsl on 2012-09-15 12:06:44
            {
                xtype: 'combobox',//搜索字段
                width: 70,
                itemId: 'column',
                store: [
                    [this.nameColumn, lang('CONTROLLER_NAME_MENU,NAME')],
                    ['model', lang('CONTROLLER')],
                    ['view', lang('ACTION')]
                ],
                value: data.column,
                editable: false
            }, Yab.Field.combo().matchMode(),//匹配模式 by mrmsl on 2012-07-28 17:59:23
            Yab.Field.field().keywordField(data.keyword),//关键字输入框
            this.btnSearch(function() {
                var ownerCt = this.ownerCt;
                var hash = Ext.util.History.getToken();
                var data = Ext.Object.fromQueryString(hash);
                data = me.getQueryData(ownerCt, data);
                me.store(me.setHistory(data)).load();
            }), '->', {
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
Ext.data.JsonP.Yab_controller_Menu(Yab.controller.Menu);