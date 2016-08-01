/**
 * Created by -tavis- on 17.12.2015.
 */

function loadkind_assets(LineActive){
    if(LineActive === undefined)
        return;
    //console.log(LineActive.parent().parent());

    var lineactive = LineActive.val();
    $('select#KindAssets').find('option').remove();
    console.log('/dolibarr/htdocs/admin/dict.php?action=get_kindassets&fx_lineactive='+lineactive);
    $.ajax({
        url: '/dolibarr/htdocs/admin/dict.php?action=get_kindassets&fx_lineactive='+lineactive,
        cache: false,
        success: function (html) {
            var optionList = html.substr(strpos(html, '<option value="0"'));
            optionList = optionList.substr(0, strpos(optionList, '</select>'));
            console.log(optionList);
            var edititem = LineActive.parent().parent();
            var KindAssets = edititem.find('select#KindAssets');
            KindAssets.append(optionList);
        }
    });
}
function load_measurement(){
    var lineactive = $('select#LineActive').val();
    $('select#KindAssets').find('option').remove();
    console.log('/dolibarr/htdocs/admin/dict.php?action=get_kindassets&fx_lineactive='+lineactive);
    $.ajax({
        url: '/dolibarr/htdocs/admin/dict.php?action=get_kindassets&fx_lineactive='+lineactive,
        cache: false,
        success: function (html) {
            var optionList = html.substr(strpos(html, '<option value="0"'));
            optionList = optionList.substr(0, strpos(optionList, '</select>'));
            //console.log(optionList);
            $('select#KindAssets').append(optionList);
        }
    });
}
function strpos( haystack, needle, offset){ // Find position of first occurrence of a string
    var i = haystack.indexOf( needle, offset ); // returns -1
    return i >= 0 ? i : false;
}
function SetEnableTrademark(){
    var KindAssetsID=["67","68","69","70","71","72","73","74","75"];
    //console.log($('select#KindAssets').val(), $.inArray($('select#KindAssets').val(), KindAssetsID));
    if($.inArray($('select#KindAssets').val(), KindAssetsID) != -1) {
        $('select#Trademark').prop('disabled', 'disabled');
        //console.log('disabled', $('select#KindAssets').val());
    }else {
        $('select#Trademark').prop('disabled', false);
        //console.log('enabled');
    }
}