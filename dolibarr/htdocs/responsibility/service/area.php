<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 04.11.2015
 * Time: 12:10
 */

require $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/main.inc.php';
require $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/responsibility/service/lib/function.php';
if(count($_POST)>0){
    $_SESSION['region_id'] = GETPOST('lineacitve', 'int');
//    echo '<pre>';
//    var_dump($_SESSION['region_id']);
//    echo '</pre>';
//    die();
}
//echo '<pre>';
//var_dump($_REQUEST);
//echo '</pre>';
//die();
$Area = $langs->trans('Area');
llxHeader("",$Area,"");
print_fiche_titre($Area);

if(isset($_GET['id_usr'])&&!empty($_GET['id_usr'])){
    global $db;
    $sql = 'select lastname from llx_user where rowid = '.$_GET['id_usr'];
    $res = $db->query($sql);
    $obj = $db->fetch_object($res);
    $username = $obj->lastname;
    $id_usr = $_GET['id_usr'];
}else {
    $id_usr = $user->id;
    $username = $user->lastname;
}

//Шапка сторінки
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/responsibility/service/area/header.php';
//Перелік контрагентів
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/responsibility/service/area/customers.php';
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