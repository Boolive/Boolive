<div class="RichTextEditor" data-p="RichTextEditor" data-o="<?=$v['object']?>"  data-v="<?php echo $v['view_uri'];?>">
    <div class="tools" ><div class="body RichTextBG">
        <div class="toolbar" data-p="RichTextToolbar">
            <div class="btn-group paragraph-type-group">
                <a class="btn-tool paragraph-type" href="">Обычный</a>
                <ul class="dropdown-menu paragraph-type-list">
                    <li class="selected"><a href="" data-value="">Обычный</a></li>
                    <li><a href="" data-value="">Циата</a></li>
                    <li><a href="" data-value="">Код</a></li>
                    <li><a href="" data-value="">Заголовок 1</a></li>
                    <li><a href="" data-value="">Заголовок 2</a></li>
                    <li><a href="" data-value="">Заголовок 3</a></li>
                    <li><a href="" data-value="">Заголовок 4</a></li>
                    <li><a href="" data-value="">Заголовок 5</a></li>
                    <li><a href="" data-value="">Заголовок 6</a></li>
                </ul>
            </div>
            <a class="btn-tool btn-tool-square align-left" href=""></a>
            <a class="btn-tool btn-tool-square align-center" href=""></a>
            <a class="btn-tool btn-tool-square align-right" href=""></a>
            <a class="btn-tool btn-tool-square align-justify" href=""></a>
            <div class="btn-divider-vertical"></div>
            <div class="btn-group line-height-group">
                <a class="btn-tool btn-tool-square line-height" href=""></a>
                <ul class="dropdown-menu line-height-list">
                    <li><a href="" data-value="0.8">0.8</a></li>
                    <li><a href="" data-value="1">1</a></li>
                    <li><a href="" data-value="normal"><b>1.2 (Нормально)</b></a></li>
                    <li><a href="" data-value="1.48">1.48 (Оптимально)</a></li>
                    <li><a href="" data-value="2">2</a></li>
                    <li><a href="" data-value="3">3</a></li>
                </ul>
            </div>

            <a class="btn btn-success btn-disable save" href="">Сохранить</a>
        </div>
        <div class="hrule" data-p="RichTextRule">
            <div class="bg"><div class="client"></div></div>
            <div class="nums">
                <!-- example -->
                <div class="sep-num"><div>0</div></div>
                <div class="sep-small"></div>
                <div class="sep-big"></div>
                <div class="sep-small"></div>
                <div class="sep-num"><div>1</div></div>
                <div class="sep-small"></div>
                <div class="sep-big"></div>
                <div class="sep-small"></div>
            </div>
            <div class="pleft" title="Поле страницы слева. 30px"></div>
            <div class="pright" title="Поле страницы справа. 30px"></div>
            <div class="s-top tindent" title="Отступ первой строки абзаца. 0px"></div>
            <div class="s-bottom mleft" title="Отступ абзаца. 150px"></div>
            <div class="s-bottom mright" title="Отступ абзаца справа. 0px"></div>
        </div>
    </div></div>
    <div class="content" contentEditable="true" onresizestart="return false;" <?php if ($s = $v['style']->string()) echo 'style="'.$s.'"';?>><?php
    $list = $v['view']->arrays(\Boolive\values\Rule::string());
    if (!sizeof($list)) echo '<p>&#8203;</p>';
    foreach ($list as $item) {
        echo $item;
    }
    ?></div>
</div>