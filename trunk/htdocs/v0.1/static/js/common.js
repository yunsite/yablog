bindTableMouse();

String.prototype.format = function() {
    
    if (arguments.length == 1) {//json形，如'a{a}b{b}'.format({a: 'a', b: 'b'}) => aabb
        var args = arguments[0], pattern = /\{(\w+)\}/g;
    }
    else {//数字形，如'a{0}b{1}'.format('a', 'b') => aabb
        var args = arguments, pattern = /\{(\d+)\}/g;
    }
    
    return this.replace(pattern,               
        function(m, i){
            return args[i];
        });
}

String.prototype.ltrim = function() {
    return this.replace(/^\s+/, '');
}
String.prototype.rtrim = function() {
    return this.replace(/\s+$/, '');
}
String.prototype.trim = function() {
    return this.ltrim().rtrim();
}

/**
 * console.log
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
 * 去掉html标签
 *
 * @param {string} str 字符串
 * @param {bool}   img 是否保留img标签，默认false，不保留
 * 
 * @return {string} 去掉html标签后的字符串
 */
function strip_tags(str, img){
    str = String(str);
    var pattern = img ? /<(?!img)[^>]*>/ig : /<[^>]*>/gi;
    
    return str.replace(pattern, '');
}

/**
 * 转化为整数
 *
 * @param {mixed} str   需要转换的字符串
 * @param {int}   def   默认值
 * @param {int}   radix 进制，默认十进制
 * 
 * @return {int} 转化后的整数
 */
function intval(str, def, radix){
    radix = radix || 10;
    var str = parseInt(str, radix);
    
    return isNaN(str) ? parseInt(def == undefined ? 0 : def, radix) : str;
}

/**
 * 使用另一个字符串填充字符串为指定长度
 *
 * @param string string     待填充字符串
 * @param int    lendgh     总长度，默认10
 * @param string pad        填充字符，默认' '
 * @param string padType    填充类型，默认左填充
 * 
 * @return {string} 填充后的字符串
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
 * html转码
 *
 * @param {string} str 待转换字符串
 * 
 * @param {string} 转换后的字符串
 */
function htmlspecialchars(str) {
    return str.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\"/g, '&quot;').replace(/\'/g, '&#39;');
}

/**
 * 反转html
 *
 * @param {string} str 待转换字符串
 * 
 * @param {string} 转换后的字符串
 */

function unhtmlspecialchars(str) {
    return str.replace(/\&lt;/g, '<').replace(/\&gt;/g, '>').replace(/\&quot;/g, '"').replace(/\&#39;/g, "'");
}
/**
 * 输入框获、失焦点处理
 *
 * @param {object} obj  html元素
 * @param {string} def  默认内容，默认为元素初始值
 * 
 * @return {object} html元素
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
 * 格式化时间
 *
 * @param {string} format      格式
 * @param {mixed}  constructor 日期初始化参数
 * 
 * @return {string} 格式化后的时间
 */
function date(format, constructor) {
    
    if (typeof(constructor) == 'object') {
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
}

/**
 * 获取参数
 *
 * @param {string} name 参数
 * @param {string} str  待获取字符串
 * 
 * @return {string} 参数值
 */
function _GET(name, str){
    var pattern = new RegExp().compile('[\?&]' + name + '=([^&]+)', 'g');
    str = str || location.search;
    var arr, match = '';

    while ((arr = pattern.exec(str)) !== null) {
        match = arr[1];
    }
    
    return match;
}

function bindTableMouse() {
    var tb = $('.tb-mouse');
    
    if (tb.length == 0) {
        return false;
    }

    tb.find('tbody tr:gt(0)').hover(function() {
        $(this).addClass('bg-hover');
    }, function() {
        $(this).removeClass('bg-hover');
    });
}