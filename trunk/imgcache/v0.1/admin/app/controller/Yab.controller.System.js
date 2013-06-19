/**
 * 系统设置控制器
 *
 * @file            app/controller/Yab.controller.System.js
 * @version         0.1
 * @author          mrmsl <msl-138@163.com>
 * @date            2012-07-23 21:23:26
 * @lastmodify      $Date$ $Author$
 */

Ext.define('Yab.controller.System', {
    extend: 'Yab.controller.Base',

     /**
     * @inheritdoc Yab.controller.Log#__call
     */
    __call: function(data) {
        this.tabs(data.action);
    }
});

//放到最后，以符合生成jsduck类说明
Ext.data.JsonP.Yab_controller_System(Yab.controller.System);