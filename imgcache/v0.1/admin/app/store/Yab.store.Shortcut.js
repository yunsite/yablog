/**
 * 快捷方式数据容器
 *
 * @file            app/store/Yab.store.Shortcut.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-07-04 22:46:16
 * @lastmodify      $Date$ $Author$
 */

/**
 * 快捷方式数据模型
 */
Ext.define('Yab.model.Shortcut', {
    extend: 'Ext.data.Model',
    /**
     * @cfg {Array}
     * 字段
     */
    fields: ['short_id', 'menu_id', 'menu_name', 'additional_param', 'sort_order', 'memo', 'href'],
    /**
     * @cfg {String}
     * 主键
     */
    idProperty: 'short_id'
});

/**
 * 快捷方式数据容器
 */
Ext.define('Yab.store.Shortcut', {
    extend: 'Ext.data.Store',
    /**
     * @cfg {Boolean}
     * 自动消毁
     */
    autoDestroy: true,
    /**
     * @cfg {Object/String}
     * 模型
     */
    model: 'Yab.model.Shortcut',
    /**
     * @cfg {Number}
     * 每页显示数
     */
    pageSize: 10000,
    /**
     * @cfg {Object}
     * sorters
     */
    sorters: {
        property : 'sort_order',
        direction: 'ASC'
    },
    /**
     * @cfg {Object}
     * proxy
     */
    proxy: {
        type: C.dataType,
        reader: C.dataReader(),
        listeners: exception(),//捕获异常
        messageProperty: 'msg',
        simpleSortMode: true
    },
    constructor: function(config) {//构造函数
        this.proxy.url = config.url || getActionUrl('shortcut/list');
        this.callParent([config || {}]);
    }
});