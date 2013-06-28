/**
 * 通用js
 *
 * @file            app/util/common.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-03-31 21:50:47
 * @lastmodify      $Date$ $Author$
 */

//常用配置
var C = {
    dataType: 'ajax',//'jsonp'
    dataReader: function (cfg) {//proxy reader
        return Ext.apply({
            type: 'json',
            root: 'data'
        }, cfg || {});
    },
    headerSortCls: {//grid头部排序class类名
        ASC: 'x-column-header-sort-ASC',
        DESC: 'x-column-header-sort-DESC',
    },
    images: {//小图标
        yes: System.sys_base_common_imgcache + 'images/icons/yes.gif',
        no: System.sys_base_common_imgcache + 'images/icons/no.gif',
        loading: System.sys_base_common_imgcache + 'images/icons/loading.gif'//加载中 by mrmsl on 2012-08-22 14:09:46
    }
};

C.images['0'] = '<img alt="" src="' + C.images.no + '" />';
C.images['1'] = '<img alt="" src="' + C.images.yes + '" />';

//提示文字信息
var TEXT = {
    common: function(color, text, extra) {
        return '<span style="color: {0};{1}">{2}</span>'.format(color, extra || '', text);
    },
    red: function(text) {//红
        return this.common('red', text || '*');
    },
    green: function(text) {//绿
        return this.common('green', text);
    },
    gray: function(text, extra) {//灰
        return this.common('gray', text, extra === undefined ? 'padding-left: 4px;' : '');
    },
    strong: function(text, extra) {//strong by mrmsl on 2012-08-28 11:19:36
        return '<span style="font-weight: bold;{0}">{1}</span>'.format(extra ? extra : '', text);
    }
};

//字符串格式化输出
String.prototype.format = function() {

    if (typeof arguments[0] == 'object') {//json形，如'a{a}b{b}'.format({a: 'a', b: 'b'}) => aabb
        var args = arguments[0], pattern = /\{(\w+)\}/g;
    }
    else {//数字形，如format('a{1}b{2}', 'a', 'b') => aabb
        var args = arguments, pattern = /\{(\d+)\}/g;
    }

    return this.replace(pattern, function(m, i) {
        return args[i];
    });
};

//去除左右空白，支持自定义需要去除的字符列表 by mrmsl on 2012-07-28 10:29:41
String.prototype.ltrim = function(charlist, mode) {
    var patten = new RegExp('^' + (charlist || '\\s+'), mode || 'g');
    return this.replace(patten, '');
};
String.prototype.rtrim = function(charlist, mode) {
    var patten = new RegExp((charlist || '\\s+') + '$', mode || 'g');
    return this.replace(patten, '');
};
String.prototype.trim = function(charlist, mode) {
    charlist = charlist || '\\s';
    var patten = new RegExp('^' + charlist + '+|' + charlist + '+' + '$', mode || 'g');
    return this.replace(patten, '');
};

/**
 * 获取参数，类似php $_GET。不支持获取数组
 *
 * @member window
 *
 * @param {String} name 参数名称
 * @param {String} [str=location\.href]  匹配字符串
 *
 * @return {String} 参数值或空字符串
 */
function _GET(name, str) {
    var pattern = new RegExp('[\?&]' + name + '=([^&]+)', 'g');
    str = str || location.href;
    var arr, match = '';

    while ((arr = pattern.exec(str)) !== null) {
        match = arr[1];
    }

    return match;
}

/**
 * 友好提示
 *
 * @member window
 * @method
 *
 * @param {String}  msg 提示内容
 * @param {Mixed} [success=true] true成功，false失败，loading处理中提示
 * @param {Boolean} [cancel=false] true清除提示，false显示提示
 * @param {Number} [timeout=1500] 提示停留时间，单位：毫秒
 *
 * @return {void} 无返回值
 */
