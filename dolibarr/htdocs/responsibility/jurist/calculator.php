<?php
require $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/main.inc.php';
if(count($_POST)>0){
    $_SESSION['region_id'] = GETPOST('state_filter', 'int');
//    echo '<pre>';
//    var_dump($_SESSION['region_id']);
//    echo '</pre>';
//    die();
}
$Area = $langs->trans('Area');
llxHeader("",$Area,"");
print_fiche_titre($langs->trans('Calculator'));

//Шапка сторінки
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/responsibility/sale/calculator/header.php';
//Перелік контрагентів
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/responsibility/sale/calculator/customers.php';
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
llxFooter();