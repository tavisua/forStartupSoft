<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 24.10.2015
 * Time: 19:56
 */
require '../../main.inc.php';

//$socstatic=new Societe($db);
$Tools = $langs->trans("Tools");
$Permission = $langs->trans("Permissions");
llxHeader("",$Permission,"");

$NewItem = $langs->trans('NewItem');
$Control = $langs->trans('Control');
$theme = $conf->theme;

print_fiche_titre($Permission);

$TableParam = array();
$ColParam['title']=$langs->trans('Module');
$ColParam['width']='150';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('Permission');
$ColParam['width']='250';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('Description');
$ColParam['width']='750';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('ByDefault');
$ColParam['width']='150';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('Active');
$ColParam['width']='50';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$tablename = 'rights';

$sql = "select rowid, module,  perms, title,bydefault, active from `".$tablename."`";
$sql .= " order by module, rowid";

include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/DBManager/dbBuilder.php';
$db = new dbBuilder();
$table = $db->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme);

ob_start();
include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/perms.html');
echo ob_get_clean();