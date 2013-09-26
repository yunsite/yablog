<?php
//后台自动生成，请毋修改
//最后更新时间:2013-06-27 12:52:34

return array (
  7 => 
  array (
    'field_id' => '7',
    'menu_id' => '43',
    'field_name' => '网站域名',
    'field_code' => 'extField.textField(\'@input_name\', \'PLEASE_ENTER,%@field_name\', \'%@fieldLabel\', \'@value\')',
    'validate_rule' => 'string
notblank',
    'auto_operation' => '',
    'input_name' => 'sys_base_domain',
    'input_value' => 'www.yablog.cn',
    'is_enable' => '1',
    'sort_order' => '0',
    'memo' => '',
    'customize_1' => '1',
  ),
  80 => 
  array (
    'field_id' => '80',
    'menu_id' => '84',
    'field_name' => '留言是否需要审核',
    'field_code' => 'guestbook_comments_check@',
    'validate_rule' => 'int',
    'auto_operation' => '_getCheckboxValue',
    'input_name' => 'module_guestbook_check',
    'input_value' => '-1',
    'is_enable' => '1',
    'sort_order' => '0',
    'memo' => '',
    'customize_1' => '0',
  ),
  82 => 
  array (
    'field_id' => '82',
    'menu_id' => '99',
    'field_name' => '评论是否需要审核',
    'field_code' => 'guestbook_comments_check@',
    'validate_rule' => 'int',
    'auto_operation' => '_getCheckboxValue',
    'input_name' => 'module_comments_check',
    'input_value' => '-1',
    'is_enable' => '1',
    'sort_order' => '0',
    'memo' => '',
    'customize_1' => '0',
  ),
  85 => 
  array (
    'field_id' => '85',
    'menu_id' => '84',
    'field_name' => '留言最大回复层级',
    'field_code' => 'guestbook_comments_max_reply_level@',
    'validate_rule' => 'int',
    'auto_operation' => '',
    'input_name' => 'module_guestbook_max_reply_level',
    'input_value' => '-1',
    'is_enable' => '1',
    'sort_order' => '0',
    'memo' => '',
    'customize_1' => '0',
  ),
  86 => 
  array (
    'field_id' => '86',
    'menu_id' => '99',
    'field_name' => '评论最大回复层级',
    'field_code' => 'guestbook_comments_max_reply_level@',
    'validate_rule' => 'int',
    'auto_operation' => '',
    'input_name' => 'module_comments_max_reply_level',
    'input_value' => '-1',
    'is_enable' => '1',
    'sort_order' => '0',
    'memo' => '',
    'customize_1' => '0',
  ),
  105 => 
  array (
    'field_id' => '105',
    'menu_id' => '101',
    'field_name' => '留言评论是否需要审核',
    'field_code' => 'guestbook_comments_check',
    'validate_rule' => 'int',
    'auto_operation' => '_getCheckboxValue',
    'input_name' => 'module_guestbook_comments_check',
    'input_value' => '1',
    'is_enable' => '1',
    'sort_order' => '0',
    'memo' => '',
    'customize_1' => '0',
  ),
  106 => 
  array (
    'field_id' => '106',
    'menu_id' => '101',
    'field_name' => '留言评论最大回复层级',
    'field_code' => 'guestbook_comments_max_reply_level',
    'validate_rule' => 'int',
    'auto_operation' => '',
    'input_name' => 'module_guestbook_comments_max_reply_level',
    'input_value' => '5',
    'is_enable' => '1',
    'sort_order' => '0',
    'memo' => '',
    'customize_1' => '0',
  ),
  107 => 
  array (
    'field_id' => '107',
    'menu_id' => '99',
    'field_name' => '评论间隔',
    'field_code' => 'guestbook_comments_alternation@',
    'validate_rule' => 'int',
    'auto_operation' => '',
    'input_name' => 'module_comments_alternation',
    'input_value' => '-1',
    'is_enable' => '1',
    'sort_order' => '0',
    'memo' => '',
    'customize_1' => '0',
  ),
  108 => 
  array (
    'field_id' => '108',
    'menu_id' => '84',
    'field_name' => '留言间隔',
    'field_code' => 'guestbook_comments_alternation@',
    'validate_rule' => 'int',
    'auto_operation' => '',
    'input_name' => 'module_guestbook_alternation',
    'input_value' => '-1',
    'is_enable' => '1',
    'sort_order' => '0',
    'memo' => '',
    'customize_1' => '0',
  ),
  109 => 
  array (
    'field_id' => '109',
    'menu_id' => '101',
    'field_name' => '留言评论间隔',
    'field_code' => 'guestbook_comments_alternation@',
    'validate_rule' => 'int',
    'auto_operation' => '',
    'input_name' => 'module_guestbook_comments_alternation',
    'input_value' => '0',
    'is_enable' => '1',
    'sort_order' => '0',
    'memo' => '',
    'customize_1' => '0',
  ),
  111 => 
  array (
    'field_id' => '111',
    'menu_id' => '84',
    'field_name' => '留言禁用用户名',
    'field_code' => 'guestbook_comments_disabled_username@',
    'validate_rule' => 'string',
    'auto_operation' => '',
    'input_name' => 'module_guestbook_disabled_username',
    'input_value' => '',
    'is_enable' => '1',
    'sort_order' => '0',
    'memo' => '',
    'customize_1' => '0',
  ),
  112 => 
  array (
    'field_id' => '112',
    'menu_id' => '99',
    'field_name' => '评论禁用用户名',
    'field_code' => 'guestbook_comments_disabled_username@',
    'validate_rule' => 'string',
    'auto_operation' => '',
    'input_name' => 'module_guestbook_disabled_username',
    'input_value' => '',
    'is_enable' => '1',
    'sort_order' => '0',
    'memo' => '',
    'customize_1' => '0',
  ),
  114 => 
  array (
    'field_id' => '114',
    'menu_id' => '84',
    'field_name' => '禁止留言ip',
    'field_code' => 'guestbook_comments_disabled_ip@',
    'validate_rule' => 'string',
    'auto_operation' => '',
    'input_name' => 'module_guestbook_disabled_ip',
    'input_value' => '',
    'is_enable' => '1',
    'sort_order' => '0',
    'memo' => '',
    'customize_1' => '0',
  ),
  115 => 
  array (
    'field_id' => '115',
    'menu_id' => '99',
    'field_name' => '禁止评论ip',
    'field_code' => 'guestbook_comments_disabled_ip@',
    'validate_rule' => 'string',
    'auto_operation' => '',
    'input_name' => 'module_comments_disabled_ip',
    'input_value' => '',
    'is_enable' => '1',
    'sort_order' => '0',
    'memo' => '',
    'customize_1' => '0',
  ),
  116 => 
  array (
    'field_id' => '116',
    'menu_id' => '101',
    'field_name' => '管理员回复用户名',
    'field_code' => 'extField.textField(\'@input_name\', \'PLEASE_ENTER,%@field_name\', \'%@fieldLabel\', \'@value\')',
    'validate_rule' => 'string
notblank',
    'auto_operation' => '',
    'input_name' => 'module_guestbook_comments_reply_admin_username',
    'input_value' => 'admin',
    'is_enable' => '1',
    'sort_order' => '0',
    'memo' => '',
    'customize_1' => '0',
  ),
  117 => 
  array (
    'field_id' => '117',
    'menu_id' => '101',
    'field_name' => '管理员回复email',
    'field_code' => 'extField.textField(\'@input_name\', \'PLEASE_ENTER,%@field_name\', \'%@fieldLabel\', \'@value\')',
    'validate_rule' => 'email
notblank',
    'auto_operation' => '',
    'input_name' => 'module_guestbook_comments_reply_admin_email',
    'input_value' => 'msl-138@163.com',
    'is_enable' => '1',
    'sort_order' => '0',
    'memo' => '',
    'customize_1' => '0',
  ),
  118 => 
  array (
    'field_id' => '118',
    'menu_id' => '101',
    'field_name' => '管理员回复头像',
    'field_code' => 'extField.fieldContainer([\'%@fieldLabel\', [
    [null, \'@input_name\', \'PLEASE_ENTER,%@field_name\', \'\', \'@value\',{width: 320}],
    \'@common_imgcache@=\' + System.sys_base_common_imgcache
]])',
    'validate_rule' => 'string
