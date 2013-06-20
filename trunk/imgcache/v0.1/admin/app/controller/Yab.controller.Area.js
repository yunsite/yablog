/**
 * 国家地区控制器
 *
 * @file            app/controller/Yab.controller.Area.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-07-18 21:14:11
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.controller.Area', {
    extend: 'Yab.controller.Base',
    /**
     * @cfg {String}
     * 主键
     */
    idProperty: 'area_id',//主键 by mrmsl on 2012-08-06 21:41:28
    /**
     * @cfg {String}
     * 名称字段
     */
    nameColumn: 'area_name',//名称字段 by mrmsl on 2012-08-23 13:56:42
    /**
     * @cfg {String}
     * 查询字段
     */
    queryField: 'column,keyword,match_mode',//查询字段 by mrmsl on 2012-07-27 16:03:47

    /**
     * @inheritdoc Yab.controller.Base#addAction
     */
    addAction: function (data) {
        var me = this,
        options = {
            listeners: {
                submitsuccess: function (form) {
                    me.submitCallback(form);
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
            extField.fieldContainer(['CONTROLLER_NAME_AREA,NAME', [//地区名
                [null, this.nameColumn, 'CONTROLLER_NAME_AREA,NAME'],
                lang('LT_BYTE').format(50) + '，' + lang('CN_TO_BYTE')
            ]]),
            extField.fieldContainer(['AREA_CODE', [//地区简码
                [null, 'area_code'],
                lang('LT_BYTE').format(15)
            ]], false),
            extField.hiddenField(this.idProperty),//area_id
            extField.hiddenField('_node'),//节点关系id
            extField.hiddenField('parent_id'),//所属地区id
            {//所属地区
                xtype: 'treepicker',
                fieldLabel: TEXT.red() + lang('PARENT_AREA'),
                name: '_parent_name',
                singleSelectValueField: 'parent_id',
                emptyText: lang('TOP_LEVEL_AREA'),
                displayField: this.nameColumn,
                pickerIdProperty: this.idProperty,
                nodeField: '_node',
                store: Ext.create('Yab.store.Area', {
                    url: this.getActionUrl(false, 'publicArea', 'unshift=1&parent_id=' + data.parent_id)
                }),
                storeOnLoad: function(store) {//添加指定地区子级地区，设置指定地区相关信息 by mrmsl on 2012-08-21 13:29:49
                     var data = store.proxy.reader.rawData;

                    if (data && data.parent_data) {
                        data = data.parent_data;
                         this.up('form').getForm().setValues({
                             parent_id: data[me.idProperty],//父级id
                             _node: data.node//父级节点
                         }).findField('_parent_name').setRawValue(data[me.nameColumn]);//父级名称
                     }
                }
            },
            extField.sortOrderField(),
            extField.checkbox('is_show', Ext.valueFrom(data.is_show, 1), 'SHOW'),//是否显示
            this.btnSubmit()
        ]
    },//end formField

    /**
     * @inheritdoc Yab.controller.Admin#getListColumns
     */
    getListColumns: function() {
        var me = this;

        return [{
            xtype: 'treecolumn',
            header: lang('CONTROLLER_NAME_AREA'),//地区名
            flex: 1,
            dataIndex: this.nameColumn,
            renderer: function(v) {
                return me.searchReplaceRenderer(v, me.nameColumn);
            },
            sortable: false
        }, {
            text: lang('CONTROLLER_NAME_AREA') + 'id',//地区id
            width: 80,
            dataIndex: this.idProperty,
            sortable: false
        }, {
            header: lang('AREA_CODE'),//地区代码
            width: 120,
            dataIndex: 'area_code',
            renderer: function(v) {
                return me.searchReplaceRenderer(v, 'area_code');
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
            width: 200,
            xtype: 'appactioncolumn',
            items: [{
                text: lang('ADD,CHILD,CONTROLLER_NAME_AREA'),//添加子地区 by mrmsl on 2012-08-21 13:29:14
                handler: function(grid, rowIndex, cellIndex) {
                    var record = grid.getStore().getAt(rowIndex);
                    me.edit(record, false, '{0}=0&parent_id={1}'.format(me.idProperty, record.get(me.idProperty)));
                }
            },
            this.editColumnItem(true),//编辑
            this.deleteColumnItem(this.nameColumn)//删除
            ]
        }];
    },//end getListColumns

    /**
     * @inheritdoc Yab.controller.Base#listAction
     */
    listAction: function(data) {
        var me = this;
        data.keyword = data.keyword || '';
        data.column = data.column || this.nameColumn;
        data.match_mode = data.match_mode || 'eq';//匹配模式 by mrmsl on 2012-07-28 16:59:42

        var options = {
            xtype: 'treepanel',
            onItemClick: function(view, record, element, index, event) {//列表点击事件
                me.listitemclick(record, event);
            }
        };

        this.callParent([data, options]);//通用列表 by mrmsl on 2012-08-02 13:42:30
    },

    /**
     * @inheritdoc Yab.controller.Field#loadEditDataSuccess
     */
    loadEditDataSuccess: function(form, action) {
        form.findField('_parent_name').setRawValue(action.result.data.parent_name);//setValue => setRawValue by mrmsl on 2012-08-21 13:28:19
        form.findField('_node').setValue(action.result.data.node);
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
            this._store = Ext.create('Yab.store.Area', {
                url: url
            });

            this._store.on('load', function(store, node, childNodes) {
                node.get('checked') && Ext.each(childNodes, function(node) {
                    node.set('checked', true);
                });
            });

            this.setTreeStoreOnLoad(this._store);//设置总数信息 by mrmsl on 2012-08-15 13:22:11
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
                }]//end button area
            }, '-', {
                xtype: 'combobox',//搜索字段 by mrmsl on 2012-07-26 13:34:36
                width: 70,
                itemId: 'column',
                store: [
                    [this.nameColumn, lang('CONTROLLER_NAME_AREA,NAME')],
                    ['area_code', lang('AREA_CODE')]
                ],
                value: data.column,
                editable: false
            },
            Yab.Field.combo().matchMode(),//匹配模式 by mrmsl on 2012-07-28 17:59:23
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
Ext.data.JsonP.Yab_controller_Area(Yab.controller.Area);