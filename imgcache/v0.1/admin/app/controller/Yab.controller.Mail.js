/**
 * 邮件模板控制器
 *
 * @file            app/controller/Yab.controller.Mail.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-06-06 17:12:43
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.controller.Mail', {
    extend: 'Yab.controller.Base',
    /**
     * @cfg {String}
     * 主键
     */
    idProperty: 'template_id',
    /**
     * @cfg {String}
     * 名称字段
     */
    nameColumn: 'template_name',//名称字段

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

        seajs.use(['Yab.ux.Ueditor', 'ueditor', 'ueditorConfig'], function () {
            me.ueditor = true;
            me.superclass.addAction.apply(me, [data, options]);
        });
    },

    /**
     * @inheritdoc Yab.controller.Admin#formField
     */
    formField: function(data) {
        var me = this, extField = Yab.Field.field();

        return [
            extField.fieldContainer(['TEMPLATE,NAME', [//模板名称
                [null, this.nameColumn, 'PLEASE_ENTER,TEMPLATE,NAME'],
                lang('LT_BYTE').format(20) + '，' + lang('CN_TO_BYTE')
            ]]),
            extField.fieldContainer(['MAIL_SUBJECT', [//邮件主题
                [null, 'subject', 'PLEASE_ENTER,MAIL_SUBJECT', '', '', {width: 400}],
                lang('LT_BYTE').format(150)
            ]]),
            {//内容
                xtype: 'ueditor',
                name: 'content',
                fieldLabel: lang('CONTENT')
            },
            //extField.textarea('content', 'PLEASE_ENTER,MAIL_CONTENT', 'MAIL_CONTENT', '', { width: 1000, height: 300 }),//模板内容
            extField.sortOrderField(),//排序
            extField.memoField(),//备注
            extField.textareaComment(lang('LT_BYTE').format(60)),//备注提示
            extField.hiddenField(),//template_id
            this.btnSubmit()//通用提交按钮
        ];
    },

    /**
     * @inheritdoc Yab.controller.Admin#getListColumns
     */
    getListColumns: function() {
        var me = this;

        return [{
            text: lang('TEMPLATE') + 'id',//模板id
            width: 50,
            dataIndex: this.idProperty
        }, {
            header: lang('TEMPLATE,NAME'),//模板名
            width: 150,
            dataIndex: this.nameColumn
        }, {
            header: lang('ORDER'),//排序
            dataIndex: 'sort_order',
            width: 60,
            align: 'center'
        }, {
            header: lang('MEMO'),//备注
            flex: 1,
            dataIndex: 'memo',
            sortable: false
        }, {//操作列
            width: 160,
            xtype: 'appactioncolumn',
            items: [{
                renderer: function(v, meta, record) {//编辑
                    return '<span class="appactioncolumn appactioncolumn-'+ this +'">' + lang('EDIT') + '</span>';
                },
                handler: function(grid, rowIndex, cellIndex) {
                    me.edit(grid.getStore().getAt(rowIndex));
                }
            }, {
                renderer: function(v, meta, record) {//删除
                    return '<span class="appactioncolumn appactioncolumn-'+ this +'">' + lang('DELETE') + '</span>';
                },
                handler: function(grid, rowIndex, cellIndex) {
                    var record = grid.getStore().getAt(rowIndex);
                    me['delete'](record, '<span class="font-bold font-red">' + htmlspecialchars(record.get(me.nameColumn)) + '</span>');
                }
            }]
        }];
    },//end getListColumns

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
            displayInfo: true
        };
    },//end pagingBar

    /**
     * @inheritdoc Yab.controller.Admin#store
     */
    store: function() {

        if (!this._store) {//未创建
            this._store = Ext.create('Yab.store.Mail', {
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
            items: this.deleteItem()
        }
    }//end tbar
});

//放到最后，以符合生成jsduck类说明
Ext.data.JsonP.Yab_controller_Mail(['Yab.store.Mail', Yab.controller.Mail]);