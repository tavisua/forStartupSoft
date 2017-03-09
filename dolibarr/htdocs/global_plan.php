<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 03.01.2016
 * Time: 8:17
 */
require $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/comm/action/class/actioncomm.class.php';
unset($_SESSION['assignedtouser']);
//if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'getlastdate'){
//    $Actions = new ActionComm($db);
//    echo '<pre>';
//    var_dump($Actions->GetFutureActionDate($_REQUEST['action_id']));
//    echo '</pre>';
//    die();
//}
//echo '<pre>';
//var_dump($_REQUEST);
//echo '</pre>';
//die();
//echo '<pre>';
//var_dump(json_decode($_REQUEST["filterdatas"]));
//echo '</pre>';
//die();
$table = ShowTask();

$HourlyPlan = $langs->trans('GlobalTask');
llxHeader("",$HourlyPlan,"");
print_fiche_titre($langs->trans('GlobalTask'));
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
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/responsibility/sale/global/header.php';
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/responsibility/sale/global/task.php';
llxPopupMenu();
//llxFooter();
return;

function ShowTask(){

    global $db, $user;
    //завантажую ІД задач
    $sql = "select `llx_actioncomm`.`id`, `llx_actioncomm`.`fk_user_author`
        from `llx_actioncomm`
        where fk_action in
              (select id from `llx_c_actioncomm`
              where `code` in ('AC_GLOBAL'))";
        if(isset($_REQUEST['status'])&&!empty($_REQUEST['status'])) {
        switch($_REQUEST['status']){
            case 'ActionNotRunning':{
                $sql.=" and percent = -1";
            }break;
            case 'ActionRunningNotStarted':{
                $sql.=" and percent = 0";
            }break;
            case 'ActionRunningShort':{
                $sql.=" and (percent between 1 and 99)";
            }break;
            case 'ActionDoneShort':{
                $sql.=" and percent = 100";
            }break;
        }
    }else
        $sql.=" and percent <> 100";
    $sql.=" and (entity = 1)
              and active = 1";

//and (entity = 1 and `llx_actioncomm`.`fk_user_author` = ".$user->id." or entity = 0 and `llx_actioncomm`.`fk_user_author` <> ".$user->id.")
    if(isset($_POST["filterdatas"])&&!empty($_POST["filterdatas"])){
        $filter = (array)json_decode($_REQUEST['filterdatas']);
//        var_dump(array_keys($filter), $_REQUEST['filterdatas']);
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
                        $sql .= " and date(datep2) ";
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
                            where 1 and `llx_actioncomm`.`code` = 'AC_GLOBAL'
                            and`llx_user`.`subdiv_id` = ".$filter[$key]."
                            and `llx_actioncomm`.percent <> 100
                            and `llx_actioncomm`.`active` = 1";
                    }break;
                    case 'p_subdiv_id':{
                        $sql_tmp = "select `llx_actioncomm`.id from `llx_actioncomm_resources`
                            inner join `llx_actioncomm` on `llx_actioncomm`.id = `llx_actioncomm_resources`.`fk_actioncomm`
                            inner join `llx_user` on case when `llx_actioncomm_resources`.`fk_element` is null then `llx_actioncomm`.`fk_user_author` else `llx_actioncomm`.`fk_user_author` end = `llx_user`.rowid
                            where 1 and `llx_actioncomm`.`code` = 'AC_GLOBAL'
                            and`llx_user`.`subdiv_id` = ".$filter[$key]."
                            and `llx_actioncomm`.percent <> 100
                            and `llx_actioncomm`.`active` = 1";
                    }break;
                    case 'customer': {
                        $sql .= " and `fk_user_author` = ".$filter[$key];
                    }break;
                    case 'groupoftaskID': {
                        $sql .= " and fk_groupoftask = ".$filter[$key];
                    }break;
                    case 'performer':{
//                        $sql_tmp = "select `llx_actioncomm`.id from `llx_actioncomm_resources`
//                            inner join `llx_actioncomm` on `llx_actioncomm`.id = `llx_actioncomm_resources`.`fk_actioncomm`
//                            where `llx_actioncomm_resources`.`fk_element` = ".$filter[$key]."
//                            and `llx_actioncomm`.percent <> 100
//                            and `llx_actioncomm`.`active` = 1";

                        $sql_tmp = "select distinct `llx_actioncomm`.id from `llx_actioncomm`
                                    left join `llx_actioncomm_resources` on `llx_actioncomm`.id = `llx_actioncomm_resources`.`fk_actioncomm`
                                    where 1
                                    and `llx_actioncomm_resources`.`fk_element` = ".$filter[$key]."                                    
                                    and `llx_actioncomm`.percent <> 100
                                    and `llx_actioncomm`.`active` = 1";
                    }break;
                }
                if(in_array($key,array('p_subdiv_id','c_subdiv_id','performer'))){//Фільтр по підрозділам замовника, виконавця та по виконавцю
                    $res_tmp = $db->query($sql_tmp);
                    if(!$res_tmp)
                        dol_print_error($db);
//                    echo '<pre>';
//                    var_dump($sql_tmp);
//                    echo '</pre>';
//                    die();
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
                        and `llx_actioncomm`.`code` = 'AC_GLOBAL'
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
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
//    var_dump($sql);
//    die();
    unset($taskID);
    unset($taskAuthor);
    $taskID[] = 0;
    while($obj = $db->fetch_object($res)){
        $taskID[]=$obj->id;
        $taskAuthor[$obj->id] = $obj->fk_user_author;
    }

    //завантажую ІД пов'язаних з задачами користувачів
    $sql = "select fk_actioncomm, fk_element from llx_actioncomm_resources where fk_actioncomm in (".implode(",", $taskID).")";
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
//            if($taskAuthor[$obj->fk_actioncomm] != $obj->fk_element){
                if(empty($assignedUser[$obj->fk_actioncomm]))
                    $assignedUser[$obj->fk_actioncomm] = $obj->fk_element;
                else
                    $assignedUser[$obj->fk_actioncomm] .= ','.$obj->fk_element;
//            }
        }
    }
    if(count($taskID)>0) {

//        $sql = "select `llx_societe_action`.`action_id` as rowid, max(`llx_societe_action`.`dtChange`) dtChange from `llx_societe_action`
//        where 1 ";
//        $sql .= " and `llx_societe_action`.`action_id` in (" . implode(',', $taskID) . ")";
//        $sql .= "    and `llx_societe_action`.active = 1
//        group by `llx_societe_action`.`action_id`;";
////  die($sql);
//        $res = $db->query($sql);
//        if (!$res) {
//            dol_print_error($db);
//        }
//        if ($db->num_rows($res) > 0) {
//            while ($row = $db->fetch_object($res)) {
//                if (!isset($lastaction[$row->rowid])) {
//                    $date = new DateTime($row->dtChange);
//                    $lastaction[$row->rowid] = $date->format('d.m.y');
//                }
//            }
//        }
//        die('test');
    }
    //Завантажую завдання
    $sql = "select id, note, confirmdoc, entity, datelastaction,datefutureaction,planed_cost, fact_cost, motivator, demotivator, `datec`, datep2, round((UNIX_TIMESTAMP(datep2)-UNIX_TIMESTAMP(datep))/60,0) iMinute, `dateconfirm`, period, `percent`, `datepreperform`, `llx_c_groupoftask`.`name` groupoftask
    from `llx_actioncomm`
    left join llx_c_groupoftask on `llx_c_groupoftask`.`rowid` = fk_groupoftask
    where id in (".implode(",", $taskID).")
    order by datep asc";
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
//    var_dump($sql);
//    die();
    $table = '<tbody id="reference_body">';
    $tmp_user = new User($db);
    global $langs;
    $numrow = 0;
    $Actions = new ActionComm($db);
    if(!isset($_GET['id_usr']))
        $id_usr = $user->id;
    else
        $id_usr = $_GET['id_usr'];
    while($obj = $db->fetch_object($res)){
        $add = false;
        if($taskAuthor[$obj->id] ==$id_usr)
            $add = true;
        else{
            $users = explode(',',$assignedUser[$obj->id]);
            $add = in_array($id_usr, $users);

        }
        if(isset($_GET['performer']) && !empty($_GET['performer'])&&$add) {//If set performer filter
            $users = explode(',', $assignedUser[$obj->id]);
            $add =  in_array($_GET['performer'], $users) || (empty($assignedUser[$obj->id])&&$id_usr == $_GET['performer'] && $id_usr == $taskAuthor[$obj->id]);
//            if(20971 == $obj->id){
//                var_dump(count($users), $assignedUser[$obj->id], $_GET['performer']);
//                die();
//            }
        }
        if($add){
//            if(408515 == $obj->id){
//                echo '<pre>';
//                var_dump($obj);
//                echo '</pre>';
//                die();
//            }
            $class = fmod($numrow++,2)==0?'impair':'pair';
            $datec = new DateTime($obj->datec);
            $table.='<tr id="tr'.$obj->id.'" class="'.$class.'">';
//            $table.='<td style="width:51px"></td>
//            <td style="width:51px"></td>';
            $table.='<td style="width:50px" class="small_size">'.$datec->format('d.m.y').'</td>';
            $tmp_user->fetch($taskAuthor[$obj->id]);
            $table.='
            <td style="width:101px">'.mb_strtolower($langs->trans(ucfirst($tmp_user->respon_alias)), 'UTF-8').'</td>
            <td style="width:101px">'.$tmp_user->lastname.'</td>';
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
            $table.='<td style="width:101px">'.(mb_strlen($obj->note, 'UTF-8')>20?(mb_substr($obj->note, 0, 20).'<img id="prev' . $obj->id .'note" onclick="previewNote(' . $obj->id . ');" style="vertical-align: middle" title="Передивитись" src="/dolibarr/htdocs/theme/eldy/img/object-more.png">'):$obj->note).'</td>';
            $table.='<td style="width:100px">'.(empty($obj->confirmdoc)?'':$obj->confirmdoc).'</td>';
            if(!empty($obj->datepreperform)) {
                $predate = new DateTime($obj->datepreperform);
                $table .= '<td class="small_size">'.$predate->format('d.m.y').'</td>';//попередньо виконати до
            }else{
                $table .= '<td ></td>';
            }
            $deadline = new DateTime($obj->datep2);
            $now = new DateTime(date('Y-m-d H:i:s'));
//            $mk_deadline = mktime($deadline->format('H'),$deadline->format('i'),$deadline->format('s'),$deadline->format('d'),$deadline->format('m'),$deadline->format('Y'));
            $dedline_class =  ($deadline>$now||$user->id != $tmp_user->id)?"":"overdue";
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
                $lastaction_val = '<img src="/dolibarr/htdocs/theme/eldy/img/object_action.png">';
            }else{
                $date = new DateTime($obj->datelastaction);
                $lastaction_val = $date->format('d.m.y').'</br>'.$date->format('H:i');
            }
            $table .= '<td style="width:76px;text-align: center;"><a target="_blank" href="/dolibarr/htdocs/comm/action/chain_actions.php?action_id='.$obj->id.'&mainmenu=global_task">'.$lastaction_val.'</a></td>';
            if(empty($obj->datefutureaction)){
                $futureaction_val = '<img src="/dolibarr/htdocs/theme/eldy/img/object_action.png">';
            }else{
                $date = new DateTime($obj->datefutureaction);
                $futureaction_val = $date->format('d.m.y').'</br>'.$date->format('H:i');
            }            
            $table .= '<td style="width:76px;text-align: center;"><a target="_blank" href="/dolibarr/htdocs/comm/action/chain_actions.php?action_id='.$obj->id.'&mainmenu=global_task">'.$futureaction_val.'</a></td>';
            $table .= '<td style="width:43px;text-align: center;">'.$obj->iMinute.'</td>';
            //Дії наставника
            $table .= '<td style="width:76px;text-align: center"><img src="/dolibarr/htdocs/theme/eldy/img/object_action.png"></td><td style="width:76px;text-align: center"><img src="/dolibarr/htdocs/theme/eldy/img/object_action.png"></td>';
            //Період виконання
            $table .= '<td style="width:54px" class="small_size">'.mb_strtolower($langs->trans($obj->period), 'UTF-8').'</td>';
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
                elseif ($obj->percent > 0 && $obj->percent < 98)
                    $status = 'ActionRunningShort';
                else
                    $status = 'ActionDoneShort';
            }
            $table .= '<td '.$style.';text-align: center; width:51px" class="small_size">'.($obj->percent <= 98?($langs->trans($status)):'<img src="theme/eldy/img/BWarning.png" title="Задачу виконано" style=width: 50px;">').'</td>';


            if($taskAuthor[$obj->id] == $user->id)
                 $table .= '<td style="width:51px; text-align: center"><img src="/dolibarr/htdocs/theme/eldy/img/uncheck.png" onclick="ConfirmExec(' . $obj->id . ');" id="confirm' . $obj->id . '"></td>';
            else
                $table .= '<td  style="width:51px">&nbsp;</td>';
