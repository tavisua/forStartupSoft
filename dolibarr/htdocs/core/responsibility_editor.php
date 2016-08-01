<?php
global $db, $user;
require '../main.inc.php';
//echo '<pre>';
//var_dump($_REQUEST);
//echo '</pre>';
//die();
if(count($_POST)>0) {

    $sql = 'update responsibility set name = "'.$db->escape(GETPOST('name')).'", alias = "'.$db->escape(GETPOST('alias')).'", showlineactive='.(isset($_POST['ShowLineActive'])?1:0).' where rowid='.GETPOST('rowid');
//    die($sql);
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
    $id_respon = array();


    if(!empty($_POST['id_respon'])) {
        if(strpos(GETPOST('id_respon'),';'))
            $PostArray = explode(';', GETPOST('id_respon'));
        else
            $PostArray = explode(',', GETPOST('id_respon'));
//var_dump($PostArray);
//die();
        foreach($PostArray as $id){
//            if(!is_numeric($id)) {
                $id = "'" . $id . "'";
                if(!in_array($id, $id_respon))
                    $id_respon[]=$id;
//            }
        }
        $respon_sql = str_replace("''","'",implode(',', $id_respon));
//        $respon_sql.= str_replace("'\"",'',$respon_sql);
        $sql = 'delete from responsibility_param where fx_responsibility = ' . GETPOST('rowid') . '
            and (
                (fx_category_counterparty not in (' . str_replace("'",'',$respon_sql) . ') and other_category is null)
            or
                (other_category not in (' . $respon_sql . ') and fx_category_counterparty is null)
            )';
    }else{
        $sql = 'delete from responsibility_param where fx_responsibility = ' . GETPOST('rowid');
    }
    $res = $db->query($sql);
    if(!$res){
        var_dump($sql);
        dol_print_error($db);
    }
    foreach($id_respon as $id){
        $sql = 'insert responsibility_param (fx_responsibility, '.(is_numeric($id)?'fx_category_counterparty':'other_category').') values('.GETPOST('rowid').', '.$id.')';
        $res = $db->query($sql);
//        if(!$res){
//            var_dump($sql);
//            dol_print_error($db);
//        }
    }
//    die('Location: '.GETPOST('url'));
    header('Location: ' . GETPOST('url', 'alpha'));
    exit();
}


$Tools = $langs->trans("Tools");
$SphereOfResponsibility = $langs->trans('SphereOfResponsibility');
$title = $langs->trans('EditSphereOfResponsibility');
llxHeader("",$title,"");
print_fiche_titre($title);
$sql = 'select case when fx_category_counterparty is not null then fx_category_counterparty else other_category end fx_category_counterparty  from responsibility_param where fx_responsibility = '.$_REQUEST['rowid'];
$res = $db->query($sql);
if(!$res){
    var_dump($sql);
    dol_print_error($db);
}
$id_respon = array();
while($row = $db->fetch_object($res)){
    if(!is_numeric($row->fx_category_counterparty))
        $id_respon[] = "'".$row->fx_category_counterparty."'";
    else
        $id_respon[] = $row->fx_category_counterparty;
}
//        var_dump(in_array("'users'", $id_respon));
//        die();

$sql = 'select rowid, name from category_counterparty where active = 1 order by trim(name)';
$res = $db->query($sql);
$selector = '<select id = "select_respon" multiple size="5" name="select_respon">';
while($row = $db->fetch_object($res)){
    $selected = in_array($row->rowid, $id_respon);
    if($selected)
        $selector .= '<option selected = "selected" value="'.$row->rowid.'"> '.$row->name.'</option>\r\n';
    else
        $selector .= '<option value="'.$row->rowid.'"> '.$row->name.'</option>\r\n';
}
$selector .= '<option value="users" '.(in_array("'users'", $id_respon)?'selected = "selected"':'').'>Співробітники</option>\r\n';
$selector .= '</select>';

$sql = 'select name,alias,showlineactive from responsibility where rowid='.$_REQUEST['rowid'];

$rowid = $_REQUEST['rowid'];
$url = $_SERVER["HTTP_REFERER"];
$res = $db->query($sql);
$row = $db->fetch_object($res);
$Name = $row->name;
$Alias = $row->alias;
$ShowLineActive = $row->showlineactive;
include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/responsibility_editor.html');
echo ob_get_clean();
//llxFooter();
