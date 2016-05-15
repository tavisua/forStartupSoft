<?php

require $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';

$actions = array();
$future = array();
$outstanding = array();
$sql = 'select name from subdivision where rowid = '.(empty($user->subdiv_id)?0:$user->subdiv_id);
$res = $db->query($sql);
if(!$res)
    dol_print_error($db);
$obj = $db->fetch_object($res);
$subdivision = $obj->name;
if(!isset($_SESSION['actions'])) {
    $sql = "select sub_user.rowid  id_usr, sub_user.alias, `llx_societe`.`region_id`, llx_actioncomm.percent, date(llx_actioncomm.datep) datep, llx_actioncomm.percent, case when llx_actioncomm.`code` in ('AC_GLOBAL', 'AC_CURRENT') then llx_actioncomm.`code` else 'AC_CUST' end `code`
    from llx_actioncomm
    inner join (select id from `llx_c_actioncomm` where type in('user','system') and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
    left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = llx_actioncomm.id
    left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
    inner join (select `llx_user`.rowid, `responsibility`.`alias` from `llx_user` inner join `responsibility` on `responsibility`.`rowid` = `llx_user`.`respon_id` where `llx_user`.`subdiv_id` = ".$user->subdiv_id." and `llx_user`.`active` = 1) sub_user on sub_user.rowid = case when llx_actioncomm_resources.fk_element is null then llx_actioncomm.`fk_user_author` else llx_actioncomm_resources.fk_element end
    where 1
    and llx_actioncomm.active = 1
    and datep2 between adddate(date(now()), interval -1 month) and adddate(date(now()), interval 1 month)";
//echo '<pre>';
//var_dump($sql);
//echo '</pre>';
//die();
    $res = $db->query($sql);
    if (!$res)
        dol_print_error($db);
    $actions = array();
    $time = time();
    while ($obj = $db->fetch_object($res)) {
        $actions[] = array('id_usr' => $obj->id_usr, 'region_id' => $obj->region_id, 'respon_alias' => $obj->alias, 'percent' => $obj->percent, 'datep' => $obj->datep, 'code' => $obj->code);
    }
    $_SESSION['actions'] = $actions;

}else {
    $actions = $_SESSION['actions'];
}
if(!isset($_SESSION['future'])) {
    $future = CalcFutureActions($actions);
    $_SESSION['future'] = $future;
}else{
    $future = $_SESSION['future'];
}
if(!isset($_SESSION['outstanding'])) {
    $outstanding = CalcOutStandingActions($actions);
    $_SESSION['outstanding'] = $outstanding;
}else{
    $outstanding = $_SESSION['outstanding'];
}
//echo '<pre>';
//var_dump($future);
//echo '</pre>';
//die();
if(isset($_REQUEST['action']))
    if($_REQUEST['action'] == 'ac_cust'){
        echo getResponAliasActions();
        exit();
    }elseif($_REQUEST['action'] == 'get_userlist'){
        echo getUserList();
        exit();
    }elseif($_REQUEST['action'] == 'get_regionlist'){
        echo getRegionsList();
        exit();
    }

llxHeader("",$langs->trans('PlanOfDays'),"");
print_fiche_titre($langs->trans('PlanOfDays'));



$table = ShowTable();
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/responsibility/dir_depatment/day_plan.html';

//print '</br>';
//print'<div style="float: left">test</div>';
//llxFooter();

exit();
function getRegionsList(){
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
        if($item["id_usr"]==$_REQUEST["id_usr"]&&$item["respon_alias"]=='sale'){
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
            }else{
                if($obj->percent != 100){
                    $outstanding[$item["region_id"]]++;
                }elseif($obj->percent == 100){
                    $fact[$item["region_id"]][$item["datep"]]++;
                    if($mkToday-$mkDate<=604800)//604800 sec by week
                        $fact[$item["region_id"]]['week']++;
                    if($mkToday-$mkDate<=2678400)//2678400 sec by month
                        $fact[$item["region_id"]]['month']++;
                }
            }
            $total[$item["region_id"]][$item["datep"]]++;
            if($mkToday-$mkDate<=604800)//604800 sec by week
                $total[$item["region_id"]]['week']++;
            if($mkToday-$mkDate<=2678400)//2678400 sec by month
                $total[$item["region_id"]]['month']++;
        }
    }

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
        $out.='<tr id="reg'.$obj->rowid.'" class="'.$_REQUEST["id_usr"].' regions subtype">';
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
        where subdiv_id = ".$user->subdiv_id."
        and `responsibility`.`alias` = '".$_REQUEST['respon_alias']."'
        and llx_user.active = 1
        order by llx_user.lastname, llx_user.firstname";
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
function GetBestUserID($actions, $actioncode =''){
//    echo '<pre>';
//    var_dump($actions[57]['week']);
//    echo '</pre>';
//    die();
    $maxCount = 0;
    $id_usr = 0;
    $keys = array_keys($actions);
    for($i = 0; $i<count($keys);$i++){
        if(isset($actions[$keys[$i]]['week']) && $maxCount<array_sum($actions[$keys[$i]]['week'])) {
            $maxCount = array_sum($actions[$keys[$i]]['week']);
            $id_usr = $keys[$i];
        }
    }
    return $id_usr;
}
function getResponAliasActions(){
    global $db,$langs,$subdivision,$outstanding, $future, $user;
    $sql = "select llx_user.rowid, `responsibility`.`alias` from llx_user
        inner join `responsibility` on `responsibility`.`rowid` = `llx_user`.`respon_id`
        where llx_user.rowid in(".implode(',',array_keys($outstanding)).")";
    $res_respon = $db->query($sql);
    if(!$res_respon)
        dol_print_error($res_respon);
    $user_respon = array();
    while($obj = $db->fetch_object($res_respon)){
        if(!empty($obj->alias))
            $user_respon[$obj->rowid] = $obj->alias;
    }
    $calc_action = array();

    foreach(array_keys($future) as $id_usr){
        if(isset($future[$id_usr]['month']['AC_CUST']))
             $calc_action[$user_respon[$id_usr]]['future_month'] += $future[$id_usr]['month']['AC_CUST'];
        if(isset($future[$id_usr]['week']['AC_CUST']))
             $calc_action[$user_respon[$id_usr]]['future_week'] += $future[$id_usr]['week']['AC_CUST'];
        for($i=6;$i>=0;$i--){
            if(isset($future[$id_usr][date("Y-m-d", (time()+3600*24*$i))]['AC_CUST']))
                $calc_action[$user_respon[$id_usr]]['future'.date("Y-m-d", (time()+3600*24*$i))] += $future[$id_usr][date("Y-m-d", (time()+3600*24*$i))]['AC_CUST'];
        }
    }
//    echo '<pre>';
//    var_dump($outstanding);
//    echo '</pre>';
//    die();
    foreach(array_keys($outstanding) as $id_usr){
        if(isset($outstanding[$id_usr]['AC_CUST'])){
            $calc_action[$user_respon[$id_usr]]['outstanding'] += $outstanding[$id_usr]['AC_CUST'];
        }
        if(isset($outstanding[$id_usr]['month']['AC_CUST'])){
            $calc_action[$user_respon[$id_usr]]['fact_month'] += $outstanding[$id_usr]['month']['AC_CUST'];
            $calc_action[$user_respon[$id_usr]]['total_month'] += $outstanding[$id_usr]['totalmonth']['AC_CUST'];
        }
        if(isset($outstanding[$id_usr]['week']['AC_CUST'])){
            $calc_action[$user_respon[$id_usr]]['fact_week'] += $outstanding[$id_usr]['week']['AC_CUST'];
            $calc_action[$user_respon[$id_usr]]['total_week'] += $outstanding[$id_usr]['totalweek']['AC_CUST'];
        }
        for($i=6;$i>=0;$i--){
            if(isset($outstanding[$id_usr]['fact'.date("Y-m-d", (time()-3600*24*$i))]['AC_CUST']))
                $calc_action[$user_respon[$id_usr]]['fact'.date("Y-m-d", (time()-3600*24*$i))] += $outstanding[$id_usr]['fact'.date("Y-m-d", (time()-3600*24*$i))]['AC_CUST'];
            if(isset($outstanding[$id_usr]['total'.date("Y-m-d", (time()-3600*24*$i))]['AC_CUST']))
                $calc_action[$user_respon[$id_usr]]['total'.date("Y-m-d", (time()-3600*24*$i))] += $outstanding[$id_usr]['total'.date("Y-m-d", (time()-3600*24*$i))]['AC_CUST'];
        }
    }
    $sql = "select distinct `responsibility`.`alias` from llx_user
        inner join `responsibility` on `responsibility`.`rowid` = `llx_user`.`respon_id`
        where llx_user.subdiv_id = ".$user->subdiv_id."
        and llx_user.active = 1
        and alias not in ('gen_dir','');";
//    echo '<pre>';
//    var_dump($calc_action);
//    echo '</pre>';
//    die();
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $out = '';
    if($db->num_rows($res)>0)
        while($obj = $db->fetch_object($res)){
            $out.='<tr id="'.$obj->alias.'" class="ac_cust  respon_alias" style="font-weight: bold">
            <td class="middle_size" style="width:106px">Департамент '.$subdivision.'</td>
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
                $out.='<td style="text-align: center;">'.$calc_action[$obj->alias]['future'.date("Y-m-d", (time()+3600*24*$i))].'</td>';
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
        if(($actioncode == "'AC_GLOBAL'" || $actioncode == "'AC_CURRENT'" || $user->login !="admin")&& $id_usr != 0) {
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
        if(($actioncode == "'AC_GLOBAL'" || $actioncode == "'AC_CURRENT'" || $user->login !="admin")&& $id_usr != 0)
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
function ShowGlobalCurrentTasks($Code, $Title, $outstanding, $future, $subdivision, $userlist, $showbyusers = true){
//    echo '<pre>';
//    var_dump($Code, $future);
//    echo '</pre>';
//    die();
    global $db, $conf;
    $table='';
    $nom = 0;
    $pastactions = array();
    $keys = array_keys($outstanding);
    for($i = 0; $i<count($keys);$i++){

        $keysitem = array_keys($outstanding[$keys[$i]]);
        foreach($keysitem as $key){
    //            var_dump($key);
    //            die();
            if(substr($key, 0, strlen('fact')) == 'fact' || in_array($key,array('week','month'))){
                $pastactions[$key] += isset($outstanding[$keys[$i]][$key][$Code])?$outstanding[$keys[$i]][$key][$Code]:0;
            }
            if(substr($key, 0, strlen('total')) == 'total'){

                $pastactions[$key] += $outstanding[$keys[$i]][$key][$Code];
            }
        }
    }
    $firefoxwidth = array('8'=>33,'7'=>30);
    $table.='<tr id="tr'.$Code.'" style="font-weight: bold"><td class="middle_size" style="width:106px">Департамент '.$subdivision.'</td><td class="middle_size" style="width:144px">'.$Title.'</td>';
    $table.='<td><button id="'.$Code.'" onclick="ShowHideAllTask('."'".$Code."'".');"><img id="img'.$Code.'" src="/dolibarr/htdocs/theme/eldy/img/1downarrow.png"></button></td>';
    //% виконання запланованого по факту
    for($i=8; $i>=0; $i--){
        if($i < 8) {
            $percent = 0;
            if($i<7) {
                $count = (isset($pastactions['fact' . date("Y-m-d", (time() - 3600 * 24 * $i))]) ? $pastactions['fact' . date("Y-m-d", (time() - 3600 * 24 * $i))] : ('0'));
                $total = (isset($pastactions['total' . date("Y-m-d", (time() - 3600 * 24 * $i))]) ? $pastactions['total' . date("Y-m-d", (time() - 3600 * 24 * $i))] : (1));
            }else{
                $count = isset($pastactions['week'])?$pastactions['week']:0;
                $total = isset($pastactions['totalweek'])?$pastactions['totalweek']:1;
            }
        }else{
            $count = isset($pastactions['month'])?$pastactions['month']:('0');
            $total = isset($pastactions['totalmonth'])?$pastactions['totalmonth']:('0');
        }
        $percent = round(100*$count/($total==0?1:$total));
        $table .= '<td style="text-align:center;">'. $percent. '</td>';
    }
    //минуле (факт)
    for($i=8; $i>=0; $i--){
        if($i<7)
            $table.='<td style="text-align:center;">'.(isset($pastactions['fact'.date("Y-m-d", (time() - 3600 * 24 * $i))])?$pastactions['fact'.date("Y-m-d", (time() - 3600 * 24 * $i))]:0).'</td>';
        elseif($i==7)
            $table.='<td style="text-align:center;">'.$pastactions['week'].'</td>';
        else
            $table.='<td style="text-align:center;">'.$pastactions['month'].'</td>';
    }
    $Count = 0;
    foreach(array_keys($future) as $key){
        if(isset($outstanding[$key][$Code]))
            $Count+=$outstanding[$key][$Code];
    }
    $table.='<td style="text-align: center;">'.$Count.'</td>';

    //майбутнє (план)
    for($i=0; $i<9; $i++){
        $Count = 0;
        foreach(array_keys($future) as $key){
            if($i < 7) {

                if (isset($future[$key][date("Y-m-d", (time() + 3600 * 24 * $i))][$Code])) {
                    $Count += $future[$key][date("Y-m-d", (time() + 3600 * 24 * $i))][$Code];
    //                echo $Count.' '.date("Y-m-d", (time()+3600*24*$i)).' '.$future[$key][date("Y-m-d", (time()+3600*24*$i))].'</br>';
                }
            }elseif($i==7){
                if(isset($future[$key]['week'][$Code]))
                    $Count += $future[$key]['week'][$Code];
            }else{
                if (isset($future[$key]['month'][$Code])) {
                    $Count += $future[$key]['month'][$Code];
    //                echo $Count.' '.date("Y-m-d", (time()+3600*24*$i)).' '.$future[$key][date("Y-m-d", (time()+3600*24*$i))].'</br>';
                }
            }
        }
//        if(date("Y-m-d", (time() + 3600 * 24 * $i)) == '2016-05-07'){
//            echo '<pre>';
//            var_dump($Count);
//            echo '</pre>';
//        }
        $table.='<td  style="text-align: center;">'.$Count.'</td>';
    }
    $table.='</tr>';
    if($showbyusers) {
        mysqli_data_seek($userlist, 0);
        while ($obj = $db->fetch_object($userlist)) {
            $class = (fmod($nom++, 2) == 0 ? "impair" : "pair");
            $table .= '<tr id = "' . $obj->rowid . '" class="' . $class . ' ' . $Code . '" style="display:none">
            <td class="middle_size" style="width:106px">' . $obj->lastname . ' ' . mb_substr($obj->firstname, 0, 1, 'UTF-8') . '.' . mb_substr($obj->firstname, mb_strrpos($obj->firstname, ' ', 'UTF-8') + 1, 1, 'UTF-8') . '.</td>
            <td class="middle_size" style="width:146px">' . $Title . '</td><td></td>';
            //% виконання запланованого по факту

            for ($i = 8; $i >= 0; $i--) {
                if ($i < 8) {
                    $percent = 0;
                    if ($i < 7) {
                        $count = (isset($outstanding[$obj->rowid]['fact' . date("Y-m-d", (time() - 3600 * 24 * $i))][$Code]) ? $outstanding[$obj->rowid]['fact' . date("Y-m-d", (time() - 3600 * 24 * $i))][$Code] : ('0'));
                        $total = (isset($outstanding[$obj->rowid]['total' . date("Y-m-d", (time() - 3600 * 24 * $i))][$Code]) ? $outstanding[$obj->rowid]['total' . date("Y-m-d", (time() - 3600 * 24 * $i))][$Code] : '');
                    } else {
                        $count = $outstanding[$obj->rowid]['week'][$Code];
                        $total = $outstanding[$obj->rowid]['totalweek'][$Code];
                    }
                } else {
                    $count = isset($outstanding[$obj->rowid]['month']) ? array_sum(($outstanding[$obj->rowid]['month'])) : ('0');
                    $total = isset($outstanding[$obj->rowid]['totalmonth']) ? array_sum(($outstanding[$obj->rowid]['totalmonth'])) : ('0');
                }
                if (strlen($total) > 0)
                    $percent = round(100 * $count / ($total == 0 ? 1 : $total));
                else
                    $percent = '';

                $table .= '<td style="text-align:center;">' . $percent . '</td>';
            }
            //минуле (факт)
            for ($i = 8; $i >= 0; $i--) {
                $value = 0;
                if ($i < 7) {
                    if (isset($outstanding[$obj->rowid]['fact' . date("Y-m-d", (time() + 3600 * 24 * $i))][$Code]))
                        $value = $outstanding[$obj->rowid]['fact' . date("Y-m-d", (time() + 3600 * 24 * $i))][$Code];
                } elseif ($i == 7) {
                    if (isset($outstanding[$obj->rowid]['week'][$Code]))
                        $value = $outstanding[$obj->rowid]['week'][$Code];
                } else
                    if (isset($outstanding[$obj->rowid]['month'][$Code]))
                        $value = $outstanding[$obj->rowid]['month'][$Code];
                $table .= '<td  style="text-align: center;">' . $value . '</td>';


            }
            //            //Прострочено сьогодні

            $table .= '<td style="text-align: center;"> ' . (isset($outstanding[$obj->rowid][$Code]) ? $outstanding[$obj->rowid][$Code] : 0) . '</td>';

            //майбутнє (план)
            for ($i = 0; $i < 9; $i++) {
                $value = 0;
                if ($i < 7) {
                    if (isset($future[$obj->rowid][date("Y-m-d", (time() + 3600 * 24 * $i))][$Code]))
                        $value = $future[$obj->rowid][date("Y-m-d", (time() + 3600 * 24 * $i))][$Code];
                } elseif ($i == 7) {
                    if (isset($future[$obj->rowid]['week'][$Code]))
                        $value = $future[$obj->rowid]['week'][$Code];
                } else
                    if (isset($future[$obj->rowid]['month'][$Code]))
                        $value = $future[$obj->rowid]['month'][$Code];
                $table .= '<td  style="text-align: center;">' . $value . '</td>';
            }
            unset($totaltask);
            $table .= '</tr>';
        }
    }
    return $table;
}
function CalcOutStandingActions($actions, $code=''){
    $array = array();
    $today = new DateTime();
    $mkToday = dol_mktime(0,0,0,$today->format('m'),$today->format('d'),$today->format('Y'));
//    $exec = array();
    reset($actions);
    foreach($actions as $action){
        $obj = (object)$action;
//        echo '<pre>';
//        var_dump($obj);
//        echo '</pre>';
//        die();
        $date = new DateTime($obj->datep);
        $mkDate = dol_mktime(0,0,0,$date->format('m'),$date->format('d'),$date->format('Y'));
        if($mkDate < $mkToday && $obj->percent != 100) {
            $array[$obj->id_usr][$obj->code]++;
        }elseif($mkDate < $mkToday && $obj->percent == 100) {
            $array[$obj->id_usr]['fact'.$obj->datep][$obj->code]++;
            if($mkToday-$mkDate<=604800)//604800 sec by week
                $array[$obj->id_usr]['week'][$obj->code]++;
            if($mkToday-$mkDate<=2678400)//2678400 sec by month
                $array[$obj->id_usr]['month'][$obj->code]++;
        }
        $array[$obj->id_usr]['total'.$obj->datep][$obj->code]++;
        if($mkToday-$mkDate<=604800)//604800 sec by week
            $array[$obj->id_usr]['totalweek'][$obj->code]++;
        if($mkToday-$mkDate<=2678400)//2678400 sec by month
            $array[$obj->id_usr]['totalmonth'][$obj->code]++;
    }
    return $array;
}

function ShowTable(){
    global $db, $user, $conf, $actions, $outstanding, $future;
//    echo '<pre>';
//    var_dump($actions);
//    echo '</pre>';
//    die();
    $sql = 'select name from subdivision where rowid = '.(empty($user->subdiv_id)?0:$user->subdiv_id);
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    $subdivision = $obj->name;


    $table = '<tbody id="reference_body">';
    $nom=0;
    //Підрахунок Всього
//    $bestvalue = array();



    $bestuser_id = GetBestUserID($outstanding);


    $firefoxColWidths=array('8'=>'35','7'=>'35','6'=>'35','5'=>'35','4'=>'35','3'=>'35','2'=>'35','1'=>'35','0'=>'35','outstanding'=>'55');
    $chromeColWidths=array('8'=>'35','7'=>'35','6'=>'35','5'=>'35','4'=>'35','3'=>'35','2'=>'35','1'=>'35','0'=>'35','outstanding'=>'55');
    $table.='<tr class="bestvalue"><td class="middle_size" style="width:106px">Департамент '.$subdivision.'</td><td class="middle_size" style="width:144px">Всього по найкращому</td><td style="width:33px">&nbsp;</td>';
    //% виконання запланованого по факту
    for($i=8; $i>=0; $i--){
        if($i < 8) {
            $percent = 0;
            if($i<7) {
                $count = (isset($outstanding[$bestuser_id]['fact' . date("Y-m-d", (time() - 3600 * 24 * $i))]) ? array_sum(($outstanding[$bestuser_id]['fact' . date("Y-m-d", (time() - 3600 * 24 * $i))])) : ('0'));
                $total = (isset($outstanding[$bestuser_id]['total' . date("Y-m-d", (time() - 3600 * 24 * $i))]) ? array_sum(($outstanding[$bestuser_id]['total' . date("Y-m-d", (time() - 3600 * 24 * $i))])) : (1));
            }else{
                $count = array_sum($outstanding[$bestuser_id]['week']);
                $total = array_sum($outstanding[$bestuser_id]['totalweek']);
            }
        }else{
            $count = isset($outstanding[$bestuser_id]['month'])?array_sum(($outstanding[$bestuser_id]['month'])):('0');
            $total = isset($outstanding[$bestuser_id]['totalmonth'])?array_sum(($outstanding[$bestuser_id]['totalmonth'])):('0');
        }
        $percent = round(100*$count/($total==0?1:$total));
        $table .= '<td style="width: ' . ($conf->browser->name == 'firefox' ? $firefoxColWidths[$i] : $chromeColWidths[$i]) . 'px; text-align:center;">' . $percent. '</td>';
    }
    //минуле (факт)
    for($i=8; $i>=0; $i--){
        if($i < 8)
            $table.='<td style="width: '.($conf->browser->name=='firefox'?$firefoxColWidths[$i]:$chromeColWidths[$i]).'px; text-align:center;">'.($i<7?(isset($outstanding[$bestuser_id]['fact'.date("Y-m-d", (time()-3600*24*$i))])?array_sum(($outstanding[$bestuser_id]['fact'.date("Y-m-d", (time()-3600*24*$i))])):('0')):(array_sum($outstanding[$bestuser_id]['week']))).'</td>';
        else
            $table.='<td style="width: '.($conf->browser->name=='firefox'?$firefoxColWidths[$i]:$chromeColWidths[$i]).'px; text-align:center;">'.(isset($outstanding[$bestuser_id]['month'])?array_sum(($outstanding[$bestuser_id]['month'])):('0')).'</td>';
    }
    $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?$firefoxColWidths['outstanding']:$chromeColWidths['outstanding']).'px">'.(isset($outstanding[$bestuser_id])?array_sum($outstanding[$bestuser_id]):0).'</td>';
    //майбутнє (план)
    for($i=0; $i<9; $i++){
        $value = '';
        if($i < 8) {
            if($i < 7)
                $value =  (isset($future[$bestuser_id][date("Y-m-d", (time() + 3600 * 24 * $i))]) ? array_sum(($future[$bestuser_id][date("Y-m-d", (time() + 3600 * 24 * $i))])) : ('0'));
            else {
                if(!empty($future[$bestuser_id]['week']))
                    $value = (array_sum($future[$bestuser_id]['week']));
            }
            $table .= '<td  style="text-align: center; width: ' . ($conf->browser->name == 'firefox' ? $firefoxColWidths[$i] : $chromeColWidths[$i]) . 'px"><a href="/dolibarr/htdocs/hourly_plan.php?idmenu=10420&mainmenu=hourly_plan&leftmenu=&date=' . date("Y-m-d") . '">' . $value . '</a></td>';
        }else {
            if(!empty($future[$bestuser_id]['month']))
                $value = (array_sum($future[$bestuser_id]['month']));
            $table .= '<td style="text-align: center; width: ' . ($conf->browser->name == 'firefox' ? $firefoxColWidths[$i] : $chromeColWidths[$i]) . 'px">' . $value . '</td>';
        }
    }
    $table.='</tr>';

//    isset($outstanding[$bestuser_id][date("Y-m-d", (time()+3600*24*$i))])?$outstanding[$bestuser_id][date("Y-m-d", (time()+3600*24*$i))]:('')
    //Всього задач
    $sql = "select `code` from llx_c_actioncomm
    where type in ('system','user')";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $Code='';
    while($obj = $db->fetch_object($res)){
        if(empty($Code))
            $Code = "'".$obj->code."'";
        else
            $Code .= ",'".$obj->code."'";
    }
    $table.='<tr class="total_value" style="font-weight: bold"><td class="middle_size" style="width:106px">Департамент '.$subdivision.'</td><td class="middle_size" style="width:144px">Всього задач </td>';
    $table.='<td><button onclick="ShowHideAllTask('."'AllTask'".');"><img id="imgAllTask" src="/dolibarr/htdocs/theme/eldy/img/1downarrow.png"></button></td>';
    $pastactions = array();
    $keys = array_keys($outstanding);
    for($i = 0; $i<count($keys);$i++){
        $keysitem = array_keys($outstanding[$keys[$i]]);
        foreach($keysitem as $key){
            if(substr($key, 0, strlen('fact')) == 'fact' || in_array($key, array('month', 'week')) ){
                $pastactions[$key] += array_sum($outstanding[$keys[$i]][$key]);
            }
            if(substr($key, 0, strlen('total')) == 'total' || in_array($key, array('totalmonth', 'totalweek')) ){
                $pastactions[$key] += array_sum($outstanding[$keys[$i]][$key]);
            }
        }
    }

    //% виконання запланованого по факту
    for($i=8; $i>=0; $i--){
        if($i<7) {
            $count = $pastactions['fact' . date("Y-m-d", (time() - 3600 * 24 * $i))];
            $total = $pastactions['total' . date("Y-m-d", (time() - 3600 * 24 * $i))];
        }elseif($i==7) {
            $count = $pastactions['week'];
            $total = $pastactions['totalweek'];
        }else {
            $count = $pastactions['month'];
            $total = $pastactions['totalmonth'];
        }
        $percent = round(100*$count/($total==0?1:$total));
        $table .= '<td style="width: ' . ($conf->browser->name == 'firefox' ? (31) : (32)) . 'px; text-align:center;">' .$percent. '</td>';

    }


    //минуле (факт)
    for($i=8; $i>=0; $i--){
        if($i<7)
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(32)).'px; text-align:center;">'.$pastactions['fact'.date("Y-m-d", (time() - 3600 * 24 * $i))].'</td>';
        elseif($i==7)
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(32)).'px; text-align:center;">'.$pastactions['week'].'</td>';
        else
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(32)).'px; text-align:center;">'.$pastactions['month'].'</td>';
    }
    $Count = 0;
    for($i=0; $i<count(array_keys($outstanding)); $i++){
        $Count+=array_sum($outstanding[array_keys($outstanding)[$i]]);
    }
    $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(51):(51)).'px">'.$Count.'</td>';
    //майбутнє (план)
    for($i=0; $i<9; $i++){
        $Count = 0;
        foreach(array_keys($future) as $key){
            if($i < 7) {
                if (isset($future[$key][date("Y-m-d", (time() + 3600 * 24 * $i))])) {
                    $Count += array_sum($future[$key][date("Y-m-d", (time() + 3600 * 24 * $i))]);
//                echo $Count.' '.date("Y-m-d", (time()+3600*24*$i)).' '.$future[$key][date("Y-m-d", (time()+3600*24*$i))].'</br>';
                }
            }elseif($i==7){
                if(isset($future[$key]['week']))
                    $Count += array_sum($future[$key]['week']);
            }else{
                if (isset($future[$key]['month'])) {
                    $Count += array_sum($future[$key]['month']);
//                echo $Count.' '.date("Y-m-d", (time()+3600*24*$i)).' '.$future[$key][date("Y-m-d", (time()+3600*24*$i))].'</br>';
                }
            }
        }
//        var_dump($i, $Count);
//        die();
        if($i < 8)
            $table.='<td '.($i<7?('class="allfuturetask"'):('id="allfuturetask"')).' style="text-align: center; width: '.($conf->browser->name=='firefox'?(31):(32)).'px">'.$Count.'</td>';
        else
            $table.='<td id="all_future_month" style="text-align: center; width: '.($conf->browser->name=='firefox'?(33):(34)).'px">'.$Count.'</td>';

    }
    $table.='</tr>';

    $sql = "select rowid, lastname, firstname from llx_user where 1 ".(empty($user->subdiv_id)?"":"and`subdiv_id` = ".$user->subdiv_id);