notblank',
    'auto_operation' => '',
    'input_name' => 'module_guestbook_comments_reply_admin_img',
    'input_value' => '@common_imgcache@images/admin.png',
    'is_enable' => '1',
    'sort_order' => '0',
    'memo' => '',
    'customize_1' => '0',
  ),
  13 => 
  array (
    'field_id' => '13',
    'menu_id' => '43',
    'field_name' => '网站http协议',
    'field_code' => 'extField.textField(\'@input_name\', \'PLEASE_SELECT,%@field_name\',\'%@fieldLabel\', \'@value\', {size: 10,editable: false, store: [
    [\'\', lang(\'PLEASE_SELECT\')],
    [\'http\', \'http\'],
    [\'https\', \'https\']
]})',
    'validate_rule' => 'string
#{%PLEASE_SELECT,@field_name}#MUST_VALIDATE#notblank
http,https#{%@field_name,CAN_ONLY_BE,http OR https}#VALUE_VALIDATE#in',
    'auto_operation' => '',
    'input_name' => 'sys_base_http_protocol',
    'input_value' => 'http',
    'is_enable' => '1',
    'sort_order' => '1',
    'memo' => '',
    'customize_1' => '0',
  ),
  8 => 
  array (
    'field_id' => '8',
    'menu_id' => '43',
    'field_name' => '网站根目录',
    'field_code' => 'extField.fieldContainer([\'%@fieldLabel\', [
    [null,\'@input_name\',\'PLEASE_ENTER,%@field_name\', \'\', \'@value\'],
    lang(\'END_WITH\').replace(\'%s\',\'"<span class="font-red">/</span>"\')
]])',
    'validate_rule' => 'string
validate_path',
    'auto_operation' => '',
    'input_name' => 'sys_base_wwwroot',
    'input_value' => 'v0.2/',
    'is_enable' => '1',
    'sort_order' => '2',
    'memo' => '',
    'customize_1' => '0',
  ),
  79 => 
  array (
    'field_id' => '79',
    'menu_id' => '43',
    'field_name' => '后台管理入口',
    'field_code' => 'extField.fieldContainer([\'%@fieldLabel\', [
    [null,\'@input_name\',\'PLEASE_ENTER,%@field_name\', \'\', \'@value\'],
    lang(\'RELATIVE,WEBSITE,WWWROOT,OR\') + \'http://\' + lang(\'ABSOLUTE,ADDRESS\')
]])',
    'validate_rule' => 'string
notblank',
    'auto_operation' => '',
    'input_name' => 'sys_base_admin_entry',
    'input_value' => 'yabadmin.php',
    'is_enable' => '1',
    'sort_order' => '2',
    'memo' => '',
    'customize_1' => '0',
  ),
  9 => 
  array (
    'field_id' => '9',
    'menu_id' => '43',
    'field_name' => '网站名称',
    'field_code' => 'extField.textField(\'@input_name\', \'PLEASE_ENTER,%@field_name\', \'%@fieldLabel\', \'@value\', {size: 30})',
    'validate_rule' => 'string
notblank',
    'auto_operation' => '',
    'input_name' => 'sys_base_web_name',
    'input_value' => 'yablog',
    'is_enable' => '1',
    'sort_order' => '3',
    'memo' => '',
    'customize_1' => '1',
  ),
  15 => 
  array (
    'field_id' => '15',
    'menu_id' => '43',
    'field_name' => '网站标题',
    'field_code' => 'extField.textField(\'@input_name\', \'PLEASE_ENTER,%@field_name\', \'%@fieldLabel\', \'@value\', {size: 60})',
    'validate_rule' => 'string
notblank',
    'auto_operation' => '',
    'input_name' => 'sys_base_web_title',
    'input_value' => 'yablog,又一个博客',
    'is_enable' => '1',
    'sort_order' => '9',
    'memo' => '',
    'customize_1' => '1',
  ),
  10 => 
  array (
    'field_id' => '10',
    'menu_id' => '43',
    'field_name' => '网站首页标题',
    'field_code' => 'extField.textField(\'@input_name\', \'PLEASE_ENTER,%@field_name\', \'%@fieldLabel\', \'@value\', {size: 60})',
    'validate_rule' => 'string
notblank',
    'auto_operation' => '',
    'input_name' => 'sys_base_web_index_title',
    'input_value' => '首页',
    'is_enable' => '1',
    'sort_order' => '10',
    'memo' => '',
    'customize_1' => '0',
  ),
  18 => 
  array (
    'field_id' => '18',
    'menu_id' => '43',
    'field_name' => 'js脚本路径',
    'field_code' => 'extField.fieldContainer([\'%@fieldLabel\', [
    [null,\'@input_name\',\'PLEASE_ENTER,%@field_name\', \'\', \'@value\'],
    lang(\'RELATIVE,WEBSITE,WWWROOT,%。,CAN_NOT,START_WITH\').replace(\'%s\',\'<span class="font-red">/</span>\') + lang(\'%，,MUST,END_WITH\').replace(\'%s\',\'"<span class="font-red">/</span>"\')
]])',
    'validate_rule' => 'string
notblank
validate_dir',
    'auto_operation' => '',
    'input_name' => 'sys_base_js_path',
    'input_value' => 'static/js/',
    'is_enable' => '1',
    'sort_order' => '11',
    'memo' => '',
    'customize_1' => '0',
  ),
  19 => 
  array (
    'field_id' => '19',
    'menu_id' => '43',
    'field_name' => 'css样式路径',
    'field_code' => 'extField.fieldContainer([\'%@fieldLabel\', [
    [null,\'@input_name\',\'PLEASE_ENTER,%@field_name\', \'\', \'@value\'],
    lang(\'RELATIVE,WEBSITE,WWWROOT,%。,CAN_NOT,START_WITH\').replace(\'%s\',\'<span class="font-red">/</span>\') + lang(\'%，,MUST,END_WITH\').replace(\'%s\',\'"<span class="font-red">/</span>"\')
]])',
    'validate_rule' => 'string
notblank
validate_dir',
    'auto_operation' => '',
    'input_name' => 'sys_base_css_path',
    'input_value' => 'static/css/',
    'is_enable' => '1',
    'sort_order' => '11',
    'memo' => '',
    'customize_1' => '0',
  ),
  12 => 
  array (
    'field_id' => '12',
    'menu_id' => '44',
    'field_name' => 'seo关键字',
    'field_code' => 'extField.textarea(\'@input_name\', \'PLEASE_ENTER,%@field_name\', \'%@fieldLabel\', \'@value\', {width: 850, height: 40})',
    'validate_rule' => 'string
notblank',
    'auto_operation' => '',
    'input_name' => 'sys_seo_keyword',
    'input_value' => 'mrmsl',
    'is_enable' => '1',
    'sort_order' => '12',
    'memo' => '',
    'customize_1' => '0',
  ),
  77 => 
  array (
    'field_id' => '77',
    'menu_id' => '43',
    'field_name' => 'imgcache common地址',
    'field_code' => 'extField.fieldContainer([\'%@fieldLabel\', [
    [null,\'@input_name\',\'PLEASE_ENTER,%@field_name\', \'\', \'@value\', {width: 300}],
    lang(\'END_WITH\').replace(\'%s\',\'"<span class="font-red">/</span>"\')
]])',
    'validate_rule' => 'url
#{%PLEASE_ENTER,CORRECT,FORMAT,CN_DE,@field_name}#MUST_VALIDATE#notblank
validate_path',
    'auto_operation' => '',
    'input_name' => 'sys_base_common_imgcache',
    'input_value' => 'http://imgcache.yablog.cn/common/',
    'is_enable' => '1',
    'sort_order' => '15',
    'memo' => '',
    'customize_1' => '1',
  ),
  78 => 
  array (
    'field_id' => '78',
    'menu_id' => '43',
    'field_name' => '后台imgcache地址',
    'field_code' => 'extField.fieldContainer([\'%@fieldLabel\', [
    [null,\'@input_name\',\'PLEASE_ENTER,%@field_name\', \'\', \'@value\', {width: 300}],
    lang(\'END_WITH\').replace(\'%s\',\'"<span class="font-red">/</span>"\')
]])',
    'validate_rule' => 'url
#{%PLEASE_ENTER,CORRECT,FORMAT,CN_DE,@field_name}#MUST_VALIDATE#notblank
validate_path',
    'auto_operation' => '',
    'input_name' => 'sys_base_admin_imgcache',
    'input_value' => 'http://imgcache.yablog.cn/v0.2/admin/',
    'is_enable' => '1',
    'sort_order' => '15',
    'memo' => '',
    'customize_1' => '1',
  ),
  16 => 
  array (
    'field_id' => '16',
    'menu_id' => '43',
    'field_name' => 'js脚本url地址',
    'field_code' => 'extField.fieldContainer([\'%@fieldLabel\', [
    [null,\'@input_name\',\'PLEASE_ENTER,%@field_name\', \'\', \'@value\', {width: 300}],
    lang(\'END_WITH\').replace(\'%s\',\'"<span class="font-red">/</span>"\')
]])',
    'validate_rule' => 'url
