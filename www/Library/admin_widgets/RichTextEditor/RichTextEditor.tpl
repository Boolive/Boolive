<div class="RichTextEditor" data-p="RichTextEditor" data-o="<?=$v['object']?>"  data-v="<?php echo $v['view_uri'];?>">
    <div class="tools"><div class="body">
        <div class="toolbar">
            <a class="btn btn-success btn-disable save" href="">Сохранить</a>
        </div>
        <div class="hrule" data-p="RichTextRule">
            <div class="bg"><div class="client"></div></div>
            <div class="nums">
                <!-- example -->
                <div class="sep-num"><div>0</div></div>
                <div class="sep-small"></div>
                <div class="sep-big"></div>
                <div class="sep-small"></div>
                <div class="sep-num"><div>1</div></div>
                <div class="sep-small"></div>
                <div class="sep-big"></div>
                <div class="sep-small"></div>
            </div>
            <div class="pleft" title="Поле страницы слева. 30px"></div>
            <div class="pright" title="Поле страницы справа. 30px"></div>
            <div class="s-top tindent" title="Отступ первой строки абзаца. 0px"></div>
            <div class="s-bottom mleft" title="Отступ абзаца. 150px"></div>
            <div class="s-bottom mright" title="Отступ абзаца справа. 0px"></div>
        </div>
    </div></div>
    <div class="content" contentEditable="true" onresizestart="return false;" <?php if ($s = $v['style']->string()) echo 'style="'.$s.'"';?>><?php
    $list = $v['view']->arrays(\Boolive\values\Rule::string());
    if (!sizeof($list)) echo '<p><br /></p>';
    foreach ($list as $item) {
        echo $item;
    }
    ?></div>
</div>