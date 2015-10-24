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
$Permission = $langs->trans("Permission");
llxHeader("",$Permission,"");

$NewItem = $langs->trans('NewItem');
$Control = $langs->trans('Control');
$theme = $conf->theme;


print_fiche_titre($Permission);

ob_start();
include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/eldy/perms.html');
echo ob_get_clean();