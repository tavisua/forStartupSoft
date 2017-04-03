<?php

require $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';

$actions = array();
$future = array();
$fact = array();
$outstanding = array();
$userActions = array();
$subdivTaskID = array();
$actcode = array('AC_GLOBAL', 'AC_CURRENT');
$user_respon = array();
$DepActions = array();
if(isset($_GET['id_usr'])&&!empty($_GET['id_usr'])){
    global $db;
    $sql = 'select lastname from llx_user where rowid = '.$_GET['id_usr'];
    $res = $db->query($sql);
    $obj = $db->fetch_object($res);
    $username = $obj->lastname;
    $id_usr = $_GET['id_usr'];
}else {
    $id_usr = $user->id;
    $username = $user->lastname;
}

$sql = "select rowid, lastname, firstname, subdiv_id from llx_user where active = 1 and subdiv_id is not null and lastname <> 'test'";
$res = $db->query($sql);
if(!$res)
    dol_print_error($db);
$users = array();
while($obj = $db->fetch_object($res))
    $users[$obj->rowid] = array('lastname'=>$obj->lastname.' '.mb_substr($obj->firstname, 0,1,'UTF-8').'.', 'subdiv_id'=>$obj->subdiv_id);
$sql = "select llx_user.rowid, r1.alias a1, r2.alias a2 from llx_user
left join `responsibility` r1 on r1.rowid = llx_user.respon_id
left join `responsibility` r2 on r2.rowid = llx_user.respon_id2
where llx_user.active = 1";
$res = $db->query($sql);
if(!$res)
    dol_print_error($db);
while($obj = $db->fetch_object($res)){
    $user_respon[$obj->rowid] = array($obj->a1,$obj->a2);
}

//echo '<pre>';
//var_dump($_SESSION['actions']);
//echo '<pre>';
//die();
//echo date('Y-m-d', time()-604800);
//die();
//var_dump(isset($_SESSION['actions']));
//unset($_SESSION['actions']);

//if(!isset($_SESSION['actions'])) {
//    $sql = "select llx_actioncomm.id, sub_user.rowid  id_usr, sub_user.alias, `llx_societe`.`region_id`, sub_user.subdiv_id, llx_actioncomm.percent, date(llx_actioncomm.datep) datep,
//    llx_actioncomm.percent, case when llx_actioncomm.`code` in ('AC_GLOBAL', 'AC_CURRENT','AC_EDUCATION', 'AC_INITIATIV', 'AC_PROJECT') then llx_actioncomm.`code` else 'AC_CUST' end `code`, `llx_societe_action`.`callstatus`
//    from llx_actioncomm
//    inner join (select id from `llx_c_actioncomm` where type in('user','system') and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
//    left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = llx_actioncomm.id
//    left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
//    left join `llx_societe_action` on `llx_societe_action`.`action_id` = `llx_actioncomm`.`id`
//    inner join (select `llx_user`.rowid, `responsibility`.`alias`, `llx_user`.subdiv_id from `llx_user` inner join `responsibility` on `responsibility`.`rowid` = `llx_user`.`respon_id` where 1 and `llx_user`.`active` = 1) sub_user on sub_user.rowid = case when llx_actioncomm_resources.fk_element is null then llx_actioncomm.`fk_user_author` else llx_actioncomm_resources.fk_element end
//    where 1
//    and llx_actioncomm.active = 1
//    and datep2 between adddate(date(now()), interval -1 month) and adddate(date(now()), interval 1 month)";
////echo '<pre>';
////var_dump($sql);
////echo '</pre>';
////die();
//    $res = $db->query($sql);
//    if (!$res)
//        dol_print_error($db);
//    $actions = array();
//    $time = time();
//    while ($obj = $db->fetch_object($res)) {
//        $actions[] = array('id'=>$obj->id, 'id_usr' => $obj->id_usr, 'region_id' => $obj->region_id, 'subdiv_id'=>$obj->subdiv_id,
//            'respon_alias' => $obj->alias, 'percent' => $obj->percent, 'datep' => $obj->datep, 'code' => $obj->code,
//            'callstatus'=> $obj->callstatus);
//    }
//    $_SESSION['actions'] = $actions;
//
//}else {
//    $actions = $_SESSION['actions'];
//}

if(isset($_REQUEST['action']))
    if($_REQUEST['action'] == 'ac_cust'){
        echo getResponAliasActions();
        exit();
    }elseif($_REQUEST['action'] == 'get_userlist'){
        echo getUserList();
        exit();
    }elseif($_REQUEST['action'] == 'get_regionlist'){
        echo getRegionsList($_REQUEST["id_usr"]);
        exit();
    }
$outstanding = CalcOutStandingActions($actions);
//$_SESSION['outstanding'] = $outstanding;
//if(!isset($_SESSION['future'])) {
    $future = CalcFutureActions($actions);
    $_SESSION['future'] = $future;

llxHeader("",$langs->trans('PlanOfDays'),"");
//echo '<pre>';
//var_dump($_SESSION['actions']);
//echo '</pre>';
//die();
//die('test');

print_fiche_titre($langs->trans('PlanOfDays'));
//die('test');

$table = ShowTable();
//if(array_intersect(array($user->respon_id, $user->respon_id2),array(8,20,28)) == 0)
    include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/responsibility/dir_depatment/day_plan.html';
//else
//    include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/responsibility/gen_dir/day_plan.html';

llxPopupMenu();
//print '</br>';
//print'<div style="float: left">test</div>';
//llxFooter();

