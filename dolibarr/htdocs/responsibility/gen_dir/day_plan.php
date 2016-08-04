<?php
require $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/day_plan.php';

//$actions = array();
//$future = array();
//$fact = array();
//$outstanding = array();
//$userActions = array();
//$actcode = array('AC_GLOBAL', 'AC_CURRENT');
//$user_respon = array();
//$sql = "select llx_user.rowid, r1.alias a1, r2.alias a2 from llx_user
//left join `responsibility` r1 on r1.rowid = llx_user.respon_id
//left join `responsibility` r2 on r2.rowid = llx_user.respon_id2
//where llx_user.active = 1";
//$res = $db->query($sql);
//if(!$res)
//    dol_print_error($db);
//while($obj = $db->fetch_object($res)){
//    $user_respon[$obj->rowid] = array($obj->a1,$obj->a2);
//}
//echo '<pre>';
//var_dump($actions);
//echo '<pre>';
//die();
//echo date('Y-m-d', time()-604800);
//die();

//die($user->respon_alias2);
//unset($_SESSION['actions']);
//if(!isset($_SESSION['actions'])) {
//    $sql = "select llx_actioncomm.id, sub_user.rowid  id_usr, sub_user.alias, `llx_societe`.`region_id`, sub_user.subdiv_id, llx_actioncomm.percent, date(llx_actioncomm.datep) datep,
//    llx_actioncomm.percent, case when llx_actioncomm.`code` in ('AC_GLOBAL', 'AC_CURRENT') then llx_actioncomm.`code` else 'AC_CUST' end `code`, `llx_societe_action`.`callstatus`
//    from llx_actioncomm
//    inner join (select id from `llx_c_actioncomm` where type in('user','system') and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
//    left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = llx_actioncomm.id
//    left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
//    left join `llx_societe_action` on `llx_societe_action`.`action_id` = `llx_actioncomm`.`id`
//    inner join (select `llx_user`.rowid, `responsibility`.`alias`, `llx_user`.subdiv_id from `llx_user` inner join `responsibility` on `responsibility`.`rowid` = `llx_user`.`respon_id` where 1 and `llx_user`.`active` = 1) sub_user on sub_user.rowid = case when llx_actioncomm_resources.fk_element is null then llx_actioncomm.`fk_user_author` else llx_actioncomm_resources.fk_element end
//    where 1
//    and llx_actioncomm.active = 1
//    and datep2 between adddate(date(now()), interval -1 month) and adddate(date(now()), interval 1 month)";
//
//    $res = $db->query($sql);
//    if (!$res)
//        dol_print_error($db);
//    $actions = array();
//    $time = time();
//        $tmpactions = array();
//
//    while ($obj = $db->fetch_object($res)) {
//        $actions[] = array('id'=>$obj->id, 'id_usr' => $obj->id_usr, 'region_id' => $obj->region_id, 'subdiv_id'=>$obj->subdiv_id,
//            'respon_alias' => $obj->alias, 'percent' => $obj->percent, 'datep' => $obj->datep, 'code' => $obj->code,
//            'callstatus'=> $obj->callstatus);
//        $tmpactions[$obj->subdiv_id][$obj->id_usr][$obj->code][ $obj->datep]++;
//    }
////echo '<pre>';
////var_dump($actions);
////echo '<pre>';
////die();
//    $_SESSION['actions'] = $actions;
//
//}else {
//    $actions = $_SESSION['actions'];
//}
//$outstanding = CalcOutStandingActions($actions);
//$_SESSION['outstanding'] = $outstanding;
//if(!isset($_SESSION['future'])) {
//    $future = CalcFutureActions($actions);
//    $_SESSION['future'] = $future;
//}else{
//    $future = $_SESSION['future'];
//}

if(isset($_REQUEST['action'])&&$_REQUEST['action'] == 'ShowTable'){
    print ShowTable();
    exit();
}
if(isset($_REQUEST['action'])&&$_REQUEST['action'] == 'getLineActiveList'){
    print getLinePurchaseActiveList($_REQUEST['id_usr']);
    exit();
}
if(isset($_REQUEST['action'])&&$_REQUEST['action']=='setSpyMode'){

    if(isset($_REQUEST['id_usr'])&&!empty($_REQUEST['id_usr'])) {
        $_SESSION['spy_id_usr'] = $_REQUEST['id_usr'];
        echo 1;
    }else {
        unset($_SESSION['spy_id_usr']);
        echo 2;
    }
    exit();
}
if(isset($_REQUEST['action'])&&$_REQUEST['action']=='gettask'){
    if(isset($_REQUEST['subdiv_id'])&&empty($_REQUEST['subdiv_id']))
        echo getActionsBySub($_REQUEST['classname'], $_REQUEST['code']);
//    echo 'test';
    exit();
}
if(isset($_REQUEST['action'])&&$_REQUEST['action']=='getLineActiveService'){
    echo getLineActiveService($_REQUEST['id_usr']);
    exit();
}
if(isset($_REQUEST['action'])&&$_REQUEST['action']=='getLineActiveTask'){
    if(isset($_REQUEST['subdiv_id'])&&!empty($_REQUEST['subdiv_id']))
        echo getLineActiveTask($_REQUEST['subdiv_id'], $_REQUEST['classname'], $_REQUEST['code']);
    exit();
}
if(isset($_REQUEST['action'])&&$_REQUEST['action']=='getUsersTask'){
    if(isset($_REQUEST['subdiv_id'])&&!empty($_REQUEST['subdiv_id']))
        echo getActionsByUsers($_REQUEST['subdiv_id'], $_REQUEST['classname'], $_REQUEST['code'], $_REQUEST['responding']);
    exit();
}
if(isset($_REQUEST['action'])&&$_REQUEST['action']=='getRegions'){
    echo getRegionsList($_REQUEST['id_usr']);
    exit();
}



llxHeader("",$langs->trans('PlanOfDays'),"");
print_fiche_titre($langs->trans('PlanOfDays'));
llxLoadingForm();
//$table = ShowTable();
//var_dump(htmlspecialchars($table));
//die();

include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/responsibility/gen_dir/day_plan.html';
llxPopupMenu();
//echo '<pre>';
//var_dump($conf->browser);
//echo '</pre>';
exit();

function ShowTable(){
    global $db,$user;
    $out = '<tbody id="reference_body">';
    $start = time();

    //Найкращий користувач системи
    $sql="select count(id) iCount, case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end user_id  from llx_actioncomm
        left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
        where date(datep) between adddate(date(now()), interval -1 week) and date(now())
        and (llx_actioncomm.`code`in ('AC_GLOBAL','AC_CURRENT') or llx_actioncomm.id in (select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1))
        and active = 1
        group by user_id
        order by iCount desc limit 1;";
    $res = $db->query($sql);
//    die($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    $bestuserID = $obj->user_id;
    $out.=getTotalUserAction($bestuserID, 'bestvalue', 'Найкр.співр.сист.');

    //Найкращий директор дипартам. системи
    $sql="select count(id) iCount, case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end user_id
        from llx_actioncomm
        left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
        where date(datep) between adddate(date(now()), interval -1 week) and date(now())
        and (llx_actioncomm.`code`in ('AC_GLOBAL','AC_CURRENT') or llx_actioncomm.id in (select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1))
        and case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end in (select rowid from llx_user where respon_id = 8)
        and active = 1 group by user_id order by iCount desc limit 1;";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    $bestDDID = $obj->user_id;
    $out.=getTotalUserAction($bestDDID, 'bestvalue', 'Найкр.ДД.сист.');

    //Найкращий департамент системи
    $sql="select llx_user.subdiv_id, count(distinct llx_actioncomm.id) iCount from llx_actioncomm
            left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
            left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end
            where 1
            and  date(datep) between  adddate(date(now()), interval -1 week) and date(now())
            and (llx_actioncomm.`code`in ('AC_GLOBAL','AC_CURRENT') or llx_actioncomm.id in (select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1))
            and llx_actioncomm.percent = 100
            and llx_actioncomm.active = 1
            and llx_user.subdiv_id in (select rowid from `subdivision` where active = 1)
            group by llx_user.subdiv_id
            order by iCount desc limit 1";
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    $bestDepID = $obj->subdiv_id;
    $out.= getTotalSubdivAction('bestvalue','Найкр.деп.сист.',$bestDepID);

    //Всього по компанії
    $out.= getTotalSubdivAction('','Всього по компанії');
    $out.= getTotalSubdivAction('','Всього по компанії "Глобальні"',0,'AC_GLOBAL');
    $out.= getTotalSubdivAction('','Всього по компанії "Поточні"',0,'AC_CURRENT');
    $out.= getTotalSubdivAction('','Всього по компанії "По напрямках"',0,'AC_CUST');

//    //Глобальні і поточні ген.директора
    $out.=getTotalUserAction($user->id, 'even', 'Всього задач');
    $out.=getTotalUserAction($user->id, 'odd', 'Глобальні', 'AC_GLOBAL');
    $out.=getTotalUserAction($user->id, 'even', 'Поточні', 'AC_CURRENT');

    $out.='</tbody>';
//    echo time()-$start;
//    die();
    return $out;
}
function getTotalUserAction($user_id, $class, $title, $code = '')
{
    global $db;
    $out = '<tr class="'.$class.'">';
    $start = time();
    $sql="select lastname, firstname, subdivision.name from llx_user, subdivision where llx_user.rowid = ".$user_id.' and llx_user.subdiv_id=subdivision.rowid';
    $res = $db->query($sql);
    if (!$res)
        dol_print_error($db);
    $obj=$db->fetch_object($res);
    $out.='<td colspan="2">'.$title.' '.trim($obj->lastname).' '.mb_substr($obj->firstname, 0,1,'UTF-8').'. ('.trim($obj->name).')</td><td></td>';
    //Всього завдань та виконані
    $total = array();
    $fact = array();
    for($i=0; $i<=1; $i++) {
        if($i<1)
            $period = 'month';
        else
            $period = 'week';
        //Всього завдань
        $sql = "select count(*) iCount from llx_actioncomm
            left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
            where date(datep) between  adddate(date(now()), interval -1 ".$period.") and date(now())
            and case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end = " . $user_id;
        if(empty($code))
            $sql.=" and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
        else{
            if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
                $sql .= " and llx_actioncomm.`code`='" . $code . "'";
            else
                $sql .= " and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                        where active = 1
                        and `type` in ('system','user')
                        and `code` not in ('AC_GLOBAL','AC_CURRENT'))";
        }
        $sql.=" and active = 1";
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//        die();
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        $obj = $db->fetch_object($res);
        $total[$period] = $obj->iCount;
        //Фактично виконаних
        $sql = "select  count(*) iCount from llx_actioncomm
            left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
            where date(datep) between  adddate(date(now()), interval -1 ".$period.") and date(now())
            and case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end = " . $user_id;
        if(empty($code))
            $sql.= " and (llx_actioncomm.`code`in ('AC_GLOBAL','AC_CURRENT') or llx_actioncomm.id in (select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1))";
        else
            if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
                $sql .= " and llx_actioncomm.`code`='" . $code . "'";
            else{
                $sql.=" and llx_actioncomm.id in (select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1)";
            }
        $sql.=" and llx_actioncomm.percent = 100
            and llx_actioncomm.active = 1";
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        $obj = $db->fetch_object($res);
        $fact[$period] = $obj->iCount;
    }
    $sql = "select date(datep) datep, count(*) iCount from llx_actioncomm
    left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
    where date(datep) between  adddate(date(now()), interval -6 day) and date(now())
    and case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end = " . $user_id;
    if(empty($code))
            $sql.=" and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
        else{
            if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
                $sql .= " and llx_actioncomm.`code`='" . $code . "'";
            else
                $sql .= " and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                        where active = 1
                        and `type` in ('system','user')
                        and `code` not in ('AC_GLOBAL','AC_CURRENT'))";
        }
    $sql .= " and active = 1
    group by date(datep);";
    $res = $db->query($sql);
    if (!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)){
        $total[$obj->datep] = $obj->iCount;
    }

    $sql = "select date(datep) datep, count(*) iCount from llx_actioncomm
    left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
            where date(datep) between  adddate(date(now()), interval -6 day) and date(now())
            and case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end = " . $user_id;
    if(empty($code))
        $sql.=" and (llx_actioncomm.`code`in ('AC_GLOBAL','AC_CURRENT') or llx_actioncomm.id in (select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1))";
    else{
        if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
            $sql .= " and llx_actioncomm.`code`='" . $code . "'";
        else{
            $sql.=" and llx_actioncomm.id in (select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1)";
        }
    }
    $sql.=" and llx_actioncomm.percent = 100
            and llx_actioncomm.active = 1
            group by date(datep);";
    $res = $db->query($sql);
    if (!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)){
        $fact[$obj->datep] = $obj->iCount;
    }

    $percent_block = '';
    for($i=8; $i>=0; $i--){
        if($i < 8) {
            $percent = 0;
            if($i<7) {
                $count = (isset($fact[date("Y-m-d", (time() - 3600 * 24 * $i))]) ? $fact[date("Y-m-d", (time() - 3600 * 24 * $i))] : ('0'));
                $total_count = (isset($total[ date("Y-m-d", (time() - 3600 * 24 * $i))]) ?$total[ date("Y-m-d", (time() - 3600 * 24 * $i))] : (0));
            }else{
                $count = $fact['week'];
                $total_count = $total['week'];
            }
        }else{
            $count = isset($fact['month'])?$fact['month']:('0');
            $total_count = isset($total['month'])?$total['month']:('0');
        }
        $percent = round(100*$count/($total_count==0?1:$total_count));
        $percent_block .= '<td class = "middle_size" style="text-align:center">'.(empty($total_count)?'':$percent). '</td>';
    }
    //фактично виконано
    $fact_block = '';
    if(isset($fact['month']))
        $fact_block.='<td class="middle_size" style="text-align: center">'.$fact['month'].'</td>';
    else
        $fact_block.='<td style="width: 35px"></td>';
    if(isset($fact['week']))
        $fact_block.='<td class="middle_size" style="text-align: center">'.$fact['week'].'</td>';
    else
        $fact_block.='<td style="width: 35px"></td>';
    for($i=6;$i>=0;$i--){
//        var_dump(array_sum($outstanding[$bestuser]['fact'.date("Y-m-d", (time()-3600*24*$i))]));
//        die('fact'.date("Y-m-d", (time()-3600*24*$i)));
        if(isset($fact[date("Y-m-d", (time()-3600*24*$i))]))
            $fact_block.='<td class="middle_size" style="text-align: center">'.$fact[date("Y-m-d", (time()-3600*24*$i))].'</td>';
        else
            $fact_block.='<td class="middle_size" style="text-align: center"></td>';
    }
    //Прострочені
    $sql = "select count(*)iCount from llx_actioncomm
        left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
        where date(datep) between adddate(date(now()), interval -1 month) and date(now())
        and llx_actioncomm.percent not in (100, -100)
        and case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end = ".$user_id;
    if(empty($code))
        $sql.=" and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
    else{
        if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
            $sql .= " and llx_actioncomm.`code`='" . $code . "'";
        else
            $sql .= " and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                    where active = 1
                    and `type` in ('system','user')
                    and `code` not in ('AC_GLOBAL','AC_CURRENT'))";
    }
    $sql.=" and active = 1;";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    if (empty($obj->iCount))
        $outstanding = '<td></td>';
    else
        $outstanding = '<td  style="text-align: center">' . $obj->iCount . '</td>';

    //майбутнє
    $future = '';
    $sql = "select date(datep) datep, count(*) iCount from llx_actioncomm
        left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
        where date(datep) between  date(now()) and adddate(date(now()), interval +1 week)
        and case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end = ".$user_id;
    if(empty($code))
        $sql.=" and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
    else{
        if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
            $sql .= " and llx_actioncomm.`code`='" . $code . "'";
        else
            $sql .= " and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                    where active = 1
                    and `type` in ('system','user')
                    and `code` not in ('AC_GLOBAL','AC_CURRENT'))";
    }
    $sql.=" and active = 1
        group by date(datep);";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)){
        $array_result[$obj->datep] = $obj->iCount;
    }
