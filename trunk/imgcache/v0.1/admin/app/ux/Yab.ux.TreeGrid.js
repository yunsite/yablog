/**
 * 树列表扩展
 *
 * @file            app/ux/Yab.ux.Grid.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-12-18 11:08:40
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.ux.TreeGrid', {
    extend: 'Ext.tree.Panel',
    alias: ['widget.apptridgrid'],
    /**
     * @cfg {Boolean}
     * rootVisible
     */
    rootVisible: false,
    /**
     * @cfg {Boolean}
     * columnLines
     */
    columnLines: true,
    /**
     * @cfg {Boolean}
     * useArrows
     */
    useArrows: true,
    /**
     * @cfg {Boolean}
     * 设置自动滚动条
     */
    autoScroll: true,
    /**
     * @cfg {Object} (required)
     * 所属控制器
     */
    controller: null,
    /**
     * @cfg {Object} [tabData=null]
     * 标签数据
     */
    tabData: null,
    /**
     * @cfg {Object}
     * listeners
     */
    listeners: {
        checkchange: function(node, checked) {//子节点状态与父节点状态一致；选中子节点，同时选中父节点 by mrmsl on 22:17 2012-7-16
            !node.isLeaf() && node.cascadeBy(function(record) {
                record.set('checked', checked);
            });
        }
    },
    /**
     * @cfg {Function}
     * itemclick列表点击事件
     */
    onItemClick: Ext.emptyFn,
    /**
     * @cfg {Function}
     * 右键
     */
    onItemContextMenu: Ext.emptyFn,

    /**
     * 完成渲染
     *
     * @private
     *
     * @return {void} 无返回值
     */
    _afterRender: function(b) {//菜单完成渲染
        this.el.on({//阻止默认行为
            click: Ext.emptyFn,
            delegate: 'a',
            preventDefault: true
        });
        this.toolbar = this.child('toolbar');
    },

    /**
     * 初始化组件
     *
     * @private
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-12-18 11:10:20
     * @lastmodify      2013-01-12 21:24:33 mrmsl
     *
     * @return {Object} this
     */
    constructor: function(config) {
        var controller = config.controller, data = config.tabData, dockedItems = [];
        Ext.isFunction(controller.pagingBar) && dockedItems.push(controller.pagingBar(data));
        Ext.isFunction(controller.tbar) && dockedItems.push(controller.tbar(data));

        Ext.apply(config, {
            id: data.controller + data.action,
            store: controller.store(),
            columns: controller.getListColumns(),
            dockedItems: dockedItems.length ? dockedItems : undefined
        });

        controller._initlistgrid = true;//标识初始化，sortchange调用 by mrmsl on 2012-07-27 15:43:30
        controller._sortable = data.sort;
        this.callParent([config]);
    },

    /**
     * 完成渲染
     *
     * @private
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-12-19 12:12:30
     * @lastmodify      2013-02-02 16:11:13 by mrmsl
     *
     * @return {void} 无返回值
     */
    initComponent: function() {
        this.callParent();
        this.on({
            render: this._afterRender,
            itemclick: this.onItemClick,
            itemcontextmenu: this.onItemContextMenu,
            scope: this
        });
    }
});