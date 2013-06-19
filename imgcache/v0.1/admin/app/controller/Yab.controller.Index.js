/**
 * 首页控制器
 *
 * @file            app/controller/Yab.controller.Index.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-03-17 10:06:47
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.controller.Index', {
    extend: 'Yab.controller.Base',
    /**
     * 获取url地址
     *
     * @return {String} url地址
     */
    getUrl: function() {
        return '#controller=index&action=index';
    },

    /**
     * 加载首页
     *
     * @return {void} 无返回值
     */
    loadIndex: function() {
        Yab.cmp.tabs.tabs.length == 0 && Yab.History.push('');
        Yab.cmp.tabs.setActiveTab('index');
        Yab.cmp.viewport.setPageTitle('');
        Yab.cmp.tree.selectUrl('none');
        Yab.cmp.card.layout.setActiveItem('appIndex');
    }
});