<div class="Page">
<?php
$list = $v['views']->arrays(\Boolive\values\Rule::string());
foreach ($list as $item) {
    echo $item;
}
echo $v->NextPrevNavigation->string();
?>
</div>