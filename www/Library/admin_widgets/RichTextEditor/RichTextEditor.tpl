<div class="RichTextEditor" contenteditable="true"  data-plugin="RichTextEditor">
<?php
    $list = $v['view']->arrays(\Boolive\values\Rule::string());
    foreach ($list as $item) {
        echo $item;
    }
?>
</div>