/**
 * 字段
 *
 * @file            fields.js
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-08-06 12:01:29
 * @lastmodify      $Date$ $Author$
 */

define('fields', [], {
    matchMode: {
        width: 100,
        panelHeight: 100,
        data: [{
            text: '完全匹配',
            value: 'eq',
            selected: true
        }, {
            text: '左匹配',
            value: 'leq'
        }, {
            text: '右匹配',
            value: 'req'
        }, {
            text: '模糊匹配',
            value: 'like'
        }]
    },
    datetime: {
        formatter: function(d) {
            return date('Y-m-d ', d) + date('H:i:s', new Date());
        }
    }
});