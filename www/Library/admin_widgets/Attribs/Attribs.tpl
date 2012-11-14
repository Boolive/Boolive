<div class="Attribs" data-view_uri="<?php echo $v['view_uri'];?>" data-plugin="Attribs">
    <div class="content">
        <h2><?php echo $v['head'];?></h2>
        <form action="" enctype="multipart/form-data" type="POST">
            <div style="display: none;">
                <input type="hidden" name="direct" value="<?php echo $v['view_uri'];?>">
                <input type="hidden" name="call" value="save">
                <input type="hidden" name="object" value="<?php echo $v['object_uri'];?>">
            </div>
            <div class="item">
                <div class="col1">
                    <label>Название</label>
                </div>
                <div class="col2 item-name">
                    <span class="name" data-name="name"></span><span data-name="uri" class="txt-tag"></span>
                    <span class="error-message"></span>
                </div>
            </div>
            <div class="item">
                <div class="col1">
                    <label>Прототип</label>
                </div>
                <div class="col2 item-proto">
                    <a class="internal" href="proto" data-name="proto-show"></a><a href="" data-name="proto-uri" class="txt-tag"></a>
                    <input type="hidden" name="attrib[proto]" value="">
                    <span class="error-message"></span>
                </div>
            </div>
            <div class="item">
                <div class="col1">
                    <label for="value">Значение</label>
                </div>
                <div class="col2  item-value">
                    <div class="inpt value">
                        <textarea class="attrib" id="value" name="attrib[value]" rows="5" cols="0" data-name="value"></textarea>
                        <div class="tags">
                            <div title="Загрузить файл" class="tag file" data-name="is_file">Файл
                                <input type="file" size="1" name="attrib[file]"/>
                                <input type="hidden" name="attrib[is_file]" value="">
                            </div>
                            <div class="tag default" data-name="is_null">По умолчанию</div>
                            <input type="hidden" name="attrib[is_null]" value="">
                        </div>
                    </div>
                    <span class="error-message"></span>
                </div>
            </div>
            <div class="item">
                <div class="col1">
                    <label>Язык</label>
                </div>
                <div class="col2 item-lang">
                    <a class="internal" href="lang" data-name="lang-show"></a>
                    <input type="hidden" name="attrib[lang]" value="">
                    <span class="error-message"></span>
                </div>
            </div>
            <div class="item">
                <div class="col1">
                    <label>Владелец</label>
                </div>
                <div class="col2 item-owner">
                    <a class="internal" href="owner" data-name="owner-show"></a>
                    <input type="hidden" name="attrib[owner]" value="">
                    <span class="error-message"></span>
                </div>
            </div>
            <div class="item">
                <div class="col1">
                    <label for="order">Порядковый номер</label>
                </div>
                <div class="col2 item-order">
                    <input class="attrib inpt order" type="text" id="order" name="attrib[order]" data-name="order" value=""/>
                    <span class="error-message"></span>
                </div>
            </div>
            <div class="item">
                <div class="col1">
                    <label>Признаки</label>
                </div>
                <div class="col2 item-is_logic">
                    <input class="attrib" type="checkbox" id="is_logic" name="attrib[is_logic]" data-name="is_logic" value="1"/>
                    <label for="is_logic">Своя логика</label>
                    <span class="error-message"></span>
                </div>
            </div>
            <div class="item">
                <div class="col1">

                </div>
                <div class="col2 item-is_hidden">
                    <input class="attrib" type="checkbox" id="is_hidden" name="attrib[is_hidden]" data-name="is_hidden" value="1"/>
                    <label for="is_hidden">Скрытый</label>
                    <span class="error-message"></span>
                </div>
            </div>
            <div class="item">
                <div class="col1">

                </div>
                <div class="col2 item-is_link">
                    <input class="attrib" type="checkbox" id="is_link" name="attrib[is_link]" data-name="is_link" value="1"/>
                    <label for="is_link">Использовать как ссылку</label>
                    <span class="error-message"></span>
                </div>
            </div>
            <div class="item">
                <div class="col1">

                </div>
                <div class="col2 item-overide">
                    <input class="attrib" type="checkbox" id="override" name="attrib[override]" data-name="override" value="1"/>
                    <label for="override">Не наследовать подчиненных</label>
                    <span class="error-message"></span>
                </div>
            </div>
            <div class="item">
                <div class="col1">
                    <label>Дата обновления</label>
                </div>
                <div class="col2 item-date">
                    <span data-name="date" class="txt-tag"></span>
                    <span class="error-message"></span>
                </div>
            </div>
            <div class="item">
                <div class="col1"></div>
                <div class="col2">
                    <a class="btn cancel" href="#">Отмена</a>
                    <a class="btn hide reset">Восстановить</a>
                    <a class="btn btn-success btn-disable submit" href="#">Сохранено</a>
                    <p class="submit-message"></p>
                </div>
            </div>
        </form>
    </div>
</div>