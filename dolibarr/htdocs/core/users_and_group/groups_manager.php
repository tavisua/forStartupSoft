<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 22.10.2015
 * Time: 17:56
 */
require '../../main.inc.php';



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
$NewItem = $langs->trans('NewItem');
$Control = $langs->trans('Control');
$theme = $conf->theme;


$tablename = 'llx_usergroup';
print_fiche_titre($MenuUsersAndGroups);

$TableParam = array();
$ColParam['title']=$langs->trans('Name');
$ColParam['width']='250';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('Description');
$ColParam['width']='350';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('Permissions');
$ColParam['width']='250';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['action']='/dolibarr/htdocs/user/group/perms.php?mainmenu=tools';
$ColParam['param']='id';
$ColParam['icon_src']='/dolibarr/htdocs/theme/'.$conf->theme.'/img/permission_14.png';
$TableParam[]=$ColParam;

unset($ColParam['action']);
unset($ColParam['param']);
unset($ColParam['icon_src']);
$ColParam['title']=$langs->trans('Active');
$ColParam['width']='70';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$sql = 'select rowid, nom, note, " " as permission, active from `'.$tablename.'` order by nom';
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/DBManager/dbBuilder.php';
$db = new dbBuilder();
$table = $db->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme);
include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/eldy/groups_manager.html');
llxFooter();