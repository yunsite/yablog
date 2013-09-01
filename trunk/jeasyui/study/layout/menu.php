<?php
class Menu {
    public function listAction() {
        $html = <<<EOT
<table id="tg-menulist" class="easyui-treegrid"></table>
<div id="tb-menulist">
    <a href="javascript:void(0)" class="easyui-menubutton" id="menu-operate" data-options="menu:'#menu-menulist', iconCls:'icon-edit'">操作</a>
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
    <select id="menu-match_mode" class="match_mode">
        <option value="">匹配模式</option>
        <option value="eq">完全匹配</option>
        <option value="leq">左匹配</option>
        <option value="req">右匹配</option>
        <option value="like">模糊匹配</option>
    </select>
    <input id="menu-keyword" />
</div>
EOT;
        echo $html;
    }
}