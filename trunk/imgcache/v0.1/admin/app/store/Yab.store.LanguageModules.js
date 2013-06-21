/**
 * 语言包模块数据容器
 *
 * @file            app/store/Yab.store.LanguageModules.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-06-20 11:33:27
 * @lastmodify      $Date$ $Author$
 */

/**
 * 语言包模块数据模型
 */
Ext.define('Yab.model.LanguageModules', {
    extend: 'Ext.data.Model',
    /**
     * @cfg {Array}
     * 字段
     */
    fields: ['module_id', 'parent_id', 'module_name', 'memo', 'sort_order'],
    /**
     * @cfg {String}
     * 主键
     */
    idProperty: 'module_id'
});

/**
 * 语言包模块数据容器
 */
Ext.define('Yab.store.LanguageModules', {
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
    model: 'Yab.model.LanguageModules',
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
        text: '管理语言包模块'
    },
    constructor: function(config) {//构造函数
        config = config || {};
        this.proxy.url = config.url || getActionUrl('languagemodules/list');

        if (config.reader) {//reader 以设置总数
            this.proxy.reader = config.reader;
        }

        this.callParent([config || {}]);
    }
});