//    die($sql);
    $userlist = $db->query($sql);
    if(!$userlist)
        dol_print_error($db);
    $nom = 0;
    while($obj = $db->fetch_object($userlist)){
        $class=(fmod($nom++,2)==0?"impair":"pair");
        $table.='<tr id = "'.$obj->rowid.'" class="'.$class.' alltask" style="display:none">
            <td class="middle_size" style="width:106px">'.$obj->lastname.' '.mb_substr($obj->firstname, 0, 1, 'UTF-8').'.'.mb_substr($obj->firstname, mb_strrpos($obj->firstname, ' ','UTF-8')+1, 1, 'UTF-8').'.</td>
            <td class="middle_size" style="width:146px">Всього задач</td><td></td>';
            //% виконання запланованого по факту
            for($i=8; $i>=0; $i--){
                if($i < 8) {
                    $percent = 0;
                    if($i<7) {
                        $count = (isset($outstanding[$obj->rowid]['fact' . date("Y-m-d", (time() - 3600 * 24 * $i))]) ? array_sum(($outstanding[$obj->rowid]['fact' . date("Y-m-d", (time() - 3600 * 24 * $i))])) : ('0'));
                        $total = (isset($outstanding[$obj->rowid]['total' . date("Y-m-d", (time() - 3600 * 24 * $i))]) ? array_sum(($outstanding[$obj->rowid]['total' . date("Y-m-d", (time() - 3600 * 24 * $i))])) : '');
                    }else{
                        $count = isset($outstanding[$obj->rowid]['week'])?array_sum($outstanding[$obj->rowid]['week']):0;
                        $total = isset($outstanding[$obj->rowid]['totalweek'])?array_sum($outstanding[$obj->rowid]['totalweek']):1;
                    }
                }else{
                    $count = isset($outstanding[$obj->rowid]['month'])?array_sum(($outstanding[$obj->rowid]['month'])):('0');
                    $total = isset($outstanding[$obj->rowid]['totalmonth'])?array_sum(($outstanding[$obj->rowid]['totalmonth'])):('');
                }
                if(strlen($total)>0)
                    $percent = round(100*$count/($total==0?1:$total));
                else
                    $percent = '';
                $table .= '<td style="width: ' . ($conf->browser->name == 'firefox' ? (31) : (32)) . 'px; text-align:center;">' . $percent. '</td>';
            }
            //минуле (факт)
            for($i=8; $i>=0; $i--){
                if($i < 8) {
                    $count = 0;
                    if($i<7&&isset($outstanding[$obj->rowid]['fact' . date("Y-m-d", (time() - 3600 * 24 * $i))]))
                        $count = array_sum(($outstanding[$obj->rowid]['fact' . date("Y-m-d", (time() - 3600 * 24 * $i))]));
                    elseif($i == 7 && isset($outstanding[$obj->rowid]['week']))
                        $count = array_sum($outstanding[$obj->rowid]['week']);
                    $table .= '<td style="width: ' . ($conf->browser->name == 'firefox' ? (31) : (32)) . 'px; text-align:center;">' .$count . '</td>';
                }else
                    $table.='<td style="width: '.($conf->browser->name=='firefox'?(33):(34)).'px; text-align:center;">'.(isset($outstanding[$obj->rowid]['month'])?array_sum(($outstanding[$obj->rowid]['month'])):('')).'</td>';
            }
//            //Прострочено сьогодні
            $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(51):(51)).'px"> '.(isset($outstanding[$obj->rowid])?array_sum($outstanding[$obj->rowid]):0).'</td>';
            //майбутнє (план)
            for($i=0; $i<9; $i++){
                if($i < 7)
                    $table.='<td  class = "all_future_today" style="text-align: center; width: '.($conf->browser->name=='firefox'?(32):(33)).'px">'.(isset($future[$obj->rowid][date("Y-m-d", (time()+3600*24*$i))])?(array_sum($future[$obj->rowid][date("Y-m-d", (time()+3600*24*$i))])):('')).'</td>';
                elseif($i == 7)
                    $table.='<td  class = "all_future_today" style="text-align: center; width: '.($conf->browser->name=='firefox'?(32):(33)).'px">'.(isset($future[$obj->rowid]['week'])?(array_sum($future[$obj->rowid]['week'])):('')).'</td>';
                else
                    $table.='<td class = "all_future_month" style="text-align: center; width: '.($conf->browser->name=='firefox'?(32):(34)).'px">'.(isset($future[$obj->rowid]['month'])?array_sum($future[$obj->rowid]['month']):'').'</td>';
            }
            unset($totaltask);
        $table.='</tr>';
    }

    $Code = "AC_GLOBAL";
    $table.= ShowGlobalCurrentTasks($Code, 'Всього глобальні задачі (ТОПЗ)', $outstanding, $future, $subdivision, $userlist);

    //Поточні задачі
    $Code = "AC_CURRENT";
    $table.= ShowGlobalCurrentTasks($Code, 'Всього поточних', $outstanding, $future, $subdivision, $userlist);

    $Code = "AC_CUST";
    $table.= ShowGlobalCurrentTasks($Code, 'Всього по напрямках', $outstanding, $future, $subdivision, $userlist, false);
