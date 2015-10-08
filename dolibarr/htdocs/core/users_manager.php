<?php
/**
 * Created by PhpStorm.
 * User: tavis
 * Date: 07.10.2015
 * Time: 13:18
 */
require '../main.inc.php';



global $hookmanager, $menumanager;


//$socstatic=new Societe($db);
$Tools = $langs->trans("Tools");
llxHeader("",$Tools,"");
$action = DOL_URL_ROOT.'/user/index.php';
$newtoken = $_SESSION['newtoken'];
$SearchUserTitle = $langs->trans("SearchAUser");
$var=false;
$bc[false]=' class="bg1"';
$bc[true]=' class="bg2"';
$bc_var = $bc[$var];
$Ref = $langs->trans("Ref");
$Action = $langs->trans("Action");
$SerchBtnTitle = $langs->trans("Search");
$Users = $langs->trans('Users');
$HostName = $_SERVER['ServerName'];
$UserGroup = $langs->trans('Groups');
$NewUserGroup = $langs->trans('NewGroup');
$NewUser = $langs->trans('NewUser');
$MenuUsersAndGroups = $langs->trans('MenuUsersAndGroups');
print_fiche_titre($MenuUsersAndGroups);
ob_start();
include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/eldy/users_manager.html');
echo ob_get_clean();