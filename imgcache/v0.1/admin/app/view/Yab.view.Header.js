/**
 * 视图头部
 *
 * @file            app/view/Yab.view.Header.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-03-17 09:58:55
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.view.Header', {
    extend: 'Ext.container.Container',
    alias: 'widget.appheader',
    /**
     * @cfg {String}
     * componentCls
     */
    componentCls: 'div-header-content',
    /**
     * @cfg {Number}
     * 高
     */
    height: 65,
    /**
     * @cfg {Object}
     * layout
     */
    layout: {
        type: 'vbox',
        align: 'stretch'
    },
    /**
     * @cfg {String}
     * region
     */
    region: 'north',
    /**
     * @cfg {String}
     * style
     */
    style: 'background: #074E7C',

    /**
     * 初始化组件
     *
     * @private
     *
     * @return {void} 无返回值
     */
    initComponent: function() {
        this.items = [{
            height: 37,
            xtype: 'container',
            layout: 'hbox',
            items: [{
                xtype: 'container',
                contentEl: 'div-header-content',
                flex: 1
            }]
        }, {
            xtype: 'apptabs'
        }];
        this.callParent(arguments);
    }
});
