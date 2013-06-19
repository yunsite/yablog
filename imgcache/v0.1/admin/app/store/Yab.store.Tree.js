/**
 * 菜单数据容器
 *
 * @file            app/store/Yab.store.Tree.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-01-12 22:01:36
 * @lastmodify      $Date$ $Author$
 */

/**
 * 菜单数据模型
 */
Ext.define('Yab.model.Tree', {
    extend: 'Ext.data.Model',
    /**
     * @cfg {Array}
     * 字段
     */
    fields: ['id', 'menu_id', 'parent_id', 'menu_name', 'text', 'controller', 'action', 'node', 'sort_order', 'is_show'],
    /**
     * @cfg {String}
     * 主键
     */
    idProperty: 'menu_id'
});

/**
 * 菜单数据容器
 */
Ext.define('Yab.store.Tree', {
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
    folderSort: true,//树枝排序 by mrmsl on 2012-07-23 12:41:37
    /**
     * @cfg {Object}
     * 监听事件
     */
    listeners: {
        load: function() {//加载完成后，菜单树可用
            !Yab.History.treeLoaded && Yab.History.notifyTreeLoaded();
        }
    },
    /**
     * @cfg {Object/String}
     * 模型
     */
    model: 'Yab.model.Tree',
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
        menu_id: 0,
        text: '管理菜单'
    },
    constructor: function(config) {//构造函数
        config = config || {};
        this.proxy.url = config.url || getActionUrl('menu/publicTree');

        if (config.reader) {//reader 以设置总数 by mrmsl on 2012-08-13 15:00:38
            this.proxy.reader = config.reader;
        }

        this.callParent([config || {}]);
    }
});