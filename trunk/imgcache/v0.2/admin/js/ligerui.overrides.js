/**
 * ligerui重写
 *
 * @file            ligerui.overrides.js
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-09-28 17:40:47
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

if ($.fn.ligerToolBar) {
     $.extend($.ligerMethos.ToolBar, {
addItem: function (item)
        {
            var g = this, p = this.options;
            if (item.line)
            {
                g.toolBar.append('<div class="l-bar-separator"></div>');
                return;
            }
            var ditem = $('<div class="l-toolbar-item l-panel-btn"><span></span><div class="l-panel-btn-l"></div><div class="l-panel-btn-r"></div></div>');
            g.toolBar.append(ditem);
            if(!item.id) item.id = 'item-'+(++g.toolbarItemCount);
			ditem.attr("toolbarid", item.id);
            if (item.img)
            {
                ditem.append("<img src='" + item.img + "' />");
                ditem.addClass("l-toolbar-item-hasicon");
            }
            else if (item.icon)
            {
                ditem.append("<div class='l-icon l-icon-" + item.icon + "'></div>");
                ditem.addClass("l-toolbar-item-hasicon");
            }
			else if (item.color)
			{
				ditem.append("<div class='l-toolbar-item-color' style='background:"+item.color+"'></div>");
                ditem.addClass("l-toolbar-item-hasicon");
			}
            item.text && $("span:first", ditem).html(item.text);
            item.disable && ditem.addClass("l-toolbar-item-disable");
            item.click && ditem.click(function () { if ($(this).hasClass("l-toolbar-item-disable")) return;item.click(item); });
			if (item.menu)
            {ditem.append('<div class="l-menubar-item-down"></div>');
                item.menu = $.ligerMenu(item.menu);
                ditem.hover(function ()
                {
					if ($(this).hasClass("l-toolbar-item-disable")) return;
                    g.actionMenu && g.actionMenu.hide();
                    var left = $(this).offset().left;
                    var top = $(this).offset().top + $(this).height();
                    item.menu.show({ top: top, left: left });
                    g.actionMenu = item.menu;
                    $(this).addClass("l-panel-btn-over");
                }, function ()
                {
					if ($(this).hasClass("l-toolbar-item-disable")) return;
                    $(this).removeClass("l-panel-btn-over");
                });
            }
            else
            {
                ditem.hover(function ()
				{
					if ($(this).hasClass("l-toolbar-item-disable")) return;
					$(this).addClass("l-panel-btn-over");
				}, function ()
				{
					if ($(this).hasClass("l-toolbar-item-disable")) return;
					$(this).removeClass("l-panel-btn-over");
				});
            }
        }
    });
}