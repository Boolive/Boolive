<div class="Keywords">
    <h3 class="Keywords__title">Ключевые запросы</h3>
    <ul class="Keywords__list">
        <?php
        $keywords = $v['views']->arrays(\boolive\values\Rule::string());
        foreach ($keywords as $keyword) {
            echo '<li class="Keywords__item">'.$keyword.'</li>';
        }
        ?>
    </ul>
</div>