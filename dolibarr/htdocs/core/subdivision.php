<?php
/**
 * Created by PhpStorm.
 * User: tavis
 * Date: 13.10.2015
 * Time: 17:34
 */
require '../main.inc.php';
$Tools = $langs->trans("Tools");
$SubDisivion = $langs->trans('SubDisivion');
$NewItem = $langs->trans('NewItem');
$Control = $langs->trans('Control');
$theme = $conf->theme;

llxHeader("",$SubDisivion,"");
print_fiche_titre($SubDisivion);
$TableParam = array();
$ColParam['title']=$langs->trans('Name');
$ColParam['width']='350';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$Region = $langs->trans('Region');
$ColParam['title']=$Region;
$ColParam['width']='300';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['sourcetable']='regions';
$ColParam['detailfield']='region_id';
$TableParam[]=$ColParam;

unset($ColParam['sourcetable']);
unset($ColParam['detailfield']);

$ColParam['title']=$langs->trans('Active');
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;
$tablename='subdivision';
$sql='select subdivision.rowid, subdivision.name, regions.name s_regions_name, subdivision.active  from '.$tablename.' left join `regions` on `'.$tablename.'`.`region_id` = `regions`.rowid order by subdivision.name';
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/DBManager/dbBuilder.php';
$db = new dbBuilder();
$table = $db->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme);
$new_link = "http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/DBManager/dbManager.php?new=1&tablename='".$tablename."'";
ob_start();
include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/subdivision.html');
echo ob_get_clean();
llxFooter();