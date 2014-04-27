<div class="BreadcrumbsMenu" data-v="<?php echo $v['view_uri'];?>" data-p="BreadcrumbsMenu">
	<div class="BreadcrumbsMenu__view">
        <ul class="BreadcrumbsMenu__list">
            <li class="BreadcrumbsMenu__list-item">
              <a class="BreadcrumbsMenu__list-entity" href="" data-o="">
                <span>Шаблон</span>
              </a>
            </li>
            <?php for ($i = count($v['items'])-1; $i>=0; $i--):?>
                <li class="BreadcrumbsMenu__list-item <?php echo $v['items'][$i]['class'];?>" style="z-index: <?php echo $i?>;">
                    <a class="BreadcrumbsMenu__list-entity" href="<?php echo $v['items'][$i]['url']?>" data-o="<?php echo $v['items'][$i]['uri']?>"><span><?php echo $v['items'][$i]['title']?></span></a>
                </li>
            <?php endfor; ?>
        </ul>
        <div class="BreadcrumbsMenu__btn BreadcrumbsMenu__btn-inline" title="Адрес строкой"><img width="16" height="16" alt="" src="/site/library/admin/Admin/BreadcrumbsMenu/res/style/img/pencil.png"/></div>
<!--        <div class="BreadcrumbsMenu__btn BreadcrumbsMenu__btn-editor"><img width="16" height="16" alt="" src="/site/library/laadmin/AdminreadcrumbsMenu/res/style/img/pencil.png"/></div>-->
        <a href="/<?=ltrim($v['current']->url(),'/')?>" target="_blank" class="BreadcrumbsMenu__btn BreadcrumbsMenu__btn-out" title="Просмотр на сайте"><img width="16" height="16" alt="" src="/site/library/admin/Admin/BreadcrumbsMenu/res/style/img/arrow-out.png"/></a>
	</div>
    <div class="BreadcrumbsMenu__inline" style="display: none;">
        <input class="BreadcrumbsMenu__list-inline-input" type="text" value="<?php echo $v['current'];?>">
	</div>
    <div class="BreadcrumbsMenu__editor" style="display: none;">
        <div class="BreadcrumbsMenu__editor-uri-parent" title="Сменить родителя"><?=$v['current']?>/</div>
        <div class="BreadcrumbsMenu__editor-uri-name-wrap">
            <div class="BreadcrumbsMenu__editor-uri-name" contenteditable="true">name</div>
        </div>
        <div class="BreadcrumbsMenu-error BreadcrumbsMenu-error-parent BreadcrumbsMenu-error-name"></div>
	</div>
</div>