function Alert(msg, success, cancel, timeout) {
    var div = Ext.get('div-alert'), body = Ext.getBody();

    if (!cancel) {

        if (success === 'loading') {
            var background = '#ff8', color = '#333';
        }
        else {
            var color = '#fff', background = success === undefined ? '#16960e' : '#d90000';
        }

        if (!div) {
            div = Ext.DomHelper.append(body, {
                id: 'div-alert',
                style: 'float: left; position: absolute; padding: 4px 10px'
            }, true).hover(function() {
                Ext.get(this).show(true);
                clearInterval(window.AlertTimeout);
            }, hideAlert);
        }
        else {
            div.hide();
            clearInterval(window.AlertTimeout);
        }

        div.update(msg);
        div.show().setStyle({
            color: color,
            width: 'auto',
            'z-index': 99999,
            'background-color': background,
            nothing: true
        });
        var width = div.getWidth();
        width = Ext.Number.constrain(width, 100, 800);
        div.setStyle({
            width: width + 'px',
            'text-align': width < 800 ? 'center' : 'left'
        }).alignTo(body, 't', [-width / 2, 0]);
        hideAlert(timeout);
    }

    else {
        div.hide();
    }
}//end Alert

/**
 * 设置或取消遮罩
 *
 * @member window
 *
 * @param {String/Boolean} [msg=PROCESSING语言项] 遮罩信息，false为取消遮罩
 *
 * @return {void} 无返回值
 */
function bodyMask(msg) {
    var body = Ext.getBody();

    msg === false ? body.unmask() : body.mask(msg || lang('PROCESSING'));
}

/**
 * 检查权限
 *
 * @member window
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-07-12 16:30:20
 * @lastmodify      2013-01-12 15:31:47 by mrmsl
 *
 * @param {String} controller 控制器
 * @param {String} action     操作方法
 *
 * @return {Boolean} 如果有权限，返回true，否则给出提示，并返回false
 */
function checkPriv(controller, action) {
    controller = controller.toLowerCase();
    action = action.toLowerCase();

    if (Ext.Array.indexOf(['index'], controller) == -1 && ADMIN_INFO.roleId != ADMIN_ROLE_ID && Ext.Array.indexOf(ADMIN_INFO.priv, controller + action) == -1) {
        warn(lang('NO_PERMISSION'));
        return false;
    }

    return true;
}

/**
 * 通用ajax错误
 *
 * @member window
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-07-07 09:37:28
 * @lastmodify      2013-01-12 15:31:42 by mrmsl
 *
 * @param {Object} action 后端返回错误信息
 *
 * @return {void} 无返回值
 */
function commonFailure(action) {

    if (action.result) {//200

        if (null === action.result.data && !action.result.msg) {
            error(lang('SERVER_ERROR'));
        }
        else {
            error(action.result.msg || lang('SERVER_ERROR'));
        }
    }
    else if (action.responseText || action.response) {//非200
        var status = intval(action.status || action.response.status);

        switch (status) {

            case 401://未登陆
                Alert(lang('LOGIN_TIMEOUT'), false, false, 5000);
                global('app_login_win', global('app_login_win') || Ext.create('Yab.controller.Login').win());
                break;

            case 403://无权限
                error(lang('NO_PERMISSION'));
                break;

            default:
                error(lang('SERVER_ERROR'));
                break;
        }
    }
    else {//其它
        error(lang('SERVER_ERROR'));
    }
}

/**
 * 格式化时间，类似php date函数
 *
 * @member window
 *
 * @param {String} format      格式
 * @param {Mixed} [constructor] 日期初始化参数
 *
 * @return {String} 格式化后的时间
 */
function date(format, constructor) {

    if (typeof(constructor) == 'object') {//已经是日期类型
        var datetime = constructor;
    }
    else {
        var datetime = constructor ? new Date(constructor) : new Date();
    }

    format = format || System.sys_timezone_datetime_format;

    var o = {
        'Y': datetime.getFullYear(),
        'm': datetime.getMonth() + 1,
        'd': datetime.getDate(),
        'H': datetime.getHours(),
        'i': datetime.getMinutes(),
        's': datetime.getSeconds()
    };

    for (var i in o) {
        _s = i == 'Y' ? o[i] : str_pad(o[i], 2, '0');//不为年，补0
        format = format.replace(new RegExp(i, 'g'), _s);
    }

    return format;
}

