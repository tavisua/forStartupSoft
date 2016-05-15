/**
 * Created by tavis on 06.10.2015.
 */
function close_form(){
    location.href = '#close';
}
function setTime(link){
    $.ajax({
        url:link,
        cache: false,
        success: function(html){
            console.log('freetime', html);
            var hour = html.substr(0,2);
            var min = html.substr(3,2);
            $("#aphour [value='"+hour+"']").attr("selected", "selected");
            $("#apmin [value='"+min+"']").attr("selected", "selected");
            setP2(1);
        }
    })
}
//function EditAction(rowid){
//    //alert(rowid);
//    //console.log(location.search);
//    //return;
//    $('#action_id').val(rowid);
//    $('#action_item').val('edit');
//    $('#addaction').submit();
//}
function ConfirmExec(id){
    var src = $('img#confirm' + id).attr('src');
    var img_src = (src.substr(src.length-'uncheck.png'.length, 'uncheck.png'.length));
    if(img_src =='uncheck.png' &&  confirm('Установити відмітку про виконання роботи?')) {
        $('img#confirm' + id).off();
        for (var i = src.length; i > 0; i--) {
            if (src.substr(i, 1) == "/") {
                src = src.substr(0, i + 1) + 'Check.png';
                break;
            }
        }
        $('img#confirm' + id).attr('src', src);
        var link = "http://"+location.hostname+"/dolibarr/htdocs/comm/action/card.php?action=confirm_exec&rowid="+id;
        $.ajax({
            url: link,
            cache: false,
            success: function(html){
                console.log(html, 'confirm_exec');
            }
        })
    }
}
function previewNote(id){
    var link = "http://"+location.hostname+"/dolibarr/htdocs/comm/action/card.php?action=shownote&rowid="+id;
    $.ajax({
        url: link,
        cache: false,
        success: function(html){
            $('#phone_numbertitle').text('');
            $('#textsms').text(html);
            console.log(html);
            location.href = '#sendSMS';
            $('#sendSMSform').find('button').remove();
            $('#sendSMSform').show();
        }
    })
}
function DuplicateAction(id){
    if(confirm('Продублювати завдання?')){
        var input_html = '<input type="hidden" value="1" name="duplicate_action">';
        $('#addaction').html($('#addaction').html()+input_html);
        $('#action_id').val(id);
        $('#action_item').val('edit');
        //console.log(document.getElementById('addaction'));
        //return;
        $('#addaction').submit();
    }
}
function ConfirmReceived(id){
    var src = $('img#confirm' + id).attr('src');
    var img_src = (src.substr(src.length-'uncheck.png'.length, 'uncheck.png'.length));
    if(img_src =='uncheck.png' &&  confirm('Прийняти в роботу?')) {
        $('img#confirm' + id).off();
        for (var i = src.length; i > 0; i--) {
            if (src.substr(i, 1) == "/") {
                src = src.substr(0, i + 1) + 'Check.png';
                break;
            }
        }
        $('img#confirm' + id).attr('src', src);
        var link = "http://"+location.hostname+"/dolibarr/htdocs/comm/action/card.php?action=received_action&rowid="+id;
        $.ajax({
            url: link,
            cache: false,
            success: function(html){
                console.log('received action');
            }
        })
    }
}
function getParameterByName(name, url) {
    if (!url) url = window.location.href;
    url = url.toLowerCase(); // This is just to avoid case sensitiveness
    name = name.replace(/[\[\]]/g, "\\$&").toLowerCase();// This is just to avoid case sensitiveness for query parameter name
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}
function SaveResultProporition(contactid, lastID){
    if($('#cansaid').attr('checked') || $('#cansaid').attr('checked') === undefined && confirm('Вдалося озвучити пропозицію?')) {
        var date = new Date();
        var sDate = date.getDate() + '.' + date.getMonth() + '.' + date.getFullYear();
        var products = $('.need');
        var productsname = [];
        var needList = [];
        for (var i = 0; i < products.length; i++) {
            productsname.push($('td#productname' + products[i].id.substr(4)).html());
            needList.push($('input#' + products[i].id).val());
        }
        //console.log(location);
        //return;
        var param = {
            backtopage: location.pathname,
            action: 'addonlyresult',
            socid: getParameterByName('socid'),
            mainmenu: 'area',
            datep: date,
            actionid: lastID,
            said: $('td#titleProposition').html(),
            productsname: productsname,
            proposed_id: $('#Proposition').attr('fx_proposition'),
            need: needList,
            contactid: contactid
        }
        $('#redirect').attr('target', '_blank');
        $('#redirect').find('#action_id').val($('#actionid').val());
        $('#redirect').find('#onlyresult').remove();
        $('#redirect').find('#redirect_actioncode').remove();
        $('#redirect').find('#complete').remove();
        for (var i = 0; i < Object.keys(param).length; i++) {
            $('#redirect').append('<input type="hidden" name="' + Object.keys(param)[i] + '" value="' + param[Object.keys(param)[i]] + '">');
        }
        $('#redirect').find('input#soc_id').val(getParameterByName('socid'));
        $('#redirect').submit();

    }
    $('#Proposition').remove();
    //console.log(link);
}
function LoadProposition(){
    //console.log(getParameterByName('socid'));
    //$.ajax({
    //    dataType: 'json',
    //    url:'/dolibarr/htdocs/orders.php',
    //    data:item,
    //    cache:false,
    //    success:function(result){
    //    },
    //    type: 'post'
    //})
    var param = {
        action:'getProposition',
        socid:getParameterByName('socid')
    }
    $.ajax({
        dataType: 'json',
        url:'/dolibarr/htdocs/responsibility/sale/action.php',
        data: param,
        cache:false,
        success:function(html){
            var table = $('#ActionPanel').find('table');
            var tbody = table.find('tbody')
            //console.log(html);
            tbody.html(tbody.html()+html);
        }
    })
}
function ConfirmDelTask(id){
    if(confirm('Видалити завдання?')){
        $.ajax({
            url:'/dolibarr/htdocs/comm/action/card.php?action=del_task&id='+id,
            cache:false,
            success:function(){
                location.href = location.href;
            }
        })
    }
}
function createNewForm(basicform, newname){
    var popup;
        //if($('#popupmenu').css('display') == 'block') {
        if($('#'+name).length == 0) {
            popup = $('#'+basicform).clone();
            popup.attr('id', newname);
            popup.css('display','none');
            popup.appendTo('.fiche');
        }else{
            popup = $('#'+newname);
        }
        //}else
        //    popup = $('#popupmenu');
    return popup;
}
function showProposed(id,contactid){
    //console.log(id);
    var param = {
        id:id,
        action:'showProposition',
        contactid:contactid
    }
    $.ajax({
        url:'/dolibarr/htdocs/responsibility/sale/action.php',
        data: param,
        cache:false,
        success:function(html){
            //console.log(html);
            createNewForm('popupmenu','Proposition')
            $('#Proposition').addClass('setdate');
            $('#Proposition').css('width','auto');
            $('#Proposition').css('height','auto');
            $('#Proposition').empty().html(html);

            $('#Proposition').show();
            $('#Proposition').offset({top:$('#contactlist').offset().top-30,left:$('#contactlist').offset().left+$('#contactlist').width()/2});
            $('#Proposition').attr('TitleProposed', 1);
            $('#Proposition').attr('fx_proposition', id);
        }
    })
}

