<?php
class Admin {
    public function listAction() {
        $html = <<<EOT
<table id="grid-adminlist" class="easyui-treegrid"></table>
<span id="adminlist"></span>
<div id="tb-adminlist">
    <a href="javascript:void(0)" class="easyui-menubutton" id="admin-operate" data-options="menu:'#admin-menulist'">操作</a>
    <div id="admin-menulist" style="width:150px;">
        <div data-options="iconCls:'icon-undo'">删除</div>
        <div data-options="iconCls:'icon-redo'">Redo</div>
        <div class="menu-sep"></div>
        <div>Cut</div>
        <div>Copy</div>
        <div>Paste</div>
        <div class="menu-sep"></div>
        <div data-options="iconCls:'icon-remove'">Delete</div>
        <div>Select All</div>
    </div>
    添加时间
    <input id="admin-start_date" class="datetime" /> -
    <input id="admin-end_date" class="datetime" />
    <input id="admin-cate_id" />
    <input type="text" id="admin-match_mode" />
    <input type="text" id="admin-keyword" />
</div>
EOT;
        echo $html;
    }
}