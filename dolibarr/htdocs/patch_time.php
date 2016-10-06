<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 08.04.2016
 * Time: 19:56
 */
require $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/main.inc.php';
global $db;
$sql = "SELECT `llx_actioncomm`.id, `llx_actioncomm`.priority, `llx_actioncomm`.datep, `llx_actioncomm`.datep2, `llx_actioncomm`.fk_user_author,
case
  when `llx_actioncomm_resources`.`fk_element` is null
  then `llx_actioncomm`.fk_user_author
  else `llx_actioncomm_resources`.`fk_element`
end as fk_element , `llx_c_actioncomm`.`exec_time`, `llx_actioncomm`.`code` from `llx_actioncomm`
inner join `llx_c_actioncomm` on `llx_c_actioncomm`.`code` = `llx_actioncomm`.`code`
left join `llx_actioncomm_resources` on `llx_actioncomm`.`id` = `llx_actioncomm_resources`.`fk_actioncomm`
where 1
and datep > '2016-08-01'
and `llx_actioncomm`.`code` not in ('AC_OTH_AUTO')
and `llx_actioncomm`.active = 1
order by case when `llx_actioncomm_resources`.`fk_element` is null then `llx_actioncomm`.fk_user_author else `llx_actioncomm_resources`.`fk_element` end, `llx_actioncomm`.priority, datep;";
$res = $db->query($sql);
$id_usr = 0;
$start = new DateTime();
$finish = new DateTime();
$finish->setTimestamp(0);
$start->setTimestamp(0);
$sec = mktime(8,0,0,$start->format('m'),$start->format('d'),$start->format('Y'));
while($obj = $db->fetch_object($res)){
    $curTime = new DateTime($obj->datep);
    if($id_usr != $obj->fk_element || $finish->format('d.m.Y') != $curTime->format('d.m.Y')){
        $start = new DateTime($curTime->format('Y-m-d').' 08:00:00');
        $id_usr = $obj->fk_element;
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
    if($id_usr == 6)
        echo ''.$obj->id.'   '.$obj->datep.'</td><td>   '.$start->format('Y-m-d H:i:s').'</td><td>    '.$finish->format('Y-m-d H:i:s').'</td><td>    '.$obj->exec_time.'</td><td>    '.$obj->fk_user_author.'</td><td>    '.$obj->fk_element.'</td></tr></br>';
    $sql = "update `llx_actioncomm` set `llx_actioncomm`.datep = '".$start->format('Y-m-d H:i:s')."' , `llx_actioncomm`.datep2 = '".$finish->format('Y-m-d H:i:s')."' where id=".$obj->id;
    $update = $db->query($sql);
    if(!$update) {
        dol_print_error($db);
    }
    if($finish->format('H')>=12 && $finish->format('H')<14){
        $finish->setTimestamp(mktime(14,0,0,$finish->format('m'),$finish->format('d'),$finish->format('Y')));
    }
    $start = $finish;
}