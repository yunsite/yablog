/**
 * 视图首页
 *
 * @file            app/view/Yab.view.Index.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-03-17 09:54:28
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.view.Index', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.appindex',
    requires: ['Yab.store.Filelog', 'Yab.store.Shortcut'],
    /**
     * @cfg {String}
     * id
     */
    id: 'appIndex',
    /**
     * @cfg {String}
     * style
     */
    style: 'margin: 8px;',

    /**
     * 初始化组件
     *
     * @private
     *
     * @return {void} 无返回值
     */
    initComponent: function() {
        this.items = [{
            xtype: 'panel',
            bodyStyle: 'border: none',
            layout: {
                type: 'hbox'
            },
            items: [{
                xtype: 'panel',
                title: lang('MY_PERSONAL_INFO'),
                height: 200,
                flex: 1
            }, this.shortcutPanel()]
        }];

        if (checkPriv('filelog', 'list')) {
            this.items.push(this.filelogGrid());
        }

        this.callParent(arguments);
    },

    /**
     * 文件日志grid
     *
     * @author          mrmsl <msl-138@163.com>
     * @Date            2013-07-05 22:00:40
     *
     * @private
     *
     * @return {Object} grid配置
     */
    filelogGrid: function () {
        return {
            margin: '8 0 0 0',
            xtype: 'grid',
            tools: [{
                tooltip: lang('REFRESH'),
                type: 'refresh',
                handler: function(event, tool, header) {
                    header.ownerCt.getStore().load();
                }
            }, {
                tootip: lang('MORE'),
                type: 'next',
                handler: function(event, tool, header) {
                    Yab.History.push('controller={0}&action={1}'.format('filelog', 'list'));
                }
            }],
            title: lang('NEWEST,FILE,LOG'),
            store: Ext.create(Yab.store.Filelog, {
                autoLoad: true,
                remoteSort: false
            }),
            columns: [
                { text: lang('FILENAME'),  dataIndex: 'filename', flex: 1, renderer: function(v) { return '<a class="link" href="#controller=filelog&action=view&filename={0}">{0}</a>'.format(v);} },
                { text: lang('FILESIZE'), dataIndex: 'size', width: 100, renderer: formatSize},
                { text: lang('LAST,UPDATE,TIME'), dataIndex: 'time', width: 150 }
            ]
        };
    },

    /**
     * 快捷方式面板
     *
     * @author          mrmsl <msl-138@163.com>
     * @Date            2013-07-05 22:00:52
     *
     * @private
     *
     * @return {Object} grid配置
     */
    shortcutPanel: function () {
        return {
            xtype: 'grid',
            title: lang('SHORTCUT'),
            tools: [{
                tooltip: lang('REFRESH'),
                type: 'refresh',
                handler: function(event, tool, header) {
                    header.ownerCt.getStore().load();
                }
            }, {
                tootip: lang('MORE'),
                type: 'next',
                handler: function(event, tool, header) {
                    Yab.History.push('controller={0}&action={1}'.format('shortcut', 'list'));
                }
            }],
            flex: 1,
            style: 'margin-left: 8px',
            store: Ext.create(Yab.store.Shortcut, {
                autoLoad: true
            }),
            columns: [
                { text: lang('SHORTCUT'), flex: 1, dataIndex: 'menu_name', renderer: function(v, cls, record) { return '<a class="link" href="{0}">{1}</a>'.format(record.get('href'), v);} }
            ],
            height: 200
        };
    }
});