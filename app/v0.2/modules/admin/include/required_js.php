<?php
/**
 * 必须加载js文件，首页及压缩js调用
 *
 * @file            require_js.php
 * @package         Yab\Admin
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-12-30 10:16:18
 * @lastmodify      $Date$ $Author$
 */

return array(
    //第三方核心
    'jquery-1.10.2.js'      => COMMON_IMGCACHE . 'js/jquery/',          //jquery
    'underscore.js'         => COMMON_IMGCACHE . 'js/underscore/',      //underscore.js
    'backbone.js'           => COMMON_IMGCACHE . 'js/backbone/',        //backbone.js
    'ligerui.js'            => COMMON_IMGCACHE . 'js/ligerui/js/',      //ligerui框架
    'base/base.js'          => COMMON_IMGCACHE . 'js/',                 //Base类
    'sea.js'                => COMMON_IMGCACHE . 'js/seajs/',           //seajs

    //通用js
    'common.js'             => COMMON_IMGCACHE . 'js/',                  //通用js

    //yablog
    'global.js'             => ADMIN_IMGCACHE . 'js/',                  //整站全局js
    'base.js'               => ADMIN_IMGCACHE . 'js/',                  //基础库
    'router.js'             => ADMIN_IMGCACHE . 'js/',                  //路由
    'tree.js'               => ADMIN_IMGCACHE . 'js/',                  //导航菜单
    'tabs.js'               => ADMIN_IMGCACHE . 'js/',                  //导航菜单
);