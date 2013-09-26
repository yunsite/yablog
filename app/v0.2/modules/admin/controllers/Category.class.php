<?php
/**
 * 博客分类控制器类
 *
 * @file            Category.class.php
 * @package         Yab\Module\Admin\Controller
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-03-21 13:26:27
 * @lastmodify      $Date$ $Author$
 */

class CategoryController extends CommonController {
    /**
     * @var bool $_get_children_ids true取所有子表单， CommonController->delete()会用到。默认true
     */
    protected $_get_children_ids   = true;
    /**
     * @var string $_name_column 名称字段 CommonController->delete()会用到。默认cate_name
     */
    protected $_name_column        = 'cate_name';
    /**
     * @var array $_priv_map 权限映射，如'delete' => 'add'删除权限映射至添加权限
     */
    protected $_priv_map           = array(
        'delete'   => 'add',//删除
        'info'     => 'add',//具体信息
        'show'     => 'add',//显示隐藏
    );

    /**
     * 添加/编辑后置操作
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-18 10:49:04
     *
     * @return void 无返回值
     */
    protected function _afterCommonAddTreeData() {
        $this->_model->cate_id && $this->publicDeleteHtmlAction(array(array($this->_pk_field => $this->_model->cate_id)));
    }

    /**
     * 删除后置操作
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-18 11:05:45
     *
     * @param array $pk_id 主键值
     *
     * @return void 无返回值
     */
    protected function _afterDelete($pk_id) {

        if (!$pk_id) {
            return;
        }

        $data = array();

        foreach($pk_id as $v) {//转化为array('cate_id' => $cate_id)形式
            $data[] = array($this->_pk_field => $v);
        }

        $this->publicDeleteHtmlAction($data);//删除分类静态文件
        $this->createAction();//重新生成缓存

        $blog_arr = $this->_model->table(TB_BLOG)->field('link_url')->where(array('cate_id' => array('IN', $pk_id)))->select();//博客
        $this->_model->table(TB_BLOG)->where(array('cate_id' => array('IN', $pk_id)))->delete();//删除
        C('HTML_BUILD_INFO', $blog_arr);
        C(APP_FORWARD, true);
        $this->forward('Blog', 'publicDeleteHtml', array('build_arr' => null));//删除博客静态文件
    }

    /**
     * 获取分类树数据
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-03-21 13:42:41
     * @lastmodify      2013-03-31 13:17:48 by mrmsl
     *
     * @param $data 初始数据。默认array()，读分类树缓存
     *
     * @return array 分类树数据
     */
    private function _getCategory($data = array()) {
        $data = $data ? $data : $this->_getCache(0, CONTROLLER_NAME . '_tree');

        if (!$data) {//无分类缓存，直接返回
            return array();
        }

        $tree = array();
        $k    = 0;

        static $cate_id      = null;
        static $include_self = null;

        $cate_id      = null === $cate_id  ? Filter::int($this->_pk_field, 'get') : $cate_id;
        $include_self = null === $include_self ? isset($_GET['include_self']) : $include_self;

        foreach ($data as $cate) {

            if ($this->_unshift && !$include_self ? $cate_id != $cate[$this->_pk_field] : $cate['is_show']) {
                $tree[$k] = array(
                    'cate_id'   => $cate['cate_id'],
                    'parent_id' => $cate['parent_id'],
                    'cate_name' => $cate['cate_name'],
                    'leaf'      => $cate['leaf'],
                );

                if (!empty($cate['data'])) {
                    $tree[$k]['data'] = $this->_getCategory($cate['data']);
                }

                $k++;
            }
        }

        return $tree;
    }//end _getCategory

    /**
     * {@inheritDoc}
     */
    protected function _infoCallback(&$info) {
        $this->_treeInfoCallback($info, $this->_name_column);
    }

    /**
     * 添加或编辑
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-03-21 13:45:57
     *
     * @return void 无返回值
     */
    public function addAction() {
        $this->_commonAddTreeData('cate_name,en_name,seo_keyword,seo_description,parent_name,is_show,sort_order', $this->_name_column);
    }

    /**
     * 清除缓存
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-17 09:01:33
     *
     * @return void 无返回值
     */
    public function clearCacheAction() {
        $cate_id    = Filter::string($pk_field = $this->_pk_field);
        $cate_id    = map_int($cate_id, true);
        $cate_arr   = $this->_getCache();

        if ($cate_id) {
            $error          = '';
            $log            = '';
            $template       = $this->getViewTemplate();
            $name_column    = $this->_name_column;
            $cache_path     = $template->_cache_path . CONTROLLER_NAME . DS;

            foreach($cate_id as $v) {

                if (isset($cate_arr[$v])) {

                    foreach(glob($cache_path . "index{$v}-*") as $filename) {
                        unlink($filename);
                    }

                    $log .= ",{$cate_arr[$v][$name_column]}({$v})";
                }
                else {
                    $error .= ',' . $v;
                }
            }

            $error && $this->triggerError(__METHOD__ . ': ' . __LINE__ . ',' . L('CONTROLLER_NAME') . $error . L('NOT_EXIST'), E_USER_WARNING);

            if ($log) {
                $this->_model->addLog(L('CLEAR,CONTROLLER_NAME_CATEGORY,CACHE') . substr($log, 1) . L('SUCCESS'), LOG_TYPE_ADMIN_OPERATE);
                $this->_ajaxReturn(true, L('CLEAR,SUCCESS'));
            }
            else {
                $this->_model->addLog(L('CLEAR,CONTROLLER_NAME_CATEGORY,CACHE,FAILURE,%<br />,INVALID_PARAM,%:,CONTROLLER_NAME') . $error . L('NOT_EXIST'), LOG_TYPE_INVALID_PARAM);
            }
        }

        empty($error) && $this->_model->addLog(L("CLEAR,CONTROLLER_NAME_CATEGORY,CACHE,FAILURE,%<br />,INVALID_PARAM,%:,CONTROLLER_NAME,%{$this->_pk_field},IS_EMPTY"), LOG_TYPE_INVALID_PARAM);
        $this->_ajaxReturn(false, L('CLEAR,FAILURE'));
    }//end clearCacheAction

