<?php
/**
 * 快捷方式控制器类
 *
 * @file            Shortcut.class.php
 * @package         Yab\Module\Admin\Controller
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-07-03 22:03:39
 * @lastmodify      $Date$ $Author$
 */

class ShortcutController extends CommonController {
    /**
     * @var array $_priv_map 权限映射，如'delete' => 'add'删除权限映射至添加权限
     */
    protected $_priv_map           = array(
        'delete'   => 'add',//删除
        'info'     => 'add',//具体信息
        'enable'   => 'add',//显示隐藏
    );

    /**
     * 添加或编辑
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-07-03 22:09:24
     *
     * @return void 无返回值
     */
    public function addAction() {
        $check     = $this->_model->checkCreate();//自动创建数据

        true !== $check && $this->_ajaxReturn(false, $check);//未通过验证

        $pk_field  = $this->_pk_field;//主键
        $pk_value  = $this->_model->$pk_field;//id

        $data      = $this->_model->getProperty('_data');//数据，$model->data 在save()或add()后被重置为array()
        $diff_key  = 'menu_name,additional_param,sort_order,memo';//比较差异字段
        $msg       = L($pk_value ? 'EDIT' : 'ADD');//添加或编辑
        $log_msg   = $msg . L('SHORTCUT,FAILURE');//错误日志
        $error_msg = $msg . L('FAILURE');//错误提示信息
        $menu_arr   = $this->_getCache(0, 'Menu');

        $data['menu_name'] = $menu_arr[$this->_model->menu_id]['menu_name'];//所属菜单

        if ($pk_value) {//编辑

            $short_info = C('T_SHORT_INFO');
            $short_info['menu_name'] = $menu_arr[$short_info['menu_id']]['menu_name'];//所属菜单

            if (false === $this->_model->save()) {//更新出错
                $this->_sqlErrorExit($msg . L('SHORTCUT') . "{$short_info['menu_name']}({$pk_value})" . L('FAILURE'), $error_msg);
            }

            $diff = $this->_dataDiff($short_info, $data, $diff_key);//差异
            $this->_model->addLog($msg . L('SHORTCUT')  . "{$short_info['menu_name']}({$pk_value})." . $diff. L('SUCCESS'), LOG_TYPE_ADMIN_OPERATE);
            $this->_ajaxReturn(true, $msg . L('SUCCESS'));

        }
        else {
            $diff = $this->_dataDiff($data, false, $diff_key);//数据

            if (false === $this->_model->add()) {//插入出错
                $this->_sqlErrorExit($msg . L('SHORTCUT') . $diff . L('FAILURE'), $error_msg);
            }

            $this->_model->addLog($msg . L('SHORTCUT') . $diff . L('SUCCESS'), LOG_TYPE_ADMIN_OPERATE);
            $this->_ajaxReturn(true, $msg . L('SUCCESS'));
        }

    }//end addAction

    /**
     * 获取具体信息
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-07-08 22:36:29
     *
     * @return void 无返回值
     */
    function infoAction() {
        $info = $this->_model->alias('s')
        ->join(' JOIN ' . TB_MENU . ' AS m ON s.menu_id=m.menu_id')
        ->field('s.*,m.menu_name')
        ->where(array('short_id' => Filter::int($this->_pk_field, 'get'), 'admin_id' => $this->_admin_info['admin_id']))
        ->find();
        $this->_ajaxReturn(true, '', $info);
    }

    /**
     * 列表管理
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-07-03 22:38:00
     *
     * @return void 无返回值
     */
    public function listAction() {
        $data = $this->_model->alias('s')->join(' JOIN ' . TB_MENU . ' AS m ON s.menu_id=m.menu_id')
        ->field('s.*,m.controller,m.action,m.menu_name')
        ->order('s.sort_order ASC,s.short_id ASC')
        ->where('admin_id=' . $this->_admin_info['admin_id'])
        ->select();

        foreach($data as $k => $v) {
            $data[$k]['href'] = sprintf('#controller=%s&action=%s%s', $v['controller'], $v['action'], $v['additional_param'] ? '&' . $v['additional_param'] : '');
            unset($data[$k]['controller'], $data[$k]['action']);
        }

        $this->_ajaxReturn(true, null, $data);
    }//end listAction
}