/**
 * 错误框
 *
 * @member window
 *
 * @param {String} msg 错误内容
 * @param {String} [title=SYSTEM_INFOMATION语言项] 标题
 * @param {String} [red=undefined] 红色字体
 *
 * @return {void} 无返回值
 */
function error(msg, title, red) {
    msg = red === undefined ? TEXT.red(msg) : msg;
    Ext.Msg.show({
        msg: msg,
        title: getMsgTitle(title),
        icon: Ext.Msg.ERROR,
        buttons: Ext.Msg.OK,
        modal: true
    });
}

/**
 * 加载数据异常配置
 *
 * @member window
 *
 * @return {Object} 异常配置
 */
function exception() {
    return {
        /**
         * 加载数据异常处理
         *
         * @ignore
         *
         * @param {Object} proxy     Ext.data.proxy.Proxy
         * @param {Object} response  The response from the AJAX request
         * @param {Object} operation The operation that triggered request
         * @param {Object} eOpts     The options object passed to Ext.util.Observable.addListener.
         *
         * @return {void} 无返回值
         */
        exception: function(proxy, response, operation, eOpts) {
            commonFailure(response);
        }
    }
}

/**
 * Yab.controller.Base.field入口
 *
 * @member window
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-09-24 15:41:40
 * @lastmodify      2013-01-12 16:01:41 by mrmsl
 *
 * @return {Object} 各种textfield方法
 */
function extField() {
    var controller = _GET('controller', '?' + location.hash);
    controller = getController(Ext.String.capitalize(controller));

    return controller.field().call(controller);
}

/**
 * Yab.controller.Base.combo入口
 *
 * @member window
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-09-24 15:42:18
 * @lastmodify      2013-01-12 16:02:00 by mrmsl
 *
 * @return {Object} 各种textfield方法
 */
function extCombo() {
    var controller = _GET('controller', '?' + location.hash);
    controller = getController(Ext.String.capitalize(controller));

    return controller.field().call(controller);
}

/**
 * 格式化字节大小
 *
 * @member window
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-06-28 13:05:31
 *
 * @param {Number} filesize  文件大小，单位：字节
 * @param {Number} [precision=2] 小数点数
 *
 * @return string 带单位的文件大小
 */
function formatSize(filesize, precision) {
    var unit;

    if (filesize >= 1073741824) {
        filesize = Math.round(filesize / 1073741824 * 100) / 100;
        unit     = 'GB';
    }
    else if (filesize >= 1048576) {
        filesize = Math.round(filesize / 1048576 * 100) / 100 ;
        unit     = 'MB';
    }
    else if(filesize >= 1024) {
        filesize = Math.round(filesize / 1024 * 100) / 100;
        unit     = 'KB';
    }
    else {
        filesize = filesize;
        unit     = 'Bytes';
    }

    return '' + toFixed(filesize) + ' ' + unit;;
}

/**
 * 计算执行时间，类似thinkphp G函数
 *
 * @member window
 * @method
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-12-02 09:08:28
 * @lastmodify      2013-01-12 16:02:22 by mrmsl
 *
 * @param {String} start 开始标识符
 * @param {Mixed}  [end] 结束标识符或时间
 * @param {Number} [precision=4] 小数点位数

 * @return {Mixed} 执行时间
 */
function G(start, end, precision) {

    if (!end) {//计时，时间戳
        window[start] = new Date().getTime();
    }
    else if (typeof(end) == 'number') {//计时，指定时间
        window[start] = end;
    }
    else {//计算

        if (!window[end]) {
            window[end] = new Date().getTime();
        }

        return toFixed((window[end] - window[start]) / 1000, precision || 4);
    }
}

/**
 * 获取请求url
 *
 * @member window
 *
 * @param {String} url 请求url
 *
 * @return {String} 请求url
 */
function getActionUrl(url) {
    return System.sys_base_admin_entry + '/' + url;
}

/**
 * 获取控制器
 *
 * @member window
 *
 * @param {String} controller 控制器名称
 *
 * @throws 异常
 * @return {Object} 控制器存在，返回控制器实例，否则抛出异常
 */