#{%PLEASE_ENTER,CORRECT,FORMAT,CN_DE,@field_name}#MUST_VALIDATE#notblank
validate_path',
    'auto_operation' => '',
    'input_name' => 'sys_base_js_url',
    'input_value' => 'http://imgcache.yablog.cn/v0.2/front/js/',
    'is_enable' => '1',
    'sort_order' => '16',
    'memo' => '',
    'customize_1' => '1',
  ),
  17 => 
  array (
    'field_id' => '17',
    'menu_id' => '43',
    'field_name' => 'css样式地址',
    'field_code' => 'extField.fieldContainer([\'%@fieldLabel\', [
    [null,\'@input_name\',\'PLEASE_ENTER,%@field_name\', \'\', \'@value\', {width: 300}],
    lang(\'END_WITH\').replace(\'%s\',\'"<span class="font-red">/</span>"\')
]])',
    'validate_rule' => 'url
#{%PLEASE_ENTER,CORRECT,FORMAT,CN_DE,@field_name}#MUST_VALIDATE#notblank
validate_path',
    'auto_operation' => '',
    'input_name' => 'sys_base_css_url',
    'input_value' => 'http://imgcache.yablog.cn/v0.2/front/css/',
    'is_enable' => '1',
    'sort_order' => '17',
    'memo' => '',
    'customize_1' => '1',
  ),
  20 => 
  array (
    'field_id' => '20',
    'menu_id' => '43',
    'field_name' => 'img图片url地址',
    'field_code' => 'extField.fieldContainer([\'%@fieldLabel\', [
    [null,\'@input_name\',\'PLEASE_ENTER,%@field_name\', \'\', \'@value\', {width: 300}],
    lang(\'END_WITH\').replace(\'%s\',\'"<span class="font-red">/</span>"\')
]])',
    'validate_rule' => 'url
#{%PLEASE_ENTER,CORRECT,FORMAT,CN_DE,@field_name}#MUST_VALIDATE#notblank
validate_path',
    'auto_operation' => '',
    'input_name' => 'sys_base_img_url',
    'input_value' => 'http://imgcache.yablog.cn/v0.2/front/images/',
    'is_enable' => '1',
    'sort_order' => '20',
    'memo' => '',
    'customize_1' => '1',
  ),
  21 => 
  array (
    'field_id' => '21',
    'menu_id' => '44',
    'field_name' => 'seo描述',
    'field_code' => 'extField.textarea(\'@input_name\', \'PLEASE_ENTER,%@field_name\', \'%@fieldLabel\', \'@value\', {width: 850, height: 60})',
    'validate_rule' => 'string
notblank',
    'auto_operation' => '',
    'input_name' => 'sys_seo_description',
    'input_value' => 'seo描述',
    'is_enable' => '1',
    'sort_order' => '21',
    'memo' => '',
    'customize_1' => '0',
  ),
  22 => 
  array (
    'field_id' => '22',
    'menu_id' => '43',
    'field_name' => '网站是否关闭',
    'field_code' => 'extField.checkbox(\'@input_name\',\'@value\', \'%@fieldLabel\')',
    'validate_rule' => 'int',
    'auto_operation' => '_getCheckboxValue',
    'input_name' => 'sys_base_closed',
    'input_value' => '1',
    'is_enable' => '1',
    'sort_order' => '22',
    'memo' => '',
    'customize_1' => '0',
  ),
  23 => 
  array (
    'field_id' => '23',
    'menu_id' => '43',
    'field_name' => '网站关闭原因',
    'field_code' => 'extField.textarea(\'@input_name\', \'\', \'%@fieldLabel\', \'@value\', {width: 700, height: 120})',
    'validate_rule' => 'raw
return',
    'auto_operation' => '',
    'input_name' => 'sys_base_closed_reason',
    'input_value' => '<div style="color: red">网站关闭了</div>',
    'is_enable' => '1',
    'sort_order' => '23',
    'memo' => '',
    'customize_1' => '0',
  ),
  24 => 
  array (
    'field_id' => '24',
    'menu_id' => '69',
    'field_name' => '标准时间相差',
    'field_code' => 'extField.fieldContainer([\'%@fieldLabel\', [
    [\'numberField\',\'@input_name\',\'PLEASE_ENTER,%@field_name\', \'\', \'@value\'],
    lang(\'UNIT,%：,SECOND\')
]])',
    'validate_rule' => 'int
notblank',
    'auto_operation' => '',
    'input_name' => 'sys_timezone_timediff',
    'input_value' => '28800',
    'is_enable' => '1',
    'sort_order' => '24',
    'memo' => '',
    'customize_1' => '1',
  ),
  25 => 
  array (
    'field_id' => '25',
    'menu_id' => '69',
    'field_name' => '网站默认时区',
    'field_code' => 'extField.textField(\'@input_name\', \'PLEASE_ENTER,%@field_name\', \'%@fieldLabel\', \'@value\')',
    'validate_rule' => 'string
notblank
_timezone#{%@field_name,NOT_EXIST}#VALUE_VALIDATE#callback',
    'auto_operation' => '',
    'input_name' => 'sys_timezone_default_timezone',
    'input_value' => 'asia/shanghai',
    'is_enable' => '1',
    'sort_order' => '25',
    'memo' => '',
    'customize_1' => '0',
  ),
  26 => 
  array (
    'field_id' => '26',
    'menu_id' => '69',
    'field_name' => '长时间格式',
    'field_code' => 'extField.textField(\'@input_name\', \'PLEASE_ENTER,%@field_name\', \'%@fieldLabel\', \'@value\')',
    'validate_rule' => 'string
notblank',
    'auto_operation' => '',
    'input_name' => 'sys_timezone_datetime_format',
    'input_value' => 'Y-m-d H:i:s',
    'is_enable' => '1',
    'sort_order' => '26',
    'memo' => '',
    'customize_1' => '1',
  ),
  27 => 
  array (
    'field_id' => '27',
    'menu_id' => '69',
    'field_name' => '日期格式',
    'field_code' => 'extField.textField(\'@input_name\', \'PLEASE_ENTER,%@field_name\', \'%@fieldLabel\', \'@value\')',
    'validate_rule' => 'string
notblank',
    'auto_operation' => '',
    'input_name' => 'sys_timezone_date_format',
    'input_value' => 'Y-m-d',
    'is_enable' => '1',
    'sort_order' => '27',
    'memo' => '',
    'customize_1' => '1',
  ),
  28 => 
  array (
    'field_id' => '28',
    'menu_id' => '69',
    'field_name' => '时间格式',
    'field_code' => 'extField.textField(\'@input_name\', \'PLEASE_ENTER,%@field_name\', \'%@fieldLabel\', \'@value\')',
    'validate_rule' => 'string
notblank',
    'auto_operation' => '',
    'input_name' => 'sys_timezone_time_format',
    'input_value' => 'H:i:s',
    'is_enable' => '1',
    'sort_order' => '28',
    'memo' => '',
    'customize_1' => '1',
  ),
  38 => 
  array (
    'field_id' => '38',
    'menu_id' => '71',
    'field_name' => '日志文件大小',
    'field_code' => 'extField.fieldContainer([\'%@fieldLabel\', [
    [\'numberField\',\'@input_name\',\'PLEASE_ENTER,%@field_name\',\'\', \'@value\', {minValue: 0}],
    lang(\'UNIT,%：KB\')
]])',
    'validate_rule' => 'int
