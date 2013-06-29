/**
 * 页面窗口视图
 *
 * @file            app/view/Yab.view.Viewport.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-03-17 08:46:32
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.view.Viewport', {
    extend: 'Ext.container.Viewport',
    /**
     * @cfg {Object}
     * 网站标题缓存
     */
    cache: {},//网站标题缓存 by mrmsl on 2012-07-24 15:21:54
    /**
     * @cfg {Object}
     * defaults
     */
    defaults: {
        xtype: 'container',
        bodyPadding: 4
    },
    /**
     * @cfg {String}
     * id
     */
    id: 'appViewport',
    /**
     * @cfg {String}
     * layout
     */
    layout: 'border',
    /**
     * @cfg {String}
     * style
     */
    style: 'background: #fff',

    /**
     * @cfg {Array}
     * items
     */
    items: [{
        xtype: 'appheader'
    }, {
        collapseMode: 'mini',
        xtype: 'apptree'
    }, {
        xtype: 'appcenter'
    }],

    /**
     * 设置页面标题，参数大于2个将手动设置标题
     *
     * @param {String} [controller=url自动获取] 控制器
     * @param {String} [action==url自动获取] 操作方法
     *
     * @return {void} 无返回值
     */
    setPageTitle: function(controller, action) {
        var str = '?' + location.hash.substr(1);
        controller = controller || _GET('controller', str), action = action || _GET('action', str);

        if (arguments[2]) {//手动设置标题
            document.title = arguments[2];
            //添加 => 编辑 by mrmsl on 2012-08-09 12:45:28 最近操作调用
            this.cache[controller + action] = this.cache[controller + action].replace(lang('ADD'), lang('EDIT'));
        }
        else {

            if (!this.cache[controller + action]) {//增加网站标题缓存控制 by mrmsl on 2012-07-24 15:22:21
                var store = Yab.cmp.tree.findRecordByUrl(controller, action);//高亮选中菜单
                var title = [];

                if (store) {
                    store.bubble(function(node) {
                        !node.isRoot() && title.push(node.get('menu_name'));
                    });
                }

                title = title.join(System.sys_show_title_separator);
                title = strip_tags(title);
                this.cache[controller + action] = title;
            }

            this.origTitle = this.origTitle ? this.origTitle : document.title;
            //编辑 => 添加  by mrmsl on 2012-08-09 12:46:17 最近操作调用
            document.title = this.cache[controller + action] ? (this.cache[controller + action].replace(lang('EDIT'), lang('ADD')) + System.sys_show_title_separator + this.origTitle) : this.origTitle;
        }

        var title = document.title.split(System.sys_show_title_separator);
        title.pop();
        title = title.reverse().join(System.sys_show_bread_separator);
        Yab.cmp.card.setTitle(title);//主面板标题 by mrmsl on 2012-12-03 13:21:49
    }//end setPageTitle
});