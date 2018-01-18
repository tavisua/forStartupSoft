<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 14.01.2016
 * Time: 13:45
 */
//if('getTypeNotification'!=$_REQUEST['action']) {

define("NOLOGIN",1);		// This means this output page does not require to be logged.
global $db,$user;
//if($_REQUEST['action'] == 'setNotInterestingProposed'){
//    exit();
//}

require_once '../../main.inc.php';
require_once 'class/actioncomm.class.php';

if(!$user->id){
    $user->fetch('',$_SESSION["dol_login"]);
}
//echo '<pre>';
//var_dump($_REQUEST);
//echo '</pre>';
//die('test');

if(isset($_REQUEST["backtopage"]) && !empty($_REQUEST["backtopage"])){
    $_REQUEST["backtopage"] = str_replace('_amp038;', '&', $_REQUEST["backtopage"]);
}
if(isset($_REQUEST['action'])) {
    if (in_array($_REQUEST['action'], array('update', 'update_and_create', 'saveonlyresult','addonlyresult_and_create', 'updateonlyresult_and_create')) ||
        in_array($_REQUEST['action'], array('updateonlyresult')) && $_REQUEST['mainmenu'] == 'area') {
//    var_dump((substr($_POST['action'],strlen($_POST['action'])-strlen('_and_create') )== '_and_create'));
//    die();
//        if($user->login == 'admin') {
//        if(!empty($_REQUEST['AutoCreateAction'])){
//            AutoCreateAction(!empty($_REQUEST['today'])?$_REQUEST['today']:false);
//        }
//        }
        $actions = array($_REQUEST['actionid']);
        require_once './class/actioncomm.class.php';
        $newItem = new ActionComm($db);
        if(isset($_REQUEST['forAllGroup'])&&$_REQUEST['forAllGroup'] == true) {

            $actions = $newItem->getGroupActions($_REQUEST['actionid']);
        }

        foreach ($actions as $item) {
            saveaction($_REQUEST['rowid'], (substr($_REQUEST['action'], strlen($_REQUEST['action']) - strlen('_and_create')) == '_and_create'), $item);
        }
        if((substr($_REQUEST['action'], strlen($_REQUEST['action']) - strlen('_and_create')) == '_and_create')){
            $sql = "select code from llx_actioncomm where id =".$_REQUEST['action_id'];
            $res = $db->query($sql);
            $obj = $db->fetch_object($res);
            $location = 'Location:'.'http://'.$_SERVER["HTTP_HOST"].'/dolibarr/htdocs/comm/action/card.php?&socid='.$_REQUEST['socid'].'&actioncode='.$obj->code.
                '&action=create&parent_id='.$_REQUEST['actionid'].'&contactid='.$_REQUEST['contactid'].'&mainmenu=area&datep='.date('d.m.Y').'&backtopage='.$_REQUEST['backtopage'];
        }else
            $location = 'Location:'.htmlspecialchars(str_replace("'",'',$_REQUEST['backtopage']));
        if($_REQUEST['action'] =='updateonlyresult')
            echo ($location);
        header(urldecode($location));
        exit();
    } elseif (in_array($_REQUEST["action"], array('savetaskmentor','savetaskmentor_and_create'))) {
        savetaskmentor($_REQUEST['action'] == 'savetaskmentor_and_create');
        exit();
    }elseif($_REQUEST['action'] == 'save_societe_need'){//Зберігання потреби
        global $db,$user;
        require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
        $societe = new Societe($db);
        $societe->fetch($_REQUEST['socid']);
        $societe->save_societe_need($_REQUEST['need']);
        die(1);
    } elseif ($_REQUEST["action"] == 'get_user_id') {
        global $db,$user;
        $id_usr  = $user->fetch_userID($_REQUEST["login"], $_REQUEST["pass"]);
//        die($id_usr);
        if(is_numeric($id_usr))
            echo '{"result": "ok", "error":"", "id_usr":"'.$id_usr.'"}';
        else
            echo '{"result": "error":"'.$id_usr.'", "id_usr":""}';
        exit();
    } elseif ($_REQUEST["action"] == 'incoming_call') {
        global $db;
        $sql = "insert into llx_incomingcall(incoming_number,id_usr,active)
          values(".$_REQUEST["incoming_number"].",".$_REQUEST["id_usr"].",1)";
        $res = $db->query($sql);
        if(!$res)
            echo '{"result": "error", "error":"'.$db->lasterror() .'"}';
        else
            echo '{"result": "ok", "error":""}';
        exit();

    } elseif ($_REQUEST["action"] == 'patch_societe_action') {
        global $db;
        $sql = "select rowid, socid from llx_societe_action
                where (id_usr is null or id_usr = 0)
                and socid is not null";
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
        while($obj = $db->fetch_object($res)){
            $sql = "select llx_user_regions.fk_user from llx_societe
                inner join `llx_user_regions` on `fk_id` = llx_societe.region_id
                where llx_societe.rowid = $obj->socid
                and region_id is not null
                and `llx_user_regions`.`active` = 1
                order by `llx_user_regions`.`dtChange` desc
                limit 1";
            $res_usr = $db->query($sql);
            if(!$res_usr) {
                print $sql;
                dol_print_error($db);
            }
            $obj_usr=$db->fetch_object($res_usr);
            if(!empty($obj_usr->fk_user)){
                $sql = "update llx_societe_action set id_usr = $obj_usr->fk_user where rowid = ".$obj->rowid;
                $res_update = $db->query($sql);
                if(!$res_update) {
                    print $sql;
                    dol_print_error($db);
                }else
                    print $sql.'</br>';
            }
//            llxHeader();
//            var_dump($sql);
        }
        exit();

    } elseif ($_REQUEST["action"] == 'get_freetime') {
        if(substr($_REQUEST["date"],0,strlen('undefined'))=='undefined') {
            echo '';
            exit();
        }
//        echo '<pre>';
//        var_dump($_REQUEST);
//        echo '</pre>';
//	    die();
        global $user;
        require_once(DOL_DOCUMENT_ROOT . "/comm/action/class/actioncomm.class.php");
        $Action = new ActionComm($db);

        if (isset($_GET['action_id']) && !empty($_GET['action_id'])) {
            $Action->fetch($_GET['action_id']);
        }
        $date = new DateTime();
        $date->setTimestamp(time());
//	echo '<pre>';
//	var_dump($_GET['id_usr']);
//	echo '</pre>';
//	die();
        $freetime = $Action->GetFreeTime($_GET['date'], $_GET['id_usr'], ($Action->datef - $Action->datep) / 60, $Action->priority);
        $out = array('freetime' => $freetime, 'minute' => ($Action->datef - $Action->datep) / 60);
        echo dol_json_encode($out);
        exit();
    } elseif ($_REQUEST["action"] == 'getTypeNotification') {
        echo getTypeNotification();
        exit();
    }
    if (in_array($_POST['action'],array('saveuseraction','saveuseraction_and_create'))) {//Зберігаю результати перемовин зі співробітником
        saveuseraction($_POST['rowid']);
    }
    if($_REQUEST['action'] == 'view_call') {//Реєстрація вхідного дзвінка
        global $db;
        $sql = "select incoming_number,number,time,dtChange from llx_incomingcall order by dtChange desc";
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
        $out = '<table><thead>
<th>incoming_number</th>
<th>number</th>
<th>time</th>
<th>time_in</th>
</thead><tbody>';
        while($obj = $db->fetch_object($res)){
            $out.='<tr>';
            $out.='<td>'.$obj->incoming_number.'</td>';
            $out.='<td>'.$obj->number.'</td>';
            $out.='<td>'.$obj->time.'</td>';
            $out.='<td>'.$obj->dtChange.'</td>';
            $out.='</tr>';
        }
        $out.='</tbody></table>';

        print $out;
        exit();
    }
    if($_REQUEST['action'] == 'incoming_call'){//Реєстрація вхідного дзвінка
        global $db;
        $sql = "insert into llx_incomingcall(incoming_number,number,active)
                values('".substr($_REQUEST["incoming_number"],1)."','".substr($_REQUEST["number"],1)."',1)";
        $res = $db->query($sql);
        print '{"result": "ok", “error”:””}';
        exit();
    }
    if($_REQUEST['action'] == 'completed_call'){//Реєстрація вхідного дзвінка
        define("NOLOGIN",1);		// This means this output page does not require to be logged.
        global $db;
        $sql = "update llx_incomingcall set time = ".$_REQUEST['time'].", active = 0
            where incoming_number = '".substr($_REQUEST["incoming_number"],1).
            "' and number = '".substr($_REQUEST["number"],1)."' and active = 1 top 1";
        $res = $db->query($sql);
        print '{"result": "ok", “error”:””}';
        exit();
    }
    if($_REQUEST['action'] == 'setNotInterestingProposed')
        return '{"result": "ok", “error”:””}';
}

    require_once DOL_DOCUMENT_ROOT . '/core/lib/agenda.lib.php';
    require_once DOL_DOCUMENT_ROOT . '/comm/action/class/actioncomm.class.php';
    require_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';
    global $user;
    if ($_GET['action'] == 'addonlyresult' || $_GET['action'] == 'addonlyresult_and_create' || $_GET['action'] == 'useraction') {
        llxHeader('', $langs->trans("AddResultAction"), $help_url);
    } elseif (isset($_REQUEST["onlyresult"]) && $_REQUEST["onlyresult"] == '1' || $_GET['action'] == 'updateonlyresult') {
        llxHeader('', $langs->trans("EditResultAction"), $help_url);
    } elseif ($_GET['action'] == 'SetRemarkOfMentor') {
        llxHeader('', $langs->trans("SetRemarkOfMentor"), $help_url);
    } else
        llxHeader('', $langs->trans("EditAction"), $help_url);
    $action_id = 0;
    $socid = 0;

    if (isset($_REQUEST["action_id"]) && $_REQUEST["action"] != 'addonlyresult') {
        $action_id = $_REQUEST["action_id"];
        $sql = "select * from llx_societe_action where 1 ";
        if ((!isset($_REQUEST["onlyresult"]) || empty($_REQUEST['onlyresult'])) && isset($_REQUEST["answer_id"]) && !empty($_REQUEST["answer_id"]))
            $sql .= "and rowid=" . $_REQUEST["answer_id"];
        else
            $sql .= "and rowid=" . $_REQUEST["action_id"];

        $res = $db->query($sql);
        if (!$res) {
            dol_print_error($db);
        }
    }
    if ((!isset($_REQUEST["onlyresult"]) || empty($_REQUEST["onlyresult"])) && $action_id) {
        $object = new ActionComm($db);
        $object->fetch($action_id);
        $socid = $object->socid;
//}elseif($_REQUEST["action"]=='SetRemarkOfMentor') {

    } elseif ($_REQUEST["action"] == 'edituseration') {

    } else {
        if (!empty($res))
            $object = $db->fetch_object($res);
        $socid = $object->socid;
    }
    $head = actions_prepare_head($object);
    if ($_GET['action'] == 'addonlyresult' || $_GET['action'] == 'useraction')
        print_fiche_titre($langs->trans("AddResultAction"));
    elseif ($_REQUEST["action"] == 'edituseration')
        print_fiche_titre('Редагувати перемовини');
    elseif ($_REQUEST["action"] == 'SetRemarkOfMentor') {
//    echo '<pre>';
//    var_dump($object);
//    echo '</pre>';
//    die();
        if (empty($object) && isset($_REQUEST['rowid'])) {
            $sql = "select " . $_REQUEST['rowid'] . " as rowid";
            $res = $db->query($sql);
            $object = $db->fetch_object($res);
        }
        if (isset($_REQUEST['rowid'])) {
            $sql = "select work_before_the_next_action_mentor,dtMentorChange from llx_societe_action where rowid = " . $_REQUEST['rowid'];
            $res = $db->query($sql);
            if (!$res)
                dol_print_error($db);
            $obj = $db->fetch_object($res);
            $task_mentor = $obj->work_before_the_next_action_mentor;
            $date_mentor = $obj->dtMentorChange;
        }
        print_fiche_titre('Завдання наставника');
        $form = new Form($db);
        include $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/theme/' . $conf->theme . '/responsibility/dir_depatment/action/addmentoraction.html';
        print '</div>';
        llxPopupMenu();
        //llxFooter();
        exit();
    } elseif (isset($_REQUEST["onlyresult"]) && $_REQUEST["onlyresult"] == '1' || $_GET['action'] == 'updateonlyresult') {
        print_fiche_titre($langs->trans("EditResultAction"));
    } else
        print_fiche_titre($langs->trans("EditAction"));
    print '<script type="text/javascript" src="/dolibarr/htdocs/comm/action/js/action.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
    print '<script type="text/javascript"> var id_usr = ' . $user->id . '</script>' . "\n";
    if (!empty($conf->use_javascript_ajax)) {
        print "\n" . '<script type="text/javascript">';
        print '$(document).ready(function () {
	            		function setdatefields()
	            		{
	            			if ($("#fullday:checked").val() == null) {
	            				$(".fulldaystarthour").removeAttr("disabled");
	            				$(".fulldaystartmin").removeAttr("disabled");
	            				$(".fulldayendhour").removeAttr("disabled");
	            				$(".fulldayendmin").removeAttr("disabled");
	            			} else {
	            				$(".fulldaystarthour").attr("disabled","disabled").val("00");
	            				$(".fulldaystartmin").attr("disabled","disabled").val("00");
	            				$(".fulldayendhour").attr("disabled","disabled").val("23");
	            				$(".fulldayendmin").attr("disabled","disabled").val("59");
	            			}
	            		}
	            		setdatefields();
	            		$("#fullday").change(function() {
	            			setdatefields();
	            		});

                        $("#event_desc").removeClass("tabactive");
                        $("#event_desc").addClass("tab");
                        $(".tabBar").width(600);
                        $("#formaction").width(600);
                   })';
        print '</script>' . "\n";
    }