//    $sql = "select rowid from responsibility where alias in ('sale')";
//    $res = $db->query($sql);
//    if(!$res)
//        dol_print_error($db);
//    $respon = array();
//    while($obj = $db->fetch_object($res)){
//        $respon[]=$obj->rowid;
//    }
//    $Code = "'AC_TEL','AC_FAX','AC_EMAIL','AC_RDV','AC_INT','AC_OTH','AC_DEP'";
//    $bestvalue = array();
//    $bestuser_id = GetBestUserID($Code, implode(',', $respon));
//
//    $table.='<tr class="bestvalue"><td class="middle_size" style="width:106px">Продажі '.$subdivision.'</td><td class="middle_size" style="width:144px">Всього по найкращому</td>';
//    //% виконання запланованого по факту
//    for($i=8; $i>=0; $i--){
//        if($i == 8)
//            $table.='<td style="width: '.($conf->browser->name=='firefox'?(33):(34)).'px; text-align:center;">'.$bestvalue['percent_month'].'</td>';
//        elseif($i == 0)
//            $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(32)).'px; text-align:center;">'.$bestvalue['percent_'.$i].'</td>';
//        else
//             $table.='<td style="width: '.($conf->browser->name=='firefox'?(30):(31)).'px; text-align:center;">'.$bestvalue['percent_'.$i].'</td>';
//    }
//    //минуле (факт)
//    for($i=8; $i>=0; $i--){
//        if($i == 0)
//            $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(32)).'px; text-align:center;">'.$bestvalue['fakt_today'].'</td>';
//        elseif($i == 8)
//            $table.='<td style="width: '.($conf->browser->name=='firefox'?(33):(34)).'px; text-align:center;">'.$bestvalue['fakt_month'].'</td>';
//        else
//            $table.='<td style="width: '.($conf->browser->name=='firefox'?(30):(30)).'px; text-align:center;">' . $bestvalue['fakt_day_m' . $i] . '</td>';
//    }
//    $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(51):(51)).'px">'.$bestvalue["outstanding"].'</td>';
//    //майбутнє (план)
//    for($i=0; $i<9; $i++){
//        if($i < 7)
//            $table.='<td  class = "all_future_today" style="text-align: center; width: '.($conf->browser->name=='firefox'?(32):(33)).'px">'.(isset($future[$obj->rowid][date("Y-m-d", (time()+3600*24*$i))])?(array_sum($future[$obj->rowid][date("Y-m-d", (time()+3600*24*$i))])):('0')).'</td>';
//        elseif($i == 7)
//            $table.='<td  class = "all_future_today" style="text-align: center; width: '.($conf->browser->name=='firefox'?(32):(33)).'px">'.(isset($future[$obj->rowid]['week'])?(array_sum($future[$obj->rowid]['week'])):('0')).'</td>';
//        else
//            $table.='<td class = "all_future_month" style="text-align: center; width: '.($conf->browser->name=='firefox'?(32):(34)).'px">'.(isset($future[$obj->rowid]['month'])?array_sum($future[$obj->rowid]['month']):0).'</td>';
//    }
//    $table.='</tr>';
//   //Всього задач по направленнях та співробітниках
//    $sql = "select `code` from llx_c_actioncomm
//    where type in ('system','user')
//    and code not in ('AC_GLOBAL', 'AC_CURRENT')";
//    $res = $db->query($sql);
//    if(!$res)
//        dol_print_error($db);
//    $Code='';
//    while($obj = $db->fetch_object($res)){
//        if(empty($Code))
//            $Code = "'".$obj->code."'";
//        else
//            $Code .= ",'".$obj->code."'";
//    }
//    $totaltask = array();
//    $totaltask = CalcOutStandingActions($Code, $totaltask, 0);
//    $totaltask = CalcoutstandingActions($Code, $totaltask, 0);
//    $totaltask = CalcFaktActions($Code, $totaltask, 0);
//    $totaltask = CalcPercentExecActions($Code, $totaltask, 0);
//    $table.='<tr style="font-weight: bold"><td class="middle_size" style="width:106px">Всього завдань</td><td class="middle_size" style="width:144px">по співробітниках і направленнях</td>';
//    //% виконання запланованого по факту
//    for($i=8; $i>=0; $i--){
//        if($i == 8)
//            $table.='<td style="width: '.($conf->browser->name=='firefox'?(33):(34)).'px; text-align:center;">'.$totaltask['percent_month'].'</td>';
//        elseif($i == 0)
//            $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(32)).'px; text-align:center;">'.$totaltask['percent_'.$i].'</td>';
//        else
//             $table.='<td style="width: '.($conf->browser->name=='firefox'?(30):(31)).'px; text-align:center;">'.$totaltask['percent_'.$i].'</td>';
//    }
//    //минуле (факт)
//    for($i=8; $i>=0; $i--){
//        if($i == 0)
//            $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(32)).'px; text-align:center;">'.$totaltask['fakt_today'].'</td>';
//        elseif($i == 8)
//            $table.='<td style="width: '.($conf->browser->name=='firefox'?(33):(34)).'px; text-align:center;">'.$totaltask['fakt_month'].'</td>';
//        else
//            $table.='<td style="width: '.($conf->browser->name=='firefox'?(30):(30)).'px; text-align:center;">' . $totaltask['fakt_day_m' . $i] . '</td>';
//    }
//    $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(51):(51)).'px">'.$totaltask["outstanding"].'</td>';
//    //майбутнє (план)
//    for($i=0; $i<9; $i++){
//        if($i == 0)
//            $table.='<td  style="text-align: center; width: '.($conf->browser->name=='firefox'?(31):(32)).'px">'.$totaltask['outstanding_today'].'</td>';
//        elseif($i == 8)
//            $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(33):(34)).'px">'.$totaltask['outstanding_month'].'</td>';
//        else {
//            $table .= '<td style="text-align: center; width: ' . ($conf->browser->name == 'firefox' ? (30) : (31)) . 'px">' . $totaltask['outstanding_day_pl' . $i] . '</td>';
//        }
//    }
//    $table.='</tr>';
//    $outstanding_actions = array();
//    //Майбутні дії
//    for($i=0; $i<9; $i++) {
//        $sql = "select `llx_societe`.`region_id`, count(*) as iCount  from `llx_actioncomm`
//        inner join
//        (select id from `llx_c_actioncomm` where type in ('system','user') and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
//        left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
//        inner join `llx_actioncomm_resources` on `llx_actioncomm`.`id` =  `llx_actioncomm_resources`.`fk_actioncomm`
//        where 1 ";
//        $sql .= "and `llx_actioncomm_resources`.fk_element in (select rowid from llx_user where 1 and subdiv_id=".$user->subdiv_id." and `respon_id` in (".implode(',', $respon)."))";
//        if($i<8) {
//            $query_date = date("Y-m-d", (time()+3600*24*$i));
//            if($i!=7)
//                $sql .= " and datep2 between '".$query_date . "' and date_add('" . $query_date . "', interval 1 day)";
//            else
//                $sql .= " and datep2 between '".date("Y-m-d") . "' and date_add('" . date("Y-m-d") . "', interval 7 day)";
//        }else {
//            $month = date("m");
//            if($month+1<10)
//                $month = '0'.($month+1);
//            else
//                $month =($month+1);
//                $sql .= " and datep2 between '" . date("Y-m-d") . "' and '" . date("Y") . "-" . $month . "-" . (date("d")) . "'";
//        }
//        $sql .=" and datea is null
//        group by `llx_societe`.`region_id`";
//
//        $res = $db->query($sql);
//        while($res && $obj = $db->fetch_object($res)){
//            if($i<8)
//                $outstanding_actions[$obj->region_id.'_'.($i)]=$obj->iCount;
//            else
//                $outstanding_actions[$obj->region_id.'_month']=$obj->iCount;
//        }
//    }
////    echo '<pre>';
////    var_dump($sql);
////    echo '</pre>';
////    die();
//    //Прострочені дії
//    $outstanding_actions = array();
//    $sql = "select `llx_societe`.`region_id`, count(*) as iCount  from `llx_actioncomm`
//    inner join
//    (select id from `llx_c_actioncomm` where type in ('system','user') and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
//    left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
//    inner join `llx_actioncomm_resources` on `llx_actioncomm`.`id` =  `llx_actioncomm_resources`.`fk_actioncomm`
//    where 1 ";
//    $sql .= "and `llx_actioncomm_resources`.fk_element in (select rowid from llx_user where 1 and subdiv_id=".$user->subdiv_id." and `respon_id` in (".implode(',', $respon)."))";
//    $sql .= " and datep2 < '".date("Y-m-d")."'";
//    $sql .=" and datea is null
//    group by `llx_societe`.`region_id`";
//    $res = $db->query($sql);
//    while($obj = $db->fetch_object($res)){
//        $outstanding_actions[$obj->region_id]=$obj->iCount;
//    }
//    //Виконані дії
//    $exec_actions = array();
//    $percent_action = array();
//    for($i=0; $i<9; $i++) {
//        $sql = "select `llx_societe`.`region_id`, count(*) as iCount  from `llx_actioncomm`
//        inner join
//        (select id from `llx_c_actioncomm` where type in ('system','user') and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
//        left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
//        inner join `llx_actioncomm_resources` on `llx_actioncomm`.`id` =  `llx_actioncomm_resources`.`fk_actioncomm`
//        where 1 ";
//        $sql .= "and `llx_actioncomm_resources`.fk_element in (select rowid from llx_user where 1 and subdiv_id=".$user->subdiv_id." and `respon_id` in (".implode(',', $respon)."))";
////        if($i < 2) {
////            var_dump($sql);
////            die();
////        }
//
//        if($i<8) {
//            $query_date = date("Y-m-d", (time()-3600*24*$i+3600*24));
//            if($i!=7)
//                $sql .= " and datep2 between date_add('" . $query_date . "', interval -1 day) and '".$query_date . "'";
//            else
//                $sql .= " and datep2 between date_add('" . date("Y-m-d") . "', interval -7 day) and '".date("Y-m-d") . "'";
//        }else {
//            $month = date("m");
//            if($month-1<10)
//                $month = '0'.($month-1);
//            else
//                $month =($month-1);
//                $sql .= " and datep2 between '" . date("Y") . "-" . $month . "-" . (date("d")) . "' and '" . date("Y-m-d") . "'";
//        }
//
//        $res = $db->query($sql." group by `llx_societe`.`region_id`");
//
//        while($res && $obj = $db->fetch_object($res)){
//            if($i<8) {
//                $totaltask[$obj->region_id.'_'.$i] = $obj->iCount;
//            }else
//                $totaltask[$obj->region_id.'_month']=$obj->iCount;
//        }
//
//        $res = $db->query($sql." and datea is not null
//                 group by `llx_societe`.`region_id`");
//
//        while($res && $obj = $db->fetch_object($res)){
//            if($i<8) {
//                $exec_actions[$obj->region_id . '_' . ($i)] = $obj->iCount;
//                if($totaltask[$obj->region_id.'_'.$i]!=0){
//                   $percent_action[$obj->region_id.'_'.$i] = round($exec_actions[$obj->region_id.'_'.$i]*100/$totaltask[$obj->region_id.'_'.$i],0);
//                }else{
//                   $percent_action[$obj->region_id.'_'.$i] = '';
//                }
//            }else {
//                $exec_actions[$obj->region_id . '_month'] = $obj->iCount;
//                if($totaltask[$obj->region_id.'_month'] != 0){
//                    $percent_action[$obj->region_id.'_month'] =  round($exec_actions[$obj->region_id.'_month']*100/$totaltask[$obj->region_id.'_month'],0);
//                }else{
//                   $percent_action[$obj->region_id.'_month'] = '';
//                }
//            }
//        }
//    }
//    $sql = "select `regions`.rowid, llx_user.lastname, llx_user.firstname,`regions`.`name` from llx_user
//        left join `llx_user_regions` on `llx_user_regions`.`fk_user` = llx_user.rowid
//        left join `regions` on `regions`.`rowid` = `llx_user_regions`.`fk_id`
//        where 1 ".(empty($user->subdiv_id)?"":" and subdiv_id=".$user->subdiv_id)."
//        and llx_user.respon_id in (".implode(",", $respon).")";
//    $res = $db->query($sql);
//    if(!$res)
//        dol_print_error($db);
//
////    echo '<pre>';
////    var_dump($outstanding_actions);
////    echo '</pre>';
////    die();
//    $nom = 1;
//    while($obj = $db->fetch_object($res)){
//        $class=(fmod($nom++,2)==0?"impair":"pair");
//        $table .= '<tr class = "'.$class.'"id = "'.$obj->rowid.'">
//        <td>'.$obj->lastname.' '.mb_substr($obj->firstname, 0, 1, 'UTF-8').'.'.mb_substr($obj->firstname, mb_strrpos($obj->firstname, ' ','UTF-8')+1, 1, 'UTF-8').'.</td>
//        <td>'.$obj->name.'</td>';
//        //% виконання запланованого по факту
//        for($i=8; $i>=0; $i--){
//            if($i == 8)
//                $table.='<td style="width: '.($conf->browser->name=='firefox'?(33):(34)).'px; text-align:center;">'.$percent_action[$obj->rowid.'_month'].'</td>';
//            elseif($i == 0)
//                $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(32)).'px; text-align:center;">'.$percent_action[$obj->rowid.'_'.$i].'</td>';
//            else
//                 $table.='<td style="width: '.($conf->browser->name=='firefox'?(30):(31)).'px; text-align:center;">'.$percent_action[$obj->rowid.'_'.$i].'</td>';
//        }
//        //минуле (факт)
//        for($i=8; $i>=0; $i--){
//            if($i == 0)
//                $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(32)).'px; text-align:center;">'.$exec_actions[$obj->rowid.'_' . $i].'</td>';
//            elseif($i == 8)
//                $table.='<td style="width: '.($conf->browser->name=='firefox'?(33):(34)).'px; text-align:center;">'.$exec_actions[$obj->rowid.'_month'].'</td>';
//            else
//                $table.='<td style="width: '.($conf->browser->name=='firefox'?(30):(30)).'px; text-align:center;">' . $exec_actions[$obj->rowid.'_' . $i] . '</td>';
//        }
//        $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(51):(51)).'px">'.$outstanding_actions[$obj->rowid].'</td>';
//        //майбутнє (план)
//        for($i=0; $i<9; $i++){
//            if($i == 0)
//                $table.='<td  style="text-align: center; width: '.($conf->browser->name=='firefox'?(31):(32)).'px">'.$outstanding_actions[$obj->rowid.'_' . $i].'</td>';
//            elseif($i == 8)
//                $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(33):(34)).'px">'.$outstanding_actions[$obj->rowid.'_month'].'</td>';
//            else {
//                $table .= '<td style="text-align: center; width: ' . ($conf->browser->name == 'firefox' ? (30) : (31)) . 'px">' . $outstanding_actions[$obj->rowid.'_' . $i] . '</td>';
//            }
//        }
//        $table .= '</tr>';
//    }
    $table .= '</tbody>';
    return $table;
}