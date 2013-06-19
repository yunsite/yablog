<?php
/**
 * 必须加载js文件，首页及压缩js调用
 *
 * @file            require_js.php
 * @package         Yab\Admin
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-12-30 10:16:18
 * @lastmodify      $Date$ $Author$
 */

return array(
    'util/common.js',
    'util/override.js',
    'util/Yab.History.js',
    'util/Yab.Application.js',
    'util/Yab.Field.js',//表单域 by mrmsl on 2012-12-11 15:56:15
    'store/Yab.store.Admin.js',
    'store/Yab.store.Role.js',
    'store/Yab.store.Tree.js',
    'store/Yab.store.Area.js',//国家地区 by mrmsl 22:05 2012-7-18
    'store/Yab.store.Category.js',//博客分类 by mrmsl on 2013-03-21 16:26:55
    'ux/Yab.ux.RoleCombo.js',
    'ux/Yab.ux.TreePicker.js',//下拉树 by mrmsl on 2012-08-02 18:25:52
    'ux/Yab.ux.Form.js',//表单 by mrmsl on 2012-12-11 15:55:54
    'ux/Yab.ux.Grid.js',//普通列表扩展 by mrmsl on 2012-12-18 11:32:51
    'ux/Yab.ux.TreeGrid.js',//树列表扩展 by mrmsl on 2012-12-18 11:32:51
    'view/Yab.view.Viewport.js',
    'view/Yab.view.Tabs.js',
    'view/Yab.view.Index.js',
    'view/Yab.view.Center.js',
    'view/Yab.view.Header.js',
    'view/Yab.view.Tree.js',
    'controller/Yab.controller.Base.js',//底层控制器 by mrmsl on 2012-07-28 09:04:24
    'controller/Yab.controller.Tree.js',
    'controller/Yab.controller.Tabs.js',
    'controller/Yab.controller.Index.js',
    'controller/Yab.controller.Login.js',
);