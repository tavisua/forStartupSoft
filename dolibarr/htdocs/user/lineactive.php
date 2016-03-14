<?php
/* Copyright (C) 2002-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2002-2003 Jean-Louis Bergamo   <jlb@j1b.org>
 * Copyright (C) 2004-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Eric Seigne          <eric.seigne@ryxeo.com>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2012      Juanjo Menent        <jmenent@2byte.es>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *       \file       htdocs/user/perms.php
 *       \brief      Onglet user et permissions de la fiche utilisateur
 */

require '../main.inc.php';
if(isset($_POST['action']) && $_POST['action']=='update'){

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
    $lineactive = explode(',', $_REQUEST['values']);
    $sql = 'select fk_lineactive, rowid from llx_user_lineactive where fk_user='.$update_user->id.' and page='.$page;
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $user_lineactive = array();
    while($obj = $db->fetch_object($res)){
        $user_lineactive[$obj->fk_lineactive] = $obj->rowid;
    }
    $inserted_values = array_keys($user_lineactive);

    foreach($inserted_values as $item){//Помічаю на видалення
        if(!in_array($item, $lineactive)){
            $sql = 'update llx_user_lineactive set active = 0, id_usr='.$user->id.
                ' where fk_user='.$update_user->id.' and fk_lineactive='.$item.' and page='.$page.' limit 1';
//            die($sql);
            $res = $db->query($sql);
            if(!$res)
                dol_print_error($db);
        }
    }
    foreach($lineactive as $item){//Добавляю інші
        if(!isset($user_lineactive[$item]))
            $sql = 'insert into llx_user_lineactive(fk_user,fk_lineactive,page,active,id_usr)
            values('.$update_user->id.', '.$item.', '.$_REQUEST['page'].', 1, '.$user->id.')';
        else
            $sql = 'update llx_user_lineactive set active = 1, id_usr='.$user->id.
                ' where fk_user='.$update_user->id.' and fk_lineactive='.$item.' and page='.$page.' limit 1';
//        die($sql);
        $res = $db->query($sql);
//        if(!$res)
//            dol_print_error($db);
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
$Title=$langs->trans("PropLineActive");
llxHeader('',$Title);
print_fiche_titre($Title);
$user = new User($db);
$user->info($id);

$form = new Form($db);
//echo '<pre>';
//var_dump($user);
//echo '</pre>';
//die();
$page = ((!isset($_REQUEST['page'])||empty($_REQUEST['page']))?'1':$_REQUEST['page']);
$lineactive=array();
$sql = 'select fk_lineactive from llx_user_lineactive where fk_user = '.$id.' and page='.$page.' and active=1';
$res = $db->query($sql);
if(!$res)
    dol_print_error($db);
if($db->num_rows($res)>0)
    while($obj = $db->fetch_object($res)){
        $lineactive[]=$obj->fk_lineactive;
    }

$setting = $user->getregions($id);
if(count($setting) == 0)
    $setting[]=-1;
print '<a href="/dolibarr/htdocs/core/tools.php?mainmenu=tools&idmenu=5223">'.$langs->trans("Tools").'</a>  /
    <a href="/dolibarr/htdocs/core/users_and_group/groups_manager.php?mainmenu=tools">'.$langs->trans('MenuUsersAndGroups').'</a>/
    <a href="/dolibarr/htdocs/core/users_and_group/groups_manager.php?mainmenu=tools">'.$langs->trans('Users').'</a> /
    <a href="/dolibarr/htdocs/user/card.php?id='.$id.'&mainmenu=tools">'.$user->lastname.' '.$user->firstname.'</a>';
print '<div style="width: 100%; height: 20px"></div>';
print '<div class="tabPage" style="width: 530px">';

print '<div class="tabs" data-type="horizontal" data-role="controlgroup">
             <div class="inline-block tabsElem tabsElemActive">
                 <a id="first" class="'.($page==1?'tabpriceactive':'').' tab inline-block" href="'.$_SERVER["PHP_SELF"].'?mainmenu='.$_REQUEST["mainmenu"].'&idmenu='.$_REQUEST["idmenu"].'&id='.$_REQUEST["id"].'&page=1" data-role="button">Ціле</a>
             </div>
             <div class="inline-block tabsElem">
                 <a id="second" class="'.($page==2?'tabpriceactive':'').' tab inline-block" href="'.$_SERVER["PHP_SELF"].'?mainmenu='.$_REQUEST["mainmenu"].'&idmenu='.$_REQUEST["idmenu"].'&id='.$_REQUEST["id"].'&page=2" data-role="button">Унікальні з/ч</a>
             </div>
             <div class="inline-block tabsElem">
                 <a id="thirth" class="'.($page==3?'tabpriceactive':'').' tab inline-block" href="'.$_SERVER["PHP_SELF"].'?mainmenu='.$_REQUEST["mainmenu"].'&idmenu='.$_REQUEST["idmenu"].'&id='.$_REQUEST["id"].'&page=3" data-role="button">Стандартні вироби</a>
             </div>
         </div>';
print '<form id="lineaction" action="lineactive.php" method="post" style="width: 550px">';
print '<input id="id" name="id" value="'.$user->id.'" type="hidden">';
print '<input id="mainmenu" name="mainmenu" value="'.$_REQUEST['mainmenu'].'" type="hidden">';
print '<input id="idmenu" name="idmenu" value="'.$_REQUEST['idmenu'].'" type="hidden">';
print '<input id="page" name="page" value="'.$page.'" type="hidden">';
print '<input id="values" name="values" value="" type="hidden">';
print '<input id="action" name="action" value="update" type="hidden">';
print $form->selectLineAction($lineactive, 'lineaction', 30);
print '</br>';
print '<input type="submit" value="Зберегти">';
print '</form>';
print '</div>';
print "<script>
    $(document).ready(function(){
        $('#select_lineaction').on('change', SelectLineaction);
    })
    function SelectLineaction(){
        $('#values').val($('#select_lineaction').val());
    }
</script>";

