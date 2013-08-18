<div class="ToolParagraph" style="display: none" data-v="<?php echo $v['view_uri'];?>" data-p="ToolParagraph">
    <div class="btn-group proto-group">
        <a class="btn-tool proto" href="">Обычный</a>
        <ul class="dropdown-menu proto-list">
            <?php foreach ($v['plist'] as $p){ ?>
                <li><a href="" data-value="<?php echo $p['id'];?>"><?php echo $p['title'];?></a></li>
            <?php } ?>
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
    <div class="btn-divider-vertical"></div>
    <div class="btn-group font-size-group">
        <a class="btn-tool font-size" href=""></a>
        <ul class="dropdown-menu font-size-list">
            <li><a href="" data-value="7pt">7pt</a></li>
            <li><a href="" data-value="8pt">8pt</a></li>
            <li><a href="" data-value="9pt">9pt</a></li>
            <li><a href="" data-value="10pt">10pt</a></li>
            <li><a href="" data-value="11pt">11pt</a></li>
            <li><a href="" data-value="12pt">12pt</a></li>
            <li><a href="" data-value="14pt">14pt</a></li>
            <li><a href="" data-value="17pt">17pt</a></li>
            <li><a href="" data-value="22pt">22pt</a></li>
            <li><a href="" data-value="30pt">30pt</a></li>
            <li><a href="" data-value="43pt">43pt</a></li>
            <li><a href="" data-value="64pt">64pt</a></li>
            <li><a href="" data-value="">Авто</a></li>
        </ul>
    </div>
</div>