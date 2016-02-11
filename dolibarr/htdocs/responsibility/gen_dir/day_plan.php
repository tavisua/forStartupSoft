<?php

require $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';

if($_REQUEST['action'] == 'gettask'){
//    echo '<pre>';
//    var_dump($_REQUEST);
//    echo '</pre>';
//    die();
    echo GetTask($_REQUEST['code']=='all'?'':$_REQUEST['code'], $_REQUEST['classname'], $_REQUEST['responding'], $_REQUEST['subdiv_id']);
    exit();
}


llxHeader("",$langs->trans('PlanOfDays'),"");
print_fiche_titre($langs->trans('PlanOfDays'));

$sql = 'select name from subdivision where rowid = '.(empty($user->subdiv_id)?0:$user->subdiv_id);
$res = $db->query($sql);
if(!$res)
    dol_print_error($db);
$obj = $db->fetch_object($res);
$subdivision = $obj->name;

$table = ShowTable();
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/responsibility/gen_dir/day_plan.html';

//print '</br>';
//print'<div style="float: left">test</div>';
llxFooter();

exit();
function GetUserTask($Code = '', $classname='', $id_usr){

}
function GetTask($Code = '', $classname='', $responding = '', $subdiv_id=''){
    global $db,$conf;
    $nom = 0;
    $table='';
    $Postfix='';

    switch($Code){
        case '':{
            $kindtask = ' задач';
            $Postfix = 'AllTask';
        }break;
        case "'AC_GLOBAL'":{
            $kindtask = 'глобальних задач';
            $Postfix = 'GlobalTask';
        }break;
        case "'AC_CURRENT'":{
            $kindtask = 'поточних задач';
            $Postfix = 'CurrentTask';
        }break;
        case "'AC_TEL','AC_FAX','AC_EMAIL','AC_RDV','AC_INT','AC_OTH','AC_DEP'":{
            $kindtask = 'задач по направленнях';
            $Postfix = 'LineActiveTask';
        }break;
    }
    $Prefix = '';
    if(!empty($responding)) {
        $sql = "select llx_user.rowid, CONCAT(llx_user.lastname, ' ', `subdivision`.`name`)name
            from llx_user
            left join `subdivision` on `subdivision`.`rowid` = `llx_user`.`subdiv_id`
            where `respon_id` = (" . $responding . ")";
        $Prefix="user_";
    }elseif(empty($subdiv_id)){
        $sql = "select rowid, name from subdivision where active = 1 order by name";
        $Prefix="subdiv_";
    }elseif(!empty($subdiv_id)){
        $sql = "select llx_user.rowid, llx_user.lastname, llx_user.firstname
            from llx_user
            left join `subdivision` on `subdivision`.`rowid` = `llx_user`.`subdiv_id`
            where `llx_user`.`subdiv_id` in (" . $subdiv_id . ")";
        $Prefix="user_";
    }
    $userlist = $db->query($sql);
    if(!$userlist)
        dol_print_error($db);
    if(empty($Code)){
        //Підрахунок по направленнях
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
    }
    while($obj = $db->fetch_object($userlist)) {
        $class = (fmod($nom++, 2) == 0 ? "impair" : "pair");
        $id = $Prefix . $obj->rowid . $Postfix;
        $name = $obj->name;
        if(!empty($obj->lastname)&&!empty($obj->firstname)){
            $name = $obj->lastname.' '.$obj->firstname;
        }
        $table .= '<tr id = "tr' . $id . '" class="' . $class . ' ' . $classname .($Prefix == 'user_'?" userlist":"").'">
            <td class="middle_size" style="width:106px">' . $name . '</td>
            <td class="middle_size" style="width:146px">Всього ' . $kindtask . '</td>';
        if ($Prefix == 'user_'){
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(30):(31)).'px; text-align:center;"></td>';
            }else
            $table.='<td style="width: '.($conf->browser->name=='firefox'?(30):(31)).'px; text-align:center;"><button id="'.$id.'" style="width:25px" onclick="ShowAllTask('."'".$id."'".');"><img id="img'.$id.'" src="/dolibarr/htdocs/theme/eldy/img/1downarrow.png"></button></td>';
            $totaltask = array();
            if(!empty($responding)) {
                $totaltask = CalcOutStandingActions($Code, $totaltask, $obj->rowid, '', '');
                $totaltask = CalcFutureActions($Code, $totaltask, $obj->rowid, '', '');
                $totaltask = CalcFaktActions($Code, $totaltask, $obj->rowid, '', '');
                $totaltask = CalcPercentExecActions($Code, $totaltask, $obj->rowid, '', '');
            }elseif(empty($subdiv_id)){
                $totaltask = CalcOutStandingActions($Code, $totaltask, 0, '', $obj->rowid);
                $totaltask = CalcFutureActions($Code, $totaltask, 0, '', $obj->rowid);
                $totaltask = CalcFaktActions($Code, $totaltask, 0, '', $obj->rowid);
                $totaltask = CalcPercentExecActions($Code, $totaltask, 0, '', $obj->rowid);
            }elseif(!empty($subdiv_id)){
                $totaltask = CalcOutStandingActions($Code, $totaltask, $obj->rowid, '', '');
                $totaltask = CalcFutureActions($Code, $totaltask, $obj->rowid, '', '');
                $totaltask = CalcFaktActions($Code, $totaltask, $obj->rowid, '', '');
                $totaltask = CalcPercentExecActions($Code, $totaltask, $obj->rowid, '', '');
            }
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
    return $table;
}
function GetBestUserID($Code="'AC_TEL','AC_FAX','AC_EMAIL','AC_RDV','AC_INT','AC_OTH','AC_DEP'", $responming='', $subdiv_id='0'){
    global $db, $user;
    $sql = "select `llx_actioncomm_resources`.fk_element id_usr, count(*) iCount
        from `llx_actioncomm`
        inner join (select id from `llx_c_actioncomm` where code in(".$Code.") and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
        inner join `llx_actioncomm_resources` on `llx_actioncomm`.`id` =  `llx_actioncomm_resources`.`fk_actioncomm`
        where `llx_actioncomm_resources`.fk_element in (select rowid from llx_user where 1 ".
        (empty($responming)?"":" and llx_user.respon_id in (".$responming.")");
    if(!empty($subdiv_id)) {
        $subdiv_array = explode(',', $subdiv_id);
        if($subdiv_array[0]>0)
            $sql .= (empty($subdiv_id) ? "" : " and llx_user.subdiv_id in (" . $subdiv_id . ")");
        else{
            for($i = 0; $i<count($subdiv_array); $i++){
                $subdiv_array[$i] = -$subdiv_array[$i];
            }
            $sql .= (empty($subdiv_id) ? "" : " and llx_user.subdiv_id not in (" . implode(',', $subdiv_array) . ")");
        }
    }
    $sql .=
        ") group by `llx_actioncomm_resources`.fk_element
        order by iCount desc
        limit 1";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    return $obj->id_usr;

}
function CalcPercentExecActions($actioncode, $array, $id_usr=0, $responding='', $subdiv_id=0){
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
        if($id_usr != 0) {
            $sql .= " and `llx_actioncomm_resources`.fk_element = " . $id_usr;
        }else
            $sql .=" and `llx_actioncomm_resources`.fk_element in (select rowid from llx_user where 1 ".(empty($subdiv_id)?"":"and `subdiv_id` = ".$subdiv_id).(empty($responding)?"":" and respon_id in(".$responding.")").")";
        if($i<8) {
            $query_date = date("Y-m-d", (time()+3600*24*(-$i)));
            if($i!=7)
                $sql .= " and datep2 between '".$query_date . "' and date_add('" . $query_date . "', interval 1 day)";
            else
                $sql .= " and datep2 between date_add('" . date("Y-m-d") . "', interval -7 day) and '".date("Y-m-d") . "'";
        }else {
                $sql .= " and datep2 between date_add('" . date("Y-m-d") . "', interval -31 day) and '" . date("Y-m-d") . "'";
        }
//        if($subdiv_id == 0 && $i == 2)
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
//    if($id_usr == 21){
//        var_dump($totaltask);
//        die();
//    }
    for($i=0; $i<9; $i++){
        if($i<8) {
//            if($i == 2 && $subdiv_id == 8){
//                echo '<pre>';
//                var_dump($exectask[$i], $totaltask[$i]);
//                echo '</pre>';
//                die();
//            }
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
function CalcFaktActions($actioncode, $array, $id_usr=0, $responding='', $subdiv_id=0){
    global $db, $user;
    //Минулі виконані дії
    for($i=0; $i<9; $i++) {
        $sql = "select count(*) as iCount  from `llx_actioncomm`
        inner join
        (select id from `llx_c_actioncomm` where code in(".$actioncode.") and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
        left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
        inner join `llx_actioncomm_resources` on `llx_actioncomm`.`id` =  `llx_actioncomm_resources`.`fk_actioncomm`
        where 1";
        if($id_usr != 0)
            $sql .=" and `llx_actioncomm_resources`.fk_element = ".$id_usr;
        else
            $sql .=" and `llx_actioncomm_resources`.fk_element in (select rowid from llx_user where 1 ".(empty($subdiv_id)?"":"and `subdiv_id` = ".$subdiv_id).(empty($responding)?"":" and respon_id in(".$responding.")").")";
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

function CalcFutureActions($actioncode, $array, $id_usr=0, $responding='', $subdiv_id=0){
    global $db, $user;
    //Майбутні дії
    for($i=0; $i<9; $i++) {
        $sql = "select count(*) as iCount  from `llx_actioncomm`
        inner join
        (select id from `llx_c_actioncomm` where code in(".$actioncode.") and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
        left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
        inner join `llx_actioncomm_resources` on `llx_actioncomm`.`id` =  `llx_actioncomm_resources`.`fk_actioncomm`
        where 1";
        if( $id_usr != 0)
            $sql .=" and `llx_actioncomm_resources`.fk_element = ".$id_usr;
        else
            $sql .=" and `llx_actioncomm_resources`.fk_element in (select rowid from llx_user where 1 ".(empty($subdiv_id)?"":"and `subdiv_id` = ".$subdiv_id).(empty($responding)?"":" and respon_id in(".$responding.")").")";
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
function CalcOutStandingActions($actioncode, $array, $id_usr=0, $responding='', $subdiv_id=0){
    global $db, $user;
    $sql = "select count(*) as iCount  from `llx_actioncomm`
    inner join
    (select id from `llx_c_actioncomm` where code in(".$actioncode.") and active = 1) type_action on type_action.id = `llx_actioncomm`.`fk_action`
    left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`
    inner join `llx_actioncomm_resources` on `llx_actioncomm`.`id` =  `llx_actioncomm_resources`.`fk_actioncomm`
    where 1";
        if( $id_usr != 0)
            $sql .=" and `llx_actioncomm_resources`.fk_element = ".$id_usr;
        else
            $sql .=" and `llx_actioncomm_resources`.fk_element in (select rowid from llx_user where 1 ".(empty($subdiv_id)?"":"and `subdiv_id` = ".$subdiv_id).(empty($responding)?"":" and respon_id in(".$responding.")").")";
    $sql .= " and datep2 < '".date("Y-m-d")."'";
    $sql .=" and datea is null";
//    if($actioncode == "'AC_GLOBAL'" || $actioncode == "'AC_CURRENT'"){}
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

    $table = '<tbody id="reference_body">';
    $nom=0;
    //Підрахунок по направленнях
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

    unset($bestvalue);
    $bestvalue = array();
    $bestuser_id = GetBestUserID($Code, '', 1);
    if($bestuser_id) {
        $bestvalue = CalcOutStandingActions($Code, $bestvalue, $bestuser_id);
        $bestvalue = CalcFutureActions($Code, $bestvalue, $bestuser_id);
        $bestvalue = CalcFaktActions($Code, $bestvalue, $bestuser_id);
        $bestvalue = CalcPercentExecActions($Code, $bestvalue, $bestuser_id);
    }

    $sql = 'select name from subdivision, llx_user
        where llx_user.rowid = '.(empty($bestuser_id)?0:$bestuser_id).
    ' and llx_user.subdiv_id = subdivision.rowid';
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    $subdivision = $obj->name;

//    die(DOL_DOCUMENT_ROOT);
    $table.='<tr class="bestvalue"><td class="middle_size" style="width:106px">Офіс</td><td class="middle_size" style="width:144px">Всього по найкращому</td>';
    $table.='<td style="width: '.($conf->browser->name=='firefox'?(30):(31)).'px; text-align:center;"></td>';
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
    unset($bestvalue);

    $sql = 'select rowid from `responsibility` where alias = "dir_depatment" and active = 1';
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $responding = array();
    while($obj = $db->fetch_object($res)){
        $responding[] = $obj->rowid;
    }
    $bestvalue = array();
    $bestuser_id = GetBestUserID($Code, implode(',', $responding));
    if($bestuser_id) {
        $bestvalue = CalcOutStandingActions($Code, $bestvalue, $bestuser_id);
        $bestvalue = CalcFutureActions($Code, $bestvalue, $bestuser_id);
        $bestvalue = CalcFaktActions($Code, $bestvalue, $bestuser_id);
        $bestvalue = CalcPercentExecActions($Code, $bestvalue, $bestuser_id);
    }

    $sql = 'select name from subdivision, llx_user
        where llx_user.rowid = '.(empty($bestuser_id)?0:$bestuser_id).
    ' and llx_user.subdiv_id = subdivision.rowid';
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    $subdivision = $obj->name;

//    die(DOL_DOCUMENT_ROOT);
    $table.='<tr class="bestvalue"><td class="middle_size" style="width:106px">Директор департаменту</td><td class="middle_size" style="width:144px">Всього по найкращому</td>';
    $table.='<td style="width: '.($conf->browser->name=='firefox'?(30):(31)).'px; text-align:center;"></td>';
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

    unset($responding);
    unset($bestvalue);

    $sql = 'select rowid from `responsibility` where alias = "sale" and active = 1';
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $responding = array();
    while($obj = $db->fetch_object($res)){
        $responding[] = $obj->rowid;
    }

    $bestvalue = array();
    $bestuser_id = GetBestUserID($Code, implode(',', $responding), '-1,0');
    if($bestuser_id) {
        $bestvalue = CalcOutStandingActions($Code, $bestvalue, $bestuser_id);
        $bestvalue = CalcFutureActions($Code, $bestvalue, $bestuser_id);
        $bestvalue = CalcFaktActions($Code, $bestvalue, $bestuser_id);
        $bestvalue = CalcPercentExecActions($Code, $bestvalue, $bestuser_id);
    }

    $sql = 'select name from subdivision, llx_user
        where llx_user.rowid = '.(empty($bestuser_id)?0:$bestuser_id).
    ' and llx_user.subdiv_id = subdivision.rowid';
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    $subdivision = $obj->name;

//    die(DOL_DOCUMENT_ROOT);
    $table.='<tr class="bestvalue"><td class="middle_size" style="width:106px">Департамент '.$subdivision.'</td><td class="middle_size" style="width:144px">Всього по найкращому співробітнику</td>';
    $table.='<td style="width: '.($conf->browser->name=='firefox'?(30):(31)).'px; text-align:center;"></td>';
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
//    echo '<pre>';
//    var_dump($totaltask);
//    echo '</pre>';
//    die();
    $table.='<tr style="font-weight: bold"><td class="middle_size" style="width:106px">Компанія</td><td class="middle_size" style="width:144px">Всього задач</td>';
    $id="CompanyAllTask";
    $table.='<td style="width: '.($conf->browser->name=='firefox'?(30):(31)).'px; text-align:center;"><button id="CompanyAllTask" style="width:25px" onclick="ShowAllTask('."'".$id."'".');"><img id="img'.$id.'" src="/dolibarr/htdocs/theme/eldy/img/1downarrow.png"></button></td>';
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
    unset($totaltask);
    $table.='</tr>';

    $sql = "select rowid, name from subdivision where active = 1 order by name";
    $userlist = $db->query($sql);
    if(!$userlist)
        dol_print_error($db);

    //Всього глобальних задач
    $Code = "'AC_GLOBAL'";
    $totaltask = array();
    $totaltask = CalcOutStandingActions($Code, $totaltask, 0);
    $totaltask = CalcFutureActions($Code, $totaltask, 0);
    $totaltask = CalcFaktActions($Code, $totaltask, 0);
    $totaltask = CalcPercentExecActions($Code, $totaltask, 0);
//    echo '<pre>';
//    var_dump($totaltask);
//    echo '</pre>';
//    die();
    $id="CompanyGlobalTask";
    $table.='<tr style="font-weight: bold"><td class="middle_size" style="width:106px">Компанія</td><td class="middle_size" style="width:144px">Всього глобальні задачі (ТОПЗ)</td>';
    $table.='<td style="width: '.($conf->browser->name=='firefox'?(30):(31)).'px; text-align:center;"><button id="CompanyGlobalTask" style="width:25px" onclick="ShowAllTask('."'".$id."'".');"><img id="imgCompanyGlobalTask" src="/dolibarr/htdocs/theme/eldy/img/1downarrow.png"></button></td>';
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
    unset($totaltask);
    $table.='</tr>';


    //Всього поточних задач
    $Code = "'AC_CURRENT'";
    $totaltask = array();
    $totaltask = CalcOutStandingActions($Code, $totaltask, 0);
    $totaltask = CalcFutureActions($Code, $totaltask, 0);
    $totaltask = CalcFaktActions($Code, $totaltask, 0);
    $totaltask = CalcPercentExecActions($Code, $totaltask, 0);
//    echo '<pre>';
//    var_dump($totaltask);
//    echo '</pre>';
//    die();
    $id = 'CompanyCurentTask';
    $table.='<tr style="font-weight: bold"><td class="middle_size" style="width:106px">Компанія</td><td class="middle_size" style="width:144px">Всього поточних</td>';
    $table.='<td style="width: '.($conf->browser->name=='firefox'?(30):(31)).'px; text-align:center;"><button id="CompanyCurentTask" style="width:25px" onclick="ShowAllTask('."'".$id."'".');"><img id="imgCompanyCurentTask" src="/dolibarr/htdocs/theme/eldy/img/1downarrow.png"></button></td>';
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
    unset($totaltask);
    $table.='</tr>';

//Всього по напрямках
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
//    echo '<pre>';
//    var_dump($totaltask);
//    echo '</pre>';
//    die();
    $id = 'CompanyLineActive';
    $table.='<tr style="font-weight: bold"><td class="middle_size" style="width:106px">Компанія</td><td class="middle_size" style="width:144px">Всього по направленнях</td>';
    $table.='<td style="width: '.($conf->browser->name=='firefox'?(30):(31)).'px; text-align:center;"><button id="CompanyLineActive" style="width:25px" onclick="ShowAllTask('."'".$id."'".');"><img id="imgCompanyLineActive" src="/dolibarr/htdocs/theme/eldy/img/1downarrow.png"></button></td>';
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
    unset($totaltask);
    $table.='</tr>';

    //Компанія всього директорів
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
    $totaltask = CalcOutStandingActions($Code, $totaltask, 0, 10);
    $totaltask = CalcFutureActions($Code, $totaltask, 0, 10);
    $totaltask = CalcFaktActions($Code, $totaltask, 0, 10);
    $totaltask = CalcPercentExecActions($Code, $totaltask, 0, 10);

//    echo '<pre>';
//    var_dump($totaltask);
//    echo '</pre>';
//    die();
    $id="DirecorsAllTask";
    $table.='<tr style="font-weight: bold"><td class="middle_size" style="width:106px">Компанія всі директори</td><td class="middle_size" style="width:144px">Всього задач</td>';
    $table.='<td style="width: '.($conf->browser->name=='firefox'?(30):(31)).'px; text-align:center;"><button id="DirecorsAllTask" style="width:25px" onclick="ShowAllTask('."'".$id."'".');"><img id="img'.$id.'" src="/dolibarr/htdocs/theme/eldy/img/1downarrow.png"></button></td>';
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
    unset($totaltask);
    $table.='</tr>';


    $table .= '</tbody>';
    return $table;
}