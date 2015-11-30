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
$Area = $langs->trans('Area');
llxHeader("",$Area,"");
print_fiche_titre($Area);
//Шапка сторінки
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/responsibility/sale/header.php';
//Перелік контрагентів
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/responsibility/sale/customers.php';
llxFooter();