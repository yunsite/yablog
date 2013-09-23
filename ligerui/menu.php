<?php
class Menu {
    public function listAction() {
        $html = <<<EOT
<div id="menulist" tabid="menulist">
    <table id="grid-menulist" class="easyui-treegrid"></table>
    <span id="menulist"></span>
    <div id="tb-menulist">
        <a href="javascript:void(0)" class="easyui-menubutton" id="menu-operate" data-options="menu:'#menu-menulist'">操作</a>
        <div id="menu-menulist" style="width:150px;">
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
        <input id="menu-start_date" class="datetime" /> -
        <input id="menu-end_date" class="datetime" />
        <input id="menu-cate_id" />
        <input type="text" id="menu-match_mode" />
        <input type="text" id="menu-keyword" />
    </div>
</div>
EOT;
        echo $html;
    }
}