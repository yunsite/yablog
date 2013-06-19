/**
 * 导航菜单树
 *
 * @file            app/view/Yab.view.Tree.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-03-17 08:51:12
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.view.Tree', {
    extend: 'Ext.tree.Panel',
    alias: 'widget.apptree',
    /**
     * @cfg {Boolean}
     * animCollapse
     */
    animCollapse: true,
    /**
     * @cfg {Object}
     * 选中菜单缓存
     */
    cache: {},//选中菜单缓存 by mrmsl on 2012-07-24 15:22:51
    /**
     * @cfg {String}
     * 显示名称字段
     */
    displayField: 'menu_name',
    /**
     * @cfg {String}
     * id
     */
    id: 'appTree',
    /**
     * @cfg {Boolean}
     * 可最小化
     */
    collapsible: true,
    /**
     * @cfg {Number}
     * 最大宽度
     */
    maxWidth: 300,
    /**
     * @cfg {Number}
     * 最小宽度
     */
    minWidth: 200,
    /**
     * @cfg {Number}
     * 宽度
     */
    width: 200,
    /**
     * @cfg {Boolean}
     * 可拉伸
     */
    split: true,
    /**
     * @cfg {Boolean}
     * collapseFirst
     */
    collapseFirst: false,
    /**
     * @cfg {String}
     * region
     */
    region: 'west',
    /**
     * @cfg {String}
     * layout
     */
    layout: 'fit',
    /**
     * @cfg {String}
     * bodyStyle
     */
    bodyStyle: 'background: #f7f7f7',
    /**
     * @cfg {Boolean}
     * 根文字可见
     */
    rootVisible: false,
    /**
     * @cfg {Object}
     * treeStore
     */
    store: Ext.create('Yab.store.Tree', {
        storeId: 'Yab.store.Tree'//不加storeId,会加载两次???!!! by mrmsl on 2012-07-24 22:05:34
    }),
    /**
     * @cfg {String} [title=NAVIGATION_MENU语言项]
     * 标题
     */
    title: lang('NAVIGATION_MENU'),
    /**
     * @cfg {Array}
     * 工具图标
     */
    tools: [{
        tooltip: lang('REFRESH'),
        type: 'refresh',
        handler: function(event, tool, header) {
            header.ownerCt.getStore().load();
        }
    }],
    /**
     * @cfg {Boolean}
     * useArrows
     */
    useArrows: true,
    /**
     * 查找树节点
     *
     * @param {String} controller 控制器
     * @param {String} action     执行动作
     *
     * @return {Mixed} 成功查找，返回Ext.data.NodeInterface，否则返回null
     */
    findRecordByUrl: function(controller, action) {
        return this.getRootNode().findChildBy(function(record) {
            var data = Ext.Object.fromQueryString(String(record.data.href).substr(1));
            return data.controller == controller && data.action == action;
        }, this, true)
    },

    /**
     * 初始化组件
     *
     * @private
     *
     * @return {void} 无返回值
     */
    initComponent: function() {
        this.addEvents('resizetabs');
        this.callParent();
    },

    /**
     * 高亮选中菜单
     *
     * @param {String} controller 控制器
     * @param {String} action     执行动作
     *
     * @return {Object} Ext.data.Store
     */
    selectUrl: function(controller, action) {

        if (this.cache[controller + action] === undefined) {//增加网站标题缓存控制 by mrmsl on 2012-07-24 15:23:08
            this.cache[controller + action] = this.findRecordByUrl(controller, action);
        }

        //优化选中菜单2012-07-24 15:04:26
        this.cache[controller + action] ? this.selectPath(this.cache[controller + action].getPath()) : this.getSelectionModel().deselectAll();

        return this.cache[controller + action];
    }
});