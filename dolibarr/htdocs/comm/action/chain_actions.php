<?php
//echo '<pre>';
//var_dump($_SERVER["REQUEST_URI"]);
//echo '</pre>';
//die();
require $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/main.inc.php';
global $user,$db;

$subdivUserID = array(0);
if(in_array('dir_depatment',array($user->respon_alias,$user->respon_alias2))){
    $sql = "select rowid from llx_user
        inner join (select subdiv_id from llx_user
        where rowid = ".$user->id.") subdiv on llx_user.subdiv_id = subdiv.subdiv_id
        where llx_user.active = 1
        and rowid <> ".$user->id;
    $res = $db->query($sql);
    while($obj = $db->fetch_object($sql)){
        $subdivUserID[]=$obj->rowid;
    }
}
$subaction = getSubActionType();
if($_REQUEST['action'] == 'get_subactiontype'){
    switch ($subaction->subaction){
        default:{
            if(!empty($subaction->subaction_id)) {
                $out['subaction'] = $subaction->subaction;
                $sql = "select titre,body from llx_mailing where rowid = " . $subaction->subaction_id;
                $mess = $db->query($sql);
                if (!$mess)
                    dol_print_error($db);
                $obj = $db->fetch_object($mess);
                $out['subject'] = $obj->titre;
                $out['body'] = '<div style="z-index: 10; position: fixed;" id="closePrevDiv"><a class="close"  onclick="ClosePreviewMail();" title="Закрити"></a></div>' . $obj->body;
                print json_encode($out);
            }
        }break;
    }
    exit();
}
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
$description = GetDescription($_GET['action_id']);
if($actioncode == 'AC_CONVERSATION') {
    $actiontabe = ShowConversation();
}else{
    $actiontabe = ShowActionTable();
}
$deadline = getDeadLine();

$accessMentor = ValidAsseccMentor();
//var_dump($subaction->subaction);
//die();
$contactdata = getContactData($author_id);
$date = new DateTime();
if(empty($subaction->subaction)) {
    $addSubAction = '<form method="post" action="/dolibarr/htdocs/comm/action/card.php">
                    <input type="hidden" value="' . $_SERVER['REQUEST_URI'] . '" name="backtopage">
                    <input id="action" type="hidden" value="create" name="action">
                    <input id="parent_id" type="hidden" value="' . $_GET['action_id'] . '" name="parent_id">
                    <input type="hidden" value="hourly_plan" name="mainmenu">
                    <input type="hidden" name="actioncode" value="AC_CURRENT">
                    <input id="id" type="hidden" value="" name="id">
                    <input id="typeaction" type="hidden" value="subaction" name="typeaction">
                    <!--<input type="hidden" value="' . $date->format('YmdHis') . '" name="datep">-->
                    <button style="width: 100%" type="submit"> Додати піддію / Запланувати контакт</button>
               </form>';
}
//elseif ($subaction->subaction == 'validate'){
//
//}

include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/responsibility/sale/action/chain_action.html';
//include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/eldy/responsibility/sale/action/chain_action.html';
//llxFooter();
//

