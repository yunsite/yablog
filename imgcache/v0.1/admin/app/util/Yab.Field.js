/**
 * 各种ext表单域
 *
 * @file            app/util/Yab.Field.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-12-11 12:34:01
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.Field', {
    singleton: true,

    /**
     * 自定义下拉框Ext.form.ComboBox
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-09-16 11:28:14
     * @lastmodify      2013-01-14 15:03:07 by mrmsl
     *
     * @return {Object} 各种combo方法
     * @return {Function} return.base 基础下拉框
     * @return {Function} return.enable 是否启用
     * @return {Function} return.matchMode 匹配模式
     * @return {Function} return.show 是否显示
     */
    combo: function() {
        var me = this;

        return {
            /**
             * 审核状态下拉框
             *
             * @author          mrmsl <msl-138@163.com>
             * @date            2013-05-28 12:52:51
             *
             * @ignore
             *
             * @return {Object} Ext.form.ComboBox
             */
            auditing: function() {
                return this.base({
                    width: 80,
                    itemId: 'auditing',
                    value: '-1',
                    store: [
                        ['-1', lang('AUDITING,STATUS')],
                        ['0', lang('CN_WEI,AUDITING')],
                        ['1', lang('CN_YI,PASS')],
                        ['2', lang('CN_WEI,PASS')]
                    ]
                });
            },

            /**
             * 基础下拉框
             *
             * @author          mrmsl <msl-138@163.com>
             * @date            2012-09-15 09:36:14
             * @lastmodify      2013-01-14 15:15:03 by mrmsl
             *
             * @ignore
             *
             * @param {Object} config 配置
             *
             * @return {Object} Ext.form.ComboBox
             */
            base: function(config) {
                return Ext.create('Ext.form.ComboBox', Ext.apply({
                    extend: 'Ext.form.field.ComboBox',
                    alias: ['widget.appcombo'],
                    editable: false,
                    emptyText: lang('PLEASE_SELECT'),
                    width: 80
                }, config));
            },

            /**
             * 是否启用状态下拉框
             *
             * @author          mrmsl <msl-138@163.com>
             * @date            2012-09-15 14:26:59
             * @lastmodify      2013-01-27 14:02:10 by mrmsl
             *
             * @ignore
             *
             * @return {Object} Ext.form.ComboBox
             */
            enable: function() {
                return this.base({
                    width: 80,
                    itemId: 'is_enable',
                    value: '-1',
                    store: [
                        ['-1', lang('ENABLE,STATUS')],
                        ['1', lang('ENABLE')],
                        ['0', lang('DISABLED')]
                    ]
                });
            },

            /**
             * 匹配模式下拉框
             *
             * @author          mrmsl <msl-138@163.com>
             * @date            2012-09-15 09:45:30
             * @lastmodify      2013-01-14 15:15:42 by mrmsl
             *
             * @ignore
             *
             * @return {Object} Ext.form.ComboBox
             */
            matchMode: function() {
                return this.base({
                    width: 80,
                    itemId: 'match_mode',
                    value: 'eq',
                    store: [
                        ['eq', lang('MATCH_MODE_EQ')],
                        ['leq', lang('MATCH_MODE_LEQ')],
                        ['req', lang('MATCH_MODE_REQ')],
                        ['like', lang('MATCH_MODE_LIKE')]
                    ]
                });
            },

            /**
             * 是否显示状态下拉框
             *
             * @author          mrmsl <msl-138@163.com>
             * @date            2012-09-15 11:16:59
             * @lastmodify      2013-01-27 14:02:05 by mrmsl
             *
             * @ignore
             *
             * @return {Object} Ext.form.ComboBox
             */
            show: function() {
                return this.base({
                    width: 80,
                    itemId: 'is_show',
                    value: '-1',
                    store: [
                        ['-1', lang('SHOW,STATUS')],
                        ['1', lang('SHOW')],
                        ['0', lang('NO,SHOW')]
                    ]
                });
            }
        };
    },//end combo

    /**
     * 各种表单域
     *
     * @author              mrmsl <msl-138@163.com>
     * @date                2012-09-16 13:54:03
     * @lastmodify          2013-01-14 15:15:51 by mrmsl
     *
     * @return {Object} 各种textfield方法
     * @return {Function} return.checkbox xtype: 'checkbox'
     * @return {Function} return.dateField xtype: 'datefield'
     * @return {Function} return.displayField xtype: 'displayfield'提示信息
     * @return {Function} return.fieldContainer xtype: 'fieldcontainer'
     * @return {Function} return.hiddenField xtype: 'hiddenfield'
     * @return {Function} return.keywordField 搜索关键字输入框
     * @return {Function} return.memoField 备注textarea
     * @return {Function} return.numberField xtype: 'numberfield'
     * @return {Function} return.passwordField 密码输入框配置
     * @return {Function} return.sortOrderField 排序字段textfield
     * @return {Function} return.textarea xtype: 'textarea'
     * @return {Function} return.textareaComment xtype: 'textarea'提示信息
     * @return {Function} return.textField xtype: 'textfield'
     * @return {Function} return.verifyCode 验证码输入框
     */
    field: function() {
        var me = this;

        return {
            /**
             * 最终Ext.form.field.Base配置
             *
             * @author          mrmsl <msl-138@163.com>
             * @date            2012-09-24 22:16:47
             * @lastmodify      2013-01-14 15:16:05 by mrmsl
             *
             * @ignore
             *
             * @param {Object} options 配置
             *
             * @return {Object} xtype: 'checkbox'
             */
            base: function(options) {
                global('app_labelWidth') ? options.labelWidth = global('app_labelWidth') : '';//fieldLabel
                options.blankText && options.fieldLabel ? options.fieldLabel = TEXT.red() + options.fieldLabel : '';//!allowBlank

                return options;
            },

            /**
             * xtype: 'checkbox'
             *
             * @author          mrmsl <msl-138@163.com>
             * @date            2012-09-24 14:17:57
             * @lastmodify      2013-01-14 15:16:187 by mrmsl
             *
             * @ignore
             *
             * @param {String} name        输入框名称
             * @param {String} value       值
             * @param {String} [fieldLabel] fieldLabel
             * @param {String} [boxLabel] boxLabel
             * @param {String} [inputValue=1] <input value="inputValue",默认1
             * @param {String} [blankText] 不允许为空提示信息
             * @param {Object} [option] 额外配置
             *
             * @return {Object} xtype: 'checkbox'
             */
            checkbox: function(name, value, fieldLabel, boxLabel, inputValue, blankText, options) {
                inputValue = Ext.valueFrom(inputValue, 1);

                var config = {
                    xtype: 'checkbox',
                    checked: value == inputValue,
                    inputValue: inputValue,
                    name: name,
                    value: value
                };

                boxLabel ? config.boxLabel = lang(boxLabel) : '';
                fieldLabel ? config.fieldLabel = lang(fieldLabel) : '';

                if (blankText) {
                    config.allowBlank = false,
                    config.blankText = lang(blankText);
                }

                options && Ext.apply(config, options);

                return this.base(config);
            },

            /**
             * xtype: 'datefield'
             *
             * @author          mrmsl <msl-138@163.com>
             * @date            2012-09-16 16:16:40
             * @lastmodify      2013-01-14 15:18:01 by mrmsl
             *
             * @ignore
             *
             * @param {Object} [config] 配置
             *
             * @return {Object} xtype: 'datefield'
             */
            dateField: function(config) {
                var cfg = {
                    xtype: 'datefield',
                    format: System.sys_timezone_datetime_format,
                    //maxValue: date(),
                    showToday: false
                };

                cfg = Ext.apply(cfg, config || {});

                return cfg;
            },

            /**
             * xtype: 'displayfield'提示信息
             *
             * @author          mrmsl <msl-138@163.com>
             * @date            2012-09-11 12:31:47
             * @lastmodify      2013-01-14 15:18:26 by mrmsl
             *
             * @ignore
             *
             * @param {String} text 提示信息
             * @param {Object} [options] 额外配置
             *
             * @return {Object} xtype: 'displayfield'配置
             */
            displayField: function(text, options) {
                var config = {
                    xtype: 'displayfield',
                    value: TEXT.gray(text)
                };

                options && Ext.apply(config, options);

                return config;
            },

            /**
             * xtype: 'fieldcontainer'
             *
             * @author          mrmsl <msl-138@163.com>
             * @date            2012-09-24 14:41:12
             * @lastmodify      2013-01-14 15:18:59 by mrmsl
             *
             * @ignore
             *
             * @param {String} fieldLabel fieldLabel
             * @param {Array}  items items
             * @param {String/Boolean} [allowBlank=undefied] !allowBlank成立，表示允许为空时提示，否则允许为空
             * @param {Object} [options] 额外配置
             *
             * @return {Object} textfield 配置
             */
            fieldContainer: function(fieldLabel, items, allowBlank, options) {

                if (Ext.isArray(fieldLabel)) {//fieldContainer([fieldLabel, [[fieldType, args], diaplayField], ...])
                    fieldLabel[1][1] = Ext.isArray(fieldLabel[1][1]) ? fieldLabel[1][1] : [fieldLabel[1][1]];
                    return this.fieldContainer(fieldLabel[0], [this[fieldLabel[1][0].shift() || 'textField'].apply(this, fieldLabel[1][0]), this.displayField.apply(this, fieldLabel[1][1])], fieldLabel[2], fieldLabel[3]);
                }

                var config = {
                    xtype: 'fieldcontainer',
                    layout: 'hbox',
                    fieldLabel: lang(fieldLabel),
                    blankText: !allowBlank,
                    items: items
                };

                if (options) {
                    options.vertical ? delete config.layout : '';
                    Ext.apply(config, options);
                }

                return this.base(config);
            },

            /**
             * xtype: 'hiddenfield'
             *
             * @author          mrmsl <msl-138@163.com>
             * @date            2012-09-11 14:15:46
             * @lastmodify      2013-01-14 15:23:17 by mrmsl
             *
             * @ignore
             *
             * @param {String} [name=当前控制器主键] 名称
             * @param {Object} [options] 额外配置
             *
             * @return {Object} xtype: 'hiddenfield'配置
             */
            hiddenField: function(name, options) {
                var config = {
                    xtype: 'hiddenfield',
                    name: name || getController().idProperty
                };

                options && Ext.apply(config, options);

                return config;
            },

            /**
             * 搜索关键字输入框
             *
             * @author          mrmsl <msl-138@163.com>
             * @date            2012-09-25 10:37:45
             * @lastmodify      2013-01-14 15:31:28 by mrmsl
             *
             * @ignore
             *
             * @param {Object} [options] 额外配置
             *
             * @return {Object} 搜索关键字输入框配置
             */
            keywordField: function(value, options) {
                var config = {
                    xtype: 'textfield',
                    itemId: 'keyword',
                    value: value,
                    emptyText: lang('PLEASE_ENTER,KEYWORD'),
                    enableKeyEvents: true,
                    listeners: {
                        keydown: function(input, e) {
                            e.getKey() == e.ENTER && this.nextSibling('component[name=submit]').fireHandler();
                        }
                    }
                };

                options && Ext.apply(config, options);

                return config;
            },

            /**
             * 备注textarea
             *
             * @author          mrmsl <msl-138@163.com>
             * @date            2012-09-11 14:11:21
             * @lastmodify      2013-01-14 15:32:00 by mrmsl
             *
             * @ignore
             *
             * @param {Object} [options] 额外配置
             *
             * @return {Object} textfield 配置
             */
            memoField: function(options) {
                var config = {
                    xtype: 'textarea',
                    width: 400,
                    fieldLabel: lang('MEMO')
                };

                options && Ext.apply(config, options);

                return this.textField('memo', '', 'MEMO', undefined, config)
            },

            /**
             * xtype: 'numberfield'
             *
             * @author          mrmsl <msl-138@163.com>
             * @date            2012-09-24 14:39:58
             * @lastmodify      2013-01-14 15:32:23 by mrmsl
             *
             * @ignore
             *
             * @param {String} name        输入框名称
             * @param {String} [blankText] 不允许为空提示信息
             * @param {String} [fieldLabel] fieldLabel
             * @param {String} [value] 值
             * @param {Object} [options] 额外配置
             *
             * @return {Object} xtype: 'numberfield'
             */
            numberField: function(name, blankText, fieldLabel, value, options) {
                var config = {
                    xtype: 'numberfield',
                    name: name,
                    value: value,
                    size: 8
                };

                fieldLabel ? config.fieldLabel = lang(fieldLabel) : '';

                if (blankText) {
                    config.allowBlank = false,
                    config.blankText = lang(blankText);
                }

                options && Ext.apply(config, options);

                return this.base(config);
            },

            /**
             * 密码输入框
             *
             * @author          mrmsl <msl-138@163.com>
             * @date            2012-09-24 15:57:25
             * @lastmodify      2013-01-14 15:38:18 by mrmsl
             *
             * @ignore
             *
             * @param {String} name        输入框名称
             * @param {String} [blankText] 不允许为空提示信息
             * @param {String} [fieldLabel] fieldLabel
             * @param {Object} [options] 额外配置
             *
             * @return {Object} 密码输入框配置
             */
            passwordField: function(name, blankText, fieldLabel, options) {
                var config = {
                    inputType: 'password',
                    minLength: 6,
                    maxLength: 16
                };

                options && Ext.apply(config, options);

                return this.textField(name, blankText, fieldLabel, undefined, config);
            },

            /**
             * 排序字段textfield
             *
             * @author          mrmsl <msl-138@163.com>
             * @date            2012-09-11 14:03:59
             * @lastmodify      2013-01-14 15:39:09 by mrmsl
             *
             * @ignore
             *
             * @param {Object} options 额外配置
             *
             * @return {Object} textfield 配置
             */
            sortOrderField: function(options) {
                return this.fieldContainer(['ORDER', [
                    ['numberField', 'sort_order', '', '', -1, { minValue: -1 }],
                    lang('ORDER_TIP')
                ], true, options]);
            },

            /**
             * xtype: 'textarea'
             *
             * @author          mrmsl <msl-138@163.com>
             * @date            2012-09-24 17:01:39
             * @lastmodify      2012-09-24 21:22:11 by mrmsl
             *
             * @ignore
             *
             * @param {String} name        输入框名称
             * @param {String} [blankText] 不允许为空提示信息
             * @param {String} [fieldLabel] fieldLabel
             * @param {String} [value] 值
             * @param {Object} [options] 额外配置
             *
             * @return {Object} xtype: 'textarea'配置
             */
            textarea: function(name, blankText, fieldLabel, value, options) {
                var config = {
                    xtype: 'textarea',
                    width: 400
                };

                options && Ext.apply(config, options);

                return this.textField(name, blankText, fieldLabel, value, config);
            },

            /**
             * xtype: 'textarea'提示信息
             *
             * @author          mrmsl <msl-138@163.com>
             * @date            2012-09-06 08:30:21
             * @lastmodify      2013-01-14 15:39:43 by mrmsl
             *
             * @ignore
             *
             * @param {String} html 提示信息
             * @param {Number} paddingLeft 左边距,null取global('app_labelWidth'),undefined取100,包含' ',为自定义padding
             *
             * @return {Object} xtype: 'textarea'配置
             */
            textareaComment: function(html, paddingLeft) {

                if (paddingLeft === null) {
                    paddingLeft = String(global('app_labelWidth'));
                }
                else if (paddingLeft === undefined) {
                    paddingLeft = '100';
                }

                return {
                    xtype: 'component',
                    html: TEXT.gray(html),
                    padding: paddingLeft.indexOf(' ') > 0 ? paddingLeft : '0 0 10 ' + paddingLeft
                }
            },

            /**
             * xtype: 'textfield'
             *
             * @author          mrmsl <msl-138@163.com>
             * @date            2012-09-24 14:55:12
             * @lastmodify      2013-01-14 15:40:51 by mrmsl
             *
             * @ignore
             *
             * @param {String} name        输入框名称
             * @param {String} [blankText] 不允许为空提示信息
             * @param {String} [fieldLabel] fieldLabel
             * @param {String} [value] 值
             * @param {Object} [options] 额外配置
             *
             * @return {Object} xtype: 'textfield'配置
             */
            textField: function(name, blankText, fieldLabel, value, options) {
                var config = {
                    xtype: 'textfield',
                    name: name,
                    value: value
                };

                fieldLabel ? config.fieldLabel = lang(fieldLabel) : '';

                if (blankText) {
                    config.allowBlank = false,
                    config.blankText = lang(blankText);
                }

                options && Ext.apply(config, options);

                return this.base(config);
            },

            /**
             * 验证码输入框
             *
             * @author          mrmsl <msl-138@163.com>
             * @date            2012-09-24 17:54:45
             * @lastmodify      2013-01-14 15:41:02 by mrmsl
             *
             * @ignore
             *
             * @param {String} imgCodeId 验证码图片id
             * @param {String} [module=当前控制器验证码所属模块] 验证码所属模块
             *
             * @return {Object} xtype: 'textfield'配置
             */
            verifyCode: function(imgCodeId, module) {
                module = module || getController().verifycodeModule;
                var enable = System[module + '_verifycode_enable'];//是否开启验证码

                if (!enable) {//未开启验证码
                    return {
                        hidden: true,
                        allowBlank: true,
                        name: '_verify_code'
                    };
                }

                return this.fieldContainer('VERIFY_CODE',[//验证码
                    this.textField('_verify_code', 'PLEASE_ENTER,VERIFY_CODE', '', undefined, {
                    width: 50,
                    listeners: {
                        focus: function() {//获得焦点，显示验证码
                            var img = Ext.get(imgCodeId);

                            if (!img.isVisible()) {//未显示
                                img.show();
                                refreshCode(imgCodeId, module);
                            }
                        }
                    }
                }), {
                    flex: 1,
                    xtype: 'component',
                    html: ' <img id="' + imgCodeId + '" src="" style="display: none; valign: absmiddle; margin-left: 5px; cursor: pointer;" title="' + lang('REFRESH_CODE_TIP') + '" />',
                    listeners: {
                        render: function() {
                            this.el.on({
                                click: function(e, img) {//刷新验证码
                                    refreshCode(imgCodeId, module);
                                },
                                delegate: '#' + imgCodeId
                            });
                        }
                    }
                }]);
            }//end verifyCode
        };
    }//end field
});