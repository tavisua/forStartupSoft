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


$table = 'test11';
print_fiche_titre($MenuUsersAndGroups);

$TableParam = array();
$ColParam['title']=$langs->trans('Login');
$ColParam['width']='150';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('LastName');
$ColParam['width']='150';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('FirstName');
$ColParam['width']='150';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('SubDisivion');
$ColParam['width']='150';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['sourcetable']='subdivision';
$ColParam['detailfield']='subdiv_id';
$TableParam[]=$ColParam;

unset($ColParam['sourcetable']);
unset($ColParam['detailfield']);
$ColParam['title']=$langs->trans('Active');
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$tablename='users';


ob_start();
include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/eldy/users_manager.html');
echo ob_get_clean();