/**
 * 视图中心
 *
 * @file            app/view/Yab.view.Center.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-03-17 10:00:20
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.view.Center', {
    extend: 'Ext.container.Container',
    alias: 'widget.appcenter',
    /**
     * @cfg {String}
     * id
     */
    id: 'appCenter',
    /**
     * @cfg {Object}
     * items
     */
    items: {
        id: 'appCard',
        cls: 'card-panel',
        xtype: 'panel',
        style: 'margin-top: 4px;',
        defaults: {
            bodyStyle: 'border: none',
        },
        title: lang('BACKEND,MANAGE_CENTER'),
        layout: {
            type: 'card',
            deferredRender: true
        },
        items: [{
            xtype: 'appindex'
        }]
    },
    /**
     * @cfg {String}
     * layout
     */
    layout: 'fit',
    /**
     * @cfg {Number}
     * 最小宽度
     */
    minWidth: 500,
    /**
     * @cfg {String}
     * region
     */
    region: 'center'
});