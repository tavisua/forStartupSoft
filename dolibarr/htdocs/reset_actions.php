<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 10.06.2016
 * Time: 11:30
 */
require_once 'main.inc.php';
$start = time();
if(isset($_GET['id_usr'])&&!empty($_GET['id_usr'])){
    resetActions($_GET['id_usr']);
}else{
    $sql = "select llx_user.rowid from llx_user
        left join `responsibility` on `responsibility`.`rowid` = llx_user.respon_id
        left join `responsibility` r2 on `r2`.`rowid` = llx_user.respon_id2
        where (responsibility.alias = 'sale' or r2.alias = 'sale')
        and llx_user.active = 1";
//    die($sql);
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)){
        resetActions($obj->rowid);
    }
}
echo 'ready '.(time()-$start);
exit();
function resetActions($id_usr){
    global $db;
    $sql = "select llx_actioncomm.id from llx_actioncomm
    left join llx_actioncomm_resources on llx_actioncomm_resources.`fk_actioncomm` = llx_actioncomm.id
    left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end
    inner join llx_societe on llx_societe.rowid = llx_actioncomm.fk_soc
    where 1
    and llx_actioncomm.active = 1
    and llx_actioncomm.`code` = 'AC_TEL'
    and llx_societe.region_id in (select fk_id from llx_user_regions where fk_user = ".$id_usr." and active = 1)
    and llx_actioncomm.fk_user_author <> ".$id_usr."
    and datep>= adddate(date(now()), interval -1 month)";
//    die($sql);
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $actionsID = array();
    while($obj = $db->fetch_object($res)){
        $actionsID[]=$obj->id;
    }
    $sql = "update llx_actioncomm set fk_user_author = ".$id_usr." where id in(".implode(',',$actionsID).")";
    $res = $db->query($sql);

}