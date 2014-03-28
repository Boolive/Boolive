<div class="Import" data-v="<?php echo $v['view_uri'];?>" data-o="<?php echo $v['data-o'];?>" data-p="Import">
    <div class="layout-main">
        <h2><?php echo $v['title'];?></h2>
        <p>Загрузка данных в текущий раздел из файла.</p>
        <p class="mini">Выберите файл с импортируемой информацией</p>
        <form class="Import__form" action="" enctype="multipart/form-data" type="POST">
            <input class="Import__input-file" type="file" size="" name="import" id="import"/>
            <div class="Import__buttons">
                <a class="btn btn-primary Import__input-submit btn-disable" href="#">+ Добавить в очередь</a>
            </div>
        </form>
        <h4 class="Import__h4">Очередь на импорт <a class="Import__clear" href="">Очистить завершенные</a></h4>
        <ul class="Import__list">
            <?php foreach ($v['tasks'] as $task):?>
            <li class="Import__item">
                <span class="Import__item-title"><?=$task['title']?></span>
                <span class="Import__item-status Import__item-status_<?=$task['status']?>"><?=$task['status_msg']?></span>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>