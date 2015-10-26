/**
 * Created by tavis on 06.10.2015.
 */
function close_form(){
    location.href = '#close';
}
function save_item(tablename){
    if(confirm('Зберегти данні?')){
        var sID = document.getElementById('edit_rowid').value;
        var id_usr = document.getElementById('user_id').value;
        var editor = document.getElementsByClassName('popup');
        var input_field = editor[0].getElementsByTagName('input');
        var fields='', values ='';

        for(var i=0;i<input_field.length;i++){
            if(input_field[i].type != 'hidden'){
                var fieldname = input_field[i].id.substring(5);
                if(sID != 0) {
                    var send_field = document.getElementById(sID + fieldname);
                    send_field.innerHTML = input_field[i].value;
                }
                var value = input_field[i].value;
                value = value.replace(/\&/gi, "@@");
                if(fields != '') {
                    fields = fields + ',' + fieldname;
                    values = values + ','+value;
                }else {
                    fields = fieldname;
                    values = value;
                }
            }
        }
        var text_field = editor[0].getElementsByTagName('textarea');
        for(var i=0; i<text_field.length; i++){
            var fieldname = text_field[i].id.substring(5);
            if(sID != 0) {
                var send_field = document.getElementById(sID + fieldname);
                send_field.innerHTML = text_field[i].value;
            }
            var value = text_field[i].value;//.replace(/\./gi, "&&");
            value = value.replace(/\&/gi, "@@");
            if(fields != '') {
                fields = fields + ',' + fieldname;
                values = values + ','+value;
            }else {
                fields = fieldname;
                values = value;
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
                fields = fields + ',' + fieldname;
                values = values + ','+value;
            }else {
                fields = fieldname;
                values = value;
            }
        }
        var select_field = editor[0].getElementsByTagName('select');
        if(select_field.length>0) {
            var detail_id = 'detail_' + select_field[0].id.substr(5);
            var detail_field = document.getElementById(detail_id);
            //console.log(select_field[0].value + ' 111 ' + detail_field.value);
            if(fields != '') {
                fields = fields + ',' + detail_field.value;
                values = values + ','+select_field[0].value;
            }else {
                fields = detail_field.value;
                values = select_field[0].value;
            }
        }
        if(sID == 0 && alrady_exist(fields, values)) {
            alert('Такая запись уже существует');
            location.href = '#close';
            return;
        }

        var link = "tablename="+tablename+"&columns='"+fields+"'&values='"+values+"'&id_usr="+id_usr+"&save=1";
        if(sID != 0)
            link += "&rowid="+sID;
        else {
            console.log('Добавление новой записи '+sID);
            add_item();
        }
        console.log(link);
        save_data(link);
        location.href = '#close';
    }
}
function alrady_exist(fields, values){//Проверка на идентичные записи
    var $table = $('#reference');
    var fieldslist = fields.split(',');
    var valuelist = values.split(',');
    var i = 0;
    var $td = null;
    for(i; i<valuelist.length; i++) {
        var value = valuelist[i].trim();
        $td = $table.find('td:contains("'+value+'")');
        if($td.length > 0) {
            if($td[0].id.substr($td[0].id.length-fieldslist[i].length) != fieldslist[i]){
                return false;
            }
        }
    }
    return true;
}
function add_item(){
    var title = document.getElementById('reference_title');
    var table = document.getElementById('reference');
    table = document.getElementById('edit_table');
    var td_list = table.getElementsByTagName('td');
    var th = title.getElementsByTagName('th');
    var sRow='';
    for(var i = 0; i<td_list.length; i++){
        var td = td_list[i];

        if(td.getElementsByTagName('select').length > 0){
            var elems = td.getElementsByTagName('select');
            for(var el_index = 0; el_index<elems.length; el_index++) {
                if(elems[el_index].type != 'hidden') {
                    var fieldname = elems[el_index].id.substr(5);
                    var optionlist = elems[el_index].getElementsByTagName('option');
                    for(var index = 0; index<optionlist.length; index++){
                        if(optionlist[index].value == elems[el_index].value) {
                            var selectHTML = elems[el_index].outerHTML;
                            selectHTML = selectHTML.replace('edit_'+fieldname, 'select'+document.getElementById('edit_rowid').value+fieldname);

                            sRow += '<td  id="' + document.getElementById('edit_rowid').value + fieldname + '" >' + selectHTML + '</td>';
                            break;
                        }
                    }
                    //var text = $('option#'+elems[el_index].value).html();
                    //console.log(text);

                }

                //return;
            }
        }else if(td.getElementsByTagName('textarea').length > 0){
            var elems = td.getElementsByTagName('textarea');
            var fieldname = elems[0].id.substr(5);
            sRow +='<td id="'+document.getElementById('edit_rowid').value+fieldname+'">'+elems[0].value.trim()+'</td>'
        }else if(td.getElementsByTagName('img').length > 0){
            var img = td.getElementsByTagName('img');
            var fieldname = img[0].id.substr(5);
            var html = td.innerHTML.replace(/change_switch\(0/gi, "change_switch("+document.getElementById('edit_rowid').value);
            html = html.replace(/img id="edit_active"/gi, "img id='img"+document.getElementById('edit_rowid').value+fieldname+"'")
            sRow +='<td id="'+document.getElementById('edit_rowid').value+fieldname+'">'+html+'</td>';
        }else if(td.getElementsByTagName('input').length > 0) {
            var elems = td.getElementsByTagName('input');
            for(var el_index = 0; el_index<elems.length; el_index++) {
                if(elems[el_index].type != 'hidden') {
                    var fieldname = elems[el_index].id.substr(5);
                    sRow += '<td  id="' + document.getElementById('edit_rowid').value + fieldname + '">' + elems[el_index].value.trim() + '</td>'
                }
                //console.log(elems[i].type);
                //return;
            }
        }
    }

    var img_src='http://'+location.hostname+'/dolibarr/htdocs/theme/eldy/img/edit.png';

    var edit_icon='<td style="width: 20px" align="left"><img src="'+img_src+'" title="Редактировать" style="vertical-align: middle" onclick="edit_item('+document.getElementById('edit_rowid').value+');"></td>';
    sRow +=edit_icon;
    var class_name;
    table = document.getElementById('reference');
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
    title.insertAdjacentHTML('afterend', '<tr id="'+document.getElementById('edit_rowid').value+'" class="'+class_name+'">'+sRow+'</tr>')
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
            if(edit_field == null&&fieldname.substr(0,2) == 's_'){
                //alert(fieldname.substr(2));
                bSelectedField = true;
                var select_field = $('select#edit_'+fieldname.substr(2))
                var detail_id = 'detail_' + select_field[0].id.substr(5);
                var detail_field = document.getElementById(detail_id);
                console.log("select#edit_"+fieldname.substr(2));
                $("select#edit_"+fieldname.substr(2)+"  [value="+$('select#select'+rowid+detail_field.value).val()+"]").attr("selected", "selected");
            }
            var img = source_field.getElementsByTagName('img');
            if(img.length > 0){
                //console.log(edit_field.id);
                edit_field.src = img[0].src;
            }else {
                if(edit_field != null) {
                    console.log(edit_field.id + ' ' + edit_field.tagName);
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
function save_data(link){
    $.ajax({
        url: 'http://'+location.hostname+'/dolibarr/htdocs/DBManager/dbManager.php?save=1&'+link,
        cache: false,
        success: function (html) {
            //console.log(html);
            var rowid = html;
            var tr = document.getElementById("0");
            if(tr == null)
                tr = document.getElementById(rowid);
            tr.id=rowid;
            var tdlist = tr.getElementsByTagName('td');
            for(var i=0; i<tdlist.length;i++){
                if(tdlist[i].id.substr(0,1)=='0'){
                    tdlist[i].id = rowid+tdlist[i].id.substr(1);
                }
                if(tdlist[i].getElementsByTagName('img').length>0){
                    var imglist = tdlist[i].getElementsByTagName('img');
                    var img = imglist[0];
                    if(img.id.substring(0,4) == 'img0') {
                        var begin = strpos(link, '=')+1;
                        var end = strpos(link, '&');
                        var tablename = link.substr(begin, end-begin)
                        var fieldname= img.id.substring(4);
                        img.id='img'+rowid+img.id.substring(4);
                        img.onclick = function () {
                            change_switch(rowid, tablename, fieldname);
                        }
                    }else {
                        if(img.id == null) {
                            img.onclick = function () {
                                edit_item(rowid)
                            };
                        }
                    }
                }
                if(tdlist[i].getElementsByTagName('select').length>0){
                    //console.log($('select#edit_regions_name').val());
                    var selectList = tdlist[i].getElementsByTagName('select');
                    var select = selectList[0];
                    var fieldname = select.id.substring(7);
                    var select_field = $('select#edit_'+select.id.substring(7))
                    var detail_id = 'detail_' + select_field[0].id.substr(5);
                    var detail_field = document.getElementById(detail_id);
                    console.log(select.id.substring(7));
                    select.onchange = function(){
                        change_select(rowid, tablename, detail_field.value);
                    }
                    //console.log(select.id.substring(7));
                    select.id = 'select'+rowid+detail_field.value;
                    $("select#select"+rowid+select.id.substring(7)+"  [value="+$('select#edit_'+tdlist[i].id.substr(html.length)+'').val()+"]").attr("selected", "selected");
                }
            }
            
            document.getElementById('edit_rowid').value=html;
        }
    });

};