//    var_dump($array_result);
//    die();
    for($i=0;$i<=8;$i++){
        $date = date("Y-m-d", (time() + 3600 * 24 * $i));
        if($i<=6) {
            if(isset($array_result[$date]))
                $future .= '<td  style="text-align: center">'.$array_result[$date].'</td>';
            else
                $future .= '<td></td>';
        }else {
            $sql = "select count(id) iCount  from llx_actioncomm
            left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id";
            if ($i == 7)
                $sql .= " where date(datep) between date(now()) and adddate(date(now()), interval 1 week)";
            else
                $sql .= " where date(datep) between date(now()) and adddate(date(now()), interval 1 month)";

            $sql .= " and case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end = " . $user_id . "
            and llx_actioncomm.`code` " . (empty($code) ? "<> 'AC_OTH_AUTO'" : "= '" . $code . "'") . "
            and active = 1;";
//        if($i == 7){
//            var_dump($sql);
//            die();
//        }
//        die($sql);
            $res = $db->query($sql);
            if (!$res)
                dol_print_error($db);
            $obj = $db->fetch_object($res);
            if (empty($obj->iCount))
                $future .= '<td></td>';
            else
                $future .= '<td  style="text-align: center">' . $obj->iCount . '</td>';
        }
    }
    $out.=$percent_block.$fact_block.$outstanding.$future;
//    echo '<pre>';
//    var_dump($fact, htmlspecialchars($fact_block));
//    echo '</pre>';
//    echo time()-$start;
//    die();
    $out.='</tr>';
    return $out;
    //% виконання запланованого по факту
}
function getRegionsList($id_usr){
    global $db;
    $start = time();
//Всього завдань та виконані
    $total = array();
    $fact = array();
    $outstanding = array();
    $future = array();
    for($i=0; $i<=1; $i++) {
        if($i<1)
            $period = 'month';
        else
            $period = 'week';
        //Всього завдань
        $sql = "select llx_societe.region_id, count(distinct llx_actioncomm.id) iCount  from llx_actioncomm
            left join llx_societe on `llx_societe`.`rowid` = llx_actioncomm.fk_soc
            where 1
            and fk_user_author = ".$id_usr."
            and llx_actioncomm.active = 1";
        $sql.=" and date(datep) between  adddate(date(now()), interval -1 ".$period.") and date(now())";
        $sql.=" and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                  where active = 1
                  and `type` in ('system','user')
                  and `code` not in ('AC_GLOBAL','AC_CURRENT'))
            group by llx_societe.region_id";

        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        while($obj = $db->fetch_object($res)) {
            $total[$obj->region_id][$period] = $obj->iCount;
        }
        //Фактично виконаних
        $sql = "select llx_societe.region_id, count(distinct llx_actioncomm.id) iCount  from llx_actioncomm
            left join llx_societe on `llx_societe`.`rowid` = llx_actioncomm.fk_soc
            where 1
            and fk_user_author = ".$id_usr."
            and llx_actioncomm.active = 1";
        $sql.=" and date(datep) between  adddate(date(now()), interval -1 ".$period.") and date(now())";
        $sql.=" and llx_actioncomm.id in (select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1)";
        $sql.=" and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                  where active = 1
                  and `type` in ('system','user')
                  and `code` not in ('AC_GLOBAL','AC_CURRENT'))
            group by llx_societe.region_id";
//        if(empty($subdiv_id)&&$i==1){
//echo '<pre>';
//var_dump($sql);
//echo '</pre>';
//        die();
//        }
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        while($obj = $db->fetch_object($res)) {
            $fact[$obj->region_id][$period] = $obj->iCount;
        }

    }
    $sql = "select llx_societe.region_id, date(llx_actioncomm.datep) as datep, count(distinct llx_actioncomm.id) iCount  from llx_actioncomm
            left join llx_societe on `llx_societe`.`rowid` = llx_actioncomm.fk_soc
            where 1
            and fk_user_author = ".$id_usr."
            and llx_actioncomm.active = 1 and date(datep) between  adddate(date(now()), interval -6 day) and date(now())
            and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                  where active = 1
                  and `type` in ('system','user')
                  and `code` not in ('AC_GLOBAL','AC_CURRENT'))
            group by llx_societe.region_id, date(llx_actioncomm.datep)";
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        while($obj = $db->fetch_object($res)) {
            $total[$obj->region_id][$obj->datep] = $obj->iCount;
        }
    $sql = "select llx_societe.region_id, date(llx_actioncomm.datep) as datep, count(distinct llx_actioncomm.id) iCount  from llx_actioncomm
            left join llx_societe on `llx_societe`.`rowid` = llx_actioncomm.fk_soc
            where 1
            and fk_user_author = ".$id_usr."
            and llx_actioncomm.active = 1 and date(datep) between  adddate(date(now()), interval -6 day) and date(now())
            and llx_actioncomm.id in (select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1)
            and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                  where active = 1
                  and `type` in ('system','user')
                  and `code` not in ('AC_GLOBAL','AC_CURRENT'))
            group by llx_societe.region_id, date(llx_actioncomm.datep)";
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        while($obj = $db->fetch_object($res)) {
            $fact[$obj->region_id][$obj->datep] = $obj->iCount;
        }
    //Прострочені
    $sql = "select llx_societe.region_id,  count(distinct llx_actioncomm.id) iCount  from llx_actioncomm
            left join llx_societe on `llx_societe`.`rowid` = llx_actioncomm.fk_soc
            where 1
            and fk_user_author = ".$id_usr."
            and llx_actioncomm.active = 1
            and date(datep) between  adddate(date(now()), interval -1 month) and date(now())
            and llx_actioncomm.percent not in (100, -100)
            and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                  where active = 1
                  and `type` in ('system','user')
                  and `code` not in ('AC_GLOBAL','AC_CURRENT'))
            group by llx_societe.region_id";
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        while($obj = $db->fetch_object($res)) {
            $outstanding[$obj->region_id] = $obj->iCount;
        }
    //Майбутнє
    $sql = "select llx_societe.region_id, date(llx_actioncomm.datep) datep, count(distinct llx_actioncomm.id) iCount  from llx_actioncomm
            left join llx_societe on `llx_societe`.`rowid` = llx_actioncomm.fk_soc
            where 1
            and fk_user_author = ".$id_usr."
            and llx_actioncomm.active = 1
            and date(datep) between date(now()) and adddate(date(now()), interval +6 day)
            and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                  where active = 1
                  and `type` in ('system','user')
                  and `code` not in ('AC_GLOBAL','AC_CURRENT'))
            group by llx_societe.region_id, date(llx_actioncomm.datep)";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)){
        $future[$obj->region_id][$obj->datep] = $obj->iCount;
    }
    for($i=0; $i<=1; $i++){
        if($i<1)
            $period = 'week';
        else
            $period = 'month';
        $sql = "select llx_societe.region_id,  count(distinct llx_actioncomm.id) iCount  from llx_actioncomm
            left join llx_societe on `llx_societe`.`rowid` = llx_actioncomm.fk_soc
            where 1
            and fk_user_author = ".$id_usr."
            and llx_actioncomm.active = 1
            and date(datep) between date(now()) and adddate(date(now()), interval +1 ".$period.")
            and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                  where active = 1
                  and `type` in ('system','user')
                  and `code` not in ('AC_GLOBAL','AC_CURRENT'))
            group by llx_societe.region_id";
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
        while($obj = $db->fetch_object($res)){
            $future[$obj->region_id][$period] = $obj->iCount;
        }
    }
//echo '<pre>';
//var_dump(implode(',',array_keys($fact)));
//echo '</pre>';
//die();
    $sql = "select regions.rowid, regions.name, states.name as state_name from regions
    left join states on states.rowid = regions.state_id
    where regions.rowid in (select fk_id from llx_user_regions where fk_user = ".$id_usr." and active = 1)
    union select '', 'Район не вказано', '' order by name";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $out = '';
    $num = 0;