function GetExecDate(datetype){
var param = {
        typeaction: getParameterByName('mainmenu'),
        action:'get_execdate',
        datetype: datetype
    }
    $.ajax({
        url:'/dolibarr/htdocs/comm/action/index.php',
        data: param,
        cache:false,
        success:function(html){
            //console.log(html);
            if($('#getDate').length == 0) {
                createNewForm('popupmenu', 'getDate')
            }
            $('#getDate').empty().html(html);
            $('#getDate').width('auto');
            $('#getDate').offset({
                top: $('#ExecDateFilter').offset().top - 180,
                left: $('#ExecDateFilter').offset().left - 180
            });
            $('#getDate').show();
        }
    })
}
function CloseDatesMenu(){
    $('#getDate').remove();
}
function showTitleProposed(post_id, lineactive, contactid, td, socid){
    var param = {
        post_id: post_id,
        lineactive: lineactive,
        action:'showTitleProposition',
        contactid:contactid
    }

    $.ajax({
        url:'/dolibarr/htdocs/responsibility/sale/action.php',
        data: param,
        cache:false,
        success:function(html){
            if(td === undefined && socid !== undefined){
                $('#PropositionTitle').empty().html(html);
                $('#PropositionTitle').find('table').removeClass('setdate');
                $('#PropositionTitle').find('table').width(245);
                $('#PropositionTitle').find('a').remove();
                var tr = $('#PropositionTitle').find('thead').find('tr');
                tr[0].innerHTML = '<th class="middle_size" style="width: 100%" colspan="3">Актуальні пропозиції</th>';
            }else {
                $('#popupmenu').css('width', 250);
                //$('#popupmenu').css('height',250);
                $('#popupmenu').empty().html(html);

                $('#popupmenu').show();
                $('#popupmenu').offset({
                    top: $('#' + td.id).offset().top - 30,
                    left: $('#' + td.id).offset().left - 50
                });
                $('#popupmenu').attr('TitleProposed', 1);
            }
        }
    })
}
function setActionCode(){
    //console.log('setActionCode');
    switch ($("#mainmenu").val()){
        case "global_task":{
            $("#actioncode [value='AC_GLOBAL']").attr("selected", "selected");
        }break;
        case "current_task":{
            $("#actioncode [value='AC_CURRENT']").attr("selected", "selected");
        }break;
    }
}
function PrepareOrder(order_id, task_id){
    $.ajax({
        url:'/dolibarr/htdocs/orders.php?type_action=gettojob&order_id='+order_id,
        cache: false,
        success:function(res){
            var link = "http://"+location.hostname+'/dolibarr/htdocs/orders.php?idmenu=10426&mainmenu=orders&leftmenu=&type_action=prepare_order&order_id='+order_id+'&task_id='+task_id;
            location.href = link;
        }
    })

//        console.log(order_id, link);
}
function ShowUserTasks(id, respon_alias){
    var src = $('#img'+id).attr('src');
    if(src.substr(src.length-'1downarrow.png'.length)=='1downarrow.png') {
        $('#img' + id).attr('src', '/dolibarr/htdocs/theme/eldy/img/1uparrow.png');
        $('tr.'+id).show();
    }else {
        $('#img' + id).attr('src', '/dolibarr/htdocs/theme/eldy/img/1downarrow.png');
        $('tr.'+id).hide();
    }
    if($('tr.'+id).length == 0){
        var action = '';
        switch (respon_alias){
            case 'sale':{
                action = 'get_regionlist';
            }break;
        }
        $.ajax({
            url:'/dolibarr/htdocs/responsibility/dir_depatment/day_plan.php?action='+action+'&id_usr='+id,
            cache:false,
            success: function(result){
                var tr_item = document.getElementById('bnt'+id).parentNode.parentNode;
                //console.log(tr_item);
                tr_item.insertAdjacentHTML('afterend', result);
            }
        })
    }
}
function CalcP(date, minute, id_usr){
    //alert('test');
    if(minute === undefined || minute.length == 0)
        return;

    //if(date.substr(0,1)!="'")
    //    date = "'"+date+"'";
    $.ajax({
        url:'/dolibarr/htdocs/comm/action/card.php?date='+date+'&minute='+minute+'&id_usr='+id_usr+'&action=get_freetime',
        cache:false,
        success:function(result){
            var time = result.substr(10,6);
            //console.log(result);
            //return;
            if($('#ap').val()!=result.substr(8,2)+'.'+result.substr(5,2)+'.'+result.substr(0,4)) {
                $('#ap').val(result.substr(8,2)+'.'+result.substr(5,2)+'.'+result.substr(0,4));
                $('#p2').val(result.substr(8,2)+'.'+result.substr(5,2)+'.'+result.substr(0,4));
                $('#apday').val(result.substr(8, 2));
                $('#apmonth').val(result.substr(5, 2));
                $('#apyear').val(result.substr(0, 4));
                $('#p2day').val($('#apday').val());
                $('#p2month').val($('#apmonth').val());
                $('#p2year').val($('#apyear').val());
            }



            $('#aphour [value='+time.substr(1,2)+']').attr("selected","selected");
            $('#apmin  [value='+time.substr(4,2)+']').attr("selected","selected");
            console.log(time.substr(4,2).trim());
            CalcP2();

        }
    })
}
function CalcP2(){
    var hour = parseInt(document.getElementById("aphour").value)+Math.floor($("#exec_time").val()/60);

    //document.getElementById("p2hour").value = hour<10?("0"+hour):hour;
    var p2min = 0;
    if(parseInt($("#exec_time").val())%60){
        p2min = parseInt(document.getElementById("apmin").value)+parseInt($("#exec_time").val());
        hour = parseInt(document.getElementById("aphour").value)+Math.floor(p2min/60);
    }else{
       p2min = parseInt(document.getElementById("apmin").value);
       hour = parseInt($("#exec_time").val())+parseInt(document.getElementById("aphour").value);
    }

    document.getElementById("p2hour").value = hour<10?("0"+hour):hour;
    var min="";
    if(p2min%60<10)
        min = "0"+(p2min%60).toString();
    else
        min = (p2min%60).toString();


    //var sHour = hour<10?("0"+hour.toString()):(hour.toString());
    //document.getElementById("p2hour").value = sHour;
    document.getElementById("p2min").value = min;
}
function AddOrder(){
     $("#actionbuttons").attr('action', '/dolibarr/htdocs/orders.php?idmenu=10426&mainmenu=orders&leftmenu=');
    console.log('test');
}
function saveorders(typicalqueries){
        //console.log(typicalqueries, $.cookie('products_id'), $.cookie('answerId'));
        //return;
        console.log('typicalqueries '+typicalqueries);
        if(typicalqueries === undefined){

            preparedorders();
//            console.log();
        }else {
            var Query = '';
            if($('#popup_table').attr('order_id') === undefined)
                Query = 'Зберегти активну заявку?';
            else
                Query = 'Зберегти заявку?';
            if (confirm(Query)) {
                $('#questions_form').submit();
            }
        }
}
function showHideActionPanel(){
    console.log($('#bookmarkActionPanel').css('right') == '-70px');
    var show = $('#bookmarkActionPanel').css('right') == '-70px';
    if(show) {
        $('#bookmarkActionPanel').css('right', 185);
        $('#ActionPanel').css('right', 0);
    }else{
        $('#bookmarkActionPanel').css('right', -70);
        $('#ActionPanel').css('right', -255);
    }

}
function delete_answer(answer_id){
    if(confirm('Видалити відповідь?')){
        var answerId = $.cookie('answerId').split(',');
        var index = $.inArray(answer_id.toString(), answerId);
        console.log('length', answerId.length);
        answerId.splice(index, 1);
        $.cookie('a'+answer_id, null);
        console.log('length', answerId.length);
        $.cookie('answerId', answerId.toString());
        $('#q'+answer_id).parent().remove();
        $('#a'+answer_id).remove();
        console.log($.cookie('answerId'), answerId.toString());
    }
}
    $(window).click(function(){
        $('#timer').text('0сек');
        $('#backgroundtimer').css('background', 'url(http://'+location.host+'/dolibarr/htdocs/theme/eldy/img/green_timer.png)');
        $('#timer').css('color', '#ffffff');
    })
    $(window).keydown(function(){
        $('#timer').text('0сек');
        $('#backgroundtimer').css('background', 'url(http://'+location.host+'/dolibarr/htdocs/theme/eldy/img/green_timer.png)');
        $('#timer').css('color', '#ffffff');
    })
    function Timer(){
        var sec = $('#timer').text().substr(0, $('#timer').text().length-1);

        sec = parseInt(sec)+1;
        $('#timer').text(sec + 'с');
        if(sec<10){
            $('#backgroundtimer').css('background', 'url(http://'+location.host+'/dolibarr/htdocs/theme/eldy/img/green_timer.png)');
            $('#timer').css('color', '#ffffff');
        }else if(sec>=10&&sec<15){
            $('#backgroundtimer').css('background', 'url(http://'+location.host+'/dolibarr/htdocs/theme/eldy/img/yelow_timer.png)');
            $('#timer').css('color', '#000000');
        }else {
            $('#backgroundtimer').css('background', 'url(http://' + location.host + '/dolibarr/htdocs/theme/eldy/img/red_timer.png)');
            $('#timer').css('color', '#ffffff');
        }
//        if(sec<=15) {
//
////            $('#timer').css('width', sec*100/15+'%');
////            if(sec<10){
////                $('#timer').css('background-color', '#008000');
////            }else if(sec>=10&&sec<=15){
////                $('#timer').css('background-color', '#fbef7e');
////            }
//        }else {
////            $('#timer').css('background-color', 'red');
////            $('#timer').text(sec+'сек бездіяльності');
////            return false;
//        }
        setTimeout(Timer, 1000);
    }
