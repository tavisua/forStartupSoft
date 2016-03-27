<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 14.01.2016
 * Time: 13:45
 */
require '../../main.inc.php';
if(isset($_POST['action']) && ($_POST['action'] == 'update' || $_POST['action'] == 'update_and_create' || $_POST['action'] == 'addonlyresult')){
    saveaction($_POST['rowid'], ($_POST['action'] == 'update_and_create'));
}
//echo '<pre>';
//var_dump($_POST);
//echo '</pre>';
//die();
require_once DOL_DOCUMENT_ROOT.'/core/lib/agenda.lib.php';
require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
global $user, $db;
if($_GET['action'] == 'addonlyresult')
    llxHeader('',$langs->trans("AddResultAction"),$help_url);
else
    llxHeader('',$langs->trans("EditAction"),$help_url);
$action_id = 0;
if(isset($_REQUEST["id"])){
    $action_id = $_REQUEST["id"];
    $sql = "select * from llx_societe_action where action_id=".$_REQUEST["id"];
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
}
$object = new ActionComm($db);
$object->fetch($action_id);

//echo '<pre>';
//var_dump($object->socid);
//echo '</pre>';
//die();

$head=actions_prepare_head($object);
if($_GET['action'] == 'addonlyresult')
    print_fiche_titre($langs->trans("AddResultAction"));
else
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

if($_GET['action'] != 'addonlyresult') {
    dol_fiche_head($head, 'event_desc', $langs->trans("Action"), 0, 'action');
    $contactlist='';
}else {
    $form = new Form($db);
    $contactlist = '<tr><td>Контактне лице</br>'.$form->selectcontacts($_GET['socid'], '', 'contactid', 1).'</td></tr>';
}
//var_dump(htmlspecialchars($contactlist));
//die();

$societe = new Societe($db);
$societe->fetch(empty($object->socid)&&$_GET['action'] == 'addonlyresult'?$_GET['socid']:$object->socid);
//echo '<pre>';
//var_dump($object->resultaction->work_before_the_next_action);
//echo '</pre>';
//die();

//print '<div class="tabBar">';
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/responsibility/sale/addaction.html';
//print '</div>';

llxFooter();
exit();


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
function saveaction($rowid, $createaction = false){
    global $user, $db;
//    echo '<pre>';
//    var_dump($_POST);
//    echo '</pre>';
//    var_dump(empty($rowid));
//    die();
    if($_REQUEST['action'] == 'addonlyresult')
        $socid = $_REQUEST['socid'];
    else
        $socid = get_soc_id($_REQUEST['actionid']);
    if(empty($rowid)){
        $sql='insert into llx_societe_action(`action_id`, `socid`, `contactid`, `said`,`answer`,
          `argument`,`said_important`,`result_of_action`,`work_before_the_next_action`,`id_usr`, `new`) values(';
        if(empty($_REQUEST['actionid'])) $sql.='null,';
        else $sql.=$_REQUEST['actionid'].',';
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
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
    if($_REQUEST['action'] != 'addonlyresult') {
        if (empty($rowid))
            $rowid = get_last_id();
        $TypeAction = array('AC_GLOBAL', 'AC_CURRENT');
        $sql = 'SELECT `code` from `llx_actioncomm` where id = ' . $rowid;
        $res = $db->query($sql);
        $obj = $db->fetch_object($res);


        $sql = 'update llx_actioncomm set datea=Now() ' . (in_array($obj->code, $TypeAction) ? '' : ', percent = 100') . '
            where llx_actioncomm.id in (select llx_societe_action.action_id from `llx_societe_action` where 1
            and llx_societe_action.rowid = ' . $rowid . ')
            and datea is null';
//    die($sql);
        $res = $db->query($sql);
//    if($res)
//        dol_print_error($db);

//    die(substr($_REQUEST['backtopage'], 1, strlen($_REQUEST['backtopage'])-2));
//    die(DOL_URL_ROOT);
    }
    if(!$createaction)
        header("Location: ".substr($_REQUEST['backtopage'], 1, strlen($_REQUEST['backtopage'])-2));
    else{
        $link = "http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/comm/action/card.php?mainmenu=".$_REQUEST['mainmenu']."&actioncode=".$_REQUEST['actioncode']."&socid=".$socid."&action=create&parent_id=".$_REQUEST["actionid"]."&backtopage=".urlencode(htmlspecialchars(substr($_REQUEST['backtopage'], 1, strlen($_REQUEST['backtopage'])-2)));
//        die($link);
        header("Location: ".$link);
    }
}
