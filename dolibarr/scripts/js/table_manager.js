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
                var value = input_field[i].value;//.replace(/\./gi, "&&");
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
        var link = "tablename="+tablename+"&columns='"+fields+"'&values='"+values+"'&id_usr="+id_usr;
        if(sID != 0)
            link += "&rowid="+sID;
        //save_data(link);
        add_item();
        location.href = '#close';
    }
}
function add_item(){
    var title = document.getElementById('reference_title');
    var table = document.getElementById('edit_table');
    var td_list = table.getElementsByTagName('td');
    document.getElementById('edit_rowid').value = 111;
    var sRow='';
    for(var i = 0; i<td_list.length; i++){
        var td = td_list[i];
        if(td.getElementsByTagName('input').length > 0) {
            var elems = td.getElementsByTagName('input');
            sRow +='<td>'+elems[0].value.trim()+'</td>'
        }else if(td.getElementsByTagName('textarea').length > 0){
            var elems = td.getElementsByTagName('textarea');
            sRow +='<td>'+elems[0].value.trim()+'</td>'
        }else if(td.getElementsByTagName('img').length > 0){
            var html = td.innerHTML.replace(/change_switch\(0/gi, "change_switch("+document.getElementById('edit_rowid').value);
            //console.log(html);
            sRow +='<td>'+html+'</td>';
        }
    }
    var img_src='http://'+location.hostname+'/dolibarr/htdocs/theme/eldy/img/edit.png';

    var edit_icon='<td style="width: 20px" align="left"><img src="'+img_src+'" title="Редактировать" style="vertical-align: middle" onclick="edit_item('+document.getElementById('edit_rowid').value+');"></td>';
    sRow +=edit_icon;
    title.insertAdjacentHTML('afterend', '<tr>'+sRow+'</tr>')
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
            var edit_field = document.getElementById('edit_'+fieldname);
            var source_field = document.getElementById(sID+fieldname);
            var img = source_field.getElementsByTagName('img');
            if(img.length == 1){
                edit_field.src = img[0].src;
            }else
                edit_field.value = source_field.innerHTML;
        }
    }
    location.href = '#editor';
}
function change_switch(rowid, tablename, col_name, theme){
    var x;
    if(rowid != 0)
        x = document.getElementById('img'+rowid+col_name);
    else
        x = document.getElementById('edit_'+col_name);
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
            document.getElementById('edit_rowid').value=html;
        }
    });

};