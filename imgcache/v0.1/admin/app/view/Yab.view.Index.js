/**
 * 视图首页
 *
 * @file            app/view/Yab.view.Index.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-03-17 09:54:28
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.view.Index', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.appindex',
    /**
     * @cfg {String}
     * id
     */
    id: 'appIndex',
    /**
     * @cfg {String}
     * html
     */
    html: 'index',

    /**
     * 初始化组件
     *
     * @private
     *
     * @return {void} 无返回值
     */
    initComponent: function() {
        this.callParent(arguments);
    }
});