//    llxHeader();
    global $langs;
    while($obj = $db->fetch_object($res)){
        $class_row = fmod($num,2)==0?'impare':'pare';
        $out.='<tr id="region'.$obj->rowid.'" class="regions'.$id_usr.' region">';
        $state_name = $obj->state_name;
        $symbols = array('а','о','у','и','і','ї','є','е','ю','я');
//        var_dump(in_array(mb_substr($obj->state_name,2,1,'UTF-8'),$symbols));
//        die();
        for($i=3; $i<=mb_strlen($obj->state_name,'UTF-8');$i++){
            if(in_array(mb_substr($obj->state_name,$i,1,'UTF-8'),$symbols)) {
                $state_name = mb_substr($obj->state_name,0,$i,'UTF-8').'.';
                break;
            }
        }

        $out.='<td colspan="2">'.(empty($obj->rowid)?'':'<a href="/dolibarr/htdocs/responsibility/sale/area.php?idmenu=10425&mainmenu=area&leftmenu=&id_usr='.$id_usr.'&state_filter=' . $obj->rowid . '" target="_blank">').$obj->name.(!empty($state_name)?' ('.$state_name.')':'').(empty($obj->rowid)?'':'</a>').'</td>';
        $out.='<td></td>';
        

//        for($i=0;$i<9;$i++){
//            $out.='<td></td>';
//        }
         //% виконання запланованого по факту
            for($i=8; $i>=0; $i--){
                if($i < 8) {
                    $percent = '';
                    if($i<7) {
                        $count = (isset($fact[$obj->rowid][date("Y-m-d", (time() - 3600 * 24 * $i))]) ? $fact[$obj->rowid][date("Y-m-d", (time() - 3600 * 24 * $i))] : ('0'));
                        $totalcount = (isset($total[$obj->rowid][date("Y-m-d", (time() - 3600 * 24 * $i))]) ? $total[$obj->rowid][date("Y-m-d", (time() - 3600 * 24 * $i))] : (''));
                    }else{
                        $count = isset($fact[$obj->rowid]['week'])?$fact[$obj->rowid]['week']:'';
                        $totalcount = isset($total[$obj->rowid]['week'])?$total[$obj->rowid]['week']:'';
                    }
                }else{
                    $count = isset($fact[$obj->rowid]['month'])?$fact[$obj->rowid]['month']:('0');
                    $totalcount = isset($total[$obj->rowid]['month'])?$total[$obj->rowid]['month']:('0');
                }
                if(!empty($totalcount))
                    $percent = round(100*$count/($totalcount==0?1:$totalcount));
                $out .= '<td class="middle_size" style="text-align: center;">' .$percent. '</td>';
            }
        //Фактично виконано
        if(isset($fact[$obj->rowid]['month']))
                $out.='<td class="middle_size" style="text-align: center">'.$fact[$obj->rowid]['month'].'</td>';
            else
                $out.='<td style="width: 35px"></td>';
        if(isset($fact[$obj->rowid]['week']))
                $out.='<td class="middle_size" style="text-align: center">'.$fact[$obj->rowid]['week'].'</td>';
            else
                $out.='<td style="width: 35px"></td>';
        for($i=6;$i>=0;$i--){
    //        var_dump(array_sum($outstanding[$bestuser]['fact'.date("Y-m-d", (time()-3600*24*$i))]));
    //        die('fact'.date("Y-m-d", (time()-3600*24*$i)));
            if(isset($fact[$obj->rowid][date("Y-m-d", (time()-3600*24*$i))]))
                $out.='<td class="middle_size" style="text-align: center">'.$fact[$obj->rowid][date("Y-m-d", (time()-3600*24*$i))].'</td>';
            else
                $out.='<td class="middle_size" style="text-align: center"></td>';
        }

        //Прострочено
        $out.='<td id="outstanding'.$obj->rowid.'" style="text-align: center; cursor: pointer;" onclick="ShowOutStandingRegion('.$obj->rowid.', '.$id_usr.');">'.(isset($outstanding[$obj->rowid])?$outstanding[$obj->rowid]:0).'</td>';

        //майбутнє (план)
        for($i=0; $i<9; $i++){
            $value = '';
            if($i < 8) {
                if($i < 7)
                    $value =  (isset($future[$obj->rowid][date("Y-m-d", (time() + 3600 * 24 * $i))]) ? $future[$obj->rowid][date("Y-m-d", (time() + 3600 * 24 * $i))] : (''));
                else {
                    if(!empty($future[$obj->rowid]['week']))
                        $value = $future[$obj->rowid]['week'];
                }

                $out .= '<td  class="middle_size" style="text-align: center">' . $value . '</td>';
            }else {
                if(!empty($future[$obj->rowid]['month']))
                    $value = $future[$obj->rowid]['month'];
                $out .= '<td class="middle_size" style="text-align: center">' . $value . '</td>';
            }
        }
        $out .='</tr>';
    }

//    llxHeader();
//    print'<table style="width: 100%"><tbody>'.$out.'</tbody>';
//    print (time()-$start);
//    die();

    return $out;    

}
function getLineActiveTask($subdiv_id, $class, $code = '', $title=''){
//    var_dump($subdiv_id, $class, $code);
//    die();
    if($code=='all')
        $code='';
    elseif(substr($code,0,1) == "'" && substr($code,strlen($code)-1,1)=="'")
        $code = substr($code, 1, strlen($code)-2);
//    die($code);

    global $db;
    $start = time();
//Всього завдань та виконані
    $total = array();
    $fact = array();
    $outstanding = array();
    $future = array();
    for($i=0; $i<=1; $i++) {
        if($i<1)
            $period = 'month';
        else
            $period = 'week';
        //Всього завдань
        $sql = "select responsibility.alias respon_id, count(distinct llx_actioncomm.id) iCount from llx_actioncomm
            left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
            left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end
            left join responsibility on llx_user.respon_id = responsibility.rowid";
        if(!empty($subdiv_id))
            $sql.=" where llx_user.subdiv_id = " . $subdiv_id;
        else
            $sql.=" where 1";

        $sql.=" and date(datep) between  adddate(date(now()), interval -1 ".$period.") and date(now())";
        if(empty($code))
            $sql.=" and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
        else{
            if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
                $sql .= " and llx_actioncomm.`code`='" . $code . "'";
            else
                $sql .= " and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                    where active = 1
                    and `type` in ('system','user')
                    and `code` not in ('AC_GLOBAL','AC_CURRENT'))";
        }
         $sql.=" and llx_actioncomm.active = 1";
         $sql.=" and llx_user.subdiv_id in (select rowid from `subdivision` where active = 1)";
         $sql.=" group by responsibility.alias";

        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        while($obj = $db->fetch_object($res)) {
            $total[$obj->respon_id][$period] = $obj->iCount;
        }
        //Фактично виконаних
        $sql = "select responsibility.alias respon_id, count(distinct llx_actioncomm.id) iCount from llx_actioncomm
            left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
            left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end
            left join responsibility on llx_user.respon_id = responsibility.rowid";
        if(!empty($subdiv_id))
            $sql.=" where llx_user.subdiv_id = " . $subdiv_id;
        else
            $sql.=" where 1";
        $sql.=" and  date(datep) between  adddate(date(now()), interval -1 ".$period.") and date(now())";
        if(empty($code))
            $sql.=" and (llx_actioncomm.`code`in ('AC_GLOBAL','AC_CURRENT') or llx_actioncomm.id in (select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1))";
        else{
            if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
                $sql .= " and llx_actioncomm.`code`='" . $code . "'";
            else
                $sql .= " and llx_actioncomm.id in (select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1)";
        }
        $sql.=" and llx_actioncomm.percent = 100
            and llx_actioncomm.active = 1
            and llx_user.subdiv_id in (select rowid from `subdivision` where active = 1)
            group by responsibility.alias";
//        if(empty($subdiv_id)&&$i==1){
//echo '<pre>';
//var_dump($sql);
//echo '</pre>';
//        die();
//        }
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        while($obj = $db->fetch_object($res)) {
            $fact[$obj->respon_id][$period] = $obj->iCount;
        }

    }
    $sql = "select responsibility.alias respon_id, date(datep) datep, count(distinct llx_actioncomm.id) iCount from llx_actioncomm
    left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
    left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end
    left join responsibility on llx_user.respon_id = responsibility.rowid";
    if(!empty($subdiv_id))
        $sql.=" where llx_user.subdiv_id = " . $subdiv_id;
    else
        $sql.=" where 1";
    $sql.=" and date(datep) between  adddate(date(now()), interval -6 day) and date(now())";
    if(empty($code))
        $sql.=" and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
    else{
        if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
            $sql .= " and llx_actioncomm.`code`='" . $code . "'";
        else
            $sql .= " and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                where active = 1
                and `type` in ('system','user')
                and `code` not in ('AC_GLOBAL','AC_CURRENT'))";
    }
    $sql.=" and llx_actioncomm.active = 1
    and llx_user.subdiv_id in (select rowid from `subdivision` where active = 1)
    group by responsibility.alias, date(datep);";

    $res = $db->query($sql);
    if (!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)){
        $total[$obj->respon_id][$obj->datep] = $obj->iCount;
    }

    $sql = "select responsibility.alias respon_id, date(datep) datep, count(distinct llx_actioncomm.id) iCount from llx_actioncomm
        left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
        left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end
        left join responsibility on llx_user.respon_id = responsibility.rowid";
    if(!empty($subdiv_id))
        $sql.=" where llx_user.subdiv_id = " . $subdiv_id;
    else
        $sql.=" where 1";
    $sql.=" and date(datep) between  adddate(date(now()), interval -6 day) and date(now())";
    if(empty($code))
        $sql.=" and (llx_actioncomm.`code`in ('AC_GLOBAL','AC_CURRENT') or llx_actioncomm.id in (select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1))";
    else{
            if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
                $sql .= " and llx_actioncomm.`code`='" . $code . "'";
            else
                $sql .= " and llx_actioncomm.id in (select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1)";
    }
    $sql.=" and llx_actioncomm.percent = 100
        and llx_actioncomm.active = 1
        group by responsibility.alias, date(datep);";
//    if($code == 'AC_CUST'){

//    }
    $res = $db->query($sql);
    if (!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)){
        $fact[$obj->respon_id][$obj->datep] = $obj->iCount;
    }
    //Прострочені
    $sql = "select responsibility.alias respon_id, count(distinct llx_actioncomm.id)iCount from llx_actioncomm
        left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
        left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end
        left join responsibility on llx_user.respon_id = responsibility.rowid";
    $sql.=" where 1";
    $sql.=" and date(datep) between adddate(date(now()), interval -1 month) and date(now())
        and llx_actioncomm.percent not in (100, -100) ";
    if(empty($code))
        $sql.=" and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
    else{
        if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
            $sql .= " and llx_actioncomm.`code`='" . $code . "'";
        else
            $sql .= " and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                    where active = 1
                    and `type` in ('system','user')
                    and `code` not in ('AC_GLOBAL','AC_CURRENT'))";
    }
    $sql.=" and llx_actioncomm.active = 1
            and llx_user.subdiv_id = ". $subdiv_id."
            group by responsibility.alias";
//if(empty($code)) {
//echo '<pre>';
//var_dump($sql);
//echo '</pre>';
//        die();
//}

    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)){
        $outstanding[$obj->respon_id] = $obj->iCount;
    }
 //майбутнє
    $sql = "select responsibility.alias respon_id, date(datep) datep, count(distinct llx_actioncomm.id) iCount from llx_actioncomm
        left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
        left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end
        left join responsibility on llx_user.respon_id = responsibility.rowid";
    if(!empty($subdiv_id))
        $sql.=" where llx_user.subdiv_id = " . $subdiv_id;
    else
        $sql.=" where 1";
    $sql.=" and date(datep) between  date(now()) and adddate(date(now()), interval +1 week)";
    if(empty($code))
        $sql.=" and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
    else{
        if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
            $sql .= " and llx_actioncomm.`code`='" . $code . "'";
        else
            $sql .= " and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                    where active = 1
                    and `type` in ('system','user')
                    and `code` not in ('AC_GLOBAL','AC_CURRENT'))";
    }

    $sql.=" and llx_actioncomm.active = 1
        group by responsibility.alias, date(datep);";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)){
        $future[$obj->respon_id][$obj->datep] = $obj->iCount;
    }
    for($i=0; $i<=1; $i++){
        if($i<1)
            $period = 'week';
        else
            $period = 'month';
        $sql = "select responsibility.alias respon_id, count(id) iCount  from llx_actioncomm
        left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
        left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end
        left join responsibility on llx_user.respon_id = responsibility.rowid";
        $sql .= " where 1";
        $sql .= " and date(datep) between date(now()) and adddate(date(now()), interval 1 ".$period.")";
        if(empty($code))
            $sql.=" and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
        else{
            if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
                $sql .= " and llx_actioncomm.`code`='" . $code . "'";
            else
                $sql .= " and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                        where active = 1
                        and `type` in ('system','user')
                        and `code` not in ('AC_GLOBAL','AC_CURRENT'))";
        }
        $sql .= " and llx_actioncomm.active = 1
        and llx_user.subdiv_id = ".$subdiv_id."
        group by llx_user.respon_id;";
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
        while($obj = $db->fetch_object($res)){
            $future[$obj->respon_id][$period] = $obj->iCount;
        }
    }
