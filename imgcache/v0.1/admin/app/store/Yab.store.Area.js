/**
 * 国家地区数据容器
 *
 * @file            app/store/Yab.store.Area.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-01-12 22:01:36
 * @lastmodify      $Date$ $Author$
 */

 /**
 * 地区数据模型
 */
Ext.define('Yab.model.Area', {
    extend: 'Ext.data.Model',
    /**
     * @cfg {Array}
     * 字段
     */
    fields: ['area_id', 'parent_id', 'area_name', 'area_code', 'node', 'sort_order', 'is_show'],
    /**
     * @cfg {String}
     * 主键
     */
    idProperty: 'area_id'
});

/**
 * 国家地区数据容器
 */
Ext.define('Yab.store.Area', {
    extend: 'Ext.data.TreeStore',
    /**
     * @cfg {Boolean}
     * 自动消毁
     */
    autoDestroy: true,
    /**
     * @cfg {Boolean}
     * 排序，枝在前，叶在后
     */
    folderSort: true,//树枝排序 by mrmsl on 2012-07-23 12:44:23
    /**
     * @cfg {Object/String}
     * 模型
     */
    model: 'Yab.model.Area',
    /**
     * @cfg {Object}
     * proxy
     */
    proxy: {
        type: C.dataType,
        listeners: exception(),
        simpleSortMode: true,
        reader: C.dataReader()
    },
    /**
     * @cfg {Object}
     * 根节点
     */
    root: {
        expanded: true,
        area_id: 0,
        text: '管理菜单'
    },
    constructor: function(config) {//构造函数
        config = config || {};
        this.proxy.url = config.url || getActionUrl('area/list');
        this.callParent([config || {}]);
    }
});