unsigned#10',
    'auto_operation' => '',
    'input_name' => 'sys_log_filesize',
    'input_value' => '1024',
    'is_enable' => '1',
    'sort_order' => '29',
    'memo' => '',
    'customize_1' => '0',
  ),
  30 => 
  array (
    'field_id' => '30',
    'menu_id' => '71',
    'field_name' => '记录日志级别',
    'field_code' => 'extField.fieldContainer(\'%@fieldLabel\',[
    extField.checkbox(\'@input_name[]\', \'\', \'\', \'%\' + lang(\'INFO\') + TEXT.gray(lang(\'SYS_LOG_LEVEL_INFO\')), \'E_APP_INFO\'),
    extField.checkbox(\'@input_name[]\', \'\', \'\', \'%\' + lang(\'DEBUG\') + TEXT.gray(lang(\'SYS_LOG_LEVEL_DEBUG\')), \'E_APP_DEBUG\'),
    extField.checkbox(\'@input_name[]\', \'\', \'\', \'%SQL\' + TEXT.gray(lang(\'SYS_LOG_LEVEL_SQL\')), \'E_APP_SQL\'),
    extField.checkbox(\'@input_name[]\', \'\', \'\', \'%\' + lang(\'ROLLBACK_SQL\') + TEXT.gray(lang(\'SYS_LOG_LEVEL_ROOLBACK_SQL\')), \'E_APP_ROLLBACK_SQL\')
], true, {
xtype: \'checkboxgroup\',
value: \'@value\' ? {\'@input_name[]\': \'@value\'.split(\',\')} : false,
columns: 1,
vertical: true,
name: \'@input_name\'
})',
    'validate_rule' => '_array,post,string
#{%@field_name,DATA,INVALID}#EXISTS_VALIDATE#return',
    'auto_operation' => '_getCheckboxValue#,',
    'input_name' => 'sys_log_level',
    'input_value' => 'E_APP_SQL,E_APP_ROLLBACK_SQL',
    'is_enable' => '1',
    'sort_order' => '30',
    'memo' => '',
    'customize_1' => '0',
  ),
  52 => 
  array (
    'field_id' => '52',
    'menu_id' => '76',
    'field_name' => 'session前缀',
    'field_code' => 'extField.fieldContainer([\'%@fieldLabel\', [
    [null,\'@input_name\',\'\', \'\', \'@value\'],
    lang(\'AVOID,CONFLICT\')
], true])',
    'validate_rule' => 'string
return',
    'auto_operation' => '',
    'input_name' => 'sys_session_prefix',
    'input_value' => 'mrmsl',
    'is_enable' => '1',
    'sort_order' => '30',
    'memo' => '',
    'customize_1' => '0',
  ),
  53 => 
  array (
    'field_id' => '53',
    'menu_id' => '76',
    'field_name' => 'session.name',
    'field_code' => 'extField.fieldContainer([\'%@fieldLabel\', [
    [null,\'@input_name\',\'\', \'\', \'@value\'],
    lang(\'SESSION_NAME_TIP\')
], true])',
    'validate_rule' => 'string
return
english#{%@field_name,CAN_BUT,CN_YOU,LETTER,MAKEUP}#VALUE_VALIDATE',
    'auto_operation' => '',
    'input_name' => 'sys_session_name',
    'input_value' => 'PHPSESSID',
    'is_enable' => '1',
    'sort_order' => '30',
    'memo' => '',
    'customize_1' => '0',
  ),
  31 => 
  array (
    'field_id' => '31',
    'menu_id' => '71',
    'field_name' => '记录慢查询',
    'field_code' => 'extField.fieldContainer([\'%@fieldLabel\', [
    [\'numberField\',\'@input_name\',\'PLEASE_ENTER,%@field_name\', \'\', \'@value\', {size: 4, minValue: 0, maxValue: 10}],
    lang(\'UNIT,%：,SECOND,%。0,MEAN,NO,RECORD\')
]])',
    'validate_rule' => 'int',
    'auto_operation' => '',
    'input_name' => 'sys_log_slowquery',
    'input_value' => '2',
    'is_enable' => '1',
    'sort_order' => '31',
    'memo' => '',
    'customize_1' => '0',
  ),
  40 => 
  array (
    'field_id' => '40',
    'menu_id' => '76',
    'field_name' => 'session.save_handler',
    'field_code' => 'extField.fieldContainer([\'%@fieldLabel\', [
    [null,\'@input_name\', \'PLEASE_SELECT,%@field_name\', \'\', \'@value\', {editable: false, size: 12, store: [
        [\'files\', \'files\'],
        [\'mysql\', \'mysql\'],
        [\'memcache\', \'memcache\']
    ]}],
    lang(\'SESSION_SAVE_HANDLER_TIP\')
]])',
    'validate_rule' => 'string
#{%PLEASE_SELECT,@field_name}#MUST_VALIDATE#notblank
files,mysql,files#{%@field_name,CAN_ONLY_BE,files or mysql or memcache}#VALUE_VALIDATE#in',
    'auto_operation' => '',
    'input_name' => 'sys_session_save_handler',
    'input_value' => 'files',
    'is_enable' => '1',
    'sort_order' => '31',
    'memo' => '',
    'customize_1' => '0',
  ),
  41 => 
  array (
    'field_id' => '41',
    'menu_id' => '76',
    'field_name' => 'session.gc_maxlifetime',
    'field_code' => 'extField.fieldContainer([\'%@fieldLabel\', [
    [\'numberField\',\'@input_name\',\'PLEASE_ENTER,%@field_name\', \'\', \'@value\', {minValue: 0}],
    lang(\'SESSION_LIFETIME_TIP\')
]])',
    'validate_rule' => 'int
unsigned#60',
    'auto_operation' => '',
    'input_name' => 'sys_session_gc_maxlifetime',
    'input_value' => '1800',
    'is_enable' => '1',
    'sort_order' => '31',
    'memo' => '',
    'customize_1' => '0',
  ),
  43 => 
  array (
    'field_id' => '43',
    'menu_id' => '76',
    'field_name' => 'session.save_path',
    'field_code' => 'extField.fieldContainer([\'%@fieldLabel\', [
    [null,\'@input_name\',\'\', \'\', \'@value\'],
    lang(\'SESSION_SAVE_PATH_TIP\')
], true])',
    'validate_rule' => 'string
return
validate_dir#SESSION_PATH|1|1',
    'auto_operation' => '',
    'input_name' => 'sys_session_save_path',
    'input_value' => '/',
    'is_enable' => '1',
    'sort_order' => '31',
    'memo' => '',
    'customize_1' => '0',
  ),
  44 => 
  array (
    'field_id' => '44',
    'menu_id' => '76',
    'field_name' => 'session.use_trans_id',
    'field_code' => 'extField.checkbox(\'@input_name\',\'@value\', \'%@fieldLabel\',  \'%\' + TEXT.gray(lang(\'SESSION_USE_TRANS_ID_TIP\')))',
    'validate_rule' => 'int',
    'auto_operation' => '_getCheckboxValue',
    'input_name' => 'sys_session_use_trans_id',
    'input_value' => '0',
    'is_enable' => '1',
    'sort_order' => '31',
    'memo' => '',
    'customize_1' => '0',
  ),
  45 => 
  array (
    'field_id' => '45',
    'menu_id' => '76',
    'field_name' => 'session.use_cookies',
    'field_code' => 'extField.checkbox(\'@input_name\',\'@value\', \'%@fieldLabel\',  \'%\' + TEXT.gray(lang(\'SESSION_USE_COOKIE_TIP\')))',
    'validate_rule' => 'int',
    'auto_operation' => '_getCheckboxValue',
    'input_name' => 'sys_session_use_cookies',
    'input_value' => '1',
    'is_enable' => '1',
    'sort_order' => '31',
    'memo' => '',
    'customize_1' => '0',
  ),
  46 => 
  array (
    'field_id' => '46',
    'menu_id' => '76',
    'field_name' => 'session.use_only_cookies',
    'field_code' => 'extField.checkbox(\'@input_name\',\'@value\', \'%@fieldLabel\',  \'%\' + TEXT.gray(lang(\'SESSION_USE_ONLY_COOKIES_TIP\')))',
    'validate_rule' => 'int',
    'auto_operation' => '_getCheckboxValue',
    'input_name' => 'sys_session_use_only_cookies',
    'input_value' => '1',
    'is_enable' => '1',
    'sort_order' => '31',
    'memo' => '',
    'customize_1' => '0',
  ),
  47 => 
  array (
    'field_id' => '47',
    'menu_id' => '76',
    'field_name' => 'session.cookie_secure',
    'field_code' => 'extField.checkbox(\'@input_name\',\'@value\', \'%@fieldLabel\',  \'%\' + TEXT.gray(lang(\'SESSION_COOKIE_SECURE_TIP\')))',
    'validate_rule' => 'int',
    'auto_operation' => '_getCheckboxValue',
    'input_name' => 'sys_session_cookie_secure',
    'input_value' => '0',
    'is_enable' => '1',
    'sort_order' => '31',
    'memo' => '',
    'customize_1' => '0',
  ),
  48 => 
  array (
    'field_id' => '48',
    'menu_id' => '76',
    'field_name' => 'session.cookie_httponly',
    'field_code' => 'extField.checkbox(\'@input_name\',\'@value\', \'%@fieldLabel\', \'%\' + TEXT.gray(lang(\'SESSION_COOKIE_HTTPONLY_TIP\')))',
    'validate_rule' => 'int',
    'auto_operation' => '_getCheckboxValue',
    'input_name' => 'sys_session_cookie_httponly',
    'input_value' => '0',
    'is_enable' => '1',
    'sort_order' => '31',
    'memo' => '',
    'customize_1' => '0',
  ),
  49 => 
  array (
    'field_id' => '49',
    'menu_id' => '76',
    'field_name' => 'session.cookie_domain',
    'field_code' => 'extField.fieldContainer([\'%@fieldLabel\', [
    [null, \'@input_name\', \'\', \'\', \'@value\'],
    \'@cookie=\' + System.sys_base_domain_scope
], true])',
    'validate_rule' => 'string
