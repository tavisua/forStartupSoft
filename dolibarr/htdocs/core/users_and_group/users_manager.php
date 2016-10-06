<?php
/**
 * Created by PhpStorm.
 * User: tavis
 * Date: 07.10.2015
 * Time: 13:18
 */
require '../../main.inc.php';

//var_dump($_SERVER["PHP_SELF"]);
//die();

global $user,$hookmanager, $menumanager;
//echo '<pre>';
//var_dump($user->rights->user->user->creer);
//echo '</pre>';
//die();
if (! $user->rights->user->user->lire) accessforbidden();

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
$NewUserGroup = $langs->trans('NewGroup');
$NewUser = $langs->trans('NewUser');
$MenuUsersAndGroups = $langs->trans('MenuUsersAndGroups');
$NewItem = $langs->trans('NewItem');
$Control = $langs->trans('Control');
$theme = $conf->theme;


//$table = 'test11';
print_fiche_titre($MenuUsersAndGroups);

$TableParam = array();
$ColParam['title']=$langs->trans('Login');
$ColParam['width']='180';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=trim($langs->trans('LastName'));
$ColParam['width']='120';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('FirstName');
$ColParam['width']='150';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

//$ColParam['title']=$langs->trans('LastConnexion');
//$ColParam['width']='70';
//$ColParam['align']='';
//$ColParam['class']='';
//$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('OfficePhone');
$ColParam['width']='100';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('Skype');
$ColParam['width']='120';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('SubDivision');
$ColParam['width']='180';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['sourcetable']='subdivision';
$ColParam['detailfield']='subdiv_id';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('UsersGroup');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['sourcetable']='llx_usergroup';
$ColParam['detailfield']='usergroup_id';
$TableParam[]=$ColParam;

unset($ColParam['sourcetable']);
unset($ColParam['detailfield']);
$ColParam['title']=$langs->trans('Active');
$ColParam['width']='70';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$tablename='llx_user';

$sql='select `'.$tablename.'`.rowid, `'.$tablename.'`.login, `'.$tablename.'`.lastname, `'.$tablename.'`.firstname,  `'.$tablename.'`.`office_phone`, `'.$tablename.'`.`skype`,
`subdivision`.`name` as s_subdivision_name, `llx_usergroup`.`nom` as s_llx_usergroup_nom, `'.$tablename.'`.active
from `'.$tablename.'` left join `subdivision` on `'.$tablename.'`.`subdiv_id`= `subdivision`.rowid
left join `llx_usergroup` on `'.$tablename.'`.`usergroup_id`=`llx_usergroup`.rowid
where 1

order by login';
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/DBManager/dbBuilder.php';
$dbBuilder = new dbBuilder();
$table = $dbBuilder->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme);
$new_link = "http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/DBManager/dbManager.php?new=1&tablename='".$tablename."'";
ob_start();
include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/users_manager.html');
echo ob_get_clean();
llxFooter();