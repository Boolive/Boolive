$(document).ready(function(){
    var message = ['Я тут', 'Ха-ха :)', 'Не поймаешь','Никогда ))', 'Да да да', 'Упс :)'];
    $('.hello').each(function(){
        var count = 0;
        var hello = $(this);
        hello.css('left', Math.round(Math.random()*($(document).width()-hello.outerWidth()))+'px');
        hello.css('top', Math.round(Math.random()*($(document).height()-hello.outerHeight()))+'px');
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
});