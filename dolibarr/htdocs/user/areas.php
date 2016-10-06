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
//echo '<pre>';
//var_dump($_REQUEST);
//echo '</pre>';
require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/usergroups.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
if(isset($_REQUEST['switch_active'])){
    global $user;

    $param = explode(',', $_REQUEST['switch_active']);
    $id_param = substr($param[1], 3, strpos($param[1], 'active')-3);
    $sql = "select count(*) iCount from ".MAIN_DB_PREFIX."user_regions where fk_user=".$param[0]." and fk_id=".$id_param." limit 1";
    $res = $db->query($sql);
    $row = $db->fetch_object($res);
    if($row->iCount == 0)
        $sql = "insert into ".MAIN_DB_PREFIX."user_regions(fk_user, fk_id, active, id_usr, dtChange)
            values(".$param[0].", ".$id_param.", 1, ".$user->id.", Now())";
    else
        $sql = "update ".MAIN_DB_PREFIX."user_regions set active = ".$param[2].", id_usr=".$user->id.", dtChange=Now() where fk_user=".$param[0]." and fk_id=".$id_param;
    if($db->query($sql))
        echo 'succesfull';
    else
        echo $sql;
    //Переподключаю задачи
    if($param[2] == 1) {
        $sql = "select id,`fk_user_author` from llx_actioncomm
            inner join (select rowid from llx_societe where region_id = " . $id_param . ") societe on societe.rowid = llx_actioncomm.fk_soc
            inner join (select code from `llx_c_actioncomm` where active = 1 and (type = 'system' or type = 'user'))code_t on code_t.`code` = `llx_actioncomm`.`code`
            where 1
            and percent <> 100";
        $resAction = $db->query($sql);
        $actionsID = array();
        while($obj = $db->fetch_object($resAction)){
            $actionsID[] = $obj->id;
        }
        $sql = "update `llx_actioncomm_resources`
            set fk_element = ".$param[0]."
            where `llx_actioncomm_resources`.`fk_actioncomm` in (".implode(",",$actionsID).")";
//        echo '<pre>';
//        var_dump($sql);
//        echo '</pre>';
//        die();
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        else
            echo ' redirect llx_actioncomm_resources';
        $sql = "update llx_actioncomm
            set `fk_user_author` = " . $param[0] . "
            where llx_actioncomm.id in (".implode(",",$actionsID).")";
//    var_dump($sql);
//    die();
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        else
            echo ' redirect_task';
    }
    exit();
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
$Title=$langs->trans("PropAreas");
llxHeader('',$Title);
print_fiche_titre($Title);
$user = new User($db);
$user->info($id);
//echo '<pre>';
//var_dump($user);
//echo '</pre>';
//die();
$setting = $user->getregions($id);
if(count($setting) == 0)
    $setting[]=-1;
print '<a href="/dolibarr/htdocs/core/tools.php?mainmenu=tools&idmenu=5223">'.$langs->trans("Tools").'</a>  /
    <a href="/dolibarr/htdocs/core/users_and_group/groups_manager.php?mainmenu=tools">'.$langs->trans('MenuUsersAndGroups').'</a>/
    <a href="/dolibarr/htdocs/core/users_and_group/groups_manager.php?mainmenu=tools">'.$langs->trans('Users').'</a> /
    <a href="/dolibarr/htdocs/user/card.php?id='.$id.'&mainmenu=tools">'.$user->lastname.' '.$user->firstname.'</a>';

$TableParam = array();
$ColParam['title']=$langs->trans('Name');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('Region');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['sourcetable']='states';
$ColParam['detailfield']='state_id';
$TableParam[]=$ColParam;
unset($ColParam['sourcetable']);
unset($ColParam['detailfield']);

$ColParam['title']=$langs->trans('Param');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['hidden']='regions_param';
$ColParam['sourcetable']='classifycation';
$ColParam['detailfield']='classifycation_id';
$TableParam[]=$ColParam;
unset($ColParam['hidden']);
unset($ColParam['sourcetable']);
unset($ColParam['detailfield']);

$ColParam['title']=$langs->trans('Active');
$ColParam['width']='100';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$tablename='regions';
$sql='select `'.$tablename.'`.rowid, `'.$tablename.'`.name regions_name, states.name states_name, null, `'.$tablename.'`.active
from `'.$tablename.'` left join states on `'.$tablename.'`.`state_id` = `states`.rowid';

if(GETPOST('state_filter') != 0){
    $sql.= ' where state_id = '. GETPOST('state_filter');
}
$sql.=' order by `'.$tablename.'`.name';

//echo '<pre>';
//var_dump($sql);
//echo '</pre>';
//die();
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/DBManager/dbBuilder.php';
$db_mysql = new dbBuilder();
if(!isset($_REQUEST['sortfield']))
    $table = $db_mysql->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme, '', '', $setting);
