<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 18.05.2016
 * Time: 11:32
 */
require $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
$form = new Form($db);
$object = new User($db);
if(isset($_REQUEST['action'])&&$_REQUEST['action']=='getAction'){
    $sql = "select distinct action from llx_c_actiontoaddress
      where action like '%".trim($_REQUEST['find']['term'])."%' and active = 1";
//    echo '<pre>';
//    var_dump($_REQUEST);
//    echo '</pre>';
//    die($sql);
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $out = [];
    while($obj = $db->fetch_object($res)){
        $out[]=$obj->action;
    }
    print json_encode($out);
    exit();
}
if(isset($_REQUEST['action'])&&$_REQUEST['action']=='getusercontact'){
    $object->fetch($_REQUEST['id_usr']);
    $phone = str_replace(array('+','(',')',' ','-'),'',$object->office_phone);
//    $out = array('office_phone'=>$object->office_phone,'email'=>$object->email,'skype'=>$object->skype);
//    print json_encode($out);
    $out = '<table id="callfunction"><tr>';
    $out.='
    <td><a onclick="Call('.$phone.', '."'users'".', '.trim($_REQUEST['id_usr']).')"><img src="/dolibarr/htdocs/theme/eldy/img/object_call2.png" title="Телефонний дзвінок"></a></td>
    <td><a onclick="showSMSform('.$phone.');"><img src="/dolibarr/htdocs/theme/eldy/img/object_sms.png" title="Відправити смс"></a></td>
    <td><a href="mailto:' . $object->email . '"><img src="/dolibarr/htdocs/theme/eldy/img/email_delivery.png" title="Відправити email"></a></td>
    <td width="30px"><a href="skype:' . $object->skype . '?call"><img src="/dolibarr/htdocs/theme/eldy/img/object_skype.png" title="Доодати дію"></a></td>
    ';
    $out .= '</tr></table>';
    $out.='<a class="close" title="Закрити" onclick="CloseMenu();" ></a>
    <script>
        function CloseMenu(){
            $("#contactform").remove();
        }
    </script>';
    print $out;
    exit();
}

global $user;
//var_dump($_REQUEST['list']=='callstatistic');
//die($user->rights->user->user->proposition);
//$table = ShowTable();

