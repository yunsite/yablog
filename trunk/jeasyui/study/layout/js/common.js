var A,
    C,
    ID,
    TREE_DATA,
    Q2O,
    UEDITOR_HOME_URL = 'http://localhost/ueditor/';

seajs.config({
    base: 'http://localhost/jeasyui/yablog/study/layout/js/',
    map: [
        [/\.js$/, '.js?' + Math.random()]
    ],
    alias: {
        ueditor: 'common/ueditor/ueditor',
        ueconfig: 'common/ueditor/ueconfig'
    }
});

seajs.use(['tabs', 'tree', 'router'], function(a, b, c) {
    //log(a, b, c);
});



/**
 * 获取location.hash
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-08-03 15:47:53
 *
 * return {string} location.hash
 */
function getHash() {
    var match = window.location.hash.match(/^#+(.*)$/);

    return match ? match[1] : '';
}

/**
 * 将object转化为html代码
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-08-08 16:32:15
 *
 * @param {array} object 待转化object
 *
 * @return {string} 转化后的html代码
 */
function object2html(object) {
    var arr     = [],
        object  = 'array' == $.type(object) ? object : [object];

    $.each(object, function(key, value) {

        if ('string' == $.type(value)) {
            arr.push(value);
        }
        else {
            var tag     = value.tag || 'div';

            delete value.tag;

            arr.push('<', tag);

            $.each(value, function(k, v) {
                 0 != k.indexOf('_') && arr.push(' ', k, '="', v, '"');
            });

            if (value._begin) {
                arr.push('>');
            }
            else if ('input' == tag) {
                arr.push(' />');
            }
            else {
                arr.push('>', undefined === value._text ? '' : value._text, '</', tag, '>');
            }
        }
    });

    return arr.join('');
}//end object2html

/**
 * 将object转化为url格式字符串
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-08-03 16:12:55
 *
 * @param {object} object 待转化object
 *
 * @return {string} 转化后的url格式字符串
 */
function object2querystring(object) {
    var querystring = [];

    $.each(object, function(key, value) {
        querystring.push(key + '=' + encodeURIComponent(value));
    });

    return querystring.join('&');
}

/**
 * 将object转化为字符串
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-07-27 08:50:25
 *
 * @param {object} object 待转化object
 *
 * @return {string} 转化后的字符串
 */
function object2string(object) {
    return JSON.stringify(object);
}

/**
 * 将url格式字符串转化为object
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-08-03 16:15:12
 *
 * @param {string} querystring 待转化字符串
 *
 * @return {object} 转化后的object
 */
function querystring2object(querystring) {
    var object = {};

    $.each(querystring.split('&'), function(index, item) {
        var arr = item.split('=');

        if (2 == arr.length) {
            object[arr[0]] = decodeURIComponent(arr[1]);
        }
    });

    return object;
}

/**
 * 设置控制器C,操作方法A
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-09-13 13:38:16
 *
 * @param {mixed} hash hash或_treeData[id].queryParams
 *
 * @return {void} 无返回值
 */
function setCA(hash) {
    hash    = hash || getHash();
    Q2O     = 'string' == typeof(hash) ? querystring2object(hash) : hash;
    C       = Q2O.controller;
    A       = Q2O.action;
    ID      = C + A;
}