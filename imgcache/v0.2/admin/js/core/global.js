/**
 * 全局库
 *
 * @file            global.js
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-09-28 17:40:19
 * @lastmodify      $Date$ $Author$
 */

var A,//操作方法
    C,//控制器
    ID,//C + A
    TREE_DATA,//当前树节点数据
    Q2O,//location.hash对象
    UEDITOR_HOME_URL = System.sys_base_site_url + 'static/js/ueditor/v1.2.6/',//百度ueditor路径
    TEXT = {//文字
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
        strong: function(text, extra) {//粗
            return '<span style="font-weight: bold;{0}">{1}</span>'.format(extra ? extra : '', text);
        }
    },
    IMAGES = {//图像
        yes: 'http://localhost/ligerui/yablog/images/yes.gif',//勾
        no: 'http://localhost/ligerui/yablog/images/no.gif',//叉
        loading: 'http://localhost/ligerui/yablog/images/loading.gif'//加载中
    };

IMAGES[0] = IMAGES.no;
IMAGES[1] = IMAGES.yes;

seajs.config({
    base: System.sys_base_admin_imgcache + 'js/controllers/',
    map: [
        [/\.js$/, '.js?' + Math.random()]
    ],
    paths: {
        core: System.sys_base_admin_imgcache + 'js/core'
    },
    alias: {
        ueditor: 'common/ueditor/ueditor',
        ueconfig: 'common/ueditor/ueconfig'
    }
});

$(function() {
    if ('undefined' != typeof LOGIN_PAGE) {//登录
        seajs.use('login', function(login) {
            login.win();
        });
    }
    else {
        bootstrap();
        seajs.use(['core/tabs', 'core/tree', 'core/router'], function(a, b, c) {
        });
    }
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
        isLeftCollapse: true,
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
 * 获取控制器controller或action名称
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-09-23 10:50:11
 *
 * @parma {string} [ca=controller] c或a
 *
 * return {string} 控制器controller或action名称
 */
function getCA(ca) {
    return _GET('a' == ca ? 'action' : 'controller');
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
 * 设置或获取语言，支持批量
 *
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-10-04 14:21:53
 *
 * @param {string} name 名
 * @param {mixed} [value] 值
 *
 * @return {mixed} 如果不传参数name，将返回整个语言包；否则返回指定语言
 */
function lang(name, value) {

    if (!name) {//返回整个语言包
        return L;
    }
    else if (undefined !== value) {//单个
        L[name.toUpperCase()] = value;
        return L;
    }
    else {//取值
        var _lang = '';

        $.each(name.split(','), function(index, item) {

            if (0 == item.indexOf('%')) {//支持原形返回
                _lang += item.substr(1);
            }
            else {//如果设置值，返回值，否则只返回键名
                item = item.toUpperCase();
                _lang += undefined === L[item] ? item : L[item];
            }

        });

        return _lang;
    }
}//end lang

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
function o2q(object) {
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
function o2s(object) {
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
function q2o(querystring) {
    var object = {};

    $.each(querystring.split('&'), function(index, item) {
        var arr = item.split('=');

        if (2 == arr.length) {
            object[arr[0]] = decodeURIComponent(arr[1]);
        }
    });

    return object;
}