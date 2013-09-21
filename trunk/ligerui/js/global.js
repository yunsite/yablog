/**
 * 全局库
 *
 * @file            global.js
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-09-21 11:38:56
 * @lastmodify      $Date$ $Author$
 */

var A,
    C,
    ID,
    TREE_DATA,
    Q2O,
    UEDITOR_HOME_URL = 'http://localhost/ueditor/';

seajs.config({
    base: 'http://localhost/ligerui/yablog/js/',
    map: [
        [/\.js$/, '.js?' + Math.random()]
    ],
    alias: {
        ueditor: 'common/ueditor/ueditor',
        ueconfig: 'common/ueditor/ueconfig'
    }
});

$(function() {
    bootstrap();

    seajs.use(['tabs', 'tree', 'router'], function(a, b, c) {
        //log(a, b, c);
    });
});

/**
 * 启动
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-09-21 12:50:15
 *
 * return {void} 无返回值
 */
function bootstrap() {
    $('#layout').ligerLayout({
        leftWidth: 200,
        height: '100%',
        onHeightChanged: function(options) {

            if (accordion && options.middleHeight > 0) {
                accordion.setHeight(options.middleHeight);
            }
        }
    });

    $('#left').ligerAccordion({
        height: $('.l-layout-center').height()
    });

     var accordion = $('#left').ligerGetAccordionManager();
}

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