llxPopupMenu();
exit();
function getSubActionType(){
    global $db;
    $sql = "select subaction, subaction_id from llx_actioncomm where id = ".$_REQUEST["action_id"];
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
    $obj = $db->fetch_object($res);
    return $obj;
//    echo '<pre>';
//    var_dump($_REQUEST);
//    echo '</pre>';
//    die();
//    switch ($obj->subaction){
//        case '':{
//            print '';
//        }break;
//        default:{
//            $out['subaction'] = $obj->subaction;
//            $sql = "select titre,body from llx_mailing where rowid = ".$obj->subaction_id;
//            $mess = $db->query($sql);
//            if(!$mess)
//                dol_print_error($db);
//            $obj = $db->fetch_object($mess);
//            $out['subject'] = $obj->titre;
//            $out['body'] = '<div style="z-index: 10; position: fixed;" id="closePrevDiv"><a class="close"  onclick="ClosePreviewMail();" title="Закрити"></a></div>'.$obj->body;
//            print json_encode($out);
//        }break;
//    }
}
function getDeadLine(){
    global $db;
    $sql = "select entity from llx_actioncomm where id = ".$_GET['action_id'];
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    $out = '';
    if($obj->entity == 1) {
        $actionsID = GetChainActions($_GET['action_id']);
        $sql = "select id from llx_actioncomm where id in (" . implode(',', $actionsID) . ") and entity = 1";
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        $obj = $db->fetch_object($res);
        $sql = "select datepreperform, datep2  from llx_actioncomm where id = " . $obj->id;
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        $obj = $db->fetch_object($res);
        if (!empty($obj->datepreperform)) {
            $out .= '<b>Попередньо виконати до</b></br>' . $obj->datepreperform . '</br>';
        }
        $out .= '<b>Кінцево виконати до</b></br>' . $obj->datep2;
    }else{
        $sql = "select datep2  from llx_actioncomm where id = " . $_GET['action_id'];
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        $obj = $db->fetch_object($res);
        $out .= '<b>Кінцево виконати до</b></br>' . $obj->datep2;
    }
    return $out;
}
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
    elseif(in_array('dir_depatment',array($user->respon_alias,$user->respon_alias2))){
            return true;
    }else
            return 0;
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
//    $chain_actions = array();
//    $chain_actions = GetChainActions($_GET['action_id']);
//    //Завантажую результат дії
//    $sql = 'select `llx_societe_action`.`action_id`, `llx_actioncomm`.percent, `llx_societe_action`.`rowid`, `llx_actioncomm`.`datep`,
//        `llx_societe_action`.dtChange datec, create_user.`lastname`, create_user.rowid author_id, `llx_user`.`lastname` as contactname, `llx_user`.rowid as contactUserID,
//                TypeCode.code kindaction, `llx_societe_action`.`said`, `llx_societe_action`.`answer`,`llx_societe_action`.`argument`,
//                `llx_societe_action`.`said_important`, `llx_societe_action`.`result_of_action`,
//                case when `llx_societe_action`.`rowid` is null then `llx_actioncomm`.`note` else `llx_societe_action`.`work_before_the_next_action` end work_before_the_next_action,`work_before_the_next_action_mentor` work_mentor,
//                `llx_societe_action`.`date_next_action_mentor` date_mentor,`llx_societe_action`.`active`
//        from `llx_societe_action`
//        left join `llx_user` create_user on `llx_societe_action`.id_usr = create_user.rowid
//        inner join `llx_actioncomm` on `llx_actioncomm`.id = `llx_societe_action`.`action_id`
//        left join `llx_user` on `llx_actioncomm`.fk_user_author = `llx_user`.rowid
//        inner join (select code, libelle label from `llx_c_actioncomm` where active = 1 and (type = "system" or type = "user")) TypeCode on TypeCode.code = `llx_actioncomm`.code
//        where `llx_societe_action`.`action_id` in ('.implode(",", $chain_actions).')
//        and `llx_societe_action`.active = 1
//        order by `llx_societe_action`.dtChange desc, `llx_societe_action`.`rowid` desc;';
   return ShowActionTable();
}
function ShowActionTable(){

    global $db, $langs, $conf,$user;
    $chain_actions = array();
    $Action = new ActionComm($db);
    $chain_actions = $Action->GetChainActions($_GET['action_id']);
//echo '<pre>';
//var_dump($chain_actions);
//echo '</pre>';
//die();
//
    $AssignedUsersID = $Action->getAssignedUser($_GET['action_id'], true);

    $sql = 'select fk_parent, datep, subaction from `llx_actioncomm` where id in ('.implode(",", $chain_actions).') and fk_parent <> 0';
    $res = $db->query($sql);

    $nextaction = array();

    while($row = $db->fetch_object($res)){
        $nextaction[$row->fk_parent] = $row->datep;
        if(empty($subaction))
            $subaction = $row->subaction;
    }
    if($subaction == 'sendmail'){//Якщо дія, пов'язана з відсиланням ємейлів, видаляю з $chain_actions дії інших користувачів
        $sql = "select `llx_actioncomm_resources`.`fk_actioncomm`, `llx_actioncomm_resources`.`fk_element`, `llx_actioncomm_resources`.`transparency` from llx_actioncomm 
            inner join `llx_actioncomm_resources` on `fk_actioncomm` = llx_actioncomm.id
            where llx_actioncomm.id in(".implode(',',$chain_actions).")";
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
        while($obj = $db->fetch_object($res)){
            if($obj->transparency == 0 && $obj->fk_element != $user->id){
                unset($chain_actions[array_search($obj->fk_actioncomm, $chain_actions)]);
            }
        }
//        echo '<pre>';
//        var_dump($chain_actions);
//        echo '</pre>';
//        die();
    }
    //Завантажую результат дії
    $sql = 'select `llx_societe_action`.`action_id`, `llx_actioncomm`.percent, `llx_societe_action`.`rowid`, `llx_actioncomm`.`datep`,
        `llx_societe_action`.dtChange datec, create_user.`lastname`, create_user.rowid author_id, `llx_user`.`lastname` as contactname, `llx_user`.rowid as contactUserID,
                TypeCode.code kindaction, `llx_societe_action`.`said`, `llx_societe_action`.`answer`,`llx_societe_action`.`argument`,
                `llx_societe_action`.`said_important`, `llx_societe_action`.`result_of_action`,
                case when `llx_societe_action`.`rowid` is null then `llx_actioncomm`.`note` else `llx_societe_action`.`work_before_the_next_action` end work_before_the_next_action,`work_before_the_next_action_mentor` work_mentor,
                `llx_societe_action`.`dtMentorChange` date_mentor,`llx_societe_action`.`active`,`llx_societe_action`.id_mentor
        from `llx_societe_action`
        left join `llx_user` create_user on case when `llx_societe_action`.id_mentor is null then `llx_societe_action`.id_usr else `llx_societe_action`.id_mentor end = create_user.rowid
        inner join `llx_actioncomm` on `llx_actioncomm`.id = `llx_societe_action`.`action_id`
        left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = `llx_actioncomm`.id
        left join `llx_user` on  `llx_societe_action`.contactid = `llx_user`.rowid
        inner join (select code, libelle label from `llx_c_actioncomm` where active = 1 and (type = "system" or type = "user")) TypeCode on TypeCode.code = `llx_actioncomm`.code
        where `llx_societe_action`.`action_id` in ('.implode(",", $chain_actions).')
        and `llx_societe_action`.active = 1
        order by `llx_societe_action`.dtChange desc, `llx_societe_action`.`rowid` desc;';

//    $sql = "select * from llx_societe_action where action_id in (".implode(",", $chain_actions).") and active = 1";

    $res_result_action = $db->query($sql);
    $result_action = array();
    $result_actionID = array();
    while($array_item = $db->fetch_object($res_result_action)){
//echo '<pre>';
//var_dump($array_item);
//echo '</pre>';
//die();
        $result_action[]=(array)$array_item;
        if(!in_array($array_item->action_id, $result_actionID))
            $result_actionID[]=$array_item->action_id;
    }

//V 0
//    $sql='select `llx_actioncomm`.id action_id, `llx_actioncomm`.percent, `llx_societe_action`.`rowid` as rowid, `llx_actioncomm`.`datep`,
//       case when `llx_societe_action`.dtChange is null then `llx_actioncomm`.datec else `llx_societe_action`.dtChange end  as `datec`,
//       case when `llx_societe_action`.`rowid` is null then `create_user`.`lastname` else `llx_user`.lastname end lastname,
//       case when `llx_societe_action`.`rowid` is null then `create_user`.`rowid` else `llx_user`.rowid end author_id,
//        concat(case when `llx_societe_contact`.lastname is null then "" else `llx_societe_contact`.lastname end,
//        case when `llx_societe_contact`.firstname is null then "" else `llx_societe_contact`.firstname end) as contactname,
//        TypeCode.code kindaction, `llx_societe_action`.`said`, `llx_societe_action`.`answer`,`llx_societe_action`.`argument`,
//        `llx_societe_action`.`said_important`, `llx_societe_action`.`result_of_action`,
//        case when `llx_societe_action`.`rowid` is null then `llx_actioncomm`.`note` else `llx_societe_action`.`work_before_the_next_action` end work_before_the_next_action,`work_before_the_next_action_mentor` work_mentor,
//        `llx_societe_action`.`date_next_action_mentor` date_mentor,`llx_societe_action`.`active`
//        from `llx_actioncomm`
//        inner join (select code, libelle label from `llx_c_actioncomm` where active = 1 and (type = "system" or type = "user")) TypeCode on TypeCode.code = `llx_actioncomm`.code
//        left join `llx_societe_contact` on `llx_societe_contact`.rowid=`llx_actioncomm`.fk_contact
//        left join `llx_societe_action` on `llx_actioncomm`.id = `llx_societe_action`.`action_id`
//        left join `llx_user` on `llx_societe_action`.id_usr = `llx_user`.rowid
//        left join `llx_user` create_user on `llx_actioncomm`.fk_user_author = `create_user`.rowid
//        where id in ('.implode(",", $chain_actions).') and llx_actioncomm.active = 1  order by datep desc, datec desc';

//V 1
//    $sql = 'select `llx_actioncomm`.id action_id, `llx_actioncomm`.percent, `llx_societe_action`.`rowid` as rowid, `llx_actioncomm`.`datep`,
//       case when `llx_societe_action`.dtChange is null then `llx_actioncomm`.datec else `llx_societe_action`.dtChange end  as `datec`,
//       `create_user`.`lastname` lastname,
//       case when `llx_societe_action`.`rowid` is null then `create_user`.`rowid` else `llx_user`.rowid end author_id,
//        concat(case when `llx_societe_contact`.lastname is null then "" else `llx_societe_contact`.lastname end,
//        case when `llx_societe_contact`.firstname is null then "" else `llx_societe_contact`.firstname end) as contactname,
//        TypeCode.code kindaction, `llx_societe_action`.`said`, `llx_societe_action`.`answer`,`llx_societe_action`.`argument`,
//        `llx_societe_action`.`said_important`, `llx_societe_action`.`result_of_action`,
//        case when `llx_societe_action`.`rowid` is null then `llx_actioncomm`.`note` else `llx_societe_action`.`work_before_the_next_action` end work_before_the_next_action,`work_before_the_next_action_mentor` work_mentor,
//        `llx_societe_action`.`date_next_action_mentor` date_mentor,`llx_societe_action`.`active`
//        from `llx_actioncomm`
//        inner join (select code, libelle label from `llx_c_actioncomm` where active = 1 and (type = "system" or type = "user")) TypeCode on TypeCode.code = `llx_actioncomm`.code
//        left join `llx_societe_contact` on `llx_societe_contact`.rowid=`llx_actioncomm`.fk_contact
//        left join `llx_societe_action` on `llx_actioncomm`.id = `llx_societe_action`.`action_id`
//        left join `llx_user` on `llx_societe_action`.id_usr = `llx_user`.rowid
//        left join `llx_user` create_user on `llx_actioncomm`.fk_user_author = `create_user`.rowid
//        where id in ('.implode(",", $chain_actions).') and llx_actioncomm.active = 1  order by datep desc, datec desc';

//V 2
    $sql = "select `llx_actioncomm`.id action_id, `llx_actioncomm`.percent, 0 rowid, `llx_actioncomm`.`datep`,
       `llx_actioncomm`.datec,
       `create_user`.`lastname` lastname,
       `create_user`.`rowid` author_id,
        concat(case when `llx_societe_contact`.lastname is null then '' else `llx_societe_contact`.lastname end,
        case when `llx_societe_contact`.firstname is null then '' else `llx_societe_contact`.firstname end) as contactname, 0 as contactUserID,
        TypeCode.code kindaction, '' `said`, '' `answer`, '' `argument`,
        '' `said_important`, '' `result_of_action`,
        `llx_actioncomm`.`note` work_before_the_next_action, '' work_mentor,
        '' date_mentor, null `active`
        from `llx_actioncomm`
        inner join (select code, libelle label from `llx_c_actioncomm` where active = 1 and (type = 'system' or type = 'user')) TypeCode on TypeCode.code = `llx_actioncomm`.code
        left join `llx_societe_contact` on `llx_societe_contact`.rowid=`llx_actioncomm`.fk_contact
        left join `llx_user` create_user on `llx_actioncomm`.fk_user_author = `create_user`.rowid
        where id in (".implode(",", $chain_actions).") and llx_actioncomm.active = 1
        order by datec desc, `llx_actioncomm`.id desc";
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
            <td style="widtd: 50px" class="middle_size">&nbsp;</td>
            <td style="width: 35px" class="middle_size">&nbsp;</td>
            </tr>';
    }
    $num=0;

    while($row = $db->fetch_object($res)){
        if($row->active == null || $row->active == 1) {


            if (isset($nextaction[$row->rowid])) {
                $row->date_next_action = $nextaction[$row->rowid];
//            var_dump($nextaction[$row->rowid]);
//            die();
            }
//        var_dump($row->work_before_the_next_action);
//        die();

            if(empty($row->rowid)&&!empty($row->action_id)){

                $sql = "select fk_element from `llx_actioncomm_resources` where `fk_actioncomm` = ".$row->action_id;

                $resUserID = $db->query($sql);
                    if(!$resUserID)
                        dol_print_error($db);
//                    echo '<pre>';
//                    var_dump($row);
//                    echo '</pre>';
//                    die();
                if($db->num_rows($resUserID) == 0) {
//                    var_dump($db->num_rows($res) == 0);
                    $row->contactUserID = $row->author_id;
                    $row->contactname = $row->lastname;
                }else{
                    $obj = $db->fetch_object($resUserID);
                    $sql = "select rowid, lastname, firstname from llx_user where rowid = ".$obj->fk_element;
                    $row->contactUserID = $obj->fk_element;
                    $resUserID = $db->query($sql);
                        if(!$resUserID)
                            dol_print_error($db);
                    $obj = $db->fetch_object($resUserID);
                    $row->contactname =  $obj->lastname.' '.mb_substr($obj->firstname,0,1,'UTF-8').'.';
                }

            }

            //Перевіряю наявніть результатів перемовин
            if(in_array($row->action_id, $result_actionID)){
                foreach($result_action as $item){
                    if(empty($item["contactUserID"])&&count($AssignedUsersID) == 2) {//Якщо в діях задіяно два користувача, але не вказано в системі кому призначено повідомлення - вибираю контактом того, хто не є автором
                        for($i=0;$i<2;$i++){
                            if($AssignedUsersID[$i] != $item['author_id']){
//                                var_dump();
//                                die();
                                $item["contactUserID"] =  $AssignedUsersID[$i];
                                $sql = "select lastname from llx_user where rowid = ".$AssignedUsersID[$i];
                                $res_contact = $db->query($sql);
                                $obj_contact = $db->fetch_object($res_contact);
                                $item["contactname"] = $obj_contact->lastname;
                            }
                        }
//                        var_dump($row);
//                        die();
                    }
                    if($row->action_id == $item["action_id"]) {

//                        if(){
//
//                        }
                        $item = (object)$item;
                        $out .=CreateNewActionItem($item, $num++, true);

                    }
                }
            }
            $out .=CreateNewActionItem($row, $num++);
        }
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
function CreateNewActionItem($row, $num, $result = false){
    global $db, $conf,$user,$langs,$subdivUserID;
    $dtChange = new DateTime($row->datec);
    $dateaction = new DateTime($row->datep);
//    $dtNextAction = new DateTime($row->date_next_action);
    $dtDateMentor = new DateTime($row->date_mentor);
    $iconitem = '';
    $title = '';
    switch ($row->kindaction) {
        case 'AC_GLOBAL': {
            $classitem = 'global_taskitem';
            $iconitem = 'object_global_task.png';
            $title = $langs->trans('ActionGlobalTask');
        }
            break;
        case 'AC_CURRENT': {
            $classitem = 'current_taskitem';
            $iconitem = 'object_current_task.png';
            $title = $langs->trans('ActionCurrentTask');
        }
            break;
        case 'AC_RDV': {
            $classitem = 'office_meetting_taskitem';
            $iconitem = 'object_office_meetting_task.png';
            $title = $langs->trans('ActionAC_RDV');
        }
            break;
        case 'AC_TEL': {
            $classitem = 'office_callphone_taskitem';
            $iconitem = 'object_call2.png';
            $title = $langs->trans('ActionAC_TEL');
        }
            break;
        case 'AC_DEP': {
            $classitem = 'departure_taskitem';
            $iconitem = 'object_departure_task.png';
            $title = $langs->trans('ActionDepartureMeeteng');
        }
            break;
    }
    if($result) {
        $iconitem = 'result_action.png';
        $title = 'Результат дії '.$title;
    }
    $out = '<tr class="' . (fmod($num, 2) == 0 ? 'impair' : 'pair') . '">
    <td rowid="' . (empty($row->rowid)?$row->action_id:$row->rowid) . '" id = "' . (empty($row->rowid)?$row->action_id:$row->rowid) . 'dtChange" style="widtd: 80px" class="middle_size">' . (empty($row->datec) ? '' : $dtChange->format('d.m.y H:i:s')) . '</td>
    <td rowid="' . (empty($row->rowid)?$row->action_id:$row->rowid) . '" id = "' . (empty($row->rowid)?$row->action_id:$row->rowid) . 'lastname" style="widtd: 100px" class="middle_size">' . $row->lastname . '</td>
    <td rowid="' . (empty($row->rowid)?$row->action_id:$row->rowid) . '" id = "' . (empty($row->rowid)?$row->action_id:$row->rowid) . 'contactname" style="widtd: 80px" class="middle_size">' . $row->contactname . '</td>
    <td rowid="' . (empty($row->rowid)?$row->action_id:$row->rowid) . '" id = "' . (empty($row->rowid)?$row->action_id:$row->rowid) . 'kindaction" style="widtd: 50px; text-align: center;" class="middle_size" ><img src="/dolibarr/htdocs/theme/' . $conf->theme . '/img/' . $iconitem . '" title="' . $title . '"></td>
    <td rowid="' . (empty($row->rowid)?$row->action_id:$row->rowid) . '" id = "' . (empty($row->rowid)?$row->action_id:$row->rowid) . 'said" style="widtd: 80px" class="middle_size">' . (strlen($row->said) > 20 ? mb_substr($row->said, 0, 20, 'UTF-8') . '...<input type="hidden" value="' . EcsapeQuote($row->said) . '" id="input_' . $row->rowid . 'said">' : $row->said) . '</td>
    <td rowid="' . (empty($row->rowid)?$row->action_id:$row->rowid) . '" id = "' . (empty($row->rowid)?$row->action_id:$row->rowid) . 'answer" style="widtd: 80px" class="middle_size">' . (strlen($row->answer) > 20 ? mb_substr($row->answer, 0, 20, 'UTF-8') . '...<input type="hidden" value="' . EcsapeQuote($row->answer) . '" id="input_' . $row->rowid . 'answer">' : $row->answer) . '</td>
    <td rowid="' . (empty($row->rowid)?$row->action_id:$row->rowid) . '" id = "' . (empty($row->rowid)?$row->action_id:$row->rowid) . 'argument" style="widtd: 80px" class="middle_size">' . (strlen($row->argument) > 20 ? mb_substr($row->argument, 0, 20, 'UTF-8') . '...<input type="hidden" value="' . EcsapeQuote($row->argument) . '" id="input_' . $row->rowid . 'argument">' : $row->argument) . '</td>
    <td rowid="' . (empty($row->rowid)?$row->action_id:$row->rowid) . '" id = "' . (empty($row->rowid)?$row->action_id:$row->rowid) . 'said_important" style="widtd: 80px" class="middle_size">' . (strlen($row->said_important) > 20 ? mb_substr($row->said_important, 0, 20, 'UTF-8') . '...<input type="hidden" value="' . EcsapeQuote($row->said_important) . '" id="input_' . $row->rowid . 'said_important>' : $row->said_important) . '</td>
    <td rowid="' . (empty($row->rowid)?$row->action_id:$row->rowid) . '" id = "' . (empty($row->rowid)?$row->action_id:$row->rowid) . 'result_of_action" style="widtd: 80px" class="middle_size">' . (strlen($row->result_of_action) > 20 ? mb_substr($row->result_of_action, 0, 20, 'UTF-8') . '...<input type="hidden" value="' . EcsapeQuote($row->result_of_action) . '" id="input_' . $row->rowid . 'result_of_action">' : $row->result_of_action) . '</td>
    <td rowid="' . (empty($row->rowid)?$row->action_id:$row->rowid) . '" id = "' . (empty($row->rowid)?$row->action_id:$row->rowid) . 'work_before_the_next_action" style="widtd: 80px" class="middle_size">' . (strlen($row->work_before_the_next_action) > 20 ? mb_substr($row->work_before_the_next_action, 0, 20, 'UTF-8') . '...<input type="hidden" value="' . EcsapeQuote($row->work_before_the_next_action) . '" id="input_' . $row->rowid . 'work_before_the_next_action">' : $row->work_before_the_next_action) . '</td>';
    if(!$result)
        $out.= '<td rowid="' . (empty($row->rowid)?$row->action_id:$row->rowid) . '" id = "' . (empty($row->rowid)?$row->action_id:$row->rowid) . 'dtAction" style="widtd: 80px" class="middle_size">' . (empty($row->datep) ? '' : ($dateaction->format('d.m.y') . '</br>' . $dateaction->format('H:i'))) . '</td>';
    else
        $out.= '<td rowid="' . (empty($row->rowid)?$row->action_id:$row->rowid) . '" id = "' . (empty($row->rowid)?$row->action_id:$row->rowid) . 'dtAction" style="widtd: 80px" class="middle_size"></td>';
    $out.='<td rowid="' . (empty($row->rowid)?$row->action_id:$row->rowid) . '" id = "' . (empty($row->rowid)?$row->action_id:$row->rowid) . 'work_before_the_next_action_mentor" style="widtd: 80px" class="middle_size">' . (strlen($row->work_mentor) > 20 ? mb_substr($row->work_mentor, 0, 20, 'UTF-8') . '...<input type="hidden" value="' . $row->work_mentor . '" id="input_' . $row->rowid . 'work_mentor">' : $row->work_mentor) . '</td>
    <td rowid="' . (empty($row->rowid)?$row->action_id:$row->rowid) . '" id = "' . (empty($row->rowid)?$row->action_id:$row->rowid) . 'date_next_action_mentor" style="widtd: 80px" class="middle_size">' . (empty($row->date_mentor) ? '' : $dtDateMentor->format('d.m.Y')) . '</td>';

    //Статус завдання
//    if ($row->percent == -1)
//        $status = 'Не розпочато';
//    elseif ($row->percent > 0 && $row->percent < 99)
//        $status = 'В роботі(' . $row->percent . '%)';
//    elseif ($row->percent == 99)
//        $status = '<img title="Чекає підтвердження" src="/dolibarr/htdocs/theme/eldy/img/warning.png">';
//    elseif ($row->percent == 0)
//        $status = 'Тільки-но розпочато';
//    else
//        $status = 'Виконано';

    $date = new DateTime();
    $style = 'style="';
    if($row->percent < 98) {
        if (!$result) {
            if ($dateaction < $date) {
                $style = 'style="background:rgb(255, 0, 0)';
            } elseif ($dateaction == $date) {
                $style = 'style="background:rgb(0, 255, 0)';
            }
        }
        if ($row->percent == "-1")
            $status = 'ActionNotRunning';
        elseif ($row->percent == 0)
            $status = 'ActionRunningNotStarted';
        elseif ($row->percent > 0 && $row->percent < 98)
            $status = 'ActionRunningShort';
        else
            $status = 'ActionDoneShort';
    }elseif($row->percent == 99)
        $status = '<img title="Чекає підтвердження" src="/dolibarr/htdocs/theme/eldy/img/BWarning.png">';
    else
        $status = '<img title="Робота прийнята" src="/dolibarr/htdocs/theme/eldy/img/done.png">';
    if($result)
        $status = '';
    $out.='<td '.$style.';widtd: 50px; text-align: center;" class="middle_size" >' . $langs->trans($status) . '</td>';

    $out.='<td rowid="' . (empty($row->rowid)?$row->action_id:$row->rowid) . '" id = "' . (empty($row->rowid)?$row->action_id:$row->rowid) . 'action" style="width: 35px; text-align: center;" class="middle_size"><script>
         var click_event = "/dolibarr/htdocs/societe/addcontact.php?action=edit&mainmenu=companies&rowid=1";
        </script>';
//<td rowid="' . $row->rowid . '" id = "' . $row->rowid . 'date_next_action" style="widtd: 80px" class="middle_size">' . (empty($row->date_next_action) ? '' : $dtNextAction->format('d.m.Y H:i:s')) . '</td>
//            $out .= '<img id="img_1" "="" onclick="" style="vertical-align: middle" title="'.$langs->trans('AddSubAction').'" src="/dolibarr/htdocs/theme/eldy/img/Add.png">';
    $actioncode = '';
    switch ($_REQUEST['mainmenu']) {
        case 'current_task': {
            $actioncode = "'AC_CURRENT'";
        }
            break;
        case 'global_task': {
            $actioncode = "'AC_GLOBAL'";
        }
    }
//    var_dump(empty($row->id_mentor));
//    die();
    if($row->author_id == $user->id) {
        if(empty($row->id_mentor)) {
            if ($row->percent == 99 && !$result) {
                $out .= '<img id="confirm' . $row->action_id . '" title = "' . $langs->trans('Confirm') . '" onclick="Confirmation(' . $row->action_id . ');" src="/dolibarr/htdocs/theme/eldy/img/uncheck.png">';
            } elseif ($row->percent < 99) {
                if ($result)
                    $out .= '<img id="img_1"  onclick="EditOnlyResult(' . $row->action_id . ',' . (empty($row->rowid) ? 0 : $row->rowid) . ', ' . $actioncode . ');" style="vertical-align: middle; cursor: pointer;" title="' . $langs->trans('Edit') . '" src="/dolibarr/htdocs/theme/eldy/img/edit.png">';
                else
                    $out .= '<img id="img_1"  onclick="EditAction(' . $row->action_id . ',' . (empty($row->rowid) ? 0 : $row->rowid) . ', ' . $actioncode . ');" style="vertical-align: middle; cursor: pointer;" title="' . $langs->trans('Edit') . '" src="/dolibarr/htdocs/theme/eldy/img/edit.png">';
                $out .= '&nbsp;&nbsp;<img  onclick="DelAction(' . (empty($row->rowid) ? "'" . $row->action_id : "'_" . $row->rowid) . "'" . ');" style="vertical-align: middle; cursor: pointer;" title="' . $langs->trans('Delete') . '" src="/dolibarr/htdocs/theme/eldy/img/delete.png">';
            }
        }elseif($user->id == $row->id_mentor){
            $out .= '<img id="img_1"  onclick="SetRemarkOfMentor(' . $row->action_id . ','.$row->rowid.');" style="vertical-align: middle; cursor: pointer;" title="' . $langs->trans('SetRemarkOfMentor') . '" src="/dolibarr/htdocs/theme/eldy/img/edit.png">';
            $out .= '&nbsp;&nbsp;<img  onclick="DelAction(' . (empty($row->rowid) ? "'" . $row->action_id : "'_" . $row->rowid) . "'" . ');" style="vertical-align: middle; cursor: pointer;" title="' . $langs->trans('Delete') . '" src="/dolibarr/htdocs/theme/eldy/img/delete.png">';
        }
    }elseif(count($subdivUserID)>0&&in_array($row->contactUserID, $subdivUserID)&&$row->action_id!=0 || (in_array('gen_dir', array($user->respon_alias,$user->respon_alias2)))){
        $out .= '<img id="img_1"  onclick="SetRemarkOfMentor(' . $row->action_id . ');" style="vertical-align: middle; cursor: pointer;" title="' . $langs->trans('SetRemarkOfMentor') . '" src="/dolibarr/htdocs/theme/eldy/img/filenew.png">';
    }
//    elseif($row->contactUserID== $user->id){
//        $out .= '<img id="img_1"  onclick="EditOnlyResult(' . $row->action_id . ',' . (empty($row->rowid) ? 0 : $row->rowid) . ', ' . $actioncode . ');" style="vertical-align: middle; cursor: pointer;" title="' . $langs->trans('Edit') . '" src="/dolibarr/htdocs/theme/eldy/img/edit.png">';
//    }
    $out .= '</td>
    </tr>';
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
function EcsapeQuote($input){
    return str_replace('"', '&quot;', $input);
}
function GetChainActions($action_id){
    global $db;
    $Actions = new ActionComm($db);
    $chain_actions = $Actions->GetChainActions($action_id);
    return $chain_actions;
}

