/**
 * Created by -tavis- on 07.11.2015.
 */
function loadareas(){
    $('select#region_id').find('option').remove();
    $.ajax({
        url: '/dolibarr/htdocs/societe/soc.php?getregion='+$('select#state_id').val(),
        cache: false,
        success: function (html) {
            var optionList = html.substr(strpos(html, '<option value="0">'));
            optionList = optionList.substr(0, strpos(optionList, '</select>'));
            $('select#region_id').append(optionList);
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
    if($.inArray($('#categoryofcustomer').val(), val_categories) == -1){
        show = true;
    }
    console.log($('#categoryofcustomer').val(), $.inArray($('#categoryofcustomer').val(), val_categories));
    if(show){
        $('#assign_name').show();
        $('#classifycation').show();
    }else{
        $('#assign_name').hide();
        $('#classifycation').hide();
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