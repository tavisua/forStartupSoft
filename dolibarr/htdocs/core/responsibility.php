<?php
/**
 * Created by PhpStorm.
 * User: tavis
 * Date: 12.10.2015
 * Time: 17:35
 */
require '../main.inc.php';
$Tools = $langs->trans("Tools");
$SphereOfResponsibility = $langs->trans('SphereOfResponsibility');
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
$ColParam['width']='100';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$sql='select rowid, name, active from responsibility order by name';
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/DBManager/dbBuilder.php';
$db = new dbBuilder();
if(!isset($_REQUEST['sortfield']))
    $table = $db->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme);
else
    $table = $db->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme, $_REQUEST['sortfield'], $_REQUEST['sortorder']);
$new_link = "http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/DBManager/dbManager.php?new=1&tablename='responsibility'";
ob_start();

include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/responsibility.html');
echo ob_get_clean();
llxFooter();
