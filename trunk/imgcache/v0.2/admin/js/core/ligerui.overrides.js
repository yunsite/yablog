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
        },
        _addTabItemEvent: function (tabitem)
        {
            var g = this, p = this.options;
            tabitem.click(function ()
            {
                var tabid = $(this).attr("tabid");
                global('clickTabItem', true);
                g.trigger('beforeItemClick', [tabid, tabitem]);//by mashanling on 2013-10-02 16:26:36
                g.selectTabItem(tabid);
                g.trigger('afterItemClick', [tabid, tabitem]);//by mashanling on 2013-10-02 16:26:36
                global('clickTabItem', false);
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

//_buliderSpaceContainer重写,支持在输入框后增加提示文字 by mashanling on 2013-10-08 14:00:35
//保留源代码格式,方便版本变更对比
$.ligerui.controls.Form.prototype._buliderSpaceContainer = function (field)
{
    var g = this, p = this.options;
    var spaceWidth = field.space || field.spaceWidth || p.space;
    var out = [];
    out.push('<li');
    if (p.spaceCss)
    {
        out.push(' class="' + p.spaceCss + '"');
    }
    out.push(' style="');
    if (spaceWidth && !field.tip)//增加 && !filed.tip by mashanling on 2013-10-08 13:57:24
    {
        out.push('width:' + spaceWidth + 'px;');
    }
    out.push('">');
    if (field.validate && field.validate.required)
    {
        out.push("<span class='l-star'>*</span>");
    }

    field.tip && out.push('<span style="color: gray; margin-left: 4px;">' + field.tip + '<span>');//提示 by mashanling on 2013-10-08 13:58:06

    out.push('</li>');
    return out.join('');
}

//ueditor继承
$.fn.extend(liger.editors, {
    //纯html
    displayfield: {
        body: $('<div style="color: gray"></div>'),
        control: 'DisplayField'
    }
});

//jquery方法继承
$.extend($.fn,{

    //纯html
    ligerDisplayField: function(options) {
        var me      = this,
            html    = options.html;

        if (!html) {
            return;
        }

        switch($.type(html)) {

            case 'string'://html代码
                this.html(html);
                break;

            case 'function'://函数
                html.call(this);
                break;

            case 'object':

                if (html instanceof $) {//jquery对象
                    this.append(html);
                }

                break;

            case 'array'://jquery对象组成的数组
                $.each(html, function(index, item) {
                    me.append(item);
                });
                break;
        }
    }//end ligerDisplayField
});