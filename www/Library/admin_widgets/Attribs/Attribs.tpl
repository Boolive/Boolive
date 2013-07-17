<div class="Attribs" data-v="<?php echo $v['view_uri'];?>" data-p="Attribs">
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
                    <label title="URI - идентфиикатор объекта">Название с путём</label>
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
                    <label>Прототип</label>
                </div>
                <div class="col2 item-proto">
                    <span class="input-pfx tag-link" data-name="proto-uri" title="Сменить прототип...">&nbsp;</span>
                    <a class="btn-icon-delete" title="Удалить прототип у объекта" href="" data-name="proto-delete" style="display: none;"></a>
                    <input type="hidden" name="attrib[proto]" value="">
                    <span class="error-message"></span>
                    <span class="description">Наследуемый объект или на кого ссылаться.</span>
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
                    <label for="order">Порядковый номер</label>
                </div>
                <div class="col2 item-order">
                    <input class="attrib inpt order" type="text" id="order" name="attrib[order]" data-name="order" value=""/>
                    <span class="error-message"></span>
                    <span class="description">Используется для ручного упорядочивания объектов</span>
                </div>
            </div>
            <!--<div class="item">
                <div class="col1">
                    <label>Родитель</label>
                </div>
                <div class="col2 item-parent">
                    <span class="txt-tag" data-name="uri"></span>
                    <!--<a class="internal" href="" data-name="name" title="Внтури чего находится редактируемый объект."> </a>-->

                   <!-- <input type="hidden" name="attrib[parent]" value="">
                    <span class="error-message"></span>
                    <span class="description"></span>
                </div>
            </div>-->

            <!--<div class="item">
                <div class="col1">
                    <label>Владелец</label>
                </div>
                <div class="col2 item-owner">
                    <a class="internal" href="owner" data-name="owner-show" title="Чей объект? Объект доступен только владельцу, если не является общим."></a>
                    <a href="" data-name="owner-delete" class="btn-icon-delete" title="Сделать общим"></a>
                    <span data-name="owner-uri" class="txt-tag"></span>
                    <input type="hidden" name="attrib[owner]" value="">
                    <span class="error-message"></span>
                    <span class="description"></span>
                </div>
            </div>
            <div class="item">
                <div class="col1">
                    <label>Язык</label>
                </div>
                <div class="col2 item-lang">
                    <a class="internal" href="lang" data-name="lang-show" title="Если не общий, то объект доступен только на выбранном языке сайта."></a>
                    <a href="" data-name="lang-delete" class="btn-icon-delete" title="Сделать общим"></a>
                    <span data-name="lang-uri" class="txt-tag"></span>
                    <input type="hidden" name="attrib[lang]" value="">
                    <span class="error-message"></span>
                    <span class="description"></span>
                </div>
            </div>-->
            <div class="item">
                <div class="col1">
                    <label>Признаки</label>
                </div>
                <div class="col2 item-is_hidden">
                    <input class="attrib" type="checkbox" id="is_hidden" name="attrib[is_hidden]" data-name="is_hidden" value="1"/>
                    <label for="is_hidden">Скрытый</label>
                    <span class="error-message"></span><br>
                    <span class="description">Скрытый объект невиден, но доступен.</span>
                </div>
            </div>
            <div class="item">
                <div class="col1">

                </div>
                <div class="col2 item-is_link">
                    <input class="attrib" type="checkbox" id="is_link" name="attrib[is_link]" data-name="is_link" value="1"/>
                    <label for="is_link">Ссылка</label>
                    <span class="error-message"></span><br>
                    <span class="description">Если объект является ссылкой, то свойства прототипа не наследуются.
                        Ссылка используется, чтобы сослаться на прототип и использовать его.
                        Например, автор статьи - это ссылка на пользователя.</span>
                </div>
            </div>
            <div class="item">
                <div class="col1">

                </div>
                <div class="col2 item-is_logic">
                    <input class="attrib" type="checkbox" id="is_logic" name="attrib[is_logic]" data-name="is_logic" value="1"/>
                    <label for="is_logic" title="Объект имеет свой php-класс или использует класс прототипа?">Свой класс</label>
                    <span class="error-message"></span><br>
                    <span class="description">Своим классом дополняется базовая логика объекта.<br>
                    Свой класс <span class="txt-tag class_name_self"></span> должен быть в директории объекта.<br>
                    Используется класс: <span class="txt-tag class_name"></span><br>
                    </span>
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
                    <span class="description">Также применяется в качестве версии объекта.</span>
                </div>
            </div>
            <div class="item submits">
                <div class="col1"></div>
                <div class="col2">
                    <a class="btn btn-success btn-disable submit" href="#">Сохранено</a>
                    <a class="btn hide reset">Восстановить</a>
                    <a class="btn cancel" href="#">Отмена</a>
                    <p class="submit-message"></p>
                </div>
            </div>
        </form>
    </div>
</div>