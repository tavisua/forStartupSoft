<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 14.01.2016
 * Time: 13:45
 */
require '../../main.inc.php';


if(isset($_POST['action']) && ($_POST['action'] == 'update' || $_POST['action'] == 'update_and_create'
        || $_POST['action'] == 'addonlyresult' || $_POST['action'] == 'updateonlyresult'
    || $_POST['action'] == 'addonlyresult_and_create' || $_POST['action'] == 'updateonlyresult_and_create')){
//    var_dump((substr($_POST['action'],strlen($_POST['action'])-strlen('_and_create') )== '_and_create'));
//    die();

    saveaction($_POST['rowid'], (substr($_POST['action'],strlen($_POST['action'])-strlen('_and_create') )== '_and_create'));
}
if($_POST['action'] == 'saveuseraction'){//Зберігаю результати перемовин зі співробітником
    saveuseraction($_POST['rowid'] );
}
require_once DOL_DOCUMENT_ROOT.'/core/lib/agenda.lib.php';
require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formactions.class.php';
global $user, $db;
if($_GET['action'] == 'addonlyresult' || $_GET['action'] == 'addonlyresult_and_create' || $_GET['action'] == 'useraction') {
    llxHeader('', $langs->trans("AddResultAction"), $help_url);
}elseif(isset($_REQUEST["onlyresult"])&&$_REQUEST["onlyresult"]=='1'){
    llxHeader('', $langs->trans("EditResultAction"), $help_url);
}else
    llxHeader('',$langs->trans("EditAction"),$help_url);
$action_id = 0;
$socid = 0;
//echo '<pre>';
//var_dump($_REQUEST);
//echo '</pre>';
//die();
if (isset($_REQUEST["action_id"])) {
    $action_id = $_REQUEST["action_id"];
    $sql = "select * from llx_societe_action where 1 ";
    if(!isset($_REQUEST["onlyresult"]) || empty($_REQUEST['onlyresult']))
        $sql.="and action_id=" . $_REQUEST["action_id"];
    else
        $sql.="and rowid=" . $_REQUEST["action_id"];
    $res = $db->query($sql);
    if (!$res) {
        dol_print_error($db);
    }
}
if(!isset($_REQUEST["onlyresult"])||empty($_REQUEST["onlyresult"])) {
    $object = new ActionComm($db);
    $object->fetch($action_id);
    $socid = $object->socid;

//    echo '<pre>';
//    var_dump($object);
//    echo '</pre>';
//    die();
}elseif($_REQUEST["action"]=='edituseration'){


}else{
    $object = $db->fetch_object($res);
    $socid = $object->socid;
}

$head=actions_prepare_head($object);
if($_GET['action'] == 'addonlyresult' || $_GET['action'] == 'useraction')
    print_fiche_titre($langs->trans("AddResultAction"));
elseif($_REQUEST["action"]=='edituseration')
    print_fiche_titre('Редагувати перемовини');
elseif(isset($_REQUEST["onlyresult"])&&$_REQUEST["onlyresult"]=='1'){
    print_fiche_titre($langs->trans("EditResultAction"));
}else
    print_fiche_titre($langs->trans("EditAction"));

if (! empty($conf->use_javascript_ajax))
{
    print "\n".'<script type="text/javascript">';
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
    print '</script>'."\n";
}
//        print '<div class="tabPage">';
//print '<form id = "formaction" name="formaction" action="'.$_SERVER['PHP_SELF'].'" method="POST">';
//print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
//print '<input type="hidden" name="action" value="update">';
//print '<input type="hidden" name="id" value="'.$object->id.'">';
//print '<input type="hidden" name="ref_ext" value="'.$object->ref_ext.'">';
//print '<input type="hidden" name="backtopage" value="'.$_GET['backtopage'].'">';
//if (empty($conf->global->AGENDA_USE_EVENT_TYPE)) print '<input type="hidden" name="actioncode" value="'.$object->type_code.'">';

if(!($_GET['action'] == 'addonlyresult' || (isset($_REQUEST["onlyresult"])&&$_REQUEST["onlyresult"]=='1'))) {
    if(!($_GET['action'] == 'useraction'||$_GET['action'] == 'edituseration'))
        dol_fiche_head($head, 'event_desc', $langs->trans("Action"), 0, 'action');
    $contactlist='';
}else {
    $contactid = empty($object->contactid)?$_GET['contactid']:$object->contactid;
    $lastactiveaction = getLastContact();
    if(empty($contactid))
        $contactid = count($lastactiveaction)>0?$lastactiveaction['contactid']:1;
    $form = new Form($db);
    $contactlist = '<tr><td>Контактне лице</br>'.$form->selectcontacts(empty($_GET['socid'])?$object->socid:$_GET['socid'], $contactid, 'contactid', 1).'</td></tr>';
    $productname = explode(',', $_REQUEST['productsname']);
    $needlist = explode(',', $_REQUEST['need']);
    $object->resultaction['answer']='';
    $action_id = count($lastactiveaction)>0?$lastactiveaction['actionid']:0;
    for($i=0; $i<count($productname);$i++){
        $object->resultaction['answer'].=$productname[$i].' '.(empty($needlist[$i])?'не потрібно':$needlist[$i]).'; ';
    }
}
//echo '<pre>';
//var_dump($action_id);
//echo '</pre>';
//die();

$societe = new Societe($db);
$societe->fetch(empty($object->socid)&&$_GET['action'] == 'addonlyresult'?$_GET['socid']:$object->socid);

