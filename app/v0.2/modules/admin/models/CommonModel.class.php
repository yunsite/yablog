<?php
/**
 * 底层通用模型
 *
 * @file            CommonModel.class.php
 * @package         Yab\Module\Admin\Model
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-12-26 12:43:14
 * @lastmodify      $Date$ $Author$
 */

class CommonModel extends BaseModel {
    /**
     * 根据主键验证缓存是否存在
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-06-21 11:52:03
     *
     * @param int       $id         主键id
     * @param string    $filename   缓存文件名,默认CONTROLLER_NAME
     *
     * @return bool true验证成功,否则false
     */
    protected function _checkCacheExists($id, $filename = CONTROLLER_NAME) {
        return $id && $this->_module->cache($id, $filename) ? true : false;
    }

    /**
     * 从缓存文件验证唯一字段
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-05-13 14:59:58
     *
     * @param string $name          验证字符串
     * @param array  $data          _POST数据
     * @param string $field_name    字段名
     * @param string $lang_name     语言项。默认null，取$field_name
     * @param bool   $parent_id     true联合parent_id字段一起验证
     *
     * @return mixed true验证成功。否则，如果未输入，返回提示信息，否则返回false
     */
    protected function _checkUnique($name, $data, $field_name, $lang_name = null, $parent_id = false) {
        $parent_id_column   = is_numeric($parent_id) ? 'parent_id' : $parent_id;
        $pk_field           = $this->getPk();

        if ('' === $name || !isset($data[$pk_field])) {//如果未输入，提示输入
            return false;
        }
        elseif ($parent_id && !isset($data[$parent_id_column])) {
            return false;
        }

        $name       = strtolower($name);
        $pk_value   = isset($data[$pk_field]) ? $data[$pk_field] : 0;
        $lang_name  = $lang_name ? $lang_name : $field_name;
        $caches     = $this->_module->cache();

        if (!$caches) {
            return true;
        }


        if ($parent_id) {
            $parent_id = $data[$parent_id_column];

            foreach ($caches as $id => $item) {

                if ($name == $item[$field_name] && $pk_value != $id && $item[$parent_id_column] == $parent_id) {
                    return L($lang_name . ',EXIST');
                }
            }
        }
        else {

            foreach ($caches as $id => $item) {

                if ($name == $item[$field_name] && $pk_value != $id) {
                    return L($lang_name . ',EXIST');
                }
            }
        }

        return true;
    }//end _checkUnique
}