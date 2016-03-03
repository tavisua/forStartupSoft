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
            var hour = html.substr(0,2);
            var min = html.substr(3,2);
            $("#aphour [value='"+hour+"']").attr("selected", "selected");
            $("#apmin [value='"+min+"']").attr("selected", "selected");
            setP2(1);
        }
    })
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
        var input_field = editor[0].getElementsByTagName('input');
        var fields='', values ='';
        //console.log(input_field);
        //return;
        for(var i=0;i<input_field.length;i++){
            if(input_field[i].type != 'hidden' && input_field[i].id.substr(0, 5) == 'edit_'){
                var fieldname = input_field[i].id.substring(5);
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
        var text_field = editor[0].getElementsByTagName('textarea');
        for(var i=0; i<text_field.length; i++){
            var fieldname = text_field[i].id.substring(5);
            if(sID != 0) {
                var send_field = document.getElementById(sID + fieldname);
                if(send_field.getElementsByTagName('a').length>0){
                    send_field.innerHTML = '<a id = "'+send_field.getElementsByTagName('a')[0].id+'" href="'+send_field.getElementsByTagName('a')[0].href+'">' +
                    '<img border="0" src="/dolibarr/htdocs/theme/eldy/img/object_user.png" alt="" title="Show user">'+text_field[i].value+'</a>';
                }else
                    send_field.innerHTML = text_field[i].value;
            }
            var value = text_field[i].value;//.replace(/\./gi, "&&");
            //value = value.replace(/\&/gi, "@@");
            if(fields != '') {
                fields = fields + ',' + fieldname;
                values = values + ','+escapeHtml(value).trim();
            }else {
                fields = fieldname;
                values = escapeHtml(value).trim();
            }
        }

        var img_field = editor[0].getElementsByTagName('img');
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
        var select_field = editor[0].getElementsByTagName('select');

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