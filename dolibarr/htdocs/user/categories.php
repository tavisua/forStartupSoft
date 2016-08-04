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
    $update_user = new User($db);
    $update_user->info($_REQUEST['id']);
    $categories = $_REQUEST['contractors'];
    if(empty($categories))
        $categories = array();
//echo '<pre>';
//var_dump($categories);
//echo '</pre>';
//die();
    $sql = 'select case when fk_categories <> 0 then fk_categories else other_categories end fk_categories, rowid from llx_user_categories_contractor where fk_user='.$update_user->id;

    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $user_categories = array();
    while($obj = $db->fetch_object($res)){
        $user_categories[$obj->fk_categories] = $obj->rowid;
    }
    $inserted_values = array_keys($user_categories);
//var_dump($user_categories);
//    die($sql);
    foreach($inserted_values as $item){//Помічаю на видалення
        if(!in_array($item, $categories)){
            $sql = 'update llx_user_categories_contractor set active = 0, id_usr='.$user->id.
                ' where fk_user='.$update_user->id;
            if(is_numeric($item))
                $sql.=' and fk_categories='.$item.' and other_categories is null limit 1';
            else
                $sql.=" and other_categories='".$item."' and fk_categories = 0 limit 1";
//            echo '<pre>';
//            var_dump($sql);
//            echo '</pre>';
//            die($sql);
            $res = $db->query($sql);
            if(!$res)
                dol_print_error($db);
        }
    }
//    die();
    foreach($categories as $item){//Добавляю інші
        if(!isset($user_categories[$item]))
            $sql = 'insert into llx_user_categories_contractor(fk_user,'.(is_numeric($item)?'fk_categories':'other_categories').',active,id_usr)
            values('.$update_user->id.', '.(is_numeric($item)?$item:"'".$item."'").', 1, '.$user->id.')';
        else {
            $sql = 'update llx_user_categories_contractor set active = 1, id_usr=' . $user->id .
                ' where fk_user=' . $update_user->id;
            if (is_numeric($item))
                $sql .= ' and fk_categories=' . $item . ' and other_categories is null limit 1';
            else
                $sql .= " and other_categories='" . $item . "' and fk_categories = 0 limit 1";
        }
//        die($sql);
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
    }
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
$Title=$langs->trans("CategoriesContractors");
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
    $sql = "select distinct case when `responsibility_param`.`fx_category_counterparty` is null then `other_category` else `fx_category_counterparty` end `fx_category_counterparty`, `category_counterparty`.`name`
        from `responsibility_param`
        inner join (select rowid from `responsibility` where alias in ('".(empty($usr_tmp->respon_alias)?'null':$usr_tmp->respon_alias)."','".(empty($usr_tmp->respon_alias2)?'null':$usr_tmp->respon_alias2)."')) counter on counter.rowid=responsibility_param.fx_responsibility
        left join `category_counterparty` on `category_counterparty`.`rowid` = case when `responsibility_param`.`fx_category_counterparty` is null then `other_category` else `fx_category_counterparty` end
        where `category_counterparty`.`active` = 1
        or `responsibility_param`.`fx_category_counterparty` is null
        and `category_counterparty`.`name` is not null";
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $sql_contractors = 'select case when fk_categories <> 0 then fk_categories else other_categories end fk_categories, rowid from llx_user_categories_contractor where fk_user='.$id_usr.' and active = 1';
    $res_contractors = $db->query($sql_contractors);
    $contractors = array();
    while($obj = $db->fetch_object($res_contractors)){
        $contractors[]=$obj->fk_categories;
    }
//    die($sql);
    $out = '<select id="contractors" name="contractors[]" class="combobox" multiple="multiple" size="30" style="width: 80%">';
    while($obj = $db->fetch_object($res)){
        $selected = in_array($obj->fx_category_counterparty, $contractors);
        switch($obj->fx_category_counterparty){
            case 'users':{
                $name = 'Співробітники';
            }break;
            default:{
                $name = $obj->name;
            }
        }
        $out .= '<option value="'.$obj->fx_category_counterparty.'" '.($selected?'selected="selected"':'').'>'.$name.'</option>\n';
    }
    $out .= '</select>';
    return $out;
}
