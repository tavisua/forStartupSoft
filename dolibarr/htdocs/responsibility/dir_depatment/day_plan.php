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
llxFooter();

exit();
function GetBestUserID($Code="'AC_TEL','AC_FAX','AC_EMAIL','AC_RDV','AC_INT','AC_OTH','AC_DEP'", $responming=''){
    global $db, $user;
    $sql = "select `llx_actioncomm_resources`.fk_element id_usr, count(*) iCount
        from `llx_actioncomm`
        inner join (select id from `llx_c_actioncomm` where code in(".$Code.") and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
        inner join `llx_actioncomm_resources` on `llx_actioncomm`.`id` =  `llx_actioncomm_resources`.`fk_actioncomm`
        where `llx_actioncomm_resources`.fk_element in (select rowid from llx_user where  `subdiv_id` = ".$user->subdiv_id.(empty($responding)?"":" and respon_id in(".$responding.")").")
        group by `llx_actioncomm_resources`.fk_element
        order by iCount desc
        limit 1";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    return $obj->id_usr;

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
        inner join `llx_actioncomm_resources` on `llx_actioncomm`.`id` =  `llx_actioncomm_resources`.`fk_actioncomm`
        where 1 ";
        if(($actioncode == "'AC_GLOBAL'" || $actioncode == "'AC_CURRENT'" || $user->login !="admin")&& $id_usr != 0) {
            $sql .= " and `llx_actioncomm_resources`.fk_element = " . $id_usr;
        }else
            $sql .=" and `llx_actioncomm_resources`.fk_element in (select rowid from llx_user where `subdiv_id` = ".$user->subdiv_id.(empty($responding)?"":" and respon_id in(".$responding.")").")";
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
        else
            $sql .=" and `llx_actioncomm_resources`.fk_element in (select rowid from llx_user where `subdiv_id` = ".$user->subdiv_id.(empty($responding)?"":" and respon_id in(".$responding.")").")";
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

function CalcFutureActions($actioncode, $array, $id_usr=0, $responding=''){
    global $db, $user;
    //Майбутні дії
    for($i=0; $i<9; $i++) {
        $sql = "select count(*) as iCount  from `llx_actioncomm`
        inner join
        (select id from `llx_c_actioncomm` where code in(".$actioncode.") and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
        left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
        inner join `llx_actioncomm_resources` on `llx_actioncomm`.`id` =  `llx_actioncomm_resources`.`fk_actioncomm`
        where 1";
        if(($actioncode == "'AC_GLOBAL'" || $actioncode == "'AC_CURRENT'" || $user->login !="admin")&& $id_usr != 0)
            $sql .=" and `llx_actioncomm_resources`.fk_element = ".$id_usr;
        else
            $sql .=" and `llx_actioncomm_resources`.fk_element in (select rowid from llx_user where `subdiv_id` = ".$user->subdiv_id.(empty($responding)?"":" and respon_id in(".$responding.")").")";
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
function CalcOutStandingActions($actioncode, $array, $id_usr=0, $responding=''){
    global $db, $user;
    $sql = "select count(*) as iCount  from `llx_actioncomm`
    inner join
    (select id from `llx_c_actioncomm` where code in(".$actioncode.") and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
    left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
    inner join `llx_actioncomm_resources` on `llx_actioncomm`.`id` =  `llx_actioncomm_resources`.`fk_actioncomm`
    where 1";
        if(($actioncode == "'AC_GLOBAL'" || $actioncode == "'AC_CURRENT'" || $user->login !="admin")&& $id_usr != 0)
            $sql .=" and fk_user_author = ".$id_usr;
        else
            $sql .=" and `llx_actioncomm_resources`.fk_element in (select rowid from llx_user where `subdiv_id` = ".$user->subdiv_id.(empty($responding)?"":" and respon_id in(".$responding.")").")";
    $sql .= " and datep2 < '".date("Y-m-d")."'";
    $sql .=" and datea is null";
//    if($actioncode == "'AC_GLOBAL'" || $actioncode == "'AC_CURRENT'"){}
//            die($sql);
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    if($res) {
        $obj = $db->fetch_object($res);
        $array['outstanding'] = $obj->iCount;
    }else
        $array['outstanding'] = '';
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
//
//    $sql = 'select distinct `regions`.rowid, `regions`.name regions_name, states.name states_name
//    from `regions`
//    inner join (select fk_id from `llx_user_regions`';
//    if(!$user->admin)
//        $sql .='where fk_user = '.$user->id.' ';
//    else
//        $sql .='where 1 ';
//    $sql .='and llx_user_regions.active = 1) as active_regions on active_regions.fk_id = `regions`.rowid
//    left join states on `regions`.`state_id` = `states`.rowid
//    where `regions`.active = 1
//    order by states_name, `regions`.name';
//    $res = $db->query($sql);
//    if(!$res)
//        dol_print_error($db);

    $table = '<tbody id="reference_body">';
    $nom=0;
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
    $bestvalue = array();
    $bestuser_id = GetBestUserID();
    $bestvalue = CalcOutStandingActions($Code, $bestvalue, $bestuser_id);
    $bestvalue = CalcFutureActions($Code, $bestvalue, $bestuser_id);
    $bestvalue = CalcFaktActions($Code, $bestvalue, $bestuser_id);
    $bestvalue = CalcPercentExecActions($Code, $bestvalue, $bestuser_id);


    $table.='<tr class="bestvalue"><td class="middle_size" style="width:106px">Департамент '.$subdivision.'</td><td class="middle_size" style="width:144px">Всього по найкращому</td>';
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
        if($i == 0)
            $table.='<td  style="text-align: center; width: '.($conf->browser->name=='firefox'?(31):(32)).'px"><a href="/dolibarr/htdocs/hourly_plan.php?idmenu=10420&mainmenu=hourly_plan&leftmenu=&date='.date("Y-m-d").'">'.$bestvalue['future_today'].'</a></td>';
        elseif($i == 8)
            $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(33):(34)).'px">'.$bestvalue['future_month'].'</td>';
        else {
            $table .= '<td style="text-align: center; width: ' . ($conf->browser->name == 'firefox' ? (30) : (31)) . 'px"><a href="/dolibarr/htdocs/hourly_plan.php?idmenu=10420&mainmenu=hourly_plan&leftmenu=&date='.date("Y-m-d", (time()+3600*24*$i)).'">' . $bestvalue['future_day_pl' . $i] . '</a></td>';
        }
    }
    $table.='</tr>';

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
    $totaltask = array();
    $totaltask = CalcOutStandingActions($Code, $totaltask, 0);
    $totaltask = CalcFutureActions($Code, $totaltask, 0);
    $totaltask = CalcFaktActions($Code, $totaltask, 0);
    $totaltask = CalcPercentExecActions($Code, $totaltask, 0);
    $table.='<tr style="font-weight: bold"><td class="middle_size" style="width:106px">Департамент '.$subdivision.'</td><td class="middle_size" style="width:144px">Всього задач</td>';
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
            $table.='<td  style="text-align: center; width: '.($conf->browser->name=='firefox'?(31):(32)).'px">'.$totaltask['future_today'].'</td>';
        elseif($i == 8)
            $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(33):(34)).'px">'.$totaltask['future_month'].'</td>';
        else {
            $table .= '<td style="text-align: center; width: ' . ($conf->browser->name == 'firefox' ? (30) : (31)) . 'px">' . $totaltask['future_day_pl' . $i] . '</td>';
        }
    }
    $table.='</tr>';

    $sql = "select rowid, lastname from llx_user where  `subdiv_id` = ".$user->subdiv_id;
    $userlist = $db->query($sql);
    if(!$userlist)
        dol_print_error($db);
    $nom = 0;
    while($obj = $db->fetch_object($userlist)){
        $class=(fmod($nom++,2)==0?"impair":"pair");
        $table.='<tr id = "'.$obj->rowid.'" class="'.$class.'">
            <td class="middle_size" style="width:106px">'.$obj->lastname.'</td>
            <td class="middle_size" style="width:146px">Всього задач</td>';
            $totaltask = array();
            $totaltask = CalcOutStandingActions($Code, $totaltask, $obj->rowid);
            $totaltask = CalcFutureActions($Code, $totaltask, $obj->rowid);
            $totaltask = CalcFaktActions($Code, $totaltask, $obj->rowid);
            $totaltask = CalcPercentExecActions($Code, $totaltask, $obj->rowid);
//            echo '<pre>';
//            var_dump($totaltask);
//            echo '</pre>';
//            die($Code);
            //% виконання запланованого по факту

            for($i=8; $i>=0; $i--){
                if($i == 8)
                    $table.='<td style="width: '.($conf->browser->name=='firefox'?(32):(33)).'px; text-align:center;">'.$totaltask['percent_month'].'</td>';
                else
                    $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(31)).'px; text-align:center;">'.$totaltask['percent_'.$i].'</td>';
            }
            //минуле (факт)
            for($i=8; $i>=0; $i--){
                if($i == 0)
                    $table.='<td style="width: '.($conf->browser->name=='firefox'?(34):(35)).'px; text-align:center;">'.$totaltask['fakt_day_m'.$i].'</td>';
                elseif($i == 8)
                    $table.='<td style="width: '.($conf->browser->name=='firefox'?(32):(33)).'px; text-align:center;">'.$totaltask['fakt_month'].'</td>';
                else
                    $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(31)).'px; text-align:center;">' . $totaltask['fakt_day_m'.$i] . '</td>';
            }
//            //Прострочено сьогодні
            $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(51):(51)).'px"> '.$totaltask['outstanding'].'</td>';
            //майбутнє (план)
            for($i=0; $i<9; $i++){
                if($i == 0)
                    $table.='<td  style="text-align: center; width: '.($conf->browser->name=='firefox'?(32):(33)).'px">'.$totaltask['future_today'].'</td>';
                elseif($i == 8)
                    $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(32):(34)).'px">'.$totaltask['future_month'].'</td>';
                else {
                    $table .= '<td style="text-align: center; width: ' . ($conf->browser->name == 'firefox' ? (31) : (31)) . 'px">' . $totaltask[ 'future_day_pl' . $i] . '</a></td>';
                }
            }
            unset($totaltask);
        $table.='</tr>';
    }
    $Code = "'AC_GLOBAL'";
    $globaltask = array();

    $globaltask = CalcOutStandingActions($Code, $globaltask, 0);
    $globaltask = CalcFutureActions($Code, $globaltask, 0);
    $globaltask = CalcFaktActions($Code, $globaltask, 0);
    $globaltask = CalcPercentExecActions($Code, $globaltask, 0);

    $table.='<tr style="font-weight: bold"><td class="middle_size" style="width:106px">Департамент '.$subdivision.'</td><td class="middle_size" style="width:144px">Всього глобальні задачі (ТОПЗ)</td>';
    //% виконання запланованого по факту
    for($i=8; $i>=0; $i--){
        if($i == 8)
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(33):(34)).'px; text-align:center;">'.$globaltask['percent_month'].'</td>';
        elseif($i == 0)
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(32)).'px; text-align:center;">'.$globaltask['percent_'.$i].'</td>';
        else
             $table.='<td style="width: '.($conf->browser->name=='firefox'?(30):(31)).'px; text-align:center;">'.$globaltask['percent_'.$i].'</td>';
    }
    //минуле (факт)
    for($i=8; $i>=0; $i--){
        if($i == 0)
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(32)).'px; text-align:center;">'.$globaltask['fakt_today'].'</td>';
        elseif($i == 8)
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(33):(34)).'px; text-align:center;">'.$globaltask['fakt_month'].'</td>';
        else
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(30):(30)).'px; text-align:center;">' . $globaltask['fakt_day_m' . $i] . '</td>';
    }
    $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(51):(51)).'px">'.$globaltask["outstanding"].'</td>';
    //майбутнє (план)
    for($i=0; $i<9; $i++){
        if($i == 0)
            $table.='<td  style="text-align: center; width: '.($conf->browser->name=='firefox'?(31):(32)).'px">'.$globaltask['future_today'].'</td>';
        elseif($i == 8)
            $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(33):(34)).'px">'.$globaltask['future_month'].'</td>';
        else {
            $table .= '<td style="text-align: center; width: ' . ($conf->browser->name == 'firefox' ? (30) : (31)) . 'px">' . $globaltask['future_day_pl' . $i] . '</td>';
        }
    }
    $table.='</tr>';
    mysqli_data_seek($userlist, 0);
    while($obj = $db->fetch_object($userlist)){
        $class=(fmod($nom++,2)==0?"impair":"pair");
        $table.='<tr id = "'.$obj->rowid.'" class="'.$class.'">
            <td class="middle_size" style="width:106px">'.$obj->lastname.'</td>
            <td class="middle_size" style="width:146px">Всього глобальні задачі (ТОПЗ)</td>';
            $totaltask = array();
            $totaltask = CalcOutStandingActions($Code, $totaltask, $obj->rowid);
            $totaltask = CalcFutureActions($Code, $totaltask, $obj->rowid);
            $totaltask = CalcFaktActions($Code, $totaltask, $obj->rowid);
            $totaltask = CalcPercentExecActions($Code, $totaltask, $obj->rowid);
//            echo '<pre>';
//            var_dump($totaltask);
//            echo '</pre>';
//            die($Code);
            //% виконання запланованого по факту

            for($i=8; $i>=0; $i--){
                if($i == 8)
                    $table.='<td style="width: '.($conf->browser->name=='firefox'?(32):(33)).'px; text-align:center;">'.$totaltask['percent_month'].'</td>';
                else
                    $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(31)).'px; text-align:center;">'.$totaltask['percent_'.$i].'</td>';
            }
            //минуле (факт)
            for($i=8; $i>=0; $i--){
                if($i == 0)
                    $table.='<td style="width: '.($conf->browser->name=='firefox'?(34):(35)).'px; text-align:center;">'.$totaltask['fakt_day_m'.$i].'</td>';
                elseif($i == 8)
                    $table.='<td style="width: '.($conf->browser->name=='firefox'?(32):(33)).'px; text-align:center;">'.$totaltask['fakt_month'].'</td>';
                else
                    $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(31)).'px; text-align:center;">' . $totaltask['fakt_day_m'.$i] . '</td>';
            }