//    echo '<pre>';
//    var_dump($outstanding, time()-$start);
//    echo '</pre>';
//    die();
    $sql = "select distinct responsibility.`alias` name from llx_user
        inner join responsibility on responsibility.rowid = llx_user.respon_id
        where llx_user.active = 1
        and subdiv_id = ".$subdiv_id."
        order by `name`";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);

    $out = '';
    $num = 0;
    global $langs;
    while($obj = $db->fetch_object($res)){
        $class_row = fmod($num,2)==0?'impare':'pare';
        $out.='<tr id="'.$class.$obj->name.'" class="'.$class.' '.$class_row.' lineactive lineactive_'.$subdiv_id.'">';
        $out.='<td colspan="2">'.$langs->trans($obj->name).'</td>';
        if(in_array($code, array('AC_GLOBAL','AC_CURRENT')))
            $out.='<td></td>';
        else
            $out.='<td><button id="btnLn'.(empty($code)?'AllTask':$code).$obj->name.$subdiv_id.'" onclick="ShowActionsByUsers('.$subdiv_id.", '".(empty($code)?'AllTask':$code)."','".$obj->name."'".');"><img id="imgLn'.(empty($code)?'AllTask':$code).$obj->name.$subdiv_id.'" src="/dolibarr/htdocs/theme/eldy/img/1downarrow.png"></button></td>';

//        for($i=0;$i<9;$i++){
//            $out.='<td></td>';
//        }
         //% виконання запланованого по факту
            for($i=8; $i>=0; $i--){
                if($i < 8) {
                    $percent = '';
                    if($i<7) {
                        $count = (isset($fact[$obj->name][date("Y-m-d", (time() - 3600 * 24 * $i))]) ? $fact[$obj->name][date("Y-m-d", (time() - 3600 * 24 * $i))] : ('0'));
                        $totalcount = (isset($total[$obj->name][date("Y-m-d", (time() - 3600 * 24 * $i))]) ? $total[$obj->name][date("Y-m-d", (time() - 3600 * 24 * $i))] : (''));
                    }else{
                        $count = isset($fact[$obj->name]['week'])?$fact[$obj->name]['week']:'';
                        $totalcount = isset($total[$obj->name]['week'])?$total[$obj->name]['week']:'';
                    }
                }else{
                    $count = isset($fact[$obj->name]['month'])?$fact[$obj->name]['month']:('0');
                    $totalcount = isset($total[$obj->name]['month'])?$total[$obj->name]['month']:('0');
                }
                if(!empty($totalcount))
                    $percent = round(100*$count/($totalcount==0?1:$totalcount));
                $out .= '<td class="middle_size" style="text-align: center;">' .$percent. '</td>';
            }
        //Фактично виконано
        if(isset($fact[$obj->name]['month']))
                $out.='<td class="middle_size" style="text-align: center">'.$fact[$obj->name]['month'].'</td>';
            else
                $out.='<td style="width: 35px"></td>';
        if(isset($fact[$obj->name]['week']))
                $out.='<td class="middle_size" style="text-align: center">'.$fact[$obj->name]['week'].'</td>';
            else
                $out.='<td style="width: 35px"></td>';
        for($i=6;$i>=0;$i--){
    //        var_dump(array_sum($outstanding[$bestuser]['fact'.date("Y-m-d", (time()-3600*24*$i))]));
    //        die('fact'.date("Y-m-d", (time()-3600*24*$i)));
            if(isset($fact[$obj->name][date("Y-m-d", (time()-3600*24*$i))]))
                $out.='<td class="middle_size" style="text-align: center">'.$fact[$obj->name][date("Y-m-d", (time()-3600*24*$i))].'</td>';
            else
                $out.='<td class="middle_size" style="text-align: center"></td>';
        }
        //Прострочено
        $out.='<td class="middle_size" style="text-align: center; ">'.(isset($outstanding[$obj->name])?$outstanding[$obj->name]:0).'</td>';

        //майбутнє (план)
        for($i=0; $i<9; $i++){
            $value = '';
            if($i < 8) {
                if($i < 7)
                    $value =  (isset($future[$obj->name][date("Y-m-d", (time() + 3600 * 24 * $i))]) ? $future[$obj->name][date("Y-m-d", (time() + 3600 * 24 * $i))] : (''));
                else {
                    if(!empty($future[$obj->name]['week']))
                        $value = $future[$obj->name]['week'];
                }

                $out .= '<td  class="middle_size" style="text-align: center">' . $value . '</td>';
            }else {
                if(!empty($future[$obj->name]['month']))
                    $value = $future[$obj->name]['month'];
                $out .= '<td class="middle_size" style="text-align: center">' . $value . '</td>';
            }
        }
        $out .='</tr>';
    }

//    llxHeader();
//    echo '<pre>';
////    var_dump($total);
//    var_dump('<table><tbody>'.$out.'</tbody>', time()-$start);
//    echo '</pre>';
//    die();
    return $out;
}
function getActionsByUsers($subdiv_id, $class, $code = '', $respon_alias='', $title=''){
//    var_dump($subdiv_id, $class, $code);
//    die();
    if($code=='all')
        $code='';
    elseif(substr($code,0,1) == "'" && substr($code,strlen($code)-1,1)=="'")
        $code = substr($code, 1, strlen($code)-2);
//    die($code);

    global $db;
    $start = time();
//Всього завдань та виконані
    $respon = array();
    if(!empty($respon_alias)){
        $sql = "select rowid from responsibility where alias = '".$respon_alias."'";
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
        if($db->num_rows($res)>0)
            while($obj = $db->fetch_object($res)){
                $respon[] = $obj->rowid;
            }
    }
    $total = array();
    $fact = array();
    $outstanding = array();
    $future = array();
    for($i=0; $i<=1; $i++) {
        if($i<1)
            $period = 'month';
        else
            $period = 'week';
        //Всього завдань
        $sql = "select llx_user.rowid, count(distinct llx_actioncomm.id) iCount from llx_actioncomm
            left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
            left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end";
        if(!empty($subdiv_id))
            $sql.=" where llx_user.subdiv_id = " . $subdiv_id;
        else
            $sql.=" where 1";

        $sql.=" and date(datep) between  adddate(date(now()), interval -1 ".$period.") and date(now())";
        if(empty($code))
            $sql.=" and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
        else{
            if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
                $sql .= " and llx_actioncomm.`code`='" . $code . "'";
            else
                $sql .= " and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                    where active = 1
                    and `type` in ('system','user')
                    and `code` not in ('AC_GLOBAL','AC_CURRENT'))";
        }
         $sql.=" and llx_actioncomm.active = 1";
         $sql.=" group by llx_user.rowid";

        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        while($obj = $db->fetch_object($res)) {
            $total[$obj->rowid][$period] = $obj->iCount;
        }
        //Фактично виконаних
        $sql = "select llx_user.rowid, count(distinct llx_actioncomm.id) iCount from llx_actioncomm
            left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
            left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end";
        if(!empty($subdiv_id))
            $sql.=" where llx_user.subdiv_id = " . $subdiv_id;
        else
            $sql.=" where 1";
        $sql.=" and  date(datep) between  adddate(date(now()), interval -1 ".$period.") and date(now())";
        if(empty($code))
            $sql.=" and (llx_actioncomm.`code`in ('AC_GLOBAL','AC_CURRENT') or llx_actioncomm.id in (select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1))";
        else{
            if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
                $sql .= " and llx_actioncomm.`code`='" . $code . "'";
            else
                $sql .= " and llx_actioncomm.id in (select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1)";
        }
        $sql.=" and llx_actioncomm.percent = 100
            and llx_actioncomm.active = 1
            and llx_user.subdiv_id in (select rowid from `subdivision` where active = 1)
            group by llx_user.rowid";
//        if(empty($subdiv_id)&&$i==1){
//            echo '<pre>';
//            var_dump($sql);
//            echo '</pre>';
//            die();
//        }
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        while($obj = $db->fetch_object($res)) {
            $fact[$obj->rowid][$period] = $obj->iCount;
        }

    }
    $sql = "select llx_user.rowid, date(datep) datep, count(distinct llx_actioncomm.id) iCount from llx_actioncomm
    left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
    left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end";
    if(!empty($subdiv_id))
        $sql.=" where llx_user.subdiv_id = " . $subdiv_id;
    else
        $sql.=" where 1";
    $sql.=" and date(datep) between  adddate(date(now()), interval -6 day) and date(now())";
    if(empty($code))
        $sql.=" and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
    else{
        if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
            $sql .= " and llx_actioncomm.`code`='" . $code . "'";
        else
            $sql .= " and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                where active = 1
                and `type` in ('system','user')
                and `code` not in ('AC_GLOBAL','AC_CURRENT'))";
    }
    $sql.=" and llx_actioncomm.active = 1
    and llx_user.subdiv_id in (select rowid from `subdivision` where active = 1)
    group by llx_user.rowid, date(datep);";

    $res = $db->query($sql);
    if (!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)){
        $total[$obj->rowid][$obj->datep] = $obj->iCount;
    }

    $sql = "select llx_user.rowid, date(datep) datep, count(distinct llx_actioncomm.id) iCount from llx_actioncomm
        left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
        left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end";
    if(!empty($subdiv_id))
        $sql.=" where llx_user.subdiv_id = " . $subdiv_id;
    else
        $sql.=" where 1";
    $sql.=" and date(datep) between  adddate(date(now()), interval -6 day) and date(now())";
    if(empty($code))
        $sql.=" and (llx_actioncomm.`code`in ('AC_GLOBAL','AC_CURRENT') or llx_actioncomm.id in (select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1))";
    else{
            if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
                $sql .= " and llx_actioncomm.`code`='" . $code . "'";
            else
                $sql .= " and llx_actioncomm.id in (select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1)";
    }
    $sql.=" and llx_actioncomm.percent = 100
        and llx_actioncomm.active = 1
        and llx_user.subdiv_id in (select rowid from `subdivision` where active = 1)
        group by llx_user.rowid, date(datep);";
//    if($code == 'AC_CUST'){
//        echo '<pre>';
//        var_dump($sql);
//        echo '</pre>';
//        die();
//    }
    $res = $db->query($sql);
    if (!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)){
        $fact[$obj->rowid][$obj->datep] = $obj->iCount;
    }
    //Прострочені
    $sql = "select llx_user.rowid, count(distinct llx_actioncomm.id)iCount from llx_actioncomm
        left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
        left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end";
    $sql.=" where 1";
    $sql.=" and date(datep) between adddate(date(now()), interval -1 month) and date(now())
        and llx_actioncomm.percent not in (100, -100) ";
    if(empty($code))
        $sql.=" and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
    else{
        if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
            $sql .= " and llx_actioncomm.`code`='" . $code . "'";
        else
            $sql .= " and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                    where active = 1
                    and `type` in ('system','user')
                    and `code` not in ('AC_GLOBAL','AC_CURRENT'))";
    }
    $sql.=" and llx_actioncomm.active = 1
            and llx_user.subdiv_id in (select rowid from `subdivision` where active = 1)
            group by llx_user.rowid";
