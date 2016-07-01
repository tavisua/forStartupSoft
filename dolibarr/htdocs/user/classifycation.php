<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 31.10.2015
 * Time: 12:52
 */
require '../main.inc.php';
$Tools = $langs->trans("Tools");
//$Country = $langs->trans('Country');
//$Region = $langs->trans('Region');
$SphereOfResponsibility = $langs->trans('SphereOfResponsibility');
$NewItem = $langs->trans('NewItem');
$Control = $langs->trans('Control');
$theme = $conf->theme;

llxHeader("",$SphereOfResponsibility,"");
print_fiche_titre($SphereOfResponsibility);
$TableParam = array();
$ColParam['title']=$langs->trans('Name');
$ColParam['width']='250';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$SphereOfResponsibility;
$ColParam['width']='170';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['sourcetable']='responsibility';
$ColParam['detailfield']='responsibility_id';
$TableParam[]=$ColParam;
unset($ColParam['sourcetable']);
unset($ColParam['detailfield']);

$ColParam['title']=$langs->trans('Calc1');
$ColParam['width']='100';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('Active');
$ColParam['width']='100';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$tablename='classifycation';

$sql='select `'.$tablename.'`.rowid, `'.$tablename.'`.name, responsibility.name s_responsibility_name, `'.$tablename.'`.`calc`, `'.$tablename.'`.`active`  from `'.$tablename.'` left join responsibility on `'.$tablename.'`.`responsibility_id` = `responsibility`.rowid order by `'.$tablename.'`.name';
//die($sql);
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/DBManager/dbBuilder.php';
$dbBuilder = new dbBuilder();
if(!isset($_REQUEST['sortfield']))
    $table = $dbBuilder->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme);
else
    $table = $dbBuilder->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme, $_REQUEST['sortfield'], $_REQUEST['sortorder']);
$new_link = "http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/DBManager/dbManager.php?new=1&tablename='".$tablename."'";
ob_start();
include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/classifycation.html');
echo ob_get_clean();
llxFooter();
