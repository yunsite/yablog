/**
 * 留言评论js
 *
 * @file            comments.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-05-07 13:48:02
 * @lastmodify      $Date$ $Author$
 */

define('comments', [], function (require, exports, module) {

    //评论类
    function Comments() {
        var me = this;
        /**
         * @var {string} _formPanelId 评论表单面板id,<div id="form-panel"
         */
        me._formPanelId = 'form-panel';

        /**
         * @var {string} _replyFormPanelId 回复表单面板id,<div id="form-reply"
         */
        me._replyFormPanelId = 'form-reply';

        /**
         * @var {string} _commentFormId 评论表单id,<form id="form-comment"
         */
        me._commentFormId = 'form-comment';

        /**
         * @var {int} _guestbookType 留言类型
         */
        me._guestbookType = 0;

        /**
         * @var {int} formPanel 博客评论类型
         */
        me._blogType = 1;

        /**
         * @var {int} _miniblogType 微博评论类型
         */
        me._miniblogType = 2;

        /**
         * @var {string} _submitBtnId 提交按钮id,<input type="btn-submit"
         */
        me._submitBtnId = 'btn-submit';

        /**
         * 添加留言或者评论
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-05-05 22:13:52
         *
         * @return {object} 本类实例
         */
        me._addComments = function()  {
            me._formPanel = $('#' + me._formPanelId).html(me._getFormHtml());//表单,html

            me._commentForm = $('#' + me._commentFormId)//评论表单
            .on('submit', me._submitForm)
            .on('click', 'input,textarea', function (e) {
                me._commentForm.find('.error').hide();
                'content' == $(e.target).attr('name') && me._commentForm.find('div.hide').show();//显示用户名，邮件等
            })
            .on('error', me._formError);

            return me;
        };//end _addComments



        /**
         * ctrl + enter提交表单
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-05-26 21:49:16
         *
         * @return {object} 本类实例
         */
        me._bindSubmitForm = function () {
            $(document).on('keypress', function (e) {
                if (e.ctrlKey && (13 == e.keyCode || 10 == e.keyCode)) {
                    me._commentForm.find('div.hide').show();
                    me._commentForm.trigger('submit', true);
                }
            });

            return me;
        };//end _bindSubmitForm

        /**
         * 绑定验证码事件
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-05-21 08:43:04
         *
         * @return {object} 本类实例
         */
        me._bindVerifycode = function () {
            var el = $('#txt-verifycode');

            if (el.length) {
                el.focus(function () {
                    var img = el.parent().find('img');

                    if (!img.length) {
                        $('<img />').bind({
                            error: function() {

                                if (!el.data('error')) {
                                    this.src = System.sys_base_common_imgcache + 'images/verifycode_error.png';
                                    el.data('error', true);
                                }
                            },
                            click: function() {
                                this.src = System.sys_base_site_url + 'verifycode/' + me._verifycodeModule + '.shtml?' + Math.random();
                            }
                        }).attr({
                            title: lang('REFRESH_CODE_TIP'),
                            id: 'img-verifycode',
                            src: System.sys_base_site_url + 'verifycode/' + me._verifycodeModule + '.shtml'
                        }).css({
                            valign: 'absmiddle',
                            margin: '0 5px',
                            cursor: 'pointer'
                        }).insertAfter(el)
                        .after(
                            $('<span class="muted">' +
                            (1 == System[me._verifycodeModule + '_verifycode_case'] ? lang('CASE_SENSITIVE') : lang('NO,CASE_SENSITIVE')) +
                             '，' + lang('VERIFY_CODE_ORDER') + '：' + '<span class="text-error">' +
                            (System[me._verifycodeModule + '_verifycode_order']) +
                            '</span></span>')
                        );
                    }
                });
            }
            /*else if (!el.next('img').is(':visible')) {
                el.next('img').click();
            }*/

            return me;
        };//end _bindVerifycode

        /**
         * 验证表单
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-05-26 09:38:29
         *
         * @param {bool} [trigger=undefined] true通过trigger触发
         *
         * @return {void} true验证成功，否则false
         */
        me._checkForm = function (trigger) {

            if (IS_OLD_IE || trigger) {//ie6,7,8
                var checked = true;
                var elArr = [
                    [me._commentForm.find('input[name=username]'), 'USERNAME'],
                    [me._commentForm.find('textarea'), 'CONTENT'],
                    [me._commentForm.find('input[name=_verify_code]'), 'VERIFY_CODE']
                ];

                for (var i = 0, len = elArr.length; i < len; i++) {

                    var item = elArr[i];

                    if (!item[0].val().trim()) {
                        me._commentForm.trigger('error', [lang('PLEASE_ENTER,' + item[1]), item[0]]);

                        return false;
                    }
                };

                var el = me._commentForm.find('input[name=email]'), email = el.val().trim();

                if (email && !/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/.test(email)) {
                    me._commentForm.trigger('error', [lang('PLEASE_ENTER,CORRECT,CN_DE,EMAIL'), el]);

                    return false;
                }

            }//end if IS_OLD_IE

            var el = me._commentForm.find('input[name=user_homepage]'), url = el.val().trim();

            if (url.length && 'http://' != url && !/http:\/\/[a-z0-9]+\.[a-z0-9]+/i.test(url)) {//主页链接
                me._commentForm.trigger('error', [lang('PLEASE_ENTER,CORRECT,CN_DE,HOMEPAGE,LINK'), el]);

                return false;
            }

            if (me._commentForm.find('input[name=at_email]').prop('checked')) {
                var el = me._commentForm.find('input[name=email]'), email = el.val().trim();

                if (!email) {//勾选 有人回复我时通知我，邮箱却为空
                    me._commentForm.trigger('error', [lang('PLEASE_ENTER,EMAIL'), el]);

                    return false;
                }
            }

            return true;
        };//_checkForm

        /**
         * 表单错误
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-05-26 09:36:15
         *
         * @return {void} 无返回值
         */
        me._formError = function (e, error, input) {
            var el = $(this).find('.error').html(error).show();

            if (input) {//输入框
                me.jQShake(input);
                input.after(el);
            }
            else {//置于提交按钮前
                me._submitBtn.before(el);
                me.jQShake(el, {border: '1px solid red', 'background-color': '#ffe9e8'}, {border: 'none', 'background-color': '#fff'});
            }

            animateTop((input || el).offset().top - 100);
        };//end _formError

        /**
         * 提交表单
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-05-26 09:39:18
         *
         * @param {object} e event
         * @param {bool} [trigger=undefined] true通过trigger触发
         *
         * @return {bool} false，阻止表单提交
         */
        me._submitForm = function (e, trigger) {

            if (!me._checkForm(trigger)) {
                return false;
            }

            $.ajax({
                method: 'post',
                url: System.sys_base_site_url + 'comments/add.shtml',
                dataType: 'json',
                data: $(this).serialize(),
                beforeSend: function () {
                    me._submitBtn = me._commentForm.find('#' + me._submitBtnId).attr('disabled', true);
                },
                ok: function () {
                    location.href = System.sys_base_site_url + 'msg.shtml?success=1&type=' + (me._guestbookType == me._commentType ? 'guestbook' : 'comment');
                },
                success: function(data) {

                    if (data) {

                        if (data.success) {
                            this.ok();
                        }
                        else if (data.msg) {

                            if (data.redirect) {
                                lang('SERVER_ERROR', data.msg);
                                this.error();
                            }
                            else {
                                me._commentForm.trigger('error', data.msg);
                            }
                        }
                        else {
                            this.error();
                        }
                    }
                    else {
                        this.error();
                    }
                },
                complete: function() {
                    me._submitBtn.attr('disabled', false);
                },
                error: function() {
                    location.href = System.sys_base_site_url + 'msg.shtml?msg=' + encodeURIComponent(lang('SERVER_ERROR'));
                }
            });

            return false;
        };
        /**
         * 获取留言或者评论 表单 html
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-05-06 17:05:32
         *
         * @return {string} 表单html
         */
        me._getFormHtml = function () {

            switch (NAV_ID) {
                case GUESTBOOK_FLAG://留言
                    var type = me._guestbookType, blogId = 0, verifycodeModule = 'module_guestbook';
                    break;

                default://评论
                    var blogId = META_INFO.hits.split(',')[1], verifycodeModule = 'module_comments';
                    var type = BLOG_FLAG == NAV_ID ? me._blogType : me._miniblogType;
                    break;
            }

            me._verifycodeModule = verifycodeModule;
            me._commentType = type;

            var html = [];
            html.push('<form class="form-horizontal" id="' + me._commentFormId + '" method="post" action="' + System.sys_base_site_url + 'comments/add.shtml">');
            html.push('    <div class="control-group hide">');
            html.push('        <label class="control-label"><span class="text-error">*</span>' + lang('USERNAME') + '</label>');
            html.push('        <div class="controls">');
            html.push('            <input type="text" name="username" required maxlength="20" />');
            html.push('            <span class="muted">(' + lang('LT_BYTE').replace('{0}', 20) + '。' + lang('CN_TO_BYTE') + ')</span>');
            html.push('        </div>');
            html.push('    </div>');
            html.push('    <div class="control-group hide">');
            html.push('        <label class="control-label">' + lang('EMAIL') + '</label>');
            html.push('        <div class="controls">');
            html.push('            <input type="email" value="" name="email" maxlength="50" />');
            html.push('            <span class="muted">(' + lang('CN_XUANTIAN,%，,SECRET,%，,NO,SHOW,%。,SUPPORT,%gravatar,HEAD_PIC') + '。' + lang('LT_BYTE').replace('{0}', 50) + ')</span>');
            html.push('        </div>');
            html.push('    </div>');
            html.push('    <div class="control-group hide">');
            html.push('        <label class="control-label">' + lang('HOMEPAGE') + '</label>');
            html.push('        <div class="controls">');
            html.push('            <input type="text" value="http://" name="user_homepage" />');
            html.push('            <span class="muted">(' + lang('CN_XUANTIAN') + '。' + lang('LT_BYTE').replace('{0}', 50) + ')</span>');
            html.push('        </div>');
            html.push('    </div>');
            html.push('    <div class="control-group">');
            html.push('        <label class="control-label"><span class="text-error">*</span>' + lang('CONTENT') + '</label>');
            html.push('        <div class="controls">');
            html.push('            <textarea name="content" rows="3" cols="50" class="input-block-level" required></textarea>');
            html.push('            <span class="muted">http(s)://www.yablog.cn/path/?querystring ' + lang('SPACE') + '... =&gt; <a href="http://www.yablog.cn/path/?querystring" rel="nofollow">http(s)://www.yablog.cn/path/?querystring</a></span>');
            html.push('        </div>');
            html.push('    </div>');

            if (1 == System[me._verifycodeModule + '_verifycode_enable']) {//开启验证码
                html.push('    <div class="control-group hide">');
                html.push('        <label class="control-label"><span class="text-error">*</span>' + lang('VERIFY_CODE') + '</label>');
                html.push('        <div class="controls">');
                html.push('            <input type="text" id="txt-verifycode" name="_verify_code" required maxlength="6" />');
                html.push('        </div>');
                html.push('    </div>');
            }
            else {
                html.push('<input type="hidden" name="_verify_code" value="ok" />');
            }

            html.push('    <div class="control-group hide">');
            html.push('        <div class="controls">');
            html.push('            <label class="muted"><input type="checkbox" value="1" name="at_email" /> ' + lang('AT_ME_NOTICE_ME') + '</label>');
            html.push('        </div>');
            html.push('    </div>');
            html.push('    <div class="controls text-right">');
            html.push('        <span class="error font-red" style="padding: 4px 3px 4px 4px; margin-right: 1px;"></span>');
            html.push('        <button id="btn-submit" class="btn btn-primary">' + lang('SUBMIT') + '</button>');
            html.push('        <button id="btn-reset-cancel" type="reset" class="btn hide">' + lang('CANCEL') + '</button>');
            html.push('         <span class="muted">' + lang('SUBMIT_TIP') + '</span>');
            html.push('    </div>');
            html.push('    <input type="hidden" name="type" value="' + type + '" />');
            html.push('    <input type="hidden" name="parent_id" value="0" />');
            html.push('    <input type="hidden" name="blog_id" value="' + blogId + '" />');
            html.push('</form>');

            html = html.join('');

            return html;
        };//end _getFormHtml

        /**
         * 回复
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-05-26 09:44:30
         *
         * @return {bool} false
         */
        me._replyClick = function () {
            var href = $(this).attr('href'),
            el = $(href),//<span id="base-{comment_id}"></span>
            id = href.split('-')[1];

            if (!me._replyFormPanel) {//首次点击
                var html = [];
                html.push('<div class="popover hide bottom" id="' + me._replyFormPanelId +'">');
                html.push('    <div class="arrow"></div>');
                html.push('    <div class="popover-title">' + lang('REPLY') + ' <b class="name"></b></div>');
                html.push('    <div class="popover-content">');
                html.push('    </div>');
                html.push('</div>');
                el.after(html.join(''));//置于<span id="base-{comment_id}"></span>后
                me._replyFormPanel = $('#' + me._replyFormPanelId);
                me._commentForm.appendTo(me._replyFormPanel.find('.popover-content'));//表单置于.popover-content内

                me._resetCancelBtn = $('#btn-reset-cancel').on('click', function() {//取消
                    me._replyFormPanel.hide();
                    me._commentForm.appendTo(me._formPanel).find('input[name=parent_id]').val(0);
                    me._commentForm.find('div.hide').hide().end().find(me._resetCancelBtn).hide();
                });
            }
            else {
                if (el.next('#' + me._replyFormPanelId).find('form').length) {//toggle
                     me._resetCancelBtn.trigger('click');
                    return false;
                }

                me._commentForm.appendTo(me._replyFormPanel.find('.popover-content'));
                el.after(me._replyFormPanel);
            }

            me._replyFormPanel.show()
            .find('b.name').text($(this).next().text())
            .end()
            .find('input[name=parent_id]').val(id)
            .end()
            .find(me._resetCancelBtn).show();

            animateTop(el.offset().top - 100);

            return false;
        };//end _replyClick

        /**
         * 鼠标滑过留言评论，显示回复
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-05-01 22:20:05
         *
         * @return {object} 本类实例
         */
        me._showCommentsReply = function () {

            $('.comment-detail').hover(function(e) {
                 $.each($(this).parents('.comment-detail'), function(index, item) {
                    $(item).find('.reply:first').hide();
                    $(item).find('.time-axis:first').show();
                });
                $(this).find('.reply:first').show();
                $(this).find('.time-axis:first').hide();

                return false;
            }, function(e) {
                $(this).find('.reply:first').hide();
                $(this).find('.time-axis:first').show();
            })
            .find('.reply').on('click', me._replyClick)
            .end().find('a[href^=#comment-]').on('click', function() {
                animateTop($($(this).attr('href')).offset().top - 50);

                return false;
            });

            return me;
        };//end _showCommentsReply

        /**
         * 启用函数
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-05-25 22:42:59
         *
         * @return {object} 本类实例
         */
         me.init = function () {
            me._showCommentsReply()//鼠标滑过留言评论，显示回复
            ._addComments()//添加留言或者评论
            ._bindVerifycode()//绑定验证码事件
            ._bindSubmitForm();//ctrl + enter提交表单

            return me;
         };

        /**
         * 闪烁效果
         *
         * @author          mrmsl <msl-138@163.com>
         * @date            2013-05-26 10:16:26
         *
         * @param {object} el 闪烁元素
         * @param {object} [oddCss={'background-color': '#ffe9e8', 'border-color': 'red'}] 闪烁奇数次css
         * @param {object} [evenCss={'background-color': '#fff', 'border-color': '#ccc'}] 闪烁偶数次css
         * @param {object} [endCss=evenCss] 闪烁结束css
         * @param {int} [times=3] 闪烁次数
         *
         * @return {object} 本类实例
         */
        me.jQShake = function(el, oddCss, evenCss, endCss, times){
            oddCss = oddCss || {'background-color': '#ffe9e8', 'border-color': 'red'};
            evenCss = evenCss || {'background-color': '#fff', 'border-color': '#ccc'};
            var i = 0, t= false , times = times || 3;

            if(t) {
                return;
            }

            el.css(oddCss);
            t = setInterval(function() {
                i++;

                el.css(i % 2 ? evenCss : oddCss);

                if(i == 2 * times){
                    clearInterval(t);
                    el.focus();
                    el.css(endCss || evenCss);
                }
            }, IS_OLD_IE ? 200 : 400);
        };//end jQShake
    }//end Comments

    module.exports = new Comments().init();
});