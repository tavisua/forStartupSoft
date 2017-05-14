<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 04.11.2015
 * Time: 12:10
 */

require $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/main.inc.php';
if(count($_POST)>0){
    $_SESSION['region_id'] = GETPOST('state_filter', 'int');
//    echo '<pre>';
//    var_dump($_SESSION['region_id']);
//    echo '</pre>';
//    die();
}
if($_REQUEST['action'] == 'showmodel'){
    echo fShowModel($_GET['kind_assets']);
    exit();
}
//echo '<pre>';
//var_dump($_REQUEST);
//echo '</pre>';
//die();
$Area = $langs->trans('Area');
llxHeader("",$Area,"");
print_fiche_titre($Area);
//print '<div>';
if(isset($_GET['id_usr'])&&!empty($_GET['id_usr'])){
    global $db;
    $sql = 'select lastname,respon_id from llx_user where rowid = '.$_GET['id_usr'];
    $res = $db->query($sql);
    $obj = $db->fetch_object($res);
    $username = $obj->lastname;
    $id_usr = $_GET['id_usr'];
    $respon_id = $obj->respon_id;
}else {
    $id_usr = $user->id;
    $username = $user->lastname;
    $respon_id = $user->respon_id;
}
//echo '<pre>';
//var_dump($_REQUEST);
//echo '</pre>';
//die();
if(empty($_REQUEST['category'])&&!empty($_REQUEST['lineactive']))
    $_REQUEST['category'] = $_REQUEST['lineactive'];
//Шапка сторінки

include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/responsibility/logistika/area/header.php';
//Перелік контрагентів
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/responsibility/logistika/area/customers.php';
if(strpos($_SERVER['QUERY_STRING'],'&page='))
    $link_page = $_SERVER['PHP_SELF'].'?'.substr($_SERVER['QUERY_STRING'],0,strpos($_SERVER['QUERY_STRING'],'&page='));
else
    $link_page = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
//echo '<pre>';
//var_dump($link_page);
//echo '</pre>';
//die();
llxPopupMenu();
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/static_content/layout/pagination.phtml';
//print '</div>';
//llxFooter();
exit();
function fShowModel($fx_kind_assets = 0, $id = 0){
    global $db;
    $out = '<select '.(empty($fx_kind_assets)?'style="width:150px"':'').' id="model" class="combobox" name="model" size=1" >';
    $out .= '<option '.(empty($id)?('selected = "selected" disabled="disabled" value="0"'):'').' value="0">Вкажіть модель</option>';
    if($fx_kind_assets == 0){
        $out.='</select>';
        return $out;
    }
    $sql = "select rowid, model, description, description_1, description_2, basic_param from llx_c_model
        where fx_kind_assets = $fx_kind_assets
        and active = 1
        order by model";
//        print $sql;
//        die();
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    while($row = $db->fetch_object($res)){
        $out .= '<option '.($id == $row->rowid?('selected = "selected"'):'').' value="'.$row->rowid.'">'."$row->model $row->basic_param $row->description $row->description_1 $row->description_2".'</option>';
    }
    $out.='</select>';
    return $out;
}