return',
    'auto_operation' => '',
    'input_name' => 'sys_session_cookie_domain',
    'input_value' => '@domain',
    'is_enable' => '1',
    'sort_order' => '31',
    'memo' => '',
    'customize_1' => '0',
  ),
  50 => 
  array (
    'field_id' => '50',
    'menu_id' => '76',
    'field_name' => 'session.cookie_lifetime',
    'field_code' => 'extField.fieldContainer([\'%@fieldLabel\', [
    [\'numberField\',\'@input_name\',\'PLEASE_ENTER,%@field_name\', \'\', \'@value\', {minValue: 0}],
    \'session cookie\' + lang(\'OVERDUE,TIME,UNIT,%：,SECOND,%。0,MEAN,WITH_BROWSER_PROCESS\')
]])',
    'validate_rule' => 'int
unsigned',
    'auto_operation' => '',
    'input_name' => 'sys_session_cookie_lifetime',
    'input_value' => '0',
    'is_enable' => '1',
    'sort_order' => '31',
    'memo' => '',
    'customize_1' => '0',
  ),
  51 => 
  array (
    'field_id' => '51',
    'menu_id' => '76',
    'field_name' => 'session.cookie_path',
    'field_code' => 'extField.fieldContainer([\'%@fieldLabel\', [
    [null, \'@input_name\', \'\', \'\', \'@value\'],
    (\'session cookie\' + lang(\'SAVE,PATH,%。,NOT_FILL,WILL,CN_QU,SYSTEM,DEFAULT,VALUE,%，,USUALLY,FOR\') + \'/\')
], true])',
    'validate_rule' => 'string
return',
    'auto_operation' => '',
    'input_name' => 'sys_session_cookie_path',
    'input_value' => '/',
    'is_enable' => '1',
    'sort_order' => '31',
    'memo' => '',
    'customize_1' => '0',
  ),
  32 => 
  array (
    'field_id' => '32',
    'menu_id' => '77',
    'field_name' => 'cookie域名',
    'field_code' => 'extField.fieldContainer([\'%@fieldLabel\', [
    [null, \'@input_name\', \'\', \'\', \'@value\'],
    \'@cookie=\' + System.sys_base_domain_scope
], true])',
    'validate_rule' => 'string
return',
    'auto_operation' => '',
    'input_name' => 'sys_cookie_domain',
    'input_value' => '@domain',
    'is_enable' => '1',
    'sort_order' => '32',
    'memo' => '',
    'customize_1' => '1',
  ),
  34 => 
  array (
    'field_id' => '34',
    'menu_id' => '77',
    'field_name' => 'cookie路径',
    'field_code' => 'extField.textField(\'@input_name\', \'\', \'%@fieldLabel\', \'@value\')',
    'validate_rule' => 'string
#{%PLEASE_ENTER,@field_name}#MUST_VALIDATE#return',
    'auto_operation' => '',
    'input_name' => 'sys_cookie_path',
    'input_value' => '/',
    'is_enable' => '1',
    'sort_order' => '32',
    'memo' => '',
    'customize_1' => '1',
  ),
  33 => 
  array (
    'field_id' => '33',
    'menu_id' => '77',
    'field_name' => 'cookie过期时间',
    'field_code' => 'extField.fieldContainer([\'%@fieldLabel\', [
    [\'numberField\',\'@input_name\',\'PLEASE_ENTER,%@field_name\', \'\', \'@value\', {minValue: 0}],
    lang(\'UNIT,%：,SECOND,%。0,MEAN,WITH_BROWSER_PROCESS\')
]])',
    'validate_rule' => 'int
unsigned',
    'auto_operation' => '',
    'input_name' => 'sys_cookie_expire',
    'input_value' => '0',
    'is_enable' => '1',
    'sort_order' => '33',
    'memo' => '',
    'customize_1' => '1',
  ),
  35 => 
  array (
    'field_id' => '35',
    'menu_id' => '77',
    'field_name' => 'cookie前缀',
    'field_code' => 'extField.fieldContainer([\'%@fieldLabel\', [
    [null,\'@input_name\',\'\', \'\', \'@value\'],
    lang(\'AVOID,CONFLICT\')
], true])',
    'validate_rule' => 'string