function preparedorders(){
    //alert($('#questions_form').find('input#order_id').val());
    if($('#prepared_order').val() == 1){
        location.href ='/dolibarr/htdocs/orders.php?idmenu=10426&mainmenu=orders&leftmenu=&type_action=prepare_order&order_id='+$('#order_id').val();
        return;
    }
    if($.cookie('products_id') != null){
        var products_id = $.cookie('products_id').split(',');
        var JSON = '{';
        for(var i = 0; i<products_id.length; i++) {
            if($.cookie('p'+products_id[i])!=null) {
                if (JSON.length > 1)
                    JSON += ',';
                JSON += '"' + products_id[i] + '":' + $.cookie('p'+products_id[i]);
            }
        }
        JSON+='}';
        $('input#products').val(JSON);
        //console.log(JSON, $('input#products').val());
    }
    console.log($.cookie('answerId'));
    if($.cookie('answerId') != null){
        var answerId = $.cookie('answerId').split(',');
        var JSON = '(';
        for(var i = 0; i<answerId.length; i++) {
            if($.cookie('a'+answerId[i])!=null) {
                if (JSON.length > 1)
                    JSON += ',';
                JSON += '"' + answerId[i] + '"=>"' + $.cookie('a'+answerId[i])+'"';
            }
        }
        JSON+=')';
        $('#answer').val(JSON);
        console.log(JSON, $('input#answer_id').val());
    }

    if($('#popup_table').attr('order_id') !== undefined) {
        //console.log('order_id param', $('#popup_table').attr('order_id'));
        //return;
        var order_id = $('#questions_form').find('input#order_id');
        order_id.val($('#popup_table').attr('order_id'))
        var products = $('#questions_form').find('input#products');
        products.val($.cookie('products'));
//                console.log(order_id.val());
    }
    $('.popupmenu').hide();
    $('#typicalqueries').css('position', "absolute");
    $('#typicalqueries').css("top", '150');
    $('#typicalqueries').css("z-index", '1500000');
    $('#typicalqueries').css("left", (($(window).width() - 458) / 2));
//            location.href = '#login_phone';
    var link = 'http://'+location.hostname+'/dolibarr/htdocs/orders.php?idmenu=10426&mainmenu=orders&leftmenu=&type_action=get_typical_question';
    //console.log(link);
    //return;
    $.ajax({
        url: link,
        cashe: false,
        success: function(html){
            $('#questions').html(html);
        }
    })
    $('#typicalqueries').show();
}
function clearOrderCookie(){

    if($.cookie('products_id') != null){
        var products_id = $.cookie('products_id').split(',');
        for(var i = 0; i<products_id.length; i++){
            $.cookie('p'+products_id[i], null);
        }
        $.cookie('products_id', null);
    }
    if($.cookie('answerId') != null){
        var answerId = $.cookie('answerId').split(',');
        for(var i = 0; i<answerId.length; i++){
            $.cookie('a'+answerId[i], null);
        }
        $.cookie('answerId', null);
    }
}
function ReinitPassword(){
    $.ajax({
        url:'/dolibarr/htdocs/user/card.php?action=getpass',
        cache:false,
        success: function(pass){
            $('#password').val(pass);
            alert('Новий пароль згенеровано. Для збереження змін натисніть "Зберегти"');
        }
    })
}
function sendSMS(number, text, confirmSend){
    console.log(number, number);
    if((number === undefined  && text === undefined)||(number.length == 0  && text.length == 0)) {
        number = $("#phone_number").val();
        text = $("#textsms").val();
    }
    //console.log(number, text);
    //return;
    var send = 0;
    if(confirmSend == true){
        if(confirm('Відправити СМС повідомлення?'))
            send = 1;
    }else
        send = 1;
    if(send == 1) {
        var blob = new Blob(['{"phone":"' + number + '","text":"' + text + '"}'], {type: "text/plain;charset=utf-8"});
        saveAs(blob, "sms.json");
        console.log('savefile');
        close_registerform();
    }
}
function Call(number){
    var blob = new Blob(['{"call":"'+number+'"}'], {type: "text/plain;charset=utf-8"});
    saveAs(blob, "call.json");
    //var link = 'http://'+location.hostname+'/dolibarr/htdocs/autocall/index.php?action=CallPhone&phonenumber='+number;
    ////console.log(link);
    ////    return;
    //$.ajax({
    //    url:link,
    //    cache: false,
    //    success: function(html){
    //        console.log(html);
    //        console.log('success Call')
    //    }
    //})
    console.log('savefile');
}
function GotoRequiredPage(pagename){
    //if(pagename.length == 0)
        return;
    //$.cookie('required_pages');
    var pages = [];
    $.ajax({
        url: '/dolibarr/htdocs/index.php?action=requeredpages',
        cache:false,
        success: function(html){
            pages = html.split(',');
        }
    })
    //alert($.cookie('required_pages'));


    if($.cookie('required_pages') == null) {
        console.log("Добавить");
        var insert = false;
        if(pagename == 'home')
            insert = true;
        if(insert)
        pages.push('home');
        if(pagename == 'calculator')
            insert = true;
        if(insert)
            pages.push('calculator');
        if(pagename == 'plan_of_days')
            insert = true;
        if(insert)
            pages.push('plan_of_days');
        if(pagename == 'hourly_plan')
            insert = true;
        if(insert)
            pages.push('hourly_plan');
        if(pagename == 'global_task')
            insert = true;
        if(insert)
            pages.push('global_task');
        if(pagename == 'current_task')
            insert = true;
        if(insert)
            pages.push('current_task');
        $.cookie('required_pages', pages);
    }
    var pages = $.cookie('required_pages').split(',');
    console.log($.cookie('required_pages'));
    var firstpage = pages[0];
    pages.splice(0,1);
    $.cookie('required_pages', pages);
    if(firstpage != pagename){
        console.log(firstpage);
        switch(firstpage){
            case 'home':{
                location.href = 'http://'+location.hostname+'/index.php?mainmenu=home&leftmenu=&idmenu=5216&mainmenu=home&leftmenu=&redirect=1';
            }break;
            case 'calculator':{
                location.href = 'http://'+location.hostname+'/dolibarr/htdocs/calculator/index.php?idmenu=10418&mainmenu=calculator&leftmenu=&redirect=1';
            }break;
            case 'plan_of_days':{
                location.href = 'http://'+location.hostname+'/dolibarr/htdocs/day_plan.php?idmenu=10419&mainmenu=plan_of_days&leftmenu=&redirect=1';
            }break;
            case 'hourly_plan':{
                location.href = 'http://'+location.hostname+'/dolibarr/htdocs/hourly_plan.php?idmenu=10420&mainmenu=hourly_plan&leftmenu=&redirect=1'
            }break;
            case 'global_task':{
                location.href = 'http://'+location.hostname+'/dolibarr/htdocs/global_plan.php?idmenu=10421&mainmenu=global_task&leftmenu=&redirect=1';
            }break;
            case 'current_task':{
                location.href = 'http://'+location.hostname+'/dolibarr/htdocs/current_plan.php?idmenu=10423&mainmenu=current_task&leftmenu=&redirect=1';
            }break;
        }
    }

    console.log($.cookie('required_pages'));
}
function getCookie(name) {
  var matches = document.cookie.match(new RegExp(
    "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
  ));
  return matches ? decodeURIComponent(matches[1]) : undefined;
}
function save_item(tablename, paramfield, sendtable){
    if(confirm('Зберегти данні?')){
        var sID = document.getElementById('edit_rowid').value;
        var id_usr = document.getElementById('user_id').value;
        var editor = document.getElementsByClassName('popup');
        var input_field = new Array();
        for(var i = 0; i<editor.length; i++) {
            if(editor[i].id.length==0) {
                var field = editor[i].getElementsByTagName('input');
                for(var p = 0; p<field.length; p++)
                 input_field[input_field.length]= field[p];
                field = editor[i].getElementsByTagName('textarea');
                for(var p = 0; p<field.length; p++)
                 input_field[input_field.length]= field[p];
                var edit_form = editor[i];
            }
        }
        var fields='', values ='';
        //console.log(input_field);
        //return;
        for(var i=0;i<input_field.length;i++){
            if(input_field[i].type != 'hidden' && input_field[i].id.substr(0, 5) == 'edit_'){
                var fieldname = input_field[i].id.substring(5);
                //console.log(fieldname, input_field[i].id);
                if(sID != 0) {
                    var send_field = document.getElementById(sID + fieldname);
                    send_field.innerHTML = input_field[i].value;
                }
                var value = input_field[i].value;
                value = value.replace(/\&/gi, "@@");
                if(fields != '') {
                    fields = fields + ',' + fieldname;
                    values = values + ','+escapeHtml(value).trim();
                }else {
                    fields = fieldname;
                    values = escapeHtml(value).trim();
                }
            }
        }
        //var text_field = editor[0].getElementsByTagName('textarea');
        //for(var i=0; i<text_field.length; i++){
        //    var fieldname = text_field[i].id.substring(5);
        //    if(sID != 0) {
        //        var send_field = document.getElementById(sID + fieldname);
        //        if(send_field.getElementsByTagName('a').length>0){
        //            send_field.innerHTML = '<a id = "'+send_field.getElementsByTagName('a')[0].id+'" href="'+send_field.getElementsByTagName('a')[0].href+'">' +
        //            '<img border="0" src="/dolibarr/htdocs/theme/eldy/img/object_user.png" alt="" title="Show user">'+text_field[i].value+'</a>';
        //        }else
        //            send_field.innerHTML = text_field[i].value;
        //    }
        //    var value = text_field[i].value;//.replace(/\./gi, "&&");
        //    //value = value.replace(/\&/gi, "@@");
        //    if(fields != '') {
        //        fields = fields + ',' + fieldname;
        //        values = values + ','+escapeHtml(value).trim();
        //    }else {
        //        fields = fieldname;
        //        values = escapeHtml(value).trim();
        //    }
        //}

        var img_field = edit_form.getElementsByTagName('img');
        for(var i=0; i<img_field.length; i++){
            var fieldname = img_field[i].id.substring(5);
            if(sID != 0) {
                var send_field = document.getElementById('img' + sID + fieldname);
                send_field.src = img_field[i].src;
            }
            var value ='';
            if(img_field[i].src == 'http://'+location.hostname+'/dolibarr/htdocs/theme/eldy/img/switch_on.png')
                value = '1';
            else
                value = '0';
            if(fields != '') {
                fields += (',' + fieldname);
                values += (',' + escapeHtml(value).trim());
            }else {
                fields = fieldname;
                values = escapeHtml(value).trim();
            }
        }
        var select_field = edit_form.getElementsByTagName('select');

        for(var s = 0; s<select_field.length; s++) {
            var detail_id = 'detail_' + select_field[s].id.substr(5);
            var detail_field = document.getElementById(detail_id);
            if(fields != '') {
                fields = fields + ',' + detail_field.value;
                values = values + ','+escapeHtml(select_field[s].value).trim();
            }else {
                fields = detail_field.value;
                values = escapeHtml(select_field[s].value).trim();
            }
        }
        //console.log(values);
        //return;
        if(sID == 0 && alrady_exist(fields, values)) {
            alert('Такая запись уже существует');
            location.href = '#close';
            return;
        }

        var link = "tablename="+tablename+"&columns='"+fields+"'&values='"+values+"'&id_usr="+id_usr+"&save=1";

        //console.log(link);
        //return;
        if(paramfield != ''){//Зберігаю додаткові параметри
            //console.log($('#'+paramfield).find('input').length);
            //return;

            var fields='', values ='';
            var input_field = $('#'+paramfield).find('input');
            for(var i=0;i<input_field.length;i++){
                if(input_field[i].className=='param') {
                    //console.log(input_field[i].value);
                    var value = input_field[i].value;
                    value = value.replace(/\&/gi, "@@");
                    if (fields != '') {
                        fields = fields + ',' + input_field[i].id;
                        values = values + ',' + value.trim();
                    } else {
                        fields = input_field[i].id;
                        values = value.trim();
                    }
                }
            }
            //paramfield, sendtable
            link += "&param="+fields+"&pvalues="+values+"&paramtable="+sendtable+"&paramfield="+paramfield;
            //console.log(link);
            //return;
        }

        if(sID != 0)
            link += "&rowid="+sID;
        else {
            console.log('Добавление новой записи '+sID);
            //add_item();
        }
        //console.log(link);
        //return;
        save_data(link);
        location.href = '#close';
        //location.href='';
    }
}

