<?php
/**
 * 邮件模板控制器
 *
 * @file            MailController.class.php
 * @package         Yab\Module\Admin\Controller
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-06-06 15:56:11
 * @lastmodify      $Date$ $Author$
 */

class MailController extends CommonController {
    /**
     * @var bool $_after_exec_cache true删除后调用CommonController->cache()生成缓存， CommonController->delete()会用到。默认true
     */
    protected $_after_exec_cache   = true;

    /**
     * @var string $_name_column 名称字段 CommonController->delete()会用到。默认template_name
     */
    protected $_name_column = 'template_name';

    /**
     * @var array $_priv_map 权限映射，如'delete' => 'add'删除权限映射至添加权限
     */
    protected $_priv_map = array(//权限映射
    	   'delete'   => 'add',//删除
        'info'     => 'add',//具体信息
    );

    /**
     * 删除后置操作
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-07 09:03:17
     *
     * @param array $pk_id 主键值
     *
     * @return void 无返回值
     */
    protected function _afterDelete($pk_id) {
        $o      = $this->getViewTemplate();
        $data   = $this->cache();
        $path   = THEME_PATH . 'mail/';
        $suffix = C('TEMPLATE_SUFFIX');

        foreach($data as $id => $item) {

            if (in_array($id, $pk_id)) {
                $o->clearCache(CONTROLLER_NAME, $item['template_name']);//清除缓存
                new_unlink($path . $item['template_name'] . $suffix);//模板文件

            }
        }
    }

    /**
     * 获取写缓存数据
     * @date            2012-09-05 14:22:04
     * @lastmodify      2013-01-21 15:44:59 by mrmsl
     *
     * @return mixed 查询成功，返回数组，否则false
     */
    protected function _setCacheData() {
        $data   = $this->_model->key_column($this->_pk_field)->order('sort_order ASC,' . $this->_pk_field . ' ASC')->select();
        $path   = FRONT_THEME_PATH . 'mail/';
        $suffix = C('TEMPLATE_SUFFIX');

        new_mkdir($path);

        foreach ($data as $item) {
            $template_name  = $path . $item['template_name'] . $suffix;
            file_put_contents($template_name, $item['content']);//邮件模板
        }

        return $data;
    }

    /**
     * 添加或保存
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-06 16:03:15
     *
     * @return void 无返回值
     */
    public function addAction() {
        $check     = $this->_model->checkCreate();//自动创建数据

        $check !== true && $this->_ajaxReturn(false, $check);//未通过验证

        $pk_field  = $this->_pk_field;//主键
        $pk_value  = $this->_model->$pk_field;//主键值
        $data      = $this->_model->getProperty('_data');//数据，$model->data 在save()或add()后被重置为array()
        $diff_key  = 'template_name,subject,sort_order,memo,content';//比较差异字段
        $msg       = L($pk_value ? 'EDIT' : 'ADD');//添加或编辑
        $log_msg   = $msg . L('CONTROLLER_NAME,FAILURE');//错误日志
        $error_msg = $msg . L('FAILURE');//错误提示信息

        if ($pk_value) {//编辑

            if (!$info = $this->cache($pk_value)) {//不存在
                $log = get_method_line(__METHOD__ , __LINE__, LOG_INVALID_PARAM) . $log_msg . ': ' . L("INVALID_PARAM,%:,CONTROLLER_NAME,%{$pk_field}({$pk_value}),NOT_EXIST");
                trigger_error($log, E_USER_ERROR);
                $this->_ajaxReturn(false, $error_msg);
            }

            if (false === $this->_model->save()) {//更新出错
                $this->_sqlErrorExit($msg . L('CONTROLLER_NAME') . "{$info['template_name']}({$pk_value})" . L('FAILURE'), $error_msg);
            }

            $diff = $this->_dataDiff($info, $data, $diff_key);//差异
            $this->_model->addLog($msg . L('CONTROLLER_NAME')  . "{$info['template_name']}({$pk_value})." . $diff. L('SUCCESS'));
            $this->cache(null, null, null)->_ajaxReturn(true, $msg . L('SUCCESS'));

        }
        else {
            $data = $this->_dataDiff($data, false, $diff_key);//数据

            if ($this->_model->add() === false) {//插入出错
                $this->_sqlErrorExit($msg . L('CONTROLLER_NAME') . $data . L('FAILURE'), $error_msg);
            }

            $this->_model->addLog($msg . L('CONTROLLER_NAME') . $data . L('SUCCESS'));
            $this->cache(null, null, null)->_ajaxReturn(true, $msg . L('SUCCESS'));
        }
    }//end addAction

    /**
     * 列表
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-06 16:22:42
     *
     * @return void 无返回值
     */
    public function listAction() {
        $data = $this->cache();
        $data = $data ? array_values($data) : array();

        if (isset($_GET['combo'])) {//邮件历史,所属邮件模板
            $pk_field   = $this->_pk_field;
            $_data      = array(array($pk_field => '0', 'template_name' => L('BELONG_TO,MAIL_TEMPLATE')));

            foreach($data as $v) {
                $_data[] = array($pk_field => $v[$pk_field], 'template_name' => $v['template_name']);
            }

            $this->_ajaxReturn(true, '', $_data);
        }

        $this->_ajaxReturn(true, '', $data, count($data));
    }
}