return',
    'auto_operation' => '',
    'input_name' => 'sys_cookie_prefix',
    'input_value' => 'mrmsl',
    'is_enable' => '1',
    'sort_order' => '35',
    'memo' => '',
    'customize_1' => '1',
  ),
  36 => 
  array (
    'field_id' => '36',
    'menu_id' => '71',
    'field_name' => '系统发生错误是否入库',
    'field_code' => 'extField.checkbox(\'@input_name\',\'@value\', \'%@fieldLabel\', \'%\' + TEXT.gray(lang(\'FOR_EXAMPLE,%：,MODULE,NOT_EXIST,%。,SUGGEST,TURN_ON\')))',
    'validate_rule' => 'int',
    'auto_operation' => '_getCheckboxValue',
    'input_name' => 'sys_log_systemerror',
    'input_value' => '1',
    'is_enable' => '1',
    'sort_order' => '36',
    'memo' => '',
    'customize_1' => '0',
  ),
  37 => 
  array (
    'field_id' => '37',
    'menu_id' => '71',
    'field_name' => 'SQL查询错误是否入库',
    'field_code' => 'extField.checkbox(\'@input_name\',\'@value\', \'%@fieldLabel\', \'%\' + TEXT.gray(lang(\'SUGGEST,TURN_ON\')))',
    'validate_rule' => 'int',
    'auto_operation' => '_getCheckboxValue',
    'input_name' => 'sys_log_sqlerror',
    'input_value' => '1',
    'is_enable' => '1',
    'sort_order' => '37',
    'memo' => '',
    'customize_1' => '0',
  ),
  76 => 
  array (
    'field_id' => '76',
    'menu_id' => '71',
    'field_name' => '事务回滚SQL是否入库',
    'field_code' => 'extField.checkbox(\'@input_name\',\'@value\', \'%@fieldLabel\', \'%\' + TEXT.gray(lang(\'SUGGEST,TURN_ON\')))',
    'validate_rule' => 'int',
    'auto_operation' => '_getCheckboxValue',
    'input_name' => 'sys_log_rollback_sql',
    'input_value' => '1',
    'is_enable' => '1',
    'sort_order' => '38',
    'memo' => '',
    'customize_1' => '0',
  ),
  39 => 
  array (
    'field_id' => '39',
    'menu_id' => '70',
    'field_name' => '后台皮肤样式',
    'field_code' => 'extField.textField(\'@input_name\', \'\', \'%@fieldLabel\', \'@value\')',
    'validate_rule' => 'string
notblank',
    'auto_operation' => '',
    'input_name' => 'sys_template_admin_style',
    'input_value' => '',
    'is_enable' => '1',
    'sort_order' => '39',
    'memo' => '',
    'customize_1' => '0',
  ),
  54 => 
  array (
    'field_id' => '54',
    'menu_id' => '75',
    'field_name' => 'memcache服务器ip',
    'field_code' => 'extField.textField(\'@input_name\', \'\', \'%@fieldLabel\', \'@value\')',
    'validate_rule' => 'string
notblank',
    'auto_operation' => '',
    'input_name' => 'sys_memcache_host',
    'input_value' => '127.0.0.1',
    'is_enable' => '1',
    'sort_order' => '54',
    'memo' => '',
    'customize_1' => '0',
  ),
  55 => 
  array (
    'field_id' => '55',
    'menu_id' => '75',
    'field_name' => 'memcache服务器端口',
    'field_code' => 'extField.numberField(\'@input_name\',\'\', \'%@fieldLabel\', \'@value\', {minValue:0})',
    'validate_rule' => 'int
unsigned',
    'auto_operation' => '',
    'input_name' => 'sys_memcache_port',
    'input_value' => '11211',
    'is_enable' => '1',
    'sort_order' => '55',
    'memo' => '',
    'customize_1' => '0',
  ),
  56 => 
  array (
    'field_id' => '56',
    'menu_id' => '75',
    'field_name' => 'sphinx全文索引ip',
    'field_code' => 'extField.textField(\'@input_name\', \'\', \'%@fieldLabel\', \'@value\')',
    'validate_rule' => 'string
notblank',
    'auto_operation' => '',
    'input_name' => 'sys_sphinx_host',
    'input_value' => '127.0.0.1',
    'is_enable' => '1',
    'sort_order' => '56',
    'memo' => '',
    'customize_1' => '0',
  ),
  57 => 
  array (
    'field_id' => '57',
    'menu_id' => '75',
    'field_name' => 'sphinx全文索引端口',
    'field_code' => 'extField.numberField(\'@input_name\',\'\', \'%@fieldLabel\', \'@value\', {minValue:0})',
    'validate_rule' => 'int
unsigned',
    'auto_operation' => '',
    'input_name' => 'sys_sphinx_port',
    'input_value' => '3312',
    'is_enable' => '1',
    'sort_order' => '57',
    'memo' => '',
    'customize_1' => '0',
  ),
  58 => 
  array (
    'field_id' => '58',
    'menu_id' => '78',
    'field_name' => '是否开启验证码',
    'field_code' => 'verifycode_enable',
    'validate_rule' => 'int',
    'auto_operation' => '_getCheckboxValue',
    'input_name' => 'sys_verifycode_enable',
    'input_value' => '1',
    'is_enable' => '1',
    'sort_order' => '58',
    'memo' => '',
    'customize_1' => '0',
  ),
  59 => 
  array (
    'field_id' => '59',
    'menu_id' => '78',
    'field_name' => '验证码宽度',
    'field_code' => 'verifycode_width',
    'validate_rule' => 'int
unsigned',
    'auto_operation' => '',
    'input_name' => 'sys_verifycode_width',
    'input_value' => '40',
    'is_enable' => '1',
    'sort_order' => '59',
    'memo' => '',
    'customize_1' => '0',
  ),
  60 => 
  array (
    'field_id' => '60',
    'menu_id' => '78',
    'field_name' => '验证码高度',
    'field_code' => 'verifycode_height',
    'validate_rule' => 'int
unsigned',
    'auto_operation' => '',
    'input_name' => 'sys_verifycode_height',
    'input_value' => '20',
    'is_enable' => '1',
    'sort_order' => '60',
    'memo' => '',
    'customize_1' => '0',
  ),
  61 => 
  array (
    'field_id' => '61',
    'menu_id' => '78',
    'field_name' => '验证码字母长度',
    'field_code' => 'verifycode_length',
    'validate_rule' => 'int
unsigned',
    'auto_operation' => '',
    'input_name' => 'sys_verifycode_length',
    'input_value' => '4',
    'is_enable' => '1',
    'sort_order' => '61',
    'memo' => '',
    'customize_1' => '0',
  ),
  70 => 
  array (
    'field_id' => '70',
    'menu_id' => '78',
    'field_name' => '验证码顺序',
    'field_code' => 'verifycode_order',
    'validate_rule' => 'string
return',
    'auto_operation' => '',
    'input_name' => 'sys_verifycode_order',
    'input_value' => '0',
    'is_enable' => '1',
    'sort_order' => '61',
    'memo' => '',
    'customize_1' => '0',
  ),
  72 => 
  array (
    'field_id' => '72',
    'menu_id' => '78',
    'field_name' => '验证码刷新次数限制',
    'field_code' => 'verifycode_refresh_limit',
    'validate_rule' => 'string
_checkExplodeNumericFormat#{%PLEASE_ENTER,CORRECT,FORMAT,CN_DE,@field_name}#VALUE_VALIDATE#callback',
    'auto_operation' => '',
    'input_name' => 'sys_verifycode_refresh_limit',
    'input_value' => '5/10',
    'is_enable' => '1',
    'sort_order' => '61',
    'memo' => '',
    'customize_1' => '0',
  ),
  73 => 
  array (
    'field_id' => '73',
    'menu_id' => '78',
    'field_name' => '验证码错误次数限制',
    'field_code' => 'verifycode_error_limit',
    'validate_rule' => 'string
_checkExplodeNumericFormat#{%PLEASE_ENTER,CORRECT,FORMAT,CN_DE,@field_name}#VALUE_VALIDATE#callback',
    'auto_operation' => '',
    'input_name' => 'sys_verifycode_error_limit',
    'input_value' => '5/10',
    'is_enable' => '1',
    'sort_order' => '61',
    'memo' => '',
    'customize_1' => '0',
  ),
  71 => 
  array (
    'field_id' => '71',
    'menu_id' => '78',
    'field_name' => '验证码是否区分大小写',
    'field_code' => 'verifycode_case',
    'validate_rule' => 'int
unsigned',
    'auto_operation' => '_getCheckboxValue',
    'input_name' => 'sys_verifycode_case',
    'input_value' => '',
    'is_enable' => '1',
    'sort_order' => '62',
    'memo' => '',
    'customize_1' => '0',
  ),
  62 => 
  array (
    'field_id' => '62',
    'menu_id' => '78',
    'field_name' => '验证码类型',
    'field_code' => 'verifycode_type',
    'validate_rule' => 'int
unsigned#-1',
    'auto_operation' => '_getCheckboxValue',
    'input_name' => 'sys_verifycode_type',
    'input_value' => '5',
    'is_enable' => '1',
    'sort_order' => '63',
    'memo' => '',
    'customize_1' => '0',
  ),
  63 => 
  array (
    'field_id' => '63',
    'menu_id' => '80',
    'field_name' => '后台登陆及修改密码开启验证码',
    'field_code' => 'verifycode_enable@',
    'validate_rule' => 'int',
    'auto_operation' => '_getCheckboxValue',
    'input_name' => 'module_admin_verifycode_enable',
    'input_value' => '1',
    'is_enable' => '1',
    'sort_order' => '63',
    'memo' => '',
    'customize_1' => '0',
  ),
  95 => 
  array (
    'field_id' => '95',
    'menu_id' => '84',
    'field_name' => '开启验证码',
    'field_code' => 'verifycode_enable@',
    'validate_rule' => 'int',
    'auto_operation' => '_getCheckboxValue',
    'input_name' => 'module_guestbook_verifycode_enable',
    'input_value' => '0',
    'is_enable' => '1',
    'sort_order' => '63',
    'memo' => '',
    'customize_1' => '0',
  ),
  104 => 
  array (
    'field_id' => '104',
    'menu_id' => '99',
    'field_name' => '开启验证码',
    'field_code' => 'verifycode_enable@',
    'validate_rule' => 'int',
    'auto_operation' => '_getCheckboxValue',
    'input_name' => 'module_comments_verifycode_enable',
    'input_value' => '0',
    'is_enable' => '1',
    'sort_order' => '63',
    'memo' => '',
    'customize_1' => '0',
  ),
  64 => 
  array (
    'field_id' => '64',
    'menu_id' => '80',
    'field_name' => '验证码宽度',
    'field_code' => 'verifycode_width@',
    'validate_rule' => 'int
unsigned',
    'auto_operation' => '',
    'input_name' => 'module_admin_verifycode_width',
    'input_value' => '40',
    'is_enable' => '1',
    'sort_order' => '64',
    'memo' => '',
    'customize_1' => '0',
  ),
  94 => 
  array (
    'field_id' => '94',
    'menu_id' => '84',
    'field_name' => '验证码宽度',
    'field_code' => 'verifycode_width@',
    'validate_rule' => 'int
unsigned',
    'auto_operation' => '',
    'input_name' => 'module_guestbook_verifycode_width',
    'input_value' => '40',
    'is_enable' => '1',
    'sort_order' => '64',
    'memo' => '',
    'customize_1' => '0',
  ),
  103 => 
  array (
    'field_id' => '103',
    'menu_id' => '99',
    'field_name' => '验证码宽度',
    'field_code' => 'verifycode_width@',
    'validate_rule' => 'int
unsigned',
    'auto_operation' => '',
    'input_name' => 'module_comments_verifycode_width',
    'input_value' => '40',
    'is_enable' => '1',
    'sort_order' => '64',
    'memo' => '',
    'customize_1' => '0',
  ),
  65 => 
  array (
    'field_id' => '65',
    'menu_id' => '80',
    'field_name' => '验证码高度',
    'field_code' => 'verifycode_height@',
    'validate_rule' => 'int
unsigned',
    'auto_operation' => '',
    'input_name' => 'module_admin_verifycode_height',
    'input_value' => '20',
    'is_enable' => '1',
    'sort_order' => '65',
    'memo' => '',
    'customize_1' => '0',
  ),
  93 => 
  array (
    'field_id' => '93',
    'menu_id' => '84',
    'field_name' => '验证码高度',
    'field_code' => 'verifycode_height@',
    'validate_rule' => 'int
unsigned',
    'auto_operation' => '',
    'input_name' => 'module_guestbook_verifycode_height',
    'input_value' => '20',
    'is_enable' => '1',
    'sort_order' => '65',
    'memo' => '',
    'customize_1' => '0',
  ),
  102 => 
  array (
    'field_id' => '102',
    'menu_id' => '99',
    'field_name' => '验证码高度',
    'field_code' => 'verifycode_height@',
    'validate_rule' => 'int
unsigned',
    'auto_operation' => '',
    'input_name' => 'module_comments_verifycode_height',
    'input_value' => '20',
    'is_enable' => '1',
    'sort_order' => '65',
    'memo' => '',
    'customize_1' => '0',
  ),
  66 => 
  array (
    'field_id' => '66',
    'menu_id' => '80',
    'field_name' => '验证码字母长度',
    'field_code' => 'verifycode_length@',
    'validate_rule' => 'int
unsigned',
    'auto_operation' => '',
    'input_name' => 'module_admin_verifycode_length',
    'input_value' => '5',
    'is_enable' => '1',
    'sort_order' => '66',
    'memo' => '',
    'customize_1' => '0',
  ),
  69 => 
  array (
    'field_id' => '69',
    'menu_id' => '80',
    'field_name' => '验证码顺序',
    'field_code' => 'verifycode_order@',
    'validate_rule' => 'string
return',
    'auto_operation' => '',
    'input_name' => 'module_admin_verifycode_order',
    'input_value' => '4312',
    'is_enable' => '1',
    'sort_order' => '66',
    'memo' => '',
    'customize_1' => '0',
  ),
  74 => 
  array (
    'field_id' => '74',
    'menu_id' => '80',
    'field_name' => '验证码刷新次数限制',
    'field_code' => 'verifycode_refresh_limit@',
    'validate_rule' => 'string
_checkExplodeNumericFormat#{%PLEASE_ENTER,CORRECT,FORMAT,CN_DE,@field_name}#VALUE_VALIDATE#callback',
    'auto_operation' => '',
    'input_name' => 'module_admin_verifycode_refresh_limit',
    'input_value' => '',
    'is_enable' => '1',
    'sort_order' => '66',
    'memo' => '',
    'customize_1' => '0',
  ),
  75 => 
  array (
    'field_id' => '75',
    'menu_id' => '80',
    'field_name' => '验证码错误次数限制',
    'field_code' => 'verifycode_error_limit@',
    'validate_rule' => 'string
_checkExplodeNumericFormat#{%PLEASE_ENTER,CORRECT,FORMAT,CN_DE,@field_name}#VALUE_VALIDATE#callback',
    'auto_operation' => '',
    'input_name' => 'module_admin_verifycode_error_limit',
    'input_value' => '',
    'is_enable' => '1',
    'sort_order' => '66',
    'memo' => '',
    'customize_1' => '0',
  ),
  87 => 
  array (
    'field_id' => '87',
    'menu_id' => '84',
    'field_name' => '验证码错误次数限制',
    'field_code' => 'verifycode_error_limit@',
    'validate_rule' => 'string
_checkExplodeNumericFormat#{%PLEASE_ENTER,CORRECT,FORMAT,CN_DE,@field_name}#VALUE_VALIDATE#callback',
    'auto_operation' => '',
    'input_name' => 'module_guestbook_verifycode_error_limit',
    'input_value' => '',
    'is_enable' => '1',
    'sort_order' => '66',
    'memo' => '',
    'customize_1' => '0',
  ),
  88 => 
  array (
    'field_id' => '88',
    'menu_id' => '84',
    'field_name' => '验证码刷新次数限制',
    'field_code' => 'verifycode_refresh_limit@',
    'validate_rule' => 'string
_checkExplodeNumericFormat#{%PLEASE_ENTER,CORRECT,FORMAT,CN_DE,@field_name}#VALUE_VALIDATE#callback',
    'auto_operation' => '',
    'input_name' => 'module_guestbook_verifycode_refresh_limit',
    'input_value' => '',
    'is_enable' => '1',
    'sort_order' => '66',
    'memo' => '',
    'customize_1' => '0',
  ),
  89 => 
  array (
    'field_id' => '89',
    'menu_id' => '84',
    'field_name' => '验证码顺序',
    'field_code' => 'verifycode_order@',
    'validate_rule' => 'string
return',
    'auto_operation' => '',
    'input_name' => 'module_guestbook_verifycode_order',
    'input_value' => '4312',
    'is_enable' => '1',
    'sort_order' => '66',
    'memo' => '',
    'customize_1' => '0',
  ),
  92 => 
  array (
    'field_id' => '92',
    'menu_id' => '84',
    'field_name' => '验证码字母长度',
    'field_code' => 'verifycode_length@',
    'validate_rule' => 'int
unsigned',
    'auto_operation' => '',
    'input_name' => 'module_guestbook_verifycode_length',
    'input_value' => '5',
    'is_enable' => '1',
    'sort_order' => '66',
    'memo' => '',
    'customize_1' => '0',
  ),
  96 => 
  array (
    'field_id' => '96',
    'menu_id' => '99',
    'field_name' => '验证码错误次数限制',
    'field_code' => 'verifycode_error_limit@',
    'validate_rule' => 'string
_checkExplodeNumericFormat#{%PLEASE_ENTER,CORRECT,FORMAT,CN_DE,@field_name}#VALUE_VALIDATE#callback',
    'auto_operation' => '',
    'input_name' => 'module_comments_verifycode_error_limit',
    'input_value' => '',
    'is_enable' => '1',
    'sort_order' => '66',
    'memo' => '',
    'customize_1' => '0',
  ),
  97 => 
  array (
    'field_id' => '97',
    'menu_id' => '99',
    'field_name' => '验证码刷新次数限制',
    'field_code' => 'verifycode_refresh_limit@',
    'validate_rule' => 'string
_checkExplodeNumericFormat#{%PLEASE_ENTER,CORRECT,FORMAT,CN_DE,@field_name}#VALUE_VALIDATE#callback',
    'auto_operation' => '',
    'input_name' => 'module_comments_verifycode_refresh_limit',
    'input_value' => '',
    'is_enable' => '1',
    'sort_order' => '66',
    'memo' => '',
    'customize_1' => '0',
  ),
  98 => 
  array (
    'field_id' => '98',
    'menu_id' => '99',
    'field_name' => '验证码顺序',
    'field_code' => 'verifycode_order@',
    'validate_rule' => 'string
return',
    'auto_operation' => '',
    'input_name' => 'module_comments_verifycode_order',
    'input_value' => '4312',
    'is_enable' => '1',
    'sort_order' => '66',
    'memo' => '',
    'customize_1' => '0',
  ),
  101 => 
  array (
    'field_id' => '101',
    'menu_id' => '99',
    'field_name' => '验证码字母长度',
    'field_code' => 'verifycode_length@',
    'validate_rule' => 'int
unsigned',
    'auto_operation' => '',
    'input_name' => 'module_comments_verifycode_length',
    'input_value' => '5',
    'is_enable' => '1',
    'sort_order' => '66',
    'memo' => '',
    'customize_1' => '0',
  ),
  68 => 
  array (
    'field_id' => '68',
    'menu_id' => '80',
    'field_name' => '验证码是否区分大小写',
    'field_code' => 'verifycode_case@',
    'validate_rule' => 'int',
    'auto_operation' => '_getCheckboxValue',
    'input_name' => 'module_admin_verifycode_case',
    'input_value' => '1',
    'is_enable' => '1',
    'sort_order' => '67',
    'memo' => '',
    'customize_1' => '0',
  ),
  90 => 
  array (
    'field_id' => '90',
    'menu_id' => '84',
    'field_name' => '验证码是否区分大小写',
    'field_code' => 'verifycode_case@',
    'validate_rule' => 'int',
    'auto_operation' => '_getCheckboxValue',
    'input_name' => 'module_guestbook_verifycode_case',
    'input_value' => '1',
    'is_enable' => '1',
    'sort_order' => '67',
    'memo' => '',
    'customize_1' => '0',
  ),
  99 => 
  array (
    'field_id' => '99',
    'menu_id' => '99',
    'field_name' => '验证码是否区分大小写',
    'field_code' => 'verifycode_case@',
    'validate_rule' => 'int',
    'auto_operation' => '_getCheckboxValue',
    'input_name' => 'module_comments_verifycode_case',
    'input_value' => '1',
    'is_enable' => '1',
    'sort_order' => '67',
    'memo' => '',
    'customize_1' => '0',
  ),
  67 => 
  array (
    'field_id' => '67',
    'menu_id' => '80',
    'field_name' => '验证码类型',
    'field_code' => 'verifycode_type@',
    'validate_rule' => 'int
unsigned#-2',
    'auto_operation' => '_getCheckboxValue',
    'input_name' => 'module_admin_verifycode_type',
    'input_value' => '5',
    'is_enable' => '1',
    'sort_order' => '68',
    'memo' => '',
    'customize_1' => '0',
  ),
  91 => 
  array (
    'field_id' => '91',
    'menu_id' => '84',
    'field_name' => '验证码类型',
    'field_code' => 'verifycode_type@',
    'validate_rule' => 'int
unsigned#-2',
    'auto_operation' => '_getCheckboxValue',
    'input_name' => 'module_guestbook_verifycode_type',
    'input_value' => '5',
    'is_enable' => '1',
    'sort_order' => '68',
    'memo' => '',
    'customize_1' => '0',
  ),
  100 => 
  array (
    'field_id' => '100',
    'menu_id' => '99',
    'field_name' => '验证码类型',
    'field_code' => 'verifycode_type@',
    'validate_rule' => 'int
unsigned#-2',
    'auto_operation' => '_getCheckboxValue',
    'input_name' => 'module_comments_verifycode_type',
    'input_value' => '5',
    'is_enable' => '1',
    'sort_order' => '68',
    'memo' => '',
    'customize_1' => '0',
  ),
  83 => 
  array (
    'field_id' => '83',
    'menu_id' => '100',
    'field_name' => '标题分割符',
    'field_code' => 'extField.textField(\'@input_name\', \'\', \'%@fieldLabel\', \'@value\')',
    'validate_rule' => 'string
notblank',
    'auto_operation' => '',
    'input_name' => 'sys_show_title_separator',
    'input_value' => '-',
    'is_enable' => '1',
    'sort_order' => '83',
    'memo' => '',
    'customize_1' => '0',
  ),
  84 => 
  array (
    'field_id' => '84',
    'menu_id' => '100',
    'field_name' => '面包屑分割符',
    'field_code' => 'extField.textField(\'@input_name\', \'\', \'%@fieldLabel\', \'@value\')',
    'validate_rule' => 'raw
notblank',
    'auto_operation' => '',
    'input_name' => 'sys_show_bread_separator',
    'input_value' => '&raquo;',
    'is_enable' => '1',
    'sort_order' => '83',
    'memo' => '',
    'customize_1' => '0',
  ),
  11 => 
  array (
    'field_id' => '11',
    'menu_id' => '43',
    'field_name' => '网站版权信息',
    'field_code' => 'extField.textarea(\'@input_name\', \'PLEASE_ENTER,%@field_name\', \'%@fieldLabel\', \'@value\', {width: 700, height: 120})',
    'validate_rule' => 'raw
notblank',
    'auto_operation' => '',
    'input_name' => 'sys_base_copyright',
    'input_value' => 'Copyright &copy; 2013 yablog 版权所有',
    'is_enable' => '1',
    'sort_order' => '99',
    'memo' => '',
    'customize_1' => '0',
  ),
  110 => 
  array (
    'field_id' => '110',
    'menu_id' => '101',
    'field_name' => '留言评论禁用用户名',
    'field_code' => 'guestbook_comments_disabled_username',
    'validate_rule' => 'string',
    'auto_operation' => '',
    'input_name' => 'module_guestbook_comments_disabled_username',
    'input_value' => 'admin
yablog
mrmsl',
    'is_enable' => '1',
    'sort_order' => '110',
    'memo' => '',
    'customize_1' => '0',
  ),
  113 => 
  array (
    'field_id' => '113',
    'menu_id' => '101',
    'field_name' => '禁止留言评论ip',
    'field_code' => 'guestbook_comments_disabled_ip',
    'validate_rule' => 'string',
    'auto_operation' => '',
    'input_name' => 'module_guestbook_comments_disabled_ip',
    'input_value' => '',
    'is_enable' => '1',
    'sort_order' => '113',
    'memo' => '',
    'customize_1' => '0',
  ),
  119 => 
  array (
    'field_id' => '119',
    'menu_id' => '45',
    'field_name' => '邮件发送方法',
    'field_code' => 'extField.textField(\'@input_name\', \'PLEASE_ENTER,%@field_name\', \'%@fieldLabel\', \'@value\')',
    'validate_rule' => 'string
notblank',
    'auto_operation' => '',
    'input_name' => 'sys_mail_method',
    'input_value' => 'smtp',
    'is_enable' => '1',
    'sort_order' => '119',
    'memo' => '',
    'customize_1' => '0',
  ),
  120 => 
  array (
    'field_id' => '120',
    'menu_id' => '45',
    'field_name' => 'smtp服务器',
    'field_code' => 'extField.textField(\'@input_name\', \'PLEASE_ENTER,%@field_name\', \'%@fieldLabel\', \'@value\')',
    'validate_rule' => 'string
notblank',
    'auto_operation' => '',
    'input_name' => 'sys_mail_smtp',
    'input_value' => 'smtp.163.com',
    'is_enable' => '1',
    'sort_order' => '120',
    'memo' => '',
    'customize_1' => '0',
  ),
  121 => 
  array (
    'field_id' => '121',
    'menu_id' => '45',
    'field_name' => 'smtp端口',
    'field_code' => 'extField.numberField(\'@input_name\',\'\', \'%@fieldLabel\', \'@value\', {minValue:0})',
    'validate_rule' => 'int
unsigned',
    'auto_operation' => '',
    'input_name' => 'sys_mail_smtp_port',
    'input_value' => '25',
    'is_enable' => '1',
    'sort_order' => '121',
    'memo' => '',
    'customize_1' => '0',
  ),
  122 => 
  array (
    'field_id' => '122',
    'menu_id' => '45',
    'field_name' => '邮箱帐号',
    'field_code' => 'extField.textField(\'@input_name\', \'PLEASE_ENTER,%@field_name\', \'%@fieldLabel\', \'@value\')',
    'validate_rule' => 'email
notblank',
    'auto_operation' => '',
    'input_name' => 'sys_mail_email',
    'input_value' => 'yablog@163.com',
    'is_enable' => '1',
    'sort_order' => '122',
    'memo' => '',
    'customize_1' => '0',
  ),
  123 => 
  array (
    'field_id' => '123',
    'menu_id' => '45',
    'field_name' => '邮箱密码',
    'field_code' => 'extField.textField(\'@input_name\', \'PLEASE_ENTER,%@field_name\', \'%@fieldLabel\', \'@value\', {inputType: \'password\'})',
    'validate_rule' => 'string
notblank',
    'auto_operation' => '',
    'input_name' => 'sys_mail_password',
    'input_value' => 'mrmsl170066918',
    'is_enable' => '1',
    'sort_order' => '123',
    'memo' => '',
    'customize_1' => '0',
  ),
  125 => 
  array (
    'field_id' => '125',
    'menu_id' => '45',
    'field_name' => '显示发件人姓名',
    'field_code' => 'extField.textField(\'@input_name\', \'PLEASE_ENTER,%@field_name\', \'%@fieldLabel\', \'@value\')',
    'validate_rule' => 'string
notblank',
    'auto_operation' => '',
    'input_name' => 'sys_mail_from_name',
    'input_value' => 'mrmsl',
    'is_enable' => '1',
    'sort_order' => '125',
    'memo' => '',
    'customize_1' => '0',
  ),
  4 => 
  array (
    'field_id' => '4',
    'menu_id' => '76',
    'field_name' => '安全设置',
    'field_code' => 'extField.textField(\'@input_name\', \'\', \'%@fieldLabel\', \'@value\')',
    'validate_rule' => 'string',
    'auto_operation' => '',
    'input_name' => 'sys_security_setting',
    'input_value' => '安全设置',
    'is_enable' => '0',
    'sort_order' => '0',
    'memo' => '',
    'customize_1' => '0',
  ),
);