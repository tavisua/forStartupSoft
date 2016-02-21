<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 05.02.2016
 * Time: 9:06
 */
//echo '<pre>';
//var_dump($_SERVER);
//echo '</pre>';
//die();
require $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/main.inc.php';
if(isset($_REQUEST['action'])&&$_REQUEST['action']=='getuserplan'){
    global $db;
    $typeaction='';
    switch($_REQUEST['mainmenu']){
        case 'current_task':{
            $typeaction="'AC_CURRENT'";
        }break;
        case 'global_task':{
            $typeaction="'AC_GLOBAL'";
        }break;
        case 'area':{
            $sql = "select `code` from 	llx_c_actioncomm where type in ('user','system')
            and code not in ('AC_CURRENT','AC_GLOBAL')";
            $res = $db->query($sql);
            if(!$res)
                dol_print_error($db);
            $codes=array();
            if($db->num_rows($res))
                while($obj = $db->fetch_object($res)){
                    $codes[]="'".$obj->code."'";
                }
            $typeaction=implode(',', $codes);
        }break;
    }
    $today = array();
    $today = CalcOutStandingActions($typeaction, $today, $_REQUEST['id_usr']);

    $today = CalcFutureActions($typeaction, $today, $_REQUEST['id_usr']);
    $today = CalcFaktActions($typeaction, $today, $_REQUEST['id_usr']);
    echo json_encode($today);
    exit();
}

header("Location: http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/responsibility/".$user->respon_alias."/day_plan.php?idmenu=10419&mainmenu=plan_of_days&leftmenu=");
exit();

function CalcOutStandingActions($actioncode, $array, $id_usr){
    global $db, $user;
    $sql = "select count(*) as iCount  from `llx_actioncomm`
    inner join
    (select id from `llx_c_actioncomm` where code in(".$actioncode.") and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
    left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
    inner join `llx_actioncomm_resources` on `llx_actioncomm`.`id` =  `llx_actioncomm_resources`.`fk_actioncomm`
    where 1";
        if($actioncode == "'AC_GLOBAL'" || $actioncode == "'AC_CURRENT'" || $user->login !="admin")
            $sql .=" and `llx_actioncomm_resources`.fk_element = ".$id_usr;
    $sql .= " and datep2 < '".date("Y-m-d")."'";
    $sql .=" and datea is null";
//    if($actioncode == "'AC_GLOBAL'" || $actioncode == "'AC_CURRENT'"){}
//        else
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();

    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    if($db->num_rows($res)) {
        $obj = $db->fetch_object($res);
        $array['outstanding'] = $obj->iCount;
    }else
        $array['outstanding'] = '';
    return $array;
}
function CalcFaktActions($actioncode, $array, $id_usr){
    global $db, $user;
    //Минулі виконані дії
    $i=0;
        $sql = "select count(*) as iCount  from `llx_actioncomm`
        inner join
        (select id from `llx_c_actioncomm` where code in(".$actioncode.") and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
        left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
        inner join `llx_actioncomm_resources` on `llx_actioncomm`.`id` =  `llx_actioncomm_resources`.`fk_actioncomm`
        where 1";
        if($actioncode == "'AC_GLOBAL'" || $actioncode == "'AC_CURRENT'" || $id_usr != 1)
            $sql .=" and `llx_actioncomm_resources`.fk_element = ".$id_usr;
        if($i<8) {
            $query_date = date("Y-m-d", (time()+3600*24*(-$i)));
            if($i!=7)
                $sql .= " and datep2 between '".$query_date . "' and date_add('" . $query_date . "', interval 1 day)";
            else
                $sql .= " and datep2 between date_add('" . date("Y-m-d") . "', interval -7 day) and '".date("Y-m-d") . "'";
        }else {
                $sql .= " and datep2 between date_add('" . date("Y-m-d") . "', interval -31 day) and '" . date("Y-m-d") . "'";
        }
        $sql .=" and datea is not null";
//        if($i == 7 && ($id_usr != 1))
        $res = $db->query($sql);
        while($res && $obj = $db->fetch_object($res)){
            if($i<8) {
                if($i == 0)
                    $array['fakt_today'] = $obj->iCount;
                else
                    $array['fakt_day_m' . ($i)] = $obj->iCount;
            }else
                $array['fakt_month']=$obj->iCount;
        }
    return $array;
}
function CalcFutureActions($actioncode, $array, $id_usr){
    global $db, $user;
    //Майбутні дії
    $i=0;
        $sql = "select count(*) as iCount  from `llx_actioncomm`
        inner join
        (select id from `llx_c_actioncomm` where code in(".$actioncode.") and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
        left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
        inner join `llx_actioncomm_resources` on `llx_actioncomm`.`id` =  `llx_actioncomm_resources`.`fk_actioncomm`
        where 1";
        if($actioncode == "'AC_GLOBAL'" || $actioncode == "'AC_CURRENT'" || $user->login !="admin")
            $sql .=" and `llx_actioncomm_resources`.fk_element = ".$id_usr;
        if($i<8) {
            $query_date = date("Y-m-d", (time()+3600*24*$i));
            if($i!=7)
                $sql .= " and datep2 between '".$query_date . "' and date_add('" . $query_date . "', interval 1 day)";
            else
                $sql .= " and datep2 between '".date("Y-m-d") . "' and date_add('" . date("Y-m-d") . "', interval 7 day)";
        }else {
            $month = date("m");
            if($month+1<10)
                $month = '0'.($month+1);
            else
                $month =($month+1);
                $sql .= " and datep2 between '" . date("Y-m-d") . "' and date_add('" . date("Y-m-d") . "', interval 31 day)";
        }
        $sql .=" and datea is null";
//        echo '<pre>';
//        var_dump($sql);
//        echo '</pre>';
//        die();
        $res = $db->query($sql);
        while($res && $obj = $db->fetch_object($res)){
            if($i<8) {
                if($i == 0)
                    $array['future_today'] = $obj->iCount;
                else
                    $array['future_day_pl' . ($i)] = $obj->iCount;
            }else
                $array['future_month']=$obj->iCount;
        }
    return $array;
}