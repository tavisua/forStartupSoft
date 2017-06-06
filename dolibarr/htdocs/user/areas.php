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
global $db;
if(empty($_REQUEST['id'])&&$_REQUEST['action']=='doublelink'){
    llxHeader();
    $sql = "select llx_user_regions.rowid, llx_user.lastname, regions.`name`, llx_user_regions.active from llx_user_regions
        inner join llx_user on llx_user.rowid = llx_user_regions.fk_user
        inner join regions on regions.rowid = llx_user_regions.fk_id
        where 1
        and llx_user_regions.fk_id in 
          (select fk_id from llx_user_regions 
            inner join llx_user on llx_user.rowid = llx_user_regions.fk_user  
            where llx_user_regions.active = 1 
            and llx_user.active = 1
            group by fk_id having count(*)>1)
        and llx_user_regions.active = 1
        and llx_user.active = 1
        
        order by regions.`name`, llx_user.lastname";
    print_fiche_titre('Райони, що закріплені за декількома АТ');
    print '<div class="liste_titre" style="height: 30px"></div>';
    print '<div class="reference_without_control">';
    print '<table class="scrolling-table" id="reference_title">';
    print '<thead><tr class="liste_titre"><th>Прізвище</th><th>Район</th><th></th></tr></thead>';
    print '<tbody id="reference_body">';
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)){
        print '<tr>
                <td>'.$obj->lastname.'</td>
                <td>'.$obj->name.'</td>';
            if($obj->active == 1)
                print '<td><img id="img'.$obj->rowid.'active" src="/dolibarr/htdocs/theme/eldy/img/switch_on.png" onclick="switch_change('.$obj->rowid.');"></td>';
            else
                print '<td><img id="img'.$obj->rowid.'active" src="/dolibarr/htdocs/theme/eldy/img/switch_off.png" onclick="switch_change('.$obj->rowid.');"></td>';
        print'</tr>';
    }
    print '</tbody>';
    print '</div>';
    print'
<script type="text/javascript">
    $(document).ready(function() {
        $("#reference_body").height($("#reference_body").height()-40);
    });
    function switch_change(rowid){
        var active;
        if("/dolibarr/htdocs/theme/eldy/img/switch_on.png" == $("#img"+rowid+"active").attr("src")){
            $("#img"+rowid+"active").attr("src", "/dolibarr/htdocs/theme/eldy/img/switch_off.png");
            active = 0;
        }else{
            $("#img"+rowid+"active").attr("src", "/dolibarr/htdocs/theme/eldy/img/switch_on.png");        
            active = 1;
        }
        var param={
            active:active,
            rowid:rowid,
            action:"putch"
        }        
        $.ajax({
            url:"/dolibarr/htdocs/user/areas.php",
            data:param,
            cache:false,
            success:function(result) {
              if(result == 1)
                console.log("ok");
              else 
                console.log("error");
            }
        })
       console.log();
    }
