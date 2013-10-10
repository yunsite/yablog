/**
 * 通用库
 *
 * @file            common.js
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-09-21 11:37:15
 * @lastmodify      $Date$ $Author$
 */

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
String.prototype.capitalize = function() {//首字母大写
    return this.charAt(0).toUpperCase() + this.substr(1);
};

/**
 * 获取参数，类似php $_GET。不支持获取数组
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-08-02 21:18:30
 *
 * @param {string} name 参数名称
 * @param {string} [str=location.href]  匹配字符串
 *
 * @return {string} 参数值或空字符串
 */
function _GET(name, str) {
    var pattern = new RegExp('(?:^#|[\?&])' + name + '=([^&]+)', 'g');
    str = str || location.hash;
    var arr, match = '';

    while ((arr = pattern.exec(str)) !== null) {
        match = arr[1];
    }

    return match;
}/**
 * 友好提示
 *
 * @param {string} msg      提示内容
 * @param {bool}   [success=true]  true成功提示
 * @param {bool}   [cancel=false]  true清除提示
 * @param {int}    [timeout=2000]  提示停留时间,单位毫秒
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-09-11 22:29:57
 *
 * @param {void} 无返回值
 */
function Alert(msg, success, cancel, timeout) {
    'undefined' != typeof(AlertTimeout) && clearTimeout(AlertTimeout);

    var div = $('#div-alert');

    if (!cancel) {

        if ('loading' === success) {
            timeout = false;

            var background  = '#666',//'#ff8',
                color       = '#fff';//'#333';
        }
        else {
            var background  = false === success ? '#d90000' : '#16960e',
                color       = '#fff';
        }

        if (0 == div.length) {
            div = $('<div/>').html(msg).attr('id', 'div-alert').css({
                'background-color': background,
                color: color,
                left: '50%',
                'z-index': 10000,
                position: 'absolute',
                padding: '4px 8px',
                'font-size': '13px'
            }).appendTo($('body')).hide();
        }
        else {
            div.html(msg).width('auto').css({
                'background-color': background,
                color: color
            });
        }

        var width = div.width();
        width = width < 100 ? 100 : (width > 600 ? 600 : width);
        div.show().css({
            top: $(window).scrollTop(),
            width: width,
            'margin-left': -width / 2,
            'text-align': width >= 600 ? 'center' : 'left'
        });

        hideAlert(timeout);
    }

    else {
        div.hide();
    }
}//end Alert

/**
 * 格式化时间，类似php date函数
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-08-04 22:11:02
 *
 * @param {string} [format=Y-m-d H:i:s] 格式
 * @param {mixed} [constructor] new Date()初始化参数
 *
 * @return {string} 格式化后的时间
 */
function date(format, constructor) {

    if (typeof(constructor) == 'object') {//已经是日期类型
        var datetime = constructor;
    }
    else {
        var datetime = constructor ? new Date(constructor) : new Date();
    }

    format = format || 'Y-m-d H:i:s';

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
}//end date

/**
 * 格式化字节大小
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-10-05 16:32:32
 *
 * @param {int} filesize  文件大小，单位：字节
 * @param {int} [precision=2] 小数点数
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
 * 设置或获取全局变量，如果只传一个参数，则取该参数值;否则设置变量，第一个参数为变量名，第二个参数为变量值
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-07-21 22:05:24
 *
 * @return {Mixed} 如果只传一个参数，则返回参数值;否则返回true
 */
function global() {

    if (1 == arguments.length) {//取值
        return window[arguments[0]];
    }

    window[arguments[0]] = arguments[1];

    return true;
}

/**
 * 隐藏友好提示
 *
 * @param {int} [timeout=2000]  提示停留时间,单位毫秒
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-09-11 22:28:12
 *
 * @param {void} 无返回值
 */
function hideAlert(timeout) {

    if (false !== timeout) {
        AlertTimeout = setTimeout(function() {
            Alert(false, false, true);
        }, timeout || 2000);
    }
}

/**
 * 转义html
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-10-05 16:39:05
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
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-08-04 09:12:13
 *
 * @param {mixed} str   需要转换的字符串
 * @param {int} [def=0] 转换失败默认值
 * @param {int} [radix=10] 进制
 *
 * @return {int} 转化后的整数
 */
function intval(str, def, radix) {
    radix = radix || 10;
    var str = parseInt(str, radix);

    return isNaN(str) ? parseInt(def == undefined ? 0 : def, radix) : str;
}

/**
 * ctrl + enter事件
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-10-08 14:14:14
 *
 * @param {object} target 目标元素
 * @param {function} handler 处理函数
 * @param {object} [thisObj=window] this作用域
 * @param {bool} [ctrlKey=undefined] true需要同时按下ctrl键
 *
 * @return {void} 无返回值
 */
function ctrlEnter(target, handler, thisObj, ctrlKey) {
    $(target).keypress(function(e) {

        if (13 == e.which && (ctrlKey && e.ctrlKey || !ctrlKey)) {
            handler.call(thisObj || window, e);
        }
    });
}

/**
 * console.log
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-05-07 21:38:55
 *
 * @return {void} 无返回值
 */
function log() {

    if ('undefined' != typeof(console)) {

        for (var i = 0, len = arguments.length; i < len; i++) {
            console.log(arguments[i]);
        }
    }
}

/**
 * 使用另一个字符串填充字符串为指定长度。类似php str_pad
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-08-04 22:10:09
 *
 * @param {string} string 待填充字符串
 * @param {int} [lendgh=10] 总长度
 * @param {string} [pad=' '] 填充字符
 * @param {string} [padType=undefined] 填充类型，right为右填充
 *
 * @return {string} 填充后的字符串
 */
function str_pad(str, length, pad, padType) {
    str = String(str);
    length = length ? length : 10;
    pad = pad == undefined ? ' ' : pad;

    while (str.length < length) {
        str = 'right' == padType ? str + pad : pad + str;
    }

    return str;
}

/**
 * 去掉html标签
 *
 * @author              mrmsl <msl-138@163.com>
 * @date                2013-08-04 22:04:50
 *
 * @param {string} str 字符串
 * @param {bool} [img=false] true保留img标签，false不保留
 *
 * @return {string} 去掉html标签后的字符串
 */
function strip_tags(str, img) {
    str = String(str);
    var pattern = img ? /<(?!img)[^>]*>/ig : /<[^>]*>/gi;

    return str.replace(pattern, '');
}

/**
 * 数字精确度
 *
 * @author              mrmsl <msl-138@163.com>
 * @date                2013-10-05 16:37:16
 *
 * @param {int} value 数字
 * @param {int} [precision=2] 小数点位数
 *
 * @return {int} 精确小数点后的数值
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
 * @author              mrmsl <msl-138@163.com>
 * @date                2013-10-05 16:37:35
 *
 * @param {mixed} str 需要转换的字符串
 * @param {float} [def=0.00] 转换失败默认值
 *
 * @return {float} 转化后的浮点数
 */
function toFloat(str, def) {
    var str = parseFloat(str);

    return isNaN(str) ? parseFloat(def == undefined ? 0.00 : def) : str;
}

/**
 * 反转义html
 *
 * @author              mrmsl <msl-138@163.com>
 * @date                2013-10-05 16:37:35
 *
 * @param {string} str 待转义字符串
 *
 * @return {string} 转义后的字符串
 */
function unhtmlspecialchars(str) {
    return str.replace(/\&lt;/g, '<').replace(/\&gt;/g, '>').replace(/\&quot;/g, '"').replace(/\&#39;/g, "'");
}