//        print '<div class="tabPage">';
//print '<form id = "formaction" name="formaction" action="'.$_SERVER['PHP_SELF'].'" method="POST">';
//print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
//print '<input type="hidden" name="action" value="update">';
//print '<input type="hidden" name="id" value="'.$object->id.'">';
//print '<input type="hidden" name="ref_ext" value="'.$object->ref_ext.'">';
//print '<input type="hidden" name="backtopage" value="'.$_GET['backtopage'].'">';
//if (empty($conf->global->AGENDA_USE_EVENT_TYPE)) print '<input type="hidden" name="actioncode" value="'.$object->type_code.'">';
//echo '<pre>';
//var_dump($object);
//echo '</pre>';
//die();
    if (!($_GET['action'] == 'addonlyresult' || (isset($_REQUEST["onlyresult"]) && $_REQUEST["onlyresult"] == '1'))) {
        if (!($_GET['action'] == 'useraction' || $_GET['action'] == 'edituseration'))
            dol_fiche_head($head, 'event_desc', $langs->trans("Action"), 0, 'action');
        $contactlist = '';
    } else {
        if (!in_array($_REQUEST['actioncode'], array('AC_GLOBAL', 'AC_CURRENT'))) {
            $contactid = empty($object->contactid) ? $_REQUEST['contactid'] : $object->contactid;
            $lastactiveaction = getLastContact();
            if (empty($contactid))
                $contactid = count($lastactiveaction) > 0 ? $lastactiveaction['contactid'] : 1;
            $form = new Form($db);
            if (!empty($_GET['socid']) || !empty($object->socid))
                $contactlist = '<tr><td>Контактне лице</br>' . $form->selectcontacts(empty($_GET['socid']) ? $object->socid : $_GET['socid'], $contactid, 'contactid', 1) . '</td></tr>';
            $productname = explode(',', $_REQUEST['productsname']);
            $needlist = explode(',', $_REQUEST['need']);
            $object->resultaction['answer'] = '';
            $action_id = count($lastactiveaction) > 0 ? $lastactiveaction['actionid'] : 0;
            if (count($productname) > 0 && !empty($productname[0]))
                for ($i = 0; $i < count($productname); $i++) {
                    $object->resultaction['answer'] .= $productname[$i] . ' ' . (empty($needlist[$i]) ? 'не потрібно' : $needlist[$i]) . '; ';
                }
        }
    }
    if ($action_id == 0 && !empty($_REQUEST['action_id']))
        $action_id = $_REQUEST['action_id'];
