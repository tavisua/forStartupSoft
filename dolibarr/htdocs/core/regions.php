<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 26.10.2015
 * Time: 20:20
 */
//var_dump($_SERVER['PHP_SELF']);
//die();
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

$ColParam['title']=$langs->trans('Param');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['hidden']='regions_param';
$ColParam['sourcetable']='classifycation';
$ColParam['detailfield']='classifycation_id';
$TableParam[]=$ColParam;
unset($ColParam['hidden']);
unset($ColParam['sourcetable']);
unset($ColParam['detailfield']);

$ColParam['title']=$langs->trans('Active');
$ColParam['width']='100';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$tablename='regions';
$sql='select `'.$tablename.'`.rowid, `'.$tablename.'`.name, states.name s_states_name, null, `'.$tablename.'`.active  from `'.$tablename.'` left join states on `'.$tablename.'`.`state_id` = `states`.rowid order by `'.$tablename.'`.name';
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/DBManager/dbBuilder.php';
$db = new dbBuilder();
if(!isset($_REQUEST['sortfield']))
    $table = $db->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme);
else
    $table = $db->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme, $_REQUEST['sortfield'], $_REQUEST['sortorder']);
$new_link = "http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/DBManager/dbManager.php?new=1&tablename='".$tablename."'";
ob_start();
include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/regions.html');
echo ob_get_clean();
llxFooter();