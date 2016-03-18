/**
 * Created by -tavis- on 07.11.2015.
 */
function loadareas(region_id){
    $('select#region_id').find('option').remove();
    $.ajax({
        url: '/dolibarr/htdocs/societe/soc.php?getregion='+$('select#state_id').val()+'&region_id='+region_id,
        cache: false,
        success: function (html) {
            console.log(region_id);
            var optionList = html.substr(strpos(html, '<option value="0">'));
            optionList = optionList.substr(0, strpos(optionList, '</select>'));
            $('select#region_id').append(optionList);
            //if(region_id != 0){
            //    $("select#region_id  [value=" + region_id + "]").attr("selected", "selected");
            //}
        }
    });
}
function strpos( haystack, needle, offset){ // Find position of first occurrence of a string
    var i = haystack.indexOf( needle, offset ); // returns -1
    return i >= 0 ? i : false;
}
function setvisiblbloks(){
    var val_categories = ['0','4','6','7','1','9'];
    var show = false;
    if($.inArray($('#categoryofcustomer').val().toString, val_categories) == -1){
        show = true;
    }
    console.log($('#categoryofcustomer').val(), $.inArray($('#categoryofcustomer').val().toString, val_categories));
    if(show){
        $('#assign_name').show();
        $('#classifycation').show();
        $('#lineactive').show();
        console.log('show');
    }else{
        $('#assign_name').hide();
        $('#classifycation').hide();
        $('#lineactive').hide();
        console.log('hide');
    }

}
function addtownitem(val){
    var item = '<input id="'+val.name+'" class="townitem" type="hidden" name="action" value="'+val.rowid+'">'
    var  newitem = document.createElement('input');

    newitem.id = val.name;
    newitem.type = "hidden"
    newitem.value = val.rowid;
    newitem.class = "townitem";
    $('#formsoc').add(newitem);

}
function setHightTable(table){
    var tbody = document.getElementById(table);
    if(tbody!=null){
        var tdlist = $('#reference_body').find('td');
        if((tdlist.length/22)>20) {
            tbody.style.height = window.innerHeight * .78;
            //console.log(document.getElementsByClassName('tabPage').length);
            if (document.getElementsByClassName('tabPage').length > 0) {
                tbody.style.height = window.innerHeight - 370;
                //console.log(tbody.style.width);
                tbody.style.width = Number(tbody.style.width.substr(0, tbody.style.width.length - 2)) + 20;
                //console.log('.tabPage');
            }
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
function change_switch(obj){
    return;//Запрет на переключение флажков при добавлении контакта. Нужно доделать
    console.log(obj.attr('src'));

    var check = 0;
    var end = strpos(obj.attr('src'), '/img/');
    var id = '';
    if (obj.attr('src') == obj.attr('src').substr(0, end + 4) + '/switch_on.png') {
        obj.attr('src', obj.attr('src').substr(0, end + 4) + '/switch_off.png');
    } else {
        obj.attr('src', obj.attr('src').substr(0, end + 4) + '/switch_on.png');
        check = 1;
    }
    if(obj.attr('id').substr(0, 4)=='call')
        id = obj.attr('id').replace('call', 'call_');
    else if(obj.attr('id').substr(0, 4)=='send')
        id = obj.attr('id').replace('send', 'send_');
    $('#'+id).val(check);
    console.log('#'+id, $('#'+id).val());
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
function change_select(rowid, tablename, col_name){
    console.log('select#select'+rowid+col_name);
    var value = $('select#select'+rowid+col_name+' option:selected').val();
    if(rowid != 0) {
        var link = 'http://'+location.hostname+'/dolibarr/htdocs/DBManager/dbManager.php?rowid='+rowid+'&edit=1&tablename='+tablename+'&col_name='+col_name+'&value='+value;
        console.log(link);
        update_data(link);
    }
}
 $(window).click(function(){
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
