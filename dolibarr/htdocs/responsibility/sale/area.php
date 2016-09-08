<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 04.11.2015
 * Time: 12:10
 */

require $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/main.inc.php';
if(count($_POST)>0){
    if($_SESSION['state_filter'] != GETPOST('state_filter', 'int')){
        unset($_REQUEST['page']);
        unset($_GET['page']);
    }
//    die('test');
    $_SESSION['state_filter'] = GETPOST('state_filter', 'int');
}
//$region_id = $_REQUEST['state_filter'];
$Area = $langs->trans('Area');
llxHeader("",$Area,"");
print_fiche_titre($Area);

//echo '<pre>';
//var_dump($_SESSION['state_filter']);
//echo '</pre>';
//die();
if(isset($_GET['id_usr'])&&!empty($_GET['id_usr'])){
    global $db;
    $sql = 'select lastname, respon_id from llx_user where rowid = '.$_GET['id_usr'];
    $res = $db->query($sql);
    $obj = $db->fetch_object($res);
    $username = $obj->lastname;
    $id_usr = $_GET['id_usr'];
    $respon_id = $obj->respon_id;
}else {
    $username = $user->lastname;
    $id_usr = $user->id;
}
//Шапка сторінки
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/responsibility/sale/area/header.php';
//Перелік контрагентів
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/responsibility/sale/area/customers.php';
if(strpos($_SERVER['QUERY_STRING'],'&page='))
    $link_page = $_SERVER['PHP_SELF'].'?'.substr($_SERVER['QUERY_STRING'],0,strpos($_SERVER['QUERY_STRING'],'&page='));
else
    $link_page = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
//echo '<pre>';
//var_dump($link_page);
//echo '</pre>';
//die();
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/static_content/layout/pagination.phtml';
//print '</div>';
//llxFooter();