</script>';

    exit();
}elseif (empty($_REQUEST['id'])&&$_REQUEST['action']=="putch"){
    $sql = "update llx_user_regions set active = ".$_REQUEST["active"].", dtChange=now() where rowid = ".$_REQUEST["rowid"];
    $res = $db->query($sql);
    if(!$res)
        print 0;
    else
        print 1;
    exit();
}elseif (empty($_REQUEST['id'])){
    llxHeader();
    print_fiche_titre('Поверніться на попередню сторінку');
    exit();
}
if(isset($_REQUEST['action']) && $_REQUEST['action']=='resetexecuter'){
    global $db;
    $sql = "select rowid from llx_societe
        where region_id in
          (select fk_id from llx_user_regions
            where fk_user = ".$_REQUEST['id_usr']."
            and active = 1)
        and active = 1";
    $res = $db->query($sql);
    if(!$res) {
        print '{result:"error", info:"'.$db->lasterror().'"}';
        exit();
    }
    $societe_id = array();
    while ($obj = $db->fetch_object($res)){
        $societe_id[]=$obj->rowid;
    }
    $sql = "select `llx_actioncomm`.id, `llx_actioncomm`.fk_user_action, `llx_actioncomm`.fk_user_author, `llx_actioncomm_resources`.rowid, `llx_actioncomm_resources`.`fk_element`
        from llx_actioncomm 
        left join `llx_actioncomm_resources` on `llx_actioncomm`.`id` =  `llx_actioncomm_resources`.`fk_actioncomm`
        where fk_soc in (".implode(',', $societe_id).") and active = 1 and percent <> 100
        and code <> 'AC_OTH_AUTO'";
    $res = $db->query($sql);
    if(!$res) {
        print '{"result":"error", info:"'.$db->lasterror().'"}';
        exit();
    }
    while($obj = $db->fetch_object($res)){
        if(empty($obj->fk_element))
            $sql = "update llx_actioncomm set fk_user_action = ".$_REQUEST['id_usr']." where id = ".$obj->id;
        else
            $sql = "update llx_actioncomm_resources set fk_element = ".$_REQUEST['id_usr']." where rowid = ".$obj->rowid;
        $up_res = $db->query($sql);
        if(!$up_res) {
            print '{"result":"error", info:"'.$db->lasterror().'"}';
            exit();
        }
    }
    print '{"result":"ok"}';
    exit();
}
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
            RedirectTask($param[0], $id_param);
    }
    exit();
}
if($_REQUEST['action'] == 'redirectAllTask'){
    llxHeader();
    global $db;
//    $sql = "select count(*) iCount from llx_actioncomm where code = 'AC_TEL' and `fk_user_author` = 0";
//    $res = $db->query($sql);

    set_time_limit(0);
    $start = $_GET['start'];
    if($start == 0) {
        $sql = "update llx_actioncomm
            set `fk_user_author` = 0
            where 1 and code = 'AC_TEL'";
        $resAction = $db->query($sql);
        $iCount = $db->affected_rows($resAction);
        echo 'Обнулено', ($iCount), 'записей';
    }
    $sql = 'select fk_user, fk_id from `llx_user_regions` where 1 and active = 1 limit '.$start.',50';
    $res = $db->query($sql);
    $usersID = array();
    $num = 0;

    print '<tbody>';
    while($obj = $db->fetch_object($res)){

            RedirectTask($obj->fk_user, $obj->fk_id);
//            $usersID[] = $obj->fk_user;
        $num++;
        print date('H:i:s').'</br>';
//        if(count($usersID)>10)
//        exit();
    }
    print '</tbody>';
    print 'ok';
    if($num>0) {
        $start+=50;
        print '<script type="application/javascript">

    $(document).ready(function() {
        console.log("'.$start.'");
        location.href="/dolibarr/htdocs/user/areas.php?action=redirectAllTask&mainmenu=home&start='.$start.'"
    })
    </script>';
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
//$sql='select `'.$tablename.'`.rowid, `'.$tablename.'`.name regions_name, states.name states_name, null, `'.$tablename.'`.active
//from `'.$tablename.'` left join states on `'.$tablename.'`.`state_id` = `states`.rowid';
$sql = 'select `regions`.rowid, `regions`.name regions_name, states.name states_name, null, `usr_regions`.active
from `regions` 
left join (select fk_id, active from `llx_user_regions`
  where fk_user = '.$_GET['id'].') usr_regions on usr_regions.fk_id = `regions`.rowid
left join states on `regions`.`state_id` = `states`.rowid';

if(GETPOST('state_filter') != 0){
    $sql.= ' where state_id = '. GETPOST('state_filter');
}
$sql.=' order by `'.$tablename.'`.name';

//die($sql);

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
print '<div class="liste_titre"><table><td id="filtercol0" style="color: #ffffff">Фильтр</td><td id="filtercol1">'.$filter_selector.'</td><td id="filtercol2"><button onclick = "ReSetExecuter('.$_REQUEST['id'].');" title="Перезакріпити завдання, що пов&#39;язані з прикріпленими районами">Перезакріпити завдання</button></td></table></div>';
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

        /*for(var i=0; i<switchList.length; i++){
            var img = switchList[i].getElementsByTagName("img");
            $("#"+img[0].id).off("click");
        }*/
        var html = $("#reference_body").html().replace(/change_switch/g, "switch_change");
        $("#reference_body").html(html);
//        console.log(html);
    });
    function ReSetExecuter(id_usr){
        if(confirm("Перезакріпити завдання?")){
            var param = {id_usr:id_usr, action:"resetexecuter"}
            $.ajax({
                data:param,
                cache:false,
                success:function(result){
                    var result = JSON.parse(result);
                    if(result["result"] == "ok")
                        alert("Виконано успішно");
                    else
                        alert("Перезакріплення не виконано. Зверніться до адміністратора");                    
//                    console.log(result["result"]);
                }
            })
        }
    }    
    function switch_change(rowid, tablename, active){
        var img = $("#img"+rowid+"active")
        var end = strpos(img.attr("src"), "/img/");
        var check = 0;
//        alert("test");

        if(img.attr("src") == img.attr("src").substr(0, end+4)+"/switch_on.png"){
            img.attr("src", img.attr("src").substr(0, end+4)+"/switch_off.png");
        }else{
            img.attr("src", img.attr("src").substr(0, end+4)+"/switch_on.png");
            check = 1;
        }

//        console.log(img.attr("src"));
//        return;
        $.ajax({
            url: location.href+"&switch_active='.$id.',"+img.attr("id")+","+check,
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

exit();

function RedirectTask($id_usr, $region_id, $reset = false){
    global $db;

    $sql = "select id,`fk_user_author` from llx_actioncomm
            inner join (select rowid from llx_societe where region_id = " . $region_id . ") societe on societe.rowid = llx_actioncomm.fk_soc
            inner join (select code from `llx_c_actioncomm` where active = 1 and (type = 'system' or type = 'user'))code_t on code_t.`code` = `llx_actioncomm`.`code`
            where 1";
    $resAction = $db->query($sql);
    $actionsID = array(0);
    while($obj = $db->fetch_object($resAction)){
        $actionsID[] = $obj->id;
    }
//    $sql = "update `llx_actioncomm_resources`
//            set fk_element = ".$id_usr."
//            where `llx_actioncomm_resources`.`fk_actioncomm` in (".implode(",",$actionsID).")";
//
//    $res = $db->query($sql);
//    if (!$res)
//        dol_print_error($db);
//    else
//        echo ' redirect llx_actioncomm_resources</br>';


    $sql = "update llx_actioncomm
            set `fk_user_author` = " . $id_usr . "
            where llx_actioncomm.id in (".implode(",",$actionsID).")";

    $res = $db->query($sql);
//    $iCount = $db->affected_rows($res);
    echo 'Установлено ', $db->affected_rows($res), ' записей';
//    var_dump(mysql_affected_rows(), $sql);
//    die();
    if (!$res)
        dol_print_error($db);
//    else
//        echo ' redirect_task';
    unset($actionsID);
}

//llxFooter();

//$db->close();
