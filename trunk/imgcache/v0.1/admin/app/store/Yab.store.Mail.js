/**
 * 邮件模板数据容器
 *
 * @file            app/store/Yab.store.Mail.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-06-14 11:23:07
 * @lastmodify      $Date$ $Author$
 */

/**
 * 管理员数据模型
 */
Ext.define('Yab.model.Mail', {
    extend: 'Ext.data.Model',
    /**
     * @cfg {Array}
     * 字段
     */
    fields: ['template_id', 'subject', 'content', 'add_time', 'template_name', 'update_time', 'memo', 'sort_order'],
    /**
     * @cfg {String}
     * 主键
     */
    idProperty: 'template_id'
});

/**
 * 管理员数据容器
 */
Ext.define('Yab.store.Mail', {
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
    model: 'Yab.model.Mail',
    /**
     * @cfg {Object}
     * proxy
     */
    proxy: {
        type: C.dataType,
        url: getActionUrl('Mail/list'),
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
        property : 'sort_order',
        direction: 'ASC'
    },
    constructor: function(config) {//构造函数.
        config = config || {};
        this.proxy.url = config.url || getActionUrl('mail/list');
        this.callParent([config]);
    }
});