$HourlyPlan = $langs->trans('Coworkers');
llxHeader("",$HourlyPlan,"");
print_fiche_titre($langs->trans('Coworkers'));
if($_REQUEST['list']=='contactlist') {
    $tbody = showDictActionToAddress();
    include $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/theme/eldy/users/contactlist.html';
    print '<div id="popupmenu" style="display: none; position: absolute; width: auto; height: auto" class="pair popupmenu" >';
}elseif($_REQUEST['list']=='proposition' && $user->rights->user->user->proposition){
//    echo '<pre>';
//    var_dump($user->rights->user->user->proposition);
//    echo '</pre>';
//    die();
    $tbody = showUserProposition();
    include $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/theme/eldy/users/proposition.html';
}elseif($_REQUEST['list']=='callstatistic'){
    if(empty($_REQUEST['month'])) {
        $date = new DateTime();
        $month = $date->format('m');
    }else
        $month = $_REQUEST['month'];
//    $months = '<select id="months" name="months" onchange="SetMonthFilter();">';
    $today = new DateTime();
    $today->setDate(date('Y'),date('m'),date('d'));
    for($i=1;$i<=12;$i++){
        $date = new DateTime();
        $date->setDate(date('Y'),$i,1);
        if($date>$today)
            $year = date('Y')-1;
        else
            $year = date('Y');
        $months.='<option id="Month'.$i.'" '.($month==$i?('selected = "selected"'):'').' value="'.$i.'">'.$langs->trans('Month'.($i<10?('0'.$i):$i)).' ('.$year.'р.)</option>';
    }
    $months.='</select>';
    if(!isset($_REQUEST['begin'])||empty($_REQUEST['begin']))
        $begin = date('Y-m').'-01';
    else{
        $begin = $_REQUEST['beginyear'].'-'.$_REQUEST['beginmonth'].'-'.$_REQUEST['beginday'];
    }
    if(!isset($_REQUEST['end'])||empty($_REQUEST['end'])){
        $unixtime = time();
        $end = date('Y-m').'-'.date('t',$unixtime);
    }else{
        $Date = $_REQUEST['endyear'].'-'.$_REQUEST['endmonth'].'-'.$_REQUEST['endday'];
        $end = date('Y-m-d', strtotime($Date.' +1 days'));
    }
    $tbody = callStatistic($begin,$end);
    $end = $_REQUEST['endyear'].'-'.$_REQUEST['endmonth'].'-'.$_REQUEST['endday'];
    include $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/theme/eldy/users/callstatistic.html';
}else {
    $tbody = showUserList();
    include $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/theme/eldy/users/userlist.html';
}
exit();
function showUserProposition(){

}
function callStatistic($begin,$end){
//    phpinfo();
//    var_dump($begin,$end);
//    die();
    global $db,$langs;

        $now = new DateTime();
        $nowMonth = $now->format('m');
        $sql = "select sub_user.rowid  id_usr, sub_user.alias, `llx_societe`.`region_id`, sub_user.subdiv_id, actioncomm.percent, date(actioncomm.datep) datep,
        case when actioncomm.`code` in ('AC_GLOBAL', 'AC_CURRENT','AC_EDUCATION', 'AC_INITIATIV', 'AC_PROJECT') then actioncomm.`code` else 'AC_CUST' end `code`,
        `llx_societe_action`.`callstatus`, `llx_societe_action`.rowid as answer_id

        from
        (select id, percent, datep,fk_soc,`code`, fk_user_action fk_user_author from llx_actioncomm
        where 1";

        if(empty($_REQUEST['month'])){
            $sql.= " and datep between '".$begin."' and '".$end."'";
        }else {
            $selMonth = $_REQUEST['month'];
            $Year = date('Y');
            if($selMonth<10)$selMonth='0'.$selMonth;
            if ($selMonth+1 == 13)
            {
                $Year--;
            }
            $date = new DateTime();
            $date->setDate($Year,$selMonth,1);
            $date_to = new DateTime();
            $date_to->setDate($Year,$selMonth+1,1);
            $today = new DateTime();
            $today->setDate(date('Y'),date('m'),date('d'));

            if($date>$today) {
                if($date->format('m')<=12){
                    $date->setDate($date->format('Y')-1,$selMonth,1);
                }
                if($date_to->format('m')<=12){
                    $date_to->setDate($date_to->format('Y')-1,$selMonth+1,1);
                }
            }
//            var_dump($date>$today);
//            die();
            $sql .= " and datep between '".$date->format('Y-m-d')."' and '".$date_to->format('Y-m-d')."'";
        }
        $sql.=" and llx_actioncomm.active = 1
                  and `code` not in ('AC_GLOBAL', 'AC_CURRENT')) actioncomm
        left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = actioncomm.id
        left join `llx_societe` on `llx_societe`.`rowid` = `actioncomm`.`fk_soc`
        left join `llx_societe_action` on `llx_societe_action`.`action_id` = `actioncomm`.`id`
        inner join (select `llx_user`.rowid, `responsibility`.`alias`, `llx_user`.subdiv_id from `llx_user` inner join `responsibility` on `responsibility`.`rowid` = `llx_user`.`respon_id`
        where 1 and `llx_user`.`active` = 1) sub_user on sub_user.rowid = case when llx_actioncomm_resources.fk_element is null then actioncomm.`fk_user_author` else llx_actioncomm_resources.fk_element end";
//        echo '<pre>';
//        var_dump($sql);
//        echo '</pre>';
//        die();
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        $actions = array();
        $time = time();
        while ($obj = $db->fetch_object($res)) {
            $actions[] = array('id_usr' => $obj->id_usr, 'region_id' => $obj->region_id, 'subdiv_id'=>$obj->subdiv_id,
                'respon_alias' => $obj->alias, 'percent' => $obj->percent, 'datep' => $obj->datep, 'code' => $obj->code,
                'callstatus'=> $obj->callstatus);
        }
        $_SESSION['callstatistic'] = $actions;

//    }else {
//        $actions = $_SESSION['callstatistic'];
//    }
    $allcall = array();
    $execcall = array();
    $efectcall = array();
    foreach($actions as $call){
        $allcall[$call['id_usr']]++;
        if($call["percent"] == 100)
            $execcall[$call['id_usr']]++;
        if($call["callstatus"] == 5)
            $efectcall[$call['id_usr']]++;
    }
    $sql = "select `llx_user`.rowid, `llx_user`.login email, `llx_user`.lastname, `llx_user`.firstname, `llx_user`.`subdiv_id`, `llx_user`.`office_phone`, `llx_user`.`skype`,
        `subdivision`.`name` as s_subdivision_name, `llx_usergroup`.`nom` as s_llx_usergroup_nom, `responsibility`.alias, r2.alias as alias2, `llx_post`.`postname`
        from `llx_user` left join `subdivision` on `llx_user`.`subdiv_id`= `subdivision`.rowid
        left join `llx_usergroup` on `llx_user`.`usergroup_id`=`llx_usergroup`.rowid
        left join `responsibility` on `responsibility`.rowid = `llx_user`.respon_id
        left join `responsibility` r2 on `r2`.rowid = `llx_user`.respon_id2
        left join `llx_post` on `llx_post`.`rowid` = `llx_user`.`post_id`
        where `llx_user`.active=1
        and `subdivision`.`name` is not null
        and `llx_user`.login not in ('test')
        and (`responsibility`.alias = 'sale' or r2.alias = 'sale')";
    $sql.=" order by s_subdivision_name, `llx_user`.`lastname`, `llx_user`.`firstname`";
//    var_dump($sql);
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $out='<tbody id="userlist">';
    $count=0;
    $symbols = explode(',', '(,), ,+,-');
//    if(count($_POST)>0){
//        echo '<pre>';
//        var_dump($sql);
//        echo '</pre>';
//        die();
//    }
    $setfilter = false;
    foreach($_POST as $item){
        if(!empty($item)){
            $setfilter = true;
            break;
        }
    }

    while($obj = $db->fetch_object($res)){
//        if(!$setfilter ||
//        )
        $class = fmod($count, 2) != 1 ? ("impair") : ("pair");
        $out.='<tr id="rowid_'.$obj->rowid.'" class="'.$class.'">';
//        if($obj->rowid == 38){
//            echo '<pre>';
//            var_dump($obj);
//            echo '</pre>';
//            die();
//        }
        $out.='<td class="middle_size" style="width:152px"id="s_subdivision_name_'.$obj->rowid.'">'.$obj->s_subdivision_name.'</td>';
        $out.='<td class="middle_size" style="width:186px"id="alias_'.$obj->rowid.'">'.$langs->trans($obj->alias).'</td>';
        $out.='<td class="middle_size" style="width:152px"id="postname_'.$obj->rowid.'">'.$obj->postname.'</td>';
        $out.='<td class="middle_size" style="width:148px"id="lastname_'.$obj->rowid.'"><a href="/dolibarr/htdocs/user/useractions.php?id_usr='.$obj->rowid.'">'.$obj->lastname.'</a></td>';
        $out.='<td class="middle_size" style="width:147px"id="firstname_'.$obj->rowid.'">'.$obj->firstname.'</td>';
        $out.='<td class="middle_size" style="width:100px; text-align:center;"id="allcall_'.$obj->rowid.'">'.(!empty($allcall[$obj->rowid])?$allcall[$obj->rowid]:'').'</td>';
        $out.='<td class="middle_size" style="width:73px; text-align:center;"id="execcall_'.$obj->rowid.'">'.(!empty($execcall[$obj->rowid])?$execcall[$obj->rowid]:'').'</td>';
        $out.='<td class="middle_size" style="width:91px; text-align:center;"id="efectcall_'.$obj->rowid.'">'.(!empty($efectcall[$obj->rowid])?$efectcall[$obj->rowid]:'').'</td>';
        $out.='<td style="width:127px" class="emptycol">&nbsp;</td>';
        $out.='</tr>';
        $count++;
    }
    $out.='</tbody>';
    return $out;
//    echo '<pre>';
//    var_dump($execcall);
//    var_dump(array_filter($actions, function($action){
//        return $action['subdiv_id']==1&&$action['respon_alias']=='corp_manager';
//    }));
//    echo '</pre>';
//    exit();
}
function showUserList(){

    global $db,$langs;
    $sql = "select `llx_user`.rowid, `llx_user`.login email, `llx_user`.lastname, `llx_user`.firstname,  `llx_user`.`office_phone`,`llx_user`.`user_mobile`,`llx_user`.`skype`,
        `subdivision`.`name` as s_subdivision_name, `llx_usergroup`.`nom` as s_llx_usergroup_nom, `responsibility`.alias,`llx_post`.`postname`
        from `llx_user` left join `subdivision` on `llx_user`.`subdiv_id`= `subdivision`.rowid
        left join `llx_usergroup` on `llx_user`.`usergroup_id`=`llx_usergroup`.rowid
        left join `responsibility` on `responsibility`.rowid = `llx_user`.respon_id
        left join `llx_post` on `llx_post`.`rowid` = `llx_user`.`post_id`
        where `llx_user`.active=1
        and `llx_user`.statut = 1
        and `subdivision`.`name` is not null
        and `llx_user`.login not in ('test')";
//echo '<pre>';
//var_dump($sql);
//echo '</pre>';
//die();
    if(isset($_POST["subdiv_id"])&&!empty($_POST["subdiv_id"]))
        $sql.=" and `llx_user`.`subdiv_id`=".$_POST["subdiv_id"];
    if(isset($_POST["respon_id"])&&!empty($_POST["respon_id"]))
        $sql.=" and `llx_user`.`respon_id`=".$_POST["respon_id"];
    if(isset($_POST["post_id"])&&!empty($_POST["post_id"]))
        $sql.=" and `llx_user`.`post_id`=".$_POST["post_id"];
    if(isset($_POST["lastname"])&&!empty($_POST["lastname"]))
        $sql.=" and `llx_user`.`lastname` like '%".$_POST["lastname"]."%'";
    if(isset($_POST["firstname"])&&!empty($_POST["firstname"]))
        $sql.=" and `llx_user`.`firstname` like '%".$_POST["firstname"]."%'";


    $sql.=" order by s_subdivision_name, `llx_user`.`lastname`, `llx_user`.`firstname`";
   $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $out='<tbody id="userlist">';
    $count=0;
    $symbols = explode(',', '(,), ,+,-');

    while($obj = $db->fetch_object($res)){
        $class = fmod($count, 2) != 1 ? ("impair") : ("pair");
        $phone_title = (empty($obj->user_mobile)?$obj->office_phone:$obj->user_mobile);
        $out.='<tr id="rowid_'.$obj->rowid.'" class="'.$class.'">';
        $out.='<td class="middle_size" style="width:152px"id="s_subdivision_name_'.$obj->rowid.'">'.$obj->s_subdivision_name.'</td>';
        $out.='<td class="middle_size" style="width:186px"id="alias_'.$obj->rowid.'">'.$langs->trans($obj->alias).'</td>';
        $out.='<td class="middle_size" style="width:152px"id="postname_'.$obj->rowid.'">'.$obj->postname.'</td>';
        $out.='<td class="middle_size" style="width:148px"id="lastname_'.$obj->rowid.'"><a href="/dolibarr/htdocs/user/useractions.php?id_usr='.$obj->rowid.'">'.$obj->lastname.'</a></td>';
        $out.='<td class="middle_size" style="width:147px"id="firstname_'.$obj->rowid.'">'.$obj->firstname.'</td>';

        $phone = str_replace($symbols,'', $phone_title);
        if(!empty($phone_title)) {
            $phonelink = '<a onclick="Call('.$phone.', '."'users'".', '.$obj->rowid.');">';
            $out .= '<td class="middle_size" style="width:148px"><table class="phone"><tbody><tr>';
            $out .= '<td class="middle_size" style="width:130px"= id="office_phone_' . $obj->rowid . '">' . $phonelink . $phone_title . '</a></td>';
            $out .= '<td class="middle_size" id="sms_' . $obj->rowid . '" onclick="showSMSform(' . $phone . ');" style="width: 20px"><img src="/dolibarr/htdocs/theme/eldy/img/object_sms.png"></td>';
            $out .= '</tr></tbody></table></td>';
        }else{
            $out.='<td></td>';
        }
        $out.='<td class="middle_size" style="width:205px"id="email_'.$obj->rowid.'"><a href="mailto:'.$obj->email.'">'.$obj->email.'</a></td>';
        $out.='<td class="middle_size" style="width:148px"id="skype_'.$obj->rowid.'"><a href="skype:'.$obj->skype.'?call">'.$obj->skype.'</a></td>';
        $out.='<td style="width:127px" class="emptycol">&nbsp;</td>';
        $out.='</tr>';
        $count++;

    }
    $out.='</tbody>';
    return $out;
}
function showResponsible(){
    global $db;
    $sql = "select distinct responsible from llx_c_actiontoaddress where active = 1 order by responsible";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $out = '<select id = "responsible" class="combobox" name="responsible" style="width: 100%">';
    $out .= '<option></option>';
    while($obj = $db->fetch_object($res)) {
        $out .= '<option>'.$obj->responsible.'</option>';
    }
    $out.='</select>';
    return $out;
}
function showDirectlyResponsible(){
    global $db;
    $sql = "select distinct directly_responsible from llx_c_actiontoaddress where active = 1 order by responsible";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $out = '<select id = "directly_responsible" class="combobox" name="directly_responsible" style="width: 100%">';
    $out .= '<option></option>';
    while($obj = $db->fetch_object($res)) {
        $out .= '<option>'.$obj->directly_responsible.'</option>';
    }
    $out.='</select>';
    return $out;
}
function showDictActionToAddress(){
    global $db;
    $sql = "select `llx_c_actiontoaddress`.`rowid`,  `llx_c_groupoforgissues`.`issues` fk_groupissues, case when `llx_c_actiontoaddress`.`fk_subdivision` = -1 then 'Всі підрозділи' else `subdivision`.`name` end fk_subdivision,
    `llx_c_actiontoaddress`.`action`, `llx_c_actiontoaddress`.`responsible`,`llx_c_actiontoaddress`.`directly_responsible`
	from llx_c_actiontoaddress
	left join `llx_c_groupoforgissues` on `llx_c_groupoforgissues`.`rowid` = `llx_c_actiontoaddress`.`fk_groupissues`
	left join `subdivision` on `subdivision`.`rowid` = `llx_c_actiontoaddress`.`fk_subdivision`
	where 1 ";
    if(isset($_POST["fk_groupissues"])&&$_POST["fk_groupissues"]!='-1'){//Група завдань
        $sql.=" and `llx_c_groupoforgissues`.`rowid` = ".$_POST["fk_groupissues"];
    }
    if(isset($_POST["fk_subdivision"])&&$_POST["fk_subdivision"]>0){//Підрозділ
        $sql.=" and `subdivision`.`rowid` = ".$_POST["fk_subdivision"];
    }
    if(isset($_POST["action"])&&!empty($_POST["action"])) {//Підрозділ
        $sql.=" and `llx_c_actiontoaddress`.`action` like '%".$_POST["action"]."%'";
    }
    if(isset($_POST["responsible"])&&!empty($_POST["responsible"])) {//Відповідальний
        $sql.=" and `llx_c_actiontoaddress`.`responsible` like '%".$_POST["responsible"]."%'";
    }
    if(isset($_POST["responsible"])&&!empty($_POST["responsible"])) {//Відповідальний
        $sql.=" and `llx_c_actiontoaddress`.`directly_responsible` like '%".$_POST["directly_responsible"]."%'";
    }
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $out='<tbody id="dict">';
    $count=0;
    while($obj = $db->fetch_object($res)){
        $class = fmod($count, 2) != 1 ? ("impair") : ("pair");
        $out.='<tr id="rowid_'.$obj->rowid.'" class="'.$class.'">';
        $out.='<td style="width: 205px"id="fk_groupissues_'.$obj->rowid.'">'.$obj->fk_groupissues.'</td>';
        $out.='<td style="width: 143px" id="fk_subdivision_'.$obj->rowid.'">'.$obj->fk_subdivision.'</td>';
        $out.='<td style="width: 205px" id="action_'.$obj->rowid.'">'.$obj->action.'</td>';
        $out.='<td style="width: 200px"id="responsible_'.$obj->rowid.'">'.$obj->responsible.'</td>';
        $out.='<td style="width: 233px" id="directly_responsible_'.$obj->rowid.'">'.$obj->directly_responsible.'</td>';
        $out.='<td style="width: 127px">&nbsp;</td>';
        $out.='</tr>';
        $count++;
    }
    $out.='</tbody>';
    return $out;
}

