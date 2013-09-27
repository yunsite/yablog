<?php
/**
 * 国家地区控制器类
 *
 * @file            AreaController.class.php
 * @package         Yab\Module\Admin\Controller
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-12-29 14:16:45
 * @lastmodify      $Date$ $Author$
 */

class AreaController extends CommonController {
    /**
     * @var bool $_get_children_ids true取所有子表单， CommonController->delete()会用到。默认true
     */
    protected $_get_children_ids   = true;
    /**
     * @var string $_name_column 名称字段 CommonController->delete()会用到。默认area_name
     */
    protected $_name_column        = 'area_name';
    /**
     * @var array $_priv_map 权限映射，如'delete' => 'add'删除权限映射至添加权限
     */
    protected $_priv_map           = array(
        'delete'   => 'add',//删除
        'info'     => 'add',//具体信息
        'show'     => 'add',//显示隐藏
    );

	   /**
     * {@inheritDoc}
     */
    protected function _infoCallback(&$info) {
        $this->_treeInfoCallback($info);
    }

    /**
     * 添加或编辑
     *
     * @return void 无返回值
     */
    public function addAction() {
        $this->_commonAddTreeData('area_name,area_code,parent_name,is_show,sort_order');
    }

    /**
     * 生成缓存
     *
     * @return object 本类实例
     */
    public function createAction() {
        $data      = $this->_model
        ->order('parent_id ASC,is_show DESC,sort_order ASC, area_id ASC')
        ->key_column($this->_pk_field)->select();

        if ($data === false) {
            $this->_model->addLog();
            $this->_ajaxReturn(false, L('CREATE_AREA_CACHE,FAILURE'), 'EXIT');
        }

        $area_data  = array();

        foreach ($data as $area_id => &$item) {

            if ($item['level'] < 3) {
                $item['leaf'] = $this->_model->checkIsLeaf($area_id, '`level` IN(2,3)');
                $item['expanded'] = $item['level'] == 1;
                $area_data[$area_id] = $item;
            }
        }

        $area_data = Tree::array2tree($area_data, $this->_pk_field);//树形式

        return $this->cache(null, null, $data)->cache(null, $this->_getControllerName() . '_tree', $area_data);
    }

    /**
     * 列表管理
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-07-19 12:40:43
     * @lastmodify      2013-01-28 11:22:55 by mrmsl
     *
     * @return void 无返回值
     */
    public function listAction() {
        $column  = Filter::string('column', 'get');//搜索字段
        $keyword = Filter::string('keyword', 'get');//搜索关键字
        $area_id = Filter::int('node', 'get');//地区id

        //搜索 by mrmsl on 2012-07-24 18:02:02
        if (!$area_id && $column && $keyword && in_array($column, array('area_name', 'area_code'))) {
            $this->_queryTree($column, $keyword);
        }
        elseif ($area_id) {
            $this->_ajaxReturn(true, '', $this->_getTreeData($area_id, false));
        }

        $data = $this->cache(0, CONTROLLER_NAME . '_tree');
        $this->_ajaxReturn(true, '', $data, count($this->cache()));
    }

    /**
     * 所属地区
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2012-07-19 11:39:11
     * @lastmodify      2013-01-28 11:23:08 by mrmsl
     *
     * @return void 无返回值
     */
    public function publicAreaAction() {
        $area_id   = Filter::int('node', 'get');
        $data      = $this->_getTreeData($area_id, 'nochecked');

        if (!$area_id) {//非加载指定节点
            //增加顶级菜单
            $this->_unshift && array_unshift($data, array('area_id' => 0, 'area_name' => L('TOP_LEVEL_AREA'), 'leaf' => true));
            $parent_id = Filter::int('parent_id', 'get');

            //添加指定地区子级地区，获取指定地区信息by mashanlng on 2012-08-21 13:51:25
            if ($parent_id && ($parent_info = $this->cache($parent_id))) {
                $parent_info = array(
                	'area_id'   => $parent_id,
                	'area_name' => $parent_info['area_name'],
                	'node'      => $parent_info['node'])
                ;
                $this->_ajaxReturn(array('data' => $data, 'parent_data' => $parent_info));
            }
        }

        $this->_ajaxReturn(true, '', $data);
    }

    /**
     * 显示/隐藏
     *
     * @return void 无返回值
     */
    public function showAction() {
        $this->_setOneOrZero();
    }
}