//        var_dump($action_id);
//die('test');
    $societe = new Societe($db);
    $societe->fetch(empty($object->socid) && $_GET['action'] == 'addonlyresult' ? $_GET['socid'] : $object->socid);

    $formactions = new FormActions($db);
    if ($_GET['action'] == 'edituseration') {//Якщо редагуються результати перемовин зі співробітниками
        $sql = "select rowid,said,answer,argument,said_important,result_of_action,fact_cost,work_before_the_next_action,date_next_action,work_before_the_next_action_mentor,date_next_action_mentor
        from llx_users_action
        where rowid = " . $_REQUEST['rowid'];
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        $object = $db->fetch_object($res);
        $said = empty($object->resultaction['said']) ? $object->said : $object->resultaction['said'];
    } else {
        $percent = $object->percentage == 99 ? 100 : $object->percentage;
        $said = empty($object->resultaction['said']) ? $object->said : $object->resultaction['said'];
        if (empty($said))
            $said = $_REQUEST['said'];
////print '<div class="tabBar">';
    }
    require_once $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/comm/action/class/actioncomm.class.php';

    $Actions = new ActionComm($db);
    if (empty($_REQUEST["socid"]))//Якщо не стосується зовнішніх контрагентів
        $AssignedUsersID = implode(',', $Actions->getAssignedUser($_GET['action_id'], true));

    if (isset($_GET['action_id']) && !empty($_GET['action_id']))
        $Actions->fetch($_GET['action_id']);

    if (($_GET['action'] == 'addonlyresult' || $_GET['action'] == 'updateonlyresult') && empty($_REQUEST['socid'])) {//Виконується, якщо дія пов'язана з внутрішніми роботами
        $style = 'style="display:none;"';
        if (!empty($object)) {
            if (get_class($Actions) == 'ActionComm') {
                $object->label = $Actions->note;
                $object->type = $Actions->type_code;
            } else
                $object->label = $object->note;
        }
    }
    if (empty($form)) {
        $form = new Form($db);
    }
//["callstatus"]
//var_dump($_REQUEST["callstatus"], $object->callstatus, $object);
//die();
    if (isset($_REQUEST["callstatus"]) && !empty($_REQUEST["callstatus"]) && !empty($object)) {
        $object->callstatus = $_REQUEST["callstatus"];
    }
    if (empty($_REQUEST["action_id"]) && !empty($_REQUEST["id"]))
        $_REQUEST["action_id"] = $_REQUEST["id"];
//echo '<pre>';
//var_dump($AssignedUsersID);
//echo '</pre>';
//die();
//$subactionID = '111';
    if (!empty($_REQUEST["action_id"]))
        $subactionID = getSubactionID($_REQUEST["action_id"]);
    if (empty($subactionID))
        $subactionID = -1;
//var_dump($subactionID);
//die();
    global $user;
    $category = $societe->getCategoryOfCustomer();
//    $status = $_REQUEST['actioncode'] == 'AC_TEL' ? $formactions->form_select_callstatus('callstatus', empty($object->callstatus) ? 2 : $object->callstatus) : $formactions->form_select_status_action('formaction', $percent, 1, 'complete');
    $societe_name = $societe->name;
    if (!empty($object->resultaction['answer']) && !empty($_REQUEST['proposed_id'])) {
        $object->callstatus = 5;
    }
    include $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/theme/' . $conf->theme . '/responsibility/sale/addaction.html';
    print '</div>';


    llxPopupMenu();
//llxFooter();
    exit();

