/**
 * 文件日志数据容器
 *
 * @file            app/store/Yab.store.Filelog.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-06-27 13:47:59
 * @lastmodify      $Date$ $Author$
 */

/**
 * 文件日志数据模型
 */
Ext.define('Yab.model.Filelog', {
    extend: 'Ext.data.Model',
    /**
     * @cfg {Array}
     * 字段
     */
    fields: ['filename', 'size', 'time', 'content'],
    /**
     * @cfg {String}
     * 主键
     */
    idProperty: 'filename'
});

/**
 * 文件日志数据容器
 */
Ext.define('Yab.store.Filelog', {
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
    model: 'Yab.model.Filelog',
    /**
     * @cfg {Number}
     * 每页显示数
     */
    pageSize: 10000,
    /**
     * @cfg {Boolean}
     * 服务器端排序
     */
    remoteSort: true,
    /**
     * @cfg {Object}
     * sorters
     */
    sorters: {
        property : 'time',
        direction: 'DESC'
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
        this.proxy.url = config.url || getActionUrl('filelog/list');
        this.callParent([config || {}]);
    }
});