<div class="BaseExplorer Editor" data-o="<?=$v['object']?>"  data-v="<?php echo $v['view_uri'];?>" data-p="Editor">
    <div class="BaseExplorer__head">
        <div class="BaseExplorer__head-left">
            <h1 class="BaseExplorer__title"><?=$v['title']?></h1>
            <div class="Editor__attribs-btn" title="Атрибуты объекта"><img src="/Site/Library/admin_widgets/Editor/res/style_editor/attr.png" width="16" height="16" alt=""/></div>
        </div>
        <div class="BaseExplorer__head-right">
            <?=$v->FilterTool->string()?>
        </div>
    </div>
    <div class="Editor__attribs" style="display: none;">
        <div class="Editor__attribs-item">
            <div class="Editor__attribs-col1">
                <label>Адрес</label>
            </div>
            <div class="Editor__attribs-col2">
                <div class="inpt Editor__attribs-uri">
                    <div class="Editor__attribs-uri-parent"></div>
                    <div class="Editor__attribs-uri-name-wrap">
                        <input type="text" class="Editor__attribs-uri-name" value=""/>
                    </div>
                </div>
                <div class="Editor__attribs-error Editor__attribs-error-parent Editor__attribs-error-name"></div>
            </div>
        </div>
<!--        <div class="Editor__attribs-item">-->
<!--            <div class="Editor__attribs-col1">-->
<!--                <label>Прототип</label>-->
<!--            </div>-->
<!--            <div class="Editor__attribs-col2">-->
<!--                <input type="text" class="inpt" value="--><?//=$v['object']?><!--"/>-->
<!--            </div>-->
<!--        </div>-->
    </div>
    <p class="BaseExplorer__description"><?=$v['description']?></p>

    <div class="BaseExplorer__list Editor__list">
    <?php
        $list = $v['views']->arrays(\Boolive\values\Rule::string());
        if(!empty($list)){
            foreach ($list as $item) echo $item;
        }else{
            echo '<div class="BaseExplorer__empty">Пусто
                    <div class="BaseExplorer__empty-explain">У объекта нет подчиненных или они не соответсятвуют фильтру </div></div>';
        }
    ?>
    </div>
</div>