//if(empty($code)) {
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();
//}

    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)){
        $outstanding[$obj->rowid] = $obj->iCount;
    }
 //майбутнє
    $sql = "select llx_user.rowid, date(datep) datep, count(distinct llx_actioncomm.id) iCount from llx_actioncomm
        left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
        left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end";
    if(!empty($subdiv_id))
        $sql.=" where llx_user.subdiv_id = " . $subdiv_id;
    else
        $sql.=" where 1";
    $sql.=" and date(datep) between  date(now()) and adddate(date(now()), interval +1 week)";
    if(empty($code))
        $sql.=" and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
    else{
        if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
            $sql .= " and llx_actioncomm.`code`='" . $code . "'";
        else
            $sql .= " and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                    where active = 1
                    and `type` in ('system','user')
                    and `code` not in ('AC_GLOBAL','AC_CURRENT'))";
    }

    $sql.=" and llx_actioncomm.active = 1
        and llx_user.subdiv_id in (select rowid from `subdivision` where active = 1)
        group by llx_user.rowid, date(datep);";

    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)){
        $future[$obj->rowid][$obj->datep] = $obj->iCount;
    }
    for($i=0; $i<=1; $i++){
        if($i<1)
            $period = 'week';
        else
            $period = 'month';
        $sql = "select llx_user.rowid, count(id) iCount  from llx_actioncomm
        left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
        left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end";
        $sql .= " where 1";
        $sql .= " and date(datep) between date(now()) and adddate(date(now()), interval 1 ".$period.")";
        if(empty($code))
            $sql.=" and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
        else{
            if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
                $sql .= " and llx_actioncomm.`code`='" . $code . "'";
            else
                $sql .= " and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                        where active = 1
                        and `type` in ('system','user')
                        and `code` not in ('AC_GLOBAL','AC_CURRENT'))";
        }
        $sql .= " and llx_actioncomm.active = 1
        and llx_user.subdiv_id in (select rowid from `subdivision` where active = 1)
        group by llx_user.rowid;";
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
        while($obj = $db->fetch_object($res)){
            $future[$obj->rowid][$period] = $obj->iCount;
        }
    }
//    echo '<pre>';
//    var_dump($outstanding, time()-$start);
//    echo '</pre>';
//    die();
    $sql = "select rowid, `lastname`, `firstname` from llx_user
        where active = 1
        and subdiv_id = ".$subdiv_id;
    if(count($respon)>0)
        $sql.=" and (llx_user.respon_id in (".implode(',',$respon).") or llx_user.respon_id2 in (".implode(',',$respon)."))";
    $sql.=" order by `lastname`";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $bestuserID = 0;
    $maxCount = 0;
    while($obj = $db->fetch_object($res)){
        if($maxCount < $fact[$obj->rowid]['week']){
            $maxCount = $fact[$obj->rowid]['week'];
            $bestuserID = $obj->rowid;
        }
    }
//    var_dump($code);
//    die();
    mysqli_data_seek($res,0);
    $out = '';
    $num = 0;
    if($code == 'AC_GLOBAL')
        $lnk = '/dolibarr/htdocs/global_plan.php?idmenu=10421&mainmenu=global_task&leftmenu=';
    elseif($code == 'AC_CURRENT')
        $lnk = '/dolibarr/htdocs/current_plan.php?idmenu=10423&mainmenu=current_task&leftmenu=';
    elseif($code == 'AC_CUST')
        $lnk = '/dolibarr/htdocs/responsibility/sale/area.php?idmenu=10425&mainmenu=area&leftmenu=';
    while($obj = $db->fetch_object($res)){
        if(empty($code))
            $lnk = 'onclick="SpyMode('.$obj->rowid.')"';
//        $class_row = fmod($num,2)==0?'impare':'pare';
        $out.='<tr id="'.$class.$obj->rowid.'" class="'.$class.($bestuserID == $obj->rowid?' bestvalue ':'').' userlist '.$subdiv_id.$respon_alias.' '.$code.'_'.$subdiv_id.'">';
        $out.='<td colspan="2"><a '.(empty($code)?$lnk.' class="link"':'href="'.$lnk.'&user_id='.$obj->rowid.'"').' target="_blank">'.$obj->lastname.' '.mb_substr($obj->firstname, 0,1,'UTF-8').'.</a></td>';
        if(in_array($code, array('AC_GLOBAL','AC_CURRENT'))||!in_array($respon_alias, array('sale', 'purchase', 'service')))
            $out.='<td></td>';
        else {
            $functionName='';
            switch($respon_alias){
                case 'sale':{
                    $functionName = 'getRegionsList';
                }break;
                case 'purchase':{
                    $functionName = 'getLineActiveList';
                }break;
                case 'service':{
                    $functionName = 'getLineActiveService';
                }break;
            }
            $out .= '<td><button id="btnUsr' . $obj->rowid . '" onclick="'.$functionName.'(' . $obj->rowid . ', $(this));"><img id="imgUsr' . $obj->rowid . '" src="/dolibarr/htdocs/theme/eldy/img/1downarrow.png"></button></td>';
        }
         //% виконання запланованого по факту
            for($i=8; $i>=0; $i--){
                if($i < 8) {
                    $percent = '';
                    if($i<7) {
                        $count = (isset($fact[$obj->rowid][date("Y-m-d", (time() - 3600 * 24 * $i))]) ? $fact[$obj->rowid][date("Y-m-d", (time() - 3600 * 24 * $i))] : ('0'));
                        $totalcount = (isset($total[$obj->rowid][date("Y-m-d", (time() - 3600 * 24 * $i))]) ? $total[$obj->rowid][date("Y-m-d", (time() - 3600 * 24 * $i))] : (''));
                    }else{
                        $count = isset($fact[$obj->rowid]['week'])?$fact[$obj->rowid]['week']:'';
                        $totalcount = isset($total[$obj->rowid]['week'])?$total[$obj->rowid]['week']:'';
                    }
                }else{
                    $count = isset($fact[$obj->rowid]['month'])?$fact[$obj->rowid]['month']:('0');
                    $totalcount = isset($total[$obj->rowid]['month'])?$total[$obj->rowid]['month']:('0');
                }
                if(!empty($totalcount))
                    $percent = round(100*$count/($totalcount==0?1:$totalcount));
                $out .= '<td class="middle_size" style="text-align: center;">' .$percent. '</td>';
            }
        //Фактично виконано
        if(isset($fact[$obj->rowid]['month']))
                $out.='<td class="middle_size" style="text-align: center">'.$fact[$obj->rowid]['month'].'</td>';
            else
                $out.='<td style="width: 35px"></td>';
        if(isset($fact[$obj->rowid]['week']))
                $out.='<td class="middle_size" style="text-align: center">'.$fact[$obj->rowid]['week'].'</td>';
            else
                $out.='<td style="width: 35px"></td>';
        for($i=6;$i>=0;$i--){
    //        var_dump(array_sum($outstanding[$bestuser]['fact'.date("Y-m-d", (time()-3600*24*$i))]));
    //        die('fact'.date("Y-m-d", (time()-3600*24*$i)));
            if(isset($fact[$obj->rowid][date("Y-m-d", (time()-3600*24*$i))]))
                $out.='<td class="middle_size" style="text-align: center">'.$fact[$obj->rowid][date("Y-m-d", (time()-3600*24*$i))].'</td>';
            else
                $out.='<td class="middle_size" style="text-align: center"></td>';
        }
        //Прострочено
        $out.='<td class="middle_size" style="text-align: center; ">'.(isset($outstanding[$obj->rowid])?$outstanding[$obj->rowid]:0).'</td>';

        //майбутнє (план)
        for($i=0; $i<9; $i++){
            $value = '';
            if($i < 8) {
                if($i < 7)
                    $value =  (isset($future[$obj->rowid][date("Y-m-d", (time() + 3600 * 24 * $i))]) ? $future[$obj->rowid][date("Y-m-d", (time() + 3600 * 24 * $i))] : (''));
                else {
                    if(!empty($future[$obj->rowid]['week']))
                        $value = $future[$obj->rowid]['week'];
                }

                $out .= '<td  class="middle_size" style="text-align: center">' . $value . '</td>';
            }else {
                if(!empty($future[$obj->rowid]['month']))
                    $value = $future[$obj->rowid]['month'];
                $out .= '<td class="middle_size" style="text-align: center">' . $value . '</td>';
            }
        }
        $out .='</tr>';
    }
    $search = array('lineactive','impare','subdivision');
    $out = str_replace($search,'',$out);
//    llxHeader();
//    print'<table><tbody>'.$out.'</tbody></table>';
//    die();
    return $out;
}
function getActionsBySub($class, $code){
    $class=trim($class);
    if($code=='all')
        $code='';
    elseif(substr($code,0,1) == "'" && substr($code,strlen($code)-1,1)=="'")
        $code = substr($code, 1, strlen($code)-2);

    global $db;
    $start = time();
//Всього завдань та виконані
    $total = array();
    $fact = array();
    $outstanding = array();
    $future = array();
    for($i=0; $i<=1; $i++) {
        if($i<1)
            $period = 'month';
        else
            $period = 'week';
        //Всього завдань
        $sql = "select llx_user.subdiv_id, count(distinct llx_actioncomm.id) iCount from llx_actioncomm
            left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
            left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end";
        if(!empty($subdiv_id))
            $sql.=" where llx_user.subdiv_id = " . $subdiv_id;
        else
            $sql.=" where 1";

        $sql.=" and date(datep) between  adddate(date(now()), interval -1 ".$period.") and date(now())";
        if(empty($code))
            $sql.=" and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
        else{
            if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
                $sql .= " and llx_actioncomm.`code`='" . $code . "'";
            else
                $sql .= " and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                    where active = 1
                    and `type` in ('system','user')
                    and `code` not in ('AC_GLOBAL','AC_CURRENT'))";
        }
         $sql.=" and llx_actioncomm.active = 1";
         $sql.=" and llx_user.subdiv_id in (select rowid from `subdivision` where active = 1)";
         $sql.=" group by llx_user.subdiv_id";

        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        while($obj = $db->fetch_object($res)) {
            $total[$obj->subdiv_id][$period] = $obj->iCount;
        }
        //Фактично виконаних
        $sql = "select llx_user.subdiv_id, count(distinct llx_actioncomm.id) iCount from llx_actioncomm
            left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
            left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end";
        if(!empty($subdiv_id))
            $sql.=" where llx_user.subdiv_id = " . $subdiv_id;
        else
            $sql.=" where 1";
        $sql.=" and  date(datep) between  adddate(date(now()), interval -1 ".$period.") and date(now())";
        if(empty($code))
            $sql.=" and (llx_actioncomm.`code`in ('AC_GLOBAL','AC_CURRENT') or llx_actioncomm.id in (select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1))";
        else{
            if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
                $sql .= " and llx_actioncomm.`code`='" . $code . "'";
            else
                $sql .= " and llx_actioncomm.id in (select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1)";
        }
        $sql.=" and llx_actioncomm.percent = 100
            and llx_actioncomm.active = 1
            and llx_user.subdiv_id in (select rowid from `subdivision` where active = 1)
            group by llx_user.subdiv_id";
//        if(empty($subdiv_id)&&$i==1){
//            echo '<pre>';
//            var_dump($sql);
//            echo '</pre>';
//            die();
//        }
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        while($obj = $db->fetch_object($res)) {
            $fact[$obj->subdiv_id][$period] = $obj->iCount;
        }

    }
    $sql = "select llx_user.subdiv_id, date(datep) datep, count(distinct llx_actioncomm.id) iCount from llx_actioncomm
    left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
    left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end";
    if(!empty($subdiv_id))
        $sql.=" where llx_user.subdiv_id = " . $subdiv_id;
    else
        $sql.=" where 1";
    $sql.=" and date(datep) between  adddate(date(now()), interval -6 day) and date(now())";
    if(empty($code))
        $sql.=" and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
    else{
        if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
            $sql .= " and llx_actioncomm.`code`='" . $code . "'";
        else
            $sql .= " and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                where active = 1
                and `type` in ('system','user')
                and `code` not in ('AC_GLOBAL','AC_CURRENT'))";
    }
    $sql.=" and llx_actioncomm.active = 1
    and llx_user.subdiv_id in (select rowid from `subdivision` where active = 1)
    group by llx_user.subdiv_id, date(datep);";

    $res = $db->query($sql);
    if (!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)){
        $total[$obj->subdiv_id][$obj->datep] = $obj->iCount;
    }

    $sql = "select llx_user.subdiv_id, date(datep) datep, count(distinct llx_actioncomm.id) iCount from llx_actioncomm
        left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
        left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end";
    if(!empty($subdiv_id))
        $sql.=" where llx_user.subdiv_id = " . $subdiv_id;
    else
        $sql.=" where 1";
    $sql.=" and date(datep) between  adddate(date(now()), interval -6 day) and date(now())";
    if(empty($code))
        $sql.=" and (llx_actioncomm.`code`in ('AC_GLOBAL','AC_CURRENT') or llx_actioncomm.id in (select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1))";
    else{
            if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
                $sql .= " and llx_actioncomm.`code`='" . $code . "'";
            else
                $sql .= " and llx_actioncomm.id in (select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1)";
    }
    $sql.=" and llx_actioncomm.percent = 100
        and llx_actioncomm.active = 1
        and llx_user.subdiv_id in (select rowid from `subdivision` where active = 1)
        group by llx_user.subdiv_id, date(datep);";
