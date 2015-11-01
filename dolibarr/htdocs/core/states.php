<?php
/**
 * Created by PhpStorm.
 * User: tavis
 * Date: 14.10.2015
 * Time: 9:57
 */
require '../main.inc.php';
$Tools = $langs->trans("Tools");
$Country = $langs->trans('Country');
$Region = $langs->trans('Region');
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

$ColParam['title']=$langs->trans('Country');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['sourcetable']='countries';
$ColParam['detailfield']='country_id';
$TableParam[]=$ColParam;

unset($ColParam['sourcetable']);
unset($ColParam['detailfield']);

$ColParam['title']=$langs->trans('Active');
$ColParam['width']='100';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$tablename='states';
$sql='select `'.$tablename.'`.rowid, `'.$tablename.'`.name, countries.name s_countries_name, `'.$tablename.'`.active  from `'.$tablename.'` left join countries on `'.$tablename.'`.`country_id` = `countries`.rowid order by `'.$tablename.'`.name';
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/DBManager/dbBuilder.php';
$db = new dbBuilder();
if(!isset($_REQUEST['sortfield']))
    $table = $db->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme);
else
    $table = $db->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme, $_REQUEST['sortfield'], $_REQUEST['sortorder']);
$new_link = "http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/DBManager/dbManager.php?new=1&tablename='".$tablename."'";
ob_start();
include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/states.html');
echo ob_get_clean();
llxFooter();