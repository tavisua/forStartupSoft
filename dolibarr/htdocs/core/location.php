<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 20.11.2015
 * Time: 14:56
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
$ColParam['title']=$langs->trans('Zip');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('Location');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('Country');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['sourcetable']='countries';
$ColParam['detailfield']='fk_country';
$CountryParam = explode(':', $conf->global->MAIN_INFO_SOCIETE_COUNTRY);
$ColParam['selrow']=$CountryParam[0];
$TableParam[]=$ColParam;
unset($ColParam['selrow']);
$ColParam['title']=$langs->trans('Region');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['sourcetable']='states';
$ColParam['detailfield']='fk_state';
$TableParam[]=$ColParam;
unset($ColParam['sourcetable']);
unset($ColParam['detailfield']);

$ColParam['title']=$langs->trans('Area');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['sourcetable']='regions';
$ColParam['detailfield']='fk_region';
$TableParam[]=$ColParam;
unset($ColParam['sourcetable']);
unset($ColParam['detailfield']);


$ColParam['title']=$langs->trans('Active');
$ColParam['width']='100';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$tablename='llx_c_ziptown';
$sql='select `'.$tablename.'`.`rowid`, `'.$tablename.'`.`zip`, `'.$tablename.'`.`nametown`,
`countries`.`label` as s_countries_label, `states`.`name` as s_states_name, `regions`.`name` as s_regions_name,
`'.$tablename.'`.active
from `'.$tablename.'`
left join `countries` on `'.$tablename.'`.`fk_country`=`countries`.`rowid`
left join `states` on `'.$tablename.'`.`fk_state`=`states`.`rowid`
left join `regions` on `'.$tablename.'`.`fk_region`=`regions`.`rowid`
order by nametown limit 50';
//die($sql);
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/DBManager/dbBuilder.php';
$db = new dbBuilder();
if(!isset($_REQUEST['sortfield']))
    $table = $db->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme);
else
    $table = $db->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme, $_REQUEST['sortfield'], $_REQUEST['sortorder']);
$new_link = "http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/DBManager/dbManager.php?new=1&tablename='".$tablename."'";
ob_start();
include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/location.html');
echo ob_get_clean();
llxFooter();