function getController(controller) {
    controller = controller || Ext.String.capitalize(_GET('controller', '?' + Ext.History.getToken()));

    try {
        return Yab.App.getController('Yab.controller.' + controller);
    }
    catch (e) {
        error(lang('CONTROLLER') + '<span class="font-red">' + controller + '</span> ' + lang('NOT_EXIST'));
    }
}
/**
 * 获取消息弹出层标题
 *
 * @member window
 *
 * @param {String} [title=SYSTEM_INFOMATION语言项] 标题
 *
 * @return {String} 标题
 */
function getMsgTitle(title) {
    return title || lang('SYSTEM_INFOMATION');
}

/**
 * 设置或获取全局变量，如果只传一个参数，则取该参数值;否则设置变量，第一个参数为变量名，第二个参数为变量值
 *
 * @member window
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-07-31 09:13:17
 * @lastmodify      2013-01-12 16:14:38 by mrmsl
 *
 * @return {Mixed} 如果只传一个参数，则返回参数值;否则返回true
 */
function global() {

    if (arguments.length == 1) {//取值
        return Ext.global[arguments[0]];
    }

    Ext.global[arguments[0]] = arguments[1];

    return true;
}

/**
 * 隐藏友好提示
 *
 * @member window
 *
 * @param {Number} [timeout=2000] 提示停留时间，单位：毫秒
 *
 * @return {void} 无返回值
 */
function hideAlert(timeout) {

    if (timeout !== false) {
        window.AlertTimeout = setTimeout(function() {
            Alert(false, false, true);
        }, timeout || 2000);
    }
}

/**
 * 转义html
 *
 * @member window
 *
 * @param {String} str 待转义字符串
 *
 * @return {String} 转义后的字符串
 */
function htmlspecialchars(str) {
    return str.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\"/g, '&quot;').replace(/\'/g, '&#39;');
}

/**
 * 提示
 *
 * @member window
 *
 * @param {String} msg 内容
 * @param {String} [title=SYSTEM_INFOMATION语言项] 标题
 * @param {String} [green=undefined] 绿色字体
 *
 * @return {void} 无返回值
 */
function info(msg, title, green) {
    msg = green === undefined ? TEXT.green(msg) : msg;
    Ext.Msg.show({
        msg: msg,
        title: getMsgTitle(title),
        icon: Ext.Msg.INFO,
        buttons: Ext.Msg.OK,
        modal: true
    });
}

/**
 * 转化为整数，类似php intval函数
 *
 * @member window
 *
 * @param {Mixed} str   需要转换的字符串
 * @param {Number} [def=0] 转换失败默认值
 * @param {Number} [radix=10] 进制
 *
 * @return {Number} 转化后的整数
 */
function intval(str, def, radix) {
    radix = radix || 10;
    var str = parseInt(str, radix);

    return isNaN(str) ? parseInt(def == undefined ? 0 : def, radix) : str;
}

/**
 * 设置或获取语言，支持批量
 *
 * @member window
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-07-04 11:17:12
 * @lastmodify      2013-01-12 16:19:06 by mrmsl
 *
 * @param {Mixed} name  名
 * @param {Mixed} value 值
 *
 * @return {Mixed} 如果不传参数name，将返回整个语言包；如果name为object或传value，将设置语言；否则返回指定语言
 */
function lang(name, value) {

    if (!name) {//返回整个语言包
        return L;
    }
    else if (typeof(name) == 'object') {//批量设置
        return Ext.apply(L, name);
    }
    else if (value !== undefined) {//单个
        L[name.toUpperCase()] = value;
        return L;
    }
    else {//取值
        var _lang = '';

        Ext.each(name.split(','), function(item) {

            if (item.indexOf('%') == 0) {//支持原形返回
                _lang += item.substr(1);
            }
            else {//如果设置值，返回值，否则只返回键名
            item = item.toUpperCase();
                _lang += L[item] === undefined ? item : L[item]
            }

        });

        return _lang;
    }
}//end lang

/**
 * console.log
 *
 * @member window
 *
 * @return {void} 无返回值
 */
function log() {
    var len = arguments.length;

    if (typeof(console) != 'undefined') {
        var i = 0;

        for (i = 0; i < len; i++) {
            console.log(arguments[i]);
        }
    }
}

