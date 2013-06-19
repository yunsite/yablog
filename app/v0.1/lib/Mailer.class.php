<?php
/**
 * 邮件发送类
 *
 * @file            Mailer.class.php
 * @package         Yab\Mailer
 * @version         0.1
 * @copyright       Copyright (c) 2013 {@link http://www.yablog.cn yablog} All rights reserved
 * @license         http://www.apache.org/licenses/LICENSE-2.0.html Apache License 2.0
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-06-05 17:19:40
 * @lastmodify      $Date$ $Author$
 */

define('PHPMAILER_PATH'         , LIB_PATH . 'phpmailer/');
define('PHPMAILER_SMTP_PATH'    , PHPMAILER_PATH);
require(PHPMAILER_PATH . 'class.phpmailer.php');

class Mailer extends PHPMailer {
    /**
     * @var object $_model 实例
     */
    private $_model = null;
    /**
     * @var object $_db 数据库实例
     */
    private $_db = null;
    /**
     * @var object $_view_template 数据库实例
     */
    private $_view_template = null;
    /**
     * @var int $_insert_history_id 邮件历史插入id
     */
    private $_insert_history_id = 0;
    /**
     * @var string $CharSet 邮件内容编码，默认utf-8
     */
    public $CharSet = 'utf-8';

    /**
     * @var string $ContentType 邮件内容mime类型，默认text/html
     */
    public $ContentType = 'text/html';

    /**
     * @var string $PluginDir smtp类所在路径，默认PHPMAILER_SMTP_PATH
     */
    public $PluginDir = PHPMAILER_SMTP_PATH;

    /**
     * @var string 发送邮件方法。默认smtp，可用mail, sendmail, smtp
     */
    public $Mailer = 'smtp';

    /**
     * @var bool true smtp发送需要验证用户。默认true
     */
    public $SMTPAuth = true;

    /**
     * 留言评论有回复，发邮件通知
     *
     * @author      mrmsl <msl-138@163.com>
     * @date        2013-06-07 17:36:25
     *
     * @param array $info 邮件信息,array('email' => $email, 'subject' => $subject, 'content' => $content)
     *
     * @return true|string true发送成功，否则错误信息
     */
    private function _comments_at_email($info) {
        $comment_info   = $info['email'];
        $info['email'] = $comment_info['email'];
        $info['mail_type'] = MAIL_TYPE_COMMMENTS_AT_EMAIL;
        $info['subject'] = str_replace('{$comment_name}', $comment_name = $comment_info['comment_name'], $info['subject']);
        $info['content'] = $this->_view_template
        ->assign(array(
            'comment_name'  => $comment_name,
            'content'       => $comment_info['parent_id'] ? str_replace('@<a class="link" href="', '@<a class="link" target="_blank" href="' . substr_replace($comment_info['link_url'], '', strpos($comment_info['link_url'], '#')), $comment_info['content']) : $comment_info['content'],
            'link_url'      => $comment_info['link_url'],
        ))
        ->fetch('Mail', 'comments_at_email');

        $result = $this->doMail($info);

        //C('_FACADE_SKIP', 'skip');
        //$this->_model->table(TB_COMMENTS)->where(array('comment_id' => $comment_info['comment_id']))->save(array('at_email' => true === $result ? MAIL_RESULT_SUCCESS : MAIL_RESULT_FAILURE));

        return $result;
    }

    /**
     * 重新发送邮件
     *
     * @author      mrmsl <msl-138@163.com>
     * @date        2013-06-07 16:50:57
     *
     * @param int $history_id 邮件历史id
     *
     * @return true|string true发送成功，否则错误信息
     */
    private function _reMail($history_id) {
        $info = $this->_model
        ->field('history_id,email,subject,content')
        ->table(TB_MAIL_HISTORY)
        ->where('history_id=' . $history_id)
        ->find();

        if ($info) {
            return $this->doMail($info);
        }
        else {
            $error  = L('CN_YOUJIAN,INFO') . "({$history_id})" . L('NOT_EXIST');
            $log    = __METHOD__ . ': ' . __LINE__ . ',' . $error;
            C('TRIGGER_ERROR', array($log, E_USER_ERROR, 'mail.error'));
            $this->_model->addLog($log, LOG_TYPE_INVALID_PARAM);

            return $error;
        }
    }

    /**
     * 构造函数
     *
     * @author      mrmsl <msl-138@163.com>
     * @date        2013-06-05 17:42:41
     *
     * @param object $model            数据库实例
     * @param object $view_template     默认null,自动获取
     * @param bool   $exceptions        true可捕获发送异常。默认false
     *
     * @return void 无返回值
     */
    public function __construct($model, $view_template = null,$exceptions = false) {
        parent::__construct($exceptions);
        $this->SetLanguage('zh_cn', PHPMAILER_PATH . 'language/');
        $this->setConfig();
        $this->_model = $model;
        $this->_view_template = null === $view_template ? $model->getProperty('_module')->getViewTemplate(array('_caching' => false)) : $view_template;
    }

