/**
 * ueditor编辑器扩展
 *
 * @file            app/ux/Yab.ux.Ueditor.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-03-20 17:51:20
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.ux.Ueditor', {
    extend:'Ext.form.field.Base',
    alias: 'widget.ueditor',
    /*
     * @cfg {Number} [width=800]
     * 编辑器宽
     */
    width: 800,
    /*
     * @cfg {Number} [height=200]
     * 编辑器高
     */
    height: 200,
    /*
     * @cfg {Object}
     * 编辑器配置
     */
    editorConfig: {
        wordCount: false,
        elementPathEnabled: false
    },
    /*
     * @cfg {Array}
     * 域代码
     */
    fieldSubTpl: [
        '<div id="{id}"',
        '<tpl if="fieldStyle"> style="{fieldStyle}"</tpl>',
        ' class="x-hide-display"></div>',
        {
            compiled: true,
            disableFormats: true
        }
    ],

    initEvents: Ext.emptyFn,

    /**
     * isValid
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-03-20 17:45:50
     *
     * @private
     *
     * @return {Bool} true
     */
    isValid: function() {
        return true;
    },

    /**
     * validate
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-03-20 17:47:14
     *
     * @private
     *
     * @return {Bool} true
     */
    validate: function() {
        return true;
    },

    /**
     * getSubTplData
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-03-20 17:51:07
     *
     * @private
     *
     * @return {Object}
     */
    getSubTplData: function() {
        var ret = this.callParent(arguments);

        ret.value = this.getRawValue();

        return ret;
    },

    /**
     * 获取编辑器值
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-03-20 17:50:09
     *
     * @private
     *
     * @return {String} 编辑器值
     */
    getRawValue: function() {
        return this.editor && this.editor.body ? this.editor.getContent() : '';
    },

    /**
     * 初始化组件并渲染编辑器
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-03-20 17:48:01
     *
     * @private
     *
     * @return {void} 无返回值
     */
    initComponent: function() {
        this.callParent(arguments);

        this.on('afterrender', function(){
            Ext.apply(this.editorConfig, {
                initialFrameWidth: this.width || '100%',
                initialFrameHeight: this.height || '100%',
                initialContent: this.value || '',
                initialStyle: this.initialStyle || 'body{font-size: 12px}'
            });
            this.editor = UE.getEditor(this.bodyEl.id, this.editorConfig);
            this.editorId = this.editor.id;
        }, this);
    },

    /**
     * 设置编辑器值
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-03-20 17:49:23
     *
     * @private
     *
     * @return {void} 无返回值
     */
    setValue: function(value){
        this.callParent(arguments);

        if (this.editor) {

            if (this.editor.body) {
                this.editor.setContent(value);
            }
            else {//未初始化完成,如chrome this.editor.body.innerHTML is 'undefined', 故延时100ms by mashanling on 2013-06-07 08:27:06
                var editor = this.editor;

                setTimeout(function() {
                    editor.setContent(value);
                }, 100);
            }
        }
    }
});