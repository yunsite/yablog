/**
 * 通用js
 *
 * @file            global.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-04-30 21:34:20
 * @lastmodify      $Date$ $Author$
 */

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