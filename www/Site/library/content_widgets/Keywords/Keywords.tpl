<div class="Keywords">
    <ul class="list">
        <?php
        $keywords = $v['views']->arrays(\Boolive\values\Rule::string());
        foreach ($keywords as $keyword) {
            echo '<li>'.$keyword.'</li>';
        }
        ?>
    </ul>
</div>