//    if($code == 'AC_CUST'){
//        echo '<pre>';
//        var_dump($sql);
//        echo '</pre>';
//        die();
//    }
    $res = $db->query($sql);
    if (!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)){
        $fact[$obj->subdiv_id][$obj->datep] = $obj->iCount;
    }
    //Прострочені
    $sql = "select llx_user.subdiv_id, count(distinct llx_actioncomm.id)iCount from llx_actioncomm
        left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
        left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end";
    $sql.=" where 1";
    $sql.=" and date(datep) between adddate(date(now()), interval -1 month) and date(now())
        and llx_actioncomm.percent not in (100, -100) ";
    if(empty($code))
        $sql.=" and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
    else{
        if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
            $sql .= " and llx_actioncomm.`code`='" . $code . "'";
        else
            $sql .= " and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                    where active = 1
                    and `type` in ('system','user')
                    and `code` not in ('AC_GLOBAL','AC_CURRENT'))";
    }
    $sql.=" and llx_actioncomm.active = 1
            and llx_user.subdiv_id in (select rowid from `subdivision` where active = 1)
            group by llx_user.subdiv_id";
//if(empty($code)) {
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();
//}

    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)){
        $outstanding[$obj->subdiv_id] = $obj->iCount;
    }
 //майбутнє
    $sql = "select llx_user.subdiv_id, date(datep) datep, count(distinct llx_actioncomm.id) iCount from llx_actioncomm
        left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
        left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end";
    if(!empty($subdiv_id))
        $sql.=" where llx_user.subdiv_id = " . $subdiv_id;
    else
        $sql.=" where 1";
    $sql.=" and date(datep) between  date(now()) and adddate(date(now()), interval +1 week)";
    if(empty($code))
        $sql.=" and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
    else{
        if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
            $sql .= " and llx_actioncomm.`code`='" . $code . "'";
        else
            $sql .= " and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                    where active = 1
                    and `type` in ('system','user')
                    and `code` not in ('AC_GLOBAL','AC_CURRENT'))";
    }

    $sql.=" and llx_actioncomm.active = 1
        and llx_user.subdiv_id in (select rowid from `subdivision` where active = 1)
        group by llx_user.subdiv_id, date(datep);";

    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)){
        $future[$obj->subdiv_id][$obj->datep] = $obj->iCount;
    }
    for($i=0; $i<=1; $i++){
        if($i<1)
            $period = 'week';
        else
            $period = 'month';
        $sql = "select llx_user.subdiv_id, count(id) iCount  from llx_actioncomm
        left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
        left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end";
        $sql .= " where 1";
        $sql .= " and date(datep) between date(now()) and adddate(date(now()), interval 1 ".$period.")";
        if(empty($code))
            $sql.=" and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
        else{
            if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
                $sql .= " and llx_actioncomm.`code`='" . $code . "'";
            else
                $sql .= " and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                        where active = 1
                        and `type` in ('system','user')
                        and `code` not in ('AC_GLOBAL','AC_CURRENT'))";
        }
        $sql .= " and llx_actioncomm.active = 1
        and llx_user.subdiv_id in (select rowid from `subdivision` where active = 1)
        group by llx_user.subdiv_id;";
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
        while($obj = $db->fetch_object($res)){
            $future[$obj->subdiv_id][$period] = $obj->iCount;
        }
    }
//    echo '<pre>';
//    var_dump($outstanding, time()-$start);
//    echo '</pre>';
//    die();
    $sql = "select rowid, `name` from subdivision
        where active = 1
        order by `name`";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);

    $out = '';
    $num = 0;
    $showActionsByUsersCode = array('AllTask','AC_GLOBAL','AC_CURRENT');
    while($obj = $db->fetch_object($res)){
        $class_row = fmod($num,2)==0?'impare':'pare';
        $out.='<tr id="'.$class.$obj->rowid.'" class="'.$class.' '.$class_row.' subdivision">';
        $out.='<td colspan="2">'.$obj->name.'</td>';
        if(in_array((empty($code)?'AllTask':$code), $showActionsByUsersCode))
            $action = 'ShowActionsByUsers('.$obj->rowid.', '."'".(empty($code)?'AllTask':$code)."'".", '')";
        else
            $action = 'ShowLinectiveTask('.$obj->rowid.",'".$class."'".');';
        $out.='<td><button id="btnSub'.(empty($code)?'AllTask':$code).$obj->rowid.'" onclick="'.$action.'"><img id="imgSub'.(empty($code)?'AllTask':$code).$obj->rowid.'" src="/dolibarr/htdocs/theme/eldy/img/1downarrow.png"></button></td>';
//        for($i=0;$i<9;$i++){
//            $out.='<td></td>';
//        }
         //% виконання запланованого по факту
            for($i=8; $i>=0; $i--){
                if($i < 8) {
                    $percent = '';
                    if($i<7) {
                        $count = (isset($fact[$obj->rowid][date("Y-m-d", (time() - 3600 * 24 * $i))]) ? $fact[$obj->rowid][date("Y-m-d", (time() - 3600 * 24 * $i))] : ('0'));
                        $totalcount = (isset($total[$obj->rowid][date("Y-m-d", (time() - 3600 * 24 * $i))]) ? $total[$obj->rowid][date("Y-m-d", (time() - 3600 * 24 * $i))] : (''));
                    }else{
                        $count = isset($fact[$obj->rowid]['week'])?$fact[$obj->rowid]['week']:'';
                        $totalcount = isset($total[$obj->rowid]['week'])?$total[$obj->rowid]['week']:'';
                    }
                }else{
                    $count = isset($fact[$obj->rowid]['month'])?$fact[$obj->rowid]['month']:('0');
                    $totalcount = isset($total[$obj->rowid]['month'])?$total[$obj->rowid]['month']:('0');
                }
                if(!empty($totalcount))
                    $percent = round(100*$count/($totalcount==0?1:$totalcount));
                $out .= '<td class="middle_size" style="text-align: center;">' .$percent. '</td>';
            }
        //Фактично виконано
        if(isset($fact[$obj->rowid]['month']))
                $out.='<td class="middle_size" style="text-align: center">'.$fact[$obj->rowid]['month'].'</td>';
            else
                $out.='<td style="width: 35px"></td>';
        if(isset($fact[$obj->rowid]['week']))
                $out.='<td class="middle_size" style="text-align: center">'.$fact[$obj->rowid]['week'].'</td>';
            else
                $out.='<td style="width: 35px"></td>';
        for($i=6;$i>=0;$i--){
    //        var_dump(array_sum($outstanding[$bestuser]['fact'.date("Y-m-d", (time()-3600*24*$i))]));
    //        die('fact'.date("Y-m-d", (time()-3600*24*$i)));
            if(isset($fact[$obj->rowid][date("Y-m-d", (time()-3600*24*$i))]))
                $out.='<td class="middle_size" style="text-align: center">'.$fact[$obj->rowid][date("Y-m-d", (time()-3600*24*$i))].'</td>';
            else
                $out.='<td class="middle_size" style="text-align: center"></td>';
        }
        //Прострочено
        $out.='<td class="middle_size" style="text-align: center; ">'.(isset($outstanding[$obj->rowid])?$outstanding[$obj->rowid]:0).'</td>';

        //майбутнє (план)
        for($i=0; $i<9; $i++){
            $value = '';
            if($i < 8) {
                if($i < 7)
                    $value =  (isset($future[$obj->rowid][date("Y-m-d", (time() + 3600 * 24 * $i))]) ? $future[$obj->rowid][date("Y-m-d", (time() + 3600 * 24 * $i))] : (''));
                else {
                    if(!empty($future[$obj->rowid]['week']))
                        $value = $future[$obj->rowid]['week'];
                }

                $out .= '<td  class="middle_size" style="text-align: center">' . $value . '</td>';
            }else {
                if(!empty($future[$obj->rowid]['month']))
                    $value = $future[$obj->rowid]['month'];
                $out .= '<td class="middle_size" style="text-align: center">' . $value . '</td>';
            }
        }
        $out .='</tr>';
    }


//    echo '<pre>';
//    var_dump(htmlspecialchars($out), time()-$start);
//    echo '</pre>';
//    die();
    return $out;
}
function getTotalSubdivAction($class,$title,$subdiv_id=0,$code=''){
    global $db;
    $code=trim($code);
    $out = '<tr class="'.$class.'">';
    $start = time();
    $sql="select name from subdivision where rowid = ".$subdiv_id;
    $res = $db->query($sql);
    if (!$res)
        dol_print_error($db);
    $obj=$db->fetch_object($res);
    $out.='<td colspan="2">'.$title.(!empty($subdiv_id)?(' '.trim($obj->name).'.'):'').'</td>';
    if(!empty($subdiv_id))
        $out.='<td></td>';
    else
        $out.='<td><button id="btn'.(empty($code)?'AllTask':$code).'" onclick="ShowAllTask('."'".(empty($code)?'AllTask':$code)."'".');"><img id="img'.(empty($code)?'AllTask':$code).'" src="/dolibarr/htdocs/theme/eldy/img/1downarrow.png"></button></td>';
//Всього завдань та виконані
    $total = array();
    $fact = array();
    for($i=0; $i<=1; $i++) {
        if($i<1)
            $period = 'month';
        else
            $period = 'week';
        //Всього завдань
        $sql = "select count(distinct llx_actioncomm.id) iCount from llx_actioncomm
            left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
            left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end";
        if(!empty($subdiv_id))
            $sql.=" where llx_user.subdiv_id = " . $subdiv_id;
        else
            $sql.=" where 1";

        $sql.=" and date(datep) between  adddate(date(now()), interval -1 ".$period.") and date(now())";
        if(empty($code))
            $sql.=" and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
        else{
            if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
                $sql .= " and llx_actioncomm.`code`='" . $code . "'";
            else
                $sql .= " and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                    where active = 1
                    and `type` in ('system','user')
                    and `code` not in ('AC_GLOBAL','AC_CURRENT'))";
        }
         $sql.=" and llx_actioncomm.active = 1";
         $sql.=" and llx_user.subdiv_id in (select rowid from `subdivision` where active = 1)";
//        if(empty($subdiv_id)){
//            echo '<pre>';
//            var_dump($sql);
//            echo '</pre>';
//            die();
//        }
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        $obj = $db->fetch_object($res);
        $total[$period] = $obj->iCount;
        //Фактично виконаних
        $sql = "select  count(distinct llx_actioncomm.id) iCount from llx_actioncomm
            left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
            left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end";
        if(!empty($subdiv_id))
            $sql.=" where llx_user.subdiv_id = " . $subdiv_id;
        else
            $sql.=" where 1";
        $sql.=" and  date(datep) between  adddate(date(now()), interval -1 ".$period.") and date(now())";
        if(empty($code))
            $sql.=" and (llx_actioncomm.`code`in ('AC_GLOBAL','AC_CURRENT') or llx_actioncomm.id in (select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1))";
        else{
            if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
                $sql .= " and llx_actioncomm.`code`='" . $code . "'";
            else
                $sql .= " and llx_actioncomm.id in (select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1)";
        }
        $sql.=" and llx_actioncomm.percent = 100
            and llx_actioncomm.active = 1";
         $sql.=" and llx_user.subdiv_id in (select rowid from `subdivision` where active = 1)";

        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        $obj = $db->fetch_object($res);
        $fact[$period] = $obj->iCount;

    }
    $sql = "select date(datep) datep, count(distinct llx_actioncomm.id) iCount from llx_actioncomm
    left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
    left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end";
    if(!empty($subdiv_id))
        $sql.=" where llx_user.subdiv_id = " . $subdiv_id;
    else
        $sql.=" where 1";
    $sql.=" and date(datep) between  adddate(date(now()), interval -6 day) and date(now())";
    if(empty($code))
        $sql.=" and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
    else{
        if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
            $sql .= " and llx_actioncomm.`code`='" . $code . "'";
        else
            $sql .= " and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                where active = 1
                and `type` in ('system','user')
                and `code` not in ('AC_GLOBAL','AC_CURRENT'))";
    }
    $sql.=" and llx_actioncomm.active = 1
            and llx_user.subdiv_id in (select rowid from `subdivision` where active = 1)
    group by date(datep);";

    $res = $db->query($sql);
    if (!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)){
        $total[$obj->datep] = $obj->iCount;
    }

    $sql = "select date(datep) datep, count(distinct llx_actioncomm.id) iCount from llx_actioncomm
        left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
        left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end";
    if(!empty($subdiv_id))
        $sql.=" where llx_user.subdiv_id = " . $subdiv_id;
    else
        $sql.=" where 1";
    $sql.=" and date(datep) between  adddate(date(now()), interval -6 day) and date(now())";
    if(empty($code))
        $sql.=" and (llx_actioncomm.`code`in ('AC_GLOBAL','AC_CURRENT') or llx_actioncomm.id in (select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1))";
    else{
            if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
                $sql .= " and llx_actioncomm.`code`='" . $code . "'";
            else
                $sql .= " and llx_actioncomm.id in (select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1)";
    }
    $sql.=" and llx_actioncomm.percent = 100
        and llx_actioncomm.active = 1
        and llx_user.subdiv_id in (select rowid from `subdivision` where active = 1)
        group by date(datep);";
