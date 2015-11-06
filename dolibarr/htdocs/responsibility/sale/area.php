<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 04.11.2015
 * Time: 12:10
 */
require $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/main.inc.php';
$Area = $langs->trans('Area');
llxHeader("",$Area,"");
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/responsibility/sale/header.php';
llxFooter();