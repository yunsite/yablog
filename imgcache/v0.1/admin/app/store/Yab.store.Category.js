/**
 * 博客分类数据容器
 *
 * @file            app/store/Yab.store.Category.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-03-21 16:23:08
 * @lastmodify      $Date$ $Author$
 */

/**
 * 菜单数据模型
 */
Ext.define('Yab.model.Category', {
    extend: 'Ext.data.Model',
    /**
     * @cfg {Array}
     * 字段
     */
    fields: ['cate_id', 'parent_id', 'cate_name', 'en_name', 'node', 'sort_order', 'is_show'],
    /**
     * @cfg {String}
     * 主键
     */
    idProperty: 'cate_id'
});

/**
 * 博客分类数据容器
 */
Ext.define('Yab.store.Category', {
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
    folderSort: true,//树枝排序
    /**
     * @cfg {Object/String}
     * 模型
     */
    model: 'Yab.model.Category',
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
        cate_id: 0,
        text: '管理菜单'
    },
    constructor: function(config) {//构造函数
        config = config || {};
        this.proxy.url = config.url || getActionUrl('category/publicCategory');

        if (config.reader) {//reader 以设置总数
            this.proxy.reader = config.reader;
        }

        this.callParent([config || {}]);
    }
});