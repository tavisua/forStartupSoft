<?php

require $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';

//echo '<pre>';
//var_dump($user);
//echo '</pre>';
//die();

llxHeader("",$langs->trans('PlanOfDays'),"");
print_fiche_titre($langs->trans('PlanOfDays'));

$sql = 'select name from subdivision where rowid = '.(empty($user->subdiv_id)?0:$user->subdiv_id);
$res = $db->query($sql);
if(!$res)
    dol_print_error($db);
$obj = $db->fetch_object($res);
$subdivision = $obj->name;

$table = ShowTable();
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/responsibility/dir_depatment/day_plan.html';

//print '</br>';
//print'<div style="float: left">test</div>';
//llxFooter();

exit();
function GetBestUserID($actions, $actioncode =''){
    $maxCount = 0;
    $id_usr = 0;
    $keys = array_keys($actions);
    for($i = 0; $i<count($keys);$i++){
        if(isset($actions[$keys[$i]]['week']) && $maxCount<$actions[$keys[$i]]['week'] ) {
            $maxCount = $actions[$keys[$i]]['week'];
            $id_usr = $keys[$i];
        }
    }
    return $id_usr;
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
function ShowGlobalCurrentTasks($Code, $Title, $outstanding, $future, $subdivision, $userlist){
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
    $table.='<tr style="font-weight: bold"><td class="middle_size" style="width:106px">Департамент '.$subdivision.'</td><td class="middle_size" style="width:144px">'.$Title.'</td>';
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
        $table .= '<td style="width: ' . ($conf->browser->name == 'firefox' ? (31) : (32)) . 'px; text-align:center;">'. $percent. '</td>';
    }
    //минуле (факт)
    for($i=8; $i>=0; $i--){
        if($i<7)
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(32)).'px; text-align:center;">'.(isset($pastactions['fact'.date("Y-m-d", (time() - 3600 * 24 * $i))])?$pastactions['fact'.date("Y-m-d", (time() - 3600 * 24 * $i))]:0).'</td>';
        elseif($i==7)
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(32)).'px; text-align:center;">'.$pastactions['week'].'</td>';
        else
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(32)).'px; text-align:center;">'.$pastactions['month'].'</td>';
    }
    $Count = 0;
    foreach(array_keys($future) as $key){
        if(isset($outstanding[$key][$Code]))
            $Count+=$outstanding[$key][$Code];
    }
    $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(51):(51)).'px">'.$Count.'</td>';
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
        $table.='<td  style="text-align: center; width: '.($conf->browser->name=='firefox'?(31):(32)).'px">'.$Count.'</td>';
    }
    $table.='</tr>';

    mysqli_data_seek($userlist, 0);
    while($obj = $db->fetch_object($userlist)){
        $class=(fmod($nom++,2)==0?"impair":"pair");
        $table.='<tr id = "'.$obj->rowid.'" class="'.$class.'">
            <td class="middle_size" style="width:106px">'.$obj->lastname.' '.mb_substr($obj->firstname, 0, 1, 'UTF-8').'.'.mb_substr($obj->firstname, mb_strrpos($obj->firstname, ' ','UTF-8')+1, 1, 'UTF-8').'.</td>
            <td class="middle_size" style="width:146px">'.$Title.'</td>';
        //% виконання запланованого по факту

        for($i=8; $i>=0; $i--){
            if($i < 8) {
                $percent = 0;
                if($i<7) {
                    $count = (isset($outstanding[$obj->rowid]['fact' . date("Y-m-d", (time() - 3600 * 24 * $i))][$Code]) ? $outstanding[$obj->rowid]['fact' . date("Y-m-d", (time() - 3600 * 24 * $i))][$Code] : ('0'));
                    $total = (isset($outstanding[$obj->rowid]['total' . date("Y-m-d", (time() - 3600 * 24 * $i))][$Code]) ? $outstanding[$obj->rowid]['total' . date("Y-m-d", (time() - 3600 * 24 * $i))][$Code] : '');
                }else{
                    $count = $outstanding[$obj->rowid]['week'][$Code];
                    $total = $outstanding[$obj->rowid]['totalweek'][$Code];
                }
            }else{
                $count = isset($outstanding[$obj->rowid]['month'])?array_sum(($outstanding[$obj->rowid]['month'])):('0');
                $total = isset($outstanding[$obj->rowid]['totalmonth'])?array_sum(($outstanding[$obj->rowid]['totalmonth'])):('0');
            }
            if(strlen($total)>0)
                $percent = round(100*$count/($total==0?1:$total));
            else
                $percent = '';
            $table .= '<td style="width: ' . ($conf->browser->name == 'firefox' ? (31) : (32)) . 'px; text-align:center;">' . $percent. '</td>';
        }
        //минуле (факт)
        for($i=8; $i>=0; $i--){
            $value = 0;
            if($i<7) {
                if(isset($outstanding[$obj->rowid]['fact'.date("Y-m-d", (time() + 3600 * 24 * $i))][$Code]))
                    $value = $outstanding[$obj->rowid]['fact'.date("Y-m-d", (time() + 3600 * 24 * $i))][$Code];
            }elseif($i==7){
                if(isset($outstanding[$obj->rowid]['week'][$Code]))
                    $value = $outstanding[$obj->rowid]['week'][$Code];
            }else
                if(isset($outstanding[$obj->rowid]['month'][$Code]))
                    $value = $outstanding[$obj->rowid]['month'][$Code];
            $table .= '<td  style="text-align: center; width: ' . ($conf->browser->name == 'firefox' ? (32) : (33)) . 'px">' .$value.'</td>';


        }
    //            //Прострочено сьогодні

        $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(51):(51)).'px"> '.(isset($outstanding[$obj->rowid][$Code])?$outstanding[$obj->rowid][$Code]:0).'</td>';

        //майбутнє (план)
        for($i=0; $i<9; $i++){
            $value = 0;
            if($i<7) {
                if(isset($future[$obj->rowid][date("Y-m-d", (time() + 3600 * 24 * $i))][$Code]))
                    $value = $future[$obj->rowid][date("Y-m-d", (time() + 3600 * 24 * $i))][$Code];
            }elseif($i==7){
                if(isset($future[$obj->rowid]['week'][$Code]))
                    $value = $future[$obj->rowid]['week'][$Code];
            }else
                if(isset($future[$obj->rowid]['month'][$Code]))
                    $value = $future[$obj->rowid]['month'][$Code];
            $table .= '<td  style="text-align: center; width: ' . ($conf->browser->name == 'firefox' ? (32) : (33)) . 'px">' .$value.'</td>';
        }
        unset($totaltask);
        $table.='</tr>';
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
    global $db, $user, $conf;

    $sql = 'select name from subdivision where rowid = '.(empty($user->subdiv_id)?0:$user->subdiv_id);
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    $subdivision = $obj->name;

    $sql = "select subdivision.rowid  id_usr, `llx_societe`.`region_id`, llx_actioncomm.percent, date(llx_actioncomm.datep) datep, llx_actioncomm.percent, case when llx_actioncomm.`code` in ('AC_GLOBAL', 'AC_CURRENT') then llx_actioncomm.`code` else 'AC_CUST' end `code`
        from llx_actioncomm
        inner join (select id from `llx_c_actioncomm` where type in('user','system') and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
        left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = llx_actioncomm.id
        left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
        inner join (select rowid from `llx_user` where `llx_user`.`subdiv_id` = 1 and `llx_user`.`active` = 1) subdivision on subdivision.rowid = case when llx_actioncomm_resources.fk_element is null then llx_actioncomm.`fk_user_author` else llx_actioncomm_resources.fk_element end
        where 1
        and llx_actioncomm.active = 1
        and datep2 between adddate(date(now()), interval -1 month) and adddate(date(now()), interval 1 month)";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $actions = array();
    $time = time();
    while($obj = $db->fetch_object($res)){
        $actions[] = array('id_usr'=>$obj->id_usr,'region_id'=>$obj->region_id, 'percent'=>$obj->percent,'datep'=>$obj->datep,'code'=>$obj->code);
    }
//    echo '<pre>';
//    var_dump($actions);
//    echo '</pre>';
//    die();
    $table = '<tbody id="reference_body">';
    $nom=0;
    //Підрахунок Всього
//    $bestvalue = array();
    $future = array();
    $future = CalcFutureActions($actions);
    $outstanding = array();
    $outstanding = CalcOutStandingActions($actions);
    $bestuser_id = GetBestUserID($outstanding);



    $table.='<tr class="bestvalue"><td class="middle_size" style="width:106px">Департамент '.$subdivision.'</td><td class="middle_size" style="width:144px">Всього по найкращому</td>';
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
        $table .= '<td style="width: ' . ($conf->browser->name == 'firefox' ? (31) : (32)) . 'px; text-align:center;">' . $percent. '</td>';
    }
    //минуле (факт)
    for($i=8; $i>=0; $i--){
        if($i < 8)
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(32)).'px; text-align:center;">'.($i<7?(isset($outstanding[$bestuser_id]['fact'.date("Y-m-d", (time()-3600*24*$i))])?array_sum(($outstanding[$bestuser_id]['fact'.date("Y-m-d", (time()-3600*24*$i))])):('0')):(array_sum($outstanding[$bestuser_id]['week']))).'</td>';
        else
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(33):(34)).'px; text-align:center;">'.(isset($outstanding[$bestuser_id]['month'])?array_sum(($outstanding[$bestuser_id]['month'])):('0')).'</td>';
    }
    $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(51):(51)).'px">'.(isset($outstanding[$bestuser_id])?array_sum($outstanding[$bestuser_id]):0).'</td>';
    //майбутнє (план)
    for($i=0; $i<9; $i++){
        if($i < 8)
            $table.='<td  style="text-align: center; width: '.($conf->browser->name=='firefox'?(31):(32)).'px"><a href="/dolibarr/htdocs/hourly_plan.php?idmenu=10420&mainmenu=hourly_plan&leftmenu=&date='.date("Y-m-d").'">'.($i<7?(isset($future[$bestuser_id][date("Y-m-d", (time()+3600*24*$i))])?array_sum(($future[$bestuser_id][date("Y-m-d", (time()+3600*24*$i))])):('0')):(array_sum($future[$bestuser_id]['week']))).'</a></td>';
        else
            $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(33):(34)).'px">'.array_sum($future[$bestuser_id]['month']).'</td>';
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
    $table.='<tr class="total_value" style="font-weight: bold"><td class="middle_size" style="width:106px">Департамент '.$subdivision.'</td><td class="middle_size" style="width:144px">Всього задач</td>';
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
        $table.='<tr id = "'.$obj->rowid.'" class="'.$class.'">
            <td class="middle_size" style="width:106px">'.$obj->lastname.' '.mb_substr($obj->firstname, 0, 1, 'UTF-8').'.'.mb_substr($obj->firstname, mb_strrpos($obj->firstname, ' ','UTF-8')+1, 1, 'UTF-8').'.</td>
            <td class="middle_size" style="width:146px">Всього задач</td>';
            $totaltask = array();
