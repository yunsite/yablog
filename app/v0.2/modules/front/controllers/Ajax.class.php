<?php
/**
 * ajax异步请求控制器类
 *
 * @file            Ajax.class.php
 * @package         Yab\Module\Home\Controller
 * @version         0.2
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-05-02 09:02:56
 * @lastmodify      $Date$ $Author$
 */

class AjaxController extends CommonController {
    /**
     * @var bool $_init_model true实例对应模型。默认false
     */
    protected $_init_model      = true;

    /**
     * 统计点击量
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-03 08:42:51
     *
     * @return void 无返回值
     */
    private function _updateHits() {
        $hits   = Filter::string('hits');//blog,id,add_time | miniblog,id,add_time

        if ($hits) {
            $hits_arr = explode(',', $hits);
            $valid    = false;

            if (3 == count($hits_arr) && in_array($hits_arr[0], array('miniblog', 'blog')) && ($id = intval($hits_arr[1])) && ($add_time = intval($hits_arr[2]))) {
                $this->_model->execute('UPDATE ' . DB_PREFIX . $hits_arr[0] . " SET hits=hits+1 WHERE blog_id={$id} AND add_time={$add_time}");
                $valid = $this->_model->getDb()->getProperty('_num_rows');

            }

            if (!$valid) {
                $this->triggerError($log = __METHOD__ . ': ' . __LINE__ . ',' . L('INVALID_PARAM') . var_export($hits_arr, true));
                $this->_model->addLog($log, LOG_TYPE_INVALID_PARAM);
            }
        }
    }

    /**
     * 顶操作
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-08 11:51:05
     *
     * @return void 无返回值
     */
    public function diggAction() {
        $diggs   = Filter::string('diggs');//blog,id,add_time | miniblog,id,add_time

        if ($diggs) {
            $diggs_arr = explode(',', $diggs);
            $valid     = false;

            if (3 == count($diggs_arr) && in_array($diggs_arr[0], array('miniblog', 'blog')) && ($id = intval($diggs_arr[1])) && ($add_time = intval($diggs_arr[2]))) {
                $this->_model->execute('UPDATE ' . DB_PREFIX . $diggs_arr[0] . " SET diggs=diggs+1 WHERE blog_id={$id} AND add_time={$add_time}");
                $valid = $this->_model->getDb()->getProperty('_num_rows');

            }

            $valid && $this->_ajaxReturn('.' . $diggs_arr[0] . '-diggs-' . $id);

            $this->triggerError($log = __METHOD__ . ': ' . __LINE__ . L('INVALID_PARAM') . var_export($diggs_arr, true));
            $this->_model->addLog($log, LOG_TYPE_INVALID_PARAM);
        }

        $this->_ajaxReturn(false);
    }

    /**
     * ajax异步获取博客,微博元数据,包括点击量,评论数等
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-05-02 16:21:34
     * @lastmodify      2013-05-03 08:41:05 by mrmsl
     *
     * @return void 无返回值
     */
    public function metaInfoAction() {

        /*foreach (array(TB_BLOG, TB_MINIBLOG) as $table) {

            foreach($this->_model->table($table)->select() as $v) {
                $sql = sprintf('UPDATE %s SET hits=%d,comments=%d,diggs=%d WHERE blog_id=%d', $table, rand(1, 1000), rand(1, 50), rand(1, 20), $v['blog_id']);
                $this->_model->execute($sql);
            }
        }*/

        $this->_updateHits();//统计点击

        $blog       = Filter::string('blog');
        $miniblog   = Filter::string('miniblog');

        if (!$blog && !$miniblog) {//空数据
            $this->triggerError($log = __METHOD__ . ': ' . __LINE__ . ',' . L('INVALID_PARAM'));
            $this->_model->addLog($log, LOG_TYPE_INVALID_PARAM);
            $this->_ajaxReturn(false);
        }
        $blog           = 0 === strpos($blog, ',') ? substr($blog, 1) : $blog;
        $miniblog       = 0 === strpos($miniblog, ',') ? substr($miniblog, 1) : $miniblog;
        $field_arr      = 'blog_id,add_time';
        $field          = 'blog_id,add_time,hits,comments,diggs';
        $miniblog_data  = $this->_getPairsData($field_arr, $miniblog, $field, 'blog_id', TB_MINIBLOG);
        $blog_data      = $this->_getPairsData($field_arr, $blog, $field, 'blog_id', TB_BLOG);

        $this->_ajaxReturn(array('blog' => $blog_data, 'miniblog' => $miniblog_data, 'success' => true));
    }//end metaInfoAction
}