//    if($code == 'AC_CUST'){
//        echo '<pre>';
//        var_dump($sql);
//        echo '</pre>';
//        die();
//    }
    $res = $db->query($sql);
    if (!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)){
        $fact[$obj->datep] = $obj->iCount;
    }
$percent_block = '';
    //% виконання запланованого по факту
    for($i=8; $i>=0; $i--){
        if($i < 8) {
            $percent = 0;
            if($i<7) {
                $count = (isset($fact[date("Y-m-d", (time() - 3600 * 24 * $i))]) ? $fact[date("Y-m-d", (time() - 3600 * 24 * $i))] : ('0'));
                $total_count = (isset($total[ date("Y-m-d", (time() - 3600 * 24 * $i))]) ?$total[date("Y-m-d", (time() - 3600 * 24 * $i))] : (0));
            }else{
                $count = $fact['week'];
                $total_count = $total['week'];
            }
        }else{
            $count = isset($fact['month'])?$fact['month']:('0');
            $total_count = isset($total['month'])?$total['month']:('0');
        }
        $percent = round(100*$count/($total_count==0?1:$total_count));
//        if(date("Y-m-d", (time() - 3600 * 24 * $i)) == '2016-06-04'){
////            var_dump($total_count, $count, (empty($total_count)?'':$percent));
////            die();
//            $percent_block .= '<td class = "middle_size" style="text-align:center">'.$total_count.'/'.$count.' '.(empty($total_count)?'':$percent). '</td>';
//
//        }else
//        if(!empty($code)) {
//            echo '<pre>';
//            var_dump($total, $fact);
//            echo '</pre>';
//            die();
//        }
        $percent_block .= '<td class = "middle_size" style="text-align:center">'.(empty($total_count)?'':$percent). '</td>';
    }

    //фактично виконано
    $fact_block = '';
    if(isset($fact['month']))
            $fact_block.='<td class="middle_size" style="text-align: center">'.$fact['month'].'</td>';
        else
            $fact_block.='<td style="width: 35px"></td>';
    if(isset($fact['week']))
            $fact_block.='<td class="middle_size" style="text-align: center">'.$fact['week'].'</td>';
        else
            $fact_block.='<td style="width: 35px"></td>';
    for($i=6;$i>=0;$i--){
//        var_dump(array_sum($outstanding[$bestuser]['fact'.date("Y-m-d", (time()-3600*24*$i))]));
//        die('fact'.date("Y-m-d", (time()-3600*24*$i)));
        if(isset($fact[date("Y-m-d", (time()-3600*24*$i))]))
            $fact_block.='<td class="middle_size" style="text-align: center">'.$fact[date("Y-m-d", (time()-3600*24*$i))].'</td>';
        else
            $fact_block.='<td class="middle_size" style="text-align: center"></td>';
    }

    //Прострочені
    $sql = "select count(distinct llx_actioncomm.id) iCount from llx_actioncomm
        left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
        left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end";
    if(!empty($subdiv_id))
        $sql.=" where llx_user.subdiv_id = " . $subdiv_id;
    else
        $sql.=" where 1";
    $sql.=" and date(datep) between adddate(date(now()), interval -1 month) and date(now())
        and llx_actioncomm.percent not in (100, -100) ";
    if(empty($code))
        $sql.=" and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
    else{
        if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
            $sql .= " and llx_actioncomm.`code`='" . $code . "'";
        else
            $sql .= " and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                    where active = 1
                    and `type` in ('system','user')
                    and `code` not in ('AC_GLOBAL','AC_CURRENT'))";
    }
    $sql.=" and llx_user.subdiv_id in (select rowid from `subdivision` where active = 1)";
    $sql.=" and llx_actioncomm.active = 1;";
//    if(empty($code))
//        die($sql);
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    if (empty($obj->iCount))
        $outstanding = '<td></td>';
    else
        $outstanding = '<td  style="text-align: center">' . $obj->iCount . '</td>';
    //майбутнє
    $sql = "select date(datep) datep, count(distinct llx_actioncomm.id) iCount from llx_actioncomm
        left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
        left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end";
    if(!empty($subdiv_id))
        $sql.=" where llx_user.subdiv_id = " . $subdiv_id;
    else
        $sql.=" where 1";
    $sql.=" and date(datep) between  date(now()) and adddate(date(now()), interval +1 week)";
    if(empty($code))
        $sql.=" and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
    else{
        if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
            $sql .= " and llx_actioncomm.`code`='" . $code . "'";
        else
            $sql .= " and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                    where active = 1
                    and `type` in ('system','user')
                    and `code` not in ('AC_GLOBAL','AC_CURRENT'))";
    }

    $sql.=" and llx_actioncomm.active = 1
            and llx_user.subdiv_id in (select rowid from `subdivision` where active = 1)
        group by date(datep);";

    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)){
        $future[$obj->datep] = $obj->iCount;
    }
