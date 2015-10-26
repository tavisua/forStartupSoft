<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 26.10.2015
 * Time: 20:20
 */
require '../main.inc.php';
$Tools = $langs->trans("Tools");
$Country = $langs->trans('Country');
$Area = $langs->trans('Area');
$NewItem = $langs->trans('NewItem');
$Control = $langs->trans('Control');
$theme = $conf->theme;

llxHeader("",$Region,"");
print_fiche_titre($Region);
$TableParam = array();
$ColParam['title']=$langs->trans('Name');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('Region');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['sourcetable']='states';
$ColParam['detailfield']='state_id';
$TableParam[]=$ColParam;

unset($ColParam['sourcetable']);
unset($ColParam['detailfield']);
$ColParam['title']=$langs->trans('Active');
$ColParam['width']='30';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$tablename='regions';
$sql='select `'.$tablename.'`.rowid, `'.$tablename.'`.name, states.name s_states_name, `'.$tablename.'`.active  from `'.$tablename.'` left join states on `'.$tablename.'`.`state_id` = `states`.rowid order by `'.$tablename.'`.name';
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/DBManager/dbBuilder.php';
$db = new dbBuilder();
$table = $db->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme);
$new_link = "http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/DBManager/dbManager.php?new=1&tablename='".$tablename."'";
ob_start();
include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/regions.html');
echo ob_get_clean();
llxFooter();