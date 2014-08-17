<div class="Page"><?php
    $list = $v['views']->arrays(\boolive\values\Rule::string());
    foreach ($list as $item){
        echo $item;
    }
    echo $v->pagesnum->string();
    echo $v->NextPrevNavigation->string();
?>
</div>