//    var_dump($array_result);
//    die();
    $future_block = '';
    for($i=0;$i<=8;$i++){
        $date = date("Y-m-d", (time() + 3600 * 24 * $i));
        if($i<=6) {
            if(isset($future[$date]))
                $future_block .= '<td  style="text-align: center">'.$future[$date].'</td>';
            else
                $future_block .= '<td></td>';
        }else {
            $sql = "select count(id) iCount  from llx_actioncomm
            left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
            left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_author` else `llx_actioncomm_resources`.`fk_element` end";
            if(!empty($subdiv_id))
                $sql .= " where llx_user.subdiv_id = " . $subdiv_id;
            else
                $sql .= " where 1";
            if ($i == 7)
                $sql .= " and date(datep) between date(now()) and adddate(date(now()), interval 1 week)";
            else
                $sql .= " and date(datep) between date(now()) and adddate(date(now()), interval 1 month)";

            if(empty($code))
                $sql.=" and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
            else{
                if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
                    $sql .= " and llx_actioncomm.`code`='" . $code . "'";
                else
                    $sql .= " and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
                            where active = 1
                            and `type` in ('system','user')
                            and `code` not in ('AC_GLOBAL','AC_CURRENT'))";
            }
            $sql.=" and llx_user.subdiv_id in (select rowid from `subdivision` where active = 1)";
            $sql .= " and llx_actioncomm.active = 1;";

//    if(!empty($code)){
//        echo '<pre>';
//        var_dump($sql);
//        echo '</pre>';
//        die();
//    }
            $res = $db->query($sql);
            if (!$res)
                dol_print_error($db);
            $obj = $db->fetch_object($res);
            if (empty($obj->iCount))
                $future_block .= '<td></td>';
            else
                $future_block .= '<td  style="text-align: center">' . $obj->iCount . '</td>';
        }
    }
    $out.=$percent_block.$fact_block.$outstanding.$future_block;
//    echo '<pre>';
//    var_dump($future);
//    echo '</pre>';
//    echo time()-$start;
//    die();
    return $out;
}



function createActionArray($array){
    $array = array();
    for($i=8; $i>=0; $i--){
        if($i < 8) {
            if($i<7) {
                $array['percent_'.date("Y-m-d", (time() - 3600 * 24 * $i))]=0;
                $array['total_'.date("Y-m-d", (time() - 3600 * 24 * $i))]=0;
                $array['fact_'.date("Y-m-d", (time() - 3600 * 24 * $i))]=0;
                $array['future_'.date("Y-m-d", (time() + 3600 * 24 * $i))]=0;
            }else{
                $array['percent_week']=0;
                $array['total_week']=0;
                $array['future_week']=0;
                $array['fact_week']=0;
            }
        }else{
                $array['percent_month']=0;
                $array['total_month']=0;
                $array['future_month']=0;
                $array['fact_month']=0;
        }
    }
    $array['outstanding']=0;
    return $array;
}
function ShowTable_tmp(){
    global $db,$actions,$future,$user,$user_respon,$conf,$actcode,$CustActions,$userActions,$DepActions,$outstanding, $DirDep;
//    echo count($actions).'</br>';
    $array = array();
    $sql = "select rowid, lastname, firstname, subdiv_id from llx_user where active = 1 and subdiv_id is not null and lastname <> 'test'";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $users = array();
    while($obj = $db->fetch_object($res))
        $users[$obj->rowid] = array('lastname'=>$obj->lastname.' '.mb_substr($obj->firstname, 0,1,'UTF-8').'.', 'subdiv_id'=>$obj->subdiv_id);
    $out = '<tbody id="reference_body">';
    $today = new DateTime();
    $mkToday = dol_mktime(0,0,0,$today->format('m'),$today->format('d'),$today->format('Y'));
    $start = time();
    foreach($actions as $action){
        $id_usr = $action['id_usr'];
        unset($action['id_usr']);
        $userActions[$id_usr][] = $action;
        $obj = (object)$action;
        $date = new DateTime($obj->datep);
        $mkDate = dol_mktime(0,0,0,$date->format('m'),$date->format('d'),$date->format('Y'));

        if($mkDate <= $mkToday && !in_array($obj->percent, array(100, -100))) {
            $array[$id_usr][$obj->code]++;
//            if($obj->id_usr == 43){
//                $count[]=$obj->datep;
//            }
        }elseif($mkDate <= $mkToday && $obj->percent == 100) {
//            $array[$id_usr]['fact'.$obj->datep][$obj->code]++;
//            if($mkToday-$mkDate<=604800)//604800 sec by week
//                $array[$id_usr]['week'][$obj->code]++;
//            if($mkToday-$mkDate<=2678400)//2678400 sec by month
//                $array[$id_usr]['month'][$obj->code]++;
            $added = false;
            if(in_array('sale',$user_respon[$id_usr])  && (in_array($action["code"], $actcode) || $action["callstatus"] == '5'))//Якщо дія виконується торгівельним агентом
                $added = true;
            elseif(!in_array('sale',$user_respon[$id_usr]))
                $added = true;
            if($added) {
                $array[$obj->id_usr]['fact' . $obj->datep][$obj->code]++;
                if ($mkToday - $mkDate <= 604800)//604800 sec by week
                    $array[$obj->id_usr]['week'][$obj->code]++;
                if ($mkToday - $mkDate <= 2678400)//2678400 sec by month
                    $array[$obj->id_usr]['month'][$obj->code]++;
            }
        }
        $array[$id_usr]['total'.$obj->datep][$obj->code]++;

        if($mkDate <= $mkToday && $action['percent'] == 100){
                if($mkToday-$mkDate<=604800&&$mkToday-$mkDate>=0) {//604800 sec by week
                    if($action["respon_alias"]=='sale'){//Якщо дія виконана торгівельним агентом
                        if(in_array($action['code'],$actcode)|| $action["callstatus"]=='5')//Зараховується дія якщо глобальна, поточна задача чи фактично виконаних дзвінок
                            $CustActions[$id_usr]++;
                    }
                    elseif($action["respon_alias"]=='dir_depatment'&&($action['code']=='AC_CUST'&&$action["callstatus"]=='5')){//Для всіх інших сфер відповідальності
//                        if($id_usr == 34){
//                            die($action["respon_alias"]);
//                        }
                        $CustActions[$id_usr]++;
                    }
                    $DepActions[$action['subdiv_id']]++;
                    if(in_array('dir_depatment', $user_respon[$id_usr]))
                        $DirDep[$id_usr]++;

                }

        }
    }
    $bestuser = GetBestUserID();
    $bestdep = GetBestDepID();
    $bestDD = GetBestDDID();
//    echo '<pre>';
//    var_dump($outstanding[$bestuser]);
//    echo '</pre>';
//    die();

    //Найкращий співробітник
    $out.='<tr class="bestvalue">';
    $out.= '<td colspan="2" class="middle_size">Найкр.пок. "Всього задач" '.$users[$bestuser]['lastname'].'</td><td style="width:33px">&nbsp;</td>';
    //% виконання запланованого по факту
    for($i=8; $i>=0; $i--){
        if($i < 8) {
            $percent = 0;
            if($i<7) {
                $count = (isset($outstanding[$bestuser]['fact' . date("Y-m-d", (time() - 3600 * 24 * $i))]) ? array_sum(($outstanding[$bestuser]['fact' . date("Y-m-d", (time() - 3600 * 24 * $i))])) : ('0'));
                $total = (isset($outstanding[$bestuser]['total' . date("Y-m-d", (time() - 3600 * 24 * $i))]) ? array_sum(($outstanding[$bestuser]['total' . date("Y-m-d", (time() - 3600 * 24 * $i))])) : (0));
            }else{
                $count = array_sum($outstanding[$bestuser]['week']);
                $total = array_sum($outstanding[$bestuser]['totalweek']);
            }
        }else{
            $count = isset($outstanding[$bestuser]['month'])?array_sum(($outstanding[$bestuser]['month'])):('0');
            $total = isset($outstanding[$bestuser]['totalmonth'])?array_sum(($outstanding[$bestuser]['totalmonth'])):('0');
        }
        $percent = round(100*$count/($total==0?1:$total));
        $out .= '<td class = "middle_size" style="text-align:center">'.($total==0?'':$percent). '</td>';
    }
    //фактично виконано
    if(isset($outstanding[$bestuser]['month']))
            $out.='<td class="middle_size" style="text-align: center">'.array_sum($outstanding[$bestuser]['month']).'</td>';
        else
            $out.='<td style="width: 35px"></td>';
    if(isset($outstanding[$bestuser]['week']))
            $out.='<td class="middle_size" style="text-align: center">'.array_sum($outstanding[$bestuser]['week']).'</td>';
        else
            $out.='<td style="width: 35px"></td>';
    for($i=6;$i>=0;$i--){
//        var_dump(array_sum($outstanding[$bestuser]['fact'.date("Y-m-d", (time()-3600*24*$i))]));
//        die('fact'.date("Y-m-d", (time()-3600*24*$i)));
        if(isset($outstanding[$bestuser]['fact'.date("Y-m-d", (time()-3600*24*$i))]))
            $out.='<td class="middle_size" style="text-align: center">'.array_sum($outstanding[$bestuser]['fact'.date("Y-m-d", (time()-3600*24*$i))]).'</td>';
        else
            $out.='<td class="middle_size" style="text-align: center"></td>';
    }
    $out.='<td class="middle_size" style="text-align: center">'.(isset($outstanding[$bestuser])?array_sum($outstanding[$bestuser]):0).'</td>';
//    echo '<pre>';
//    var_dump($future[$bestuser]);
//    echo '</pre>';
//    die();
    //майбутнє (план)
    for($i=0; $i<9; $i++){
        $value = '';

//           var_dump(array_sum(($future[$bestuser][date("Y-m-d", (time() + 3600 * 24 * $i))]))).'</br>';
        if($i < 8) {
            if($i < 7)
                $value =  (isset($future[$bestuser][date("Y-m-d", (time() + 3600 * 24 * $i))]) ? array_sum(($future[$bestuser][date("Y-m-d", (time() + 3600 * 24 * $i))])) : (''));
            else {
                if(!empty($future[$bestuser]['week']))
                    $value = (array_sum($future[$bestuser]['week']));
            }

            $out .= '<td  class="middle_size" style="text-align: center">' . $value . '</td>';
//            if($i == 6) {
//                echo '<pre>';
//                var_dump(htmlspecialchars($out));
//                echo '</pre>';
//                die();
//            }
//            $out .= '<td  class="middle_size" style="text-align: center; width: ' . ($conf->browser->name == 'firefox' ? $firefoxColWidths[$i] : $chromeColWidths[$i]) . 'px"><a href="/dolibarr/htdocs/hourly_plan.php?idmenu=10420&mainmenu=hourly_plan&leftmenu=&date=' . date("Y-m-d") . '">' . $value . '</a></td>';
        }else {
            if(!empty($future[$bestuser]['month']))
                $value = (array_sum($future[$bestuser]['month']));
            $out .= '<td class="middle_size" style="text-align: center">' . $value . '</td>';
        }
    }
    $out.='</tr>';
//                echo '<pre>';
//                var_dump(htmlspecialchars($out));
//                echo '</pre>';
//                die();
    //Найкращий директор департаменту
    $out.='<tr class="bestvalue">';
    $out.= '<td colspan="2" class="middle_size" ">Найкр.пок. ДД "Всього задач" '.$users[$bestDD]['lastname'].'</td><td style="width:33px">&nbsp;</td>';
    //% виконання запланованого по факту
    for($i=8; $i>=0; $i--){
        if($i < 8) {
            $percent = 0;
            if($i<7) {
                $count = (isset($outstanding[$bestDD]['fact' . date("Y-m-d", (time() - 3600 * 24 * $i))]) ? array_sum(($outstanding[$bestDD]['fact' . date("Y-m-d", (time() - 3600 * 24 * $i))])) : ('0'));
                $total = (isset($outstanding[$bestDD]['total' . date("Y-m-d", (time() - 3600 * 24 * $i))]) ? array_sum(($outstanding[$bestDD]['total' . date("Y-m-d", (time() - 3600 * 24 * $i))])) : (0));
            }else{
                $count = array_sum($outstanding[$bestDD]['week']);
                $total = array_sum($outstanding[$bestDD]['totalweek']);
            }
        }else{
            $count = isset($outstanding[$bestDD]['month'])?array_sum(($outstanding[$bestDD]['month'])):('0');
            $total = isset($outstanding[$bestDD]['totalmonth'])?array_sum(($outstanding[$bestDD]['totalmonth'])):('0');
        }
        $percent = round(100*$count/($total==0?1:$total));
        $out .= '<td class = "middle_size" style="text-align:center;">'.($total==0?'':$percent). '</td>';
    }
    //фактично виконано
    if(isset($outstanding[$bestDD]['month']))
            $out.='<td class="middle_size" style="text-align: center;width: 35px">'.array_sum($outstanding[$bestDD]['month']).'</td>';
        else
            $out.='<td style="width: 35px"></td>';
    if(isset($outstanding[$bestDD]['week']))
            $out.='<td class="middle_size" style="text-align: center;width: 35px">'.array_sum($outstanding[$bestDD]['week']).'</td>';
        else
            $out.='<td style="width: 35px"></td>';
    for($i=6;$i>=0;$i--){
        if(isset($outstanding[$bestDD]['fact'.date("Y-m-d", (time()-3600*24*$i))]))
            $out.='<td class="middle_size" style="text-align: center;">'.array_sum($outstanding[$bestDD]['fact'.date("Y-m-d", (time()-3600*24*$i))]).'</td>';
        else
            $out.='<td class="middle_size" style="text-align: center; "></td>';
    }
    $out.='<td class="middle_size" style="text-align: center; ">'.(isset($outstanding[$bestDD])?array_sum($outstanding[$bestDD]):0).'</td>';

    //майбутнє (план)
    for($i=0; $i<9; $i++){
        $value = '';
        if($i < 8) {
            if($i < 7)
                $value =  (isset($future[$bestDD][date("Y-m-d", (time() + 3600 * 24 * $i))]) ? array_sum(($future[$bestDD][date("Y-m-d", (time() + 3600 * 24 * $i))])) : (''));
            else {
                if(!empty($future[$bestDD]['week']))
                    $value = (array_sum($future[$bestDD]['week']));
            }
            $out .= '<td  class="middle_size" style="text-align: center;">' . $value . '</td>';
//            $out .= '<td  class="middle_size" style="text-align: center; width: ' . ($conf->browser->name == 'firefox' ? $firefoxColWidths[$i] : $chromeColWidths[$i]) . 'px"><a href="/dolibarr/htdocs/hourly_plan.php?idmenu=10420&mainmenu=hourly_plan&leftmenu=&date=' . date("Y-m-d") . '">' . $value . '</a></td>';
        }else {
            if(!empty($future[$bestDD]['month']))
                $value = (array_sum($future[$bestDD]['month']));
            $out .= '<td class="middle_size" style="text-align: center;">' . $value . '</td>';
        }
    }
    $out.='</tr>';

//    echo count($actions).'</br>';
    //Всього по найкращому департаменту
    $out .= getAllSubdivTask($actions, $bestdep);
    $out .= getAllSubdivTask($actions, 0, 'Всього задач Компанія', true);
    echo time()-$start;
    die();
//    $out .= getAllSubdivTask($actions, 0, 'Всього глобальних Компанія', true, 'AC_GLOBAL');
//    $out .= getAllSubdivTask($actions, 0, 'Всього поточних Компанія', true, 'AC_CURRENT');
//    $out .= getAllSubdivTask($actions, 0, 'Всього по напрямках Компанія', true, 'AC_CUST');

    $out .= '</tbody>';
    return $out;
}
function CalcOutStandingActions($actions, $code=''){
    global $actcode,$user_respon;
    $array = array();
    $today = new DateTime();
    $mkToday = dol_mktime(0,0,0,$today->format('m'),$today->format('d'),$today->format('Y'));
//    $exec = array();
    reset($actions);
    $count = array();
    foreach($actions as $action){
        $obj = (object)$action;
//        echo '<pre>';
//        var_dump($obj);
//        echo '</pre>';
//        die();
        $date = new DateTime($obj->datep);
        $mkDate = dol_mktime(0,0,0,$date->format('m'),$date->format('d'),$date->format('Y'));
        if($mkDate <= $mkToday && !in_array($obj->percent, array(100, -100))) {
            $array[$obj->id_usr][$obj->code]++;
//            if($obj->id_usr == 43){
//                $count[]=$obj->datep;
//            }
        }elseif($mkDate <= $mkToday && $obj->percent == 100) {
            $added = false;
            if(in_array('sale',$user_respon[$obj->id_usr])  && (in_array($obj->code, $actcode) || $obj->callstatus == '5'))//Якщо дія виконується торгівельним агентом
                $added = true;
            elseif(!in_array('sale',$user_respon[$obj->id_usr]))
                $added = true;
            if($added) {
                $array[$obj->id_usr]['fact' . $obj->datep][$obj->code]++;
                if ($mkToday - $mkDate <= 604800)//604800 sec by week
                    $array[$obj->id_usr]['week'][$obj->code]++;
                if ($mkToday - $mkDate <= 2678400)//2678400 sec by month
                    $array[$obj->id_usr]['month'][$obj->code]++;
            }
        }
        $array[$obj->id_usr]['total'.$obj->datep][$obj->code]++;
        if($mkToday-$mkDate<=604800 && $mkToday-$mkDate>=0) {//604800 sec by week
            $array[$obj->id_usr]['totalweek'][$obj->code]++;
//            if('2016-05-30' == $obj->datep){
//                var_dump($mkToday-$mkDate, $mkToday,$mkDate);
//                die();
//            }
        }
        if($mkToday-$mkDate<=2678400&& $mkToday-$mkDate>=0)//2678400 sec by month
            $array[$obj->id_usr]['totalmonth'][$obj->code]++;
    }
//        echo '<pre>';
//        var_dump($array);
//        echo '</pre>';
//        die();
    return $array;
}
function GetBestDDID(){
    global $DirDep,$user;

    $maxCount = 0;
    $id_usr = 0;
    foreach(array_keys($DirDep) as $key){
        if($maxCount<$DirDep[$key]){
//            echo $DepActions[$key].'</br>';
            $maxCount = $DirDep[$key];
            $id_usr = $key;
        }
    }
    return $id_usr;

}
function GetBestDepID(){
    global $DepActions,$user;

    $maxCount = 0;
    $subdiv_id = 0;
    foreach(array_keys($DepActions) as $key){
        if($maxCount<$DepActions[$key]){
//            echo $DepActions[$key].'</br>';
            $maxCount = $DepActions[$key];
            $subdiv_id = $key;
        }
    }
    return $subdiv_id;

}
