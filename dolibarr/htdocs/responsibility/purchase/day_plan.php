<?php

require $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';

//echo '<pre>';
//var_dump($_SERVER);
//echo '</pre>';
//die();

llxHeader("",$langs->trans('PlanOfDays'),"");
print_fiche_titre($langs->trans('PlanOfDays'));
$table = ShowTable();

//Підрахунок глобальних задач
$global_task = array();
$global_task = CalcOutStandingActions("'AC_GLOBAL'", $global_task, $user->id);
$global_task = CalcFutureActions("'AC_GLOBAL'", $global_task, $user->id);
$global_task = CalcFaktActions("'AC_GLOBAL'", $global_task, $user->id);
$global_task = CalcPercentExecActions("'AC_GLOBAL'", $global_task, $user->id);

//Підрахунок поточних задач
$current_task = array();
$current_task = CalcOutStandingActions("'AC_CURRENT'", $current_task, $user->id);
$current_task = CalcFutureActions("'AC_CURRENT'", $current_task, $user->id);
$current_task = CalcFaktActions("'AC_CURRENT'", $current_task, $user->id);
$current_task = CalcPercentExecActions("'AC_CURRENT'", $current_task, $user->id);

//Підрахунок по направленнях
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
$lineaction = array();
$lineaction = CalcOutStandingActions($Code, $lineaction, $user->id);
$lineaction = CalcFutureActions($Code, $lineaction, $user->id);
$lineaction = CalcFaktActions($Code, $lineaction, $user->id);
$lineaction = CalcPercentExecActions($Code, $lineaction, $user->id);

$bestvalue = array();
$bestuser_id = GetBestUserID();
$bestvalue = CalcOutStandingActions($Code, $bestvalue, $bestuser_id);
$bestvalue = CalcFutureActions($Code, $bestvalue, $bestuser_id);
$bestvalue = CalcFaktActions($Code, $bestvalue, $bestuser_id);
$bestvalue = CalcPercentExecActions($Code, $bestvalue, $bestuser_id);
//echo '<pre>';
//var_dump($bestvalue);
//echo '</pre>';
//die();

//Підсумок виконання задач
$total_percent =array();
for($i = 0; $i<9; $i++){
    $count = 0;
    $sum = 0;
    if($i<8){
        if(strlen($global_task["percent_".$i])>0){
            $count++;
            $sum.=$global_task["percent_".$i];
        }
        if(strlen($current_task["percent_".$i])>0){
            $count++;
            $sum+=$current_task["percent_".$i];
        }
        if(strlen($lineaction["percent_".$i])>0){
            $count++;
            $sum+=$lineaction["percent_".$i];
        }
        if($count != 0)
            $total_percent["percent_".$i] = round($sum/$count);
    }else{
        if(strlen($global_task["percent_month"])>0){
            $count++;
            $sum+=$global_task["percent_month"];
        }
        if(strlen($current_task["percent_month"])>0){
            $count++;
            $sum+=$current_task["percent_month"];
        }
        if(strlen($lineaction["percent_month"])>0){
            $count++;
            $sum+=$lineaction["percent_month"];
        }
        if($count != 0) {
            $total_percent["percent_month"] = round($sum / $count,0);
        }
    }
}
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/responsibility/sale/day_plan.html';

//print '</br>';
//print'<div style="float: left">test</div>';
llxFooter();

