/**
 * Created by -tavis- on 12.07.2016.
 */
function dtChangeNextDateAction(pref, dateformat){
    $("#"+pref+"day").val($("#"+pref).val().substr(0,2));
    $("#"+pref+"month").val($("#"+pref).val().substr(3,2));
    $("#"+pref+"year").val($("#"+pref).val().substr(6,4));	    
    CalcP($("#"+pref+"year").val()+"-"+$("#"+pref+"month").val()+"-"+$("#"+pref+"day").val()+" "+($("#"+pref+"hour").val()<0?'00':$("#"+pref+"hour").val())+":"+($("#"+pref+"min").val()<0?'00':$("#"+pref+"min").val()),
        $("#exec_time_"+pref).val(), user_id, pref);
    $("#"+pref+"hour").removeClass('fielderrorSelBorder');
    $("#"+pref+"min").removeClass('fielderrorSelBorder');
}
function ShowFreeTime(prefix){
    var param = {
        action:'showFreeTime',
        date:($('#'+prefix+'year').val().length == 0?'':$('#'+prefix+'year').val()+'-'+$('#'+prefix+'month').val()+'-'+
            $('#'+prefix+'day').val()+' '+$('#'+prefix+'hour').val()+':'+$('#'+prefix+'min').val()),
        minutes:$('#exec_time_'+prefix).val(),
        id_usr: id_usr,
        prioritet:$('#priority').val()
    }
    $.ajax({
        url:'/dolibarr/htdocs/comm/action/card.php',
        cache:false,
        data:param,
        success:function(html){
            console.log(html);
            createNewForm('popupmenu','freetime')
            $('#freetime').html(html);
            console.log();
            $('#freetime').show();
            $('#freetime').offset({
                top: $('#ShowFreeTime').offset().top - 30,
                left: $('#ShowFreeTime').offset().left - 50
            });
            $('#freetime').attr('TitleProposed', 1);
//                $('#fulltext').focus();
        }
    })
    return 0;
}
$('select').change(function(e){
    console.log('select');
    if($.inArray(e.target.id, ['aphour','apmin','dateNextActionhour','dateNextActionmin'])>=0){
        var prefix;
        if(e.target.id.substr(e.target.id.length - 'hour'.length) == 'hour'){
            prefix = e.target.id.substr(0, e.target.id.length - 'hour'.length);
        }
        if(e.target.id.substr(e.target.id.length - 'min'.length) == 'min'){
            prefix = e.target.id.substr(0, e.target.id.length - 'min'.length);
        }
        var param = {
            action:'validateDataAction',
            date:($('#'+prefix+'year').val().length == 0?'':$('#'+prefix+'year').val()+'-'+$('#'+prefix+'month').val()+'-'+
                $('#'+prefix+'day').val()+' '+$('#'+prefix+'hour').val()+':'+$('#'+prefix+'min').val()),
            minutes:$('#exec_time_'+prefix).val(),
            id_usr:".$user->id.",
            prioritet:$('#priority').val(),
            into_parent_action:$('#into_parent_action').length>0?($('#into_parent_action').attr('checked')=='checked'?$('#parent_id').val():0):0
        }
        $.ajax({
            cache:false,
            data:param,
            success:function(result){
                $('#type').val('w');
                if(result == 0){
                    $('#'+e.target.id).addClass('fielderrorSelBorder');
                    $('#'+e.target.id).removeClass('validfieldSelBorder');
                }else{
                    $('#'+e.target.id).addClass('validfieldSelBorder');
                    $('#'+e.target.id).removeClass('fielderrorSelBorder');
                }
                if(!$('#'+prefix+'hour').hasClass('fielderrorSelBorder')&&
                    !$('#'+prefix+'min').hasClass('fielderrorSelBorder'))
                    $('#error').val(0);
                else
                    $('#error').val(1);
                console.log(result);
            }
        })
    }
});