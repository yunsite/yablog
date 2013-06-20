/**
 * 底层基础控制器，项目所有控制器都继承此类
 *
 * @file            app/controller/Yab.controller.Base.js
 * @version         0.1
 * @abstract
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-07-05 17:52:31
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.controller.Base', {
    extend: 'Ext.app.Controller',

    /**
     * 通用添加/编辑
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-07 21:45:44
     * @lastmodify      2013-01-14 10:21:54 by mrmsl
     *
     * @param {Object} data 当前标签数据
     * @param {Object} [options] Ext.ux.Form配置项
     *
     * @return {void} 无返回值
     */
    addAction: function(data, options) {
        var pkValue = intval(data[this.idProperty]);
        data[this.idProperty] = pkValue;
        var title = data.text;

        if (data.clone) {//复制
            title = lang('CLONE,CONTROLLER_NAME_' + data.controller);
        }
        else if (pkValue) {//编辑
            title = lang('EDIT,CONTROLLER_NAME_' + data.controller);
        }

        Ext.get(data.controller).update(title);
        pkValue && Yab.cmp.viewport.setPageTitle(data.controller, data.action, document.title.replace(data.text, title));
        this.formPanel(data, options);
        var field = this._formpanel.getForm().findField(this.idProperty);

        if (pkValue && (field.getValue() != pkValue || global('app_contextmenu_refresh'))) {//编辑，加载数据
            var extraParams = '';

            if (data.clone) {//复制
                extraParams = '&clone=true';
            }

            this._formpanel.load({
                waitMsg: lang('LOADING'),
                url: this.getActionUrl(data.controller, 'info', '{0}={1}{2}'.format(this.idProperty, pkValue, extraParams)),
                method: 'GET',
                success: this.loadEditDataSuccess,
                failure: function(form, action) {
                    commonFailure(action);
                }
            });
        }

        Yab.cmp.card.layout.setActiveItem(this._formpanel);
    },//end addAction

    /**
     * 搜索按钮
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-09-25 10:24:07
     * @lastmodify      2012-09-25 10:24:07 by mrmsl
     *
     * @protected
     *
     * @param {Function} handler 按钮操作
     *
     * @return {Object} Ext.button.Button搜索按钮配置项
     */
    btnSearch: function(handler) {
        return {
            text: lang('SEARCH'),
            iconCls: 'icon-search',
            name: 'submit',
            handler: handler
        };
    },

    /**
     * 通用提交表单按钮
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-28 09:22:48
     * @lastmodify      2013-01-14 10:23:55 by mrmsl
     *
     * @protected
     *
     * @return {Object} 按钮item配置
     */
    btnSubmit: function(submitTip) {
        var me = this;
        var labelWidth = global('app_labelWidth') || 100;//labelWidth
        var btnText = global('app_btnText') || lang('SAVE');//按钮文字

        global('app_labelWidth', false);
        global('app_btnText', false);

        return {
            xtype: 'fieldcontainer',
            fieldLabel: 'nothing',
            labelStyle: 'visibility:hidden;',
            labelWidth: labelWidth,
            layout: 'hbox',
            items: [{
                xtype: 'button',
                formBind: true,
                //disabled: true,//调用add()方法，表单会处于禁用状态，不能disabled by mrmsl 2012-08-31 12:42:34
                text: btnText,
                name: 'submit',
                handler: function() {
                    this.up('form').onSubmit(this);//onSubmit(this);
                }
            },
            Yab.Field.field().displayField(undefined === submitTip ? lang('SUBMIT_TIP') : '')//可按ctrl+enter提交 提示 by mrmsl on 2012-09-07 09:20:19
            ]
        }
    },//end btnSubmit

    /**
     * commit提交已更新记录
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-10 18:34:29
     * @lastmodify      2013-01-14 10:24:24 by mrmsl
     *
     * @protected
     *
     * @param {Array}  records      records数组
     * @param {Object} fieldValues  新键值对
     * @param {Object} selectModel  Ext.selection.Model
     *
     * @return {void} 无返回值
     */
    commitUpdatedRecords: function(records, fieldValues, selectModel) {
        Ext.each(records, function(item) {
            item.set(fieldValues);
            item.commit();
        });
        selectModel = this.selectModel || this._listgrid;
        selectModel.deselectAll && selectModel.deselectAll();//取消选择 by mrmsl on 2012-08-10 22:04:05

        if (selectModel.getChecked) {//treepanel by mrmsl on 2012-08-10 22:27:41
            Ext.each(selectModel.getChecked(), function(item) {
                item.set('checked', false);
            });
        };
    },
    /**
     * 通用ajax操作
     *
     * @protected
     *
     * @param {Object} options 参数
     * @param {String} options.action Ext.Ajax.request.url (required)
     * @param {Object/String} [options.params] Ext.Ajax.request.params
     * @param {Function} [options.callback] 操作完成回调
     * @param {Array} [options.callbackArgs] 回调参数
     * @param {Object} [options.pagingbar] Ext.toolbar.PagingBar
     * @param {Object} [options.store] Ext.data.Store
     * @param {String} [options.failedMsg=OPERATE,FAILURE语言项] 操作失败提示
     *
     * @return {void} 无返回值
     */
    commonAction: function(options) {
        var args = arguments;//, scope = scope || window;

        setLoading();

        Ext.Ajax.request({
            url: options.action,
            params: Ext.isString(options.data) ? Ext.Object.fromQueryString(options.data) : options.data,
            callback: function(opt, success, response) {
                setLoading(false);
                var data = Ext.decode(response.responseText, true);

                if (data && data.success) {

                    if (Ext.isFunction(options.callback)) {
                        options.callback.apply(options.scope || window, options.callbackArgs);
                    }

                    options.pagingbar && options.pagingbar.doRefresh();
                    options.store && options.store.load();
                    Alert(data.msg);
                }
                else {
                    error(data && data.msg ? data.msg : options.failedMsg || lang('OPERATE,FAILURE'));
                }
            }
        });
    },//end commonAction

    /**
     * 通用删除
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-22 22:13:12
     * @lastmodify      2013-01-14 10:31:49 by mrmsl
     *
     * @protected
     *
     * @param {Mixed}  record      record数据或id串
     * @param {String} confirmText 确认信息
     *
     * @return {void} 无返回值
     */
    'delete': function(record, confirmText) {
        var pkValue;
        var controller = this.getControllerName();

        if (Ext.isString(record)) {//选中删除
            pkValue = record;
            confirmText = lang('YOU_CONFIRM,DELETE,SELECTED,RECORD');

            if (controller == 'role') {//角色
                confirmText = lang('DELETE_TIP_ROLE,SELECTED,RECORD');
            }
        }
        else {//点击删除
            pkValue = record.get(this.idProperty);

            if (controller == 'role') {//角色
                confirmText = confirmText;
            }
            else {
                confirmText = lang('YOU_CONFIRM,DELETE') + confirmText;
            }
        }

        var options = {
            action: this.getActionUrl(false, 'delete'),
            data: this.idProperty + '=' + pkValue,
            confirmText: confirmText,
            failedMsg: lang('DELETE,FAILURE'),
            scope: this,
            store: this.store()
        };

        if (controller == 'role') {//角色
            delete options.store;
            options.callback = function() {
                this.store().remove(Ext.isString(record) ? this.selectModel.getSelection() : record);
            };
        }

        this.myConfirm(options);
    },//end delete

    /**
     * 删除博客,微博静态文件
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-17 14:52:21
     *
     * @protected
     *
     * @param {Mixed}  record      record数据或id串
     * @param {String} confirmText 确认信息
     *
     * @return {void} 无返回值
     */
    deleteBlogHtml: function(record, confirmText) {
        var pkValue;
        var controller = this.getControllerName();

        if (Ext.isString(record)) {//选中删除
            pkValue = record;
            confirmText = lang('SELECTED,RECORD');
        }
        else {//点击删除
            pkValue = record.get(this.idProperty);
        }

        var options = {
            action: this.getActionUrl(false, 'deleteBlogHtml'),
            data: this.idProperty + '=' + pkValue,
            confirmText: lang('YOU_CONFIRM,DELETE') + confirmText + lang('STATIC_PAGE'),
            failedMsg: lang('DELETE,FAILURE'),
            scope: this,
            callback: function () {
                this.selectModel.deselectAll();
            }
        };

        this.myConfirm(options);
    },//end clearCache

    /**
     * actioncolumn通用删除item
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-23 13:52:34
     * @lastmodify      2013-01-14 10:32:24 by mrmsl
     *
     * @protected
     *
     * @param {String} [confirmField=null] 删除提示字段
     *
     * @return {Object} actioncolumn item 配置
     */
    deleteColumnItem: function(confirmField) {
        var me = this;

        return {
            text: lang('DELETE'),
            handler: function(grid, rowIndex, cellIndex) {
                var record = grid.getStore().getAt(rowIndex),
                confirmText = null === confirmField ? lang('CN_CI,RECORD') : (confirmField ? htmlspecialchars(record.get(confirmField)) : '')
                me['delete'](record, confirmField ? '<span class="font-red font-bold">' + confirmText + '</span>' : confirmText);
            }
        };
    },

    /**
     * tbar通用删除item
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-22 22:28:08
     * @lastmodify      2013-01-14 10:34:02 by mrmsl
     *
     * @protected
     *
     * @param {String} [text=DELETE] 删除文字语言包。默认DELETE
     *
     * @return {Object} tbar menuitem 配置
     */
    deleteItem: function(text) {
        var me = this;

        return {
            text: lang(text || 'DELETE'),
            handler: function() {
                var pkValue = me.hasSelect(me.selectModel || me._listgrid);
                pkValue && me['delete'](pkValue);
            }
        }
    },

    /**
     * 编辑操作
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-23 11:10:32
     * @lastmodify      2013-01-14 10:32:45 by mrmsl
     *
     * @protected
     *
     * @param {Object} record record数据
     * @param {Boolean} [setEditRecord=undefined] true需要this.setEditRecord()
     * @param {String} [extraParam=undefined] url额外参数信息
     *
     * @return {void} 无返回值
     */
    edit: function(record, setEditRecord, extraParam) {
        setEditRecord && this.setEditRecord(record);//编辑record，提交后，直接form.updateRecord()，更新数据
        Yab.History.push('{action}&{idProperty}={pkValue}&back={back}{extraParam}'.format({
            action: this.getAction(false, 'add'),
            idProperty: this.idProperty,
            pkValue: record.get(this.idProperty),
            extraParam: extraParam ? '&' + extraParam : '',
            back: encodeURIComponent(Ext.History.getToken())
        }));
    },

    /**
     * actioncolumn通用编辑item
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-23 11:22:06
     * @lastmodify      2013-01-14 10:34:17 by mrmsl
     *
     * @protected
     *
     * @param {Boolean} [setEditRecord=undefined] true需要this.setEditRecord()
     *
     * @return {Object} actioncolumn item 配置
     */
    editColumnItem: function(setEditRecord) {
        var me = this;

        return {
            text: lang('EDIT'),
            handler: function(grid, rowIndex, cellIndex) {
                me.edit(grid.getStore().getAt(rowIndex), setEditRecord);
            }
        };
    },

    /**
     * 筛选选中记录
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-10 16:59:06
     * @lastmodify      2013-01-14 10:34:53 by mrmsl
     *
     * @protected
     *
     * @param {Array}  selection 选中record
     * @param {String} field     字段
     * @param {String} val       保留值
     *
     * @return {Array} 满足条件record组成的数组
     */
    filterSelection: function(selection, field, val) {
        var result = [];

        Ext.each(selection, function(item, index) {

            if (Ext.isArray(val) && -1 != Ext.Array.indexOf(val, item.get(field)) || item.get(field) == val) {
                result.push(item);
            }
        });

        return result;
    },

    /**
     * 通用表单
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-07 21:55:32
     * @lastmodify      2013-01-14 10:38:45 by mrmsl
     *
     * @protected
     *
     * @param {Object} data 当前标签数据
     * @param {Object} [options] Ext.ux.Form配置项
     *
     * @return {Object} Ext.form.Panel
     */
    formPanel: function(data, options) {
        var me = this;
        var pkValue = data[this.idProperty];
        var destroy = false;//是否需要销毁表单

        if (this._formpanel) {
            var form = this._formpanel.getForm();

            if (data.clone) {//复制 by mrmsl on 2013-04-11 20:25:22
                destroy = true;
            }
            else if (form.findField(this.idProperty).getValue() != pkValue) {//主键值不等
                destroy = true;
            }
            //父级id不等
            else if (form.findField('parent_id') && intval(form.findField('parent_id').getValue()) != intval(data.parent_id)) {
                destroy = true;
            }
            //管理员，所属角色id不等
            else if (this.getControllerName() == 'admin' && intval(form.findField('role_id').getValue()) != intval(data.role_id)) {
                destroy = true;
            }
        }

        if (!this._formpanel || destroy) {
            this._formpanel && this._formpanel.destroy();

            var cfg = {
                tabData: data,
                controller: this
            };
            options && Ext.apply(cfg, options);

            this._formpanel = Ext.create('Yab.ux.Form', cfg);
        }

        return this._formpanel;
    },//formPanel

    /**
     * 获取controller=控制器名称&action=操作方法名称
     *
     * @param {String} [controller=当前控制器名称] 控制器
     * @param {String} [action=当前操作方法名称] 操作
     *
     * @return {String} controller=控制器名称&action=操作方法名称
     */
    getAction: function(controller, action) {
        return 'controller={0}&action={1}'.format(controller || this.getControllerName(), action || this.getActionName());
    },

    /**
     * 获取当前url action参数值，即操作名称
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-02 11:46:22
     * @lastmodify      2013-01-14 10:42:03 by mrmsl
     *
     * @protected
     *
     * @return {String} 操作名称
     */
    getActionName: function() {
        return _GET('action');
    },

    /**
     * 获取请求url
     *
     * @protected
     *
     * @param {String} [controller=当前控制器名称] 控制器
     * @param {String} [action=当前操作方法名称] 操作
     * @param {String} [queryString] 查询字符串
     *
     * @return {String} 请求url
     */
    getActionUrl: function(controller, action, queryString) {
        controller = controller || this.getControllerName();
        action = action || this.getActionName();
        var url = controller + '/' + action + (queryString ? '?' + queryString : '');

        return getActionUrl(url);
    },

    /**
     * 获取转化成小写的控制器名称
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-02 11:35:18
     * @lastmodify      2013-01-14 10:44:43 by mrmsl
     *
     * @return {String} 转化成小写的控制器名称
     */
    getControllerName: function() {
        return Ext.String.uncapitalize(this.$className.split('.').pop());
    },

    /**
     * 获取编辑的record
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-11 17:01:47
     * @lastmodify      2013-01-14 10:49:06 by mrmsl
     *
     * @protected
     *
     * @return {Mixed} Ext.data.Model 编辑record或者undefined
     */
    getEditRecord: function() {
        return this['_' + this.getControllerName() + 'editRecord'];
    },

    /**
     * 自动获取Ext.toolbar.ToolBar输入框查询数据
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-07-28 14:47:13
     * @lastmodify      2013-03-31 19:00:05 by mrmsl
     *
     * @protected
     *
     * @param {Object} tbar Ext.toolbar.ToolBar
     * @param {Object} data 初始数据
     *
     * @return {Object} 输入框查询数据
     */
    getQueryData: function(tbar, data) {
        Ext.each(tbar.items.items, function(item) {

            if (item.isXType('textfield') || item.isXType('hiddenfield')) {
                data[item.itemId || item.name] = item.getSubmitValue();//getSubmitValue() by mrmsl on 2012-09-08 23:17:13
            }
        });

        return data;
    },

    /**
     * 判断是否有选择
     *
     * @protected
     *
     * @param {Object} selModel selModel
     * @param {Mixed}  [internalId=undefined] internalId
     * @param {String} [separator=,]  分割符
     *
     * @return {Mixed} 如果有选择,返回用separator分割的字符串，否则返回false，并提示
     */
    hasSelect: function(selModel, internalId, separator) {
        var selection = selModel.getSelection ? selModel.getSelection() : selModel.getChecked();
        separator = separator || ',';

        if (selection.length == 0) {
            !arguments[3] && Alert(lang('SELECT_ONE'), false);
            return false;
        }

        if (internalId === undefined) {//返回内部主键
            return Ext.Array.pluck(selection, 'internalId').join(separator);
        }
        else if (Ext.isFunction(internalId)) {
            return internalId.call(selection);
        }
        else if (Ext.isArray(internalId)) {//过滤结果 by mrmsl on 2012-08-10 14:09:05
            selection = this.filterSelection(selection, internalId[0], internalId[1]);

            if (Ext.isEmpty(selection)) {
                !arguments[3] && info(lang('SELECTED,RECORD,DO_NOT_NEED_TO_BE_UPDATE'), null, true);
                return false;
            }

            internalId = this.idProperty;
        }

        var ids = [];

        if (Ext.isString(internalId) && internalId.indexOf(',') > -1) {
            var arr = internalId.split(',');

            Ext.each(selection, function(item) {
                var v = [];

                Ext.each(arr, function(a) {
                    v.push(item.get(a));
                });

                ids.push(v.join('|'));
            });
        }
        else {
            Ext.each(selection, function(item) {
                ids.push(item.get(internalId || this.idProperty));
            }, this);
        }

        return [ids.join(separator), selection];
    },//end hasSelect

    /**
     * 组装查询字符串
     *
     * @author          mrmsl
     * @date            2012-07-26 11:45:42
     * @lastmodify      2013-01-14 10:51:35 by mrmsl
     *
     * @protected
     *
     * @param {Mixed}  data  数据，通常为标签数据
     * @param {String} field 字段
     *
     * @return {String} 查询字符串
     */
    httpBuildQuery: function(data, field) {

        if (Ext.isString(data)) {//已经组装，直接返回
            return data;
        }

        //增加是否为数组控制 by mrmsl 2012-07-27 15:47:38
        var fieldArr = Ext.isArray(field) ? field : field.split(',');
        var resultArr = [];

        Ext.each(fieldArr, function(field) {

           if (data[field] !== undefined) {
               resultArr.push(field + '=' + encodeURIComponent(data[field]));
           }
        });

        return resultArr.join('&');
    },

    /**
     * 通用列表
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-02 13:26:23
     * @lastmodify      2013-01-14 10:52:11 by mrmsl
     *
     * @protected
     *
     * @param {Object} data 数据
     * @param {Object} [options] Yab.ux.Grid/Yab.ux.TreeGrid配置项
     *
     * @return {void} 无返回值
     */
    listAction: function(data, options) {
        var queryData = this.queryField ? this.httpBuildQuery(data, this.queryField) : null;
        //是否需要重新加载数据
        var loadData = this._store && this._store._data !== queryData || global('app_contextmenu_refresh');
        var store = this.store(data);

        if (data.sort) {//可排序，通常为普通列表

            if (loadData || !this._listgrid) {//加载数据
                store.loadPage(data.page);
            }

            Yab.cmp.card.layout.setActiveItem(this.listgrid(data, options));

            if (this._listgrid.toolbar && this.queryField) {
                this.setQueryData(this._listgrid.toolbar, data, this.queryField.split(',').slice(2));
            }

            this.setSort.call(this._listgrid, data.sort, data.order);
        }
        else {//不可排序或treePanel
            loadData && store.load();//加载数据
            Yab.cmp.card.layout.setActiveItem(this.treegrid(data, options));

            if (this._listgrid.toolbar && this.queryField) {
                this.setQueryData(this._listgrid.toolbar, data, this.queryField);
            }
        }
    },//end listAction

    /**
     * 通用列表grid
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-07 16:27:25
     * @lastmodify      2013-01-14 10:53:40 by mrmsl
     *
     * @protected
     *
     * @param {Object} data 数据
     * @param {Object} [options] Yab.ux.Grid/Yab.ux.TreeGrid配置项
     *
     * @return {Object} Yab.ux.Grid/Yab.ux.TreeGrid实例
     */
    listgrid: function(data, options) {
        var me = this;

        if (!this._listgrid) {
            var cfg = {
                controller: this,
                tabData: data
            };

            options && Ext.apply(cfg, options);

            this._listgrid = Ext.create(cfg.appgridtype || 'Yab.ux.Grid', cfg);
        }

        return this._listgrid;
    },

    /**
     * 通用列表点击事件,适用于设置0,1状态,如is_show,is_enable
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-24 22:23:35
     * @lastmodify      2013-01-14 10:17:32 by mrmsl
     *
     * @protected
     *
     * @param {Object} record record数据
     * @param {Object} event  鼠标点击事件
     * @param {String} [field=is_show] 字段
     *
     * @return {void} 无返回值
     */
    listitemclick: function(record, event, field) {
        element = Ext.fly(event.getTarget());
        field = field || 'is_show';
        var idProperty = this.idProperty;

        if (element.hasCls(field)) {//显示与不显示

            var selection = [], pkValue = [];

            if (record.cascadeBy) {//treepanel
                record.cascadeBy(function(node) {
                    selection.push(node);
                    pkValue.push(node.get(idProperty));
                });
            }
            else {//gridpanel
                selection = [record];
                pkValue = record.get(this.idProperty);
            }

            this.setLoadingImg(element);
            this.setOneOrZero(record.get(this.idProperty), record.get(field) == 1 ? 0 : 1, field, null, selection, true);
        }
    },


     /**
     * 列表右键事件
     *
     * @protected
     */
    listitemcontextmenu: Ext.emptyFn,//列表右键事件 by mrmsl on 2012-08-09 21:42:28

    /**
     * 编辑，加载数据成功回调函数
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-17 12:59:45
     * @lastmodify      2013-01-14 11:32:37 by mrmsl
     *
     * @protected
     *
     * @param {Object} form   Ext.form.Panel
     * @param {Object} action Ext.form.Action
     *
     * @return {void} 无返回值
     */
    loadEditDataSuccess: Ext.emptyFn,//编辑，加载数据成功回调函数 by mrmsl on 2012-08-08 11:15:03

    /**
     * 确认操作
     *
     * @protected
     *
     * @param {Object} options 传递给this.commonAction()参数
     *
     * @return {void} 无返回值
     */
    myConfirm: function(options) {
        var me = this;

        Ext.Msg.confirm(getMsgTitle(), options.confirmText + '？', function(btn) {
            btn == 'yes' && me.commonAction(options);
        });
    },

    /**
     * 加载是否小图片
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-22 13:30:37
     * @lastmodify      2013-01-14 10:58:22 by mrmsl
     *
     * @protected
     *
     * @param {String} v   值
     * @param {String} [cls] 图片class
     *
     * @return {String} 图片标签
     */
    renderYesNoImg: function(v, cls) {
        return '<img alt="" src="{0}" class="img-yesno{1}" />'.format(C.images[v == 0 ? 'no' : 'yes'], cls ? ' ' + cls : '');
    },

    /**
     * 渲染时间戳字段
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-09-08 12:36:23
     * @lastmodify      2013-02-26 17:03:18 by mrmsl
     *
     * @protected
     *
     * @param {String} v   值
     *
     * @return {String} 时间格式
     */
    renderDatetime: function(v) {
        return v == 0 ? '' : date(System.sys_timezone_datetime_format, intval(v) * 1000);
    },

    /**
     * 标红搜索关键字
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-11 17:02:39
     * @lastmodify      2013-01-14 11:00:20 by mrmsl
     *
     * @protected
     *
     * @param {String} v      值
     * @param {String} column 字段
     *
     * @return {String} 搜索关键字标红的字段值
     */
    searchReplaceRenderer: function(v, column) {
        var data = Ext.Object.fromQueryString(Ext.History.getToken());

        if (data.keyword && data.column == column) {
            return v.replace(new RegExp('(' + data.keyword + ')', 'gi'), '<span style="color: red">$1</span>')
        }

        return v;
    },


    /**
     * 设置编辑的record
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-11 17:02:39
     * @lastmodify      2012-08-11 17:02:39 by mrmsl
     *
     * @protected
     *
     * @return {void} 无返回值
     */
    setEditRecord: function(record) {
        this['_' + this.getControllerName() + 'editRecord'] = record;
    },

    /**
     * 设置导航
     *
     * @protected
     *
     * @param {Object} data 标签数据
     *
     * @return {Object} 当前标签数据
     */
    setHistory: function(data) {
        var tab = Yab.cmp.tabs.getActiveTab();
        Ext.apply(tab, data);
        var href = Ext.Object.toQueryString(tab);
        Yab.History.push(href, true);
        Ext.get(tab.controller).dom.href = '#' + href;

        return tab;//返回标签数据 by mrmsl on 2012-07-27 15:42:20
    },

    /**
     * 设置正在处理中小图片，适用于listitemclick点击事件
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-22 14:20:29
     * @lastmodify      2013-01-14 11:01:35 by mrmsl
     *
     * @protected
     *
     * @return {void} 无返回值
     */
    setLoadingImg: function(element) {
        element.set({
            src: C.images.loading
        });
    },

    /**
     * 显示、隐藏
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-24 15:49:28
     * @lastmodify      2013-01-14 11:02:03 by mrmsl
     *
     * @protected
     *
     * @param {String} pkValue     主键id串
     * @param {Number} val         0,1值
     * @param {String} field       字段
     * @param {String} confirmText 确认信息
     * @param {Object} selection   选中record
     * @param {Boolean} [skipConfirm=undefined] true跳过确认操作提示
     *
     * @return {void} 无返回值
     */
    setOneOrZero: function(pkValue, val, field, confirmText, selection, skipConfirm) {
        var fieldMap = {
            is_show: ['HIDE', 'SHOW'],//显示与隐藏
            is_enable: ['DISABLED', 'ENABLE'],//启用与禁用
            is_restrict: ['RELEASE,CN_BANGDING,LOGIN', 'CN_BANGDING,LOGIN'],//绑定与不绑定登陆管理员
            is_lock: ['RELEASE,LOCK', 'LOCK'],//锁定与不锁定管理员
            is_delete: ['CN_WEI,DELETE', 'CN_YI,DELETE'],//删除与未删除
            is_issue: ['CN_WEI,ISSUE', 'CN_YI,ISSUE'],//发布与未发布
            auditing: ['CN_WEI,AUDITING', 'CN_YI,PASS', 'CN_WEI,PASS']//审核状态
        };
        var data = {}, a = field.indexOf('is_') == 0 ? field.substr(3) : field;
        data[this.idProperty] = pkValue;
        data[field] = val;
        var setted = {};

        setted[field] = val;

        var options = {
            action: this.getActionUrl(false, 'delete' == a ? 'isDelete' : a),
            data: data,
            confirmText: confirmText,
            failedMsg: lang(fieldMap[field][val]) + lang('FAILURE'),
            scope: this,
            callback: this.commitUpdatedRecords,
            callbackArgs: [selection, setted]
        };

        skipConfirm ? this.commonAction(options) : this.myConfirm(options);
    },//end setOneOrZero

    /**
     * 自动设置Ext.toolbar.ToolBar输入框查询数据
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-07-28 14:50:54
     * @lastmodify      2013-01-14 11:03:33 by mrmsl
     *
     * @param {Object} tbar  Ext.toolbar.ToolBar
     * @param {Object} data  查询数据
     * @param {Mixed}  field 查询字段
     *
     * @return {void} 无返回值
     */
    setQueryData: function(tbar, data, field) {
        field = Ext.isArray(field) ? field : field.split(',');
        Ext.each(field, function(itemId) {
            var component = tbar.getComponent(itemId);

            if (component) {
                'treepicker' == component.getXType() ? component.setRawValue(data[itemId]) : component.setValue(data[itemId]);
            }
        });
    },

    /**
     * 设置列表排序className
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-02 12:56:22
     * @lastmodify      2013-01-14 11:04:31 by mrmsl
     *
     * @protected
     *
     * @param {String} sort  排序字段
     * @param {String} order 排序
     *
     * @return {void} 无返回值
     */
    setSort: function(sort, order) {
        var classes = [C.headerSortCls.ASC, C.headerSortCls.DESC];
        order = C.headerSortCls[order];

        if (order) {//存在排序
            Ext.each(this.columns, function(item) {
                item.removeCls(classes);
                item.dataIndex == sort && item.addCls(order);
            });
        }
    },

    /**
     * treeStore.on('load') 设置总数信息
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-13 14:54:15
     * @lastmodify      2013-01-14 11:05:18 by mrmsl
     *
     * @protected
     *
     * @param {Object} store Ext.store.TreeStore
     *
     * @return {void} 无返回值
     */
    setTreeStoreOnLoad: function(store) {
        var me = this;
        store.on('load', function() {
            var data = this.proxy.reader.rawData;

            if (data && data.total !== undefined) {
                me._listgrid.getComponent('bbar').getComponent('total_records').update(lang('TOTAL_RECORDS').format(data.total));
            }
        });
    },

    /**
     * 通用提交表单成功回调
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-28 10:32:48
     * @lastmodify      2013-01-14 11:06:09 by mrmsl
     *
     * @protected
     *
     * @param {Object} form Ext.form.Panel
     *
     * @return {void} 无返回值
     */
    submitCallback: function(form) {
        global('app_destroy_formpanel', true);//销毁表单

        if (this._listgrid && form.findField(this.idProperty).getValue() == 0 ) {
            this.store().load();
        }
    },

    /**
     * Ext.form.tabPanel标签选项卡
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-07-23 21:50:11
     * @lastmodify      2013-01-14 11:07:33 by mrmsl
     *
     * @protected
     *
     * @param {String} activeTab 活动标签itemId
     *
     * @return {void} 无返回值
     */
    tabs: function(activeTab) {
        Ext.require('Yab.ux.TabPanel', function() {
            var me = this;

            if (!this._tabs) {
                this._tabs = Ext.create('Yab.ux.TabPanel', {
                    controller: this
                });
            };

            Yab.cmp.card.layout.setActiveItem(this._tabs);
            this._tabs.setActiveTab(activeTab);

            var key = activeTab + 'form';

            if (!this[key] || global('app_contextmenu_refresh')) {//首次加载或右键刷新 by mrmsl on 2012-08-27 17:43:54
                var tab = this._tabs.getActiveTab();
                tab.removeAll();//删除所有输入域
                this._tabs.loadFormField(function(callback) {
                    items = callback.call(me);
                    global('app_labelWidth', global('app_labelWidth') || 180);

                    if (items.length) {
                        items.push(me.btnSubmit());
                        tab.add(items);
                    }
                    else {//无表单域，显示添加表单域按钮
                        tab.add({
                            xtype: 'button',
                            text: lang('ADD,CONTROLLER_NAME_FIELD'),
                            handler: function() {
                                Yab.History.push('#' + me.getAction('field', 'add&parent_id=' + items + '&back=' + encodeURIComponent(location.href)));
                            }
                        });
                    }
                });//加载表单输入域

                this[key] = true;
            }
        }, this);
    },//end tabs

    /**
     * a将被替换为b 提示
     *
     * @protected
     *
     * @param {String} find      查找
     * @param {String} replaced  替换
     * @param {String} [separator=''] 分割符
     * @param {Array}  [html=[]] html数组
     *
     * @return {String} 替换后的用separator分割的提示信息
     */
    toBeReplaced: function(find, replaced, separator, html) {
        html = html || [];

        if (typeof(find) == 'object') {//批量设置
            Ext.Object.each(find, function(index, item) {
                html.push(this.toBeReplaced(index, item, separator));
            }, this);
        }
        else {
            html.push(TEXT.strong(find) + lang('TO_BE_REPLACED') + TEXT.red(replaced));
        }

        return html.join(separator || '');
    },

    /**
     * 通用列表treegrid
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-07 17:04:44
     * @lastmodify      2013-01-14 11:09:52 by mrmsl
     *
     * @protected
     *
     * @param {Object} data 当前标签数据
     * @param {Object} options Yab.ux.TreeGrid配置项
     *
     * @return {Object} Yab.ux.TreeGrid实例
     */
    treegrid: function(data, options) {
        Ext.apply(options, {
            appgridtype: 'Yab.ux.TreeGrid',
            rootVisible: false,
            columnLines: true,
            useArrows: true
        });

        return this.listgrid(data, options);
    },

    /**
     * treepanel 底部工具栏
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-13 14:49:49
     * @lastmodify      2013-01-14 11:11:06 by mrmsl
     *
     * @protected
     *
     * @return {Object} Ext.toolbar.toolBar工具栏配置项
     */
    treegridbbar: function() {
        return {
            xtype: 'toolbar',
            dock: 'bottom',
            itemId: 'bbar',
            items: ['->', {
                xtype: 'component',
                html: lang('LOADING'),
                itemId: 'total_records',
                width: 120,
                style: 'padding: 4px;text-align: right'
            }]
        };
    }
});
