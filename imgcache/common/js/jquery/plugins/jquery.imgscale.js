/**
 * jquery等比例缩放图片插件
 *
 * @file            jquery.imgscale.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2013-07-21 22:24:14
 * @lastmodify      $Date$ $Author$
 */

(function($) {
    /**
     * 绝对居中
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-07-21 22:08:47
     *
     * @param {object} obj html元素
     *
     * @return {void} 无返回值
     */
    function absCenter(obj) {
        obj.style.position = 'absolute';
        obj.style.left = (document.body.clientWidth - obj.clientWidth) / 2 + document.body.scrollLeft + 'px';
        obj.style.top = (document.body.clientHeight - obj.clientHeight) / 2 + document.body.scrollTop + 'px';
    }

    /**
     * 绝对居中显示原图
     *
     * @param   {object}    img     图片img
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-07-21 23:05:14
     *
     * @return {void} 无返回值
     */
    function showImg(img) {
        img.on('click', function() {
            var o = window.IMGSCALE, src = img.attr('data-src') || img.attr('src');

            if (o) {

                if (o.attr('src') == src) {

                    if (o.is(':visible')) {
                        o.hide();
                    }
                    else {
                        o.show();
                        absCenter(o[0]);
                    }
                }
                else {
                    o.show();
                    o.attr('src', src);
                    absCenter(o[0]);
                }
            }
            else {
                o = $('<img src="' + src + '" />').appendTo($body)
                .css('border', '3px solid #ccc')
                .on({
                    mouseleave: function() {
                        $(this).hide();
                    }
                });
                window.IMGSCALE = o;log(o, src);
                absCenter(o[0]);

            }
        });
    }//end showImg

    /**
     * 等比例缩放图片及点击图片居中显示原图
     *
     * @member window
     *
     * @param   {object}    img     图片img
     * @param   {int}    [width=240]     缩放至宽度
     * @param   {int}    [height=180]    缩放至高度
     * @param   {bool}   [clickevent=undefined] true绑定点击事件
     *
     * @author          mrmsl <msl-138@163.com>
     * @date            2013-07-21 22:04:32
     *
     * @return {void} 无返回值
     */
    function imgScale(img, width, height, clickevent) {
        width = width || 240;
        height = height || 180;
        img = $(img);
        var src = img.attr('data-src') || img.attr('src');
        var obj = new Image();
        obj.src = src;

        if (obj.width > 0 && obj.height > 0) {

            if (obj.width / obj.height >= width / height) {

                if (obj.width > width) {
                    img.width(width);
                    img.height(obj.height * width / obj.width);
                }
            }
            else if (obj.height > height) {
                    img.height(height);
                    img.width(obj.width * height / obj.height);
            }
        }

        img.show();

        clickevent && showImg(img);
    }//end imgScale

    jQuery.fn.imgscale = function() {
        return this.each(function() {
            var me = $(this);
            me.attr('data-pointer') && me.css('cursor', 'pointer');
            imgScale(this, me.attr('data-width'), me.attr('data-height'), me.attr('data-clickevent'));
        });
    };
})(jQuery);