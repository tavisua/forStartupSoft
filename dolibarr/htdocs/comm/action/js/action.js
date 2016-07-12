/**
 * Created by -tavis- on 12.07.2016.
 */
function dtChangeNextDateAction(pref, dateformat){
    $("#"+pref+"day").val($("#"+pref).val().substr(0,2));
    $("#"+pref+"month").val($("#"+pref).val().substr(3,2));
    $("#"+pref+"year").val($("#"+pref).val().substr(6,4));	    
    CalcP($("#"+pref+"year").val()+"-"+$("#"+pref+"month").val()+"-"+$("#"+pref+"day").val()+" "+($("#"+pref+"hour").val()<0?'00':$("#"+pref+"hour").val())+":"+($("#"+pref+"min").val()<0?'00':$("#"+pref+"min").val()),
        $("#exec_time_"+pref).val(), user_id, pref);
    console.log(pref);
}