/**
 * extjs重写
 *
 * @file            app/util/override.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-05-10 17:22:37
 * @lastmodify      $Date$ $Author$
 */

Ext.Loader.setConfig({//路径
    enabled: true,
    paths: {
        'Yab.container': System.sys_base_admin_imgcache + 'app/container/',
        'Yab.controller': System.sys_base_admin_imgcache + 'app/controller/',
        'Yab.store': System.sys_base_admin_imgcache + 'app/store/',
        'Yab.store': System.sys_base_admin_imgcache + 'app/store/',
        'Yab.model': System.sys_base_admin_imgcache + 'app/model/',
        'Yab.ux': System.sys_base_admin_imgcache + 'app/ux/',
        'Yab.pack': System.sys_base_admin_imgcache + 'app/pack/',//压缩js路径 by mrmsl on 2012-09-04 17:49:49
    }
}).getPath = function(className) {
    var path = '', paths = this.config.paths;

    if (0 == className.indexOf('Yab.')) {//yablog专用 by mrmsl on 2013-06-20 18:22:26
        var arr = className.split('.');
        arr.pop();
        return paths[arr.join('.')] + className + '.js';
    }

    var prefix = this.getPrefix(className);

    if (prefix.length > 0) {
        if (prefix === className) {
            return paths[prefix];
        }

        path = paths[prefix];
        className = className.substring(prefix.length + 1);
    }

    if (path.length > 0) {
        path += '/';
    }

    return path.replace(/\/\.\//g, '/') + className.replace(/\./g, '/') + '.js';
};

//重写表单提交时，setLoading()提示
Ext.override(Ext.form.Basic, {
    beforeAction: function(action) {//处理前
        var waitMsg = action.waitMsg;

        this.getFields().each(function(f) {
            f.isFormField && f.syncValue && f.syncValue();
        });

        waitMsg && setLoading(waitMsg);
        action.btn && action.btn.setDisabled(true);//禁用提交按钮 by mrmsl on 2012-07-06 16:56:08
    },

    afterAction: function(action, success) {//处理后
        action.waitMsg && setLoading(false);
        action.btn && action.btn.setDisabled(false);//启用提交按钮 by mrmsl on 2012-07-06 16:58:32

        if (success) {
            action.reset && this.reset();
            Ext.callback(action.success, action.scope || action, [this, action]);
            this.fireEvent('actioncomplete', this, action);
        }
        else {
            Ext.callback(action.failure, action.scope || action, [this, action]);
            this.fireEvent('actionfailed', this, action);
        }
    }
});

//重新加载树根节点，而不是全部都加载
Ext.override(Ext.data.TreeStore, {
    load: function(options) {
        options = options || {};
        options.params = options.params || {};

        var me = this, node = options.node || me.tree.getRootNode(), root;

        // If there is not a node it means the user hasnt defined a rootnode yet. In this case lets just
        // create one for them.
        if (!node) {
            node = me.setRootNode({
                expanded: true
            });
        }

        if (me.clearOnLoad) {
            //node.removeAll(true);
            node.removeAll(false);
        }

        Ext.applyIf(options, {
            node: node
        });
        options.params[me.nodeParam] = node ? node.getId() : 'root';

        if (node) {
            node.set('loading', true);
        }

        return me.callParent([options]);
    }
});

//列表单元格内容可选
Ext.override(Ext.view.Table, {
    afterRender: function() {
        var me = this;

        me.callParent();
        /*me.mon(me.el, {
            scroll: me.fireBodyScroll,
            scope: me
        });*/
        if (!me.featuresMC && (me.featuresMC.findIndex('ftype', 'unselectable') >= 0)) {
            me.el.unselectable();
        }

        me.attachEventsForFeatures();
    }
});

//列表单元格内容可选
Ext.define('Yab.ux.grid.SelectFeature', {
    extend: 'Ext.grid.feature.Feature',
    alias: 'feature.selectable',

    mutateMetaRowTpl: function(metaRowTpl) {
        var i, ln = metaRowTpl.length;

        for (i = 0; i < ln; i++) {
            tpl = metaRowTpl[i];
            tpl = tpl.replace(/x-grid-row/, 'x-grid-row x-selectable');
            tpl = tpl.replace(/x-grid-cell-inner x-unselectable/g, 'x-grid-cell-inner');
            tpl = tpl.replace(/unselectable="on"/g, '');
            metaRowTpl[i] = tpl;
        }
    }
});

//弹出层
Ext.override(Ext.window.Window, {
    constrain: true,
    modal: true
});

Ext.override(Ext.form.field.Date, {//textfield默认宽150 by mrmsl on 2012-09-10 13:00:38
    width: 150
});

//自定义操作column
Ext.define('Yab.grid.column.Action', {
    extend: 'Ext.grid.column.Column',
    alias: ['widget.appactioncolumn'],
    alternateClassName: 'Yab.grid.ActionColumn',
    header: lang('OPERATE'),
    sortable: false,
    //flex: 1,
    columnCls: 'appactioncolumn',
    constructor: function(config) {
        var me = this,
            cfg = Ext.apply({}, config),
            items = cfg.items || [me],
            l = items.length,
            i,
            item;

        // This is a Container. Delete the items config to be reinstated after construction.
        delete cfg.items;
        me.callParent([cfg]);

        // Items is an array property of ActionColumns
        me.items = items;

//      Renderer closure iterates through items creating an <img> element for each and tagging with an identifying
//      class name x-action-col-{n}
        me.renderer = function(v, meta) {
//          Allow a configured renderer to create initial value (And set the other values in the "metadata" argument!)
            v = Ext.isFunction(cfg.renderer) ? cfg.renderer.apply(this, arguments)||'' : '';
            for (i = 0; i < l; i++) {
                item = items[i];
                v += item.renderer ? item.renderer.apply(i, arguments) : '<span class="appactioncolumn appactioncolumn-'+ i +'">' + item.text + '</span>';
            }
            return v;
        };
    },

    destroy: function() {
        delete this.items;
        delete this.renderer;
        return this.callParent(arguments);
    },

    /**
     * @private
     * Process and refire events routed from the GridView's processEvent method.
     * Also fires any configured click handlers. By default, cancels the mousedown event to prevent selection.
     * Returns the event handler's status to allow canceling of GridView's bubbling process.
     */
    processEvent : function(type, view, cell, recordIndex, cellIndex, e){
        var me = this,
            arr = e.getTarget().className.split(' '),
            item, fn;
        if (arr[0] == me.columnCls) {
            item = me.items[arr.pop().split('-').pop()];
            if (item) {
                if (type == 'click') {
                    fn = item.handler || me.handler;
                    if (fn && !item.disabled) {
                        fn.call(item.scope || me.scope || me, view, recordIndex, cellIndex, item, e);
                    }
                } else if (type == 'mousedown' && item.stopSelection !== false) {
                    return false;
                }
            }
        }
        return me.callParent(arguments);
    },
    cascade: function(fn, scope) {
        fn.call(scope||this, this);
    },
    getRefItems: function() {
        return [];
    }
});
/*
Ext.override(Ext.grid.Scroller, {
    afterRender: function() {
        var me = this;
        me.callParent();
        me.mon(me.scrollEl, 'scroll', me.onElScroll, me);
        Ext.cache[me.el.id].skipGarbageCollection = true;
        // add another scroll event listener to check, if main listeners is active
        Ext.EventManager.addListener(me.scrollEl, 'scroll', me.onElScrollCheck, me);
        // ensure this listener doesn't get removed
        Ext.cache[me.scrollEl.id].skipGarbageCollection = true;
    },

    // flag to check, if main listeners is active
    wasScrolled: false,

    // synchronize the scroller with the bound gridviews
    onElScroll: function(event, target) {
        this.wasScrolled = true; // change flag -> show that listener is alive
        this.fireEvent('bodyscroll', event, target);
    },

    // executes just after main scroll event listener and check flag state
    onElScrollCheck: function(event, target, options) {
        var me = this;

        if (!me.wasScrolled) {
            // Achtung! Event listener was disappeared, so we'll add it again
            me.mon(me.scrollEl, 'scroll', me.onElScroll, me);
        }
        me.wasScrolled = false; // change flag to initial value
    }

});*/

Ext.override(Ext.menu.Menu, {//重写菜单 by  mrmsl on 2012-08-09 18:37:31
    minWidth: 100,
    plain: true,
    shadow: 'drop',
    componentCls: 'tab-contextmenu'
});

Ext.override(Ext.menu.Item, {//重写菜单项 by  mrmsl on 2012-09-05 10:17:18
    iconCls: 'icon-none',//干掉背景图片,IE下会留空白 by mrmsl on 2012-07-31 21:08:08
    activeCls: Ext.baseCSSPrefix + 'menu-item-active-tab-contextmenu'
});

Ext.override(Ext.menu.CheckItem, {//重写菜单可选项 by  mrmsl on 2012-09-05 10:24:55
    iconCls: ''
});

Ext.override(Ext.form.Panel, {//重写表单fieldDefaults by mashanlin on 2012-09-06 12:42:29
    fieldDefaults: {
        labelAlign: 'right',
        labelPad: 0,
        labelSeparator: '：'
    }
});

Ext.Date.clearTime = function(date, clone) {//重写Ext.Date.clearTime，不清除时间 by mrmsl on 2012-09-08 16:21:11
    return clone ? Ext.Date.clone(date) : date;
};

Ext.override(Ext.picker.Date, {//重写Ext.picker.Date，选择日期带默认时间 by mrmsl on 2012-09-10 08:35:11
    fullUpdate: function(_date, active){
        var me = this,
            cells = me.cells.elements,
            textNodes = me.textNodes,
            disabledCls = me.disabledCellCls,
            eDate = Ext.Date,
            i = 0,
            extraDays = 0,
            visible = me.isVisible(),
            sel = +eDate.clearTime(_date, true),
            today = date(System.sys_timezone_date_format),
            min = me.minDate ? date(false, me.minDate) : false,//me.minDate ? eDate.clearTime(me.minDate, true) : Number.NEGATIVE_INFINITY,
            max = me.maxDate ? date(false, me.maxDate) : false,// me.maxDate ? eDate.clearTime(me.maxDate, true) : Number.POSITIVE_INFINITY,
            ddMatch = me.disabledDatesRE,
            ddText = me.disabledDatesText,
            ddays = me.disabledDays ? me.disabledDays.join('') : false,
            ddaysText = me.disabledDaysText,
            format = me.format,
            days = eDate.getDaysInMonth(_date),
            firstOfMonth = eDate.getFirstDateOfMonth(_date),
            startingPos = firstOfMonth.getDay() - me.startDay,
            previousMonth = eDate.add(_date, eDate.MONTH, -1),
            longDayFormat = me.longDayFormat,
            prevStart,
            current,
            disableToday,
            tempDate,
            setCellClass,
            html,
            cls,
            formatValue,
            value;

        if (startingPos < 0) {
            startingPos += 7;
        }

        //var date = new Date();
        days += startingPos;
        prevStart = eDate.getDaysInMonth(previousMonth) - startingPos;
        current = new Date(previousMonth.getFullYear(), previousMonth.getMonth(), prevStart, _date.getHours(), _date.getMinutes(), _date.getSeconds());

        if (me.showToday) {
            tempDate = eDate.clearTime(new Date());
            disableToday = (tempDate < min || tempDate > max ||
                (ddMatch && format && ddMatch.test(eDate.dateFormat(tempDate, format))) ||
                (ddays && ddays.indexOf(tempDate.getDay()) != -1));

            if (!me.disabled) {
                me.todayBtn.setDisabled(disableToday);
                me.todayKeyListener.setDisabled(disableToday);
            }
        }

        setCellClass = function(cell){
            value = date(System.sys_timezone_date_format, current);//Y-m-d by mrmsl on 2012-09-10 08:40:05
            cell.title = eDate.format(current, longDayFormat);

            cell.firstChild.dateValue = +current;
            cell.firstChild.date = value;
            if(value == today){
                cell.className += ' ' + me.todayCls;
                cell.title = me.todayText;
            }
            if(value == sel){
                cell.className += ' ' + me.selectedCls;
                me.el.dom.setAttribute('aria-activedescendant', cell.id);
                if (visible && me.floating) {
                    Ext.fly(cell.firstChild).focus(50);
                }
            }

            if(value < min) {
                cell.className = disabledCls;
                cell.title = me.minText;
                return;
            }
            if(value > max) {
                cell.className = disabledCls;
                cell.title = me.maxText;
                return;
            }
            if(ddays){
                if(ddays.indexOf(current.getDay()) != -1){
                    cell.title = ddaysText;
                    cell.className = disabledCls;
                }
            }
            if(ddMatch && format){
                formatValue = eDate.dateFormat(current, format);
                if(ddMatch.test(formatValue)){
                    cell.title = ddText.replace('%0', formatValue);
                    cell.className = disabledCls;
                }
            }
        };

        for(; i < me.numDays; ++i) {
            if (i < startingPos) {
                html = (++prevStart);
                cls = me.prevCls;
            } else if (i >= days) {
                html = (++extraDays);
                cls = me.nextCls;
            } else {
                html = i - startingPos + 1;
                cls = me.activeCls;
            }
            textNodes[i].innerHTML = html;
            cells[i].className = cls;
            current.setDate(current.getDate() + 1);
            setCellClass(cells[i]);
        }

        me.monthBtn.setText(me.monthNames[_date.getMonth()] + ' ' + _date.getFullYear());
    },

    selectedUpdate: function(_date, active){
        var me = this,
            t = date(System.sys_timezone_date_format, _date),//Y-m-d by mrmsl on 2012-09-10 08:37:34
            cells = me.cells,
            cls = me.selectedCls;

        cells.removeCls(cls);
        cells.each(function(c){
            if (c.dom.firstChild.date == t) {
                me.el.dom.setAttribute('aria-activedescendent', c.dom.id);
                c.addCls(cls);
                if(me.isVisible() && !me.cancelFocus){
                    Ext.fly(c.dom.firstChild).focus(50);
                }
                return false;
            }
        }, this);
    },

    handleDateClick: function(e, t) {
        var me = this, handler = me.handler;

        e.stopEvent();
        if (!me.disabled && t.dateValue && !Ext.fly(t.parentNode).hasCls(me.disabledCellCls)) {
            me.cancelFocus = me.focusOnSelect === false;
            var now = new Date();
            var d = new Date(t.dateValue);
            d.setHours(now.getHours(), now.getMinutes(), now.getSeconds(), now.getMilliseconds());//当前时、分、秒 by mrmsl on 2012-09-10 08:38:23
            t.dateValue = +d;
            me.setValue(d);
            delete me.cancelFocus;
            me.fireEvent('select', me, me.value);
            if (handler) {
                handler.call(me.scope || me, me, me.value);
            }
            // event handling is turned off on hide
            // when we are using the picker in a field
            // therefore onSelect comes AFTER the select
            // event.
            me.onSelect();
        }
    }
});

Ext.override(Ext.form.action.Submit, {
    handleResponse: function(response) {//自定义处理返回结果 by mrmsl on 2012-12-15 13:24:54
        var form = this.form,
            errorReader = form.errorReader,
            rs, errors, i, len, records;

        if (errorReader) {
            return errorReader.read(response);
        }

        return Ext.decode(response.responseText);
    }
});