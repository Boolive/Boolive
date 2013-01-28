<div class="RichTextEditor" data-plugin="RichTextEditor" data-object="<?=$v['object']?>"  data-view_uri="<?php echo $v['view_uri'];?>">
    <div class="tools"><div class="body">
        <a class="btn btn-success btn-disable save" href="">Сохранить</a>
    </div></div>
    <div class="content" contentEditable="true" onresizestart="return false;"><?php
    $list = $v['view']->arrays(\Boolive\values\Rule::string());
    if (!sizeof($list)) echo '<p><br /></p>';
    foreach ($list as $item) {
        echo $item;
    }
    ?></div>
</div>