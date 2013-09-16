<?php
class Admin {
    public function addAction() {
        $html = <<<EOT
<form id="adminadd" method="post">
    <div>
        <label for="name">Name:</label>
        <input class="validatebox" type="text" name="name" data-options="required:true" />
    </div>
    <div>
        <label for="email">Email:</label>
        <input class="validatebox" type="text" name="email" data-options="required:true,validType:'email'" />
    </div>
    <div><input type="submit" value="提 交" /></div>
</form>
EOT;
        echo $html;
    }

    public function listAction() {
        $html = <<<EOT
<table id="grid-adminlist"></table>
<span id="adminlist"></span>
<div id="tb-adminlist">
    <a href="javascript:void(0)" class="easyui-menubutton" id="admin-operate">操作</a>
    <div id="admin-menulist">
        <div data-options="iconCls:'icon-undo'">删除</div>
        <div>
            <span>移动</span>
                <div>
                <div>数据库</div>
                <div>php</div>
                <div>js</div>
            </div>
        </div>
    </div>
    添加时间
    <input type="text" data-name="start_date" data-jeasyui="datebox" /> -
    <input type="text" data-name="end_date" data-jeasyui="datebox" />
    <input type="text" data-name="cate_id" data-jeasyui="combobox" />
    <input type="text" data-name="match_mode" data-jeasyui="combobox" />
    <input type="text" data-name="keyword" data-jeasyui="searchbox" />
</div>
EOT;
        echo $html;
    }
}