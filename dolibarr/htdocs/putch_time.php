<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 08.04.2016
 * Time: 19:56
 */
require $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/main.inc.php';
global $db,$user;
if($_REQUEST['action'] == 'update_socid_in_action') {
    $sql = "select `llx_actioncomm`.id, fk_soc, socid
        from `llx_actioncomm`
        left join `llx_societe_contact` on `llx_societe_contact`.rowid=`llx_actioncomm`.fk_contact
        where llx_actioncomm.fk_soc <> `llx_societe_contact`.`socid`";
    $res = $db->query($sql);
    if (!$res)
        dol_print_error($db);
    set_time_limit(0);
    while ($obj = $db->fetch_object($res)) {
        $sql = "update llx_actioncomm set fk_soc = ".$obj->socid." where id = ".$obj->id;
        $up_res = $db->query($sql);
        if(!$up_res)
            dol_print_error($db);
    }
    exit();
}

if($_REQUEST['action'] == 'fix_answer') {
    $sql = "select id from llx_actioncomm
        where code in ('AC_CURRENT','AC_GLOBAL')
        and datec>='2017-02-01'
        and percent <> 100";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)){
        $sql = "select count(*) iCount from `llx_societe_action` where `action_id` =".$obj->id;
        $res_action = $db->query($sql);
        $obj_action = $db->fetch_object($res_action);
        if($obj_action->iCount > 0){
            $sql = "select fk_element from `llx_actioncomm_resources` where `fk_actioncomm` = ".$obj->id;
            $res_action = $db->query($sql);
            if(!$res_action)
                dol_print_error($db);
            $users = array();
            while($obj_action = $db->fetch_object($res_action)){
                $users[]=$obj_action->fk_element;
            }
            if(count($users)) {
                $sql = "update `llx_societe_action` set active = 0, new = 0 where `action_id` = " . $obj->id . ' and id_usr not in (' . implode(',', $users) . ')';
//            if(441634 == $obj->id)
//                die($sql);
                $res_action = $db->query($sql);
                if (!$res_action)
                    dol_print_error($db);
            }
        }
    }

}
if($_REQUEST['action'] == 'update_societe') {
    require_once DOL_DOCUMENT_ROOT . '/comm/action/class/actioncomm.class.php';
    $action = new ActionComm($db);
    $sql = "select rowid from llx_societe where active = 1";
    $res = $db->query($sql);
    if (!$res)
        dol_print_error($db);
    set_time_limit(0);
    while ($obj = $db->fetch_object($res)) {
        $action->setDateAction($obj->rowid);
        echo $obj->rowid . '<br>';
    }
    exit();
}
$sql = "SELECT `llx_actioncomm`.id, `llx_actioncomm`.priority, `llx_actioncomm`.datep, `llx_actioncomm`.datep2, `llx_actioncomm`.fk_user_action,
case
  when `llx_actioncomm_resources`.`fk_element` is null
  then `llx_actioncomm`.fk_user_action
  else `llx_actioncomm_resources`.`fk_element`
end as fk_element , `llx_c_actioncomm`.`exec_time`, `llx_actioncomm`.`code` from `llx_actioncomm`
inner join `llx_c_actioncomm` on `llx_c_actioncomm`.`code` = `llx_actioncomm`.`code`
left join `llx_actioncomm_resources` on `llx_actioncomm`.`id` = `llx_actioncomm_resources`.`fk_actioncomm`
where 1";
if(!isset($_GET['datep'])||empty($_GET['datep']))
    $sql.=" and datep > '2016-09-01'";
else {
    $sql .= " and date(datep) = '" . $_GET['datep'] . "'";
    $sql .= " and (`llx_actioncomm`.fk_user_action = ".$_GET['id_usr']." or `llx_actioncomm_resources`.`fk_element` = ".$_GET['id_usr'].")";
}
$sql .= " and `llx_actioncomm`.`code` not in ('AC_OTH_AUTO')
and `llx_actioncomm`.active = 1
and (`llx_actioncomm`.`entity` = 0 AND `llx_actioncomm`.`code` IN('AC_GLOBAL','AC_CURRENT') OR `llx_actioncomm`.`entity` = 1 AND `llx_actioncomm`.`code` NOT IN('AC_GLOBAL','AC_CURRENT'))
and (`llx_actioncomm`.hide is null or `llx_actioncomm`.hide <> 1)
order by case when `llx_actioncomm_resources`.`fk_element` is null then `llx_actioncomm`.fk_user_action else `llx_actioncomm_resources`.`fk_element` end, `llx_actioncomm`.priority, datep;";
//echo '<pre>';
//var_dump($sql);
//echo '</pre>';
//die();

$res = $db->query($sql);

$start = new DateTime();
$finish = new DateTime();
$finish->setTimestamp(0);
$start->setTimestamp(0);
$sec = mktime(8,0,0,$start->format('m'),$start->format('d'),$start->format('Y'));
while($obj = $db->fetch_object($res)){
    $curTime = new DateTime($obj->datep);
    if($finish->format('d.m.Y') != $curTime->format('d.m.Y')){
        $start = new DateTime($curTime->format('Y-m-d').' 08:00:00');
    }
    $sec = mktime($start->format('H'),$start->format('i'),$start->format('s'),$start->format('m'),$start->format('d'),$start->format('Y'));
    $finish = new DateTime();
    $finish->setTimestamp($sec + $obj->exec_time*60);
//    if(14338 == $obj->id) {
//        echo '<pre>';
//        var_dump($start, $finish);
//        echo '</pre>';
//        die();
//    }
//    if($id_usr == 6)
//        echo ''.$obj->id.'   '.$obj->datep.'</td><td>   '.$start->format('Y-m-d H:i:s').'</td><td>    '.$finish->format('Y-m-d H:i:s').'</td><td>    '.$obj->exec_time.'</td><td>    '.$obj->fk_user_author.'</td><td>    '.$obj->fk_element.'</td></tr></br>';
    $sql = "update `llx_actioncomm` set `llx_actioncomm`.datep = '".$start->format('Y-m-d H:i:s')."' , `llx_actioncomm`.datep2 = '".$finish->format('Y-m-d H:i:s')."' where id=".$obj->id;
//    echo $sql.'</br>';
    $update = $db->query($sql);
    if(!$update) {
        dol_print_error($db);
    }
    if($finish->format('H')>=12 && $finish->format('H')<14){
        $finish->setTimestamp(mktime(14,0,0,$finish->format('m'),$finish->format('d'),$finish->format('Y')));
    }
    $start = $finish;
}
echo 'success_putchtime';