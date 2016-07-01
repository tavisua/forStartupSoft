<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 16.11.2015
 * Time: 13:28
 */
require '../main.inc.php';
$Tools = $langs->trans("Tools");
$KindStreet = $langs->trans('KindOfStreet');
$NewItem = $langs->trans('NewItem');
$Control = $langs->trans('Control');
$theme = $conf->theme;

llxHeader("",$KindStreet,"");
print_fiche_titre($KindStreet);
$TableParam = array();
$ColParam['title']=$langs->trans('Name');
$ColParam['width']='300';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('Active');
$ColParam['width']='100';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$tablename = 'kindstreet';
$sql='select rowid, name, active from '.$tablename.' order by name';
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/DBManager/dbBuilder.php';
$dbBuilder = new dbBuilder();
if(!isset($_REQUEST['sortfield']))
    $table = $dbBuilder->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme);
else
    $table = $dbBuilder->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme, $_REQUEST['sortfield'], $_REQUEST['sortorder']);
$new_link = "http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/DBManager/dbManager.php?new=1&tablename=".$tablename;
ob_start();

include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$theme.'/kindstreet.html');

echo ob_get_clean();