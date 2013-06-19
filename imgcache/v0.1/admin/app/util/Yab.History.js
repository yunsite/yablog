/**
 * 浏览器历史导航
 *
 * @file            app/util/Yab.History.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-03-17 10:03:43
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.History', {
    singleton: true,

    /**
     * 获取location.hash
     *
     * @param {String} url url地址
     *
     * @return {String} location.hash
     */
    cleanUrl: function(url) {
        return url.replace(/^[^#]+#/, '#');
    },

    /**
     * 初始化
     *
     * @return {void} 无返回值
     */
    init: function(){
        Ext.util.History.init(function(){
            this.historyLoaded = true;
            this.initialNavigate();
        }, this);
        Ext.util.History.on('change', this.navigate, this)
    },

    /**
     * 初始导航
     *
     * @private
     *
     * @return {void} 无返回值
     */
    initialNavigate: function(){

        if (this.tabsLoaded && this.historyLoaded && this.treeLoaded) {
            var hash = Ext.util.History.getToken();
            hash && this.navigate(hash);
        }
    },

    /**
     * 导航
     *
     * @param {String} hash location.hash
     *
     * @return {void} 无返回值
     */
    navigate: function(hash) {
        getController('Tabs').loadScript(hash || getController('Index').getUrl(), true);
    },

    /**
     * 标签加载完成回调
     *
     * @return {void} 无返回值
     */
    notifyTabsLoaded: function(){
        this.tabsLoaded = true;
        this.initialNavigate()
    },

    /**
     * 菜单加载完成回调
     *
     * @return {void} 无返回值
     */
    notifyTreeLoaded: function(){
        this.treeLoaded = true;
        this.initialNavigate()
    },

    /**
     * 添加导航
     *
     * @param {String} url url地址
     *
     * @return {void} 无返回值
     */
    push: function(url, noNavigate) {
        url = Ext.isString(url) ? url : Ext.Object.toQueryString(url);//支持string,object类型 by mrmsl on 2012-07-30 17:33:07
        url = this.cleanUrl(url);
        Ext.util.History.add(url)
    }
});