exit();
function getRegionsList($id_usr){
    global $db, $actions;
    $outstanding = array();
    $future=array();
    $fact = array();
    $total = array();
    $regions = array();
    $today = new DateTime();
    $mkToday = dol_mktime(0,0,0,$today->format('m'),$today->format('d'),$today->format('Y'));
    $count = 0;
    foreach($actions as $item){
        $obj = (object)$item;
        if($item["id_usr"]==$id_usr&&$item["respon_alias"]=='sale'&&$item["code"]=='AC_CUST'){
            if(!in_array(empty($item["region_id"])?'null':$item["region_id"], $regions))
                $regions[]=empty($item["region_id"])?'null':$item["region_id"];
            $date = new DateTime($item["datep"]);
            $mkDate = dol_mktime(0, 0, 0, $date->format('m'), $date->format('d'), $date->format('Y'));
//            echo $item["datep"].' '.var_dump($mkDate >= $mkToday).'</br>';

//            if($item["datep"]=='2016-05-11'){
//                die('test');
//            }
            if ($mkDate == $mkToday)
                $count++;
            if ($mkDate >= $mkToday) {
                $future[$item["region_id"]][$item["datep"]]++;
                if ($mkDate - $mkToday <= 604800)//604800 sec by week
                    $future[$item["region_id"]]['week']++;
                if ($mkDate - $mkToday <= 2678400)//2678400 sec by month
                    $future[$item["region_id"]]['month']++;
            }

            if($obj->percent != 100){
                $outstanding[$item["region_id"]]++;
            }elseif($obj->percent == 100){
                $fact[$item["region_id"]][$item["datep"]]++;
                if($mkToday-$mkDate<=604800)//604800 sec by week
                    $fact[$item["region_id"]]['week']++;
                if($mkToday-$mkDate<=2678400)//2678400 sec by month
                    $fact[$item["region_id"]]['month']++;
            }

            $total[$item["region_id"]][$item["datep"]]++;
            if($mkToday-$mkDate<=604800)//604800 sec by week
                $total[$item["region_id"]]['week']++;
            if($mkToday-$mkDate<=2678400)//2678400 sec by month
                $total[$item["region_id"]]['month']++;
        }
    }
//    echo '<pre>';
//    var_dump($fact);
//    echo '</pre>';
//    die();

    $sql = "select `regions`.`rowid`,`states`.`name` statename, `regions`.`name` from `regions`
        inner join `states` on `states`.`rowid` = `regions`.`state_id`
        where (`regions`.`rowid` in (select `llx_user_regions`.fk_id from `llx_user_regions` where `llx_user_regions`.fk_user = ".$_REQUEST['id_usr']."
              and `llx_user_regions`.active = 1)
              or `regions`.`rowid` in (".(count($regions)>0?implode(",",$regions):0)."))
        and `regions`.active = 1";
    if(count($regions)>0 && in_array('null', $regions))
        $sql.=" union select null, 'Район', 'не вказано'";
    $sql.=" order by statename, `name`";
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();
    $out = '';
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)){
        $out.='<tr id="reg'.$obj->rowid.'" class="'.$id_usr.' regions subtype">';
        if(!empty($obj->rowid)) {
            $out .= '<td><a href="/dolibarr/htdocs/responsibility/sale/area.php?idmenu=10425&mainmenu=area&leftmenu=&state_filter=' . $obj->rowid . '" target="_blank">' . $obj->statename . '</a></td>';
            $out .= '<td><a href="/dolibarr/htdocs/responsibility/sale/area.php?idmenu=10425&mainmenu=area&leftmenu=&state_filter=' . $obj->rowid . '" target="_blank">' . $obj->name . '</a></td>';
        }else{
            $out .= '<td>'. $obj->statename . '</td>';
            $out .= '<td>'. $obj->name . '</td>';
        }
        $out.='<td></td>';
        //відсоток виконання
        if(isset($total[$obj->rowid]['month'])){
            $value = round($fact[$obj->rowid]['month']/$total[$obj->rowid]['month']*100,0);
            $out.='<td style="text-align: center">'.$value.'</td>';
        }else
            $out.='<td></td>';
        if(isset($total[$obj->rowid]['week'])){
            $value = round($fact[$obj->rowid]['week']/$total[$obj->rowid]['week']*100,0);
            $out.='<td style="text-align: center">'.$value.'</td>';
        }else
            $out.='<td></td>';
        for($i=6;$i>=0;$i--) {
            if(isset($total[$obj->rowid][date("Y-m-d", (time()-3600*24*$i))])){
                $value = round($fact[$obj->rowid][date("Y-m-d", (time()-3600*24*$i))]/$total[$obj->rowid][date("Y-m-d", (time()-3600*24*$i))]*100,0);
                $out.='<td style="text-align: center">'.$value.'</td>';
            }else
                $out .= '<td></td>';
        }
        //фактично виконано
        if(isset($fact[$obj->rowid]['month']))
                $out.='<td style="text-align: center">'.$fact[$obj->rowid]['month'].'</td>';
            else
                $out.='<td></td>';
        if(isset($fact[$obj->rowid]['week']))
                $out.='<td style="text-align: center">'.$fact[$obj->rowid]['week'].'</td>';
            else
                $out.='<td></td>';
        for($i=6;$i>=0;$i--){
            if(isset($fact[$obj->rowid][date("Y-m-d", (time()-3600*24*$i))]))
                $out.='<td style="text-align: center">'.$fact[$obj->rowid][date("Y-m-d", (time()-3600*24*$i))].'</td>';
            else
                $out.='<td style="text-align: center"></td>';
        }
        //прострочено
        if(isset($outstanding[$obj->rowid]))
                $out.='<td style="text-align: center">'.$outstanding[$obj->rowid].'</td>';
            else
                $out.='<td></td>';
        //заплановано на майбутнє
        for($i=0;$i<=6;$i++){
            if($future[$obj->rowid][date("Y-m-d", (time()+3600*24*$i))])
                $out.='<td style="text-align: center">'.$future[$obj->rowid][date("Y-m-d", (time()+3600*24*$i))].'</td>';
            else
                $out.='<td></td>';
        }
        if(isset($future[$obj->rowid]['week']))
                $out.='<td style="text-align: center">'.$future[$obj->rowid]['week'].'</td>';
            else
                $out.='<td></td>';
        if(isset($future[$obj->rowid]['month']))
                $out.='<td style="text-align: center">'.$future[$obj->rowid]['month'].'</td>';
            else
                $out.='<td></td>';
        $out.='</tr>';
    }
    return $out;
}
function getUserList(){
    global $db, $actions, $outstanding, $future, $user;
//    echo '<pre>';
//    var_dump($outstanding);
//    echo '</pre>';
//    die();
    $userID = array();
    $sql = "select llx_user.rowid, llx_user.lastname, llx_user.firstname from llx_user
        inner join `responsibility` on `responsibility`.`rowid` = `llx_user`.`respon_id`
        left join `responsibility` resp on `resp`.`rowid` = `llx_user`.`respon_id2`
        where subdiv_id = ".$user->subdiv_id."
        and (`responsibility`.`alias` = '".$_REQUEST['respon_alias']."' or `resp`.`alias` = '".$_REQUEST['respon_alias']."')
        and llx_user.active = 1
        order by llx_user.lastname, llx_user.firstname";
//    die($sql);
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)) {
        $userID[]=$obj->rowid;
    }
    mysqli_data_seek($res, 0);
    $calc_action = array();
    $maxcount = 0;
    $bestID=0;
    foreach($userID as $id_usr){
        if(isset($future[$id_usr]['month']['AC_CUST']))
             $calc_action[$id_usr]['future_month'] += $future[$id_usr]['month']['AC_CUST'];
        if(isset($future[$id_usr]['week']['AC_CUST']))
             $calc_action[$id_usr]['future_week'] += $future[$id_usr]['week']['AC_CUST'];
        for($i=6;$i>=0;$i--){
            if(isset($future[$id_usr][date("Y-m-d", (time()+3600*24*$i))]['AC_CUST']))
                $calc_action[$id_usr]['future'.date("Y-m-d", (time()+3600*24*$i))] += $future[$id_usr][date("Y-m-d", (time()+3600*24*$i))]['AC_CUST'];
        }
        if(isset($outstanding[$id_usr]['AC_CUST'])){
            $calc_action[$id_usr]['outstanding'] += $outstanding[$id_usr]['AC_CUST'];
        }
        if(isset($outstanding[$id_usr]['month']['AC_CUST'])){
            $calc_action[$id_usr]['fact_month'] += $outstanding[$id_usr]['month']['AC_CUST'];
            $calc_action[$id_usr]['total_month'] += $outstanding[$id_usr]['totalmonth']['AC_CUST'];
        }
        if(isset($outstanding[$id_usr]['week']['AC_CUST'])){
            $calc_action[$id_usr]['fact_week'] += $outstanding[$id_usr]['week']['AC_CUST'];
            $calc_action[$id_usr]['total_week'] += $outstanding[$id_usr]['totalweek']['AC_CUST'];
            if($maxcount<$calc_action[$id_usr]['fact_week']){
                $bestID = $id_usr;
                $maxcount=$calc_action[$id_usr]['fact_week'];
            }
        }
        for($i=6;$i>=0;$i--){
            if(isset($outstanding[$id_usr]['fact'.date("Y-m-d", (time()-3600*24*$i))]['AC_CUST']))
                $calc_action[$id_usr]['fact'.date("Y-m-d", (time()-3600*24*$i))] += $outstanding[$id_usr]['fact'.date("Y-m-d", (time()-3600*24*$i))]['AC_CUST'];
            if(isset($outstanding[$id_usr]['total'.date("Y-m-d", (time()-3600*24*$i))]['AC_CUST']))
                $calc_action[$id_usr]['total'.date("Y-m-d", (time()-3600*24*$i))] += $outstanding[$id_usr]['total'.date("Y-m-d", (time()-3600*24*$i))]['AC_CUST'];
        }
    }
