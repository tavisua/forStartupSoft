<?php
require $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/main.inc.php';
if($_POST['action'] == 'setMentorJob'){
    saveMentorJob();
    exit();
}
if($_POST['action'] == 'setMentorDate'){
    saveMentorDate();
    exit();
}
require_once DOL_DOCUMENT_ROOT.'/core/modules/societe/modules_societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/societecontact_class.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/comm/action/class/actioncomm.class.php';

switch($_REQUEST['mainmenu']){
    case 'global_task':{
        $Action = 'GlobalAction';
        $Task   = 'GlobalTask';
        $actioncode = 'AC_GLOBAL';
    }break;
    case 'current_task':{
        $Action = 'CurrentAction';
        $Task   = 'CurrentTask';
        $actioncode = 'AC_CURRENT';
    }break;
    default:{
        $Action = 'Conversation';
        $Task   = '';
        $actioncode = 'AC_CONVERSATION';
    }
}
$author_id = getAuthorID($_GET['action_id']);
$Action = $langs->trans($Action);
llxHeader("",$Action,"");
print_fiche_titre($Action);
if($actioncode == 'AC_CONVERSATION') {
    $actiontabe = ShowConversation();
}else{
    $description = GetDescription($_GET['action_id']);
    $actiontabe = ShowActionTable();
}
$accessMentor = ValidAsseccMentor();
$contactdata = getContactData($author_id);
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/responsibility/sale/action/chain_action.html';
//include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/eldy/responsibility/sale/action/chain_action.html';
//llxFooter();
llxPopupMenu();
exit();
function getContactData($author_id){
    global $user,$db;
    $sql = "select llx_actioncomm.fk_user_author, `llx_actioncomm_resources`.`fk_element` from llx_actioncomm
        left join `llx_actioncomm_resources`on `fk_actioncomm` = `llx_actioncomm`.`id`
        where id = ".$_GET['action_id'];
    $sql .= " and llx_actioncomm.fk_user_author<>`llx_actioncomm_resources`.`fk_element`";
    $res = $db->query($sql);
//    var_dump($sql);
//    die();
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    $out = '';
//    require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
    $user_tmp = new User($db);
    if(!empty($author_id) && $user->id == $author_id) {
        $user_tmp->fetch($obj->fk_element);
    }else {
        $user_tmp->fetch($obj->fk_user_author);
    }
    $out='';
    if(!empty($user_tmp->lastname))
        $out .= '<b>Прізвище</b> '.$user_tmp->lastname.'</br></br>';
    if(!empty($user_tmp->office_phone)) {
        $userphone = str_replace('+','',$user_tmp->office_phone);
        $userphone = str_replace('(','',$userphone);
        $userphone = str_replace(')','',$userphone);
        $userphone = str_replace('-','',$userphone);
        $userphone = str_replace(' ','',$userphone);
        if(!empty($user_tmp->office_phone))
            $out .= '<b>Телефон</b> <a onclick="Call('.$userphone.', '."'users'".', '.$user_tmp->id.');">' . $user_tmp->office_phone . '</a> <a onclick="showSMSform('.$userphone.')"><img src="/dolibarr/htdocs/theme/eldy/img/object_sms.png"></a>'.'</br></br>';
        if(!empty($user_tmp->skype))
            $out .= '<b>Скайп</b> <a href="skype:'.$user_tmp->skype.'?call">'.$user_tmp->skype.'</a>';

    }
    if(empty($out))
        $out='замовник і виконавець - я';
    return $out;
}
function saveMentorDate(){
    global $db,$user;
    $date = new DateTime($_POST['value']);
    $sql = "update llx_societe_action set date_next_action_mentor='".
        $date->format('Y-m-d')."', id_mentor = ".$user->id.", dtMentorChange=Now() where rowid=".$_POST['rowid'];
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    echo 1;
}
function saveMentorJob(){
    global $db,$user;
    $sql = "update llx_societe_action set work_before_the_next_action_mentor = '".$db->escape($_POST['value']).
        "', dtMentorChange=Now(), id_mentor = ".$user->id.", new=1 where rowid=".$_POST['rowid'];
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    echo 1;
}
function ValidAsseccMentor(){
    global $db,$user;
    if($user->respon_alias == 'gen_dir')
        return true;
    else{
        $sql = "select `llx_actioncomm_resources`.`fk_element` from llx_actioncomm
            inner join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = `llx_actioncomm`.id
            where llx_actioncomm.id = ".$_GET['action_id'];
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
        if($db->num_rows($res)==0){
            return 0;
        }
        $obj = $db->fetch_object($res);
        $sql = "select fk_user from llx_user where llx_user.rowid = ".$obj->fk_element;
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
        $obj = $db->fetch_object($res);
        if($user->id == $obj->fk_user)
            return true;
        else
            return 0;
    }
}
function getAuthorID($action_id){
    global $db;
    $sql = "select `llx_actioncomm`.`fk_user_author` from `llx_actioncomm` where id = ".$action_id;
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    return $obj->fk_user_author;
}
function ShowConversation(){
    global $db, $langs, $conf;
    
}
function ShowActionTable(){
    global $db, $langs, $conf;
    $chain_actions = array();
    $chain_actions = GetChainActions($_GET['action_id']);
    $sql = 'select fk_parent, datep from `llx_actioncomm` where id in ('.implode(",", $chain_actions).') and fk_parent <> 0';
    $res = $db->query($sql);
    $nextaction = array();
    while($row = $db->fetch_object($res)){
        $nextaction[$row->fk_parent] = $row->datep;
    }

    $sql='select `llx_societe_action`.`rowid` as rowid, `llx_actioncomm`.`datep`, `llx_societe_action`.dtChange as `datec`, `llx_user`.lastname,
        concat(case when `llx_societe_contact`.lastname is null then "" else `llx_societe_contact`.lastname end,
        case when `llx_societe_contact`.firstname is null then "" else `llx_societe_contact`.firstname end) as contactname,
        TypeCode.code kindaction, `llx_societe_action`.`said`, `llx_societe_action`.`answer`,`llx_societe_action`.`argument`,
        `llx_societe_action`.`said_important`, `llx_societe_action`.`result_of_action`, `llx_societe_action`.`work_before_the_next_action`,`work_before_the_next_action_mentor` work_mentor,
        `llx_societe_action`.`date_next_action_mentor` date_mentor
        from `llx_actioncomm`
        inner join (select code, libelle label from `llx_c_actioncomm` where active = 1 and (type = "system" or type = "user")) TypeCode on TypeCode.code = `llx_actioncomm`.code
        left join `llx_societe_contact` on `llx_societe_contact`.rowid=`llx_actioncomm`.fk_contact
        left join `llx_societe_action` on `llx_actioncomm`.id = `llx_societe_action`.`action_id`
        left join `llx_user` on `llx_societe_action`.id_usr = `llx_user`.rowid
        where id in ('.implode(",", $chain_actions).') and `llx_societe_action`.`active` = 1 order by datep desc, datec desc';
//echo '<pre>';
//var_dump($sql);
//echo '</pre>';
//die();
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
    $out = '<tbody>';
//    var_dump($db->num_rows($res));
//    die();
    if($db->num_rows($res)==0){
        $out .= '<tr class="impair">
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 100px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 50px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="width: 35px" class="middle_size">&nbsp;</td>
            </tr>';
    }
    $num=0;

    while($row = $db->fetch_object($res)){
        $dtChange = new DateTime($row->datec);

        if(isset($nextaction[$row->rowid])) {
            $row->date_next_action = $nextaction[$row->rowid];
//            var_dump($nextaction[$row->rowid]);
//            die();
        }
//        var_dump($row->work_before_the_next_action);
//        die();
        $dtNextAction = new DateTime($row->date_next_action);
        $dtDateMentor = new DateTime($row->date_mentor);
        $iconitem='';
        $title='';
        switch($row->kindaction){
            case 'AC_GLOBAL':{
                $classitem = 'global_taskitem';
                $iconitem = 'object_global_task.png';
                $title=$langs->trans('ActionGlobalTask');
            }break;
            case 'AC_CURRENT':{
                $classitem = 'current_taskitem';
                $iconitem = 'object_current_task.png';
                $title=$langs->trans('ActionCurrentTask');
            }break;
            case 'AC_RDV':{
                $classitem = 'office_meetting_taskitem';
                $iconitem = 'object_office_meetting_task.png';
                $title=$langs->trans('ActionAC_RDV');
            }break;
            case 'AC_TEL':{
                $classitem = 'office_callphone_taskitem';
                $iconitem = 'object_call2.png';
                $title=$langs->trans('ActionAC_TEL');
            }break;
            case 'AC_DEP':{
                $classitem = 'departure_taskitem';
                $iconitem = 'object_departure_task.png';
                $title=$langs->trans('ActionDepartureMeeteng');
            }break;
        }
        $dateaction = new DateTime($row->datep);
        $out .= '<tr class="'.(fmod($num++, 2)==0?'impair':'pair').'">
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'dtAction" style="widtd: 80px" class="middle_size">'.(empty($row->datep)?'':($dateaction->format('d.m.y').'</br>'.$dateaction->format('H:i'))).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'dtChange" style="widtd: 80px" class="middle_size">'.(empty($row->datec)?'':$dtChange->format('d.m.y H:i:s')).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'lastname" style="widtd: 100px" class="middle_size">'.$row->lastname.'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'contactname" style="widtd: 80px" class="middle_size">'.$row->contactname.'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'kindaction" style="widtd: 50px; text-align: center;" class="middle_size" ><img src="/dolibarr/htdocs/theme/'.$conf->theme.'/img/'.$iconitem.'" title="'.$title.'"></td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'said" style="widtd: 80px" class="middle_size">'.(strlen($row->said)>20?mb_substr($row->said, 0, 20, 'UTF-8').'...<input type="hidden" value="'.$row->said.'" id="input_'.$row->rowid.'said">':$row->said).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'answer" style="widtd: 80px" class="middle_size">'.(strlen($row->answer)>20?mb_substr($row->answer, 0, 20, 'UTF-8').'...<input type="hidden" value="'.$row->answer.'" id="input_'.$row->rowid.'answer">':$row->answer).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'argument" style="widtd: 80px" class="middle_size">'.(strlen($row->argument)>20?mb_substr($row->argument, 0, 20, 'UTF-8').'...<input type="hidden" value="'.$row->argument.'" id="input_'.$row->rowid.'argument">':$row->argument).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'said_important" style="widtd: 80px" class="middle_size">'.(strlen($row->said_important)>20?mb_substr($row->said_important, 0, 20, 'UTF-8').'...<input type="hidden" value="'.$row->said_important.'" id="input_'.$row->rowid.'said_important>':$row->said_important).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'result_of_action" style="widtd: 80px" class="middle_size">'.(strlen($row->result_of_action)>20?mb_substr($row->result_of_action, 0, 20, 'UTF-8').'...<input type="hidden" value="'.$row->result_of_action.'" id="input_'.$row->rowid.'result_of_action">':$row->result_of_action).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'work_before_the_next_action" style="widtd: 80px" class="middle_size">'.(strlen($row->work_before_the_next_action)>20?mb_substr($row->work_before_the_next_action, 0, 20, 'UTF-8').'...<input type="hidden" value="'.$row->work_before_the_next_action.'" id="input_'.$row->rowid.'work_before_the_next_action">':$row->work_before_the_next_action).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'date_next_action" style="widtd: 80px" class="middle_size">'.(empty($row->date_next_action)?'':$dtNextAction->format('d.m.y H:i:s')).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'work_before_the_next_action_mentor" style="widtd: 80px" class="middle_size">'.(strlen($row->work_mentor)>20?mb_substr($row->work_mentor, 0, 20, 'UTF-8').'...<input type="hidden" value="'.$row->work_mentor.'" id="input_'.$row->rowid.'work_mentor">':$row->work_mentor).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'date_next_action_mentor" style="widtd: 80px" class="middle_size">'.(empty($row->date_mentor)?'':$dtDateMentor->format('d.m.y')).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'action" style="width: 35px" class="middle_size"><script>
                 var click_event = "/dolibarr/htdocs/societe/addcontact.php?action=edit&mainmenu=companies&rowid=1";
                </script>';
//            $out .= '<img id="img_1" "="" onclick="" style="vertical-align: middle" title="'.$langs->trans('AddSubAction').'" src="/dolibarr/htdocs/theme/eldy/img/Add.png">';
            $actioncode = '';
            switch($_REQUEST['mainmenu']){
                case 'current_task':{
                    $actioncode = "'AC_CURRENT'";
                }break;
                case 'global_task':{
                    $actioncode = "'AC_GLOBAL'";
                }
            }
            $out .= '<img id="img_1"  onclick="EditAction('.$_REQUEST['action_id'].','.$row->rowid.', '.$actioncode.');" style="vertical-align: middle; cursor: pointer;" title="'.$langs->trans('Edit').'" src="/dolibarr/htdocs/theme/eldy/img/edit.png">';
            $out .= '&nbsp;&nbsp;<img  onclick="DelAction('."'_".$row->rowid."'".');" style="vertical-align: middle; cursor: pointer;" title="'.$langs->trans('Delete').'" src="/dolibarr/htdocs/theme/eldy/img/delete.png">';
            $out .= '</td>
            </tr>';
    }
//        <th style="width: 80px" class="middle_size">Дата і час внесення</th>
//            <th style="width: 100px" class="middle_size">Хто від нас вносив</th>
//            <th style="width: 80px" class="middle_size">З ким діяли</th>
//            <th style="width: 50px" class="middle_size">Вид дій</th>
//            <th style="width: 80px" class="middle_size">Що йому озвучили</th>
//            <th style="width: 80px" class="middle_size">Що він відповів</th>
//            <th style="width: 80px" class="middle_size">Чим аргументував</th>
//            <th style="width: 80px" class="middle_size">Що важливого сказав</th>
//            <th style="width: 80px" class="middle_size">Результат дій (резюме переговорника)</th>
//            <th style="width: 80px" class="middle_size">Робота до/на наступних дій</th>
//            <th style="width: 80px" class="middle_size">Дата наст.дій</th>
//            <th style="width: 80px" class="middle_size">Робота до/на наступних дій (завдання наставника)</th>
//            <th style="width: 80px" class="middle_size">Запропонована дата виконання наставником</th>
    $out .= '</tbody>';
    return $out;

}
function GetDescription($action_id){
    global $db;
    $sql = "select note from llx_actioncomm where id = ".$action_id;
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    return $obj->note;
}
function GetChainActions($action_id){
    global $db;
    $chain_actions = array();
    $chain_actions[]=$action_id;
    $Actions = new ActionComm($db);
    //Завантажую всі батьківські ІД
    while($action_id = $Actions->GetLastAction($action_id, 'id')){
        array_unshift($chain_actions, $action_id);
    }
    //Завантажую всі наступні ІД
    while($action_id = $Actions->GetNextAction($chain_actions[count($chain_actions)-1], 'id')){
        $chain_actions[] = $action_id;
    }
    return $chain_actions;
}

