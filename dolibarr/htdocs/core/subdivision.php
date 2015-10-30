<?php
/**
 * Created by PhpStorm.
 * User: tavis
 * Date: 13.10.2015
 * Time: 17:34
 */
require '../main.inc.php';
$Tools = $langs->trans("Tools");
$SubDisivion = $langs->trans('SubDivision');
$NewItem = $langs->trans('NewItem');
$Control = $langs->trans('Control');
$theme = $conf->theme;

llxHeader("",$SubDisivion,"");
print_fiche_titre($SubDisivion);
$TableParam = array();
$ColParam['title']=$langs->trans('Name');
$ColParam['width']='150';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$Region = $langs->trans('Region');
$ColParam['title']=$Region;
$ColParam['width']='150';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['sourcetable']='states';
$ColParam['detailfield']='state_id';
$TableParam[]=$ColParam;

unset($ColParam['sourcetable']);
unset($ColParam['detailfield']);

$ColParam['title']=$langs->trans('Active');
$ColParam['width']='50';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;
$tablename='subdivision';
$sql='select subdivision.rowid, subdivision.name, states.name s_states_name, subdivision.active  from '.$tablename.' left join `states` on `'.$tablename.'`.`state_id` = `states`.rowid order by subdivision.name';
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/DBManager/dbBuilder.php';
$db = new dbBuilder();
$table = $db->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme);
$new_link = "http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/DBManager/dbManager.php?new=1&tablename='".$tablename."'";
ob_start();
include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/subdivision.html');
echo ob_get_clean();
llxFooter();