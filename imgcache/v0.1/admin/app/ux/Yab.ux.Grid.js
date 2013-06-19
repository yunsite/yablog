/**
 * 普通列表扩展
 *
 * @file            app/ux/Yab.ux.Grid.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-12-18 11:08:40
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.ux.Grid', {
    extend: 'Ext.grid.Panel',
    alias: ['widget.appgrid'],
    /**
     * @cfg {Object/String}
     * selectModel
     */
    selType: 'checkboxmodel',
    /**
     * @cfg {Object}
     * 通过复选框进行多选
     */
    selModel: {
        checkOnly: true
    },
    /**
     * @cfg {String}
     * 多选
     */
    multiSelect: true,
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
     * @cfg {Boolean}
     * columnLines
     */
    columnLines: true,
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
    _afterRender: function() {
        //this.callParent(arguments);
        var data = this.tabData, controller = this.controller;
        //data.sort && controller.setSort.call(this, data.sort, data.order);
        controller.selectModel = this.selModel;
        this.toolbar = this.child('toolbar');
    },

    /**
     * 完成排序
     *
     * @private
     *
     * @return {void} 无返回值
     */
    onSortChange: function(ct, column, direction, opt) {
        var controller = this.controller;
//log(controller._initlistgrid , direction , controller._sortable)
        //if (!controller._initlistgrid && direction && controller._sortable) {//走两次？一次direction为空
            controller.setSort.call(this, column.dataIndex, direction);
            var data = {
                page: controller.store().currentPage,
                sort: column.dataIndex,
                order: direction
            };

            controller.store(controller.setHistory(data));//重新设置store排序
        //}

        delete controller._initlistgrid;//干掉 by mrmsl on 2012-07-27 15:43:49
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
     * @lastmodify      2013-01-12 21:25:00 by mrmsl
     *
     * @return {void} 无返回值
     */
    initComponent: function() {
        this.callParent();
        this.on({
            render: this._afterRender,
            sortchange: this.onSortChange,
            itemclick: this.onItemClick,
            itemcontextmenu: this.onItemContextMenu,
            scope: this
        });
    }
});