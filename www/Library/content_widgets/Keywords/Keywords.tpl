<div class="keywords_wrapper">
    <h3>Ключевые слова</h3>
    <div class="keywords">
        <?php
        $keywords = $v['view']->arrays(\Boolive\values\Rule::string());
        foreach ($keywords as $keyword) {
        echo $keyword;
        }
        ?>
    </div>
</div>