function getSubactionID($actionid){
    global $db;
    $sql = "select subaction_id from llx_actioncomm where id = " . $actionid;
    $res = $db->query($sql);
    if (!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    return empty($obj->subaction_id) ? 0 : $obj->subaction_id;
}
function savetaskmentor($createaction){
//    echo '<pre>';
//    var_dump($_POST);
//    echo '</pre>';
//    die();
    global $db,$user;

    try
    {
        $date_mentor = new DateTime($_POST['date_mentor']);
    }
    catch(Exception $ex)
    {
        $date_mentor = new DateTime();
    }
    if(empty($_POST['rowid'])){
        $sql = "insert into llx_societe_action(`action_id`,`work_before_the_next_action_mentor`,`id_mentor`,`dtMentorChange`,`new`)
          values(".$_POST['actionid'].",'".$_POST['task_mentor']."',".$user->id.",'".$date_mentor->format('Y-m-d')."',1)";
    }else{
        $sql = "update llx_societe_action set `new`=1,`work_before_the_next_action_mentor`='".$db->escape($_POST['task_mentor'])."',
        `id_mentor`=".$user->id.",`dtMentorChange`='".$date_mentor->format('Y-m-d')."' where rowid=".$_POST['rowid'];
    }
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    if(empty($_POST['rowid']))
        $id = get_last_id('llx_societe_action');
    else
        $id = $_POST['rowid'];
    $sql = "select action_id, socid from llx_societe_action where rowid = ".$id;
    $res = $db->query($sql);
    $obj = $db->fetch_object($res);
    $usersID = array();
    $action_tmp = new ActionComm($db);
    if(!empty($obj->action_id)) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/comm/action/class/actioncomm.class.php';
        if(!empty($_POST['actionid']))
        $action_tmp->fetch($obj->action_id);
        $usersID[]=$action_tmp->authorid;
        foreach(array_keys($action_tmp->userassigned) as $key){
            $usersID[]= $key;
        }
    }
    if(!empty($obj->socid)){
        $sql = "select distinct `llx_user_regions`.fk_user, llx_societe.region_id from llx_societe
            inner join `responsibility_param` on `responsibility_param`.`fx_category_counterparty` = llx_societe.`categoryofcustomer_id`
            inner join `llx_user_regions` on `llx_user_regions`.`fk_id` = llx_societe.region_id
            inner join `llx_user` on llx_user.rowid = `llx_user_regions`.fk_user
            where llx_societe.rowid = $obj->socid
            and `llx_user_regions`.`active` = 1
            and (`llx_user`.`respon_id` = `responsibility_param`.`fx_responsibility` or `llx_user`.`respon_id2` = `responsibility_param`.`fx_responsibility`)
            and `llx_user`.`active` = 1;";
        $res = $db->query($sql);
        if(!$res)
            dol_print_error();
        while($obj = $db->fetch_object($res))
            $usersID[]=$obj->fk_user;
    }
    foreach ($usersID as $value){
        if($value != $user->id)
            $action_tmp->ShowAction($value, $user->id, -$id, 'oncreate');
    }

    $backtopage = $_REQUEST['backtopage'];
    if(!$createaction)
        header("Location: " . $backtopage);
    else{
                if(substr($_REQUEST['backtopage'], 0, 1) == "'" && substr($_REQUEST['backtopage'], strlen($_REQUEST['backtopage'])-1, 1) == "'")
            $backtopage = substr($_REQUEST['backtopage'], 1, strlen($_REQUEST['backtopage']) - 2);
        else
            $backtopage = $_REQUEST['backtopage'];
//      $backtopage = urlencode(htmlspecialchars(substr($_REQUEST['backtopage'], 1, strlen($_REQUEST['backtopage'])-2)));
//      $backtopage = urlencode(substr($_REQUEST['backtopage'], 1, strlen($_REQUEST['backtopage'])-2));
//        $backtopage = $_REQUEST['backtopage'];
//        var_dump($backtopage);
//        die();
//        if(!strpos($_REQUEST['backtopage'], 'socid=')) {
//            if(!strpos('php?', $backtopage))
//                $backtopage .= "?mentor_action%3D1%26mainmenu%3D" . $_REQUEST['mainmenu'];
//            else
//                $backtopage .= "&mentor_action%3D1%26mainmenu%3D" . $_REQUEST['mainmenu'];
//        }
//        var_dump($backtopage);
//        die();
        $link = "http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/comm/action/card.php?mentor_action=1&mainmenu=".
            $_REQUEST['mainmenu']."&actioncode=AC_CURRENT&groupoftask=10&action=create&parent_id=".
            $_REQUEST["actionid"]."&backtopage=".urlencode($backtopage);

        header("Location: ".$link);
        exit();
    }

}
function getUserName($id_usr){
    global $db;
    $sql = "select lastname from llx_user where rowid = ".$id_usr;
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    return $obj->lastname;
}
function getTypeNotification(){
    if(empty($_REQUEST['action_id']))
        return 0;
    global $db;
    $sql = "select typenotification, `office_phone`, `llx_actioncomm`.`fk_user_author` from llx_actioncomm
        left join llx_user on llx_user.rowid = `llx_actioncomm`.`fk_user_author`
        where id=".$_REQUEST['action_id'];
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    $result = array('typenotification'=>$obj->typenotification, 'phonenumber'=>$obj->office_phone, 'author_id'=>$obj->fk_user_author);
    return json_encode($result);
}
function getLastContact(){
    if(empty($_REQUEST['socid']))
        return array();
    global $db;
	$sql = "select id, `fk_contact` from llx_actioncomm
	where fk_soc = ".$_REQUEST['socid']."
	and `llx_actioncomm`.`code` in (select `code` from 	llx_c_actioncomm where type in ('user','system'))
	and percent <> 100
	and `llx_actioncomm`.`code` not in ('AC_CURRENT','AC_GLOBAL')
	and date(`llx_actioncomm`.`datep`)<=date(Now())
    and `llx_actioncomm`.`active` = 1";
	$res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
	if($db->num_rows($res)==0)
		return array();
	else{
		$obj = $db->fetch_object($res);
		return array('contactid'=>$obj->fk_contact, 'actionid'=>$obj->id);
	}
}
function addaction(){
    global $conf, $db, $user, $langs;
//    die($langs->trans('Area'));
    if(!empty($_REQUEST['socid'])) {
        $socid = $_REQUEST['socid'];
        $object = new  Societe($db);
        $object->fetch($socid);
        $CategoryOfCustomer = $object->getCategoryOfCustomer();
        $FormOfGoverment = $object->getFormOfGoverment();
        $contactid = 0;
        $selectcountact = selectcontact($socid);
    }
    $action_url = $_SERVER["PHP_SELF"].'?socid='.$socid.'&idmenu=10425&mainmenu=area&action=save';
    $action = 'save';
    include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/responsibility/sale/addaction.html';
}
function get_last_id(){
    global $db;
    $sql = 'select max(rowid) rowid from llx_societe_action';
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
    $obj = $db->fetch_object($res);
    return $obj->rowid;
}
function get_soc_id($action_id){
    global $db;
    $sql = 'select fk_soc from llx_actioncomm where id = '.$action_id;
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
    $obj = $db->fetch_object($res);
    return $obj->fk_soc;
}
function saveuseraction($rowid){
        global $db, $user;

//    echo '<pre>';
//    var_dump($_REQUEST);
//    echo '</pre>';
//    die();
    if(empty($rowid)){
        $sql='insert into llx_users_action(`action_id`,`proposed_id`, `socid`, `contactid`,`said`,`answer`,
          `argument`,`said_important`,`result_of_action`,`work_before_the_next_action`,`id_usr`, `new`) values(';
        if(empty($_REQUEST['actionid'])) $sql.='null,';
        else $sql.=$_REQUEST['actionid'].',';
        if(empty($_REQUEST['proposed_id'])) $sql.='null,';
        else $sql.=$_REQUEST['proposed_id'].',';
        if(empty($socid)) $sql.='null,';
        else $sql.=$socid.',';
        $sql.=(empty($_REQUEST['contactid'])?"null":$_REQUEST['contactid']).', ';
        if(empty($_REQUEST['said'])) $sql.='null,';
        else $sql.='"'.$db->escape($_REQUEST['said']).'",';
        if(empty($_REQUEST['answer'])) $sql.='null,';
        else $sql.='"'.$db->escape($_REQUEST['answer']).'",';
        if(empty($_REQUEST['argument'])) $sql.='null,';
        else $sql.='"'.$db->escape($_REQUEST['argument']).'",';
        if(empty($_REQUEST['said_important'])) $sql.='null,';
        else $sql.='"'.$db->escape($_REQUEST['said_important']).'",';
        if(empty($_REQUEST['result_of_action'])) $sql.='null,';
        else $sql.='"'.$db->escape($_REQUEST['result_of_action']).'",';
        if(empty($_REQUEST['work_before_the_next_action'])) $sql.='null,';
        else $sql.='"'.$db->escape($_REQUEST['work_before_the_next_action']).'",';
//        if(empty($_REQUEST['date_next_action'])) $sql.='null,';
//        else {
//            $date = new DateTime($_REQUEST['date_next_action']);
//            $value = $date->format('Y-m-d');
//            $sql .= '"' .$value . '",';
//        }
        $sql .= $user->id.", 1)";
    }else {
        $sql = 'update llx_users_action set ';
        $sql.='`contactid`='.(empty($_REQUEST['contactid'])?'null':$_REQUEST['contactid']).', ';
        $sql.='`said`='.(empty($_REQUEST['said'])?'null':"'".$db->escape($_REQUEST['said'])."'").', ';
        $sql.='`answer`='.(empty($_REQUEST['answer'])?'null':"'".$db->escape($_REQUEST['answer'])."'").', ';
        $sql.='`argument`='.(empty($_REQUEST['argument'])?'null':"'".$db->escape($_REQUEST['argument'])."'").', ';
        $sql.='`said_important`='.(empty($_REQUEST['said_important'])?'null':"'".$db->escape($_REQUEST['said_important'])."'").', ';
        $sql.='`result_of_action`='.(empty($_REQUEST['result_of_action'])?'null':"'".$db->escape($_REQUEST['result_of_action'])."'").', ';
        $sql.='`work_before_the_next_action`='.(empty($_REQUEST['work_before_the_next_action'])?'null':"'".$db->escape($_REQUEST['work_before_the_next_action'])."'").', ';
        $sql.='`new`=1, ';
        $sql.='`id_usr`='.$user->id.' ';
        $sql.='where rowid='.$rowid;
    }
//echo '<pre>';
//var_dump($sql);
//echo '</pre>';
//die();
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
    $backtopage = $_REQUEST['backtopage'];
//    var_dump(strpos($backtopage, 'php?'));
//    die($backtopage);
    if(!(substr($_POST['action'],strlen($_POST['action'])-strlen('_and_create') )== '_and_create')) {
        if (empty($rowid)) {
            if (strpos($backtopage, 'php?'))
                $backtopage .= '&beforeload=close';
            else
                $backtopage .= '?beforeload=close';
        }
    }else{
        $backtopage = "http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/comm/action/card.php?mainmenu=".$_REQUEST['mainmenu']."&actioncode=".$_REQUEST['actioncode']."&action=create&parent_id=".$_REQUEST["actionid"]."&backtopage=".$backtopage;
    }
    header("Location: " . $backtopage);
}

