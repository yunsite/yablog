<?php
class Blog {
    public function addAction() {
        $html = <<<EOT
<form id="blogadd" method="post">
    <div>
        <label for="name">Name:</label>
        <input class="validatebox" type="text" name="name" data-options="required:true" />
    </div>
    <div>
        <label for="email">Email:</label>
        <input class="validatebox" type="text" name="email" data-options="required:true,validType:'email'" />
    </div>
    <div>
        <label for="email">所属分类:</label>
        <input type="text" name="cate_id" />
    </div>
    <div>
        内容: <div id="ueditor-blogadd" style="width: 90%;height: 300px;"></div>
    </div>
    <div><input type="submit" value="提 交" /></div>
</form>
EOT;
        echo $html;
    }

    public function listAction() {
        $html = <<<EOT
<table id="grid-bloglist"></table>
<span id="bloglist"></span>
<div id="tb-bloglist">
    <a href="javascript:void(0)" class="easyui-menubutton" id="blog-operate">操作</a>
    <div id="blog-menulist">
        <div data-options="iconCls:'icon-undo'">删除</div>
    </div>
    添加时间
    <input type="text" data-name="start_date" data-jeasyui="datebox" /> -
    <input type="text" data-name="end_date" data-jeasyui="datebox" />
    <input type="text" data-name="cate_id" data-jeasyui="combobox" />
    <input type="text" data-name="combotree" data-jeasyui="combotree" data-multiple="true" />
    <input type="text" data-name="match_mode" data-jeasyui="combobox" />
    <input type="text" data-name="keyword" data-jeasyui="searchbox" />
</div>
EOT;
        echo $html;
    }
}