//            $totaltask = CalcOutStandingActions($Code, $totaltask, $obj->rowid);
//        if(47 == $obj->rowid){
//            echo '<pre>';
//            var_dump($totaltask);
//            echo '</pre>';
//            die($Code);
//        }
//            $totaltask = CalcoutstandingActions($Code, $totaltask, $obj->rowid);
//            $totaltask = CalcFaktActions($Code, $totaltask, $obj->rowid);
//            $totaltask = CalcPercentExecActions($Code, $totaltask, $obj->rowid);

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
    $table .= '</tbody>';
    return $table;

    $sql = "select rowid from responsibility where alias in ('sale')";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $respon = array();
    while($obj = $db->fetch_object($res)){
        $respon[]=$obj->rowid;
    }
    $Code = "'AC_TEL','AC_FAX','AC_EMAIL','AC_RDV','AC_INT','AC_OTH','AC_DEP'";
    $bestvalue = array();
    $bestuser_id = GetBestUserID($Code, implode(',', $respon));

    $table.='<tr class="bestvalue"><td class="middle_size" style="width:106px">Продажі '.$subdivision.'</td><td class="middle_size" style="width:144px">Всього по найкращому</td>';
    //% виконання запланованого по факту
    for($i=8; $i>=0; $i--){
        if($i == 8)
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(33):(34)).'px; text-align:center;">'.$bestvalue['percent_month'].'</td>';
        elseif($i == 0)
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(32)).'px; text-align:center;">'.$bestvalue['percent_'.$i].'</td>';
        else
             $table.='<td style="width: '.($conf->browser->name=='firefox'?(30):(31)).'px; text-align:center;">'.$bestvalue['percent_'.$i].'</td>';
    }
    //минуле (факт)
    for($i=8; $i>=0; $i--){
        if($i == 0)
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(32)).'px; text-align:center;">'.$bestvalue['fakt_today'].'</td>';
        elseif($i == 8)
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(33):(34)).'px; text-align:center;">'.$bestvalue['fakt_month'].'</td>';
        else
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(30):(30)).'px; text-align:center;">' . $bestvalue['fakt_day_m' . $i] . '</td>';
    }
    $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(51):(51)).'px">'.$bestvalue["outstanding"].'</td>';
    //майбутнє (план)
    for($i=0; $i<9; $i++){
        if($i < 7)
            $table.='<td  class = "all_future_today" style="text-align: center; width: '.($conf->browser->name=='firefox'?(32):(33)).'px">'.(isset($future[$obj->rowid][date("Y-m-d", (time()+3600*24*$i))])?(array_sum($future[$obj->rowid][date("Y-m-d", (time()+3600*24*$i))])):('0')).'</td>';
        elseif($i == 7)
            $table.='<td  class = "all_future_today" style="text-align: center; width: '.($conf->browser->name=='firefox'?(32):(33)).'px">'.(isset($future[$obj->rowid]['week'])?(array_sum($future[$obj->rowid]['week'])):('0')).'</td>';
        else
            $table.='<td class = "all_future_month" style="text-align: center; width: '.($conf->browser->name=='firefox'?(32):(34)).'px">'.(isset($future[$obj->rowid]['month'])?array_sum($future[$obj->rowid]['month']):0).'</td>';
    }
    $table.='</tr>';
   //Всього задач по направленнях та співробітниках
    $sql = "select `code` from llx_c_actioncomm
    where type in ('system','user')
    and code not in ('AC_GLOBAL', 'AC_CURRENT')";
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
    $totaltask = array();
    $totaltask = CalcOutStandingActions($Code, $totaltask, 0);
    $totaltask = CalcoutstandingActions($Code, $totaltask, 0);
    $totaltask = CalcFaktActions($Code, $totaltask, 0);
    $totaltask = CalcPercentExecActions($Code, $totaltask, 0);
    $table.='<tr style="font-weight: bold"><td class="middle_size" style="width:106px">Всього завдань</td><td class="middle_size" style="width:144px">по співробітниках і направленнях</td>';
    //% виконання запланованого по факту
    for($i=8; $i>=0; $i--){
        if($i == 8)
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(33):(34)).'px; text-align:center;">'.$totaltask['percent_month'].'</td>';
        elseif($i == 0)
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(32)).'px; text-align:center;">'.$totaltask['percent_'.$i].'</td>';
        else
             $table.='<td style="width: '.($conf->browser->name=='firefox'?(30):(31)).'px; text-align:center;">'.$totaltask['percent_'.$i].'</td>';
    }
    //минуле (факт)
    for($i=8; $i>=0; $i--){
        if($i == 0)
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(32)).'px; text-align:center;">'.$totaltask['fakt_today'].'</td>';
        elseif($i == 8)
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(33):(34)).'px; text-align:center;">'.$totaltask['fakt_month'].'</td>';
        else
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(30):(30)).'px; text-align:center;">' . $totaltask['fakt_day_m' . $i] . '</td>';
    }
    $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(51):(51)).'px">'.$totaltask["outstanding"].'</td>';
    //майбутнє (план)
    for($i=0; $i<9; $i++){
        if($i == 0)
            $table.='<td  style="text-align: center; width: '.($conf->browser->name=='firefox'?(31):(32)).'px">'.$totaltask['outstanding_today'].'</td>';
        elseif($i == 8)
            $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(33):(34)).'px">'.$totaltask['outstanding_month'].'</td>';
        else {
            $table .= '<td style="text-align: center; width: ' . ($conf->browser->name == 'firefox' ? (30) : (31)) . 'px">' . $totaltask['outstanding_day_pl' . $i] . '</td>';
        }
    }
    $table.='</tr>';
    $outstanding_actions = array();
    //Майбутні дії
    for($i=0; $i<9; $i++) {
        $sql = "select `llx_societe`.`region_id`, count(*) as iCount  from `llx_actioncomm`
        inner join
        (select id from `llx_c_actioncomm` where type in ('system','user') and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
        left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
        inner join `llx_actioncomm_resources` on `llx_actioncomm`.`id` =  `llx_actioncomm_resources`.`fk_actioncomm`
        where 1 ";
        $sql .= "and `llx_actioncomm_resources`.fk_element in (select rowid from llx_user where 1 and subdiv_id=".$user->subdiv_id." and `respon_id` in (".implode(',', $respon)."))";
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
                $sql .= " and datep2 between '" . date("Y-m-d") . "' and '" . date("Y") . "-" . $month . "-" . (date("d")) . "'";
        }
        $sql .=" and datea is null
        group by `llx_societe`.`region_id`";

        $res = $db->query($sql);
        while($res && $obj = $db->fetch_object($res)){
            if($i<8)
                $outstanding_actions[$obj->region_id.'_'.($i)]=$obj->iCount;
            else
                $outstanding_actions[$obj->region_id.'_month']=$obj->iCount;
        }
    }
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();
    //Прострочені дії
    $outstanding_actions = array();
    $sql = "select `llx_societe`.`region_id`, count(*) as iCount  from `llx_actioncomm`
    inner join
    (select id from `llx_c_actioncomm` where type in ('system','user') and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
    left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
    inner join `llx_actioncomm_resources` on `llx_actioncomm`.`id` =  `llx_actioncomm_resources`.`fk_actioncomm`
    where 1 ";
    $sql .= "and `llx_actioncomm_resources`.fk_element in (select rowid from llx_user where 1 and subdiv_id=".$user->subdiv_id." and `respon_id` in (".implode(',', $respon)."))";
    $sql .= " and datep2 < '".date("Y-m-d")."'";
    $sql .=" and datea is null
    group by `llx_societe`.`region_id`";
    $res = $db->query($sql);
    while($obj = $db->fetch_object($res)){
        $outstanding_actions[$obj->region_id]=$obj->iCount;
    }
    //Виконані дії
    $exec_actions = array();
    $percent_action = array();
    for($i=0; $i<9; $i++) {
        $sql = "select `llx_societe`.`region_id`, count(*) as iCount  from `llx_actioncomm`
        inner join
        (select id from `llx_c_actioncomm` where type in ('system','user') and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
        left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
        inner join `llx_actioncomm_resources` on `llx_actioncomm`.`id` =  `llx_actioncomm_resources`.`fk_actioncomm`
        where 1 ";
        $sql .= "and `llx_actioncomm_resources`.fk_element in (select rowid from llx_user where 1 and subdiv_id=".$user->subdiv_id." and `respon_id` in (".implode(',', $respon)."))";
//        if($i < 2) {
//            var_dump($sql);
//            die();
//        }

        if($i<8) {
            $query_date = date("Y-m-d", (time()-3600*24*$i+3600*24));
            if($i!=7)
                $sql .= " and datep2 between date_add('" . $query_date . "', interval -1 day) and '".$query_date . "'";
            else
                $sql .= " and datep2 between date_add('" . date("Y-m-d") . "', interval -7 day) and '".date("Y-m-d") . "'";
        }else {
            $month = date("m");
            if($month-1<10)
                $month = '0'.($month-1);
            else
                $month =($month-1);
                $sql .= " and datep2 between '" . date("Y") . "-" . $month . "-" . (date("d")) . "' and '" . date("Y-m-d") . "'";
        }

        $res = $db->query($sql." group by `llx_societe`.`region_id`");

        while($res && $obj = $db->fetch_object($res)){
            if($i<8) {
                $totaltask[$obj->region_id.'_'.$i] = $obj->iCount;
            }else
                $totaltask[$obj->region_id.'_month']=$obj->iCount;
        }

        $res = $db->query($sql." and datea is not null
                 group by `llx_societe`.`region_id`");

        while($res && $obj = $db->fetch_object($res)){
            if($i<8) {
                $exec_actions[$obj->region_id . '_' . ($i)] = $obj->iCount;
                if($totaltask[$obj->region_id.'_'.$i]!=0){
                   $percent_action[$obj->region_id.'_'.$i] = round($exec_actions[$obj->region_id.'_'.$i]*100/$totaltask[$obj->region_id.'_'.$i],0);
                }else{
                   $percent_action[$obj->region_id.'_'.$i] = '';
                }
            }else {
                $exec_actions[$obj->region_id . '_month'] = $obj->iCount;
                if($totaltask[$obj->region_id.'_month'] != 0){
                    $percent_action[$obj->region_id.'_month'] =  round($exec_actions[$obj->region_id.'_month']*100/$totaltask[$obj->region_id.'_month'],0);
                }else{
                   $percent_action[$obj->region_id.'_month'] = '';
                }
            }
        }
    }
    $sql = "select `regions`.rowid, llx_user.lastname, llx_user.firstname,`regions`.`name` from llx_user
        left join `llx_user_regions` on `llx_user_regions`.`fk_user` = llx_user.rowid
        left join `regions` on `regions`.`rowid` = `llx_user_regions`.`fk_id`
        where 1 ".(empty($user->subdiv_id)?"":" and subdiv_id=".$user->subdiv_id)."
        and llx_user.respon_id in (".implode(",", $respon).")";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);

//    echo '<pre>';
//    var_dump($outstanding_actions);
//    echo '</pre>';
//    die();
    $nom = 1;
    while($obj = $db->fetch_object($res)){
        $class=(fmod($nom++,2)==0?"impair":"pair");
        $table .= '<tr class = "'.$class.'"id = "'.$obj->rowid.'">
        <td>'.$obj->lastname.' '.mb_substr($obj->firstname, 0, 1, 'UTF-8').'.'.mb_substr($obj->firstname, mb_strrpos($obj->firstname, ' ','UTF-8')+1, 1, 'UTF-8').'.</td>
        <td>'.$obj->name.'</td>';
        //% виконання запланованого по факту
        for($i=8; $i>=0; $i--){
            if($i == 8)
                $table.='<td style="width: '.($conf->browser->name=='firefox'?(33):(34)).'px; text-align:center;">'.$percent_action[$obj->rowid.'_month'].'</td>';
            elseif($i == 0)
                $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(32)).'px; text-align:center;">'.$percent_action[$obj->rowid.'_'.$i].'</td>';
            else
                 $table.='<td style="width: '.($conf->browser->name=='firefox'?(30):(31)).'px; text-align:center;">'.$percent_action[$obj->rowid.'_'.$i].'</td>';
        }
        //минуле (факт)
        for($i=8; $i>=0; $i--){
            if($i == 0)
                $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(32)).'px; text-align:center;">'.$exec_actions[$obj->rowid.'_' . $i].'</td>';
            elseif($i == 8)
                $table.='<td style="width: '.($conf->browser->name=='firefox'?(33):(34)).'px; text-align:center;">'.$exec_actions[$obj->rowid.'_month'].'</td>';
            else
                $table.='<td style="width: '.($conf->browser->name=='firefox'?(30):(30)).'px; text-align:center;">' . $exec_actions[$obj->rowid.'_' . $i] . '</td>';
        }
        $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(51):(51)).'px">'.$outstanding_actions[$obj->rowid].'</td>';
        //майбутнє (план)
        for($i=0; $i<9; $i++){
            if($i == 0)
                $table.='<td  style="text-align: center; width: '.($conf->browser->name=='firefox'?(31):(32)).'px">'.$outstanding_actions[$obj->rowid.'_' . $i].'</td>';
            elseif($i == 8)
                $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(33):(34)).'px">'.$outstanding_actions[$obj->rowid.'_month'].'</td>';
            else {
                $table .= '<td style="text-align: center; width: ' . ($conf->browser->name == 'firefox' ? (30) : (31)) . 'px">' . $outstanding_actions[$obj->rowid.'_' . $i] . '</td>';
            }
        }
        $table .= '</tr>';
    }
    $table .= '</tbody>';
    return $table;
}