/**
 * @param $rowid
 * @param bool $createaction
 * @param null $action_id
 */
function saveaction($rowid, $createaction = false, $action_id = null){
    global $user, $db;
//    print $action_id.'<br>';
//    return 'test';
    if((substr($_REQUEST['action'], 0, strlen('addonlyresult')) == 'addonlyresult' || substr($_REQUEST['action'], 0, strlen('updateonlyresult')) == 'updateonlyresult'))
        $socid = $_REQUEST['socid'];
    else {
//        var_dump($_REQUEST['actionid'], $_REQUEST['socid']);
//        die();
        if(!empty($_REQUEST['actionid']))
            $socid = get_soc_id($_REQUEST['actionid']);
        elseif(!empty($_REQUEST['socid']))
            $socid = $_REQUEST['socid'];
    }
    $newdate='';
    if(empty($action_id)&&isset($_REQUEST['actionid'])&&!empty($_REQUEST['actionid']))
        $action_id = $_REQUEST['actionid'];
    if(isset($_REQUEST['newdate'])&&!empty($_REQUEST['newdate'])&&isset($_REQUEST['actionid'])&&!empty($_REQUEST['actionid'])){
        $sql = "select datep, datep2 datef from llx_actioncomm where id = ".$action_id;
        $res = $db->query($sql);
        $action = $db->fetch_object($res);
        $minutes = ($action->datef-$action->datep)/60;
        $newdate = new DateTime($_REQUEST['newdate']);
        $datep = new DateTime($action->datep);
        $datef = new DateTime($action->datef);
        $mkDatep = $datep=dol_mktime($datep->format('H'), $datep->format('i'), $datep->format('s'), $datep->format('m'), $datep->format('d'), $datep->format('Y'));
        $mkDatef = $datef=dol_mktime($datef->format('H'), $datef->format('i'), $datef->format('s'), $datef->format('m'), $datef->format('d'), $datef->format('Y'));
        $mkNewDatep = $datep=dol_mktime($newdate->format('H'), $newdate->format('i'), $newdate->format('s'), $newdate->format('m'), $newdate->format('d'), $newdate->format('Y'));
        $mkNewDatef = $mkNewDatep + ($mkDatef-$mkDatep);
    }
    if(empty($user->id)){
        $user->fetch('',$_SESSION["dol_login"]);
    }
    if(empty($rowid)){
        $sql='insert into llx_societe_action(`new`,`action_id`,`proposed_id`, `socid`, `contactid`,`callstatus`, `said`,`answer`,
          `argument`,`said_important`,`result_of_action`,`work_before_the_next_action`,`need`,`fact_cost`,`id_usr`'.(empty($_REQUEST['proposed_id'])?'':',`interesting`').') values(';
        $sql .= '1,';
        if(empty($action_id)) {
            if (empty($_REQUEST['actionid'])) $sql .= 'null,';
            else $sql .= $_REQUEST['actionid'] . ',';
        }else{
            $sql .= $action_id. ',';
        }
        if(empty($_REQUEST['proposed_id'])) $sql.='null,';
        else $sql.=$_REQUEST['proposed_id'].',';
        if(empty($socid)) $sql.='null,';
        else $sql.=$socid.',';
        $sql.=(empty($_REQUEST['contactid'])?(empty($_REQUEST['changedContactID'])?"null":$_REQUEST['changedContactID']):$_REQUEST['contactid']).', ';
        $sql.=(empty($newdate)?(empty($_REQUEST['callstatus'])?"null":$_REQUEST['callstatus']):"null").', ';
        if(empty($_REQUEST['said'])) $sql.='null,';
        else $sql.='"'.$db->escape($_REQUEST['said']).'",';
        if(empty($_REQUEST['answer'])) $sql.='null,';
        else $sql.='"'.$db->escape($_REQUEST['answer']).'",';
        if(empty($_REQUEST['argument'])) $sql.='null,';
        else $sql.='"'.$db->escape($_REQUEST['argument']).'",';
        if(empty($_REQUEST['said_important'])) $sql.='null,';
        else $sql.='"'.$db->escape($_REQUEST['said_important']).'",';
        if(empty($_REQUEST['result_of_action'])) $sql.='null,';
        else $sql.='"'.$db->escape($_REQUEST['result_of_action']).'",';
        if(empty($_REQUEST['work_before_the_next_action'])) $sql.='null,';
        else $sql.='"'.$db->escape($_REQUEST['work_before_the_next_action']).'",';
        if(empty($_REQUEST['need'])) $sql.='null,';
        else $sql.='"'.$db->escape($_REQUEST['need']).'",';
        if(empty($_REQUEST['fact_cost'])) $sql.='null,';
        else $sql.=$db->escape($_REQUEST['fact_cost']).',';
//        if(empty($_REQUEST['date_next_action'])) $sql.='null,';
//        else {
//            $date = new DateTime($_REQUEST['date_next_action']);
//            $value = $date->format('Y-m-d');
//            $sql .= '"' .$value . '",';
//        }
        $sql .= $user->id.(empty($_REQUEST['proposed_id'])?'':(','.($_REQUEST['action']=='setNotInterestingProposed'?0:1))).")";
    }else {
        $sql = 'update llx_societe_action set ';
        $sql.='`contactid`='.(empty($_REQUEST['contactid'])?'null':$_REQUEST['contactid']).', ';
        $sql.='`callstatus`='.(empty($newdate)?(empty($_REQUEST['callstatus'])?'null':$_REQUEST['callstatus']):'null').', ';
        $sql.='`said`='.(empty($_REQUEST['said'])?'null':"'".$db->escape($_REQUEST['said'])."'").', ';
        $sql.='`answer`='.(empty($_REQUEST['answer'])?'null':"'".$db->escape($_REQUEST['answer'])."'").', ';
        $sql.='`argument`='.(empty($_REQUEST['argument'])?'null':"'".$db->escape($_REQUEST['argument'])."'").', ';
        $sql.='`said_important`='.(empty($_REQUEST['said_important'])?'null':"'".$db->escape($_REQUEST['said_important'])."'").', ';
        $sql.='`result_of_action`='.(empty($_REQUEST['result_of_action'])?'null':"'".$db->escape($_REQUEST['result_of_action'])."'").', ';
        $sql.='`work_before_the_next_action`='.(empty($_REQUEST['work_before_the_next_action'])?'null':"'".$db->escape($_REQUEST['work_before_the_next_action'])."'").', ';
        $sql.='`need`='.(empty($_REQUEST['need'])?'null':"'".$db->escape($_REQUEST['need'])."'").', ';
        $sql.='`fact_cost`='.(empty($_REQUEST['fact_cost'])?'null':$db->escape($_REQUEST['fact_cost'])).', ';
        $sql.='`id_usr`='.$user->id.' ';
        if(!empty($_REQUEST['proposed_id'])){
            $sql.=',`interesting`='.($_REQUEST['action']=='setNotInterestingProposed'?0:1);
        }
//        $sql.='`new`=1 ';
        $sql.='where rowid='.$rowid;
    }
//    echo '<pre>';
//    var_dump($_REQUEST);
//    echo '</pre>';
//    die($sql);

    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
    if(isset($_REQUEST['actioncode'])&&$_REQUEST['actioncode']=='AC_TEL'){//Незважаючи на те, дозвонився АТ чи ні, після збереження результатів виконання дзвінка встановлюю що дзвінок виконано
        $sql = "update llx_actioncomm set percent = 100 where id=".$_REQUEST["actionid"];
        $res = $db->query($sql);
        if(!$res){
            dol_print_error($db);
        }
    }
    if($_REQUEST['action']=='SaveResultAction'){
        if(!empty($rowid))
            die($rowid);
        else{
            $sql = "select max(rowid) rowid from llx_societe_action where id_usr = ".$user->id;
            $res = $db->query($sql);             
            $obj = $db->fetch_object($res);
            die($obj->rowid);
        }
    }
        
//    echo '<pre>';
//    var_dump($_REQUEST);
//    echo '</pre>';
//    die();
    if(isset($_REQUEST['actionid'])&&!empty($_REQUEST['actionid'])){
        $action_tmp = new ActionComm($db);
        $usersID = $action_tmp->getFromToUserIDs($_REQUEST['actionid']);
        if(!empty($usersID['for']))
            $action_tmp->ShowAction($usersID['for'],$usersID['from'],$_REQUEST['actionid'],'oncreate');
    }else{
        $action_tmp = new ActionComm($db);
        if(empty($socid)) {
            $sql = "select socid from llx_societe_action where rowid = ".$rowid;
            $res = $db->query($sql);
            if(!$res)
                dol_print_error($db);
            $obj = $db->fetch_object($res);
            $socid = $obj->socid;
        }
        if(empty($rowid)){
            require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
            $form = new Form($db);
            $rowid = $form->getlastid('llx_societe_action');
        }
        $usersID = $action_tmp->getUsersIDFromSociete($socid);
        foreach ($usersID as $value){
            if($value!=$user->id)
                $action_tmp->ShowAction($value,$user->id,-$rowid,'oncreate');
        }
    }

    if((!isset($_REQUEST['actionid'])||empty($_REQUEST['actionid'])) && !empty($rowid)) {
        $sql = "select action_id from llx_societe_action where rowid = ".$rowid;
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
        $obj = $db->fetch_object($res);
        $_REQUEST['actionid'] = $obj->action_id;
    }
    if(isset($_REQUEST['actionid'])&&!empty($_REQUEST['actionid'])) {

        $sql = "update llx_actioncomm set fact_cost = 
            (select sum(case when fact_cost is null then 0 else fact_cost end) from llx_societe_action
        where action_id = " . $_REQUEST['actionid'] . ")
        where id = " . $_REQUEST['actionid'];
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        $sql = "update llx_actioncomm set new = 1, dtValidValuer = case when fk_user_valuer = $user->id and percent = 99 then now() else null end
          where id = " . $_REQUEST['actionid'];
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
//        llxHeader('','test',null);
//        echo '<pre>';
//        var_dump($sql);
//        echo '</pre>';
//        die();

    }
//    echo '<pre>';
//    var_dump($_REQUEST);
//    echo '</pre>';
//    die();
    $update_need_sql = '';
    if(!empty($socid)){
        $postfix = '';
        if(in_array('sale', array($user->respon_alias,$user->respon_alias2)))
            $postfix = 'comerc';
        else if(in_array('purchase', array($user->respon_alias,$user->respon_alias2)))
            $postfix = 'service';
        else
            $postfix = 'mentor';

            $update_need_sql = "update llx_societe set lastdate = '".date('Y-m-d')."', lastdate".$postfix."='".date('Y-m-d')."', futuredate".$postfix."= null, llx_societe.need = " . (empty($_REQUEST['need']) ? ("null") : ("'" . $db->escape($_REQUEST['need'])) . "'") . "
          where 1 and llx_societe.rowid =".$socid;
    }elseif(!empty($_REQUEST['action_id'])||!empty($rowid)) {
        $update_need_sql = "update llx_societe, llx_societe_action,llx_actioncomm
          set llx_societe.need = " . (empty($_REQUEST['need']) ? ("null") : ("'" . $db->escape($_REQUEST['need'])) . "'") . "
          where 1 ";
        if (empty($_REQUEST['action_id'])) {
            $update_need_sql .= " and llx_societe_action.rowid = " . $rowid;
        } else {
            $update_need_sql .= " and llx_actioncomm.id = " . $_REQUEST['action_id'];
        }
        $update_need_sql .= " and llx_actioncomm.id = llx_societe_action.action_id";
        $update_need_sql .= " and llx_societe.rowid = llx_actioncomm.fk_soc";
    }
//    echo '<pre>';
//    var_dump($_REQUEST);
//    echo '</pre>';
//    die();

    if(!empty($update_need_sql)){
        $res = $db->query($update_need_sql);
        if (!$res) {
            dol_print_error($db);
        }
    }
//    if($_REQUEST['action'] == 'update'||!(substr($_REQUEST['action'], 0, strlen('addonlyresult')) == 'addonlyresult' || (substr($_REQUEST['action'], 0, strlen('updateonlyresult')) == 'updateonlyresult' && strlen($_REQUEST['action'])==strlen('updateonlyresult')))) {

    if(isset($_REQUEST['changedContactID'])&&!empty($_REQUEST['changedContactID'])&&!empty($_REQUEST["actionid"])){//Змінюю контактне лице, якщо його змінили під час внесення даних
        $sql = "update llx_actioncomm set fk_contact = ".$_REQUEST['changedContactID']." where id = ".$_REQUEST["actionid"];
        $res = $db->query($sql);
        if(!$res){
            dol_print_error($db);
        }
    }
    if($_REQUEST['action'] == 'update'||(substr($_REQUEST['action'], 0, strlen('saveonlyresult')) == 'saveonlyresult' || substr($_REQUEST['action'], 0, strlen('addonlyresult')) == 'addonlyresult' || (substr($_REQUEST['action'], 0, strlen('updateonlyresult')) == 'updateonlyresult' && strlen($_REQUEST['action'])==strlen('updateonlyresult')))) {
        if (empty($rowid))
            $rowid = get_last_id();
        $TypeAction = array('AC_GLOBAL', 'AC_CURRENT');
        $sql = 'SELECT `code`, `llx_actioncomm`.`overdue`, `llx_actioncomm`.`id`, percent from `llx_actioncomm` inner join llx_societe_action on llx_societe_action.`action_id` = `llx_actioncomm`.`id` where llx_societe_action.`rowid` = ' . $rowid;
//        die($sql);
        $res = $db->query($sql);
        $objCode = $db->fetch_object($res);
        $complete=$_REQUEST['complete'];
        $authorID = 0;
        if($objCode->percent == '99' && ($objCode->percent != $complete || $objCode->overdue)){
            $sql = 'update llx_actioncomm set percent = '.$complete.', new = 0 where id = '.$_REQUEST['actionid'];
            $db->query($sql);
            $action = new ActionComm($db);
            $action->setOuterdueStatus($_REQUEST['actionid']);
        }
        if($complete == '100' && (empty($_REQUEST["subaction"]) || !in_array($_REQUEST["subaction"], array('validate')))){
            $sql = 'select fk_user_author from `llx_actioncomm` where id='.$_REQUEST['actionid'];
            $res = $db->query($sql);
            if(!$res)
                dol_print_error($db);
            $obj = $db->fetch_object($res);
            $authorID = $obj->fk_user_author;
            if($obj->fk_user_author != $user->id)
                $complete = '99';
        }
        if($complete == '100' && $_REQUEST["subaction"] == 'validate'){//Ставлю відмітку "Затвердження" на макет розсилки
            $sql = "select subaction_id from llx_actioncomm where id = ".$_REQUEST["actionid"];
            $res = $db->query($sql);
            if(!$res)
                dol_print_error($db);
            $obj = $db->fetch_object($res);

            $sql = "update llx_mailing set date_valid = now(), fk_user_valid = ".$user->id." where 1 and rowid=".$obj->subaction_id;
//            die($sql);
            $res = $db->query($sql);
            if(!$res)
                dol_print_error($db);
        }
        $sql = "select llx_societe_action.action_id from `llx_societe_action` where 1 and llx_societe_action.rowid = " . $rowid;
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
        $obj = $db->fetch_object($res);
        $action_id = $obj->action_id;
//        die($action_id);
        if(!empty($action_id)) {//Оптимізація запроса
            $sql = "update llx_actioncomm set `new` = 1, " .
                (!empty($newdate) ? ("datep='" . date('Y-m-d H:i:s', $mkNewDatep) . "', datep2='" . date('Y-m-d H:i:s', $mkNewDatef) . "' ,") : "") .
                " dateconfirm = case when dateconfirm is null then Now() else dateconfirm end, datea= case when datea is null then Now() else datea end " .
                (empty($newdate) ? (in_array($objCode->code, $TypeAction) ? ', percent =' . (!empty($complete) ? $complete : 'percent') : ', percent = 100') : "") . ", type='" . $_REQUEST['typeSetOfDate'] . "'
            where llx_actioncomm.id = ".$action_id;
//            var_dump($sql);
//            die();
            $res = $db->query($sql);
            if (!$res)
                dol_print_error($db);
            $Actions = new ActionComm($db);
            $Actions->setOuterdueStatus($action_id);
        }
        //Встановлюю мітку про виконання на первинній задачі, якщо значення статусу = виконано
        if($complete == '100') {

            $sql = 'update llx_actioncomm, llx_actioncomm sub_action set `llx_actioncomm`.`new` = 1,
            `llx_actioncomm`.dateconfirm = case when `llx_actioncomm`.dateconfirm is null then Now() else `llx_actioncomm`.dateconfirm end,
            `llx_actioncomm`.datea= case when `llx_actioncomm`.datea is null then Now() else `llx_actioncomm`.datea end, `llx_actioncomm`.percent = 99
            where sub_action.id='.$_REQUEST["actionid"].'
            and sub_action.fk_parent = llx_actioncomm.id
            and llx_actioncomm.fk_parent = 0';
            $res = $db->query($sql);
            $Actions = new ActionComm($db);
            $Actions->setOuterdueStatus($_REQUEST['actionid']);
        }
        if(!empty($_REQUEST["actionid"])) {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/comm/action/class/actioncomm.class.php';
            $Actions = new ActionComm($db);
            $chain_action = $Actions->GetChainActions($_REQUEST["actionid"]);
//            echo '<pre>';
//            var_dump($chain_action);
//            echo '</pre>';
//            die();
            $sql = "update llx_actioncomm set datelastaction = Now()
            where id in (" . implode(',', $chain_action) . ") and active = 1";
            $res = $db->query($sql);
            if (!$res)
                dol_print_error($db);
            if(count($chain_action)>0 && $authorID == $user->id) {//Якщо основна задача очикує підтвердження про виконання, але результат дії не влаштовує - знімаю відмітку
                $sql = "update llx_actioncomm set percent = 50,`llx_actioncomm`.`new` = 1 where id = " . $chain_action[0] . " and percent = 99";
                $res = $db->query($sql);
                if (!$res)
                    dol_print_error($db);
            }
        }

//    if($res)
//        dol_print_error($db);

//    die(substr($_REQUEST['backtopage'], 1, strlen($_REQUEST['backtopage'])-2));
//    die(DOL_URL_ROOT);
    }else{
        $TypeAction = array('AC_GLOBAL', 'AC_CURRENT');
        if(!in_array($_REQUEST['actioncode'], $TypeAction)) {
            $sql = 'update llx_actioncomm set datea=Now(),  percent = 100
            where llx_actioncomm.id = ' . $_REQUEST['actionid'] . '
            and percent <> 100';
            $res = $db->query($sql);
            $Actions = new ActionComm($db);
            $Actions->setOuterdueStatus($_REQUEST['actionid']);
        }
    }

    if(!$createaction) {
        if(substr($_REQUEST['backtopage'], 0, 1) == "'" && substr($_REQUEST['backtopage'], strlen($_REQUEST['backtopage'])-1, 1) == "'")
            $backtopage = substr($_REQUEST['backtopage'], 1, strlen($_REQUEST['backtopage']) - 2);
        else
            $backtopage = $_REQUEST['backtopage'];

        if(isset($_REQUEST['proposed_id'])&&!empty($_REQUEST['proposed_id'])) {
            if(strpos($backtopage, 'php?'))
                $backtopage .= '&beforeload=close';
            else
                $backtopage .= '?beforeload=close';
        }
//        echo '<pre>';
//        var_dump(urldecode($backtopage));
//        echo '</pre>';
//        die();
        if($_REQUEST["action"]!='saveonlyresult') {
            $link = urldecode($backtopage);
            if(substr($link, 0,1)=="'")
                $link = 'http://'.$_SERVER["HTTP_HOST"] . substr($link, 1, strlen($link)-2);
            else
                $link = 'http://'.$_SERVER["HTTP_HOST"] . $link;
            header("Location: " .$link);
            die();
        }

    }else{
        if(substr($_REQUEST['backtopage'], 0, 1) == "'" && substr($_REQUEST['backtopage'], strlen($_REQUEST['backtopage'])-1, 1) == "'")
            $backtopage = substr($_REQUEST['backtopage'], 1, strlen($_REQUEST['backtopage']) - 2);
        else
            $backtopage = $_REQUEST['backtopage'];
//      $backtopage = urlencode(htmlspecialchars(substr($_REQUEST['backtopage'], 1, strlen($_REQUEST['backtopage'])-2)));
//      $backtopage = urlencode(substr($_REQUEST['backtopage'], 1, strlen($_REQUEST['backtopage'])-2));
//        $backtopage = $_REQUEST['backtopage'];
//        var_dump($backtopage);
//        die();

        if(!strpos($_REQUEST['backtopage'], 'socid=')) {
            if(!strpos($backtopage, 'php?'))
                $backtopage .= "?socid%3D" . $socid . "%26mainmenu%3D" . $_REQUEST['mainmenu'];
            else
                $backtopage .= "&socid%3D" . $socid . "%26mainmenu%3D" . $_REQUEST['mainmenu'];
        }
//        var_dump($_REQUEST['assignedusers']);
//        die();
//        if(count($_POST)){
//            llxHeader();
//            echo '<pre>';
//            var_dump($_POST);
//            echo '</pre>';
//            die();
//        }

        $link = "http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/comm/action/card.php?mainmenu=".$_REQUEST['mainmenu']."&actioncode=".$_REQUEST['actioncode'].
            "&socid=".$socid."&action=create&parent_id=".$_REQUEST["actionid"];
        if(!empty($_REQUEST['assignedusers'])) {
            $link .= '&addassignedtouser=1';
            $json = '"'.$user->id.'":{"id":"'.$user->id.'","mandatory":0,"transparency":null}';


            $userID = str_replace('"','',substr($_REQUEST["assignedusers"], 1, strlen($_REQUEST["assignedusers"])-2));
            $userID = explode(',',$userID);
            foreach ($userID as $value){
                $json.= ','. '"'.$value.'":{"id":"'.$value.'","mandatory":0,"transparency":null}';
            }
//            $json.='}';
            $note = 'Вашим клієнтам буде відправлено електронну розсилку через дві години. Переглянте будь ласка її зміст';
//            echo '<pre>';
//            var_dump($json);
//            echo '</pre>';
//            die();
            $sql = "select subaction_id from llx_actioncomm where id = ".$_REQUEST["actionid"];
            $res = $db->query($sql);
            if(!$res)
                dol_print_error($db);
            $obj = $db->fetch_object($res);
            $link.='&subaction=sendmail&subaction_id='.$obj->subaction_id.'&note='.$note.='&actioncode=AC_CURRENT&groupoftask=13&assignedUser='.$json;
            $backtopage = '/dolibarr/htdocs/current_plan.php?idmenu=10423&mainmenu=current_task&leftmenu=';
        }
        $link.="&backtopage=".str_replace("'","",$backtopage);
//        die('test '.$link);
//die('test');
        header("Location: ".$link);
    }
}
