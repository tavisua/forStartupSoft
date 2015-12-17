/**
 * Created by -tavis- on 17.12.2015.
 */
function loadkind_assets(){
    var lineactive = $('select#LineActive').val();
    $('select#KindAssets').find('option').remove();
    $.ajax({
        url: '/dolibarr/htdocs/admin/dict.php?action=get_kindassets&fx_lineactive='+lineactive,
        cache: false,
        success: function (html) {
            var optionList = html.substr(strpos(html, '<option value="0"'));
            optionList = optionList.substr(0, strpos(optionList, '</select>'));
            console.log(optionList);
            $('select#KindAssets').append(optionList);
        }
    });
}
function strpos( haystack, needle, offset){ // Find position of first occurrence of a string
    var i = haystack.indexOf( needle, offset ); // returns -1
    return i >= 0 ? i : false;
}