function AddResultAction(){
    //alert('test');
    var link = '/dolibarr/htdocs/comm/action/result_action.php';
    var inputaction = $("#actionbuttons").find('input');
    for(var i = 0; i<inputaction.length; i++) {
        if(inputaction[i].name == 'action'){
            inputaction[i].value = 'addonlyresult';
        }
    }
    $("#actionbuttons").attr('method', 'get');
    $("#actionbuttons").attr('action', link);
    //console.log(link);
    //
    //location.href=link;
}
function EditAction(rowid, actioncode){
    console.log(rowid, actioncode);
    var search = location.search.substr(1);
    search.split('&').forEach(function(item){
        item = item.split('=');
        if(item[0]=='mainmenu'){
            $('#mainmenu_action').val(item[1]);
        }else if(item[0]=='socid'){
            $('#soc_id').val(item[1]);
        }
    })
    //console.log($('#redirect').length);
    //return;
    if(!$.isNumeric(rowid)) {
        $('#onlyresult').val(1);
        $('#action_id').val(rowid.substr(1));
    }else
        $('#action_id').val(rowid);
    $('#edit_action').val('edit');
    if($('#redirect').length>0) {
        $('#redirect_actioncode').val(actioncode);
        $('#redirect').submit();
    }
    else if($('#addaction').length>0)
        $('#addaction').submit();
}
function ShowProducts(){//Відображення кількості замовлених товарів
    if($.cookie('products_id') != null) {
        var product = $.cookie('products_id').split(',');
        for (var i = 0; i < product.length; i++) {
            $('input#Col' + product[i]).val($.cookie('p' + product[i]));
//                console.log(product[i], $.cookie('p' + product[i]))
        }
    }
}
function OpenFolder(id_cat, showeditfield){
    console.log(showeditfield);
    var img = $('#cat'+id_cat).find('img');
//        console.log(img.attr('src') == '/dolibarr/htdocs/theme/eldy/img/object_folded.png');
    if(img.attr('src') == '/dolibarr/htdocs/theme/eldy/img/object_folded.png') {
        img.attr('src', '/dolibarr/htdocs/theme/eldy/img/object_deployed.png');
        $('.parent' + id_cat).show();
    }else{
        img.attr('src', '/dolibarr/htdocs/theme/eldy/img/object_folded.png');
        $('.parent' + id_cat).hide();
    }

    $.ajax({
        url: '/dolibarr/htdocs/orders.php?idmenu=10426&mainmenu=orders&leftmenu=&type_action=showproducts&id_cat=' + id_cat,
        cache: false,
        success: function (html) {
            $('#products').empty().html(html);
            if(showeditfield == 1) {
                for (var i = 0; i < $('tbody#products').find('tr').length; i++) {
                    var tr = $('tbody#products').find('tr')[i];
                    tr.innerHTML += '<td id = "td' + tr.id.substr(2) + '" style="width:50px; text-align: center"><input id="Col' + tr.id.substr(2) + '"  onblur="setProductCount(' + "'Col" + tr.id.substr(2) + "'" + ');" class="product_count" value="" type="text" size="4"></td>';
                }
            }
            ShowProducts();
        }
    })

}
    function ShowTask(object){
        var id = object.attr('id');
        //console.log(id);
        var td = $('#'+id);
        if(id.substr(0, 'current'.length)=='current'||
                id.substr(0, 'global'.length)=='global'||
                id.substr(0, 'total'.length)=='total' ||
                id.substr(0, 'outstand'.length)=='outstand'

        ){}else
            return;
        $.ajax({
            url:'/dolibarr/htdocs/day_plan.php?action=getdateaction&type_action='+id,
            cache:false,
            success:function(html){
                if(td.text() == '1'){
                    location.href = 'http://'+location.hostname+'/dolibarr/htdocs/hourly_plan.php?idmenu=10420&mainmenu=hourly_plan&leftmenu=&date='+html;
                }else {
                    $("#popupmenu").find('table').addClass('setdate');
                    console.log(document.getElementById('popupmenu'));
                    var tbody = $('table.setdate').find('tbody');
                    tbody[0].innerHTML = html;
                    $("#popupmenu").attr('type_action', id);
                    $("#popupmenu").show();
                    $("#popupmenu").offset({
                        top: td.offset().top - 50,
                        left: td.offset().left
                    });
                }
            }
        })


    }
