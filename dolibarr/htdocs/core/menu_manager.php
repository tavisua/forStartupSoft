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
$Control = $langs->trans('Control');
$MenuManager = $langs->trans("MenuManager");
$NewItem = $langs->trans('NewItem');
llxHeader("",$Tools,"");

$text=$langs->trans("MenuManager");

print_fiche_titre($text);

// Show description of content

$TableParam = array();
$ColParam['title']=$langs->trans('Name');
$ColParam['width']='120';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='Отображение в браузере';
$ColParam['width']='170';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='URL';
$ColParam['width']='450';
$ColParam['align']='left';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='Порядковый номер';
$ColParam['width']='105';
$ColParam['align']='left';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='Видимость';
$ColParam['width']='100';
$ColParam['align']='left';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='Активное';
$ColParam['width']='80';
$ColParam['align']='left';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='20';
$ColParam['align']='left';
$ColParam['class']='';
$TableParam[]=$ColParam;

$sql = 'SELECT m.rowid, m.mainmenu, m.titre, m.url, m.position, m.show, m.active
FROM llx_menu as m
WHERE m.fk_menu = 0
AND m.usertype IN (0,2) ORDER BY m.position, m.rowid';
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/DBManager/dbBuilder.php';
$db = new dbBuilder();
$table = $db->fShowTable($TableParam, $sql, "'llx_menu'", $conf->theme);
$new_link = "http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/DBManager/dbManager.php?new=1&tablename='llx_menu'";
ob_start();

include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/admin/tools/template/menu_manager.html');

echo ob_get_clean();
llxFooter();