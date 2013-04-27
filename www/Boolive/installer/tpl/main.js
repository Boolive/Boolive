$(document).ready(function(){
	var panel_first = $('#start'),
		panel_progress = $('#progress'),
		panel_form = $('#form'),
		panel_error = $('#error');

	$(window).resize(function() {
		$('.window').each(function (i) {
			$(this).css({
				position:'absolute',
				left: Math.max(0, ($(window).width() - $(this).outerWidth()) / 2),
				top: Math.max(0, ($(window).height() - $(this).outerHeight()) / 2)
			});
		});
	});
	// вызов функции:
	$(window).resize();
	panel_first.fadeIn(1500);
	/**
	 * Установка процента установки
	 * @param value Процент (число)
	 * @param message Сообщение
	 */
	var process_set = function(value, message){
		if (message == undefined) message = value+'%';
		panel_progress.find('.bar').css('width', value+'%');
		panel_progress.find('.message').text(message);
	};

	/**
	 * Отображение системной ошибки
	 * @param html
	 */
	var process_error = function(html){
		panel_error.append(html);
		$(window).resize();
		panel_error.show();
		$(window).resize();
	};

	/**
	 * Шаг установки. Проверка завершенности установки
	 * @param percent Текущий процент установки
	 * @param message Текущее сообщение при установке
	 */
	var process = function(percent, message, complete){
		process_set(percent, message);
		if (complete !== true){
			// Выполнение шага установки
			$.ajax({
				type: 'POST',
				url: '',
				data: {install_request:'next'},
				dataType: 'json',
				cache: false,
				success: function(result, textStatus, jqXHR){
					if (result.error){
						process_error(result.error);
					}else
					// Форма
					if (result.html){
						process_input(result.html);
					}else
					// Следующий шаг
					if (result.percent != undefined){
						process(result.percent, result.message, result.complete);
					}
				}
			});
		}else{
			// Установка завершена
			//alert('Установка завершена');
			window.location.reload(true);
		}
	};

	/**
	 * Отображение дополнительной формы при установки. HTML код формы присылает сервер.
	 * Управление отправкой формы. При успешной отправки переход к следующему шагу установки
	 * @param html
	 */
	var process_input = function(html){
		panel_form.html(html);
		//panel_progress.hide();
		$(window).resize();
		panel_form.fadeIn(500);


		panel_form.find('form').submit(function() {
    		// submit the form
    		$(this).ajaxSubmit({
				type: 'POST',
				url: '',
				data: {install_request:'submit'},
				dataType: 'json',
				cache: false,
				success: function(result, textStatus, jqXHR){
					if (result.error){
						process_error(result.error);
					}else
					// Форма с ошибками
					if (result.html){
						process_input(result.html);
					}else
					// Следующий шаг
					if (result.percent != undefined){
						panel_form.hide();
						panel_progress.show();
						process(result.percent, result.message);
					}
				}
			});
			return false;
		});
	};

	$('a#install').click(function(e){
		panel_first.hide();
		panel_progress.show();
		process(0);
		e.preventDefault();
	});
	
	$('body').ajaxError(function(e, jqxhr, settings, exception) {
		process_error(jqxhr.responseText);
	});
});
