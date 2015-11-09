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