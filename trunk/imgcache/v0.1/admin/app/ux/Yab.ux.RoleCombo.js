/**
 * 用户角色下拉combo
 *
 * @file            app/ux/Yab.ux.RoleCombo.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-01-12 22:01:36
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.ux.RoleCombo', {
    extend: 'Ext.form.field.ComboBox',
    alias: ['widget.rolecombo'],
    /**
     * @cfg {String} displayField
     * 显示文本字段
     */
    displayField: 'role_name',
    /**
     * @cfg {String} valueField
     * 值字段
     */
    valueField: 'role_id',
    /**
     * @cfg {Boolean} editable
     * 选择框是否可编辑
     */
    editable: false,
    /**
     * @cfg {String} [emptyText=PLEASE_SELECT语言项]
     * 空文本提示文字
     */
    emptyText: lang('PLEASE_SELECT'),
    /**
     * @cfg {String} itemId
     * 组件id
     */
    itemId: 'role_id',
    /**
     * @cfg {String} name
     * 组件名称
     */
    name: 'role_id',

    /**
     * @cfg {Number} width
     * 宽度
     */
    width: 120,

    constructor: function(config) {//构造函数
        config = config || {};
        this.store = config.store ||
        Ext.create('Yab.store.Role', {
            id: 'Yab.store.admin.Role' + (++Ext.idSeed),
            url: config.url || undefined
        });
        this.value = config.value || '';
        this.listeners = {
            beforequery: function() {//展开前

                if (config.value) {//已初始值，不重复加载
                    this.expand();
                    return false;
                }

                return true;
            },
            render: function() {
                config.value && this.getStore().load();//初始值，自动加载
            }
        };
        this.callParent([config]);
    }
});