<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 19.01.2016
 * Time: 9:54
 */
require '../main.inc.php';
global $langs;
$CalculatorSetup = $langs->trans("CalculatorSetup");

llxHeader("",$CalculatorSetup,"");
print_fiche_titre($CalculatorSetup);

$TableParam = array();
$ColParam['title']=$langs->trans('Theme');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('SphereOfResponsibility');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='small_size';
$ColParam['sourcetable']='responsibility';
$ColParam['detailfield']='respon_id';
$TableParam[]=$ColParam;
unset($ColParam['sourcetable']);
unset($ColParam['detailfield']);

$ColParam['title']=$langs->trans('Active');
$ColParam['width']='70';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$sql = "select `calculator_theme`.`rowid`, `calculator_theme`.`theme`, `responsibility`.`name` as s_responsibility_name, `calculator_theme`.`active` from `calculator_theme`
left join `responsibility` on `calculator_theme`.`respon_id` = `responsibility`.`rowid`";
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/DBManager/dbBuilder.php';
if(!isset($_REQUEST['sortfield']))
    $table = $dbBuilder->fShowTable($TableParam, $sql, "'calculator_theme'", $conf->theme);
else
    $table = $dbBuilder->fShowTable($TableParam, $sql, "'calculator_theme'", $conf->theme, $_REQUEST['sortfield'], $_REQUEST['sortorder']);
$new_link = "http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/DBManager/dbManager.php?new=1&tablename='calculator_theme'";
ob_start();
include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/calculator/setup.html');
echo ob_get_clean();
llxFooter();