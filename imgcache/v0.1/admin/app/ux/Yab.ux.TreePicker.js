/**
 * A Picker field that contains a tree panel on its popup, enabling selection of tree nodes.
 * 下拉树，摘自Extjs4.1.1
 *
 * @lastmodify      $Date$ $Author$
 */
Ext.define('Yab.ux.TreePicker', {
    extend: 'Ext.form.field.Picker',
    /**
     * @cfg {Boolean}
     * 多选时，选中子节点后是否同时选中父节点
     */
    bubbleCheck: false,
    /**
     * @cfg {Object} (required)
     * 列
     */
    columns: null,
    /**
     * @cfg {String}
     * 显示文本字段
     */
    displayField: 'text',
    /**
     * @cfg {String} (required)
     * 值字段
     */
    valueField: null,
    /**
     * @cfg {Boolean} editable
     * 选择框是否可编辑
     */
    editable: false,
    /**
     * @cfg {Number}
     * 下拉框最大高度
     */
    maxPickerHeight: 400,
    /**
     * @cfg {Number}
     * 下拉框最小高度
     */
    minPickerHeight: 100,
    /**
     * @cfg {Boolean}
     * 多选
     */
    multiSelect: false,
    /**
     * @cfg {String} [nodeField=null]
     * 节点域
     */
    nodeField: null,
    /**
     * @cfg {Object} [pickerDockedItems=null]
     * dockedItems
     */
    pickerDockedItems: null,
    /**
     * @cfg {Number}
     * 下拉框高度
     */
    pickerHight: 300,
    /**
     * @cfg {String}
     * 主键
     */
    pickerIdProperty: 'id',
    /**
     * @cfg {Object} [pickerListeners=null]
     * 下拉树监听事件
     */
    pickerListeners: null,
    /**
     * @cfg {Boolean}
     * 按tab键选择
     */
    selectOnTab: true,
    /**
     * @cfg {Array}
     * 选中文字
     */
    selectText: [],
    /**
     * @cfg {Array}
     * 选中值
     */
    selectValue: [],
    /**
     * @cfg {Boolean}
     * 单选时，选中id表单域，true表示前面一个表单域，否则为表单域名
     */
    singleSelectValueField: true,
    /**
     * @cfg {Object} [store=null] (required)
     * store
     */
    store: null,
    /**
     * @cfg {String}
     * triggerCls
     */
    triggerCls: Ext.baseCSSPrefix + 'form-arrow-trigger',
    /**
     * @cfg {String}
     * 多选值分割符
     */
    valueSepartor: ',',
     /**
     * @cfg {String}
     * xtype
     */
    xtype: 'treepicker',
    /**
     * @cfg {Number}
     * 宽度
     */
    width: 300,

    /**
     * Aligns the picker to the input element
     *
     * @private
     *
     * @return {void} 无返回值
     */
    alignPicker: function() {
        var picker;

        if (this.isExpanded) {
            picker = this.getPicker();
            if (this.matchFieldWidth) {
                // Auto the height (it will be constrained by max height)
                picker.setWidth(this.bodyEl.getWidth());
            }
            if (picker.isFloating()) {
                this.doAlign();
            }
        }
    },
    /**
     * createPicker
     *
     * @private
     *
     * @return {void} 无返回值
     */
    createPicker: function() {
        var picker = this.treepanel || Ext.create('Ext.tree.Panel', {
                autoScroll: true,
                columns: this.columns,
                displayField: this.displayField,
                dockedItems: this.pickerDockedItems,
                floating: true,
                height: this.pickerHight,
                hidden: true,
                listeners: {
                    itemclick: Ext.bind(this.onItemClick, this),
                    render: Ext.bind(this.pickerRender, this),//菜单完成渲染
                    checkchange: Ext.bind(this.pickerCheckChange, this)//子节点状态与父节点状态一致；选中子节点，同时选中父节点 by mrmsl on 22:17 2012-7-16
                },
                manageHeight: true,
                multiSelect: this.multiSelect === undefined ? false : this.multiSelect,
                shadow: false,
                store: this.store,
                rootVisible: this.rootVisible === undefined ? false : this.rootVisible,
                useArrows: this.useArrows === undefined ? true : this.useArrows,
                viewConfig: {
                    listeners: {
                        render: function(view) {
                            view.getEl().on('keypress', this.onPickerKeypress, this);
                        }
                    }
                },
                constructor: function() {
                    this.callParent([arguments]);
                }
            }),
            view = picker.getView();

        view.on('render', this.setPickerViewStyles, this);

        if (Ext.isIE9 && Ext.isStrict) {
            view.on('highlightitem', this.repaintPickerView, this);
            view.on('unhighlightitem', this.repaintPickerView, this);
            view.on('afteritemexpand', this.repaintPickerView, this);
            view.on('afteritemcollapse', this.repaintPickerView, this);
        }

        return picker;
    },//end createPicker

    /**
     * initComponent
     *
     * @private
     *
     * @return {void} 无返回值
     */
    initComponent: function() {
        this.callParent(arguments);
        this.addEvents('select');
        this.store.on('load', this.storeOnLoad, this);
    },

    /**
     * Returns the current data value of the field (the idProperty of the record)
     *
     * @return {Number}
     */
    getValue: function() {
        return this.multiSelect ? this.selectValue.join(this.valueSeparator) : this.value;
    },

    /**
     * Runs when the picker is expanded.  Selects the appropriate tree node based on the value of the input element,
     * and focuses the picker so that keyboard navigation will work.
     *
     * @private
     *
     * @return {void} 无返回值
     */
    onExpand: function() {
        var picker = this.picker;

        if (this.nodeField) {//节点域
            var path = this.nodeField.getValue().split(',');
            //path.pop();
            path.unshift(picker.getRootNode().getPath());
            picker.selectPath(path.join('/'));
        }
        else if (this.singleSelectValueField && this.singleSelectValueField !== true) {//单选
            value = this.singleSelectValueField.getValue();

            var record = value == '' ? null : picker.getRootNode().findChild(this.pickerIdProperty, value, true);

            record && picker.selectPath(record.getPath());
        }

        Ext.defer(function() {
            picker.getView().focus();
        }, 1);
    },

    /**
     * Handles a click even on a tree node
     *
     * @private
     *
     * @param {Object} view Ext.tree.View
     * @param {Object} record Ext.data.Model
     * @param {Object} node HTMLElement
     * @param {Number} rowIndex
     * @param {Object} e Ext.EventObject
     *
     * @return {void} 无返回值
     */
    onItemClick: function(view, record, node, rowIndex, e) {
        this.selectItem(record);
    },

    /**
     * Handles a keypress event on the picker element
     *
     * @private
     *
     * @param {Object} e Ext.EventObject
     * @param {Object} el HTMLElement
     *
     * @return {void} 无返回值
     */
    onPickerKeypress: function(e, el) {
        var key = e.getKey();

        if(key === e.ENTER || (key === e.TAB && this.selectOnTab)) {
            this.selectItem(this.picker.getSelectionModel().getSelection()[0]);
        }
    },

    /**
     * 下拉树多选选择变动事件
     *
     * @private
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-06 09:13:51
     * @lastmodify      2013-01-12 21:09:59 by mrmsl
     *
     * @param {Object}  node    节点
     * @param {Boolean} checked 节点选中状态
     *
     * @return {void} 无返回值
     */
    pickerCheckChange: function(node, checked) {

        if (this.multiSelect) {
            this.bubbleCheck && checked && node.bubble(function(record) {
                !record.isRoot() && record.set('checked', checked);
            });
            !node.isLeaf() && node.cascadeBy(function(record) {
                record.set('checked', checked);
            });
        }
    },

    /**
     * 下拉树多选选择变动事件
     *
     * @private
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-06 09:13:51
     * @lastmodify      2013-01-12 21:10:32 by mrmsl
     *
     * @param {String} valueField 值字段
     *
     * @return {Object} this Yab.ux.TreePicker
     */
    pickerCheckedValue: function(valueField) {
        valueField = valueField || this.valueField || this.pickerIdProperty || record.idProperty || 'id';
        this.selectValue = [];
        this.selectText = [];

        Ext.each(this.picker.getChecked(), function(item) {
            this.selectValue.push(item.get(valueField));
            this.selectText.push(item.get(this.displayField));
        }, this);

        return this;
    },

    /**
     * 下拉树渲染完成事件
     *
     * @private
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-08-06 09:10:31
     * @lastmodify      2013-01-12 21:10:51 by mrmsl
     *
     * @return {void} 无返回值
     */
    pickerRender: function() {
        this.getPicker().el.on({//阻止默认行为
            click: Ext.emptyFn,
            delegate: 'a',
            preventDefault: true
        });

        var form = this.up('form') ? this.up('form').getForm() : null;

        if (!this.multiSelect && this.singleSelectValueField) {//单选选中值表单域

            if (Ext.isString(this.singleSelectValueField)) {//表单域名
                this.singleSelectValueField = form.findField(this.singleSelectValueField);
            }
            else {//前面一个表单域
                this.singleSelectValueField = this.previousSibling();
            }
        }

        this.nodeField = this.nodeField ? form.findField(this.nodeField) : this.nodeField;//节点域
    },

    /**
     * repaints the tree view
     *
     * @private
     *
     * @return {void} 无返回值
     */
    repaintPickerView: function() {
        var style = this.picker.getView().getEl().dom.style;

        // can't use Element.repaint because it contains a setTimeout, which results in a flicker effect
        style.display = style.display;
    },

    /**
     * Changes the selection to a given record and closes the picker
     *
     * @private
     *
     * @param {Object} record Ext.data.Model
     *
     * @return {void} 无返回值
     */
    selectItem: function(record) {
        var me = this;
        var valueField = this.valueField || this.pickerIdProperty || record.idProperty || 'id';

        if (!this.multiSelect) {
            this.setValue(record.get(valueField));
            this.onTriggerClick();
            this.inputEl.focus();
            this.singleSelectValueField && this.singleSelectValueField !== true && this.singleSelectValueField.setValue(record.get(valueField));
            this.nodeField && this.nodeField.setValue(record.getPath().split('/').slice(2).join(this.valueSepartor));
        }
        else {
            this.pickerCheckedValue(valueField).setValue();
        }

        this.fireEvent('select', me, record);
    },

    /**
     * Sets min/max height styles on the tree picker's view element after it is rendered.
     *
     * @private
     *
     * @param {Object} view Ext.tree.View
     *
     * @return {void} 无返回值
     */
    setPickerViewStyles: function(view) {
        view.getEl().setStyle({
            'min-height': this.minPickerHeight + 'px',
            'max-height': this.maxPickerHeight + 'px'
        });
    },

    /**
     * Sets the specified value into the field
     *
     * @param {Mixed} value
     *
     * @return {Object} this Yab.ux.TreePicker
     */
    setValue: function(value, justSet) {

        if (this.multiSelect) {
            this.setRawValue(this.selectText.join(','));
        }
        else if (justSet) {
            this.value = value;
            this.setRawValue(value);
        }
        else {

            var me = this, record;

            this.value = value;

            if (this.store.loading) {
                // Called while the Store is loading. Ensure it is processed by the onLoad method.
                return me;
            }

            // try to find a record in the store that matches the value
            record = value ? this.store.getNodeById(value) : this.store.getRootNode();

            // set the raw value to the record's display field if a record was found
            this.setRawValue(record ? record.get(this.displayField) : '');
        }

        return me;
    },//end setValue

    /**
     * store加载完成回调
     *
     * @cfg
     *
     * @author       mrmsl <msl-138@163.com>
     * @date         2012-08-21 10:45:14
     * @lastmodify   2012-08-21 10:45:14 by mrmsl
     *
     * @return {void} 无返回值
     */
    storeOnLoad: function() {
        this.value && this.setValue(this.value);
    }
});