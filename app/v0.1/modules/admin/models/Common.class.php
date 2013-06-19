<?php
/**
 * 底层通用模型
 *
 * @file            Common.class.php
 * @package         Yab\Module\Admin\Model
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-12-26 12:43:14
 * @lastmodify      $Date$ $Author$
 */

class CommonModel extends BaseModel {
    /**
     * 验证唯一字段
     *
     * @author          mrmsl <msl-138@163.com>
     * @lastmodify      2013-05-13 14:59:58
     *
     * @param string $name          验证字符串
     * @param array  $data          _POST数据
     * @param string $field_name    字段名
     * @param string $lang_name     语言项。默认null，取$field_name
     *
     * @return mixed true验证成功。否则，如果未输入，返回提示信息，否则返回false
     */
    protected function _checkUnique($name, $data, $field_name, $lang_name = null) {
        $pk_field = $this->getPk();

        if ('' === $name || !isset($data[$pk_field])) {//如果未输入，提示输入
            return false;
        }

        $name       = strtolower($name);
        $pk_value   = isset($data[$pk_field]) ? $data[$pk_field] : 0;
        $lang_name  = $lang_name ? $lang_name : $field_name;
        $caches     = $this->_getCache();

        if (!$caches) {
            return true;
        }

        foreach ($caches as $id => $item) {

            if ($name == $item[$field_name] && $pk_value != $id) {
                return L($lang_name . ',EXIST');
            }
        }

        return true;
    }
}