else
    $table = $db_mysql->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme, $_REQUEST['sortfield'], $_REQUEST['sortorder'], $setting);
$new_link = "http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/DBManager/dbManager.php?new=1&tablename='".$tablename."'";
ob_start();
//include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/regions.html');
$CountryParam = explode(':', $conf->global->MAIN_INFO_SOCIETE_COUNTRY);
$sql = 'select rowid, name from states where `country_id`='.$CountryParam[0].' and active = 1';
$res = $db->query($sql);
//echo '<pre>';
//var_dump(GETPOST('state_filter'));
//echo '</pre>';
//die();
$filter_selector = '<form action="'.$_SERVER["REQUEST_URI"].'" method="post"><select name = "state_filter" id="state_filter" class="combobox" onchange="this.form.submit()">\r\n';
$filter_selector .='<option value="0">&nbsp;</option>\r\n';
for($i=0; $i<$db->num_rows($res); $i++){
    $obj = $db->fetch_object($res);
    $selected = GETPOST('state_filter') == $obj->rowid;
    $filter_selector .='<option value="'.$obj->rowid.'" '.($selected?('selected="selected"'):('')).'>'.$obj->name.'</option>\r\n';
}
$filter_selector .='</select></form>';

print '<div>&nbsp;</div>';
print '<div class="liste_titre"><table><td id="filtercol0" style="color: #ffffff">Фильтр</td><td id="filtercol1">'.$filter_selector.'</td><td id="filtercol2">&nbsp;</td></table></div>';
print '<div class="reference_without_control">
    '.$table.'
</div>';
print'
<script>
    $(document).ready(function() {

        var id_usr = "&id="+'.$id.'
        var thList = document.getElementsByClassName("nowrap")
        for(var i=0; i<thList.length; i++){
            $("td#filtercol"+i).width($("th#th"+i).width());
            var aLink = thList[i].getElementsByTagName("a");
            for(l=0; l<aLink.length; l++){
                aLink[l].href = aLink[l].href+id_usr;
            }

        }
        $("#reference_body").height($("#reference_body").height()-40);
        var switchList = document.getElementsByClassName("switch");

        for(var i=0; i<switchList.length; i++){
            var img = switchList[i].getElementsByTagName("img");
            $("#"+img[0].id).click(switch_change);
        }
        console.log(location.href);
    });
    function switch_change(){
        var end = strpos($(this).attr("src"), "/img/");
        var check = 0;
        if($(this).attr("src") == $(this).attr("src").substr(0, end+4)+"/switch_on.png"){
            $(this).attr("src", $(this).attr("src").substr(0, end+4)+"/switch_off.png");
        }else{
            $(this).attr("src", $(this).attr("src").substr(0, end+4)+"/switch_on.png");
            check = 1;
        }
//        console.log(location.href+"&switch_active='.$id.',"+$(this).attr("id")+","+check);
//        return;
        $.ajax({
            url: location.href+"&switch_active='.$id.',"+$(this).attr("id")+","+check,
            cache: false,
            success: function (html) {
                console.log(html);
            }
        });
    }
//    function setfilter(){
////        console.log($("#state_filter").val());
////        console.log(location.href);
//        location.href+="&filterset="+$("#state_filter").val();
//    }
</script>
';
echo ob_get_clean();

//llxFooter();

//$db->close();
