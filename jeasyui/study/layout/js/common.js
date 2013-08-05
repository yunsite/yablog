var A, C, MENU_ID, TREE_DATA, Q2O;

seajs.config({
    base: 'http://localhost/jeasyui/study/layout/js/',
    map: [
        [/\.js$/, '.js?' + Math.random()]
    ]
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