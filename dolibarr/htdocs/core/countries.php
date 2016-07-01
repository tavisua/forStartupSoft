<?php
/**
 * Created by PhpStorm.
 * User: tavis
 * Date: 13.10.2015
 * Time: 16:51
 */
require '../main.inc.php';
$Tools = $langs->trans("Tools");
$Country = $langs->trans('Country');
$NewItem = $langs->trans('NewItem');
$Control = $langs->trans('Control');
$theme = $conf->theme;

llxHeader("",$Country,"");
print_fiche_titre($Country);
$TableParam = array();
$ColParam['title']=$langs->trans('Name');
$ColParam['width']='300';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('Code');
$ColParam['width']='80';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('CodeIso');
$ColParam['width']='100';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('PhoneCode');
$ColParam['width']='120';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('Active');
$ColParam['width']='100';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;
$tablename='countries';
$sql='select rowid, label, code, code_iso, phonecode, active from '.$tablename.' where active=1 order by label';
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/DBManager/dbBuilder.php';
$dbBuilder = new dbBuilder();
if(!isset($_REQUEST['sortfield']))
    $table = $dbBuilder->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme);
else
    $table = $dbBuilder->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme, $_REQUEST['sortfield'], $_REQUEST['sortorder']);
$new_link = "http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/DBManager/dbManager.php?new=1&tablename='".$tablename."'";
ob_start();
include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/countries.html');
echo ob_get_clean();
llxFooter();