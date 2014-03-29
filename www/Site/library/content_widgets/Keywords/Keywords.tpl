<div class="Keywords">
    <h3>Ключевые слова</h3>
    <ul class="list">
        <?php
        $keywords = $v['views']->arrays(\Boolive\values\Rule::string());
        foreach ($keywords as $keyword) {
            echo '<li>'.$keyword.'</li>';
        }
        ?>
    </ul>
</div>