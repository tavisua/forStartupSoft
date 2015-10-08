<?php

require '../main.inc.php';

$langs->load("companies");
$langs->load("other");

// Security check
$socid=0;
if ($user->societe_id > 0) $socid=$user->societe_id;



/*
 * View
 */

$socstatic=new Societe($db);
$Tools = $langs->trans("Tools");
$aPost = $langs->trans("aPost");
$Control = $langs->trans('Control');
$theme = $conf->theme;
$NewItem = $langs->trans('NewItem');

llxHeader("",$Tools,"");

print_fiche_titre($aPost);

$TableParam = array();
$ColParam['title']=$langs->trans('Name');
$ColParam['width']='300';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('Active');
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$sql='select rowid, postname, active from llx_post order by postname';
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/DBManager/dbBuilder.php';
$db = new dbBuilder();
$table = $db->fShowTable($TableParam, $sql, "'llx_post'", $conf->theme);
$new_link = "http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/DBManager/dbManager.php?new=1&tablename='llx_post'";
ob_start();

include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$theme.'/post_manager.html');

echo ob_get_clean();