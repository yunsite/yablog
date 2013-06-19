/**
 * 管理员数据容器
 *
 * @file            app/store/Yab.store.Admin.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-05-10 18:03:28
 * @lastmodify      $Date$ $Author$
 */

/**
 * 管理员数据模型
 */
Ext.define('Yab.model.Admin', {
    extend: 'Ext.data.Model',
    /**
     * @cfg {Array}
     * 字段
     */
    fields: ['admin_id', 'username', 'realname', 'role_id', 'add_time', 'last_login_time', 'last_login_ip', 'logins', 'login_num', 'is_restrict', 'role_name', 'is_lock'],
    /**
     * @cfg {String}
     * 主键
     */
    idProperty: 'admin_id'
});

/**
 * 管理员数据容器
 */
Ext.define('Yab.store.Admin', {
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
    model: 'Yab.model.Admin',
    /**
     * @cfg {Number}
     * 每页显示数
     */
    pageSize: 20,
    /**
     * @cfg {Object}
     * proxy
     */
    proxy: {
        type: C.dataType,
        url: getActionUrl('admin/list'),
        reader: C.dataReader(),
        listeners: exception(),//捕获异常 by mrmsl on 2012-07-08 21:43:34
        messageProperty: 'msg',
        simpleSortMode: true
    },
    /**
     * @cfg {Boolean}
     * 服务器端排序
     */
    remoteSort: true,

    //增加排序，以防点击列表头部排序时，多余传参，出现不必要的错误 by mrmsl on 2012-07-27 15:56:17
    /**
     * @cfg {Object}
     * sorters
     */
    sorters: {
        property : 'admin_id',
        direction: 'DESC'
    },
    constructor: function(config) {
        this.callParent([config || {}]);
    }
});