//            //Прострочено сьогодні
            $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(51):(51)).'px"> '.$totaltask['outstanding'].'</td>';
            //майбутнє (план)
            for($i=0; $i<9; $i++){
                if($i == 0)
                    $table.='<td  style="text-align: center; width: '.($conf->browser->name=='firefox'?(32):(33)).'px">'.$totaltask['future_today'].'</td>';
                elseif($i == 8)
                    $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(32):(34)).'px">'.$totaltask['future_month'].'</td>';
                else {
                    $table .= '<td style="text-align: center; width: ' . ($conf->browser->name == 'firefox' ? (31) : (31)) . 'px">' . $totaltask[ 'future_day_pl' . $i] . '</a></td>';
                }
            }
            unset($totaltask);
        $table.='</tr>';
    }
    //Поточні задачі
    $Code = "'AC_CURRENT'";
    $carenttask = array();

    $carenttask = CalcOutStandingActions($Code, $carenttask, 0);
    $carenttask = CalcFutureActions($Code, $carenttask, 0);
    $carenttask = CalcFaktActions($Code, $carenttask, 0);
    $carenttask = CalcPercentExecActions($Code, $carenttask, 0);

    $table.='<tr style="font-weight: bold"><td class="middle_size" style="width:106px">Департамент '.$subdivision.'</td><td class="middle_size" style="width:144px">Всього поточних задач</td>';
    //% виконання запланованого по факту
    for($i=8; $i>=0; $i--){
        if($i == 8)
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(33):(34)).'px; text-align:center;">'.$carenttask['percent_month'].'</td>';
        elseif($i == 0)
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(32)).'px; text-align:center;">'.$carenttask['percent_'.$i].'</td>';
        else
             $table.='<td style="width: '.($conf->browser->name=='firefox'?(30):(31)).'px; text-align:center;">'.$carenttask['percent_'.$i].'</td>';
    }
    //минуле (факт)
    for($i=8; $i>=0; $i--){
        if($i == 0)
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(32)).'px; text-align:center;">'.$carenttask['fakt_today'].'</td>';
        elseif($i == 8)
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(33):(34)).'px; text-align:center;">'.$carenttask['fakt_month'].'</td>';
        else
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(30):(30)).'px; text-align:center;">' . $carenttask['fakt_day_m' . $i] . '</td>';
    }
    $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(51):(51)).'px">'.$carenttask["outstanding"].'</td>';
    //майбутнє (план)
    for($i=0; $i<9; $i++){
        if($i == 0)
            $table.='<td  style="text-align: center; width: '.($conf->browser->name=='firefox'?(31):(32)).'px">'.$carenttask['future_today'].'</td>';
        elseif($i == 8)
            $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(33):(34)).'px">'.$carenttask['future_month'].'</td>';
        else {
            $table .= '<td style="text-align: center; width: ' . ($conf->browser->name == 'firefox' ? (30) : (31)) . 'px">' . $carenttask['future_day_pl' . $i] . '</td>';
        }
    }
    $table.='</tr>';
    mysqli_data_seek($userlist, 0);
    while($obj = $db->fetch_object($userlist)){
        $class=(fmod($nom++,2)==0?"impair":"pair");
        $table.='<tr id = "'.$obj->rowid.'" class="'.$class.'">
            <td class="middle_size" style="width:106px">'.$obj->lastname.'</td>
            <td class="middle_size" style="width:146px">Всього поточних задач</td>';
            $task = array();
            $task = CalcOutStandingActions($Code, $task, $obj->rowid);
            $task = CalcFutureActions($Code, $task, $obj->rowid);
            $task = CalcFaktActions($Code, $task, $obj->rowid);
            $task = CalcPercentExecActions($Code, $task, $obj->rowid);
//            echo '<pre>';
//            var_dump($task);
//            echo '</pre>';
//            die($Code);
            //% виконання запланованого по факту

            for($i=8; $i>=0; $i--){
                if($i == 8)
                    $table.='<td style="width: '.($conf->browser->name=='firefox'?(32):(33)).'px; text-align:center;">'.$task['percent_month'].'</td>';
                else
                    $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(31)).'px; text-align:center;">'.$task['percent_'.$i].'</td>';
            }
            //минуле (факт)
            for($i=8; $i>=0; $i--){
                if($i == 0)
                    $table.='<td style="width: '.($conf->browser->name=='firefox'?(34):(35)).'px; text-align:center;">'.$task['fakt_day_m'.$i].'</td>';
                elseif($i == 8)
                    $table.='<td style="width: '.($conf->browser->name=='firefox'?(32):(33)).'px; text-align:center;">'.$task['fakt_month'].'</td>';
                else
                    $table.='<td style="width: '.($conf->browser->name=='firefox'?(31):(31)).'px; text-align:center;">' . $task['fakt_day_m'.$i] . '</td>';
            }