exit();
function GetBestUserID(){
    global $db, $user;
    $sql = "select `llx_actioncomm_resources`.fk_element id_usr, count(*) iCount
        from `llx_actioncomm`
        inner join (select id from `llx_c_actioncomm` where code in('AC_TEL','AC_FAX','AC_EMAIL','AC_RDV','AC_INT','AC_OTH','AC_DEP') and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
        inner join `llx_actioncomm_resources` on `llx_actioncomm`.`id` =  `llx_actioncomm_resources`.`fk_actioncomm`
        where `llx_actioncomm_resources`.fk_element in (select rowid from llx_user where  `respon_id` = ".$user->respon_id.")
        group by `llx_actioncomm_resources`.fk_element
        order by iCount desc
        limit 1";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    if($db->num_rows($res)>0) {
        $obj = $db->fetch_object($res);
        return $obj->id_usr;
    }else
        return 0;

}
function CalcPercentExecActions($actioncode, $array, $id_usr){
    global $db;
    $totaltask = array();
    $exectask = array();
    for($i = 0; $i<9; $i++){
        $sql = "select count(*) as iCount  from `llx_actioncomm`
        inner join
        (select id from `llx_c_actioncomm` where code in(".$actioncode.") and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
        left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
        inner join `llx_actioncomm_resources` on `llx_actioncomm`.`id` =  `llx_actioncomm_resources`.`fk_actioncomm`
        where 1 ";
        $sql .= " and `llx_actioncomm_resources`.fk_element = ".$id_usr;
        if($i<8) {
            $query_date = date("Y-m-d", (time()+3600*24*(-$i)));
            if($i!=7)
                $sql .= " and datep2 between '".$query_date . "' and date_add('" . $query_date . "', interval 1 day)";
            else
                $sql .= " and datep2 between date_add('" . date("Y-m-d") . "', interval -7 day) and '".date("Y-m-d") . "'";
        }else {
                $sql .= " and datep2 between date_add('" . date("Y-m-d") . "', interval -31 day) and '" . date("Y-m-d") . "'";
        }
//        if($i == 4)
//            die($sql);
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
function CalcFaktActions($actioncode, $array, $id_usr){
    global $db, $user;
    //Минулі виконані дії
    for($i=0; $i<9; $i++) {
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

function CalcFutureActions($actioncode, $array, $id_usr){
    global $db, $user;
    //Майбутні дії
    for($i=0; $i<9; $i++) {
        $sql = "select count(*) as iCount  from `llx_actioncomm`
        inner join
        (select id from `llx_c_actioncomm` where code in(".$actioncode.") and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
        left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
        where 1";
        if($actioncode == "'AC_GLOBAL'" || $actioncode == "'AC_CURRENT'" || $user->login !="admin")
            $sql .=" and fk_user_author = ".$id_usr;
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
    }
    return $array;
}
function CalcOutStandingActions($actioncode, $array, $id_usr){
    global $db, $user;
    $sql = "select count(*) as iCount  from `llx_actioncomm`
    inner join
    (select id from `llx_c_actioncomm` where code in(".$actioncode.") and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
    left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
    where 1";
        if($actioncode == "'AC_GLOBAL'" || $actioncode == "'AC_CURRENT'" || $user->login !="admin")
            $sql .=" and fk_user_author = ".$id_usr;
    $sql .= " and datep2 < '".date("Y-m-d")."'";
    $sql .=" and datea is null";
//    if($actioncode == "'AC_GLOBAL'" || $actioncode == "'AC_CURRENT'"){}
//        else
//            die($sql);
    $res = $db->query($sql);
    if($db->num_rows($res)) {
        $obj = $db->fetch_object($res);
        $array['outstanding'] = $obj->iCount;
    }else
        $array['outstanding'] = '';
    return $array;
}
function ShowTable(){
    global $db, $user, $conf;
    $future_actions = array();
    //Майбутні дії
    for($i=0; $i<9; $i++) {
        $sql = "select `llx_societe`.`region_id`, count(*) as iCount  from `llx_actioncomm`
        inner join
        (select id from `llx_c_actioncomm` where type in ('system','user') and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
        left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
        where 1 ";
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
                $future_actions[$obj->region_id.'_'.($i)]=$obj->iCount;
            else
                $future_actions[$obj->region_id.'_month']=$obj->iCount;
        }
    }
    
    //Прострочені дії
    $outstanding_actions = array();
    $sql = "select `llx_societe`.`region_id`, count(*) as iCount  from `llx_actioncomm`
    inner join
    (select id from `llx_c_actioncomm` where type in ('system','user') and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
    left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
    where 1";
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
//        if($i < 2) {
//            var_dump($sql);
//            die();
//        }
        if($user->login != "admin"){
            $sql .= "and `llx_actioncomm_resources`.`fk_element` = ".$user->id;
        }
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


//    echo '<pre>';
//    var_dump($percent_action);
//    echo '</pre>';
//    die(111);
    $sql = 'select distinct `regions`.rowid, `regions`.name regions_name, states.name states_name
    from `regions`
    inner join (select fk_id from `llx_user_regions`';
    if(!$user->admin)
        $sql .='where fk_user = '.$user->id.' ';
    else
        $sql .='where 1 ';
    $sql .='and llx_user_regions.active = 1) as active_regions on active_regions.fk_id = `regions`.rowid
    left join states on `regions`.`state_id` = `states`.rowid
    where `regions`.active = 1
    order by states_name, `regions`.name';
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);

    $table = '<tbody id="reference_body">';
    $nom=0;

    while($obj = $db->fetch_object($res)){
        $class=(fmod($nom++,2)==0?"impair":"pair");
        $table.='<tr id = "'.$obj->rowid.'" class="'.$class.'">
            <td class="middle_size" style="width:106px">'.$obj->states_name.'</td>
            <td class="middle_size" style="width:146px">'.str_replace('-', '- ',$obj->regions_name).'</td>';
            //% виконання запланованого по факту

            for($i=8; $i>=0; $i--){
                if($i == 8)
                    $table.='<td style="width: '.($conf->browser->name=='firefox'?(32):(33)).'px; text-align:center;">'.$percent_action[$obj->rowid.'_month'].'</td>';
                else
                    $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(31)).'px; text-align:center;">'.$percent_action[$obj->rowid.'_'.$i].'</td>';
            }
            //минуле (факт)
            for($i=8; $i>=0; $i--){
                if($i == 0)
                    $table.='<td style="width: '.($conf->browser->name=='firefox'?(34):(35)).'px; text-align:center;">'.$exec_actions[$obj->rowid.'_'.$i].'</td>';
                elseif($i == 8)
                    $table.='<td style="width: '.($conf->browser->name=='firefox'?(32):(33)).'px; text-align:center;">'.$exec_actions[$obj->rowid.'_month'].'</td>';
                else
                    $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(31)).'px; text-align:center;">' . $exec_actions[$obj->rowid . '_' . $i] . '</td>';
            }
            //Прострочено сьогодні
            $id = "'#outstand".$obj->rowid."'";
            $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(51):(51)).'px"> <a  id = "outstand'.$obj->rowid.'" onclick="ShowTask($('.$id.'));" class="link">'.$outstanding_actions[$obj->rowid].'</a></td>';
            //майбутнє (план)
            for($i=0; $i<9; $i++){
                if($i == 0)
                    $table.='<td  style="text-align: center; width: '.($conf->browser->name=='firefox'?(32):(33)).'px"><a href="/dolibarr/htdocs/hourly_plan.php?idmenu=10420&mainmenu=hourly_plan&leftmenu=&date='.date("Y-m-d").'">'.$future_actions[$obj->rowid.'_'.$i].'</a></td>';
                elseif($i == 8)
                    $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(32):(34)).'px">'.$future_actions[$obj->rowid.'_month'].'</td>';
                else {
                    $table .= '<td style="text-align: center; width: ' . ($conf->browser->name == 'firefox' ? (31) : (31)) . 'px"><a href="/dolibarr/htdocs/hourly_plan.php?idmenu=10420&mainmenu=hourly_plan&leftmenu=&date='.date("Y-m-d", (time()+3600*24*$i)).'">' . $future_actions[$obj->rowid . '_' . $i] . '</a></td>';
                }
            }
        $table.='</tr>';
    }
    $table .= '</tbody>';
    return $table;
}