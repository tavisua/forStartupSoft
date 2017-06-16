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
if($_REQUEST['action'] == 'createStatisticPage' || $_REQUEST['action'] == 'insertNewActions')
    define('NOLOGIN',1);
require $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/main.inc.php';
if(isset($_REQUEST['action'])){
    global $db,$user;
    switch($_REQUEST['action']) {
        case 'createStatisticPage':{
            createStaticDayPlanPage();
            exit();
        }break;
        case 'insertNewActions':{
            $sql = "select `llx_actioncomm`.`id`, `llx_actioncomm`.`percent`, `llx_actioncomm`.`code`, `llx_actioncomm`.`datec`, `llx_user`.`rowid`, `llx_user`.`lastname` from llx_actioncomm
                left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = `llx_actioncomm`.`id`
                left join `llx_user` on `llx_user`.`rowid` = `llx_actioncomm_resources`.`fk_element`
                where new = 1
                and `llx_actioncomm`.`fk_user_author`<>`llx_actioncomm_resources`.`fk_element`
                and `llx_actioncomm`.`percent` = -1
                and `llx_actioncomm`.`active` = 1 union
                select `llx_actioncomm`.`id`, `llx_actioncomm`.`percent`, `llx_actioncomm`.`code`, `llx_societe_action`.`dtChange`, `llx_user`.`rowid`, `llx_user`.`lastname` from llx_actioncomm
                left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = `llx_actioncomm`.`id`
                left join `llx_user` on `llx_user`.`rowid` = `llx_actioncomm_resources`.`fk_element`
                left join `llx_societe_action` on `llx_societe_action`.`action_id` = `llx_actioncomm`.`id`
                where `llx_actioncomm`.new = 1
                and `llx_actioncomm`.`active` = 1
                and `llx_societe_action`.`id_usr` <> (case when `llx_actioncomm`.`fk_user_valuer` is null or `llx_actioncomm`.`fk_user_valuer`<=0 then `llx_actioncomm`.`fk_user_author` else `llx_actioncomm`.`fk_user_valuer` end)
                and (`llx_societe_action`.`new` is null or `llx_societe_action`.`new` = 1)
                and `llx_actioncomm`.`fk_user_author`<>`llx_actioncomm_resources`.`fk_element`
                and `llx_actioncomm`.`percent` >=0 and `llx_actioncomm`.`percent`<99 union
                select `llx_actioncomm`.`id`, `llx_actioncomm`.`percent`, `llx_actioncomm`.`code`, `llx_societe_action`.`dtChange`, `llx_user`.`rowid`, `llx_user`.`lastname` from llx_actioncomm
                left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = `llx_actioncomm`.`id`
                left join `llx_societe_action` on `llx_societe_action`.`action_id` = `llx_actioncomm`.`id`
                left join `llx_user` on `llx_user`.`rowid` = case when `llx_societe_action`.`id_mentor` is null then `llx_societe_action`.`id_usr` else `llx_societe_action`.`id_mentor` end
                where 1
                and `llx_societe_action`.`new` in(1,null)
                and `llx_actioncomm`.`active` = 1
                and `llx_societe_action`.`id_usr` <> `llx_actioncomm_resources`.`fk_element`
                and `llx_actioncomm`.`fk_user_author`<>`llx_actioncomm_resources`.`fk_element`
                and `llx_actioncomm`.`percent` >=0 and `llx_actioncomm`.`percent`<99 union
                select `llx_actioncomm`.`id`, `llx_actioncomm`.`percent`, `llx_actioncomm`.`code`, `llx_societe_action`.`dtChange`, `llx_user`.`rowid`, `llx_user`.`lastname` from llx_actioncomm
                left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = `llx_actioncomm`.`id`
                left join `llx_societe_action` on `llx_societe_action`.`action_id` = `llx_actioncomm`.`id`
                left join `llx_user` on `llx_user`.`rowid` = `llx_actioncomm_resources`.`fk_element`
                where `llx_actioncomm`.new = 1
                and `llx_actioncomm`.`active` = 1
                and `llx_societe_action`.`id_usr` <> `llx_actioncomm_resources`.`fk_element`
                and `llx_societe_action`.`new` is null
                and `llx_actioncomm`.`fk_user_author`<>`llx_actioncomm_resources`.`fk_element`
                and `llx_actioncomm`.`percent` >=0 and `llx_actioncomm`.`percent`<99 union
                select `llx_actioncomm`.`id`, `llx_actioncomm`.`percent`, `llx_actioncomm`.`code`, `llx_actioncomm`.`datea`, `llx_user`.`rowid`, `llx_user`.`lastname`  from llx_actioncomm
                left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = `llx_actioncomm`.`id`
                left join `llx_user` on `llx_user`.`rowid` = `llx_actioncomm`.`fk_user_author`
                where `new` = 1
                and `llx_actioncomm`.`active` = 1
                and `llx_actioncomm`.`fk_user_author`<>`llx_actioncomm_resources`.`fk_element`
                and `llx_actioncomm`.`percent` = 99
                and (`llx_actioncomm`.`fk_user_valuer` is null or `llx_actioncomm`.`fk_user_valuer`< 0 or `llx_actioncomm`.`fk_user_valuer` > 0 and `llx_actioncomm`.`dtValidValuer` is not null)  union
                select `llx_actioncomm`.`id`, `llx_actioncomm`.`percent`, `llx_actioncomm`.`code`, `llx_actioncomm`.`datea`, `llx_user`.`rowid`, `llx_user`.`lastname`  from llx_actioncomm
                left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = `llx_actioncomm`.`id`
                left join `llx_user` on `llx_user`.`rowid` = `llx_actioncomm`.`fk_user_valuer`
                where `new` = 1
                and `llx_actioncomm`.`active` = 1
                and `llx_actioncomm`.`percent` = 99
                and `llx_actioncomm`.`fk_user_valuer` is not null
                and `llx_actioncomm`.`fk_user_author`<>`llx_actioncomm_resources`.`fk_element`
                and `llx_actioncomm`.`dtValidValuer` is null";
            $res = $db->query($sql);
            require_once $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/comm/action/class/actioncomm.class.php';
            $action_tmp = new ActionComm($db);

            while($obj = $db->fetch_object($res)){
                $usersID = $action_tmp->getFromToUserIDs($obj->id);
                $action_tmp->ShowAction($usersID['for'],$usersID['from'],$obj->id,'oncreate');
            }
        }break;
        case 'resetFutureActions':{
            set_time_limit(0);
            require_once $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/comm/action/class/actioncomm.class.php';
            $Action = new ActionComm($db);
            $sql = "select id from llx_actioncomm
                where code in ('AC_GLOBAL','AC_CURRENT')
                and fk_user_author = 5 and active = 1";
            $res = $db->query($sql);
            while($obj = $db->fetch_object($res)){
                $Action->setFutureDataAction($obj->id);
            }
        }
        case 'setOverdue_Actions_GlobalItem':{
            require_once $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/cron/class/cronjob.class.php';
            $CronJob = new Cronjob($db);
            $status = $CronJob->setStartCronStatus('setOverdue_Actions_GlobalItem');
            if($status == 0)
                return 0;

            set_time_limit(0);
            require_once $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/comm/action/class/actioncomm.class.php';
            $Action = new ActionComm($db);
            $time = time();
            if(empty($_REQUEST['action_id'])){
                $sql = "update llx_actioncomm set overdue = null where 1 and llx_actioncomm.`code` <> 'AC_OTH_AUTO'";
                $res = $db->query($sql);
                if (!$res)
                    dol_print_error($db);
                setOverdue_Actions_GlobalItem();


//                echo time()-$time;
                $CronJob->setExecCronStatus('setOverdue_Actions_GlobalItem');

            }else{
                $Action->setOuterdueStatus($_REQUEST['action_id']);
            }
        }break;
        case 'getOverdueActions':{
            $sql = "select llx_actioncomm.id, datep2, llx_actioncomm.`code` from llx_actioncomm 
                left join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id 
                where 1 and overdue = 1 and llx_actioncomm.percent not in (100, -100, 99)                  
                and case when `llx_actioncomm_resources`.`fk_element` is null then `fk_user_action` else `llx_actioncomm_resources`.`fk_element` end = $user->id ";
            switch ($_REQUEST['code']){
                case 'AC_TEL':{
                    $sql.="and llx_actioncomm.`code` = 'AC_TEL' ";
                }break;
                case 'AC_GLOBAL':{
                    $sql.="and llx_actioncomm.`code` = 'AC_GLOBAL' ";
                }break;
                case 'AC_CURRENT':{
                    $sql.="and llx_actioncomm.`code` = 'AC_CURRENT' ";
                }break;
                default :{
                    if(strpos(',',$_REQUEST['code']))
                        $sql.="and llx_actioncomm.`code` <> 'AC_OTH_AUTO' ";
                    else {
                        $sql .= "and llx_actioncomm.`code` in ('" . str_replace(',', "','", $_REQUEST['code']) . "')";
                    }
                }
            }
            $sql.="and active = 1
                order by datep2;";
//            die($sql);
            $res = $db->query($sql);
            if(!$res)
                dol_print_error($db);
            $out = '<table><tbody>';
            while($obj = $db->fetch_object($res)){
                $date = new DateTime($obj->datep2);
                $item='<td>'.$date->format('d.m.y').'</td>';
                switch ($obj->code) {
                    case 'AC_GLOBAL': {
                        $classitem = 'global_task';
                        $iconitem = 'object_global_task.png';
                        $title = $langs->trans('ActionGlobalTask');
                    }
                        break;
                    case 'AC_CURRENT': {
                        $classitem = 'current_task';
                        $iconitem = 'object_current_task.png';
                        $title = $langs->trans('ActionCurrentTask');
                    }
                        break;
                    case 'AC_RDV': {
                        $classitem = 'office_meetting_task';
                        $iconitem = 'object_office_meetting_task.png';
                        $title = $langs->trans('ActionAC_RDV');
                    }
                        break;
                    case 'AC_TEL': {
                        $classitem = 'office_callphone_task';
                        $iconitem = 'object_call2.png';
                        $title = $langs->trans('ActionAC_TEL');
                    }
                        break;
                    case 'AC_DEP': {
                        $classitem = 'departure_task';
                        $iconitem = 'object_departure_task.png';
                        $title = $langs->trans('ActionDepartureMeeteng');
                    }
                        break;
                }
                $item.='<td><img src="/dolibarr/htdocs/theme/eldy/img/'.$iconitem.'"></td>';
                global $conf;
                $link = "/dolibarr/htdocs/comm/action/chain_actions.php?mainmenu=$classitem&action_id=$obj->id";
//                die($link);
                if($conf->browser->name == 'chrome')
                    $action = "window.open($link, '_blank', 'toolbar=yes, location=yes, status=yes, menubar=yes, scrollbars=yes');";
                else
                    $action = "window.open($link);";
                $out.="<tr class='middle_size' style='cursor:pointer' onclick='ShowAction($obj->id)'>";
                $out.=$item;
                $out.='</tr>';
            }
            $out.='</tbody>
            <script>
                function ShowAction(actionid) {
                var link = "/dolibarr/htdocs/comm/action/chain_actions.php?mainmenu='.$classitem.'&action_id="+actionid
                    if(\'<?=($conf->browser->name)?>\' == \'chrome\') {

                        window.open(link, \'_blank\', \'toolbar=yes, location=yes, status=yes, menubar=yes, scrollbars=yes\');
                    }else {
                        window.open(link);
                    }                  
                }
            </script>';
            print $out;
            exit();
        }break;
        case 'getnewactions':{
            if(isset($_SESSION['spy_id_usr'])&&!empty($_SESSION['spy_id_usr']))
                return 0;
            $id_user = $user->id;
//            echo '<pre>';
//            var_dump($_SESSION['dol_login']);
//            echo '</pre>';
//            die();
            if(isset($_SESSION['spy_id_usr'])&&!empty($_SESSION['spy_id_usr'])){
                $sql = "select rowid from llx_user where login = '".$_SESSION['dol_login']."' and active = 1";
                $res = $db->query($sql);
                if(!$res)
                    dol_print_error($db);
                $obj = $db->fetch_object($res);
                $id_user = $obj->rowid;
            }
            echo getNewAcctions($id_user);
            exit();
        }break;
        case 'getOutStandingIntoRegion':{
//            var_dump(empty($_REQUEST["region_id"]));
//            die();
            global $db;
            if(!isset($_GET['id_usr'])||empty($_GET['id_usr']))
                $id_user = $user->id;
            else
                $id_user = $_GET['id_usr'];
            $sql="select distinct  date(statistic_action.datep) datep
                from statistic_action
                inner join (select id from `llx_c_actioncomm` where type in('user','system') and active = 1) type_action on type_action.id = `statistic_action`.`fk_action`
                left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = statistic_action.id
                left join `llx_societe` on `llx_societe`.`rowid` = `statistic_action`.`fk_soc`
                left join `llx_societe_action` on `llx_societe_action`.`action_id` = `statistic_action`.`id`
                inner join (select `llx_user`.rowid, `responsibility`.`alias`
                  from `llx_user` inner join `responsibility` on `responsibility`.`rowid` = `llx_user`.`respon_id` where `llx_user`.`rowid` = ".$id_user.")
                  sub_user on sub_user.rowid = case when llx_actioncomm_resources.fk_element is null then statistic_action.`fk_user_action` else llx_actioncomm_resources.fk_element end
                where 1
                and statistic_action.active = 1 ";
            if($_REQUEST["region_id"] != -1){
                if (!empty($_REQUEST["region_id"]))
                    $sql .= "and region_id = " . $_REQUEST["region_id"];
                else
                    $sql .= "and region_id is null";
            }
            $sql.= " and overdue = 1";
            $res = $db->query($sql);
//            echo '<pre>';
//            var_dump($sql);
//            echo '</pre>';
//            die();
            if(!$res)
                dol_print_error($db);
            $out = '<a class="close" title="Закрити" onclick="closeForm($(this).parent());"></a>
                <table class="setdate" style="background: #ffffff">
                <thead>
                <tr class="multiple_header_table" style="width: 100px">
                <th class="middle_size">Вкажіть дату на яку відобразити завдання</th>
                </tr>
                </thead>';
            while($obj = $db->fetch_object($res)){
                $date = new DateTime($obj->datep);
                $out.='<tr><td onclick="closeForm($(this).parent().parent().parent().parent())" class="small_size"><a target="_blank" href="/dolibarr/htdocs/hourly_plan.php?idmenu=10420&mainmenu=hourly_plan&region_id='.$_REQUEST["region_id"].'&leftmenu=&id_usr='.$id_user.'&date='.$date->format('d.m.Y.').'">'.$date->format('d.m.Y.').'</td></tr>';
            }
            $out.='</tbody></table>';
            echo $out;
        }break;
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
//            echo '<pre>';
//            var_dump($_REQUEST);
//            echo '</pre>';
//            die();
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
//echo 'pleace wait...';
//echo '<pre>';
//var_dump([$user->respon_alias,$user->respon_alias2]);
//echo '</pre>';
//die();
$dir_depatment = ['dir_depatment','corp_manager'];
//die("Location: http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/responsibility/".$user->respon_alias."/day_plan.php?idmenu=10419&mainmenu=plan_of_days&leftmenu=");
header("Location: http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/responsibility/".(count(array_intersect([$user->respon_alias,$user->respon_alias2],$dir_depatment))>0&&$user->respon_alias!='gen_dir'?'dir_depatment':$user->respon_alias)."/day_plan.php?idmenu=10419&mainmenu=plan_of_days&leftmenu=");

exit();
function setOverdue_Actions_GlobalItem(){
    global $db;
    $sql = "update llx_actioncomm set overdue = null";
    $res = $db->query($sql);
    if (!$res)
        dol_print_error($db);
    require_once $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/comm/action/class/actioncomm.class.php';
    $Action = new ActionComm($db);
    $sql = "select llx_actioncomm.id from llx_actioncomm where date(datep) between adddate(date(now()), interval -1 month) and date(now())
        and active = 1 and `code` <> 'AC_OTH_AUTO'";
    $res = $db->query($sql);
    if (!$res)
        dol_print_error($db);
//                $num = 0;
    $sql = "update llx_actioncomm set overdue = null";
    $res_update = $db->query($sql);
    if (!$res_update)
        dol_print_error($db);
    while ($obj = $db->fetch_object($res)) {

        $overdue = $Action->setOuterdueStatus($obj->id);
//        if($overdue){
//            $sql = "update llx_actioncomm set overdue = 1 where id = ".$obj->id;
//            $res_update = $db->query($sql);
//        }
//                        $actionsID = array_merge($actionsID, $Action->setOuterdueStatus($obj->id));
//                        var_dump($actionsID);
    }

}
function createStaticDayPlanPage(){
    global $db;
    set_time_limit(0);
    $sql = "insert into llx_cronjob (`command`,`datestart`,`params`,`label`,`jobtype`)  values('createStatisticPage',now(),'0','_','_')";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $actioncode = array('AC_GLOBAL','AC_CURRENT','AC_EDUCATION','AC_INITIATIV','AC_PROJECT');
    setOverdue_Actions_GlobalItem();
    $sql = "show tables like 'statistic_action'";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    if($res->num_rows != 0) {
        $sql = "drop table statistic_action;";
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
    }
    $sql = "create table statistic_action like llx_actioncomm";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $sql = "insert into statistic_action
    select * from llx_actioncomm
    where date(datep) between adddate(date(now()), interval -1 week) and adddate(date(now()), interval 1 month) or overdue = 1
    and active = 1
    and code <> 'AC_OTH_AUTO'";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $sql = "update llx_cronjob set `dateend` = now() where `command` = 'createStatisticPage' and `dateend` is null";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    echo 1;
}
function TotalTask($actions_tmp){
    global $actioncode;
    $today = new DateTime();
    $mkToday = dol_mktime(0,0,0,$today->format('m'),$today->format('d'),$today->format('Y'));
    $userActions_tmp = array();
    foreach ($actions_tmp as $key=>$item) {
//        echo '<pre>';
//        var_dump($action);
//        echo '</pre>';
//        die();
            $obj = (object)$item;
            $date = new DateTime($obj->datep);
            $mkDate = dol_mktime(0, 0, 0, $date->format('m'), $date->format('d'), $date->format('Y'));


        $userActions_tmp[$obj->datep][$obj->code]++;

            if ($mkDate >= $mkToday) { //Future actions
                if ($mkDate - $mkToday <= 604800) {//604800 sec by week
                    $userActions_tmp['future_week'][$obj->code]++;
                }
                if ($mkDate - $mkToday <= 2678400)//2678400 sec by month
                    $userActions_tmp['future_month'][$obj->code]++;
            }
            if ($obj->overdue) {
                $userActions_tmp['outstanding'][$obj->code]++;
            }
            if ($mkDate <= $mkToday && $obj->percent == 100 && (in_array($item['code'], $actioncode) || $item['callstatus'] == '5')) {
//                if($obj->datep == '2016-05-30'){
//                        echo '<pre>';
//                        var_dump($item['rowid'], $item['code'], $item['callstatus']);
//                        echo '</pre>';
//                }
                $userActions_tmp['fact_' . $obj->datep][$obj->code]++;
                if ($mkToday - $mkDate <= 604800)//604800 sec by week
                    $userActions_tmp['fact_week'][$obj->code]++;
                if ($mkToday - $mkDate <= 2678400)//2678400 sec by month
                    $userActions_tmp['fact_month'][$obj->code]++;
            }
            if ($mkDate <= $mkToday) {
                $userActions_tmp['total_' . $obj->datep][$obj->code]++;
                if ($mkToday - $mkDate <= 604800)//604800 sec by week
                    $userActions_tmp['total_week'][$obj->code]++;
                if ($mkToday - $mkDate <= 2678400)//2678400 sec by month
                    $userActions_tmp['total_month'][$obj->code]++;
            }
        }

//    if($mkDate <= $mkToday && $action['percent'] == 100 && $action['code'] == 'AC_CUST' && $action['callstatus'] == '5'){
//        if($mkToday-$mkDate<=604800&&$mkToday-$mkDate>=0)//604800 sec by week
//            $CustActions[$action['id_usr']]++;
//
//    }
    $table='<tr><td class="middle_size" style="width: 105px"><b>Всього задач</b></td>
    <td style="width: 175px">&nbsp;</td>
    <td style="width: 33px">&nbsp;</td>';
    //% виконання запланованого по факту
    for($i=8; $i>=0; $i--){
        if($i < 8) {
            $percent = '';
            if($i<7) {
                $count = (isset($userActions_tmp['fact_' . date("Y-m-d", (time() - 3600 * 24 * $i))]) ? array_sum(($userActions_tmp['fact_' . date("Y-m-d", (time() - 3600 * 24 * $i))])) : ('0'));
                $total = (isset($userActions_tmp['total_' . date("Y-m-d", (time() - 3600 * 24 * $i))]) ? array_sum(($userActions_tmp['total_' . date("Y-m-d", (time() - 3600 * 24 * $i))])) : (''));
            }else{
                $count = isset($userActions_tmp['fact_week'])?array_sum($userActions_tmp['fact_week']):'';
                $total = isset($userActions_tmp['total_week'])?array_sum($userActions_tmp['total_week']):'';
            }
        }else{
            $count = isset($userActions_tmp['fact_month'])?array_sum(($userActions_tmp['fact_month'])):('0');
            $total = isset($userActions_tmp['total_month'])?array_sum(($userActions_tmp['total_month'])):('0');
        }
        if(!empty($total))
            $percent = round(100*$count/($total==0?1:$total));
        $table .= '<td class="middle_size" style="width: ' . (in_array($i, array(0,8))?'35':'30') . 'px; text-align:center;">' . $percent. '</td>';
    }
    //минуле факт
    for($i=8; $i>=0; $i--){
        if($i < 8)
            $table.='<td class="middle_size" style="width: '.(in_array($i, array(0,8))?'35':'30').'px; text-align:center;">'.($i<7?(isset($userActions_tmp['fact_'.date("Y-m-d", (time()-3600*24*$i))])?array_sum(($userActions_tmp['fact_'.date("Y-m-d", (time()-3600*24*$i))])):('')):(isset($userActions_tmp['fact_week'])?array_sum($userActions_tmp['fact_week']):'')).'</td>';
        else
            $table.='<td class="middle_size" style="width: '.(in_array($i, array(0,8))?'35':'30').'px; text-align:center;">'.(isset($userActions_tmp['fact_month'])?array_sum($userActions_tmp['fact_month']):('0')).'</td>';
    }
    //прострочено
    $value = '';
    if(!empty($userActions_tmp['outstanding']))
        $value = array_sum($userActions_tmp['outstanding']);
    $table .= '<td class="middle_size" style="text-align: center; width: 51px">' . $value . '</td>';
    //майбутнє заплановано
    for($i=0; $i<9; $i++){
        $value = '';
        if($i < 8) {
            if($i < 7)
                $value =  (isset($userActions_tmp[date("Y-m-d", (time() + 3600 * 24 * $i))]) ? array_sum($userActions_tmp[date("Y-m-d", (time() + 3600 * 24 * $i))]) : (''));
            else {
                if(!empty($userActions_tmp['future_week']))
                    $value = (array_sum($userActions_tmp['future_week']));
            }
            $table .= '<td class="middle_size" style="text-align: center; width: ' . (in_array($i, array(0))?'35':'30') . 'px">'.(($i < 7)?'<a href="/dolibarr/htdocs/hourly_plan.php?idmenu=10420&mainmenu=hourly_plan&leftmenu=&date=' . date("Y-m-d", (time() + 3600 * 24 * $i)) . '">':'') . $value . (($i < 7)?'</a>':'').'</td>';
        }else {
            if(!empty($userActions_tmp['future_month']))
                $value = array_sum($userActions_tmp['future_month']);
            $table .= '<td class="middle_size" style="text-align: center; width: 35px">' . $value . '</td>';
        }
    }
    $table.='</tr>';
    return $table;
};
function getNewAcctions($id_usr){

    global $db, $user;
    $sql = "select llx_actioncomm.id, llx_actioncomm.datep, llx_actioncomm.when_show, `llx_actioncomm`.`code`, llx_actioncomm.percent, llx_user.lastname from llx_newactions
        inner join llx_actioncomm on llx_actioncomm.id = llx_newactions.id_action
        inner join llx_user on llx_user.rowid = llx_newactions.from_user
        where llx_newactions.id_usr = $id_usr
        union all
        select concat('_',ABS(llx_newactions.id_action)) id, llx_newactions.dtChange, llx_newactions.when_show, 'AC_COMMENT', 0, llx_user.lastname
        from llx_newactions
        inner join llx_user on llx_user.rowid = llx_newactions.from_user
        where llx_newactions.id_usr = $id_usr
        and llx_newactions.id_action < 0";

//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();
//
//    $sql = "select `llx_actioncomm`.`id`, `llx_actioncomm`.`percent`, `llx_actioncomm`.`code`, `llx_actioncomm`.`datec`, `llx_user`.`lastname` from llx_actioncomm
//        left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = `llx_actioncomm`.`id`
//        left join `llx_user` on `llx_user`.`rowid` = `llx_actioncomm`.`fk_user_author`
//        where new = 1
//        and `llx_actioncomm_resources`.`fk_element` = ".$id_usr."
//        and `llx_actioncomm`.`fk_user_author`<>`llx_actioncomm_resources`.`fk_element`
//        and `llx_actioncomm`.`percent` = -1
//        and `llx_actioncomm`.`active` = 1";
//    $sql.=" union
//        select `llx_actioncomm`.`id`, `llx_actioncomm`.`percent`, `llx_actioncomm`.`code`, `llx_societe_action`.`dtChange`, `llx_user`.`lastname` from llx_actioncomm
//        left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = `llx_actioncomm`.`id`
//        left join `llx_user` on `llx_user`.`rowid` = `llx_actioncomm_resources`.`fk_element`
//        left join `llx_societe_action` on `llx_societe_action`.`action_id` = `llx_actioncomm`.`id`
//        where `llx_actioncomm`.new = 1
//        and `llx_actioncomm`.`active` = 1
//        and (case when `llx_actioncomm`.`fk_user_valuer` is null or `llx_actioncomm`.`fk_user_valuer`<=0 then `llx_actioncomm`.`fk_user_author` else `llx_actioncomm`.`fk_user_valuer` end) = ".$id_usr."
//        and `llx_societe_action`.`id_usr` <> ".$id_usr."
//        and (`llx_societe_action`.`new` is null or `llx_societe_action`.`new` = 1)
//        and `llx_actioncomm`.`fk_user_author`<>`llx_actioncomm_resources`.`fk_element`
//        and `llx_actioncomm`.`percent` >=0 and `llx_actioncomm`.`percent`<99";
//    $sql.=" union
//        select `llx_actioncomm`.`id`, `llx_actioncomm`.`percent`, `llx_actioncomm`.`code`, `llx_societe_action`.`dtChange`, `llx_user`.`lastname` from llx_actioncomm
//        left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = `llx_actioncomm`.`id`
//        left join `llx_societe_action` on `llx_societe_action`.`action_id` = `llx_actioncomm`.`id`
//        left join `llx_user` on `llx_user`.`rowid` = case when `llx_societe_action`.`id_mentor` is null then `llx_societe_action`.`id_usr` else `llx_societe_action`.`id_mentor` end
//        where 1
//        and `llx_actioncomm_resources`.`fk_element` = ".$id_usr."
//        and `llx_societe_action`.`new` in(1,null)
//        and `llx_actioncomm`.`active` = 1
//        and `llx_societe_action`.`id_usr` <> ".$id_usr."
//        and `llx_actioncomm`.`fk_user_author`<>`llx_actioncomm_resources`.`fk_element`
//        and `llx_actioncomm`.`percent` >=0 and `llx_actioncomm`.`percent`<99";
//    $sql.=" union
//        select `llx_actioncomm`.`id`, `llx_actioncomm`.`percent`, `llx_actioncomm`.`code`, `llx_societe_action`.`dtChange`, `llx_user`.`lastname` from llx_actioncomm
//        left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = `llx_actioncomm`.`id`
//        left join `llx_societe_action` on `llx_societe_action`.`action_id` = `llx_actioncomm`.`id`
//        left join `llx_user` on `llx_user`.`rowid` = `llx_societe_action`.`id_usr`
//        where `llx_actioncomm`.new = 1
//        and `llx_actioncomm`.`active` = 1
//        and `llx_actioncomm_resources`.`fk_element` = ".$id_usr."
//        and `llx_societe_action`.`id_usr` <> ".$id_usr."
//        and `llx_societe_action`.`new` is null
//        and `llx_actioncomm`.`fk_user_author`<>`llx_actioncomm_resources`.`fk_element`
//        and `llx_actioncomm`.`percent` >=0 and `llx_actioncomm`.`percent`<99";
//    $sql.=" union
//        select `llx_actioncomm`.`id`, `llx_actioncomm`.`percent`, `llx_actioncomm`.`code`, `llx_actioncomm`.`datea`, `llx_user`.`lastname`  from llx_actioncomm
//        left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = `llx_actioncomm`.`id`
//        left join `llx_user` on `llx_user`.`rowid` = `llx_actioncomm_resources`.`fk_element`
//        where `new` = 1
//        and `llx_actioncomm`.`active` = 1
//        and `llx_actioncomm`.`fk_user_author` = ".$id_usr."
//        and `llx_actioncomm`.`fk_user_author`<>`llx_actioncomm_resources`.`fk_element`
//        and `llx_actioncomm`.`percent` = 99
//        and (`llx_actioncomm`.`fk_user_valuer` is null or `llx_actioncomm`.`fk_user_valuer`< 0 or `llx_actioncomm`.`fk_user_valuer` > 0 and `llx_actioncomm`.`dtValidValuer` is not null) ";
//    $sql.=" union
//        select `llx_actioncomm`.`id`, `llx_actioncomm`.`percent`, `llx_actioncomm`.`code`, `llx_actioncomm`.`datea`, `llx_user`.`lastname`  from llx_actioncomm
//        left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = `llx_actioncomm`.`id`
//        left join `llx_user` on `llx_user`.`rowid` = `llx_actioncomm_resources`.`fk_element`
//        where `new` = 1
//        and `llx_actioncomm`.`active` = 1
//        and `llx_actioncomm`.`percent` = 99
//        and `llx_actioncomm`.`fk_user_valuer` = ".$id_usr."
//        and `llx_actioncomm`.`fk_user_author`<>`llx_actioncomm_resources`.`fk_element`
//        and `llx_actioncomm`.`dtValidValuer` is null";
    $res = $db->query($sql);
    echo '<pre>';
    var_dump($sql);
    echo '</pre>';
    die();
    if(!$res)
        dol_print_error($db);
    $actions = array();
    if($db->num_rows($res) > 0){
        while($obj = $db->fetch_object($res)){
            $date = new DateTime($obj->datep);
//            if($obj->when_show != 'oncreate')
//                die($date->format('Y-m-d'));
            if($obj->when_show == 'oncreate' || $obj->when_show == 'ondatep' && $date->format('Y-m-d') == date('Y-m-d') || empty($obj->when_show))
                $actions[$obj->id] = array('id'=>$obj->id, 'code'=>$obj->code, 'mentor'=>0,'datec'=>$date->format('d.m H:i'), 'lastname'=>$obj->lastname, 'percent'=>$obj->percent);
        }
    }
//    if(count($actions)>0) {
//        //Завантажую коментарі наставника
//        $sql = "select `llx_actioncomm`.`id`, `llx_actioncomm`.`code`, `llx_societe_action`.`dtChange`, `llx_actioncomm`.`fk_user_action`, `llx_actioncomm_resources`.`fk_element`, `llx_actioncomm`.fk_soc, `llx_user`.`lastname`
//        from `llx_societe_action`
//        inner join `llx_user` on `llx_user`.`rowid` = `llx_societe_action`.`id_mentor`
//        inner join `llx_actioncomm` on `llx_societe_action`.`action_id`= `llx_actioncomm`.`id`
//        left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm`= `llx_actioncomm`.`id`
//        where 1
//        and `llx_actioncomm`.`id` in (".implode(',', array_keys($actions)).")
//        and `llx_societe_action`.`new` = 1
//        and `llx_societe_action`.`active` = 1";
//
//        $res = $db->query($sql);
//        if (!$res)
//            dol_print_error($db);
//        $societelist = array(0);
//        if ($db->num_rows($res) > 0) {
//            while ($obj = $db->fetch_object($res)) {
//                if ($obj->fk_user_action == $id_usr || $obj->fk_element == $id_usr || in_societelist($id_usr, $obj->fk_soc)) {
//                    $date = new DateTime($obj->dtChange);
//                    $actions[$obj->id] = array('id' => $obj->id, 'code' => $obj->code, 'mentor' => 1, 'datec' => $date->format('d.m H:i'), 'lastname' => $obj->lastname, 'percent' => $obj->percent);
//                }
//            }
//        }
//    }

    if(count($actions)>0)
        return json_encode($actions);
    else
        return 0;

}
function in_societelist($id_usr, $socid){
    if(empty($_SESSION['societelist'][$id_usr])){
        global $db;
        $sql = "select llx_societe.rowid from `llx_user_regions`
            inner join llx_societe on region_id = `llx_user_regions`.fk_id
            where `llx_user_regions`.`fk_user` = $id_usr
            and `llx_user_regions`.`active`=1
            and llx_societe.active = 1";
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
        while($obj = $db->fetch_object($res)){
            $_SESSION['societelist'][$id_usr][]=$obj->rowid;
        }
    }
    return in_array($socid, $_SESSION['societelist'][$id_usr]);
}
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
    inner join (select fk_id from `llx_user_regions`where fk_user = ".$id_usr." and llx_user_regions.active = 1) as active_regions on active_regions.fk_id = `llx_societe`.region_id
    where 1";
        if($actioncode == "'AC_GLOBAL'" || $actioncode == "'AC_CURRENT'" || $user->login !="admin")
            $sql .=" and fk_user_author = ".$id_usr;
    $sql .= " and datep2 between date(Now()) and Now()";
    $sql .=" and llx_actioncomm.`percent` <> 100 and `llx_actioncomm`.`active` = 1";
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
        left join `llx_societe` on `llx_societe`.`rowid` = `llx_actioncomm`.`fk_soc`";
        if($actioncode == "'AC_GLOBAL'" || $actioncode == "'AC_CURRENT'")
            $sql.= " inner join `llx_actioncomm_resources` on `llx_actioncomm`.`id` =  `llx_actioncomm_resources`.`fk_actioncomm`";
        $sql.=" where 1
        and `llx_actioncomm`.`active` = 1";
        if($actioncode == "'AC_GLOBAL'" || $actioncode == "'AC_CURRENT'" )
            $sql .=" and `llx_actioncomm_resources`.fk_element = ".$id_usr;
        else
            $sql .=" and `llx_actioncomm`.`fk_user_author` = ".$id_usr;
        if($i<8) {
            $query_date = date("Y-m-d", (time()+3600*24*(-$i)));
            if($i!=7)
                $sql .= " and datep2 between '".$query_date . "' and date_add('" . $query_date . "', interval 1 day)";
            else
                $sql .= " and datep2 between date_add('" . date("Y-m-d") . "', interval -7 day) and '".date("Y-m-d") . "'";
        }else {
                $sql .= " and datep2 between date_add('" . date("Y-m-d") . "', interval -31 day) and '" . date("Y-m-d") . "'";
        }
        $sql .=" and percent = 100";
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
        left join `llx_actioncomm_resources` on `llx_actioncomm`.`id` =  `llx_actioncomm_resources`.`fk_actioncomm`
        where 1";
        if($actioncode == "'AC_GLOBAL'" || $actioncode == "'AC_CURRENT'" || $user->login !="admin")
            $sql .="         and (case when `llx_actioncomm_resources`.fk_element is null then `llx_actioncomm`.fk_user_author else `llx_actioncomm_resources`.fk_element end = ".$id_usr.")";
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
        $sql.=" and `llx_actioncomm`.`active` = 1";
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