function ClosePopupMenu(elem){
    if(elem === undefined)
        $("#popupmenu").hide();
    else
        $('#'+elem.parent().attr('id')).hide();
}
function GetPerformers(){
    var html = '';
    if(!strpos(location.search, 'performer')) {
        var performer = $('.performer');
        var array = {};
        for (var i = 0; i < performer.length; i++) {
            array[$('#' + performer[i].id).attr('id_usr')] = $('#' + performer[i].id).text();
        }
        var keys = new Array();
        var values = new Array();
        $.each(array, function (index, value) {
            keys.push(index);
            values.push(value);
        })
        sort = false;
        for (var i = 0; i < values.length - 1; i++) {
            if (values[i] > values[i + 1]) {
                //console.log(i, values);
                $val1 = values[i];
                $val2 = values[i + 1];
                $key1 = keys[i];
                $key2 = keys[i + 1];
                values[i] = $val2;
                values[i + 1] = $val1;
                keys[i] = $key2;
                keys[i + 1] = $key1;
                //console.log(i, values);
                i --;
                sort = true;
            }
            if(i == values.length - 1 && sort == true){
                i = -1;
                sort = false;
            }
        }


        html = '<tr>' +
            '<td class="middle_size" onclick="setPerformetFilter(0);" style="cursor: pointer; width: 250px"><b>Всі завдання</b></td>'+
            '</tr>';
        $.each(keys, function (index, value) {
            html += '<tr>' +
                '<td class="middle_size" onclick="setPerformetFilter(' + value + ');" style="cursor: pointer; width: 250px">' + array[value] + '</td>' +
                '</tr>'
        })
        $.cookie('performers', html);
    }else{
        html =  $.cookie('performers');
    }
    $('#popupmenu').find('table').find('thead').find('th').html('Виберіть виконавця')
    //$('#popupmenu').find('table').find('thead').empty().html();
    $('#popupmenu').find('table').find('tbody').empty();
    $('#popupmenu').find('table').find('tbody').html(html);
    $('#popupmenu').show();
    $('#popupmenu').offset({top:$('#PerformerFilter').offset().top-50,left:$('#PerformerFilter').offset().left-50});
}
function setPerformetFilter(id_usr){
    if(id_usr!=0)
        location.href='?mainmenu='+GetMainMenu()+'&performer='+id_usr;
    else
        location.href='?mainmenu='+GetMainMenu();
}
function GetMainMenu(){
    var mainmenu = '';
    if(strpos(location.pathname,'current_plan')!=false){
        mainmenu='current_task';
    }else if(strpos(location.pathname,'global_plan')!=false){
        mainmenu='global_task';
    }else{
        mainmenu='area';
    }
    return mainmenu;
}
function GetUserPlan(link){
    $.ajax({
        url:link,
        cache:false,
        success:function(html){
            console.log(html);
            var obj = $.parseJSON(html);
            $('#my_fact').html(obj.fakt_today);
            $('#my_plan').html(obj.future_today);
            $('#my_outstanding').html(obj.outstanding);
        }
    })
}
function TodayActionCustomerFilter(){
    location.href='?filter=today';
}
function addAssignedUsers(){
    var select = $("#assegnedusers").val();
//        	{"4":{"id":"4","transparency":"on","mandatory":1}
//    console.log(select);
//    return;

    var assignedJSON="";

    for(var i=0; i<select.length; i++){
        if($.isNumeric(select[i])){
            if (select[i] > 0)
                    assignedJSON += ',"' + select[i] + '":{"id":"' + select[i] + '","transparency":"on","mandatory":1}';
        }else {
           var rowidList = select[i].split(',');
            console.log(rowidList);
            for (var d = 0; d < rowidList.length; d++) {
                assignedJSON += ',"' + rowidList[d] + '":{"id":"' + rowidList[d] + '","transparency":"on","mandatory":1}';
            }
        }
    }
    assignedJSON+="}";
    $('#addAssigned').find('#assignedJSON').val(assignedJSON);
    //console.log($('#assignedJSON').val());
    //return;
    //location.href='?action=add&assignedJSON='+assignedJSON+'&mainmenu='+$('input#mainmenu').val()+'&backtopage='+$('#backtopage').val();
    //$('#addAssigned').find('#backpage').val($('#backtopage').val());

    //$('#addAssigned').find('#mm').val($('input#mainmenu').val());
    //$('#addAssigned').find('#type_code').val($('select#actioncode').val());
    //$('#addAssigned').find('#formaction').val('addassignedtouser');
    var inputfield = $('#formaction').find('input');

    for(var i = 0; i<inputfield.length; i++) {
        //if(inputfield[i].attr('type'))
        if(inputfield[i].type == 'hidden') {
            $('#addAssigned').html($('#addAssigned').html()+inputfield[i].outerHTML);
        }
        else{
            var elem = inputfield[i];
            elem.style.display = 'none';
             $('#addAssigned').html($('#addAssigned').html()+elem.outerHTML);
            //console.log(elem.outerHTML);
        }
    }
    var selectfield = $('#formaction').find('select');
    for(var i = 0; i<selectfield.length; i++) {
        //if(inputfield[i].attr('type'))
        if(selectfield[i].type == 'hidden') {
            $('#addAssigned').html($('#addAssigned').html()+selectfield[i].outerHTML);
        }
        else{
            var elem = selectfield[i];
            elem.style.display = 'none';
             $('#addAssigned').html($('#addAssigned').html()+elem.outerHTML);
            //console.log(elem.outerHTML);
        }
    }
    var textfield = $('#formaction').find('textarea');
    for(var i = 0; i<textfield.length; i++) {
        //if(inputfield[i].attr('type'))
        if(textfield[i].type == 'hidden') {
            $('#addAssigned').html($('#addAssigned').html()+textfield[i].outerHTML);
        }
        else{
            var elem = textfield[i];
            elem.style.display = 'none';
             $('#addAssigned').html($('#addAssigned').html()+elem.outerHTML);
            //console.log(elem.outerHTML);
        }
    }
    $('#addAssigned').html($('#addAssigned').html()+'<input type="hidden" id = "addassignedtouser" name="addassignedtouser" value="Зберегти">');
    //console.log($('#addAssigned').html());
    //return;
    $('#addAssigned').submit();

}
function setHightTable(table){
    var tbody = document.getElementById(table);
    if(tbody!=null){
        tbody.style.height = window.innerHeight*.78;
        //console.log(document.getElementsByClassName('tabPage').length);
        if(document.getElementsByClassName('tabPage').length>0) {
            tbody.style.height = window.innerHeight - 270;
            //console.log(tbody.style.width);
            tbody.style.width = Number(tbody.style.width.substr(0, tbody.style.width.length-2))+20;
            //console.log('.tabPage');
        }
    }
    var menu = $('.vmenu')
    if(menu != null) {
        if(document.getElementsByClassName('tabPage').length>0) {
            menu.offset({top: 155, left: 30});
        }else
            menu.offset({top: 110, left: 30});
    }


    //if(('.tabPage')){
    //    console.log('tabPage');
    //
    //    //$('.page_vmenu').offset({top: 20, left: 30});
    //}

    var tabPage = $('.tabPage');
}
function escapeHtml(text) {
    return text
        .replace(/&/g, "@@")
        .replace(/'/g, "$$$39;")
        .replace(/,/g, "__");
}
function alrady_exist(fields, values){//Проверка на идентичные записи
    return false;
    var $table = $('#reference');
    var fieldslist = fields.split(',');
    var valuelist = values.split(',');
    //console.log(valuelist);

    var i = 0;
    var $td = null;
    //console.log(valuelist.length);
    var exits = false;
    for(i; i<valuelist.length; i++) {
        var value = valuelist[i].trim();
        var tdlist = $table.find('td:contains("'+value+'")');
        //console.log(value);
        //return false;
        for(var row=0; row<tdlist.length;row++) {
            //return true;
            var rowid = tdlist[row].id.substr(0, fieldslist[i].length-1)
            var tr = document.getElementById('tr'+rowid);
            var Tdlist = tr.getElementsByTagName('td');
            //console.log(Tdlist.length);
            for(var index = 0; index<Tdlist.length; index++){
                var type = '';
                var fieldname = Tdlist[index].id.substr(rowid.length);

                if(Tdlist[index].getElementsByTagName('select').length>0){
                    exits =  $('#'+Tdlist[index].getElementsByTagName('select')[0].id).val()!= $('#edit_'+fieldname.substr(2)).val();
                    console.log('selector '+($('#'+Tdlist[index].getElementsByTagName('select')[0].id).val()!= $('#edit_'+fieldname.substr(2)).val()));
                }else if(Tdlist[index].getElementsByTagName('img').length>0){

                }else{
                    exits =  console.log(Tdlist[index].innerHTML.trim()!=$('#edit_'+fieldname).val().trim());
                    console.log('text '+(Tdlist[index].innerHTML.trim()!=$('#edit_'+fieldname).val().trim()));
                }
                if(exits) {
                    console.log('Есть различия');
                    return false;
                }
            }

            //for(var f_index=0;f_index<valuelist.length; f_index++)
            //{
            //    //console.log($('td#' + rowid + fieldslist[f_index]).html() != value);
            //    if ($('td#' + rowid + fieldslist[f_index]).html() !=  valuelist[f_index].trim()) {
            //        console.log('Разбежность td#' + rowid + fieldslist[f_index]+' '+$('td#' + rowid + fieldslist[f_index]).html()+' edit '+valuelist[f_index].trim());
            //        //return false;
            //    }
            //}
        }
    }
    return true;
}
function add_item(){

    var title = document.getElementById('reference_title');
    var table = document.getElementById('edit_table');
    var td_list = table.getElementsByTagName('td');
    //console.log('***', td_list);
    //return;
    var th = title.getElementsByTagName('th');
    var sRow='';
    for(var i = 0; i<td_list.length; i++){
        var colindex = 0;
        var td = td_list[i];
        //console.log();
        if(td.className != "param") {
            if (td.getElementsByTagName('select').length > 0) {
                var elems = td.getElementsByTagName('select');
                for (var el_index = 0; el_index < elems.length; el_index++) {
                    if (elems[el_index].type != 'hidden') {
                        var fieldname = elems[el_index].id.substr(5);
                        var optionlist = elems[el_index].getElementsByTagName('option');
                        for (var index = 0; index < optionlist.length; index++) {
                            if (optionlist[index].value == elems[el_index].value) {
                                var selectHTML = elems[el_index].outerHTML;
                                selectHTML = selectHTML.replace('edit_' + fieldname, 'select' + document.getElementById('edit_rowid').value + fieldname);

                                sRow += '<td  id="' + document.getElementById('edit_rowid').value + fieldname + '" style="width: ' + th[colindex++].width + '">' + selectHTML + '</td>';
                                break;
                            }
                        }
                        //var text = $('option#'+elems[el_index].value).html();
                        //console.log(text);

                    }

                    //return;
                }
            } else if (td.getElementsByTagName('textarea').length > 0 || td.getElementsByTagName('a').length > 0) {
                var elems = td.getElementsByTagName('textarea');
                var fieldname = elems[0].id.substr(5);

                sRow += '<td id="' + document.getElementById('edit_rowid').value + fieldname + '" style="width: ' + th[colindex++].width + '">' + elems[0].value.trim() + '</td>'
            } else if (td.getElementsByTagName('img').length > 0) {
                var img = td.getElementsByTagName('img');
                var fieldname = img[0].id.substr(5);
                var html = td.innerHTML.replace(/change_switch\(0/gi, "change_switch(" + document.getElementById('edit_rowid').value);
                html = html.replace(/img id="edit_active"/gi, "img id='img" + document.getElementById('edit_rowid').value + fieldname + "'")
                sRow += '<td id="' + document.getElementById('edit_rowid').value + fieldname + '" style="width: ' + th[colindex++].width + '">' + html + '</td>';
            } else if (td.getElementsByTagName('input').length > 0) {
                var elems = td.getElementsByTagName('input');
                for (var el_index = 0; el_index < elems.length; el_index++) {
                    if (elems[el_index].type != 'hidden') {
                        var fieldname = elems[el_index].id.substr(5);
                        console.log(th.length, colindex);
                        sRow += '<td  id="' + document.getElementById('edit_rowid').value + fieldname + '" style="width: ' + th[colindex++].width + '">' + elems[el_index].value.trim() + '</td>'
                    }
                    //console.log(elems[i].type);
                    //return;
                }
            }
        }
    }
    //console.log(sRow);
    var img_src='http://'+location.hostname+'/dolibarr/htdocs/theme/eldy/img/edit.png';

    var edit_icon='<td style="width: 20px" align="left"><img src="'+img_src+'" title="Редактировать" style="vertical-align: middle" onclick="edit_item('+document.getElementById('edit_rowid').value+');"></td>';
    sRow +=edit_icon;
    var class_name;
    table = document.getElementById('reference_body');
    var tr = table.getElementsByTagName('tr');
    for(var i=0;i<tr.length;i++){
        if('impair' == tr[i].className || 'pair' == tr[i].className){
            if('impair' == tr[i].className)
                class_name = 'pair';
            else
                class_name = 'impair';
            break;
        }
    }
    //console.log(sRow);
    var sendtable = document.getElementById('reference_body').getElementsByTagName('tr')[0];
    sendtable.insertAdjacentHTML('beforebegin', '<tr id="'+document.getElementById('edit_rowid').value+'" class="'+class_name+'">'+sRow+'</tr>')
}
function new_item(){
    document.getElementById('edit_rowid').value=0;
    var editor = document.getElementsByClassName('popup');
    var input_field = editor[0].getElementsByTagName('input');

    for(var i=0;i<input_field.length;i++){
        if(input_field[i].type != 'hidden'){
            input_field[i].value='';
        }
    }
    var text_field = editor[0].getElementsByTagName('textarea');
    for(var i=0; i<text_field.length; i++){
        text_field[i].value='';
    }
    var img_field = editor[0].getElementsByTagName('img');
    for(var i=0; i<img_field.length; i++){
        img_field[i].src = 'http://'+location.hostname+'/dolibarr/htdocs/theme/eldy/img/switch_on.png';
    }
    location.href = '#editor';
}
function goto_link(link){
    location.href = link;
}
function edit_item(rowid){
    //console.log('***'+rowid);
    if(rowid != 0){
        var edit_id = document.getElementById('edit_rowid');
        edit_id.value = rowid;
    }
    var sID=rowid.toString();
    var paramList = $('td.param').find('.param');
    if(paramList.length>0){

        //console.log(tablename);
        var link = 'http://'+location.hostname+'/dolibarr/htdocs/DBManager/dbManager.php?loadparam=1&rowid='+rowid+'&tablename='+tablename+'_param&col_name='+tablename+'_id&loadfield='+$('td.param').attr('id');
        //console.log(link);
        $.ajax({
            url: link,
            cache: false,
            success: function (html) {
                var param = html.substr(strpos(html, '=')+1, (strpos(html, ';')-strpos(html, '=')-1));
                var values = html.substr(strpos(html, '=', strpos(html, ';'))+1);
                var paramlist = param.split(',');
                var valuelist = values.split(',');
                var inputList = $('td.param').find('input');
                for(var i=0; i<inputList.length;i++){
                    inputList[i].value='';
                }
                for(var i=0; i<paramlist.length;i++){
                    $('td.param').find('input#'+paramlist[i]).attr('value',valuelist[i]);
                    //console.log($('td.param').find('input#'+paramlist[i]).attr('value',valuelist[i]));
                    //console.log(paramlist[i]);
                    //console.log(valuelist[i]);
                }
                //console.log(param, values);
                return;
            }
        });
    }


    var tr = document.getElementById('tr'+sID);
    //var tr = $('tr#'+sID);
    var elements = tr.getElementsByTagName('td');
    //console.log(tr);
    for(var i=0; i<elements.length; i++){
        if(elements[i].id.substr(0, sID.length) == sID){
            var fieldname = elements[i].id.substr(sID.length);
            var bSelectedField = false;
            var source_field = document.getElementById(sID+fieldname);
            var edit_field = document.getElementById('edit_'+fieldname);
            //if(fieldname == 'login')

            if(edit_field == null&&fieldname.substr(0,2) == 's_'){
                //alert(fieldname.substr(2));
                bSelectedField = true;
                var select_field = $('select#edit_'+fieldname.substr(2))
                var detail_id = 'detail_' + select_field[0].id.substr(5);
                //console.log('***'+detail_id, fieldname, detail_id);
                var detail_field = document.getElementById(detail_id);
                //console.log("select#edit_"+fieldname.substr(2));
                if(detail_field != null)
                    $("select#edit_"+fieldname.substr(2)+"  [value="+$('select#select'+rowid+detail_field.value).val()+"]").attr("selected", "selected");
            }
            var img = source_field.getElementsByTagName('img');
            if(img.length > 0){
                if(source_field.getElementsByTagName('a').length>0){
                    edit_field.value = $('#'+fieldname+'_'+sID).text();
                }else {
                    //console.log(edit_field.id);
                    edit_field.src = img[0].src;
                }
            }else {
                if(edit_field != null) {
                    edit_field.value = source_field.innerHTML;
                }
            }
        }
    }
    location.href = '#editor';
}
function change_select(rowid, tablename, col_name){
    console.log('select#select'+rowid+col_name);
    var value = $('select#select'+rowid+col_name+' option:selected').val();
    if(rowid != 0) {
        var link = 'http://'+location.hostname+'/dolibarr/htdocs/DBManager/dbManager.php?rowid='+rowid+'&edit=1&tablename='+tablename+'&col_name='+col_name+'&value='+value;
        console.log(link);
        update_data(link);
    }
}
function changeAllPerms(group_id, module, theme, table, check){
    var switchlist = document.getElementsByClassName(module);
    for(var i = 0; i<switchlist.length; i++) {
        if (check) {
            switchlist[i].src = '/dolibarr/htdocs/theme/'+theme+'/img/switch_on.png';
        }else{
            switchlist[i].src = '/dolibarr/htdocs/theme/'+theme+'/img/switch_off.png';
            //console.log(switchlist[i].id);
        }
        var id_usr = document.getElementById('user_id').value;
        var link = 'http://'+location.hostname+'/dolibarr/htdocs/DBManager/dbManager.php?set_permission='+module+'&group_id='+group_id+
            '&perm_index='+switchlist[i].id.substr(3)+'&check='+check+'&id_usr='+id_usr+'&table='+table;
        //console.log(link);
        $.ajax({
            url: link,
            cache: false,
            success: function (html) {
                console.log(html);
            }
        });
    }
}
function change_switch(rowid, tablename, col_name, theme){
    var x;
    if(rowid != 0) {
        x = document.getElementById('img' + rowid + col_name);
    }else {
        x = document.getElementById('edit_' + col_name);
    }
    //console.log('img' + rowid + col_name);
    var check = false;
    var end = strpos(x.src, '/img/');
    //console.log(x.src);
    //console.log(x.src.substr(0, end+4)+'/switch_on.png');
    if(x.src == x.src.substr(0, end+4)+'/switch_on.png'){
        x.src = x.src.substr(0, end+4)+'/switch_off.png';
    }else{
        x.src =  x.src.substr(0, end+4)+'/switch_on.png';
        check = true;
    }
    if(rowid != 0) {
        var link = 'http://'+location.hostname+'/dolibarr/htdocs/DBManager/dbManager.php?rowid='+rowid+'&edit=1&tablename='+tablename+'&col_name='+col_name+'&value='+check;
        console.log(link);
        update_data(link);
    }
}
function strpos( haystack, needle, offset){ // Find position of first occurrence of a string
    var i = haystack.indexOf( needle, offset ); // returns -1
    return i >= 0 ? i : false;
}
function update_data(link){
    var id_usr = document.getElementById('user_id').value;
    $.ajax({
        url: link+'&id_usr='+id_usr,
        cache: false,
        success: function (html) {
            console.log(html);
        }
    });

};
function change_switch_callfield(obj){
    //console.log(obj.attr('src'));

    var check = 0;
    var end = strpos(obj.attr('src'), '/img/');
    var id = '';
    if (obj.attr('src') == obj.attr('src').substr(0, end + 4) + '/check.png') {
        obj.attr('src', obj.attr('src').substr(0, end + 4) + '/uncheck.png');
    } else {
        obj.attr('src', obj.attr('src').substr(0, end + 4) + '/check.png');
        check = 1;
    }
    var tablename = 'llx_societe_contact';
    var callfields = ['work_phone', 'fax', 'mobile_phone', 'email', 'skype', 'birthdaydate'];
    var begin = false;
    var index = 0;
    while(!begin) {
        begin = strpos(obj.attr('id'), callfields[index++]);
    }
    var fieldname = '';
    index--;
    if(callfields[index] == 'email' || callfields[index] == 'birthdaydate')
        fieldname = 'send_'+obj.attr('id').substr(begin);
    else
        fieldname = 'call_'+obj.attr('id').substr(begin);
    var ID = obj.attr('id').substr(3, begin-3);
    //console.log(fieldname);
    $.ajax({
        url: 'http://'+location.hostname+'/dolibarr/htdocs/DBManager/dbManager.php?edit=1&tablename='+tablename+'&col_name='+fieldname+'&value='+check+'&id_usr='+$('#user_id').val()+'&rowid='+ID,
        cache: false,
        success: function (html) {
            //console.log('***'+html+'***');
        }
    });

}

function save_data(link){
    //console.log('http://'+location.hostname+'/dolibarr/htdocs/DBManager/dbManager.php?save=1&'+link);
    //return;
    $.ajax({
        url: 'http://'+location.hostname+'/dolibarr/htdocs/DBManager/dbManager.php?save=1&'+link,
        cache: false,
        success: function (html) {
            console.log('***'+html+'***');
            location.reload();
            return;
            var rowid = html;
            var tr = document.getElementById("0");
            if(tr == null)
                tr = document.getElementById('tr'+rowid);
            if(tr != null) {
                tr.id = 'tr' + rowid;
                var tdlist = tr.getElementsByTagName('td');
                for (var i = 0; i < tdlist.length; i++) {
                    if (tdlist[i].id.substr(0, 1) == '0') {
                        tdlist[i].id = rowid + tdlist[i].id.substr(1);
                    }
                    if (tdlist[i].getElementsByTagName('img').length > 0) {
                        var imglist = tdlist[i].getElementsByTagName('img');
                        var img = imglist[0];
                        if (img.id.substring(0, 4) == 'img0') {
                            var begin = strpos(link, '=') + 1;
                            var end = strpos(link, '&');
                            var tablename = link.substr(begin, end - begin)
                            var fieldname = img.id.substring(4);
                            img.id = 'img' + rowid + img.id.substring(4);
                            img.onclick = function () {
                                change_switch(rowid, tablename, fieldname);
                            }
                        } else {
                            if (img.id == null) {
                                img.onclick = function () {
                                    edit_item(rowid)
                                };
                            }
                        }
                    }
                    if (tdlist[i].getElementsByTagName('select').length > 0) {
                        //console.log($('select#edit_regions_name').val());
                        var selectList = tdlist[i].getElementsByTagName('select');

                        var select = selectList[0];
                        //console.log(select);
                        var detail_field = select.id.substring(6 + rowid.length);
                        //var select_field = $('select#edit_'+select.id.substring(6+rowid.length));
                        //console.log(detail_field, 522);
                        //var detail_id = 'detail_' + select_field[0].id.substr(5);
                        //
                        //var detail_field = document.getElementById(detail_id);
                        //console.log($('select#edit_' + tdlist[i].id.substr(html.length+2)).val());
                        select.onchange = function () {
                            change_select(rowid, tablename, detail_field);
                        }
                        if (select.id == null)
                            select.id = 'select' + rowid + detail_field.value;
                        //console.log("select#" + select.id + "  [value=" + $('select#edit_' + tdlist[i].id.substr(html.length) + '').val() + "]", 531);
                        $("select#" + select.id + "  [value=" + $('select#edit_' + tdlist[i].id.substr(html.length + 2)).val() + "]").attr("selected", "selected");
                    }

                }

                document.getElementById('edit_rowid').value = html;
            }
        }
    });

};