/**
 * 博客分类控制器
 *
 * @file            app/controller/Yab.controller.Category.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-03-21 12:45:18
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.controller.Category', {
    extend: 'Yab.controller.Base',
    /**
     * @cfg {String}
     * 主键
     */
    idProperty: 'cate_id',
    /**
     * @cfg {String}
     * 名称字段
     */
    nameColumn: 'cate_name',//名称字段
    /**
     * @cfg {String}
     * 查询字段
     */
    queryField: 'column,keyword,match_mode,is_show',//查询字段

    /**
     * 清除静态页缓存
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-17 13:36:21
     *
     * @protected
     *
     * @param {Mixed}  record      record数据或id串
     * @param {String} confirmText 确认信息
     *
     * @return {void} 无返回值
     */
    clearCache: function(record, confirmText) {
        var pkValue;
        var controller = this.getControllerName();

        if (Ext.isString(record)) {//选中删除
            pkValue = record;
            confirmText = lang('SELECTED,RECORD');
        }
        else {//点击删除
            pkValue = record.get(this.idProperty);
        }

        var options = {
            action: this.getActionUrl(false, 'clearCache'),
            data: this.idProperty + '=' + pkValue,
            confirmText: lang('YOU_CONFIRM,CLEAR') + confirmText + lang('STATIC_PAGE,CACHE'),
            failedMsg: lang('CLEAR,FAILURE'),
            scope: this,
            callback: function () {
                //log(this._listgrid.getSelectionModel().deselectAll(), this._listgrid.getSelectionModel().store);
            }
        };

        this.myConfirm(options);
    },//end clearCache

    /**
     * 获取表单域
     *
     * @author       mrmsl <msl-138@163.com>
     * @date         2013-03-21 16:17:09
     *
     * @param {Object} data 数据
     *
     * @return {Array} 表单域
     */
    formField: function(data) {
        var me = this, extField = Yab.Field.field();

        return [
            extField.fieldContainer(['MODULE_NAME_CATEGORY,NAME', [//分类名称
                [null, this.nameColumn, 'PLEASE_ENTER,MODULE_NAME_CATEGORY,NAME'],
                lang('LT_BYTE').format(30) + '，' + lang('CN_TO_BYTE') + lang('%。,TO_EDIT_TIP')
            ]]),
            extField.fieldContainer(['CATEGORY_EN_NAME', [//url英文名
                [null, 'en_name', 'PLEASE_ENTER,CATEGORY_EN_NAME'],
                lang('LT_BYTE').format(15) + lang('%。,TO_EDIT_TIP')
            ]]),
            extField.hiddenField(),//cate_id
            extField.hiddenField('parent_id'),//父级分类id
            {
                xtype: 'treepicker',
                fieldLabel: TEXT.red() + lang('PARENT_CATEGORY'),
                name: '_parent_name',
                value: data.parent_id,
                singleSelectValueField: 'parent_id',
                emptyText: lang('TOP_LEVEL_CATEGORY'),
                displayField: this.nameColumn,
                pickerIdProperty: this.idProperty,
                store: Ext.create('Yab.store.Category', {
                    folderSort: false,
                    url: this.getActionUrl(false, 'publicCategory', 'unshift&cate_id={0}&parent_id={1}'.format(data[this.idProperty], data.parent_id))
                }),
                storeOnLoad: function(store) {//添加指定分类子分类，设置指定分类相关信息
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
            extField.checkbox('is_show', Ext.valueFrom(data.is_show, 1), 'SHOW'),//是否显示,
            extField.textarea('seo_keyword', 'PLEASE_ENTER,SEO_KEYWORD', 'SEO_KEYWORD', '', {width: 800, height: 50, minLength: 6, maxLength: 300}),//SEO关键字
            extField.textareaComment(lang('BETWEEN_BYTE').format(6, 180)),//SEO关键字提示
            extField.textarea('seo_description', 'PLEASE_ENTER,SEO_DESCRIPTION', 'SEO_DESCRIPTION', '', {width: 800, height: 70, minLength: 6, maxLength: 300}),//SEO描述
            extField.textareaComment(lang('BETWEEN_BYTE').format(6, 300)),//SEO描述提示
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
            header: lang('MODULE_NAME_CATEGORY'),//分类名
            flex: 3,
            dataIndex: this.nameColumn,
            renderer: function(v) {
                return me.searchReplaceRenderer(v, me.nameColumn);
            },
            sortable: false
        }, {
            text: lang('MODULE_NAME_CATEGORY') + 'id',//分类id
            width: 50,
            dataIndex: this.idProperty,
            sortable: false
        }, {
            header: lang('CATEGORY_EN_NAME'),//url英文名
            width: 80,
            dataIndex: 'en_name',
            renderer: function(v) {
                return me.searchReplaceRenderer(v, 'en_name');
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
            flex: 2,
            xtype: 'appactioncolumn',
            items: [{
                text: lang('ADD,CHILD,MODULE_NAME_CATEGORY'),//添加子分类
                handler: function(grid, rowIndex, cellIndex) {
                    var record = grid.getStore().getAt(rowIndex);
                    me.edit(record, false, '{0}=0&parent_id={1}'.format(me.idProperty, record.get(me.idProperty)));
                }
            }, {
                text: lang('ADD,MODULE_NAME_BLOG'),//添加博客 by mrmsl on
                handler: function(grid, rowIndex, cellIndex) {
                    var record = grid.getStore().getAt(rowIndex);
                    me.edit(record, false, me.getAction('blog', 'add') + '&{0}=0&cate_id={1}'.format(me.idProperty, record.get(me.idProperty)));
                }
            },
            this.editColumnItem(true),//编辑
            this.deleteColumnItem(this.nameColumn),//操作
            {//清除静态页缓存 by mrmsl on 2013-05-17 13:35:07
                text: lang('CLEAR,STATIC_PAGE,CACHE'),
                handler: function(grid, rowIndex, cellIndex) {
                    var record = grid.getStore().getAt(rowIndex);
                    me.clearCache(record, '<span class="font-red font-bold">' + record.get(me.nameColumn) + '</span>');
                }
            }]
        }];
    },//end getListColumns

    /**
     * @inheritdoc Yab.controller.Base#listAction
     */
    listAction: function(data) {
        data.keyword = data.keyword || '';
        data.column = data.column || this.nameColumn;
        data.match_mode = data.match_mode || 'eq';//匹配模式
        data.is_show = Ext.valueFrom(data.is_show, '-1');//是否显示

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
            this._store = Ext.create('Yab.store.Category', {
                url: url
            });

            this._store.on('load', function(store, node, childNodes) {
                node.get('checked') && Ext.each(childNodes, function(node) {
                    node.set('checked', true);
                });
            });

            this.setTreeStoreOnLoad(this._store);//设置总数信息

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
                }, {//清除静态页缓存 by mrmsl on 2013-05-17 13:43:57
                    text: lang('CLEAR,STATIC_PAGE,CACHE'),
                    handler: function() {
                        var selection = me.hasSelect(me._listgrid);
                        selection.length && me.clearCache(selection);
                    }
                }]//end button cate
            }, '-', Yab.Field.combo().show(),//显示状态
            {
                xtype: 'combobox',//搜索字段
                width: 70,
                itemId: 'column',
                store: [
                    [this.nameColumn, lang('MODULE_NAME_CATEGORY,NAME')],
                    ['en_name', lang('CATEGORY_EN_NAME')],
                    ['seo_keyword', lang('SEO_KEYWORD')],
                    ['SEO_DESCRIPTION', lang('SEO_DESCRIPTION')]
                ],
                value: data.column,
                editable: false
            }, Yab.Field.combo().matchMode(),//匹配模式
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
Ext.data.JsonP.Yab_controller_Category(Yab.controller.Category);