/**
 * 刷新验证码
 *
 * @member window
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-07-14 12:33:36
 * @lastmodify      2013-01-12 16:19:42 by mrmsl
 *
 * @param {String} id     验证码图片id
 * @param {String} module 验证码所属模块
 *
 * @return {void} 无返回值
 */
function refreshCode(id, module) {
    Ext.get(id).dom.src = getActionUrl('verifycode?module=' + module + '&_c=' + Math.random());
}

/**
 * 输入框获、失焦点处理
 *
 * @member window
 *
 * @param {Object} obj  html元素
 * @param {String} [def=元素初始值] 默认内容
 *
 * @return {Object} html元素
 */
function setFocus(obj, def) {
    def = def || obj.defaultValue;
    obj.value.trim() == def ? obj.value = '' : '';

    obj.onblur = function() {
        obj.value.trim() == '' ? obj.value = def : '';
    };

    return obj;
}

/**
 * 设置或取消遮罩
 *
 * @member window
 *
 * @param {Mixed} [msg=PROCESSING语言项] 遮罩信息，false取消遮罩
 *
 * @return {void} 无返回值
 */
function setLoading(msg) {
    msg === false ? Alert(false, false, true, false) : Alert(msg || lang('PROCESSING'), 'loading', false, false);
    //bodyMask(msg);
}

/**
 * 使用另一个字符串填充字符串为指定长度
 *
 * @member window
 *
 * @param {String} string 待填充字符串
 * @param {Number} [lendgh=10] 总长度，默认10
 * @param {String} [pad=' '] 填充字符
 * @param {String} [padType=undefined] 填充类型，right为右填充
 *
 * @return {String} 填充后的字符串
 */
function str_pad(str, length, pad, padType) {
    str = String(str);
    length = length ? length : 10;
    pad = pad == undefined ? ' ' : pad;

    while (str.length < length) {
        str = padType == 'right' ? str + pad : pad + str;
    }

    return str;

}

/**
 * 去掉html标签
 *
 * @member window
 *
 * @param {String} str 字符串
 * @param {Boolean} [img=false] true保留img标签，false不保留
 *
 * @return {String} 去掉html标签后的字符串
 */
function strip_tags(str, img) {
    str = String(str);
    var pattern = img ? /<(?!img)[^>]*>/ig : /<[^>]*>/gi;

    return str.replace(pattern, '');
}

/**
 * 数字精确度
 *
 * @member window
 *
 * @param {Number} value 数字
 * @param {Number} [precision=2] 小数点位数
 *
 * @return {Number} 精确小数点后的数值
 */
function toFixed(value, precision) {
    precision = precision === undefined ? 2 : precision;

    if ((0.9).toFixed() !== '1') {//IE下等于0
        var pow = Math.pow(10, precision);
        return (Math.round(value * pow) / pow).toFixed(precision);
    }

    return value.toFixed(precision);
}

/**
 * 转化为浮点数
 *
 * @member window
 *
 * @param {Mixed} str 需要转换的字符串
 * @param {Number} [def=0.00] 转换失败默认值
 *
 * @return {Number} 转化后的浮点数
 */
function toFloat(str, def) {
    var str = parseFloat(str);

    return isNaN(str) ? parseFloat(def == undefined ? 0.00 : def) : str;
}

/**
 * 反转义html
 *
 * @member window
 *
 * @param {String} str 待转义字符串
 *
 * @return {String} 转义后的字符串
 */
function unhtmlspecialchars(str) {
    return str.replace(/\&lt;/g, '<').replace(/\&gt;/g, '>').replace(/\&quot;/g, '"').replace(/\&#39;/g, "'");
}

/**
 * 警告
 *
 * @member window
 *
 * @param {String} msg 内容
 * @param {String} [title=SYSTEM_INFOMATION语言项] 标题
 *
 * @return {void} 无返回值
 */
function warn(msg, title) {
    Ext.Msg.show({
        msg: msg,
        title: getMsgTitle(title),
        icon: Ext.Msg.WARNING,
        buttons: Ext.Msg.OK,
        modal: true
    });
}