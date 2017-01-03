<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 04.04.2016
 * Time: 14:17
 */

require '../main.inc.php';
if(isset($_POST['action'])){

}
require_once DOL_DOCUMENT_ROOT.'/core/lib/usergroups.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
//echo '<pre>';
//var_dump($_POST);
//echo '</pre>';
//die();
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'update'){
    global $user;
    $page = ((!isset($_REQUEST['page'])||empty($_REQUEST['page']))?'1':$_REQUEST['page']);
    $update_user = new User($db);
    $update_user->info($_REQUEST['id']);
//    $lineactive = explode(',', $_REQUEST['values']);
//    select_lineaction
    $lineactive = $_REQUEST["select_lineaction"];

    $sql = 'select fk_lineactive, rowid, active from llx_user_lineactive where fk_user='.$update_user->id.' and (page='.$page.($page == 1? ' or page is null':'').')';
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $user_lineactive = array();
    while($obj = $db->fetch_object($res)){
        $user_lineactive[$obj->fk_lineactive] = array($obj->rowid, $obj->active);
    }
    $inserted_values = array_keys($user_lineactive);
//    echo '<pre>';
//    var_dump($lineactive);
//    echo '</pre>';
//    die();
    foreach($inserted_values as $item){//Помічаю на видалення
        if(!in_array($item, $lineactive)){
            $sql = 'update llx_user_lineactive set active = 0, id_usr='.$user->id.
                ' where fk_user='.$update_user->id.' and active = 1 and fk_lineactive='.$item.' limit 1';
//            die($sql);
            $res = $db->query($sql);
            if(!$res)
                dol_print_error($db);
        }
    }
    foreach($lineactive as $item){//Добавляю інші
        if(!isset($user_lineactive[$item]))
            $sql = 'insert into llx_user_lineactive(fk_user,fk_lineactive,active,id_usr)
            values('.$update_user->id.', '.$item.',  1, '.$user->id.')';
        else
            $sql = 'update llx_user_lineactive set active = 1, id_usr='.$user->id.
                ' where fk_user='.$update_user->id.' and fk_lineactive='.$item.' limit 1';
//        die($sql);
        $res = $db->query($sql);
        if(!$res) {
            dol_print_error($db);
//echo '<pre>';
//var_dump($lineactive, $user_lineactive);
//echo '</pre>';
//            die($sql);
        }
    }

//die();
}
$langs->load("users");
$langs->load("admin");
//$Tools = $langs->trans("Tools");

$id=GETPOST('id', 'int');
$action=GETPOST('action', 'alpha');
$confirm=GETPOST('confirm', 'alpha');
$module=GETPOST('module', 'alpha');
$rights=GETPOST('areas', 'int');
$entity=(GETPOST('entity','int')?GETPOST('entity','int'):$conf->entity);
$Title=$langs->trans("LineActive");
llxHeader('',$Title);
print_fiche_titre($Title);
$user = new User($db);
$user->info($id);

$form = new Form($db);

$contractors = Contractors($id);

print '<a href="/dolibarr/htdocs/core/tools.php?mainmenu=tools&idmenu=5223">'.$langs->trans("Tools").'</a>  /
    <a href="/dolibarr/htdocs/core/users_and_group/groups_manager.php?mainmenu=tools">'.$langs->trans('MenuUsersAndGroups').'</a>/
    <a href="/dolibarr/htdocs/core/users_and_group/groups_manager.php?mainmenu=tools">'.$langs->trans('Users').'</a> /
    <a href="/dolibarr/htdocs/user/card.php?id='.$id.'&mainmenu=tools">'.$user->lastname.' '.$user->firstname.'</a>';
print '<div style="width: 100%; height: 20px"></div>';
print '<div class="tabPage" style="width: 530px">';
print '<form id="lineaction" action="" method="post" style="width: 550px">';
print '<input id="id" name="id" value="'.$user->id.'" type="hidden">';
print '<input id="mainmenu" name="mainmenu" value="'.$_REQUEST['mainmenu'].'" type="hidden">';
print '<input id="idmenu" name="idmenu" value="'.$_REQUEST['idmenu'].'" type="hidden">';
print '<input id="page" name="page" value="'.$page.'" type="hidden">';
//print '<input id="values" name="values" value="" type="hidden">';
print '<input id="action" name="action" value="update" type="hidden">';
print $contractors;
print '</br>';
print '<input type="submit" value="Зберегти">';
print '</form>';
print '</div>';
//print "<script>
//    $(document).ready(function(){
//        $('select#contractors').on('change', SelectContractors);
//    })
//    function SelectContractors(){
//        $('#values').val($('select#contractors').val());
//    }
//</script>";

exit();

function Contractors($id_usr){
    global $db;
    $usr_tmp = new User($db);
    $usr_tmp->fetch($id_usr);
    $sql = "select rowid, `name` from llx_c_lineactive_marketing where active = 1";
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $sql_contractors = 'select fk_lineactive from llx_user_lineactive where fk_user = '.$id_usr.' and active = 1';
    $res_contractors = $db->query($sql_contractors);
    $contractors = array();
    while($obj = $db->fetch_object($res_contractors)){
        $contractors[]=$obj->fk_lineactive;
    }
//    die($sql);
    $out = '<select id="select_lineaction" name="select_lineaction[]" class="combobox" multiple="multiple" size="30" style="width: 80%">';
    while($obj = $db->fetch_object($res)){
        $selected = in_array($obj->rowid, $contractors);
        switch($obj->rowid){
            case 'users':{
                $name = 'Співробітники';
            }break;
            default:{
                $name = $obj->name;
            }
        }
        $out .= '<option value="'.$obj->rowid.'" '.($selected?'selected="selected"':'').'>'.$name.'</option>\n';
    }
    $out .= '</select>';
    return $out;
}
