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
}

$Area = $langs->trans('Area');
llxHeader("",$Area,"");
print_fiche_titre($Area);
//print '<div>';
if(isset($_REQUEST["state_filter"]))
    $_SESSION["state_filter"] = $_REQUEST["state_filter"];
elseif(isset($_SESSION["state_filter"]))
    $_REQUEST["state_filter"]=$_SESSION["state_filter"];

//echo '<pre>';
//var_dump($user);
//echo '</pre>';
//die();
//Шапка сторінки
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/responsibility/corp_manager/area/header.php';
//Перелік контрагентів
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/responsibility/corp_manager/area/customers.php';
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