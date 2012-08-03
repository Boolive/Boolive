$(document).ready(function(){
	var count = 0;
	var message = ['Я тут', 'Ха-ха :)', 'Не поймаешь','Никогда ))', 'Да да да', 'Упс :)'];
	var hello = $('#hello');
	hello.css('left', Math.round(($(document).width()-hello.outerWidth())/2)+'px');
	hello.css('top', Math.round(($(document).height()-hello.outerHeight())/2)+'px');
	hello.mouseenter(function(){
		if (count < 7){
			var l = Math.round(Math.random()*($(document).width()-$(this).outerWidth()));
			var t = Math.round(Math.random()*($(document).height()-$(this).outerHeight()));
			$(this).text(message[count]);
			$(this).css('left', l+'px');
			$(this).css('top', t+'px');
			count++;
		}else{
			if (count < 8){
				$(this).text('Оо, герой');
			}else
			if (count < 9){
				$(this).text('Жми');
			}else{
				count = -1;
			}
			count++;
		}
		return false;
	})
});