$formactions = new FormActions($db);
if($_GET['action'] == 'edituseration'){//Якщо редагуються результати перемовин зі співробітниками
    $sql = "select rowid,said,answer,argument,said_important,result_of_action,work_before_the_next_action,date_next_action,work_before_the_next_action_mentor,date_next_action_mentor
        from llx_users_action
        where rowid = ".$_REQUEST['rowid'];
    $res=$db->query($sql);
    if(!$res)
        dol_print_error($db);
    $object = $db->fetch_object($res);
    $said = empty($object->resultaction['said']) ? $object->said : $object->resultaction['said'];
}else {
    $said = empty($object->resultaction['said']) ? $object->said : $object->resultaction['said'];
    if (empty($said))
        $said = $_REQUEST['said'];
////print '<div class="tabBar">';
}
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/responsibility/sale/addaction.html';
print '</div>';

//llxFooter();
exit();
function getUserName($id_usr){
    global $db;
    $sql = "select lastname from llx_user where rowid = ".$id_usr;
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    return $obj->lastname;
}
function getLastContact(){
    global $db;
	$sql = "select id, `fk_contact` from llx_actioncomm
	where fk_soc = ".$_REQUEST['socid']."
	and `llx_actioncomm`.`code` in (select `code` from 	llx_c_actioncomm where type in ('user','system'))
	and percent <> 100
	and `llx_actioncomm`.`code` not in ('AC_CURRENT','AC_GLOBAL')
	and date(`llx_actioncomm`.`datep`)<=date(Now())";
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
    $socid = $_REQUEST['socid'];
    $object = new  Societe($db);
    $object->fetch($socid);
    $CategoryOfCustomer = $object->getCategoryOfCustomer();
    $FormOfGoverment = $object->getFormOfGoverment();
    $contactid=0;
    $selectcountact = selectcontact($socid);
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
        $sql='insert into llx_users_action(`action_id`,`proposed_id`, `socid`, `contactid`, `said`,`answer`,
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
    if(empty($rowid)) {
        if (strpos($backtopage, 'php?'))
            $backtopage .= '&beforeload=close';
        else
            $backtopage .= '?beforeload=close';
    }
    header("Location: " . $backtopage);
}
function saveaction($rowid, $createaction = false){
    global $user, $db;
//    echo '<pre>';
//    var_dump($_REQUEST['backtopage']);
//    echo '</pre>';
//    die();
    if((substr($_REQUEST['action'], 0, strlen('addonlyresult')) == 'addonlyresult' || substr($_REQUEST['action'], 0, strlen('updateonlyresult')) == 'updateonlyresult'))
        $socid = $_REQUEST['socid'];
    else
        $socid = get_soc_id($_REQUEST['actionid']);
//    echo '<pre>';
//    var_dump($createaction);
//    echo '</pre>';
//    die();
    if(empty($rowid)){
        $sql='insert into llx_societe_action(`action_id`,`proposed_id`, `socid`, `contactid`, `said`,`answer`,
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
        $sql = 'update llx_societe_action set ';
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
//    llxHeader('','test',null);
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();

    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
//    var_dump($res);
//    die();
    if(!(substr($_REQUEST['action'], 0, strlen('addonlyresult')) == 'addonlyresult' || (substr($_REQUEST['action'], 0, strlen('updateonlyresult')) == 'updateonlyresult' && strlen($_REQUEST['action'])==strlen('updateonlyresult')))) {
        if (empty($rowid))
            $rowid = get_last_id();
        $TypeAction = array('AC_GLOBAL', 'AC_CURRENT');
        $sql = 'SELECT `code`, `llx_actioncomm`.`id` from `llx_actioncomm` inner join llx_societe_action on llx_societe_action.`action_id` = `llx_actioncomm`.`id` where llx_societe_action.`rowid` = ' . $rowid;
//        die($sql);
        $res = $db->query($sql);
        $objCode = $db->fetch_object($res);
        $complete=$_REQUEST['complete'];
        if($complete == '100'){
            $sql = 'select fk_user_author from `llx_actioncomm` where id='.$_REQUEST['actionid'];
            $res = $db->query($sql);
            if(!$res)
                dol_print_error($db);
            $obj = $db->fetch_object($res);
            if($obj->fk_user_author != $user->id)
                $complete = '99';
        }

        $sql = 'update llx_actioncomm set dateconfirm = case when dateconfirm is null then Now() else dateconfirm end, datea= case when datea is null then Now() else datea end ' . (in_array($objCode->code, $TypeAction) ? ', percent ='.$complete : ', percent = 100') . '
            where llx_actioncomm.id in (select llx_societe_action.action_id from `llx_societe_action` where 1
            and llx_societe_action.rowid = ' . $rowid . ')';
//    var_dump($sql);
//    die();
        $res = $db->query($sql);
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
//            var_dump($sql);
//            die();
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
        header("Location: " . $backtopage);

    }else{
      $backtopage = urlencode(htmlspecialchars(substr($_REQUEST['backtopage'], 1, strlen($_REQUEST['backtopage'])-2)));
      $backtopage = urlencode(substr($_REQUEST['backtopage'], 1, strlen($_REQUEST['backtopage'])-2));
//        $backtopage = $_REQUEST['backtopage'];
//        var_dump($backtopage);
//        die();
        if(!strpos($_REQUEST['backtopage'], 'socid=')) {
            if(!strpos('php?', $backtopage))
                $backtopage .= "?socid%3D" . $socid . "%26mainmenu%3D" . $_REQUEST['mainmenu'];
            else
                $backtopage .= "&socid%3D" . $socid . "%26mainmenu%3D" . $_REQUEST['mainmenu'];
        }
//        var_dump($backtopage);
//        die();
        $link = "http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/comm/action/card.php?mainmenu=".$_REQUEST['mainmenu']."&actioncode=".$_REQUEST['actioncode']."&socid=".$socid."&action=create&parent_id=".$_REQUEST["actionid"]."&backtopage=".$backtopage;
//        die($link);
        header("Location: ".$link);
    }
}
