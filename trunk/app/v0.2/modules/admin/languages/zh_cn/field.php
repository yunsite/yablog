<?php
/**
 * 表单域模块语言中文包
 *
 * @file            field.php
 * @package         Yab\Module\Admin\Language
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-08-01 12:39:27
 * @lastmodify      $Date$ $Author$
 */

return array(
    'AUTO_OPERATION_TIP'=> '填充规则：<span class="font-red">填充内容,填充时候,附加规则,附加参数</span>',
    'FIELD_CODE'        => '表单域js代码',
    'FIELD_NAME'        => '表单域名',
    'INPUT_NAME'        => '输入框名称',
    'CONTROLLER_NAME_FIELD' => '表单域',
    'PARENT_FIELD'      => '所属分组',
    'VALIDATE_RULE'     => '验证规则',
    'VALIDATE_RULE_TIP' => '一行一个验证规则，其中首行为输入框值类型。验证规则：<span class="font-red">规则,提示信息,验证条件,附加规则,验证时候,附加参数</span>',

    //系统设置by mrmsl on 2012-08-29 16:40:47
    'CUSTOMIZE_1_FIELD_TIP'=> '系统设置(0,不写js;1则写)',//by mrmsl on 2012-09-04 18:27:00
    //系统设置tab标签数据 by mrmsl on 2012-09-22 15:08:01
    'SYSTEM_TAB_ARR'=> '[
        ["base", "基本信息"],
        ["seo", "SEO设置"],
        ["security", "完全设置"],
        ["account", "帐号设置"],
        ["timezone", "时间区域"],
        ["session", "session设置"],
        ["cookie", "cookie设置"],
        ["mail", "邮件配置"],
        ["template", "模板设置"],
        ["log", "日志设置"],
        ["verifycode", "验证码设置"],
        ["show", "显示设置"],
        ["other", "其它设置"]
    ]',
    'CONTROLLER_NAME_SYSTEM'=> '系统设置',

    //安全设置 by mrmsl on 2012-09-13 10:56:25
    'SYS_LOG_LEVEL_DEBUG'  => '调试信息，如执行时间',
    'SYS_LOG_LEVEL_INFO'   => '程序输出信息，如变量值',
    'SYS_LOG_LEVEL_SQL'    => 'SQL语句，以跟踪语句执行轨迹，正式环境下强烈建议不要开启',
    'SYS_LOG_LEVEL_ROOLBACK_SQL'    => 'SQL执行出错，当前db类执行SQL组，建议开启',

    //验证码类型by mrmsl on 2012-09-22 09:43:58
    'VERIFY_CODE_TYPE_LETTERS_VALUE'       => VERIFY_CODE_TYPE_LETTERS,
    'VERIFY_CODE_TYPE_LETTERS_UPPER_VALUE' => VERIFY_CODE_TYPE_LETTERS_UPPER,
    'VERIFY_CODE_TYPE_LETTERS_LOWER_VALUE' => VERIFY_CODE_TYPE_LETTERS_LOWER,
    'VERIFY_CODE_TYPE_NUMERIC_VALUE'       => VERIFY_CODE_TYPE_NUMERIC,
    'VERIFY_CODE_TYPE_ALPHANUMERIC_VALUE'  => VERIFY_CODE_TYPE_ALPHANUMERIC,
    'VERIFY_CODE_TYPE_ALPHANUMERIC_EXTEND_VALUE'  => VERIFY_CODE_TYPE_ALPHANUMERIC_EXTEND,
    'VERIFY_CODE_TYPE_LETTERS'       => '大小写字母(a-zA-Z)',
    'VERIFY_CODE_TYPE_LETTERS_UPPER' => '大写字母(A-Z)',
    'VERIFY_CODE_TYPE_LETTERS_LOWER' => '小写字母(a-z)',
    'VERIFY_CODE_TYPE_NUMERIC'       => '数字(0-9)',
    'VERIFY_CODE_TYPE_ALPHANUMERIC'  => '字母与数字(a-xA-Z0-9)',
    'VERIFY_CODE_TYPE_ALPHANUMERIC_EXTEND'  => '字母与数字(a-xA-Z0-9)，排除容易混淆的字符oOLl和数字01',
    'VERIFY_CODE_ORDER_TIP'         => '如：4123表示分别表示第四、一、二、三个字母;2222表示都为第二个字母;332表示只需要填写三个;如果不是数字，则所填字符串即为验证码。0表示按验证码顺序',//by mrmsl on 2012-09-27 08:40:59
    'VERIFY_CODE_REFRESH_LIMIT_TIP' => '格式：最大刷新次数/超出次数锁定时间(单位：秒)。0表示不限制',//by mrmsl on 2012-09-27 13:45:04
    'VERIFY_CODE_ERROR_LIMIT_TIP'   => '格式：最大错误次数/超出次数锁定时间(单位：秒)。0表示不限制',//by mrmsl on 2012-09-27 13:48:23

    //模块设置tab标签数据 by mrmsl on 2012-09-22 15:10:51
    'MODULE_TAB_ARR'=> '[
     ["admin", "管理员模块"],
     ["guestbook_comments", "留言评论模块"],
     ["guestbook", "留言模块"],
     ["comments", "评论模块"]
    ]',
);