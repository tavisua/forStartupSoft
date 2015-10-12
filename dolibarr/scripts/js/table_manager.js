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
        //console.log(link);
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
            if($td[0].id.substr($td[0].id.length-fieldslist[i].length) == fieldslist[i]){
                return true;
            }
        }
    }
    return false;
}
function add_item(){
    var title = document.getElementById('reference_title');
    var table = document.getElementById('reference');
    table = document.getElementById('edit_table');
    var td_list = table.getElementsByTagName('td');
    //document.getElementById('edit_rowid').value = 111;
    var sRow='';
    for(var i = 0; i<td_list.length; i++){
        var td = td_list[i];
        if(td.getElementsByTagName('input').length > 0) {
            var elems = td.getElementsByTagName('input');
            var fieldname = elems[0].id.substr(5);
            sRow +='<td id="'+document.getElementById('edit_rowid').value+fieldname+'">'+elems[0].value.trim()+'</td>'
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
function edit_item(rowid){
    //console.log('***'+rowid);
    if(rowid != 0){
        var edit_id = document.getElementById('edit_rowid');
        edit_id.value = rowid;
    }
    var sID=rowid.toString();
    var tr = document.getElementById(sID);
    var elements = tr.getElementsByTagName('td');
    for(var i=0; i<elements.length; i++){
        if(elements[i].id.substr(0, sID.length) == sID){
            var fieldname = elements[i].id.substr(sID.length);

            var source_field = document.getElementById(sID+fieldname);
            var edit_field = document.getElementById('edit_'+fieldname);
            //console.log(source_field.innerHTML);
            var img = source_field.getElementsByTagName('img');
            if(img.length > 0){
                //console.log(edit_field.id);
                edit_field.src = img[0].src;
            }else
                edit_field.value = source_field.innerHTML;
        }
    }
    location.href = '#editor';
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
    if(rowid != 0)
        update_data(rowid, tablename, col_name, check);
}
function strpos( haystack, needle, offset){ // Find position of first occurrence of a string
    var i = haystack.indexOf( needle, offset ); // returns -1
    return i >= 0 ? i : false;
}
function update_data(rowid, tablename, col_name, check){
    var id_usr = document.getElementById('user_id').value;
    $.ajax({
        url: 'http://'+location.hostname+'/dolibarr/htdocs/DBManager/dbManager.php?rowid='+rowid+'&edit=1&tablename='+tablename+'&col_name='+col_name+'&value='+check+'&id_usr='+id_usr,
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
            }
            document.getElementById('edit_rowid').value=html;
        }
    });

};