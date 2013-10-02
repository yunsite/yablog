/**
 * ligerui重写
 *
 * @file            ligerui.overrides.js
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-09-21 11:49:57
 * @lastmodify      $Date$ $Author$
 */

if ($.fn.ligerTab) {
    $.extend($.ligerMethos.Tab, {
        getSelected: function () {
            return this.tab.links.ul.children('li.l-selected');
        },
        _activeTab: function() {
            log('_activeTab');return;
            var g = this;
            var from = g.tab.links.ul.find(">li[tabid=" + fromTabItemID + "]");
            var to = g.tab.links.ul.find(">li[tabid=" + toTabItemID + "]");
            var index1 = g.tab.links.ul.find(">li").index(from);
            var index2 = g.tab.links.ul.find(">li").index(to);
            if (index1 < index2)
            {
                to.after(from);
            }
            else
            {
                to.before(from);
            }
        },
        _addTabItemEvent: function (tabitem)
        {
            var g = this, p = this.options;
            tabitem.click(function ()
            {
                var tabid = $(this).attr("tabid");
                g.trigger('beforeItemClick', [tabid, tabitem]);//by mashanling on 2013-10-02 16:26:36
                g.selectTabItem(tabid);
                g.trigger('afterItemClick', [tabid, tabitem]);//by mashanling on 2013-10-02 16:26:36
            });
            //右键事件支持
            g.tab.menu && g._addTabItemContextMenuEven(tabitem);
            $(".l-tab-links-item-close", tabitem).hover(function ()
            {
                $(this).addClass("l-tab-links-item-close-over");
            }, function ()
            {
                $(this).removeClass("l-tab-links-item-close-over");
            }).click(function ()
            {
                var tabid = $(this).parent().attr("tabid");
                g.removeTabItem(tabid);
            });

        }
    });
}

if ($.fn.ligerGrid) {
    $.extend($.ligerDefaults.Grid, {
        root: 'data',
        record: 'total',
        dateFormat: 'Y-m-d H:i:s'
    });
    $.extend($.ligerDefaults.Grid.formatters, {
        date: function(value, column) {
            return 0 == value ? '' : date(column.dateFormat || this.options.dateFormat, intval(value) * 1000);
        },
        yesno: function (value, column) {
            return '<img alt="" src="' + IMAGES[value] + '" class="img-yesno" />';
        }
    });
}