<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 23.08.2016
 * Time: 6:18
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/main.inc.php';
global $db,$user,$langs;
if($_REQUEST['action'] == 'getStatusAction'){
    $out = '';
    $out .= '<tr id="0">';
    $out .= '<td class="middle_size" onclick="setParam('."'status'".','."''".')" style="cursor:pointer" ><b>Всі завдання</b></td>';
    $out .= '</tr>';
    $out .= '<tr id="ActionNotRunning">';
    $out .= '<td class="middle_size" onclick="setParam('."'status'".','."'ActionNotRunning'".')" style="cursor:pointer" >'.$langs->trans('ActionNotRunning').'</td>';
    $out .= '</tr>';
//    $out .= '<tr id="ActionNotApplicable">';
//    $out .= '<td class="middle_size" onclick="setParam('."'status'".','."'ActionNotApplicable'".')" style="cursor:pointer" >'.$langs->trans('ActionNotApplicable').'</td>';
//    $out .= '</tr>';
    $out .= '<tr id="ActionRunningNotStarted">';
    $out .= '<td class="middle_size" onclick="setParam('."'status'".','."'ActionRunningNotStarted'".')" style="cursor:pointer" >'.$langs->trans('ActionRunningNotStarted').'</td>';
    $out .= '</tr>';
    $out .= '<tr id="ActionRunningShort">';
    $out .= '<td class="middle_size" onclick="setParam('."'status'".','."'ActionRunningShort'".')" style="cursor:pointer" >'.$langs->trans('ActionRunningShort').'</td>';
    $out .= '</tr>';
    $out .= '<tr id="ActionDoneShort">';
    $out .= '<td class="middle_size" onclick="setParam('."'status'".','."'ActionDoneShort'".')" style="cursor:pointer" >'.$langs->trans('ActionDoneShort').'</td>';
    $out .= '</tr>';
    echo $out;
}
if($_REQUEST['action'] == 'getSubdivision'){
    $out .= '<tr id="0">';
    $out .= '<td class="middle_size" onclick="setParam('."'".trim($_REQUEST['prefix'])."_subdiv_id'".',0)" style="cursor:pointer" ><b>Всі підрозділи</b></td>';
    $out .= '</tr>';
    $sql = "select distinct `subdivision`.`rowid`, `subdivision`.`name` from `llx_actioncomm`
        inner join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = `llx_actioncomm`.`id`\n";
    if($_REQUEST['prefix'] == 'p')
        $sql.=" inner join `llx_user` on `llx_user`.`rowid` = `llx_actioncomm_resources`.`fk_element`\n";
    else
        $sql.=" inner join `llx_user` on `llx_user`.`rowid` = `llx_actioncomm`.`fk_user_author`\n";
    $sql.=" inner join `subdivision` on `subdivision`.`rowid` = `llx_user`.`subdiv_id`
        where 1
        and percent != 100
        and code = '".$_REQUEST['code']."'";
    if($_REQUEST['prefix'] == 'p')
        $sql.=" and `llx_actioncomm`.`fk_user_author` = ".$_REQUEST['id_usr']."\n";
    else
        $sql.=" and (`llx_actioncomm`.`fk_user_author` = ".$_REQUEST['id_usr']." or `llx_actioncomm_resources`.`fk_element` = ".$_REQUEST['id_usr'].")\n";
    $sql.=" order by `subdivision`.`name`";
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);

    while($obj = $db->fetch_object($res)){
        $out .= '<tr id="'.$obj->rowid.'">';
        $out .= '<td class="middle_size" onclick="setParam('."'".trim($_REQUEST['prefix'])."_subdiv_id'".','.$obj->rowid.')" style="cursor:pointer" >'.$obj->name.'</td>';
        $out .= '</tr>';
    }
    echo $out;
}
if($_REQUEST['action'] == 'getGroupOfTask'){
    $out .= '<tr id="0">';
    $out .= '<td class="middle_size" onclick="setParam('."'groupoftaskID'".', 0)" style="cursor:pointer" ><b>Всі групи</b></td>';
    $out .= '</tr>';
    $sql = "select `llx_c_groupoftask`.rowid, `llx_c_groupoftask`.`name` as `name` from llx_c_groupoftask
        where fk_respon_id = 0
        and active = 1 ";
    $sql.= " union";
    $sql.= " select `llx_c_groupoftask`.rowid, `llx_c_groupoftask`.`name` from llx_user
        inner join `llx_c_groupoftask` on `fk_respon_id` = llx_user.respon_id
        where llx_user.rowid = ".$_REQUEST['id_usr']."
        and `llx_c_groupoftask`.`active` = 1
        order by `name`";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);

    while($obj = $db->fetch_object($res)){
        $out .= '<tr id="'.$obj->rowid.'">';
        $out .= '<td class="middle_size" onclick="setParam('."'groupoftaskID'".','.$obj->rowid.')" style="cursor:pointer" >'.$obj->name.'</td>';
        $out .= '</tr>';
    }
//    $sql = "select `llx_c_groupoftask`.rowid, `llx_c_groupoftask`.`name` from llx_user
//        inner join `llx_c_groupoftask` on `fk_respon_id` = llx_user.respon_id
//        where llx_user.rowid = ".$_REQUEST['id_usr']."
//        and `llx_c_groupoftask`.`active` = 1
//        order by `llx_c_groupoftask`.`name`";
//    $res = $db->query($sql);
//    if(!$res)
//        dol_print_error($db);
//
//    while($obj = $db->fetch_object($res)){
//        $out .= '<tr id="'.$obj->rowid.'">';
//        $out .= '<td class="middle_size" onclick="setGroupTaskFilter('.$obj->rowid.')" style="cursor:pointer" >'.$obj->name.'</td>';
//        $out .= '</tr>';
//    }
    echo $out;
}
if($_REQUEST['action'] == 'getPerformance' || $_REQUEST['action'] == 'getCustomer') {
//    echo '<pre>';
//    var_dump($_REQUEST);
//    echo '</pre>';
//    die();
    $sql = "select id from `llx_c_actioncomm` where active = 1";
    if($_REQUEST['code'] != 'AC_ALL')
        $sql.=" and `code` = '".$_REQUEST['code']."'";
    $res = $db->query($sql);
    if($res->num_rows<=1)
        $fk_action = $db->fetch_object($res);
    else{
        while($obj = $db->fetch_object($res)){
            $fk_action[]=$obj->id;
        }
    }
    $sql = "select distinct llx_user.rowid, llx_user.lastname, llx_user.firstname
        from `llx_actioncomm` 
        left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = `llx_actioncomm`.id";
    if($_REQUEST['action'] == 'getPerformance')
        $sql.="                 
        inner join llx_user on llx_user.rowid = case when llx_actioncomm_resources.fk_element is null then `llx_actioncomm`.`fk_user_author` else llx_actioncomm_resources.fk_element end";
    elseif($_REQUEST['action'] == 'getCustomer')
        $sql.=" inner join llx_user on llx_user.rowid = `llx_actioncomm`.`fk_user_author`";
    $sql.=" where 1 ";
    if(!is_array($fk_action))
        $sql.=" and fk_action = ".$fk_action->id;
    else
        $sql.=" and fk_action in (".implode(',',$fk_action).")";
    $sql.=" and llx_actioncomm.percent not in (100,-100,99) and llx_actioncomm.active = 1
        and (`llx_actioncomm`.`fk_user_author` = ".$_REQUEST['id_usr']." or `llx_actioncomm_resources`.`fk_element` = ".$_REQUEST['id_usr'].")
        order by llx_user.lastname, llx_user.firstname";
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $out .= '<tr id="0">';
    $out .= '<td class="middle_size" onclick="setParam('."'".($_REQUEST['action'] == 'getPerformance'?'performer':'customer')."',0)".'" style="cursor:pointer" ><b>Всі завдання</b></td>';
    $out .= '</tr>';
    if($_REQUEST['action'] == 'getPerformance' && in_array($user->respon_alias2, array('dir_depatment','senior_manager'))) {
            $out .= '<tr id="-1">';
            $out .= '<td class="middle_size" onclick="setParam('."'".($_REQUEST['action'] == 'getPerformance'?'performer':'customer')."'".',-1)" style="cursor:pointer" ><b>Всі завдання підрозділу</b></td>';
            $out .= '</tr>';
    }
    while($obj = $db->fetch_object($res)){
        $out .= '<tr id="'.$obj->rowid.'" '.($obj->rowid == $user->id?'class="select_item"':'').'>';
        $out .= '<td class="middle_size" onclick="setParam('."'".($_REQUEST['action'] == 'getPerformance'?'performer':'customer')."',".$obj->rowid.')" style="cursor:pointer" >'.$obj->lastname.' '.mb_substr($obj->firstname, 0,1, 'UTF-8').'.</td>';
        $out .= '</tr>';
    }
    echo $out;
}
