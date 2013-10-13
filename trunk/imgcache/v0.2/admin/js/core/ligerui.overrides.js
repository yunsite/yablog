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
    $.extend($.ligerMethos.Grid, {
        /**
         * 改变每页大小
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-10-13 12:25:31
         *
         * @param {int} pageSize 大小
         * @param {object} queryParams 列表查询参数
         *
         * return {void} 无返回值
         */
        changePageSize: function(pageSize, queryParams) {
            var options = this.options;

            if (this.isDataChanged && 'local' != options.dataAction && !confirm(options.isContinueByDataChanged)) {
                return;
            }

            options.newPage = 1;
            options.pageSize = pageSize;
            this.loadData(options.where);

            $.extend(queryParams, {
                page_size: pageSize,
                page: 1
            });

            seajs.require('core/router').navigate(o2q(queryParams));
        }

    });

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

if ($.fn.ligerForm) {
    $.extend($.ligerDefaults.Form, {
        validate: {
            //源代码在输入框首次失去焦点后，未清除掉错误样式，原因见下
            success: function (lable, element)
            {
                //if (!lable.attr("for")) return;
                //var element = $("#" + lable.attr("for"));//原因在此，首次返回空，增加element参数，把这行及上一行干掉即可

                var element = $(element);

                if (element.hasClass("l-textarea"))
                {
                    element.removeClass("l-textarea-invalid");
                }
                else if (element.hasClass("l-text-field"))
                {
                    element.parent().removeClass("l-text-invalid");
                }
                $(element).removeAttr("title").ligerHideTip();
            }
        }
    });
}

if ($.ligerDialog) {
    $.extend($.ligerDefaults.Dialog, {
        cls: 'dialog-break-word'//弹出对话框内容，强制换行，防止出现滚动条
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

    field.tip && out.push('<span style="color: gray; margin-left: 4px;">' + field.tip + '</span>');//提示 by mashanling on 2013-10-08 13:58:06

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

if ($.fn.ligerDateEditor) {
    $.extend($.ligerMethos.DateEditor, {
        showDate: function ()
        {
            var g = this, p = this.options;
            if (!this.currentDate) return;
            this.currentDate.hour = parseInt(g.toolbar.time.hour.html(), 10);
            this.currentDate.minute = parseInt(g.toolbar.time.minute.html(), 10);
            var dateStr = this.currentDate.year + '/' + this.currentDate.month + '/' + this.currentDate.date + ' ' + this.currentDate.hour + ':' + this.currentDate.minute;
            //增加这句，支持到秒
            dateStr += ':' + new Date().getSeconds();
            var myDate = new Date(dateStr);
            dateStr = g.getFormatDate(myDate);
            this.inputText.val(dateStr);
            this.onTextChange();
        }
    });
    $.extend($.ligerDefaults.DateEditor, {
        format: 'yyyy-MM-dd hh:mm:ss',
        width: 140,
        cancelable: false,
        showTime: true
    });
}