    /**
     * 执行发送邮件
     *
     * @author      mrmsl <msl-138@163.com>
     * @date        2013-06-07 17:05:04
     *
     * @param array $info 邮件信息,array('email' => $email, 'subject' => $subject, 'content' => $content)
     *
     * @return true|string true发送成功，否则错误信息
     */
    public function doMail($info) {
        $this->Subject = $info['subject'];
        $this->MsgHTML($info['content']);
        $this->AddAddress($info['email']);

        $history_id = isset($info['history_id']) ? $info['history_id'] : 0;

        if (!$history_id) {
            $insert_data = array(
                'template_id'   => $info['template_id'],
                'add_time'      => time(),
                'email'         => $info['email'],
                'subject'       => $info['subject'],
                'content'       => $info['content'],
                'mail_type'     => isset($info['mail_type']) ? $info['mail_type'] : MAIL_TYPE_MISC,
            );
        }

        $success    = rand(0, 1);//测试,不需要真正发邮件
        //var_dump($info);//return

        if ($this->Send()) {

            if ($history_id) {
                C('_FACADE_SKIP', 'skip');
                $this->_model->table(TB_MAIL_HISTORY)->where(array('history_id' => $history_id))->setInc('times');
            }
            else {
                $insert_data['times'] = 1;
            }

            $result = true;
        }
        else {
            $result = $this->ErrorInfo;
            $this->_model->addLog(L('SEND,CN_YOUJIAN') . $info['email'] . "({$info['subject']})" . L('FAILURE') . ': ' . $result, LOG_TYPE_EMAIL);
            //$result = false;
        }

        if (!$history_id) {
            C('_FACADE_SKIP', 'skip');
            $this->_model->table(TB_MAIL_HISTORY)->add($insert_data);
            $this->_insert_history_id = $this->_model->getDb()->getLastInsertID();
        }

        return $result;
    }//end doMail

    /**
     * 获取属性值
     *
     * @author      mrmsl <msl-138@163.com>
     * @date        2013-06-11 17:10:08
     *
     * @param string $name 属性值
     *
     * @return mixed 属性值
     */
    public function getProperty($name) {
        return isset($this->$name) ? $this->$name : null;
    }

    /**
     * 发送邮件
     *
     * @author      mrmsl <msl-138@163.com>
     * @date        2013-06-07 14:17:32
     *
     * @param mixed $mail_info 邮件模板名称或邮件历史id
     * @param string $email 要发送的邮箱
     *
     * @return bool true ntud
     */
    public function mail($mail_info = null, $email = null) {

        if (is_numeric($mail_info)) {
           return $this->_reMail($mail_info);
        }
        elseif (is_array($mail_info)) {
            return $this->doMail($mail_info);
        }
        else {
            static $mail_template_info = array();

            if (!isset($mail_template_info[$mail_info])) {
                $info = $this->_model
                ->table(TB_MAIL_TEMPLATE)
                ->where(array('template_name' => $mail_info))
                ->field('template_id,subject,template_name')
                ->find();

                if (!$info) {
                    $error  = L('MAIL_TEMPLATE,INFO') . "({$mail_info})" . L('NOT_EXIST');
                    $log    = __METHOD__ . ': ' . __LINE__ . ',' . $error;
                    C('TRIGGER_ERROR', array($log, E_USER_ERROR, 'mail.error'));
                    $this->_model->addLog($log, LOG_TYPE_INVALID_PARAM);
                    $mail_template_info[$mail_info] = false;

                    return $error;
                }

                $mail_template_info[$mail_info] = $info;
            }

            if ($info = $mail_template_info[$mail_info]) {
                if (method_exists($this, $method = '_' . $mail_info)) {//_+邮件模板名即为发送方法
                    $info['email'] = $email;
                    return $this->$method($info);
                }
                else {
                    $error  = L('MAIL_TEMPLATE,METHOD') . $method . L('NOT_EXIST');
                    $log    = __METHOD__ . ': ' . __LINE__ . ',' . $error;
                    C('TRIGGER_ERROR', array($log, E_USER_ERROR, 'mail.error'));
                    $this->_model->addLog($log, LOG_TYPE_INVALID_PARAM);

                    return $error;
                }
            }
        }//end if
    }//end mail

    /**
     * 设置邮箱配置
     *
     * @author      mrmsl <msl-138@163.com>
     * @date        2013-06-06 09:22:33
     *
     * @param array $config 配置信息,默认null,通过sys_config()获取
     *
     * @return void 无返回值
     */
    public function setConfig($config = null) {
        $config = null === $config ? sys_config() : $config;
        $this->Host       = $config['sys_mail_smtp'];
        $this->Port       = $config['sys_mail_smtp_port'];
        $this->Username   = $config['sys_mail_email'];
        $this->Password   = $config['sys_mail_password'];
        $this->SetFrom($config['sys_mail_email'], $config['sys_mail_from_name']);
    }
}