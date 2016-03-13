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


$table = ShowTask();
//echo '<pre>';
//var_dump($table);
//echo '</pre>';
//die();
$HourlyPlan = $langs->trans('CurrentTask');
llxHeader("",$HourlyPlan,"");
print_fiche_titre($langs->trans('CurrentTask'));
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/responsibility/'.$user->respon_alias.'/current/header.php';
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/responsibility/'.$user->respon_alias.'/current/task.php';
llxFooter();
return;

function ShowTask(){
    global $db, $user;
    //завантажую ІД задач
    $sql = "select `llx_actioncomm`.`id`, `llx_actioncomm`.`fk_user_author`
        from `llx_actioncomm`
        where fk_action in
              (select id from `llx_c_actioncomm`
              where `code` in ('AC_CURRENT'))
              and percent != 100 and datea is NULL";
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
//    die($sql);
    unset($taskID);
    unset($taskAuthor);
    $taskID[] = 0;
    while($obj = $db->fetch_object($res)){
        $taskID[]=$obj->id;
        $taskAuthor[$obj->id] = $obj->fk_user_author;
    }

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
            if($taskAuthor[$obj->fk_actioncomm] != $obj->fk_element){
                if(empty($assignedUser[$obj->fk_actioncomm]))
                    $assignedUser[$obj->fk_actioncomm] = $obj->fk_element;
                else
                    $assignedUser[$obj->fk_actioncomm] .= ','.$obj->fk_element;
            }
        }
    }
//    echo '<pre>';
//    var_dump($assignedUser);
//    echo '</pre>';
//    var_dump($user->id, 'userid');
//    die();
    //Завантажую завдання
    $sql = "select id, note, confirmdoc, `datec`, datep2, round((UNIX_TIMESTAMP(datep2)-UNIX_TIMESTAMP(datep))/60,0) iMinute, `dateconfirm`, fk_order_id, period, `percent`, `llx_c_groupoftask`.`name` groupoftask
    from `llx_actioncomm`
    left join llx_c_groupoftask on `llx_c_groupoftask`.`rowid` = fk_groupoftask
    where id in (".implode(",", $taskID).")
    order by datep asc";
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
    while($obj = $db->fetch_object($res)){
        $add = false;
        if($taskAuthor[$obj->id] == $user->id)
            $add = true;
        else{
            $users = explode(',',$assignedUser[$obj->id]);
            $add = in_array($user->id, $users);
        }
        if($add){
            $class = fmod($numrow++,2)==0?'impair':'pair';
            $datec = new DateTime($obj->datec);
            $table.='<tr id="tr'.$obj->id.'" class="'.$class.'">';
//            $table.='<td style="width:51px"></td>
//            <td style="width:51px"></td>';
            $table.='<td style="width:51px" class="small_size">'.$datec->format('d.m.y').'</td>';
            $tmp_user->fetch($taskAuthor[$obj->id]);
            $table.='
            <td style="width:103px">'.mb_strtolower($langs->trans(ucfirst($tmp_user->respon_alias)), 'UTF-8').'</td>
            <td style="width:103px">'.$tmp_user->lastname.'</td>';
            if(empty($assignedUser[$obj->id])){
                $table.='
                <td style="width:102px">'.mb_strtolower($langs->trans(ucfirst($tmp_user->respon_alias)), 'UTF-8').'</td>
                <td style="width:102px">'.$tmp_user->lastname.'</td>';
            }else{
                $users = explode(',',$assignedUser[$obj->id]);
                $tmp_user->fetch($users[0]);
                $table.='<td style="width:102px">'.mb_strtolower($langs->trans(ucfirst($tmp_user->respon_alias)), 'UTF-8').'</td>
                <td style="width:102px">'.$tmp_user->lastname.'</td>';
            }
            $table.='<td style="width:81px">'.$obj->groupoftask.'</td>';
            $table.='<td style="width:101px">'.$obj->note.'</td>';
            $table.='<td style="width:81px">'.(empty($obj->confirmdoc)?'':$obj->confirmdoc).'</td>';
            $table.='<td style="width:61px"></td>';
            $deadline = new DateTime($obj->datep2);
            $table.='<td style="width:51px" class="small_size">'.$deadline->format('d.m.y').'</br>'.$deadline->format('H:i').'</td>';
            if(!empty($obj->dateconfirm)) {
                $dateconfirm = new DateTime($obj->dateconfirm);
                $table .= '<td style="width:51px" class="small_size">' . $dateconfirm->format('d.m.y') . '</br>' . $dateconfirm->format('H:i') . '</td>';
            }else
                $table .= '<td style="width:51px; text-align: center"><img src="/dolibarr/htdocs/theme/eldy/img/uncheck.png" onclick="ConfirmReceived('.$obj->id.');" id="confirm'.$obj->id.'"></td>';
            //Дії виконавця
            $lastaction = $Actions->GetLastAction($obj->id, 'datep');
            if(empty($lastaction)){
                $lastaction = '<img src="/dolibarr/htdocs/theme/eldy/img/object_action.png">';
            }else{
                $date = new DateTime($lastaction);
                $lastaction = $date->format('d.m.Y');
            }
            $table .= '<td style="width:76px"><a href="/dolibarr/htdocs/comm/action/chain_actions.php?action_id='.$obj->id.'&mainmenu=current_task">'.$lastaction.'</a></td>';
            $table .= '<td style="width:76px"><img src="/dolibarr/htdocs/theme/eldy/img/object_action.png"></td>';
            $table .= '<td style="width:41px">'.$obj->iMinute.'</td>';
            //Дії наставника
            $table .= '<td style="width:76px"><img src="/dolibarr/htdocs/theme/eldy/img/object_action.png"></td><td style="width:76px"><img src="/dolibarr/htdocs/theme/eldy/img/object_action.png"></td>';
            //Період виконання
            $table .= '<td style="width:51px" class="small_size">'.mb_strtolower($langs->trans($obj->period), 'UTF-8').'</td>';
            //Статус завдання
            $date = new DateTime();
            $style = 'style="';
            if($deadline<$date){
                $style = 'style="background:rgb(255, 0, 0)';
            }elseif($deadline==$date){
                $style = 'style="background:rgb(0, 255, 0)';
            }
            if($obj->percent == "-1")
                $status='ActionNotRunning';
            elseif($obj->percent == 0)
                $status='ActionRunningNotStarted';
            elseif($obj->percent > 0 && $obj->percent < 100)
                $status='ActionRunningShort';
            else
                $status='ActionDoneShort';
            $table .= '<td '.$style.'; width:51px" class="small_size">'.$langs->trans($status).'</td>';
            $table .= '<td  style="width:51px">&nbsp;</td>';
            $table .= '<td  style="width:25px"><img id="img_"'.$obj->id.' onclick="EditAction('.$obj->id.');" style="vertical-align: middle; cursor: pointer;" title="'.$langs->trans('Edit').'" src="/dolibarr/htdocs/theme/eldy/img/edit.png"></td>';
            if($user->respon_alias == 'purchase'){
                if(empty($obj->fk_order_id))
                    $table.='<td style="width:25px"></td>';
                else
                    $table .= '<td  style="width:25px"><img id="img_prep"'.$obj->id.' onclick="PrepareOrder('.$obj->fk_order_id.');" style="vertical-align: middle; cursor: pointer;" title="'.$langs->trans('RedirectToOrder').'" src="/dolibarr/htdocs/theme/eldy/img/addfile.png"></td>';
            }
            $table.='</tr>';
        }
    }
    $table .= '</tbody>';
    return $table;
}