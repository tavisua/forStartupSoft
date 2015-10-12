<?php
/**
 * Created by PhpStorm.
 * User: tavis
 * Date: 12.10.2015
 * Time: 18:55
 */
require '../main.inc.php';
$Tools = $langs->trans("Tools");
$CategoryCustomer = $langs->trans('CategoryCustomer');
$NewItem = $langs->trans('NewItem');
$Control = $langs->trans('Control');
$theme = $conf->theme;

llxHeader("",$SphereOfResponsibility,"");
print_fiche_titre($title);
$TableParam = array();
$ColParam['title']=$langs->trans('Name');
$ColParam['width']='300';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('Active');
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;
$tablename='category_counterparty';
$sql='select rowid, name, active from '.$tablename.' order by name';
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/DBManager/dbBuilder.php';
$db = new dbBuilder();
$table = $db->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme);
$new_link = "http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/DBManager/dbManager.php?new=1&tablename='".$tablename."'";
ob_start();
include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/category_counterparty.html');
echo ob_get_clean();
llxFooter();