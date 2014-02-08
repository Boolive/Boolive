<div class="BaseExplorer" data-o="<?=$v['object']?>"  data-v="<?php echo $v['view_uri'];?>" data-p="BaseExplorer">
    <div class="BaseExplorer__head TileExplorer__head">
        <div class="BaseExplorer__head-left">
            <h1 class="BaseExplorer__title" title="Название объекта"><?=$v['title']?></h1>
            <?php if ($v['proto-uri']!='//0'):?>
            <a class="BaseExplorer__proto" href="<?=ltrim($v['proto-uri']->escape(),'/')?>" title="Прототип объекта" data-o="<?=$v['proto-uri']?>"><?=$v['proto-title']?></a>
            <?php endif;?>
            <div class="BaseExplorer__attribs-btn" title="Изменить адрес (URI) объекта"><img src="/Site/Library/admin_widgets/BaseExplorer/res/base_style/uri-link.png" width="16" height="16" alt=""/></div>
        </div>
        <div class="BaseExplorer__head-right">
            <?=$v->FilterTool->string()?>
        </div>
    </div>
    <div class="BaseExplorer__attribs TileExplorer__attribs" style="display: none;">
        <div class="BaseExplorer__attribs-item">
            <?php $key = uniqid();?>
            <div class="BaseExplorer__attribs-col1">
                <label class="BaseExplorer__attribs_host" title="Родитель и название объекта" for="<?=$key?>">Адрес</label>
            </div>
            <div class="BaseExplorer__attribs-col2">
                <div class="inpt BaseExplorer__attribs-uri">
                    <div class="BaseExplorer__attribs-uri-parent" title="Сменить родителя"></div>
                    <div class="BaseExplorer__attribs-uri-name-wrap">
                        <input type="text" class="BaseExplorer__attribs-uri-name" id="<?=$key?>" value=""/>
                    </div>
                </div>
                <div class="BaseExplorer__attribs-error BaseExplorer__attribs-error-parent BaseExplorer__attribs-error-name"></div>
            </div>
        </div>
    </div>
    <p class="BaseExplorer__description"><?=$v['description']?></p>

    <div class="BaseExplorer__list TileExplorer__list"><?php
        $list = $v['views']->arrays(\Boolive\values\Rule::string());
        if(!empty($list)){
            foreach ($list as $item) echo $item;
        }else{?>
            <div class="BaseExplorer__empty"><?=$v['empty']?>
                <div class="BaseExplorer__empty-explain">
                <?php if ($v['select'] == 'structure'):?>
                У объекта нет самостоятельных подчиненных или они не соответсятвуют фильтру.<br/>
                Их можно добавить или перейдите к просмотру свойств или других сведений об объекте с помощью нижнего меню.<br/>
                <a href="<?=ltrim($v['object'],'/')?>&select=property" class="BaseExplorer__show-entity" data-select="property">Свойства</a>
                <?php elseif($v['select'] == 'property'):?>
                У объекта нет свойств или они не соответсятвуют фильтру.<br/>
                Их можно добавить или перейдите к просмотру структуры или других сведений об объекте с помощью нижнего меню.<br/>
                <a href="<?=ltrim($v['object'],'/')?>&select=structure" class="BaseExplorer__show-entity" data-select="structure">Структура</a>
                <?php elseif($v['select'] == 'heirs'):?>
                У объекта нет наследников или они не соответсятвуют фильтру.<br/>
                Перейдтие к просмотру других сведений об объекте с помощью нижнего меню.
                <?php elseif($v['select'] == 'protos'):?>
                У объекта нет прототипов или они не соответсятвуют фильтру.<br/>
                Перейдтие к просмотру других сведений об объекте с помощью нижнего меню.
                <?php endif;?>
                </div>
            </div>
        <?php }
    ?></div>
</div>