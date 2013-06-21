/**
 * 语言项控制器
 *
 * @file            app/controller/Yab.controller.LanguageItems.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-06-21 14:56:57
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.controller.LanguageItems', {
    extend: 'Yab.controller.Base',
    /**
     * @cfg {String}
     * 主键
     */
    idProperty: 'item_id',//主键
    /**
     * @cfg {String}
     * 名称字段
     */
    nameColumn: 'var_name',//名称字段
    /**
     * @cfg {String}
     * 查询字段
     */
    queryField: 'sort,order,column,keyword,module_id,page,match_mode',//查询字段

    constructor: function() {//构造函数
        this.defineModel().defineStore();
    },

    /**
     * @inheritdoc Yab.controller.Base#listAction
     */
    listAction: function(data) {
        data.sort = data.sort || this.idProperty;//排序字段
        data.order = data.order || 'ASC';//排序
        data.keyword = data.keyword || '';
        data.module_id = data.module_id || '';
        data.column = data.column || this.nameColumn;
        data.match_mode = data.match_mode || 'eq';//匹配模式
        data.page = intval(data.page) || 1;//页

        this.callParent([data]);//通用列表
    },

    /**
     * @inheritdoc Yab.controller.Admin#formField
     */
    formField: function(data) {
        var me = this, extField = Yab.Field.field();
        global('app_labelWidth', 150);
        var item = [extField.hiddenField(),//item_id
        extField.itemContainer(['LANGUAGE_ITEM_VAR_NAME', [//变量名
            [null, this.nameColumn, 'PLEASE_ENTER,LANGUAGE_ITEM_VAR_NAME', '', undefined, {width: 250}],
            lang('LT_BYTE').format(50) + '，' + lang('CN_TO_BYTE')
        ]]),
        {
            allowBlank: false,
            blankText: lang('PLEASE_SELECT,LANGUAGE_MODULE'),
            xtype: 'treepicker',
            itemLabel: TEXT.red() + lang('LANGUAGE_MODULE'),
            labelWidth: global('app_labelWidth'),
            width: 400,
            name: '_module_name',
            singleSelectValueField: 'module_id',
            emptyText: lang('PLEASE_SELECT'),
            displayField: 'module_name',
            pickerIdProperty: 'module_id',
            store: Ext.create('Yab.store.LanguageModules', {
                folderSort: false,
                url: this.getActionUrl('languagemodules', 'list', 'combo&parent_id=' + data.parent_id)
            }),
            storeOnLoad: function(store) {//添加指定菜单表单域，设置指定菜单相关信息
                 var data = store.proxy.reader.rawData;

                 if (data && data.parent_data) {
                    data = data.parent_data
                    this.up('form').getForm().findField('module_id').setValue(data.module_id);//父级id
                    this.setRawValue(data.parent_name);
                 }
            }
        },
        extField.textarea('var_value_zh_cn', 'PLEASE_ENTER,VAR_VALUE_ZH_CN', 'VAR_VALUE_ZH_CN', '', {
            width: 900,
            height: 100
        }),
        extField.textarea('var_value_en', 'PLEASE_ENTER,VAR_VALUE_EN', 'VAR_VALUE_EN', '', {
            width: 900,
            height: 100
        }),
        extField.hiddenField('module_id'),
        extField.checkbox('to_js', Ext.valueFrom(data.to_js, 0), 'TO_JS'),//生成js
        extField.sortOrderField(),
        extField.memoField({width: 500}),
        extField.textareaComment(lang('LT_BYTE').format(60), null),
        this.btnSubmit()//通用提交按钮
        ];

        return item;
    },//end formField

    /**
     * @inheritdoc Yab.controller.Admin#getListColumns
     */
    getListColumns: function() {
        var me = this;

        return [{
            text: lang('LANGUAGE_ITEM') + 'id',//表单域id
            width: 70,
            sortable: true,
            dataIndex: this.idProperty
        }, {
            header: lang('LANGUAGE_ITEM_VAR_NAME'),//变量名
            width: 150,
            dataIndex: this.nameColumn,
            renderer: function(v) {
                return me.searchReplaceRenderer(v, me.nameColumn);
            },
            sortable: false
        }, {
            header: lang('LANGUAGE_MODULE'),//模块
            width: 120,
            dataIndex: 'module_name',
            sortable: false
        }, {
            header: lang('VAR_VALUE_ZH_CN'),//中文值
            width: 150,
            dataIndex: 'var_value_zh_cn',
            renderer: function(v) {
                return me.searchReplaceRenderer(v, 'var_value_zh_cn');
            }
        }, {
            header: lang('VAR_VALUE_EN'),//英文值
            width: 150,
            dataIndex: 'var_value_en',
            renderer: function(v) {
                return me.searchReplaceRenderer(v, 'var_value_en');
            }
        }, {
            header: lang('ORDER'),//排序
            dataIndex: 'sort_order',
            width: 50,
            align: 'center',
            sortable: false
        }, {//操作列
            width: 120,
            xtype: 'appactioncolumn',
            items: [
                this.editColumnItem(true),//编辑
                this.deleteColumnItem(this.nameColumn)//删除
            ]
        }];
    },//end getListColumns

    /**
     * @inheritdoc Yab.controller.Base#loadEditDataSuccess
     */
    loadEditDataSuccess: function(form, action) {
        form.findField('_module_name').setRawValue(action.result.data.module_name);
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
        this._store = this._store || Ext.create('Yab.store.LanguageItems');

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
        var me = this, extField = Yab.Field.field();

        return {
            xtype: 'toolbar',
            dock: 'top',
            items: [this.deleteItem(),//end button item
            '-',
            extField.hiddenField('module_id', {itemId: 'module_id', value: data.module_id}),
            {
                xtype: 'treepicker',
                name: '_module_id',
                value: data.module_id,
                emptyText: lang('LANGUAGE_MODULE'),
                displayField: 'module_name',
                pickerIdProperty: 'module_id',
                width: 200,
                store: Ext.create('Yab.store.LanguageModules', {
                    folderSort: false,
                    url: this.getActionUrl('languagemodules', 'list', 'unshift&combo&parent_id={0}&emptyText={1}'.format(data.module_id, encodeURIComponent(lang('LANGUAGE_MODULE'))))
                }),
                storeOnLoad: function(store) {//设置所属分类文字
                     var data = store.proxy.reader.rawData;
                     if (data && data.parent_data) {
                         this.setRawValue(data.parent_data.parent_name);
                     }
                }
            }, {
                xtype: 'combobox',//搜索字段
                width: 100,
                itemId: 'column',
                store: [
                    [this.nameColumn, lang('LANGUAGE_ITEM_VAR_NAME')],
                    ['var_value_zh_cn', lang('VAR_VALUE_ZH_CN')],
                    ['var_value_en', lang('VAR_VALUE_EN')]
                ],
                value: data.column,
                editable: false
            },
            Yab.Field.combo().matchMode(),//匹配模式
            extField.keywordField(data.keyword),//关键字输入框
            this.btnSearch(function() {
                var ownerCt = this.ownerCt;
                var hash = Ext.util.History.getToken();
                var data = Ext.Object.fromQueryString(hash);
                data.sort = data.sort || 'sort_order';
                data.order = data.order || 'ASC';
                data = me.getQueryData(ownerCt, data);

                me.store(me.setHistory(data)).loadPage(1);
            })]
        };
    },

    //放到最后定义，否则，jsduck后，上面的方法将属于Yab.store.LanguageItems或Yab.model.LanguageItems
    /**
     * 定义模型
     *
     * @author        mrmsl <msl-138@163.com>
     * @date          2013-06-21 15:27:55
     *
     * @private
     *
     * @return {Object} this
     */
    defineModel: function() {
        /**
         * 表单域数据模型
         */
        Ext.define('Yab.model.LanguageItems', {
            extend: 'Ext.data.Model',
            /**
             * @cfg {Array}
             * 字段
             */
            fields: [this.idProperty, this.nameColumn, 'module_name', 'var_value_en', 'var_value_zh_cn', 'sort_order', 'memo', 'to_js'],
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
     * @date          2013-06-21 15:28:30
     *
     * @private
     * @member Yab.controller.LanguageItems
     *
     * @return {Object} this
     */
    defineStore: function() {
        /**
         * 表单域数据容器
         */
        Ext.define('Yab.store.LanguageItems', {
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
            model: 'Yab.model.LanguageItems',
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
                property : 'sort_order',
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
Ext.data.JsonP.Yab_controller_LanguageItems(['Yab.store.LanguageModules', Yab.controller.LanguageItems]);