//    echo '<pre>';
//    var_dump($bestID);
//    echo '</pre>';
//    die();
    $out = CreateItem($calc_action, array('rowid'=>$bestID,'lastname'=>'Найкращі','firstname'=>'показники'), 'bestvalue', false);
    while($obj = $db->fetch_array($res)){
        $out.=CreateItem($calc_action, $obj, 'userlist');
    }
    return $out;
}
function CreateItem($calc_action, $user_tmp, $classname, $showbtn = true){
    global $outstanding;
    $out='<tr id="'.$user_tmp['rowid'].'" class="respon_'.$_REQUEST['respon_alias'].' '.$classname.'">';
    $out.='<td>'.$user_tmp['lastname'].'</td>';
    $out.='<td>'.$user_tmp['firstname'].'</td>';
    if(!$showbtn)
        $out.='<td></td>';
    else
        $out.='<td><button id="bnt'.$user_tmp['rowid'].'" onclick="ShowUserTasks('.$user_tmp['rowid'].', '."'".$_REQUEST['respon_alias']."'".');"><img id="img'.$user_tmp['rowid'].'" src="/dolibarr/htdocs/theme/eldy/img/1downarrow.png"></button></td>';
    $percent = $calc_action[$user_tmp['rowid']]['fact_month'];
    if(!empty($calc_action[$user_tmp['rowid']]['total_month'])) {
        $percent = $percent / $calc_action[$user_tmp['rowid']]['total_month'];
        $percent = round($percent * 100);
    }
    $out.='<td style="text-align: center;">'.$percent.'</td>';
    $percent = $calc_action[$user_tmp['rowid']]['fact_week'];
    if(!empty($calc_action[$user_tmp['rowid']]['total_week'])) {
        $percent = $percent / $calc_action[$user_tmp['rowid']]['total_week'];
        $percent = round($percent * 100);
    }
    $out.='<td style="text-align: center;">'.$percent.'</td>';
    for($i=6;$i>=0;$i--){
        $percent = '';
        if(!empty($calc_action[$user_tmp['rowid']]['total'.date("Y-m-d", (time()-3600*24*$i))])) {
            $percent = $calc_action[$user_tmp['rowid']]['fact'.date("Y-m-d", (time()-3600*24*$i))]/$calc_action[$user_tmp['rowid']]['total'.date("Y-m-d", (time()-3600*24*$i))];
            $percent = round($percent*100);
        }
        $out.='<td style="text-align: center;">'.$percent.'</td>';
    }
    if(isset($calc_action[$user_tmp['rowid']]['fact_month']))
        $out.='<td style="text-align: center">'.$calc_action[$user_tmp['rowid']]['fact_month'].'</td>';
    else
        $out.='<td></td>';
    if(isset($calc_action[$user_tmp['rowid']]['fact_week']))
        $out.='<td style="text-align: center">'.$calc_action[$user_tmp['rowid']]['fact_week'].'</td>';
    else
        $out.='<td></td>';
    for($i=6;$i>=0;$i--){
        if($calc_action[$user_tmp['rowid']]['fact'.date("Y-m-d", (time()-3600*24*$i))])
            $out.='<td style="text-align: center">'.$calc_action[$user_tmp['rowid']]['fact'.date("Y-m-d", (time()-3600*24*$i))].'</td>';
        else
            $out.='<td></td>';
    }
    if(isset($outstanding[$user_tmp['rowid']]['AC_CUST']))
        $out.='<td style="text-align: center">'.$outstanding[$user_tmp['rowid']]['AC_CUST'].'</td>';
    else
        $out.='<td></td>';
    for($i=0;$i<=6;$i++){
        if($calc_action[$user_tmp['rowid']]['future'.date("Y-m-d", (time()+3600*24*$i))])
            $out.='<td style="text-align: center">'.$calc_action[$user_tmp['rowid']]['future'.date("Y-m-d", (time()+3600*24*$i))].'</td>';
        else
            $out.='<td></td>';
    }
    if(isset($calc_action[$user_tmp['rowid']]['future_week']))
        $out.='<td style="text-align: center">'.$calc_action[$user_tmp['rowid']]['future_week'].'</td>';
    else
        $out.='<td></td>';
    if(isset($calc_action[$user_tmp['rowid']]['future_month']))
        $out.='<td style="text-align: center">'.$calc_action[$user_tmp['rowid']]['future_month'].'</td>';
    else
        $out.='<td></td>';
    $out.='</tr>';
    return $out;
}
function GetBestUserID()
{
    global $CustActions, $user;

    $maxCount = 0;
    $id_usr = 0;

    foreach (array_keys($CustActions) as $userID) {
        if ($maxCount < $CustActions[$userID]) {
            $maxCount = $CustActions[$userID];
            $id_usr = $userID;
        }
    }
    return $id_usr;

}
function getResponAliasActions(){
    global $db,$langs,$user,$actions;

    $sql = "select llx_user.rowid, `responsibility`.`alias` from llx_user
        left join `responsibility` on `responsibility`.`rowid` = case when `llx_user`.`respon_id2` is null then `llx_user`.`respon_id` else `llx_user`.`respon_id2` end
        where llx_user.subdiv_id=".$user->subdiv_id.' and llx_user.active = 1';
    $res_respon = $db->query($sql);
    if(!$res_respon)
        dol_print_error($res_respon);
    $user_respon = array();
    while($obj = $db->fetch_object($res_respon)){
        if(!empty($obj->alias))
            $user_respon[$obj->rowid] = $obj->alias;
    }
	$actcode = array('AC_GLOBAL', 'AC_CURRENT');
    $outstanding = array();
    $future = array();
    $fact=array();
    $total=array();
    $today = new DateTime();
    $mkToday = dol_mktime(0,0,0,$today->format('m'),$today->format('d'),$today->format('Y'));
    $calc_action = array();
    $calc = array();
    $actionsID = array();
    foreach($actions as $action) {
//    echo '<pre>';
//    var_dump($action);
//    echo '</pre>';
//    die();
        if($action["datep"] == '2016-06-01'&&$action['subdiv_id'] == $user->subdiv_id )
            $calc++;

        if($action['subdiv_id'] == $user->subdiv_id &&$action['code']=='AC_CUST'&& !in_array($action['id'], $actionsID)) {
            $actionsID[]=$action['id'];
            $date = new DateTime($action["datep"]);
            $mkDate = dol_mktime(0, 0, 0, $date->format('m'), $date->format('d'), $date->format('Y'));
            $calc_action[$action["respon_alias"]][$action["datep"]]++;
            if ($mkDate >= $mkToday) {
					if($mkDate - $mkToday <= 604800)
						$calc[$action['respon_alias']]['future_week']++;
                if($mkDate - $mkToday <= 604800)//604800 sec by week
                    $calc_action[$action['respon_alias']]['future_week']++;
                if ($mkToday - $mkDate <= 2678400)//2678400 sec by month
                    $calc_action[$action['respon_alias']]['future_month']++;
            }
            if ($mkDate < $mkToday && $action['percent'] != 100) {//Додав $mkDate < $mkToday. Вважається логічним, щоб кількість прострочених рахувати, коли завдання повинно вже було бути виконано
                $calc_action[$action["respon_alias"]]['outstanding']++;
            } elseif ($action['percent'] == 100 && (in_array($action['code'], $actcode) || $action['callstatus'] == '5')) {
                if ($mkToday - $mkDate <= 604800)
                    $calc_action[$action["respon_alias"]]['fact'.$action["datep"]]++;
//                    $fact[$action["id_usr"]][$action["datep"]]++;
                if ($mkToday - $mkDate <= 604800)//604800 sec by week
                    $calc_action[$action["respon_alias"]]['fact_week']++;
                if ($mkToday - $mkDate <= 2678400)//2678400 sec by month
                    $calc_action[$action["respon_alias"]]['fact_month']++;
            }
            $calc_action[$action["respon_alias"]]['total'.$action["datep"]]++;
            if ($mkToday - $mkDate <= 604800)//604800 sec by week
                $calc_action[$action["respon_alias"]]['total_week']++;
            if ($mkToday - $mkDate <= 2678400)//2678400 sec by month
                $calc_action[$action["respon_alias"]]['total_month']++;
//
//            if ($mkDate >= $mkToday) {
//                $future[$action["id_usr"]][$action["datep"]][$action['code']]++;
//                if ($mkDate - $mkToday <= 604800)//604800 sec by week
//                    $future[$action["id_usr"]]['week'][$action['code']]++;
//                if ($mkDate - $mkToday <= 2678400)//2678400 sec by month
//                    $future[$action["id_usr"]]['month'][$action['code']]++;
//            }
//            if ($mkDate < $mkToday && $action['percent'] != 100) {//Додав $mkDate < $mkToday. Вважається логічним, щоб кількість прострочених рахувати, коли завдання повинно вже було бути виконано
//                $outstanding[$action["id_usr"]]++;
//            } elseif ($action['percent'] == 100 && (in_array($action['code'], $actcode) || $action['callstatus'] == '5')) {
//                if ($mkToday - $mkDate <= 604800)
//                    $fact[$action["id_usr"]][$action["datep"]]++;
//                if ($mkToday - $mkDate <= 604800)//604800 sec by week
//                    $fact[$action["id_usr"]]['week']++;
//                if ($mkToday - $mkDate <= 2678400)//2678400 sec by month
//                    $fact[$action["id_usr"]]['month']++;
//            }
//            $total[$action["id_usr"]][$action["datep"]]++;
//            if ($mkToday - $mkDate <= 604800)//604800 sec by week
//                $total[$action["id_usr"]]['week']++;
//            if ($mkToday - $mkDate <= 2678400)//2678400 sec by month
//                $total[$action["id_usr"]]['month']++;

        }
	}
//	echo '<pre>';
////	var_dump($calc);
//	var_dump($calc_action);
//	echo '</pre>';
//	die();


//    foreach(array_keys($future) as $id_usr){
//        if(isset($future[$id_usr]['month']['AC_CUST']))
//             $calc_action[$user_respon[$id_usr]]['future_month'] += $future[$id_usr]['month']['AC_CUST'];
//        if(isset($future[$id_usr]['week']['AC_CUST']))
//             $calc_action[$user_respon[$id_usr]]['future_week'] += $future[$id_usr]['week']['AC_CUST'];
//        for($i=6;$i>=0;$i--){
//            if(isset($future[$id_usr][date("Y-m-d", (time()+3600*24*$i))]['AC_CUST']))
//                $calc_action[$user_respon[$id_usr]]['future'.date("Y-m-d", (time()+3600*24*$i))] += $future[$id_usr][date("Y-m-d", (time()+3600*24*$i))]['AC_CUST'];
//        }
//    }
//    foreach(array_keys($outstanding) as $id_usr){
//        if(isset($outstanding[$id_usr]['AC_CUST'])){
//            $calc_action[$user_respon[$id_usr]]['outstanding'] += $outstanding[$id_usr]['AC_CUST'];
//        }
//        if(isset($outstanding[$id_usr]['month']['AC_CUST'])){
//            $calc_action[$user_respon[$id_usr]]['fact_month'] += $outstanding[$id_usr]['month']['AC_CUST'];
//            $calc_action[$user_respon[$id_usr]]['total_month'] += $outstanding[$id_usr]['totalmonth']['AC_CUST'];
//        }
//        if(isset($outstanding[$id_usr]['week']['AC_CUST'])){
//            $calc_action[$user_respon[$id_usr]]['fact_week'] += $outstanding[$id_usr]['week']['AC_CUST'];
//            $calc_action[$user_respon[$id_usr]]['total_week'] += $outstanding[$id_usr]['totalweek']['AC_CUST'];
//        }
//        for($i=6;$i>=0;$i--){
//            if(isset($outstanding[$id_usr]['fact'.date("Y-m-d", (time()-3600*24*$i))]['AC_CUST']))
//                $calc_action[$user_respon[$id_usr]]['fact'.date("Y-m-d", (time()-3600*24*$i))] += $outstanding[$id_usr]['fact'.date("Y-m-d", (time()-3600*24*$i))]['AC_CUST'];
//            if(isset($outstanding[$id_usr]['total'.date("Y-m-d", (time()-3600*24*$i))]['AC_CUST']))
//                $calc_action[$user_respon[$id_usr]]['total'.date("Y-m-d", (time()-3600*24*$i))] += $outstanding[$id_usr]['total'.date("Y-m-d", (time()-3600*24*$i))]['AC_CUST'];
//        }
//    }
    $sql = "select distinct `responsibility`.`alias` from llx_user
        inner join `responsibility` on `responsibility`.`rowid` = `llx_user`.`respon_id`
        where llx_user.subdiv_id = ".$user->subdiv_id."
        and llx_user.active = 1
        and alias not in ('gen_dir','');";

    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $out = '';
    if($db->num_rows($res)>0)
        while($obj = $db->fetch_object($res)){
            $out.='<tr id="'.$obj->alias.'" class="ac_cust  respon_alias" style="font-weight: bold">
            <td class="middle_size" style="width:106px">Департамент </td>
            <td>'.$langs->trans(strtoupper(substr($obj->alias, 0,1)).substr($obj->alias,1)).'</td>
            <td><button id="bnt'.$obj->alias.'" onclick="ShowHideTaskByUsers('."'respon_".$obj->alias."'".');"><img id="img'.$obj->alias.'" src="/dolibarr/htdocs/theme/eldy/img/1downarrow.png"></button></td>';
            $percent = $calc_action[$obj->alias]['fact_month'];
            if(!empty($calc_action[$obj->alias]['total_month'])) {
                $percent = $percent / $calc_action[$obj->alias]['total_month'];
                $percent = round($percent * 100);
            }
            $out.='<td style="text-align: center;">'.$percent.'</td>';
            $percent = $calc_action[$obj->alias]['fact_week'];
            if(!empty($calc_action[$obj->alias]['total_week'])) {
                $percent = $percent / $calc_action[$obj->alias]['total_week'];
                $percent = round($percent * 100);
            }
            $out.='<td style="text-align: center;">'.$percent.'</td>';
            for($i=6;$i>=0;$i--){
                $percent = '';
                if(!empty($calc_action[$obj->alias]['total'.date("Y-m-d", (time()-3600*24*$i))])) {
                    $percent = $calc_action[$obj->alias]['fact'.date("Y-m-d", (time()-3600*24*$i))]/$calc_action[$obj->alias]['total'.date("Y-m-d", (time()-3600*24*$i))];
                    $percent = round($percent*100);
                }
                $out.='<td style="text-align: center;">'.$percent.'</td>';
            }
            $out.='<td style="text-align: center;">'.$calc_action[$obj->alias]['fact_month'].'</td>';
            $out.='<td style="text-align: center;">'.$calc_action[$obj->alias]['fact_week'].'</td>';
            for($i=6;$i>=0;$i--){
                $out.='<td style="text-align: center;">'.$calc_action[$obj->alias]['fact'.date("Y-m-d", (time()-3600*24*$i))].'</td>';
            }
            $out.='<td style="text-align: center;">'.$calc_action[$obj->alias]['outstanding'].'</td>';
            for($i=0;$i<=6;$i++){
                $out.='<td style="text-align: center;">'.$calc_action[$obj->alias][date("Y-m-d", (time()+3600*24*$i))].'</td>';
            }
            $out.='<td style="text-align: center;">'.$calc_action[$obj->alias]['future_week'].'</td>';
            $out.='<td style="text-align: center;">'.$calc_action[$obj->alias]['future_month'].'</td>';
            $out.='</tr>';
        }
    return $out;
}
function CalcPercentExecActions($actioncode, $array, $id_usr=0, $responding=''){
    global $db, $user;
    $totaltask = array();
    $exectask = array();
    for($i = 0; $i<9; $i++){
        $sql = "select count(*) as iCount  from `llx_actioncomm`
        inner join
        (select id from `llx_c_actioncomm` where code in(".$actioncode.") and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
        left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
        inner join (select fk_id from `llx_user_regions`where fk_user = ".$id_usr." and llx_user_regions.active = 1) as active_regions on active_regions.fk_id = `llx_societe`.region_id
        inner join `llx_actioncomm_resources` on `llx_actioncomm`.`id` =  `llx_actioncomm_resources`.`fk_actioncomm`
        where 1 ";
        if((in_array($actioncode, array('AC_GLOBAL','AC_CURRENT','AC_EDUCATION','AC_INITIATIV','AC_PROJECT')) || $user->login !="admin")&& $id_usr != 0) {
            $sql .= " and `llx_actioncomm_resources`.fk_element = " . $id_usr;
        }else {
            if(!empty($user->subdiv_id))
                $sql .= " and `llx_actioncomm_resources`.fk_element in (select rowid from llx_user where `subdiv_id` = " . $user->subdiv_id . (empty($responding) ? "" : " and respon_id in(" . $responding . ")") . ")";
        }
        if($i<8) {
            $query_date = date("Y-m-d", (time()+3600*24*(-$i)));
            if($i!=7)
                $sql .= " and datep2 between '".$query_date . "' and date_add('" . $query_date . "', interval 1 day)";
            else
                $sql .= " and datep2 between date_add('" . date("Y-m-d") . "', interval -7 day) and '".date("Y-m-d") . "'";
        }else {
                $sql .= " and datep2 between date_add('" . date("Y-m-d") . "', interval -31 day) and '" . date("Y-m-d") . "'";
        }

        $res = $db->query($sql);
        while($res && $obj = $db->fetch_object($res)){
            if($i<8) {
                $totaltask[$i] = $obj->iCount;
            }else
                $totaltask['month']=$obj->iCount;
        }
        $res = $db->query($sql.' and datea is not null');
        while($res && $obj = $db->fetch_object($res)){
            if($i<8) {
                $exectask[$i] = $obj->iCount;
            }else
                $exectask['month']=$obj->iCount;
        }
    }
//    if($id_usr == 21){
//        var_dump($totaltask);
//        die();
//    }
    for($i=0; $i<9; $i++){
        if($i<8) {
            if($totaltask[$i]!=0){
               $array['percent_'.$i] = round($exectask[$i]*100/$totaltask[$i],0);
            }else{
               $array['percent_'.$i] = '';
            }
        }else{
            if($totaltask['month'] != 0){
                $array['percent_month'] =  round($exectask['month']*100/$totaltask['month'],0);
            }else{
               $array['percent_month'] = '';
            }
        }
    }
    return $array;
}
function CalcFaktActions($actioncode, $array, $id_usr=0, $responding=''){
    global $db, $user;
    //Минулі виконані дії
    for($i=0; $i<9; $i++) {
        $sql = "select count(*) as iCount  from `llx_actioncomm`
        inner join
        (select id from `llx_c_actioncomm` where code in(".$actioncode.") and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
        left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
        inner join `llx_actioncomm_resources` on `llx_actioncomm`.`id` =  `llx_actioncomm_resources`.`fk_actioncomm`
        where 1";
        if((in_array($actioncode, array('AC_GLOBAL','AC_CURRENT','AC_EDUCATION','AC_INITIATIV','AC_PROJECT')) || $user->login !="admin")&& $id_usr != 0)
            $sql .=" and `llx_actioncomm_resources`.fk_element = ".$id_usr;
        elseif(!empty($user->subdiv_id))
            $sql .=" and `llx_actioncomm_resources`.fk_element in (select rowid from llx_user where 1 ".(empty($user->subdiv_id)?"":"and `subdiv_id` = ".$user->subdiv_id).(empty($responding)?"":" and respon_id in(".$responding.")").")";
        if($i<8) {
            $query_date = date("Y-m-d", (time()+3600*24*(-$i)));
            if($i!=7)
                $sql .= " and datep2 between '".$query_date . "' and date_add('" . $query_date . "', interval 1 day)";
            else
                $sql .= " and datep2 between date_add('" . date("Y-m-d") . "', interval -7 day) and '".date("Y-m-d") . "'";
        }else {
                $sql .= " and datep2 between date_add('" . date("Y-m-d") . "', interval -31 day) and '" . date("Y-m-d") . "'";
        }
        $sql .=" and llx_actioncomm.active = 1";
        $sql .=" and `llx_actioncomm`.`percent` = 100";
//        if($i == 7 && ($id_usr != 1))
//            die($sql);
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
    }
    return $array;
}

function CalcFutureActions($actions, $code=''){
    $array = array();
    $today = new DateTime();
    $mkToday = dol_mktime(0,0,0,$today->format('m'),$today->format('d'),$today->format('Y'));
    foreach($actions as $action){
        $obj = (object)$action;
        $array[$obj->id_usr][$obj->datep][$obj->code]++;
        $date = new DateTime($obj->datep);
        $mkDate = dol_mktime(0,0,0,$date->format('m'),$date->format('d'),$date->format('Y'));
        if($mkDate >= $mkToday) {
            if($mkDate-$mkToday<=604800)//604800 sec by week
                $array[$obj->id_usr]['week'][$obj->code]++;
            if($mkDate-$mkToday<=2678400)//2678400 sec by month
                $array[$obj->id_usr]['month'][$obj->code]++;
        }
    }
    return $array;
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
        if($mkDate <= $mkToday && $obj->percent != 100) {
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

function ShowTable(){
    global $db,$user;
    $out = '<tbody id="reference_body">';
    $start = time();
//    var_dump(array_intersect(array($user->respon_id, $user->respon_id2),array(8,20,28)) == 0);
//    die();
//    //Найкращий користувач системи
//    $sql="select count(id) iCount, case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end user_id  from llx_actioncomm
//        left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
//        where date(datep) between adddate(date(now()), interval -1 week) and date(now())
//        and (llx_actioncomm.`code`in ('AC_GLOBAL','AC_CURRENT') or llx_actioncomm.id in (select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1))
//        and active = 1
//        group by user_id
//        order by iCount desc limit 1;";
//    $res = $db->query($sql);
////    die($sql);
//    if(!$res)
//        dol_print_error($db);
////    echo (time()-$start).'</br>';
//    $obj = $db->fetch_object($res);
//    $bestuserID = $obj->user_id;
//    $out.=getTotalUserAction($bestuserID, 'bestvalue', 'Найкр.співр.сист.');
////    echo (time()-$start).'Найкр.співр.сист.'.'</br>';
//
//    //Найкращий директор дипартам. системи
//    $sql="select count(id) iCount, case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end user_id
//        from llx_actioncomm
//        left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
//        where date(datep) between adddate(date(now()), interval -1 week) and date(now())
//        and (llx_actioncomm.`code`in ('AC_GLOBAL','AC_CURRENT') or llx_actioncomm.id in (select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1))
//        and case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end in (select rowid from llx_user where respon_id = 8)
//        and active = 1 group by user_id order by iCount desc limit 1;";
//    $res = $db->query($sql);
//    if(!$res)
//        dol_print_error($db);
//    $obj = $db->fetch_object($res);
//    $bestDDID = $obj->user_id;
//    $out.=getTotalUserAction($bestDDID, 'bestvalue', 'Найкр.ДД.сист.');
////    echo (time()-$start).'Найкр.ДД.сист.'.'</br>';
//
//    //Найкращий департамент системи
//    $sql="select llx_user.subdiv_id, count(distinct llx_actioncomm.id) iCount from llx_actioncomm
//            left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
//            left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end
//            where 1
//            and  date(datep) between  adddate(date(now()), interval -1 week) and date(now())
//            and (llx_actioncomm.`code`in ('AC_GLOBAL','AC_CURRENT') or llx_actioncomm.id in (select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1))
//            and llx_actioncomm.percent = 100
//            and llx_actioncomm.active = 1
//            and llx_user.subdiv_id in (select rowid from `subdivision` where active = 1)
//            group by llx_user.subdiv_id
//            order by iCount desc limit 1";
////    echo '<pre>';
////    var_dump($sql);
////    echo '</pre>';
////    die();
//    $res = $db->query($sql);
//    if(!$res)
//        dol_print_error($db);
//    $obj = $db->fetch_object($res);
//    $bestDepID = $obj->subdiv_id;
//    $out.= getTotalSubdivAction('bestvalue','Найкр.деп.сист.',$bestDepID, '',false);
////    echo (time()-$start).'Найкр.деп.сист.'.'</br>';
    //Всього по департаменту
    $subdiv = $user->subdiv_id;
    if(array_intersect(array($user->respon_id, $user->respon_id2),array(8,20,28)) >0)
        $subdiv = 0;
    $out.= _getTotalSubdivAction('','Всього',$user->subdiv_id);
//    echo (time()-$start).'Всього'.'</br>';
    $out.= _getTotalSubdivAction('','Всього "Глобальні"',$user->subdiv_id,'AC_GLOBAL');
//    echo (time()-$start).'</br>';
    $out.= _getTotalSubdivAction('','Всього "Поточні"',$user->subdiv_id,'AC_CURRENT');
//    echo (time()-$start).'</br>';
//    include '/dolibarr/htdocs/responsibility/gen_dir/day_plan.php';
    if(count(array_intersect(array(6),array($user->respon_id,$user->respon_id2)))){
//        require $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/responsibility/gen_dir/day_plan.php';
        $out.= getTotalSubdivAction('','Всього "По напрямках"',$subdiv,'AC_CUST');
    }else
        $out.= _getTotalSubdivAction('','Всього "По напрямках"',$user->subdiv_id,'AC_CUST');

    //Глобальні і поточні директора
//    echo (time()-$start).'</br>';
    $out.=getTotalUserAction($user->id, 'even', 'Всього задач');
//    echo (time()-$start).'</br>';
    $out.=getTotalUserAction($user->id, 'odd', 'Глобальні', 'AC_GLOBAL');
//    echo (time()-$start).'</br>';
    $out.=getTotalUserAction($user->id, 'even', 'Поточні', 'AC_CURRENT');
//    echo (time()-$start).'</br>';
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
            left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end";
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
            left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end";
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
    left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end";
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
        left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end";
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
        left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end";
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
        left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end";
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
            left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end";
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
function _getTotalSubdivAction($class,$title,$subdiv_id=0,$code='',$showbtn=true){
    global $db;
    $code=trim($code);
    $out = '<tr '.(empty($class)?('id="'.(empty($code)?'AllTask':$code)):'').'" class="'.$class.'">';
    $start = time();
    
    $sql="select name from subdivision where rowid = ".$subdiv_id;
    $res = $db->query($sql);
    if (!$res)
        dol_print_error($db);
    $obj=$db->fetch_object($res);
    $out.='<td colspan="2">'.$title.(!empty($subdiv_id)?(' '.trim($obj->name).'.'):'').'</td>';
//    var_dump($subdiv_id, $showbtn);
//    die();
    $sql = "select `code` from llx_c_actioncomm
                        where active = 1
                        and `type` in ('system','user')";
    $res = $db->query($sql);
    if (!$res)
        dol_print_error($db);
    $actioncode = array();
    while($obj=$db->fetch_object($res)){
        $actioncode[]="'".$obj->code."'";
    }
//    echo '<pre>';
//    var_dump($actioncode);
//    echo '</pre>';
//    die();
    if(!$showbtn)
        $out.='<td></td>';
    else
        $out.='<td><button id="btn'.(empty($code)?'AllTask':$code).'" onclick="'.($code=='AC_CUST'?'ShowLinectiveTask':'ShowActionsByUsers').'('."'".(empty($code)?'AllTask':$code)."'".');"><img id="img'.(empty($code)?'AllTask':$code).'" src="/dolibarr/htdocs/theme/eldy/img/1downarrow.png"></button></td>';
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
            left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end";
        if(!empty($subdiv_id))
            $sql.=" where llx_user.subdiv_id = " . $subdiv_id;
        else
            $sql.=" where 1";

        $sql.=" and date(datep) between  adddate(date(now()), interval -1 ".$period.") and date(now())";
        if(empty($code))
            $sql.=" and llx_actioncomm.`code` in (".implode(',',$actioncode).")";
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
            left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end";
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
    left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end";
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
        left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end";
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
        left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end";
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
        left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end";
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
            left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end";
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
    $sql = "select `code` from llx_c_actioncomm
                        where active = 1
                        and `type` in ('system','user')
                        and `code` not in ('AC_GLOBAL','AC_CURRENT')";
    $res = $db->query($sql);
    if (!$res)
        dol_print_error($db);
    $actioncode = array('');
    while($obj=$db->fetch_object($res)){
        $actioncode[]=$obj->code;
    }
    $execdID = array(0);
//    $sql = "select `llx_societe_action`.`action_id` from `llx_societe_action` where `llx_societe_action`.`callstatus` = 5 and active = 1";
    $sql = "select `llx_societe_action`.`action_id`
        from `llx_societe_action`
        inner join llx_actioncomm on `llx_actioncomm`.`id` = `llx_societe_action`.`action_id`
        where  date(datep) between  adddate(date(now()), interval -1 month) and date(now())
        and `llx_societe_action`.`callstatus` = 5 and `llx_societe_action`.active = 1";
    $res = $db->query($sql);
    if (!$res)
        dol_print_error($db);
    while($obj=$db->fetch_object($res)){
        $execdID[]=$obj->action_id;
    }
    for($i=0; $i<=1; $i++) {
        if($i<1)
            $period = 'month';
        else
            $period = 'week';
        //Всього завдань
        $sql = "select count(*) iCount from llx_actioncomm
            left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
            where date(datep) between  adddate(date(now()), interval -1 ".$period.") and date(now())
            and case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end = " . $user_id;
        if(empty($code))
            $sql.=" and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
        else{
            if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
                $sql .= " and llx_actioncomm.`code`='" . $code . "'";
            else
                $sql .= " and llx_actioncomm.`code` in (".implode(',',$actioncode).")";
        }
        $sql.=" and active = 1";
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    echo time()-$start;
//    die();
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
            and case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end = " . $user_id;
        if(empty($code))
            $sql.= " and (llx_actioncomm.`code`in ('AC_GLOBAL','AC_CURRENT') or llx_actioncomm.id in (".implode(',',$execdID)."))";
        else
            if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
                $sql .= " and llx_actioncomm.`code`='" . $code . "'";
            else{
                $sql.=" and llx_actioncomm.id in (".implode(',',$execdID).")";
            }
        $sql.=" and llx_actioncomm.percent = 100
            and llx_actioncomm.active = 1";
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);

        $obj = $db->fetch_object($res);
        $fact[$period] = $obj->iCount;
    }
//    echo time()-$start;
//    die();
    $sql = "select date(datep) datep, count(*) iCount from llx_actioncomm
    left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
    where date(datep) between  adddate(date(now()), interval -6 day) and date(now())
    and case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end = " . $user_id;
    if(empty($code))
            $sql.=" and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
        else{
            if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
                $sql .= " and llx_actioncomm.`code`='" . $code . "'";
            else
                $sql .= " and llx_actioncomm.`code` in (".implode(',',$actioncode).")";
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
            and case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end = " . $user_id;
    if(empty($code))
        $sql.=" and (llx_actioncomm.`code`in ('AC_GLOBAL','AC_CURRENT') or llx_actioncomm.id in (".implode(',',$execdID)."))";
    else{
        if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
            $sql .= " and llx_actioncomm.`code`='" . $code . "'";
        else{
            $sql.=" and llx_actioncomm.id in (".implode(',',$execdID).")";
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
//echo time()-$start;
//    var_dump($sql);
//    die('middle');
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
        and case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end = ".$user_id;
    if(empty($code))
        $sql.=" and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
    else{
        if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
            $sql .= " and llx_actioncomm.`code`='" . $code . "'";
        else
            $sql .= " and llx_actioncomm.`code` in (".implode(',',$actioncode).")";
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
        and case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end = ".$user_id;
    if(empty($code))
        $sql.=" and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
    else{
        if(in_array($code, array('AC_GLOBAL', 'AC_CURRENT')))
            $sql .= " and llx_actioncomm.`code`='" . $code . "'";
        else
            $sql .= " and llx_actioncomm.`code` in (".implode(',',$actioncode).")";
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

            $sql .= " and case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end = " . $user_id . "
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
//    die('finish');
    $out.='</tr>';
    return $out;
    //% виконання запланованого по факту
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
            left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end";
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
            left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end";
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
    left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end";
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
        left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end";
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
        left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end";
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
        left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end";
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
        left join llx_user on llx_user.rowid = case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end";
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
//    var_dump($bestuserID);
//    die();
    mysqli_data_seek($res,0);
    $out = '';
    $num = 0;
    while($obj = $db->fetch_object($res)){
//        $class_row = fmod($num,2)==0?'impare':'pare';
        $out.='<tr id="'.$class.$obj->rowid.'" class="'.$class.($bestuserID == $obj->rowid?' bestvalue ':'').' userlist '.$subdiv_id.$respon_alias.'">';
        $out.='<td colspan="2">'.$obj->lastname.' '.mb_substr($obj->firstname, 0,1,'UTF-8').'.</td>';
        if(in_array($code, array('AC_GLOBAL','AC_CURRENT'))||$respon_alias!='sale')
            $out.='<td></td>';
        else
            $out.='<td><button id="btnUsr'.$obj->rowid.'" onclick="getRegionsList('.$obj->rowid.');"><img id="imgUsr'.$obj->rowid.'" src="/dolibarr/htdocs/theme/eldy/img/1downarrow.png"></button></td>';
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
    $search = array('lineactive','impair','subdivision');
    $out = str_replace($search,'',$out);
//    llxHeader();
//    print'<table><tbody>'.$out.'</tbody></table>';
//    die();
    return $out;
}
function ShowTable1(){
    global $db,$actions,$future,$user,$user_respon,$users,$conf,$subdivTaskID,$actcode,$DepActions,$CustActions,$outstanding;
    $array = array();


    $out = '<tbody id="reference_body">';
    $today = new DateTime();
    $mkToday = dol_mktime(0,0,0,$today->format('m'),$today->format('d'),$today->format('Y'));
    foreach($actions as $action){
        $id_usr = $action['id_usr'];
        unset($action['id_usr']);
        $userActions[$id_usr][] = $action;
        $obj = (object)$action;
        $date = new DateTime($obj->datep);
        $mkDate = dol_mktime(0,0,0,$date->format('m'),$date->format('d'),$date->format('Y'));

        if($mkDate <= $mkToday && $obj->percent != 100) {
            $array[$id_usr][$obj->code]++;
        }elseif($mkDate <= $mkToday && $obj->percent == 100) {
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
                        $DepActions[$action['subdiv_id']]++;

                    }


                }

        }
    }
    $bestuser = GetBestUserID();
    $bestDepID = GetBestDepID();
//    echo '<pre>';
//    var_dump($bestDepID);
//    echo '</pre>';
//    die();
//    $firefoxColWidths=array('8'=>'35','7'=>'35','6'=>'35','5'=>'35','4'=>'35','3'=>'35','2'=>'35','1'=>'35','0'=>'35','outstanding'=>'55');
//    $chromeColWidths=array('8'=>'35','7'=>'35','6'=>'35','5'=>'35','4'=>'35','3'=>'35','2'=>'35','1'=>'35','0'=>'35','outstanding'=>'55');
    //Найкращий співробітник
    $out.='<tr class="bestvalue">';
    $out.= '<td colspan="2" class="middle_size" style="width: 288px">Найкр.пок. "Всього задач" '.$users[$bestuser]['lastname'].'</td><td style="width:33px">&nbsp;</td>';
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
        $out .= '<td class = "middle_size" style="text-align:center;">'.($total==0?'':$percent). '</td>';
    }
    //фактично виконано
    if(isset($outstanding[$bestuser]['month']))
            $out.='<td class="middle_size" style="text-align: center;width: 35px">'.array_sum($outstanding[$bestuser]['month']).'</td>';
        else
            $out.='<td style="width: 35px"></td>';
    if(isset($outstanding[$bestuser]['week']))
            $out.='<td class="middle_size" style="text-align: center;width: 35px">'.array_sum($outstanding[$bestuser]['week']).'</td>';
        else
            $out.='<td style="width: 35px"></td>';
    for($i=6;$i>=0;$i--){
        if(isset($outstanding[$bestuser]['fact'.date("Y-m-d", (time()-3600*24*$i))]))
            $out.='<td class="middle_size" style="text-align: center;">'.array_sum($outstanding[$bestuser]['fact'.date("Y-m-d", (time()-3600*24*$i))]).'</td>';
        else
            $out.='<td class="middle_size" style="text-align: center;"></td>';
    }
    $out.='<td class="middle_size" style="text-align: center;">'.(isset($outstanding[$bestuser])?array_sum($outstanding[$bestuser]):0).'</td>';

    //майбутнє (план)
    for($i=0; $i<9; $i++){
        $value = '';
        if($i < 8) {
            if($i < 7)
                $value =  (isset($future[$bestuser][date("Y-m-d", (time() + 3600 * 24 * $i))]) ? array_sum(($future[$bestuser][date("Y-m-d", (time() + 3600 * 24 * $i))])) : (''));
            else {
                if(!empty($future[$bestuser]['week']))
                    $value = (array_sum($future[$bestuser]['week']));
            }
            $out .= '<td  class="middle_size" style="text-align: center;">' . $value . '</td>';
//            $out .= '<td  class="middle_size" style="text-align: center; width: ' . ($conf->browser->name == 'firefox' ? $firefoxColWidths[$i] : $chromeColWidths[$i]) . 'px"><a href="/dolibarr/htdocs/hourly_plan.php?idmenu=10420&mainmenu=hourly_plan&leftmenu=&date=' . date("Y-m-d") . '">' . $value . '</a></td>';
        }else {
            if(!empty($future[$bestuser]['month']))
                $value = (array_sum($future[$bestuser]['month']));
            $out .= '<td class="middle_size" style="text-align: center;">' . $value . '</td>';
        }
    }
    //Виділяю задачі пов'язані з департаментом
    $subdivTaskID = SelectSubdivTask();
//echo count($actions);
//    die();
    //Всього по найкращому департамент
    $out .= getAllSubdivTask($actions, $bestDepID);
    //Всього по поточному департаменту
    $out .= getAllSubdivTask($actions, $user->subdiv_id, 'Всього задач', true);
    $out .= getUsersTask($actions, $user->subdiv_id);
    //Всього глобальних по поточному департаменту
    $out .= getAllSubdivTask($actions, $user->subdiv_id, 'Всього глобальні задачі (ТОПЗ)', true, 'AC_GLOBAL');
    $out .= getUsersTask($actions, $user->subdiv_id, false, 'AC_GLOBAL', 'ТОПЗ');
    //Всього поточних по поточному департаменту
    $out .= getAllSubdivTask($actions, $user->subdiv_id, 'Всього поточних', true, 'AC_CURRENT');
    $out .= getUsersTask($actions, $user->subdiv_id, false, 'AC_CURRENT', 'Поточні завдання');
    //Всього по напрямках по поточному департаменту
    if(array_intersect(array($user->respon_id, $user->respon_id2),array(8,20,28)) == 0)
        $out .= getAllSubdivTask($actions, $user->subdiv_id, 'Всього по напрямках', true, 'AC_CUST');
//    else



//    echo '<pre>';
//    var_dump($subdivTaskID);
//    echo '</pre>';
//die();
    $out.='</tr></tbody>';

//    echo '<pre>';
//    var_dump($actions);
//    echo '</pre>';
//    die();
    return $out;

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
function SelectSubdivTask(){
    global $actions,$user;
    $num = 0;
    $taskID = array();
    foreach($actions as $action){
        if($action["subdiv_id"]==$user->subdiv_id){
            $taskID[]=$num;
        }
        $num++;
    }
    return $taskID;
}