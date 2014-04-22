<div class="Attribs" data-v="<?php echo $v['view_uri'];?>" data-p="Attribs">
    <div class="Attribs__head">
        <h1 class="Attribs__title"><?php echo $v['head'];?></h1>
    </div>
    <p class="Attribs__description">Атрибуты определяют внутреннее устройство объекта и имеются у всех объектов</p>
    <div class="Attribs__main">
        <h2></h2>
        <form action="" enctype="multipart/form-data" type="POST">
            <div style="display: none;">
                <input type="hidden" name="direct" value="<?php echo $v['view_uri'];?>">
                <input type="hidden" name="call" value="save">
                <input type="hidden" name="object" value="<?php echo $v['object_uri'];?>">
            </div>
            <div class="item">
                <div class="col1">
                    <label title="URI - идентфиикатор объекта">Адрес (URI)</label>
                </div>
                <div class="col2 item-parent item-name">
                    <!--<span class="name" data-name="name"></span><span data-name="uri" class="txt-tag"></span>
                    <span class="error-message"></span>00>-->
                    <span data-name="parent-uri" title="Родитель" class="input-pfx tag-link">&nbsp;</span><input class="attrib inpt name" type="text" id="name" name="attrib[name]" data-name="name" value=""/>
                    <input type="hidden" name="attrib[parent]" value="">
                    <span class="description"></span>
                    <span class="error-message"></span>
                </div>
            </div>
            <div class="item">
                <div class="col1">
                    <label for="value">Значение</label>
                </div>
                <div class="col2  item-value">
                    <div class="inpt value">
                        <textarea class="attrib" id="value" name="attrib[value]" rows="4" cols="0" data-name="value"></textarea>
                        <div class="tags">
                            <div title="Загрузить файл" class="tag fileupload">Выбрать файл
                                <input type="file" size="1" name="attrib[file]"/>
                            </div>
                            <div class="tag is_file" data-name="is_file">Файл</div>
                            <input type="hidden" name="attrib[is_file]" value="">
                            <div class="tag default" data-name="is_null">По умолчанию</div>
                            <input type="hidden" name="attrib[is_null]" value="">
                        </div>
                    </div>
                    <span class="error-message"></span>
                </div>
            </div>

            <div class="item">
                <div class="col1">
                    <label>Прототип</label>
                </div>
                <div class="col2 item-proto">
                    <span class="input-pfx tag-link" data-name="proto-uri" title="Сменить прототип...">&nbsp;</span>
<!--                    <a class="btn-icon-delete" title="Удалить прототип у объекта" href="" data-name="proto-delete" style="display: none;"></a>-->
                    <input type="hidden" name="attrib[proto]" value="">
                    <span class="error-message"></span>
                    <span class="description">Наследуемый объект или на кого ссылаться.</span>
                </div>
            </div>
            <div class="item">
                <div class="col1">

                </div>
                <div class="col2 item-is_relative">
                    <input class="attrib" type="checkbox" id="is_relative" name="attrib[is_relative]" data-name="is_relative" value="1"/>
                    <label for="is_relative">Относительный прототип</label>
                    <span class="error-message"></span><br>
                    <span class="description">Прототип для новых объектов определяется автоматически в зависимости от расположения обекта.</span>
                </div>
            </div>
            <div class="item">
                <div class="col1">

                </div>
                <div class="col2 item-is_link">
                    <input class="attrib" type="checkbox" id="is_link" name="attrib[is_link]" data-name="is_link" value="1"/>
                    <label for="is_link">Ссылка</label>
                    <span class="error-message"></span><br>
                    <span class="description">Если объект является ссылкой, то подчиненные у прототипа не наследуются.
                        Ссылка используется, чтобы сослаться на прототип и использовать его.
                        Например, автор статьи - это ссылка на пользователя, так как статье не нужен новый объект пользователя</span>
                </div>
            </div>
            <div class="item">
                <div class="col1">

                </div>
                <div class="col2 item-is_logic">
                    <input class="attrib" type="checkbox" id="is_logic" name="attrib[is_logic]" data-name="is_logic" value="1"/>
                    <label for="is_logic" title="Объект имеет свой php-класс или использует класс прототипа?">Своя логика</label>
                    <span class="error-message"></span><br>
                    <span class="description">Своей логикой дополняется базовая логика объекта. Логика объекта описывается php-классом.<br>
                    Используется класс: <span class="txt-tag class_name"></span><br>
                    </span>
                </div>
            </div>
            <div class="item">
                <div class="col1">
                    <label for="order">Порядковый номер</label>
                </div>
                <div class="col2 item-order">
                    <input class="attrib inpt order" type="text" id="order" name="attrib[order]" data-name="order" value=""/>
                    <span class="error-message"></span>
                    <span class="description">Используется для ручного упорядочивания объектов</span>
                </div>
            </div>


            <div class="item">
                <div class="col1">

                </div>
                <div class="col2 item-is_draft">
                    <input class="attrib" type="checkbox" id="is_draft" name="attrib[is_draft]" data-name="is_draft" value="1"/>
                    <label for="is_draft">Черновик</label>
                    <span class="error-message"></span><br>
                    <span class="description">Не готовый к использованию объект. Достпуен только в админке</span>
                </div>
            </div>
            <div class="item">
                <div class="col1">
                </div>
                <div class="col2 item-is_hidden">
                    <input class="attrib" type="checkbox" id="is_hidden" name="attrib[is_hidden]" data-name="is_hidden" value="1"/>
                    <label for="is_hidden">Скрытый</label>
                    <span class="error-message"></span><br>
                    <span class="description">Скрытый объект доступен везде, но невиден.</span>
                </div>
            </div>
            <div class="item">
                <div class="col1">
                </div>
                <div class="col2 item-is_mandatory">
                    <input class="attrib" type="checkbox" id="is_mandatory" name="attrib[is_mandatory]" data-name="is_mandatory" value="1"/>
                    <label for="is_mandatory">Обязательный</label>
                    <span class="error-message"></span><br>
                    <span class="description">Если объект обязательный, то он автоматически наследуется при прототипировании родительского объекта</span>
                </div>
            </div>
            <div class="item">
                <div class="col1">
                </div>
                <div class="col2 item-is_property">
                    <input class="attrib" type="checkbox" id="is_property" name="attrib[is_property]" data-name="is_property" value="1"/>
                    <label for="is_property">Свойство</label>
                    <span class="error-message"></span><br>
                    <span class="description">Является свойством родительского объекта или объект самостоятельный? Например "Страница" - самостоятельный объект, а "текст страницы" - свойство</span>
                </div>
            </div>

            <div class="item">
                <div class="col1">
                    <label>Сокращённый URI</label>
                </div>
                <div class="col2 item-date">
                    <span class=""><?php echo $v['object_id'];?></span>
                    <span class="error-message"></span>
                    <span class="description">Состоит из имени хранилища и идентификатора объекта в этом хранилище</span>
                </div>
            </div>
            <div class="item">
                <div class="col1">
                    <label>Дата обновления</label>
                </div>
                <div class="col2 item-date">
                    <span data-name="date" class=""></span>
                    <span class="error-message"></span>
                    <span class="description"></span>
                </div>
            </div>
            <div class="item submits">
                <div class="col1"></div>
                <div class="col2">
<!--                    <a class="btn btn-success btn-disable submit" href="#">Сохранено</a>-->
<!--                    <a class="btn hide reset">Восстановить</a>-->
<!--                    <a class="btn cancel" href="#">Отмена</a>-->
                    <p class="submit-message"></p>
                </div>
            </div>
        </form>
    </div>
</div>