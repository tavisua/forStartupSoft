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


$ColParam['title']='email';
$ColParam['width']='150';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='pass';
$ColParam['width']='20';
$ColParam['align']='';
$ColParam['class']='pass';
$TableParam[]=$ColParam;
unset($ColParam['sourcetable']);
unset($ColParam['detailfield']);

$ColParam['title']=$langs->trans('Active');
$ColParam['width']='100';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;
$tablename='subdivision';
$sql='select subdivision.rowid, subdivision.name, states.name s_states_name, subdivision.email, subdivision.pass, subdivision.active
  from '.$tablename.' left join `states` on `'.$tablename.'`.`state_id` = `states`.rowid order by subdivision.name';
//die($sql);
//$sql = "select -1 rowid, null subdivision_name, null s_states_name, null `email`, null `active`
//union
//select subdivision.rowid, subdivision.name subdivision_name, states.name s_states_name, subdivision.email, subdivision.active
//from subdivision left join `states` on `subdivision`.`state_id` = `states`.rowid order by subdivision_name";
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/DBManager/dbBuilder.php';
$dbBuilder = new dbBuilder();
if(!isset($_REQUEST['sortfield']))
    $table = $dbBuilder->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme);
else
    $table = $dbBuilder->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme, $_REQUEST['sortfield'], $_REQUEST['sortorder']);
$new_link = "http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/DBManager/dbManager.php?new=1&tablename='".$tablename."'";
ob_start();
include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/subdivision.html');
echo ob_get_clean();
llxFooter();