//            //Прострочено сьогодні
            $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(51):(51)).'px"> '.$task['outstanding'].'</td>';
            //майбутнє (план)
            for($i=0; $i<9; $i++){
                if($i == 0)
                    $table.='<td  style="text-align: center; width: '.($conf->browser->name=='firefox'?(32):(33)).'px">'.$task['future_today'].'</td>';
                elseif($i == 8)
                    $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(32):(34)).'px">'.$task['future_month'].'</td>';
                else {
                    $table .= '<td style="text-align: center; width: ' . ($conf->browser->name == 'firefox' ? (31) : (31)) . 'px">' . $task[ 'future_day_pl' . $i] . '</a></td>';
                }
            }
            unset($task);
        $table.='</tr>';
    }
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

    $bestvalue = CalcOutStandingActions($Code, $bestvalue, $bestuser_id);
    $bestvalue = CalcFutureActions($Code, $bestvalue, $bestuser_id);
    $bestvalue = CalcFaktActions($Code, $bestvalue, $bestuser_id);
    $bestvalue = CalcPercentExecActions($Code, $bestvalue, $bestuser_id);
//echo '<pre>';
//var_dump($bestvalue);
//echo '</pre>';
//die();

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
        if($i == 0)
            $table.='<td  style="text-align: center; width: '.($conf->browser->name=='firefox'?(31):(32)).'px"><a href="/dolibarr/htdocs/hourly_plan.php?idmenu=10420&mainmenu=hourly_plan&leftmenu=&date='.date("Y-m-d").'">'.$bestvalue['future_today'].'</a></td>';
        elseif($i == 8)
            $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(33):(34)).'px">'.$bestvalue['future_month'].'</td>';
        else {
            $table .= '<td style="text-align: center; width: ' . ($conf->browser->name == 'firefox' ? (30) : (31)) . 'px"><a href="/dolibarr/htdocs/hourly_plan.php?idmenu=10420&mainmenu=hourly_plan&leftmenu=&date='.date("Y-m-d", (time()+3600*24*$i)).'">' . $bestvalue['future_day_pl' . $i] . '</a></td>';
        }
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
    $totaltask = CalcFutureActions($Code, $totaltask, 0);
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
            $table.='<td  style="text-align: center; width: '.($conf->browser->name=='firefox'?(31):(32)).'px">'.$totaltask['future_today'].'</td>';
        elseif($i == 8)
            $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(33):(34)).'px">'.$totaltask['future_month'].'</td>';
        else {
            $table .= '<td style="text-align: center; width: ' . ($conf->browser->name == 'firefox' ? (30) : (31)) . 'px">' . $totaltask['future_day_pl' . $i] . '</td>';
        }
    }
    $table.='</tr>';
    $future_actions = array();
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
                $future_actions[$obj->region_id.'_'.($i)]=$obj->iCount;
            else
                $future_actions[$obj->region_id.'_month']=$obj->iCount;
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
    $sql = "select `regions`.rowid, llx_user.lastname,`regions`.`name` from llx_user
        left join `llx_user_regions` on `llx_user_regions`.`fk_user` = llx_user.rowid
        left join `regions` on `regions`.`rowid` = `llx_user_regions`.`fk_id`
        where subdiv_id=".$user->subdiv_id."
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
        <td>'.$obj->lastname.'</td>
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
                $table.='<td  style="text-align: center; width: '.($conf->browser->name=='firefox'?(31):(32)).'px">'.$future_actions[$obj->rowid.'_' . $i].'</td>';
            elseif($i == 8)
                $table.='<td style="text-align: center; width: '.($conf->browser->name=='firefox'?(33):(34)).'px">'.$future_actions[$obj->rowid.'_month'].'</td>';
            else {
                $table .= '<td style="text-align: center; width: ' . ($conf->browser->name == 'firefox' ? (30) : (31)) . 'px">' . $future_actions[$obj->rowid.'_' . $i] . '</td>';
            }
        }
        $table .= '</tr>';
    }
    $table .= '</tbody>';
    return $table;
}