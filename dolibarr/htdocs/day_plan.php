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
if(isset($_REQUEST['action'])){
    global $db;
    switch($_REQUEST['action']) {
        case 'getdateaction':{
            $typeaction = '';
            if(substr($_REQUEST['type_action'], 0, strlen('current_'))=='current_')
                $typeaction = 'current';
            elseif(substr($_REQUEST['type_action'], 0, strlen('global_'))=='global_')
                $typeaction = 'global';
            elseif(substr($_REQUEST['type_action'], 0, strlen('total_'))=='total_')
                $typeaction = 'total';
            elseif(substr($_REQUEST['type_action'], 0, strlen('outstand'))=='outstand')
                $typeaction = 'outstand';
            $out = '';
            $array = array();
            switch($typeaction){
                case 'current':{
                    if(substr($_REQUEST['type_action'], strlen('current_')) == 'outstanding'){
                        $array = GetDateOutStandingActions("'AC_CURRENT'", $user->id);
                    }
                }break;
                case 'global':{
                    if(substr($_REQUEST['type_action'], strlen('global_')) == 'outstanding'){
                        $array = GetDateOutStandingActions("'AC_GLOBAL'", $user->id);
                    }
                }break;
                case 'total':{
                    if(substr($_REQUEST['type_action'], strlen('total_')) == 'outstanding'){
                        $array = GetDateOutStandingActions("'ALL'", $user->id);
                    }
                }break;
                case 'outstand':{
                    if($user->respon_alias == 'purchase')
                        $array = GetDateOutStandingActions("'AC_LINEACTIVE'", $user->id);
                    else
                        $array = GetDateOutStandingActions("'AC_AREA'", $user->id);
                }
            }
            if(count($array)>1) {
                $num = 0;
                foreach ($array as $date) {
                    $out .= '
                    <tr>
                        <td class="middle_size" id="' . $typeaction . '_' . $num . '" onclick="setdate(' . "'" . $typeaction . '_' . $num++ . "'" . ');" style="cursor: pointer">' . $date . '</td>
                    </tr>';
                }
            }else{
                $out .= $array[0];
            }
            echo $out;
            exit();
        }break;
        case 'getuserplan': {
            $typeaction = '';
            switch ($_REQUEST['mainmenu']) {
                case 'current_task': {
                    $typeaction = "'AC_CURRENT'";
                }
                    break;
                case 'global_task': {
                    $typeaction = "'AC_GLOBAL'";
                }
                    break;
                case 'area': {
                    $sql = "select `code` from 	llx_c_actioncomm where type in ('user','system')
                and code not in ('AC_CURRENT','AC_GLOBAL')";
                    $res = $db->query($sql);
                    if (!$res)
                        dol_print_error($db);
                    $codes = array();
                    if ($db->num_rows($res))
                        while ($obj = $db->fetch_object($res)) {
                            $codes[] = "'" . $obj->code . "'";
                        }
                    $typeaction = implode(',', $codes);
                }
                    break;
            }
            $today = array();
            $today = CalcOutStandingActions($typeaction, $today, $_REQUEST['id_usr']);

            $today = CalcFutureActions($typeaction, $today, $_REQUEST['id_usr']);
            $today = CalcFaktActions($typeaction, $today, $_REQUEST['id_usr']);
            echo json_encode($today);
        }break;
        case 'gettaskcode':{
            $sql = 'select `code`, fk_soc from llx_actioncomm where id='.$_REQUEST['rowid'];
            $res = $db->query($sql);
            $obj = $db->fetch_object($res);
            echo $obj->code.'&'.$obj->fk_soc;
            exit();
        }break;


    }
    exit();
}

header("Location: http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/responsibility/".$user->respon_alias."/day_plan.php?idmenu=10419&mainmenu=plan_of_days&leftmenu=");
exit();

function GetDateOutStandingActions($actioncode, $id_usr){
    global $db, $user;

    if($actioncode == "'ALL'" || $actioncode == "'AC_AREA'"|| $actioncode == "'AC_LINEACTIVE'"){
        $sql = "select `code` from `llx_c_actioncomm` where type in ('user','system')";
        if($actioncode == "'AC_AREA'")
            $sql.=" and `code` not in ('AC_GLOBAL', 'AC_CURRENT')";
        $res = $db->query($sql);
        $code = array();
        while($obj=$db->fetch_object($res)){
            $code[]= "'".$obj->code."'";
        }
        $actioncode=implode(',', $code);
    }
    $array = array();
    $sql = "select distinct date(datep2) as date_action  from `llx_actioncomm`
    inner join
    (select id from `llx_c_actioncomm` where code in(".$actioncode.") and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
    left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
    left join llx_actioncomm_resources on llx_actioncomm_resources.fk_actioncomm = `llx_actioncomm`.id
    where 1";
        if($actioncode == "'AC_GLOBAL'" || $actioncode == "'AC_CURRENT'" || $user->login !="admin")
            $sql .=" and llx_actioncomm_resources.fk_element = ".$id_usr;
    $sql .= " and datep2 < '".date("Y-m-d")."'";
    $sql .=" and percent <> 100";
    $sql .=" order by date_action";
    $res = $db->query($sql);
    if($db->num_rows($res)) {
        while($obj = $db->fetch_object($res)) {
            $date = new DateTime($obj->date_action);
            $array[] = $date->format('d.m.Y');
        }
    }
    return $array;
}
function CalcOutStandingActions($actioncode, $array, $id_usr){
//    var_dump($actioncode, $array, $id_usr);
//    die();
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
    $sql .=" and llx_actioncomm.`percent` <> 100";
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