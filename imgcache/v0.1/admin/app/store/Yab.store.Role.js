/**
 * 管理员角色数据容器
 *
 * @file            app/store/Yab.store.Role.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-01-25 11:32:26
 * @lastmodify      $Date$ $Author$
 */

/**
 * 管理员角色数据模型
 */
Ext.define('Yab.model.Role', {
    extend: 'Ext.data.Model',
    /**
     * @cfg {Array}
     * 字段
     */
    fields: ['role_id', 'role_name', 'priv', 'memo', 'sort_order'],
    /**
     * @cfg {String}
     * 主键
     */
    idProperty: 'role_id'
});

/**
 * 管理员角色数据容器
 */
Ext.define('Yab.store.Role', {
    extend: 'Ext.data.Store',
    /**
     * @cfg {Boolean}
     * 自动消毁
     */
    autoDestroy: true,
    /**
     * @cfg {Boolean}
     * 排序，枝在前，叶在后
     */
    model: 'Yab.model.Role',
    /**
     * @cfg {Number}
     * 每页显示数
     */
    pageSize: 20,
    /**
     * @cfg {Boolean}
     * 服务器端排序
     */
    remoteSort: true,
    /**
     * @cfg {Object}
     * proxy
     */
    proxy: {
        type: C.dataType,
        reader: C.dataReader(),
        listeners: exception(),//捕获异常 by mrmsl on 2012-07-08 21:43:01
        messageProperty: 'msg',
        simpleSortMode: true
    },
    constructor: function(config) {//构造函数
        //config = config || {};
        this.proxy.url = config.url || getActionUrl('role/list');
        this.callParent([config || {}]);
    }
});