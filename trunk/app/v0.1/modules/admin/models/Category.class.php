<?php
/**
 * 博客分类模型
 *
 * @file            Category.class.php
 * @package         Yab\Module\Admin\Model
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-03-29 09:46:19
 * @lastmodify      $Date$ $Author$
 */

class CategoryModel extends CommonModel {
    /**
     * @var array $_auto 自动填充
     */
    protected $_auto = array(
        'is_show'    => '_getCheckboxValue',//是否显示
        'sort_order' => '_unsigned#data',//排序
    );

    /**
     * @var array $_db_fields
     * 数据表字段信息
     * filter: 数据类型，array(数据类型(string,int,float...),Filter::方法参数1,参数2...)
     * validate: 自动验证，支持多个验证规则
     *
     * @see Model.class.php create()方法对数据过滤
     * @see CommonModel.class.php __construct()方法设置自动验证字段_validate
     */
    protected $_db_fields = array (
        'cate_id'          => array('filter' => 'int', 'validate' => 'unsigned#PRIMARY_KEY,INVALID'),//自增主键
        'parent_id'        => array('filter' => 'int', 'validate' => '_checkParent#PLEASE_SELECT,PARENT_CATEGORY#data'),//父级分类id

        //分类名称
        'cate_name'        => array('validate' => array('_checkCateName#PLEASE_ENTER,CONTROLLER_NAME_CATEGORY,NAME#data', '_checkLength#CONTROLLER_NAME_CATEGORY,NAME#value|0|30')),

        //英文url名称
        'en_name'          => array('validate' => array('_checkEnName#PLEASE_ENTER,CATEGORY_EN_NAME#data', '_checkLength#CATEGORY_EN_NAME#value|0|15')),

        //seo关键字
        'seo_keyword'      => array('validate' => array('notblank#SEO_KEYWORD', '_checkLength#SEO_KEYWORD#value|6|180')),
        //seo描述
        'seo_description'  => array('validate' => array('notblank#SEO_DESCRIPTION', '_checkLength#SEO_DESCRIPTION#value|6|300')),
        'is_show'          => array('filter' => 'int'),//是否显示
        'sort_order'       => array('filter' => 'int', 'validate' => 'unsigned#ORDER#-2'),//排序
    );
    /**
     * @var string $_pk_field 数据表主键字段名称。默认cate_id
     */
    protected $_pk_field        = 'cate_id';
    /**
     * @var string $_true_table_name 实际数据表名(包含表前缀)。默认TB_CATEGORY
     */
    protected $_true_table_name = TB_CATEGORY;//表

    /**
     * 新增数据后，将排序设为该记录自动增长id
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-03-21 14:06:27
     *
     * @param $data     插入数据
     * @param $options  查询表达式
     *
     * @return void 无返回值
     */
    protected function _afterInsert($data, $options) {
        $this->execute("UPDATE {$this->_true_table_name} SET link_url='" . BASE_SITE_URL . "category/{$data['en_name']}" . C('HTML_SUFFIX') . "'");
        $this->_afterInserted($data, $options);
    }

    /**
     * {@inheritDoc}
     */
    protected function _afterUpdate($data, $options) {
        $count = count($data);

        if ($count == 1) {

            if (isset($data['is_show'])) {//显示，隐藏
                $this->_module->createAction();
            }
        }
    }

    /**
     * 验证分类名是否已经存在
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-03-21 14:16:51
     *
     * @param string $cate_name 用户名
     * @param array  $data     _POST数据
     *
     * @return mixed 验证成功，返回true。否则，如果未输入，返回提示信息，否则返回false
     */
    protected function _checkCateName($cate_name, $data) {

        if ('' === $cate_name) {//如果未输入，提示输入
            return false;
        }

        $cate_name = strtolower($cate_name);
        $caches    = $this->_getCache();

        if (!$caches) {
            return true;
        }

        $cate_id   = isset($data[$v = $this->getPk()]) ? $data[$v] : 0;

        foreach ($caches as $id => $item) {

            if (strtolower($item['cate_name']) == $cate_name && $cate_id != $id) {
                return L('CONTROLLER_NAME_CATEGORY,NAME,EXIST');
            }
        }

        return true;
    }

    /**
     * 验证url英文名是否已经存在
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-03-21 14:14:47
     *
     * @param string $en_name url英文名
     * @param array  $data    _POST数据
     *
     * @return mixed true验证成功。否则，如果未输入，返回提示信息，否则返回false
     */
    protected function _checkEnName($en_name, $data) {

        if ('' === $en_name) {//如果未输入，提示输入
            return false;
        }

        $pk_value  = isset($data[$this->_pk_field]) ? $data[$this->_pk_field] : 0;
        $caches    = $this->_getCache();

        if (!$caches) {
            return true;
        }

        $en_name   = strtolower($en_name);

        foreach ($caches as $id => $item) {

            if ($en_name == strtolower($item['en_name']) && $pk_value != $id) {
                return L('CATEGORY_EN_NAME,EXIST') . ': <br /><span style="color: #666;font-weight: bold;">' . $this->_module->nav($id, 'cate_name') . '</span>';
            }
        }

        return true;
    }

    /**
     * 验证父级类
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-03-21 15:07:17
     *
     * @param int   $parent_id 父类id
     * @param array $data      _POST数据
     *
     * @return mixed true验证成功。否则，如果未输入，返回提示信息，否则返回false
     */
    protected function _checkParent($parent_id, $data) {

        if (!$parent_id) {//如果未输入，提示输入
            return true;
        }
        elseif ($parent_id < 0) {
            return L('INVALID,PARENT_CATEGORY');
        }

        $caches    = $this->_getCache();

        if (!isset($caches[$parent_id])) {//父类不存在
            return L('PARENT_CATEGORY,NOT_EXIST');
        }

        $pk_value  = isset($data[$this->_pk_field]) ? $data[$this->_pk_field] : 0;
        $info      = isset($caches[$pk_value]) ? $caches[$pk_value] : '';

        if ($info) {
            return $info[$this->_pk_field] == $parent_id ? L('SAME_AS_SELF') : true;
        }

        return true;
    }
}