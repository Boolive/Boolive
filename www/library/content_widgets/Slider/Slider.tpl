<div class="Slider" data-p="slidesjs" data-p-option='{"height":300, "width":"100%","play":{"effect":"slide", "auto": true, "interval":6000, "restartDelay":100}, "effect":{"slide":{"speed":800}}}'>
    <?php
    $list = $v['views']->arrays(\boolive\values\Rule::string());
    foreach ($list as $item) {
        echo $item;
    }
    ?>
    <div class="controls"></div>
</div>