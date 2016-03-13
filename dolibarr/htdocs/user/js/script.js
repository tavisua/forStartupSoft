$(document).ready(function(){
    return;
    /* Использование плагина только с двумя обязательными опциями */
    $("#ReInitPassword").zclip({
        path:'js/ZeroClipboard.swf',
        copy:$('#ReInitPassword').text()
    });

    /* Использование плагина с пустыми функциями обратного отклика */
    $(".no-feedback").zclip({
        path:'js/ZeroClipboard.swf',
        copy:$('.no-feedback').text(),
        beforeCopy:function(){},
        afterCopy:function(){}
    });

    /* Использование плагина с функциями обратного отклика */
    $(".feedback").zclip({
        path:'js/ZeroClipboard.swf',
        copy:$('.feedback').text(),
        beforeCopy:function(){
            $(this).css('background','yellow');
            $(this).css('color','orange');
        },
        afterCopy:function(){
            alert("Текст скопирован в буфер обмена!");
            alert("И мы изменим цвет фона и текста :)");
            $(this).css('background','green');
            $(this).css('color','white');
        }
    });

});