//            $table .= '<td  style="width:25px"><img id="img_"'.$obj->id.' onclick="EditAction('.$obj->id.');" style="vertical-align: middle; cursor: pointer;" title="'.$langs->trans('Edit').'" src="/dolibarr/htdocs/theme/eldy/img/edit.png"></td>';
//            $table .= '<td  style="width:25px"><img id="imgManager_'.$obj->id.'" onclick="RedirectAction('.$obj->id.');" style="vertical-align: middle; cursor: pointer;" title="'.$langs->trans('Redirect').'" src="/dolibarr/htdocs/theme/eldy/img/redirect.png"></td>';
            if(!empty($obj->planed_cost))
                $table .= '<td style="width:51px; text-align: center">'.$obj->planed_cost.'</td>';
            else
                $table .= '<td  style="width:51px">&nbsp;</td>';
            if(!empty($obj->fact_cost))
                $table .= '<td style="width:51px; text-align: center">'.$obj->fact_cost.'</td>';
            else
                $table .= '<td  style="width:51px">&nbsp;</td>';            
            if(!empty($obj->motivator))
                $table .= '<td style="width:51px; text-align: center">'.$obj->motivator.'</td>';
            else
                $table .= '<td  style="width:51px">&nbsp;</td>';
            if(!empty($obj->demotivator))
                $table .= '<td style="width:51px; text-align: center">'.$obj->demotivator.'</td>';
            else
                $table .= '<td  style="width:51px">&nbsp;</td>';
            if($taskAuthor[$obj->id] == $user->id)
                $table .= '<td  style="width:25px"><img title="Редагувати завдання" id="img_'.$obj->id.'" onclick="EditAction('.$obj->id.', null, '."'AC_GLOBAL'".');" style="vertical-align: middle; cursor: pointer;" title="'.$langs->trans('Edit').'" src="/dolibarr/htdocs/theme/eldy/img/edit.png"></td>';
            else
                $table .= '<td  style="width:25px">&nbsp;</td>';

//            $table .= '<td  style="width:25px"><img id="imgManager_"'.$obj->id.' onclick="RedirectAction('.$obj->id.');" style="vertical-align: middle; cursor: pointer;" title="'.$langs->trans('Redirect').'" src="/dolibarr/htdocs/theme/eldy/img/redirect.png"></td>';
            if($taskAuthor[$obj->id] == $user->id)
                $table .= '<td  style="width:25px"><img title="Видалити завдання" src="/dolibarr/htdocs/theme/eldy/img/delete.png" onclick="ConfirmDelTask(' . $obj->id . ');" id="confirm' . $obj->id . '"></td>';
            else
                $table .= '<td  style="width:25px">&nbsp;</td>';
            $table.='</tr>';
        }
    }
    $table .= '</tbody>';
    return $table;
}