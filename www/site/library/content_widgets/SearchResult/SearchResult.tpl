<div class="SearchResult">
    <form action="" method="get" accept-charset="UTF-8" enctype="multipart/form-data">
        <input type="submit" class="search-submit" value="Найти">
        <div class="search-text">
            <input type="text" value="<?=$v['search']?>" name="search">
        </div>
    </form>
    <?php
    $list = $v['views']->arrays(\boolive\values\Rule::string());
    if (count($list)){ ?>
        <h2>Результаты поиска</h2>
        <div class="result"><?php foreach ($list as $item) echo $item;?></div>
    <?php } else { ?>
        <p class="message"><?php
            if ($v['search']->bool()){
                echo 'Ничего не найдено';
            }else{
                echo 'Что ищем?';
            }
        ?></p>
    <?php } ?>
</div>