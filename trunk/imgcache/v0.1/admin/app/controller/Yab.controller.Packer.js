/**
 * 在线压缩js控制器
 *
 * @file            app/controller/Yab.controller.Packer.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-03-17 10:43:58
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.controller.Packer', {
    extend: 'Yab.controller.Base',

    /**
     * @inheritdoc Yab.controller.Base#listAction
     */
    listAction: function(data) {
        var options = {
            xtype: 'treepanel'
        };

        this.callParent([data, options]);//通用列表 by mrmsl on 2012-08-02 13:42:30
    },

    /**
     * @inheritdoc Yab.controller.Admin#getListColumns
     */
    getListColumns: function() {
        var me = this;

        return [{
            xtype: 'treecolumn',
            header: lang('FILE'),//文件名
            flex: 1,
            dataIndex: 'text',
            sortable: false
        }, {
            header: lang('FILE,CN_SHUOMING'),//文件说明
            flex: 1,
            dataIndex: 'desc',
            sortable: false,
            renderer: function(v, meta, record) {
                return record.isLeaf() ? v : '--';
            }
        }, {
            header: lang('LAST,CN_XIUGAI,TIME'),//最后修改时间
            width: 140,
            dataIndex: 'filemtime',
            sortable: false,
            renderer: function(v, meta, record) {
                return record.isLeaf() ? v : '--';
            }
        }, {
            header: lang('FILESIZE'),//文件大小
            width: 100,
            dataIndex: 'filesize',
            sortable: false,
            renderer: function(v, meta, record) {
                return record.isLeaf() ? v : '--';
            }
        }, {//操作列
            width: 100,
            xtype: 'appactioncolumn',
            items: [{//压缩
                renderer: function(v, meta, record) {
                    return record.isLeaf() && record.get('text').indexOf('.pack.') == -1 ? '<span class="appactioncolumn appactioncolumn-'+ this +'">' + lang('COMPRESS') + '</span>' : '--';
                },
                handler: function(grid, rowIndex, cellIndex) {
                    var record = grid.getStore().getAt(rowIndex);
                    me.pack(record.get('id'));
                }
            }]
        }];
    },//end getListColumns


    /**
     * 压缩文件
     *
     * @private
     *
     * @param {String} file 压缩文件
     *
     * @return {void} 无返回值
     */
    pack: function(file) {
        this.commonAction({
            action: this.getActionUrl(false, 'pack'),
            data: 'file=' + file,
            scope: this,
            store: this.store()
        });
    },

    /**
     * @inheritdoc Yab.controller.Admin#store
     */
    store: function(data) {

        if (!this._store) {//未创建
            this._store = Ext.create('Ext.data.TreeStore', {
                fields: ['id', 'text', 'filesize', 'filemtime', 'desc'],
                autoDestroy: true,
                proxy: {
                    type: C.dataType,
                    listeners: exception(),
                    reader: C.dataReader(),
                    url: this.getActionUrl(false, 'list')
                },
                root: {
                    expanded: true,
                    id: '/',
                    text: '管理菜单'
                },
                folderSort: true
            });
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
                menu: [{
                    text: lang('COMPRESS,ALL'),
                    handler: function() {
                        me.pack('all');
                    }
                }, {
                    text: lang('COMPRESS,SELECTED'),
                    handler: function() {
                        var file = me.hasSelect(me._listgrid);
                        file && me.pack(file);
                    }
                }]//end button menu
            }, '->', {
                xtype: 'tool',
                type: 'expand',
                tooltip: lang('ALL,EXPAND'),
                handler: function() {
                    me._listgrid.expandAll();
                }
            }, {
                xtype: 'tool',
                type: 'collapse',
                tooltip: lang('ALL,COLLAPSE'),
                handler: function() {
                    me._listgrid.collapseAll();
                }
            }, {
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
Ext.data.JsonP.Yab_controller_Packer(Yab.controller.Packer);