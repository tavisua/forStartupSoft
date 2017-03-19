<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 03.01.2016
 * Time: 8:17
 */
//die('Зачекайте хвильку, зараз запрацює');

require $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';

require_once $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/comm/action/class/actioncomm.class.php';
unset($_SESSION['assignedtouser']);
//var_dump($token = dol_hash(uniqid(mt_rand(),TRUE)));
//die();
//echo '<pre>';
//var_dump($_REQUEST);
//echo '</pre>';
//die();
$table = ShowTask();

$Title = $langs->trans('CurrentTask');
llxHeader("",$Title,"");
print_fiche_titre($Title);
$sql = "select lastname from llx_user where rowid = ";
if(!isset($_GET['id_usr'])) {
    $sql .= $user->id;
    $id_usr = $user->id;
}else {
    $sql .= $_GET['id_usr'];
    $id_usr = $_GET['id_usr'];
}
$res = $db->query($sql);
if(!$res)
    dol_print_error($db);
$obj = $db->fetch_object($res);
$username = $obj->lastname;
//var_dump(in_array('purchase', array($user->respon_alias, $user->respon_alias2))?'purchase':'sale');
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/responsibility/sale/current/header.php';
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/responsibility/sale/current/task.php';
//llxFooter();
llxPopupMenu();
return;


