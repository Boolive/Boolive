<div class="Destroy" data-v="<?php echo $v['view_uri'];?>" data-o="<?php echo $v['data-o'];?>" data-p="Destroy" data-prev="<?php echo $v['prev'];?>">
    <div class="layout-main">
        <h2><?php echo $v['title'];?></h2>
        <div class="layout-middle">
            <ul class="entity-list">
            <?php
                $list = $v['objects'];//->arrays(\boolive\values\Rule::any());
                foreach ($list as $item):
            ?>
                <li>
                <span class="txt-primary"><?php echo $item['title'];?></span>
                <?php if ($item['uri']->string()):?>
                <span class="txt-tag"><?php echo $item['uri'];?></span>
                <?php endif;?>
                </li>
            <?php endforeach; ?>
            </ul>
            <p><?php echo $v['question'];?></p>
            <p class="mini"><?php echo $v['message'];?></p>
            <?php
                $conflicts = $v['conflicts']->any();
                if (!empty($conflicts['access'])):
            ?>
                <h3>Нет прав</h3>
                <p>Уничтожение невозможно из-за отсутстсвия прав на уничтожение следующих объектов</p>
                <ul class="entity-list">
                <?php foreach ($conflicts['access'] as $i => $uri):?>
                    <li><a href="/admin<?php echo $uri;?>" class="txt-tag" target="_blank"><?php echo $uri;?></a></li>
                <?php
                    endforeach;
                    if (count($conflicts['access'])>=50) echo '<li>Список неполный</li>';
                ?>
                </ul>
            <?php endif; ?>
            <?php if (!empty($conflicts['heirs'])): ?>
                <h3>Конфликты</h3>
                <p>Уничтожение невозможно, так как выбранные объекты используются перечисленными ниже объектами.</p>
                <p class="mini">Сперва удалите их или поменяйте у них прототипы</p>
                <ul class="entity-list">
                <?php foreach ($conflicts['heirs'] as $uri):?>
                    <li><a href="/admin<?php echo $uri;?>" class="txt-tag" target="_blank"><?php echo $uri;?></a></li>
                <?php
                    endforeach;
                    if (count($conflicts['heirs'])>=50) echo '<li>Список неполный</li>';
                ?>
                </ul>
            <?php endif; ?>
        </div>
        <div class="layout-bottoms">
            <?php if (true || empty($conflicts)): ?>
                <a class="btn btn-danger submit" href="#">Уничтожить</a>
            <?php endif; ?>
            <a class="btn cancel" href="#">Отмена</a>
        </div>
     </div>
</div>