    /**
     * 生成缓存
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-03-21 13:38:34
     *
     * @return object $this
     */
    public function createAction() {
        $data      = $this->_model
        ->order('parent_id ASC,is_show DESC,sort_order ASC, cate_id ASC')
        ->key_column($this->_pk_field)->select();

        if ($data === false) {
            $this->_model->addLog();
            $this->_ajaxReturn(false, L('CREATE_CATEGORY_CACHE,FAILURE'), 'EXIT');
        }

        new_mkdir($ssi_path = SSI_PATH . 'category/');

        $suffix     = C('HTML_SUFFIX');
        $nav        = '<a href="'  . BASE_SITE_URL . 'category' . $suffix .'">' . L('CN_WANGWEN') . '</a>' . BREAD_SEPARATOR ;
        file_put_contents($ssi_path . 'nav0' . $suffix, $nav);

        foreach($data as $v) {//生成分类ssi导航
            $html = $nav;

            foreach (explode(',', $v['node']) as $item) {
                $info  = $data[$item];
                $html .= '<a href="' . $info['link_url'] . '">' . $info['cate_name'] . '</a>' . BREAD_SEPARATOR;
            }

            file_put_contents($ssi_path . 'nav' . $v[$this->_pk_field] . $suffix, $html);
        }

        $tree_data = Tree::array2tree($data, $this->_pk_field);//树形式


        return $this->_setCache($data)->_setCache($tree_data, $this->_getControllerName() . '_tree');
    }

    /**
     * 列表管理
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-03-21 13:50:24
     *
     * @return void 无返回值
     */
    public function listAction() {
        $cate_id     = Filter::int('node', 'get');//分类id
        $column      = Filter::string('column', 'get');//搜索字段

        //搜索 by mrmsl on 2012-07-24 18:02:02
        if (isset($_GET['is_show']) && in_array($column, array('cate_name', 'en_name', 'seo_keyword', 'seo_description'))) {
            $keyword     = Filter::string('keyword', 'get');//搜索关键字
            $is_show     = Filter::int('is_show', 'get');//是否显示 by mrmsl on 2012-09-15 12:14:57

            -1 != $is_show && $this->_queryTreeWhere = array('is_show' => array('eq', $is_show));

            $this->_queryTree($column, $keyword);
        }
        elseif ($cate_id) {
            $this->_ajaxReturn(true, '', $this->_getTreeData($cate_id, false));
        }

        $data = $this->_getCache(0, CONTROLLER_NAME . '_tree');

        $this->_ajaxReturn(true, '', $data, count($this->_getCache()));
    }//end listAction

    /**
     * 所属分类
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-03-21 13:58:09
     *
     * @return void 无返回值
     */
    public function publicCategoryAction() {
        $data = $this->_getCategory();

        //增加顶级分类
        $this->_unshift && array_unshift($data, array('cate_id' => 0, 'cate_name' => isset($_GET['emptyText']) ? Filter::string('emptyText', 'get') : L('TOP_LEVEL_CATEGORY'), 'leaf' => true));

        $parent_id = Filter::int('parent_id', 'get');

        //添加指定分类子分类，获取指定分类信息by mashanlng on 2012-08-21 13:53:35
        if ($parent_id && ($parent_info = $this->_getCache($parent_id))) {
            $parent_info = array(
                 'cate_id'     => $parent_id,
                 'parent_name' => $parent_info['cate_name'],
            );
            $this->_ajaxReturn(array('data' => $data, 'parent_data' => $parent_info));
        }

        $this->_ajaxReturn(true, '', $data);
    }

    /**
     * 删除分类静态页
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-04-12 22:01:02
     *
     * @return void 无返回值
     */
    public function publicDeleteHtmlAction($build_info = null) {
        $cate_arr   = $this->_getCache();
        $build_info = null === $build_info ? C('HTML_BUILD_INFO') : $build_info;
        $set_arr    = array();

        foreach($build_info as $item) {
            $cate_id = $item[$this->_pk_field];

            if (!isset($cate_arr[$cate_id]) || isset($set_arr[$cate_id])) {
                continue;
            }
            else {
                $cate_info = $cate_arr[$cate_id];
                $node_arr  = explode(',', $cate_info['node']);

                foreach($node_arr as $v) {

                    if (isset($set_arr[$v])) {
                        continue;
                    }
                    else {
                        $filename = str_replace(BASE_SITE_URL, WWWROOT, $cate_arr[$v]['link_url']);
                        is_file($filename) && unlink($filename);
                        $set_arr[$v] = true;
                    }
                }
            }
        }
    }

    /**
     * 显示/隐藏
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-03-21 13:32:41void 无返回值
     */
    public function showAction() {
        $this->_setOneOrZero();
    }
}