function ShowTask(){
    global $db, $user;
    //завантажую ІД задач
   $sql = "select `llx_actioncomm`.`id`, `llx_actioncomm`.`new`, `llx_actioncomm`.`fk_user_author`, `llx_actioncomm`.`fk_groupoftask`
        from `llx_actioncomm`
        where fk_action in
              (select id from `llx_c_actioncomm`
              where `code` in ('AC_CURRENT'))";
        if(isset($_REQUEST['filterdatas'])&&!empty($_REQUEST['filterdatas'])){
            if(strpos($_REQUEST['filterdatas'],'status')){
                $status = substr($_REQUEST['filterdatas'], strpos($_REQUEST['filterdatas'], ':"')+2);
                $status = substr($status,0, strlen($status)-2);
//                var_dump($status);
//                die();
                switch($status) {
                    case 'ActionNotRunning': {
                        $sql .= " and percent = -1";
                    }
                        break;
                    case 'ActionRunningNotStarted': {
                        $sql .= " and percent = 0";
                    }
                        break;
                    case 'ActionRunningShort': {
                        $sql .= " and (percent between 1 and 99)";
                    }
                        break;
                    case 'ActionDoneShort': {
                        $sql .= " and percent = 100";
                    }
                        break;
                }
            }else
                $sql.=" and percent <> 100";
    }else
        $sql.=" and percent <> 100";
    $sql.=" 
              and active = 1";

//echo '<pre>';
//var_dump($_REQUEST['filterdatas']);
////var_dump($sql);
//echo '</pre>';
//die();


    if(isset($_REQUEST["filterdatas"])&&!empty($_REQUEST["filterdatas"])){
        if(!empty($_REQUEST["filterdatas"]))
            $filter = (array)json_decode($_REQUEST['filterdatas']);
//        var_dump($filter);
//        die();
//        switch($_POST["datetype"]){
//            case 'execdate':{
//                $sql.=" and date(datep2) ";
//            }break;
//            case 'prepareddate':{
//                $sql.=" and date(datepreperform) ";
//            }break;
//            case 'daterecord':{
//                $sql.=" and date(datec) ";
//            }break;
//            case 'confirmdate':{
//                $sql.=" and date(dateconfirm) ";
//            }
//        }
//        $sql.=' in('.$_POST['filterdatas'].')';
        foreach(array_keys($filter) as $key){
            if(in_array($key, array('execdate','prepareddate','daterecord','confirmdate'))) {//Фільтр дат
                switch ($key) {
                    case 'execdate': {
                        $sql .= " and date(datep) ";
                    }
                        break;
                    case 'prepareddate': {
                        $sql .= " and date(datepreperform) ";
                    }
                        break;
                    case 'daterecord': {
                        $sql .= " and date(datec) ";
                    }
                        break;
                    case 'confirmdate': {
                        $sql .= " and date(dateconfirm) ";
                    }
                        break;
                }
                $sql .= ' in(' . $filter[$key] . ')';
            }else{
                switch($key){
                    case 'c_subdiv_id':{
                        $sql_tmp = "select `llx_actioncomm`.id from `llx_actioncomm`
                            inner join `llx_user` on `llx_actioncomm`.`fk_user_author` = `llx_user`.rowid
                            where 1 and `llx_actioncomm`.`code` = 'AC_CURRENT'
                            and`llx_user`.`subdiv_id` = ".$filter[$key]."
                            and `llx_actioncomm`.percent <> 100
                            and `llx_actioncomm`.`active` = 1";
                    }break;
                    case 'p_subdiv_id':{
//                        $sql_tmp = "select `llx_actioncomm`.id from `llx_actioncomm_resources`
//                            inner join `llx_actioncomm` on `llx_actioncomm`.id = `llx_actioncomm_resources`.`fk_actioncomm`
//                            inner join `llx_user` on `llx_actioncomm_resources`.`fk_element` = `llx_user`.rowid
//                            where 1 and `llx_actioncomm`.`code` = 'AC_GLOBAL'
//                            and`llx_user`.`subdiv_id` = ".$filter[$key]."
//                            and `llx_actioncomm`.percent <> 100
//                            and `llx_actioncomm`.`active` = 1";
                        $sql_tmp = "select distinct `llx_actioncomm`.id from `llx_actioncomm`
                                    left join `llx_actioncomm_resources` on `llx_actioncomm`.id = `llx_actioncomm_resources`.`fk_actioncomm`
                                    inner join `llx_user` on case when `llx_actioncomm_resources`.`fk_element` is null then `llx_actioncomm`.`fk_user_author` else `llx_actioncomm`.`fk_user_author` end = `llx_user`.rowid
                                    where 1 and `llx_actioncomm`.`code` = 'AC_CURRENT'
                                    and`llx_user`.`subdiv_id` =  ".$filter[$key]."
                                    and `llx_actioncomm`.percent <> 100
                                    and `llx_actioncomm`.`active` = 1";
                    }break;
                    case 'customer': {
                        $sql .= " and `fk_user_author` = ".$filter[$key];
                    }break;
                    case 'groupoftaskID': {
                        $sql .= " and fk_groupoftask = ".$filter[$key];
                        $sql .= " and `llx_actioncomm`.`active` = 1
                                  and `llx_actioncomm`.`percent` <> 100";
                    }break;
                    case 'performer':{
//                        $sql_tmp = "select `llx_actioncomm`.id from `llx_actioncomm_resources`
//                            inner join `llx_actioncomm` on `llx_actioncomm`.id = `llx_actioncomm_resources`.`fk_actioncomm`
//                            where `llx_actioncomm_resources`.`fk_element` = ".$filter[$key]."
//                            and `llx_actioncomm`.percent <> 100
//                            and `llx_actioncomm`.`active` = 1";
//                        var_dump($filter[$key]);
//                        die();
                        if(!empty($filter[$key])) {
                            $sql_tmp = "";
                            if ($filter[$key] != -1)
                                $sql_tmp = "select distinct `llx_actioncomm`.id from `llx_actioncomm`
                                        left join `llx_actioncomm_resources` on `llx_actioncomm`.id = `llx_actioncomm_resources`.`fk_actioncomm`
                                        where 1
                                        and case when `llx_actioncomm_resources`.`fk_element` is null then `llx_actioncomm`.`fk_user_author` else `llx_actioncomm_resources`.`fk_element` end  = " . $filter[$key] . "
                                        and `llx_actioncomm`.percent <> 100
                                        and `llx_actioncomm`.`active` = 1";
                            else {
                                $sql_tmp = "select rowid from llx_user
                                            where subdiv_id = " . $user->subdiv_id . "
                                            and active = 1";
                                $res = $db->query($sql_tmp);
                                $users_id = array(0);
                                while ($obj = $db->fetch_object($res)) {
                                    $users_id[] = $obj->rowid;
                                }
                                $sql_tmp = "select distinct `llx_actioncomm`.id from `llx_actioncomm`
                                        left join `llx_actioncomm_resources` on `llx_actioncomm`.id = `llx_actioncomm_resources`.`fk_actioncomm`
                                        where 1
                                        and case when `llx_actioncomm_resources`.`fk_element` is null then `llx_actioncomm`.`fk_user_author` else `llx_actioncomm_resources`.`fk_element` end  in (" . implode(',', $users_id) . ")
                                        and `llx_actioncomm`.percent <> 100
                                        and `llx_actioncomm`.`active` = 1";
                            }
                        }

//                        echo '<pre>';
//                        var_dump($sql_tmp);
//                        echo '</pre>';
//                        die('');
                    }break;
                }
                if(in_array($key,array('p_subdiv_id','c_subdiv_id','performer'))&&!empty($filter[$key])){//Фільтр по підрозділам замовника, виконавця та по виконавцю
//                    die($key);
                    $res_tmp = $db->query($sql_tmp);
                    $ID = array(0);
                    while($obj = $db->fetch_object($res_tmp)){
                        $ID[]=$obj->id;
                    }
                    $sql.=" and `llx_actioncomm`.`id` in (".implode(',',$ID).")";
                }
                if(in_array($key,array('lastaction','futureaction'))){//остання і майбутня дія відповідального
                    $sql_tmp = "select `llx_actioncomm`.`id` from `llx_societe_action`
                        inner join `llx_actioncomm` on `llx_actioncomm`.`id` = `llx_societe_action`.`action_id`
                        where 1
                        and dtChange in (".$filter[$key].")
                        and `llx_actioncomm`.`code` = 'AC_CURRENT'
                        and `llx_actioncomm`.`active` = 1
                        and `llx_actioncomm`.`percent` <> 100";
                    $res_tmp = $db->query($sql_tmp);
                    $ID = array(0);
                    while($obj = $db->fetch_object($res_tmp)){
                        $ID[]=$obj->id;
                    }
                    $sql.=" and `llx_actioncomm`.`id` in (".implode(',',$ID).")";
                }
            }
        }
    }
    if(isset($_REQUEST['autorefresh'])&&$_REQUEST['autorefresh'] == '1'){
        $sql.=' and `llx_actioncomm`.percent not in (99,100)';
    }
    $sql.=' and `llx_actioncomm`.percent <> -100';

    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
//    die($sql);
    unset($taskID);
    unset($taskAuthor);
    unset($taskSociete);
    $taskID[] = 0;
    $Actions = new ActionComm($db);
    while($obj = $db->fetch_object($res)){
        $taskID[]=$obj->id;
        $taskAuthor[$obj->id] = $obj->fk_user_author;
        if($obj->fk_groupoftask == 10) {
            $chainaction = implode(',',$Actions->GetChainActions($obj->id));
            $sql = "select fk_soc from llx_actioncomm where id in ($chainaction) and active = 1 and fk_soc is not null";
            $res_tmp = $db->query($sql);
            if(!$res_tmp)
                dol_print_error($db);

            if($res_tmp->num_rows) {
                $obj_tmp = $db->fetch_object($res_tmp);
                $taskSociete[$obj->id] = $obj_tmp->fk_soc;
//                if($obj->id == 316659){
//                    die($chainaction);
//                }
            }
        }
    }
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();

    //завантажую ІД пов'язаних з задачами користувачів
    $sql = "select fk_actioncomm, fk_element from llx_actioncomm_resources where fk_actioncomm in (".implode(",", $taskID).")";
//    die($sql);
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
    unset($assignedUser);
    if($db->num_rows($res) <=1){
        $obj = $db->fetch_object($res);
        $assignedUser[$obj->fk_actioncomm]=$obj->fk_element;
    }else {
        while($obj = $db->fetch_object($res)) {
            //Закоментував строку, тому-що при фільтрації по виконавцю не відображаються завдання, що поставлені собі
//            if($taskAuthor[$obj->fk_actioncomm] != $obj->fk_element){
                if(empty($assignedUser[$obj->fk_actioncomm]))
                    $assignedUser[$obj->fk_actioncomm] = $obj->fk_element;
                else
                    $assignedUser[$obj->fk_actioncomm] .= ','.$obj->fk_element;
//            }
        }
    }
//    echo '<pre>';
//    var_dump($assignedUser);
//    echo '</pre>';
////    var_dump($user->id, 'userid');
//    die();
    if(count($taskID)>0) {

        $sql = "select `llx_societe_action`.`action_id` as rowid, max(`llx_societe_action`.`dtChange`) dtChange from `llx_societe_action`
        where 1 ";
        $sql .= " and `llx_societe_action`.`action_id` in (" . implode(',', $taskID) . ")";
        $sql .= "    and `llx_societe_action`.active = 1
        group by `llx_societe_action`.`action_id`;";
//  die($sql);
        $res = $db->query($sql);
        if (!$res) {
            dol_print_error($db);
        }
        if ($db->num_rows($res) > 0) {
            while ($row = $db->fetch_object($res)) {
                if (!isset($lastaction[$row->rowid])) {
                    $date = new DateTime($row->dtChange);
                    $lastaction[$row->rowid] = $date->format('d.m.y');
                }
            }
        }
    }
//    echo '<pre>';
//    var_dump($lastaction);
//    echo '</pre>';
//    die();
    //Завантажую завдання
    $sql = "select id, note, new, confirmdoc, entity, `datec`, datep2, datelastaction, planed_cost, fact_cost,motivator, demotivator, datefutureaction, round((UNIX_TIMESTAMP(datep2)-UNIX_TIMESTAMP(datep))/60,0) iMinute, `dateconfirm`,`datepreperform`, fk_order_id, period, `percent`, `llx_c_groupoftask`.`name` groupoftask, fk_groupoftask
    from `llx_actioncomm`
    left join llx_c_groupoftask on `llx_c_groupoftask`.`rowid` = fk_groupoftask
    where id in (".implode(",", $taskID).")";
    if(isset($_REQUEST['autorefresh'])&&$_REQUEST['autorefresh'] == '1') {
        $sql .= " order by datep desc";
    }else{
        $sql.=" order by datep asc";
    }

//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
    $table = '<tbody id="reference_body">';
    $tmp_user = new User($db);
    global $langs;
    $numrow = 0;
    $Actions = new ActionComm($db);
    if(!isset($_GET['id_usr']))
        $user_id = $user->id;
    else
        $user_id = $_GET['id_usr'];
    $performersID = array();
//    var_dump($_GET['performer']);
//    die();
    if(isset($_GET['performer']) && !empty($_GET['performer']) || isset($filter['performer'])) {//If set performer filter
        if($_GET['performer'] == '-1'||$filter['performer'] == '-1'){
            $sql = "select rowid from llx_user
                inner join (select subdiv_id from llx_user where rowid = ".$user_id.") subdiv on subdiv.subdiv_id = llx_user.subdiv_id
                where 1
                and llx_user.active = 1";
            $resPerformes = $db->query($sql);
            if(!$resPerformes)
                dol_print_error($db);
            while($obj = $db->fetch_object($resPerformes)){
                $performersID[]=$obj->rowid;
            }
        }
    }
//    echo '<pre>';
//    var_dump($performersID);
//    echo '</pre>';
//    die();
    $p_subdivID = array();
    if(isset($_GET['p_subdiv_id']) && !empty($_GET['p_subdiv_id'])) {//If set performer filter
        $sql = "select `llx_actioncomm`.`id`, `llx_user`.`subdiv_id` from `llx_actioncomm`
            inner join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = `llx_actioncomm`.`id`
            inner join `llx_user` on `llx_user`.`rowid` = `llx_actioncomm_resources`.`fk_element`
            where 1
            and percent != 100
            and code = 'AC_CURRENT'
            and (`llx_actioncomm`.`fk_user_author` = ".$user_id." or `llx_actioncomm_resources`.`fk_element` = ".$user_id.")";
        $resSubdiv = $db->query($sql);
        while($obj = $db->fetch_object($resSubdiv)){
            $p_subdivID[$obj->id] = $obj->subdiv_id;
        }
    }
    $c_subdivID = array();
    if(isset($_GET['c_subdiv_id']) && !empty($_GET['c_subdiv_id'])) {//If set performer filter
        $sql = "select `llx_actioncomm`.`id`, `llx_user`.`subdiv_id` from `llx_actioncomm`
            inner join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = `llx_actioncomm`.`id`
            inner join `llx_user` on `llx_user`.`rowid` = `llx_actioncomm`.`fk_user_author`
            where 1
            and percent != 100
            and code = 'AC_CURRENT'
            and (`llx_actioncomm`.`fk_user_author` = ".$user_id." or `llx_actioncomm_resources`.`fk_element` = ".$user_id.")";
        $resSubdiv = $db->query($sql);
        while($obj = $db->fetch_object($resSubdiv)){
            $c_subdivID[$obj->id] = $obj->subdiv_id;
        }
    }

    while($obj = $db->fetch_object($res)) {
        $add = true;
//        if(!isset($_GET['performer'])&&count($performersID)<=1||isset($_GET['performer']) && (empty($_GET['performer']))) {
//            if ($taskAuthor[$obj->id] == $user_id) {
////            $add = true;
//                $add = !(empty($assignedUser[$obj->id]) && $obj->entity == 0);
//            } else {
//                $users = explode(',', $assignedUser[$obj->id]);
//                if(count($performersID) == 0)
//                    $add = in_array($user_id, $users);
//                else
//                    $add = count(array_intersect($performersID, $users))>0;
//            }
//        }elseif (count($performersID)>1){
//            $users = explode(',', $assignedUser[$obj->id]);
//            $add = count(array_intersect($performersID, $users))>0;
//
////            $add = in_array($user_id, $performersID);
////            if(458793 == $obj->id){
////                var_dump($performersID);
////                die();
////            }
//        }


        //Перевірка на фільтр по підрозділу-замовнику
        if(isset($_GET['c_subdiv_id']) && !empty($_GET['c_subdiv_id'])&&$add) {//If set performer filter
            $add = isset($c_subdivID[$obj->id])&&$c_subdivID[$obj->id] == $_GET['c_subdiv_id'];
        }
        //Перевірка на фільтр по замовнику
        if(isset($_GET['customer']) && !empty($_GET['customer'])&&$add) {//If set performer filter
//            $users = explode(',', $assignedUser[$obj->id]);
            $add =  $_GET['customer'] == $taskAuthor[$obj->id];
        }

        //Перевірка на фільтр по підрозділу-виконавцю
        if(isset($_GET['p_subdiv_id']) && !empty($_GET['p_subdiv_id'])&&$add) {//If set performer filter
            $add = isset($p_subdivID[$obj->id])&&$p_subdivID[$obj->id] == $_GET['p_subdiv_id'];
        }

        //Перевірка на фільтр по виконавцю
        $executers = array_keys($Actions->getExecuters($obj->id));
        if(count($performersID)){//Додаю виконавців
            foreach ($performersID as $item=>$value){
                if(!in_array($value, $executers))
                    $executers[]=$value;
            }

        }

        if(isset($_GET['performer']) && !empty($_GET['performer']) || isset($filter['performer']) && !empty($filter['performer'])&&$add) {//If set performer filter

            foreach ($executers as $key=>$value){
                if($value == $taskAuthor[$obj->id]) {
                    unset($executers[$key]);
                }
            }
//            if($obj->id == 455011){
//                echo '<pre>';
//                var_dump($taskAuthor[$obj->id] == $user_id && (array_search($user_id, $executers, true)!= false || $executers[0] == $user_id    || count($executers) == 0 || count(array_intersect($executers, $performersID))>0 ||
//                        count($performersID)==0 && $taskAuthor[$obj->id] == $user_id && in_array($filter['performer'], $executers)));
//                echo '</pre>';
//                die();
//            }
            $executers = array_values($executers);

//            if($filter['performer'] == $user_id){
//
//            }
            $add =  (array_search($user_id, $executers, true)!= false || count($executers) == 0 && $taskAuthor[$obj->id] == $user_id || count(array_intersect($executers, $performersID))>0 ||
                         in_array($filter['performer'], $executers) && (in_array($user_id, array($taskAuthor[$obj->id], $filter['performer'] ))));
//            $add = true;
//            echo '<pre>';
//            var_dump($filter['performer']);
//            echo '</pre>';
//            die();

//            if(115596 == $obj->id){
//                var_dump(array_search($user_id, $executers, true)!= false , count($executers) == 0 && $taskAuthor[$obj->id] == $user_id , count(array_intersect($executers, $performersID))>0 ,
//                    in_array($filter['performer'], $executers));
//                die();
//            }
        }else
            $add = in_array($user_id, $executers) || $user_id == $taskAuthor[$obj->id];

        //Перевірка на фільтр по групі завдань
        if($add && isset($_GET['groupoftaskID']) && !empty($_GET['groupoftaskID'])){
            $add = $_GET['groupoftaskID'] == $obj->fk_groupoftask;
        }
//        if($add && )

        if($add){

            $class = fmod($numrow++,2)==0?'impair':'pair';
            $datec = new DateTime($obj->datec);
            $table.='<tr id="tr'.$obj->id.'" class="'.$class.'">';
//            $table.='<td style="width:51px"></td>
//            <td style="width:51px"></td>';
            $table.='<td style="width:51px" class="small_size">'.$datec->format('d.m.y').'</td>';
            $tmp_user->fetch($taskAuthor[$obj->id]);
            $table.='
            <td style="width:100px">'.mb_strtolower($langs->trans(ucfirst($tmp_user->respon_alias)), 'UTF-8').'</td>
            <td style="width:100px">'.$tmp_user->lastname.'</td>';
            if(empty($assignedUser[$obj->id])){
                $table.='
                <td style="width:101px">'.mb_strtolower($langs->trans(ucfirst($tmp_user->respon_alias)), 'UTF-8').'</td>
                <td style="width:101px" id="id_usr'.$tmp_user->id.'" id_usr="'.$tmp_user->id.'" class="performer">'.$tmp_user->lastname.'</td>';
            }else{
                $users = explode(',',$assignedUser[$obj->id]);
                if(count($users) == 1)
                    $tmp_user->fetch($users[0]);
                else{
                    foreach ($users as $item){
                        if($item != $taskAuthor[$obj->id]) {
                            $tmp_user->fetch($item);
                            break;
                        }
                    }
                }
                $table.='<td style="width:101px">'.mb_strtolower($langs->trans(ucfirst($tmp_user->respon_alias)), 'UTF-8').'</td>
                <td style="width:101px" id="id_usr'.$tmp_user->id.'" id_usr="'.$tmp_user->id.'" class="performer">'.$tmp_user->lastname.'</td>';
            }
            $table.='<td style="width:81px">'.$obj->groupoftask.'</td>';
            $table.='<td style="width:101px">'.(mb_strlen($obj->note, 'UTF-8')>20?(mb_substr($obj->note, 0, 20, 'UTF-8').'<img id="prev' . $obj->id .'note" onclick="previewNote(' . $obj->id . ');" style="vertical-align: middle" title="Передивитись" src="/dolibarr/htdocs/theme/eldy/img/object-more.png">'):$obj->note).'</td>';
            $table.='<td style="width:101px">'.(empty($obj->confirmdoc)?'':$obj->confirmdoc).'</td>';
            if(!empty($obj->datepreperform)) {
                $predate = new DateTime($obj->datepreperform);
                $table .= '<td style="width:61px" class="small_size">'.$predate->format('d.m.y').'</td>';//попередньо виконати до
            }else{
                $table .= '<td style="width:61px"></td>';
            }
            $deadline = new DateTime($obj->datep2);
            $now = new DateTime(date('Y-m-d H:i:s'));
//            $mk_deadline = mktime($deadline->format('H'),$deadline->format('i'),$deadline->format('s'),$deadline->format('d'),$deadline->format('m'),$deadline->format('Y'));
            $dedline_class =  ($deadline>$now||$user->id != $tmp_user->id)?"":"overdue";
//            if(441539 == $obj->id){
//                var_dump($deadline>$now, $user->id , $tmp_user->id);
//
//                die();
//            }
            if(!$obj->entity)
                $table.='<td style="width:53px" class="small_size '.$dedline_class.'">'.$deadline->format('d.m.y').'</br>'.$deadline->format('H:i').'</td>';
            else
                $table.='<td style="width:53px" class="small_size '.$dedline_class.'">'.$deadline->format('d.m.y').'</td>';
            if(!empty($obj->dateconfirm)) {
                $dateconfirm = new DateTime($obj->dateconfirm);
                $table .= '<td style="width:51px" class="small_size">' . $dateconfirm->format('d.m.y') . '</br>' . $dateconfirm->format('H:i') . '</td>';
            }else {
                if($tmp_user->id == $user->id)
                    $table .= '<td style="width:51px; text-align: center"><img src="/dolibarr/htdocs/theme/eldy/img/uncheck.png" onclick="ConfirmReceived(' . $obj->id . ');" id="confirm' . $obj->id . '"></td>';
                else
                    $table .= '<td style="width:51px; text-align: center">&nbsp;</td>';
            }
            //Дії виконавця
//            $lastaction = $Actions->GetLastAction($obj->id, 'datep');
            if(empty($obj->datelastaction)){
                $lastaction_value = '<img src="/dolibarr/htdocs/theme/eldy/img/object_action.png">';
            }else{
                $date = new DateTime($obj->datelastaction);
                $lastaction_value = $date->format('d.m.y').'</br>'.$date->format('H:i');
            }
            $link = '/dolibarr/htdocs/comm/action/chain_actions.php?action_id='.$obj->id.'&mainmenu='.$_REQUEST['mainmenu'];
            if(isset($taskSociete[$obj->id])&&!empty($taskSociete[$obj->id])){
                $link = "/dolibarr/htdocs/responsibility/sale/action.php?socid=".$taskSociete[$obj->id]."&idmenu=10425&mainmenu=area";
            }
            $table .= '<td style="width:76px;text-align: center"><a target="_blank" href="'.$link.'">'.$lastaction_value.'</a></td>';
            if(empty($obj->datefutureaction)){
                $value = '<img src="/dolibarr/htdocs/theme/eldy/img/object_action.png">';
            }else{
                $date = new DateTime($obj->datefutureaction);
                $value = $date->format('d.m.y').'</br>'.$date->format('H:i');
            }
            $table .= '<td style="width:76px;text-align: center"><a target="_blank" href="'.$link.'">'.$value.'</a></td>';
            $table .= '<td style="width:41px">'.$obj->iMinute.'</td>';
            //Дії наставника
            $table .= '<td style="width:76px;text-align: center"><img src="/dolibarr/htdocs/theme/eldy/img/object_action.png"></td>
                       <td style="width:76px;text-align: center"><img src="/dolibarr/htdocs/theme/eldy/img/object_action.png"></td>';
            //Період виконання
            $table .= '<td style="width:51px" class="small_size">'.mb_strtolower($langs->trans($obj->period), 'UTF-8').'</td>';
            //Статус завдання
            $date = new DateTime();
            $style = 'style="';
            if($obj->percent < 98) {
                if ($deadline < $date) {
                    $style = 'style="background:rgb(255, 0, 0)';
                } elseif ($deadline == $date) {
                    $style = 'style="background:rgb(0, 255, 0)';
                }
                if ($obj->percent == "-1")
                    $status = 'ActionNotRunning';
                elseif ($obj->percent == 0)
                    $status = 'ActionRunningNotStarted';
                elseif ($obj->percent > 0 && $obj->percent <= 98)
                    $status = 'ActionRunningShort';
                else
                    $status = 'ActionDoneShort';
            }
            if($obj->percent <= 99)
                $table .= '<td '.$style.'; width:51px; text-align: center;" class="small_size">'.($obj->percent <= 98?($langs->trans($status)):'<img src="theme/eldy/img/BWarning.png" title="Задачу виконано" style=width: 50px;">').'</td>';
            else
                $table .= '<td '.$style.'; width:51px; text-align: center;" class="small_size">'.($obj->percent <= 98?($langs->trans($status)):'<img src="theme/eldy/img/done.png" title="Задачу виконано" style=width: 50px;">').'</td>';

            if($taskAuthor[$obj->id] == $user->id && $obj->percent <= 99)
                 $table .= '<td style="width:51px; text-align: center"><img src="/dolibarr/htdocs/theme/eldy/img/uncheck.png" onclick="ConfirmExec(' . $obj->id . ');" id="confirm' . $obj->id . '"></td>';
            else
                $table .= '<td  style="width:51px">&nbsp;</td>';
            if(!empty($obj->planed_cost))
                $table .= '<td style="width:51px; text-align: center" class="planed_cost">'.$obj->planed_cost.'</td>';
            else
                $table .= '<td  style="width:51px">&nbsp;</td>';
            if(!empty($obj->fact_cost))
                $table .= '<td style="width:51px; text-align: center" class="fact_cost">'.$obj->fact_cost.'</td>';
            else
                $table .= '<td  style="width:51px">&nbsp;</td>';
            if(!empty($obj->motivator))
                $table .= '<td style="width:51px; text-align: center" class="motivator">'.$obj->motivator.'</td>';
            else
                $table .= '<td  style="width:51px">&nbsp;</td>';
            if(!empty($obj->demotivator))
                $table .= '<td style="width:51px; text-align: center" class="demotivator">'.$obj->demotivator.'</td>';
            else
                $table .= '<td  style="width:51px">&nbsp;</td>';
//            var_dump($obj->new);
//            die();
            if($taskAuthor[$obj->id] == $user->id && $obj->percent <= 99 && !empty($obj->new))
                $table .= '<td  style="width:25px"><img id="img_"'.$obj->id.' onclick="EditAction('.$obj->id.', null, '."'AC_CURRENT'".');" style="vertical-align: middle; cursor: pointer;" title="'.$langs->trans('Edit').'" src="/dolibarr/htdocs/theme/eldy/img/edit.png"></td>';
            else
                $table .= '<td  style="width:25px">&nbsp;</td>';
            if($obj->percent <= 99) {
                if (in_array($user->respon_alias, array('purchase', 'wholesale_purchase'))) {
                    if (empty($obj->fk_order_id))
                        $table .= '<td  style="width:25px;text-align: center"><img id="imgManager_' . $obj->id . '" onclick="DuplicateAction(' . $obj->id . ');" style="vertical-align: middle; cursor: pointer;" title="' . $langs->trans('Duplicate') . '" src="/dolibarr/htdocs/theme/eldy/img/object_duplicate.png"></td>';
                    else
                        $table .= '<td  style="width:25px"><img id="img_prep' . $obj->id . '" onclick="PrepareOrder(' . $obj->fk_order_id . ', ' . $obj->id . ');" style="vertical-align: middle; cursor: pointer;" title="' . $langs->trans('RedirectToOrder') . '" src="/dolibarr/htdocs/theme/eldy/img/addfile.png"></td>';
                } else
                    $table .= '<td  style="width:25px;text-align: center"><img id="imgManager_' . $obj->id . '" onclick="DuplicateAction(' . $obj->id . ');" style="vertical-align: middle; cursor: pointer;" title="' . $langs->trans('Duplicate') . '" src="/dolibarr/htdocs/theme/eldy/img/object_duplicate.png"></td>';
            }else
                $table .= '<td  style="width:25px">&nbsp;</td>';
            if($taskAuthor[$obj->id] == $user->id && $obj->percent <= 99 && !empty($obj->new))
                $table .= '<td  style="width:25px"><img title="Видалити завдання" src="/dolibarr/htdocs/theme/eldy/img/delete.png" onclick="ConfirmDelTask(' . $obj->id . ');" id="confirmdel' . $obj->id . '"></td>';
            else
                $table .= '<td  style="width:25px">&nbsp;</td>';
            $table.='</tr>';
        }
    }
    $table .= '</tbody>';
    return $table;
}