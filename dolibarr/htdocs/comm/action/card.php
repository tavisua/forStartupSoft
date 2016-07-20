<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2014 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005      Simon TOSSER         <simon@kornog-computing.com>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2010-2013 Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2013      Florian Henry        <florian.henry@open-concept.pro>
 * Copyright (C) 2014      Cedric GROSS         <c.gross@kreiz-it.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *       \file       htdocs/comm/action/card.php
 *       \ingroup    agenda
 *       \brief      Page for event card
 */
require '../../main.inc.php';

//llxHeader('', $langs->trans("AddAction"), $help_url);
//
//echo '<pre>';
//var_dump($_REQUEST);
//echo '</pre>';
//die();
if(isset($_POST['action'])&&$_POST['action'] == 'create' && isset($_POST['parent_id'])&&!empty($_POST['parent_id'])){
	if(getActionStatus($_POST['parent_id'])=='100'){
		llxHeader('', 'Помилка', '');
		print_fiche_titre('Вже встановлено статус "Прийнято" для дії');
		die('тому неможна створювати додаткову піддію чи планувати контакт');
	}
//	die('test');
//	var_dump(getActionStatus($_POST['parent_id']));
}
if(isset($_REQUEST['id_usr'])&&!empty($_REQUEST['id_usr'])&&$_REQUEST['id_usr']!=$user->id){
//$test = '{"6":{"id":"6","mandatory":0,"transparency":null},"7":{"id":"7","transparency":"on","mandatory":1}}';
//var_dump(json_decode($test));
//die();
	$json = '{"'.$user->id.'":{"id":"'.$user->id.'","mandatory":0,"transparency":null},"'.$_REQUEST['id_usr'].'":{"id":"'.$_REQUEST['id_usr'].'","transparency":"on","mandatory":1}}';
	$_SESSION['assignedtouser']=$json;
}
require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
//var_dump($_REQUEST);
//die();
if($_GET['action']=='get_exectime'){

	$Action = new ActionComm($db);
	$exec_time = $Action->GetExecTime($_GET['code']);
    echo $exec_time;
    exit();

}elseif($_GET['action']=='getActionStatus'){
	echo getActionStatus($_GET['action_id']);
	exit();
}elseif($_GET['action']=='ChangeDateAction'){
	echo changeDateAction($_GET['action_id'],$_GET['newdate'],$_GET['minutes'],$_GET['type']);
	exit();
}elseif($_GET['action']=='validateDataAction'){
	$Action = new ActionComm($db);
	echo $Action->validateDateAction($_REQUEST['date'], $_REQUEST['id_usr'],$_REQUEST['minutes'],$_REQUEST['prioritet']);
	exit();
}elseif($_GET['action']=='getlastactivecontact'){

	$sql = "select `fk_contact` from llx_actioncomm
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
		return 0;
	else{
		$obj = $db->fetch_object($res);
		return $obj->fk_contact;
	}
}elseif($_GET['action']=='del_task'){

	$sql = 'update llx_actioncomm set active = 0, fk_user_mod='.$user->id.' where id='.$_REQUEST['id'];
	$res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
    echo 1;
	exit();
}elseif($_GET['action']=='get_contactlist'){
    require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
    $form = new Form($db);
    $list = $form->select_contacts($_GET['socid'], '','contactid',1);
    echo $list;
    exit();
}elseif($_GET['action']=='delete_action'){

	if(substr($_GET['rowid'], 0, 1)=='_')
	    $sql = 'update llx_societe_action set active = 0 where rowid='.str_replace('_','', $_GET['rowid']);
	else {
        require_once $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/comm/action/class/actioncomm.class.php';
        $Actions = new ActionComm($db);
        $chain_action = $Actions->GetChainActions($_REQUEST["rowid"]);

		$chain_action = array_flip($chain_action); //Меняем местами ключи и значения
		unset ($chain_action[$_GET['rowid']]) ; //Удаляем элемент массива
		$chain_action = array_flip($chain_action); //Меняем местами ключи и значения

		$sql = "select max(datec) maxdate from llx_actioncomm where id in (".(implode(',', $chain_action)).")";
		$res = $db->query($sql);
		if(!$res){
			dol_print_error($db);
		}
		$obj = $db->fetch_object($res);
		$date = new DateTime($obj->maxdate);
		if(count($chain_action)>1)
			$sql = "update llx_actioncomm set datefutureaction = '".$date->format('Y-m-d H:i:s')."' where id in (".(implode(',', $chain_action)).")";
		else
			$sql = "update llx_actioncomm set datefutureaction = null where id in (".(implode(',', $chain_action)).")";
		$res = $db->query($sql);
		if(!$res){
			dol_print_error($db);
		}
		$sql = 'update llx_actioncomm set active = 0 where id=' . $_GET['rowid'];
	}
    $res = $db->query($sql);
    if(!$res){
        var_dump($sql);
        dol_print_error($db);
    }
    echo 1;
    exit();
}elseif($_GET['action']=='received_action'){

    $sql = 'update llx_actioncomm set `dateconfirm` = Now(), `new`=0, `percent`= case when `percent` = -1 then 0 else `percent` end  where id='.$_GET['rowid'];
//	die($sql);
    $res = $db->query($sql);
    if(!$res){
        var_dump($sql);
        dol_print_error($db);
    }
    $sql = 'update llx_societe_action set `new`=0  where action_id='.$_GET['rowid'];
//	die($sql);
    $res = $db->query($sql);
    if(!$res){
        var_dump($sql);
        dol_print_error($db);
    }

    return 1;
    exit();
}elseif($_GET['action']=='shownote'){

	$sql = 'select note from llx_actioncomm where id='.$_GET['rowid'];

	$res = $db->query($sql);
    if(!$res){
//        var_dump($sql);
        dol_print_error($db);
    }
	$obj = $db->fetch_object($res);
	echo trim($obj->note);
	exit();

}elseif($_GET['action']=='confirm_exec'){

	$sql = 'select period, datep, datepreperform from `llx_actioncomm` where id='.$_GET['rowid'];
	$res = $db->query($sql);
	if(!$res){
        dol_print_error($db);
    }
	$obj = $db->fetch_object($res);
	if(!empty($obj->period)){//Створюю таке саме завдання через вказаний інтервал часу
		$date = new DateTime($obj->datep);
		$datepreperform = new DateTime($obj->datepreperform);
		$mkDate = mktime('0','0','0',$date->format('m'),$date->format('d'),$date->format('Y'));
//		var_dump(date('d.m.Y',$mkDate));
		switch($obj->period){
			case 'EveryHalfYear':{
				$newDate=mktime('0','0','0',6+(int)$date->format('m'),$date->format('d'),$date->format('Y'));
				$newPreperform = mktime('0','0','0',6+(int)$datepreperform->format('m'),$datepreperform->format('d'),$datepreperform->format('Y'));
			}break;
			case 'EveryDay':{
				$newDate=mktime('0','0','0',$date->format('m'),1+(int)$date->format('d'),$date->format('Y'));
				$newPreperform = mktime('0','0','0',$datepreperform->format('m'),1+(int)$datepreperform->format('d'),$datepreperform->format('Y'));
//				var_dump(date('Y-m-d',$newDate));
//				die();
			}break;
			case 'EveryWeek':{
				$newDate=mktime('0','0','0',$date->format('m'),7+(int)$date->format('d'),$date->format('Y'));
				$newPreperform = mktime('0','0','0',$datepreperform->format('m'),7+(int)$datepreperform->format('d'),$datepreperform->format('Y'));
			}break;
			case 'EveryMonth':{
				$newDate=mktime('0','0','0',1+(int)$date->format('m'),$date->format('d'),$date->format('Y'));
				$newPreperform = mktime('0','0','0',1+(int)$datepreperform->format('m'),$datepreperform->format('d'),$datepreperform->format('Y'));
			}break;
			case 'Quarterly':{
				$newDate=mktime('0','0','0',3+(int)$date->format('m'),$date->format('d'),$date->format('Y'));
				$newPreperform = mktime('0','0','0',3+(int)$datepreperform->format('m'),$datepreperform->format('d'),$datepreperform->format('Y'));
			}break;
			case 'Annually':{
				$newDate=mktime('0','0','0',$date->format('m'),$date->format('d'),1+(int)$date->format('Y'));
				$newPreperform = mktime('0','0','0',$datepreperform->format('m'),$datepreperform->format('d'),1+(int)$datepreperform->format('Y'));
			}break;
		}
		$newAction = new ActionComm($db);
		$newAction->fetch($_GET['rowid']);
		$lengthOfTime = $newAction->datef-$newAction->datep;
		$newAction->datep = $newDate;
		$newAction->datef = $newDate+$lengthOfTime;
		$newAction->datepreperform = $newPreperform;
		$newAction->percentage = -1;
		$newAction->datec = time();
		$newAction->datem = null;
//		echo '<pre>';
//		var_dump(date('Y-m-d H:i:s',$newDate));
//		echo '</pre>';
//		$date1 = new DateTime();
//		$date1->setTimestamp($newAction->datep);
//		$date2 = new DateTime();
//		$date2->setTimestamp($newAction->datef);
//		var_dump($date1->format('Y-m-d'), $date2->format('Y-m-d'));
//		die();
		$newAction->add($user);
	}
	//Завантажую всі наступні ІД
	$newAction = new ActionComm($db);
	$chain_actions = array($_GET['rowid']);
	while($tmp_ID = $newAction->GetNextAction($chain_actions, 'id')){
		$added = false;
		foreach($tmp_ID as $item) {
			if(!in_array($item, $chain_actions)) {
				$added = true;
				$chain_actions[] = $item;
			}
		}
		if(!$added)
			break;
	}

    $sql = 'update llx_actioncomm set dateSetExec = Now(), percent=100 where id in ('.implode(',', $chain_actions).')';
//	die($sql);
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
	$sql = 'update llx_actioncomm, llx_actioncomm sub_action  set llx_actioncomm.dateSetExec = Now(), llx_actioncomm.percent=99, llx_actioncomm.new = 1 where sub_action.id='.$_GET['rowid'].
	" and sub_action.fk_parent = llx_actioncomm.id and llx_actioncomm.fk_parent = 0";
//	die($sql);
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
	$sql = 'update `llx_orders` set status = 4
			where rowid in (select fk_order_id from `llx_actioncomm` where `llx_actioncomm`.id='.$_GET['rowid'].')';
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
    exit();
}elseif($_GET['action']=='showFreeTime'){
	$Action = new ActionComm($db);
	$date = new DateTime($_REQUEST['date']);
	$out = '<a class="close" title="Закрити" onclick="closeForm($(this).parent());"></a><table class="setdate" style="background: #ffffff"><thead>
		<tr class="multiple_header_table">
			<th class="middle_size" colspan="2">Наявність вільного часу</br>'.$date->format('d.m.y').'</th>
		</tr>
		<tr class="multiple_header_table">
			<th class="small_size">Початок періоду</th>
			<th class="small_size">Кінець періоду</th>
		</tr>
		</thead><tbody>';
	$freetime = $Action->GetFreeTimePeriod($_REQUEST['date'],$_REQUEST['id_usr'],$_REQUEST['prioritet'],true);
	$num = 0;
 	foreach($freetime as $period){
		$begin = new DateTime($period[2].' '.$period[0]);
		$mk_begin = dol_mktime($begin->format('H'), $begin->format('i'), 0, $begin->format('m'), $begin->format('d'), $begin->format('Y'));
		$mk_end = $mk_begin+$period[1]*60;
		$class = fmod($num, 2) != 1 ? ("impair") : ("pair");
		$out.='<tr>
			<td class="small_size '.$class.'">'.$begin->format('H:i').'</td>
			<td class="small_size '.$class.'">'.date('H:i',$mk_end).'</td>
		</tr>';
		$num++;
	}
	$out.='</tbody></table>';
	echo $out;
	exit();
}elseif($_GET['action']=='get_freetime'){
//	echo '<pre>';
//	var_dump($_GET);
//	echo '</pre>';
//	die('test');
	global $user;
	$Action = new ActionComm($db);
	$date = new DateTime();
	$date->setTimestamp(time());
//	var_dump($date);
//	die();
	$freetime = $Action->GetFreeTime($_GET['date'], $_GET['id_usr'], $_GET['minute'], $_GET['priority'],$_GET['typePeriod']);

	echo $freetime;
	exit();
}



require_once DOL_DOCUMENT_ROOT.'/core/lib/agenda.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
require_once DOL_DOCUMENT_ROOT.'/comm/action/class/cactioncomm.class.php';
require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formactions.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
if (! empty($conf->projet->enabled))
{
	require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
	require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
}
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';


$langs->load("companies");
$langs->load("commercial");
$langs->load("other");
$langs->load("bills");
$langs->load("orders");
$langs->load("agenda");

$action=GETPOST('action','alpha');
$cancel=GETPOST('cancel','alpha');
//$backtopage=GETPOST('backtopage','alpha');
$backtopage=$_REQUEST['backtopage'];
//var_dump($backtopage);
//die();
if(substr($backtopage, 0, 1) == "'")
	$backtopage = substr($backtopage, 1, strlen($backtopage)-2);
if(substr($backtopage, 0, 1) != "/")
	$backtopage = '/'.$backtopage;
$contactid=GETPOST('contactid','int');
$origin=GETPOST('origin','alpha');
$originid=GETPOST('originid','int');
if ($cancel)
{
//	echo '<pre>';
//	var_dump($_REQUEST);
//	echo '</pre>';
//	die();
	if(empty($backtopage)){

		$sql = 'select fk_soc from `llx_actioncomm` where id='.$_REQUEST['id'];
		$res = $db->query($sql);
		if(!$res)
			dol_print_error($db);
		$obj = $db->fetch_object($res);
		$backtopage='/dolibarr/htdocs/responsibility/sale/action.php?socid='.$obj->fk_soc.'&idmenu=10425&mainmenu=area';
	}

	$listofuserid = dol_json_decode($_SESSION['assignedtouser'],1);
	foreach (array_keys($listofuserid) as $key) {
		if ($key != $user->id) {
			unset($listofuserid[$key]);
		}
	}
	$_SESSION['assignedtouser'] = dol_json_encode($listofuserid);
    $Location = "Location: ".str_replace("'",'', $backtopage);
    header($Location);
    exit;
}
$fulldayevent=GETPOST('fullday');
$datep=dol_mktime($fulldayevent?'00':GETPOST("aphour"), $fulldayevent?'00':GETPOST("apmin"), 0, GETPOST("apmonth"), GETPOST("apday"), GETPOST("apyear"));
//$datef=dol_mktime($fulldayevent?'23':GETPOST("p2hour"), $fulldayevent?'59':GETPOST("p2min"), $fulldayevent?'59':'0', GETPOST("p2month"), GETPOST("p2day"), GETPOST("p2year"));
$datef=dol_mktime($fulldayevent?'23':GETPOST("p2hour"), $fulldayevent?'59':GETPOST("p2min"), $fulldayevent?'59':'0', GETPOST("apmonth"), GETPOST("apday"), GETPOST("apyear"));

// Security check
$socid = GETPOST('socid','int');
$id = GETPOST('id','int');


if ($user->societe_id) $socid=$user->societe_id;
$result = restrictedArea($user, 'agenda', $id, 'actioncomm&societe', 'myactions|allactions', 'fk_soc', 'id');
if ($user->societe_id && $socid) $result = restrictedArea($user,'societe',$socid);

$error=GETPOST("error");
$donotclearsession=GETPOST('donotclearsession')?GETPOST('donotclearsession'):0;

$cactioncomm = new CActionComm($db);
$object = new ActionComm($db);
$contact = new Contact($db);
$extrafields = new ExtraFields($db);

if($id) {
	if($action == 'edit') {
		$sql = 'select datepreperform from `llx_actioncomm` where id = ' . $id;
		$res = $db->query($sql);
		if (!$res)
			dol_print_error($db);
		$obj = $db->fetch_object($res);

		$datepreperform = new DateTime($obj->datepreperform);
	}elseif($action == 'update'){
		$datepreperform = new DateTime(GETPOST('preperform'));
	}
}else{
	$datepreperform = new DateTime(GETPOST('preperform'));
}
//var_dump($action);
//die();
if($datepreperform)
	$dateprep = dol_mktime($datepreperform->format('H'), $datepreperform->format('i'), 0, $datepreperform->format('m'), $datepreperform->format('d'), $datepreperform->format('Y'));
//echo '<pre>';
//	var_dump($dateprep);
//	echo '</pre>';
//	die();
// fetch optionals attributes and labels
$extralabels=$extrafields->fetch_name_optionals_label($object->table_element);
//echo '<pre>';
//var_dump($_REQUEST);
//echo '</pre>';
//die();
// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array('actioncard','globalcard'));

/*
 * Actions
 */

// Remove user to assigned list
if (GETPOST('removedassigned') || GETPOST('removedassigned') == '0')
{
	$idtoremove=GETPOST('removedassigned');

	if (! empty($_SESSION['assignedtouser'])) $tmpassigneduserids=dol_json_decode($_SESSION['assignedtouser'],1);
	else $tmpassigneduserids=array();

	foreach ($tmpassigneduserids as $key => $val)
	{
		if ($val['id'] == $idtoremove || $val['id'] == -1) unset($tmpassigneduserids[$key]);
	}
	//var_dump($_POST['removedassigned']);exit;
	$_SESSION['assignedtouser']=dol_json_encode($tmpassigneduserids);
	$donotclearsession=1;
	if ($action == 'add') $action = 'create';
	if ($action == 'update') $action = 'edit';
}

// Add user to assigned list
if(GETPOST('assignedJSON')){
//	var_dump('{"6":{"id":"6","mandatory":0,"transparency":null},"43":{"id":"43","transparency":"on","mandatory":1}}</br>');

	$userlist=dol_json_decode($_SESSION['assignedtouser'], true);

	if(!isset($userlist[array_keys($userlist)[0]]["mandatory"])) {
		$_SESSION['assignedtouser'] = '{"' . $user->id . '":{"id":"' . $user->id . '","mandatory":0,"transparency":null}}';
	}
//	var_dump($_SESSION['assignedtouser']);
//	die();
	$author = substr($_SESSION['assignedtouser'], 0,strpos($_SESSION['assignedtouser'], 'null}')+strlen('null}'));
//	var_dump($author.','.$_GET['assignedJSON'].'}</br>');
//	die();
//	$assignedtouser=array();
	$_SESSION['assignedtouser']=$author.GETPOST('assignedJSON');
//	$assignedtouser=dol_json_decode($_SESSION['assignedtouser'], true);
//	$_SESSION['assignedtouser'] = $author.','.$_GET['assignedJSON'].'}';

//	var_dump($author).'</br>';
//	$_SESSION['assignedtouser'] = $_GET['assignedJSON'];
//	var_dump($_SESSION['assignedtouser']).'</br>';
//	var_du?mp($assignedtouser).'</br>';
//	die();
}

if (GETPOST('addassignedtouser') || GETPOST('updateassignedtouser') || GETPOST('duplicate_action'))
{
//	llxHeader();
//	var_dump(GETPOST('addassignedtouser'), GETPOST('assignedtouser'));
//	die();
	// Add a new user
	if (GETPOST('assignedtouser') > 0)
	{
		$assignedtouser=array();
		if (! empty($_SESSION['assignedtouser']))
		{
			$assignedtouser=dol_json_decode($_SESSION['assignedtouser'], true);
		}
		$assignedtouser[GETPOST('assignedtouser')]=array('id'=>GETPOST('assignedtouser'), 'transparency'=>GETPOST('transparency'),'mandatory'=>1);
		$_SESSION['assignedtouser']=dol_json_encode($assignedtouser);
//		var_dump($_SESSION['assignedtouser']);
//		die();
	}
	$donotclearsession=1;
	if ($action == 'add') $action = 'create';
	if ($action == 'update') $action = 'edit';
}

// Add event
if ($action == 'add')
{

	$error=0;

//    if (empty($backtopage))
//    {
//        if ($socid > 0) $backtopage = DOL_URL_ROOT.'/societe/agenda.php?socid='.$socid;
//        else $backtopage=DOL_URL_ROOT.'/comm/action/index.php';
//    }
//	echo '<pre>';
//	var_dump($_POST);
//	echo '</pre>';
//	die();
    if ($contactid)
	{
		$result=$contact->fetch($contactid);
	}

	if ($cancel)
	{
        $Location = "Location: ".str_replace("'",'', $backtopage);
        header($Location);
        exit;
	}

    $percentage=in_array(GETPOST('status'),array(-1,100))?GETPOST('status'):(in_array(GETPOST('complete'),array(-1,100))?GETPOST('complete'):GETPOST("percentage"));	// If status is -1 or 100, percentage is not defined and we must use status

    // Clean parameters
	$datep=dol_mktime($fulldayevent?'00':GETPOST("aphour"), $fulldayevent?'00':GETPOST("apmin"), 0, GETPOST("apmonth"), GETPOST("apday"), GETPOST("apyear"));
//	$datef=dol_mktime($fulldayevent?'23':GETPOST("p2hour"), $fulldayevent?'59':GETPOST("p2min"), $fulldayevent?'59':'0', GETPOST("p2month"), GETPOST("p2day"), GETPOST("p2year"));
	$datef=dol_mktime($fulldayevent?'23':GETPOST("p2hour"), $fulldayevent?'59':GETPOST("p2min"), $fulldayevent?'59':'0', GETPOST("apmonth"), GETPOST("apday"), GETPOST("apyear"));

	// Check parameters
	if (! $datef && $percentage == 100)
	{
		$error++; $donotclearsession=1;
		$action = 'create';
		setEventMessage($langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv("DateEnd")), 'errors');
	}

//	if (empty($conf->global->AGENDA_USE_EVENT_TYPE) && ! GETPOST('label'))
//	{
//		$error++; $donotclearsession=1;
//		$action = 'create';
//		setEventMessage($langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv("Title")), 'errors');
//	}

	// Initialisation objet cactioncomm
//	var_dump(! GETPOST('actioncode') > 0);
//	die();
	if (! GETPOST('actioncode') > 0)	// actioncode is id
	{
		$error++; $donotclearsession=1;
		$action = 'create';
		setEventMessage($langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv("Type")), 'errors');
	}
	else
	{
		$object->type_code = GETPOST('actioncode');
	}
//	echo '<pre>';
//	var_dump($error);
//	echo '</pre>';
	if (! $error)
	{

//        echo '<pre>';
//        var_dump($_POST);
//        echo '</pre>';
//        die();
		// Initialisation objet actioncomm
		$object->priority = GETPOST("priority")?GETPOST("priority"):0;
		$object->fulldayevent = (! empty($fulldayevent)?1:0);
		$object->location = GETPOST("location");
		$object->label = trim(GETPOST('label'));
		$object->fk_element = GETPOST("fk_element");
		$object->elementtype = GETPOST("elementtype");
		$object->period = GETPOST("selperiod");
		$object->parent_id= GETPOST("parent_id");
		$object->groupoftask= GETPOST("groupoftask");
		$object->typenotification= GETPOST("typenotification");
		$object->typeSetOfDate = GETPOST("typeSetOfDate");
//        die($object->parent_id);
		if (! GETPOST('label'))
		{
			if (GETPOST('actioncode') == 'AC_RDV' && $contact->getFullName($langs))
			{
				$object->label = $langs->transnoentitiesnoconv("TaskRDVWith",$contact->getFullName($langs));
			}
			else
			{
				if ($langs->trans("Action".$object->type_code) != "Action".$object->type_code)
				{
					$object->label = $langs->transnoentitiesnoconv("Action".$object->type_code)."\n";
				}
				else $object->label = $cactioncomm->libelle;
			}
		}

		$object->fk_project = isset($_POST["projectid"])?$_POST["projectid"]:0;
		$object->datep = $datep;
		$object->datef = $datef;
		$object->datepreperform = $dateprep;
		if(isset($_REQUEST['typeaction']) && $_REQUEST['typeaction'] == 'subaction' )
			$object->entity = 0;
//		var_dump($object->datepreperform);
//		die();
		$object->percentage = $percentage;
		$object->duree=((float) (GETPOST('dureehour') * 60) + (float) GETPOST('dureemin')) * 60;

		$listofuserid=array();

		if (! empty($_SESSION['assignedtouser'])) {
			$listofuserid = dol_json_decode($_SESSION['assignedtouser']);
		}
		$i=0;
		foreach($listofuserid as $key => $value)
		{
			if ($i == 0)	// First entry
			{
				if ($value['id'] > 0) $object->userownerid=$value['id'];
				$object->transparency = (GETPOST("transparency")=='on'?1:0);
			}
			if ($value['id'] > 0)
				$object->userassigned[$value['id']]=array('id'=>$value['id'], 'transparency'=>(GETPOST("transparency")=='on'?1:0));
			$i++;
		}

	}
//	die();

	if (! $error && ! empty($conf->global->AGENDA_ENABLE_DONEBY))
	{
		if (GETPOST("doneby") > 0) $object->userdoneid = GETPOST("doneby","int");
	}

	$object->note = trim($_POST["note"]);
	$object->confirmdoc = trim($_POST["confirmdoc"]);
	if(isset($_POST["typeaction"]) && $_POST["typeaction"] == 'subaction')
		$object->entity = 0;
	if (isset($_POST["contactid"])) $object->contact = $contact;

	if (GETPOST('socid','int') > 0)
	{
		$object->socid=GETPOST('socid','int');
		$object->fetch_thirdparty();
        $object->contactid = GETPOST("contactid", 'int');
//        if(count($_POST)) {
//            echo '<pre>';
//            var_dump(GETPOST("contactid", 'int'));
//            var_dump($_POST);
//            echo '</pre>';
//            die('test');
//        }
		$object->societe = $object->thirdparty;	// For backward compatibility
	}

	// Special for module webcal and phenix
	// TODO external modules
	if (! empty($conf->webcalendar->enabled) && GETPOST('add_webcal') == 'on') $object->use_webcal=1;
	if (! empty($conf->phenix->enabled) && GETPOST('add_phenix') == 'on') $object->use_phenix=1;

	// Check parameters
	if (empty($object->userownerid) && empty($_SESSION['assignedtouser']))
	{
		$error++; $donotclearsession=1;
		$action = 'create';
		setEventMessage($langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv("ActionsOwnedBy")), 'errors');
	}
	if ($object->type_code == 'AC_RDV' && ($datep == '' || ($datef == '' && empty($fulldayevent))))
	{
		$error++; $donotclearsession=1;
		$action = 'create';
		setEventMessage($langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv("DateEnd")), 'errors');
	}

//	if (! GETPOST('apyear') && ! GETPOST('adyear'))
//	{
//		$error++; $donotclearsession=1;
//		$action = 'create';
//		setEventMessage($langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv("Date")), 'errors');
//	}

	// Fill array 'array_options' with data from add form
	$ret = $extrafields->setOptionalsFromPost($extralabels,$object);
	if ($ret < 0) $error++;

	if (! $error)
	{

		$db->begin();
//        echo '<pre>';
//        var_dump($object);
//        echo '</pre>';
//        die();
		// On cree l'action
		if(isset($_REQUEST["dateNextAction"])&&!empty($_REQUEST["dateNextAction"])) {
			$dateconfirm = new DateTime();
			$object->dateconfirm = $dateconfirm->format('Y-m-d H:i:s');
			$object->percentage = 0;
		}
		$idaction=$object->add($user);
		if(isset($_REQUEST["dateNextAction"])&&!empty($_REQUEST["dateNextAction"])&&in_array($object->type_code, array('AC_GLOBAL','AC_CURRENT'))){
			$subaction = new ActionComm($db);
			$subaction->fetch($idaction);
			$subaction->id=null;
			$subaction->parent_id = $idaction;
			$subaction->percentage = 0;
			$subaction->datep=dol_mktime($_POST["dateNextActionhour"], $_POST["dateNextActionmin"], 0, $_POST["dateNextActionmonth"], $_POST["dateNextActionday"], $_POST["dateNextActionyear"]);
			$subaction->datef=$subaction->datep+$_POST["exec_time_dateNextAction"]*60;
			$subaction->entity = 0;
			$subaction->note = $_POST["work_before_the_next_action"];
			$dateconfirm = new DateTime();
			$subaction->dateconfirm = $dateconfirm->format('Y-m-d H:i:s');
			$subaction->add($user);
		}
//		var_dump($object->errors);
//		die();
		if ($idaction > 0)
		{
			if (! $object->error)
			{
				unset($_SESSION['assignedtouser']);
				unset($_POST['assignedJSON']);
				unset($_REQUEST['assignedJSON']);
				$moreparam='';
				if ($user->id != $object->ownerid) $moreparam="usertodo=-1";	// We force to remove filter so created record is visible when going back to per user view.
//				echo '<pre>';
//				var_dump(empty($backtopage));
//				echo '</pre>';
//				die('stop');
				$db->commit();
				if (!empty($backtopage))
				{
					dol_syslog("Back to ".$backtopage.($moreparam?(preg_match('/\?/',$backtopage)?'&'.$moreparam:'?'.$moreparam):''));
//					header("Location: ".$backtopage.($moreparam?(preg_match('/\?/',$backtopage)?'&'.$moreparam:'?'.$moreparam):''));
                    $Location = "Location: ".str_replace("'",'', $backtopage);
                    header($Location);
				}
				elseif(!empty($object->socid)){
					if($object->type_code == 'AC_GLOBAL')
						$backtopage = '/dolibarr/htdocs/comm/action/chain_actions.php?action_id='.$idaction.'&mainmenu=global_task';
					elseif($object->type_code == 'AC_CURRENT')
						$backtopage = '/dolibarr/htdocs/comm/action/chain_actions.php?action_id='.$idaction.'&mainmenu=current_task';
					else
						$backtopage = '/dolibarr/htdocs/responsibility/'.$user->respon_alias.'/action.php?socid='.$object->socid.'&idmenu=10425&mainmenu=area';
//					$backtopage = '/dolibarr/htdocs/responsibility/'.$user->respon_alias.'/action.php?socid='.$object->socid.'&idmenu=10425&mainmenu=area';
//					var_dump($backtopage);
//					die();
					$Location = "Location: ".str_replace("'",'', $backtopage);
                    header($Location);
				}
//				elseif($idaction)
//				{
//					header("Location: ".DOL_URL_ROOT.'/comm/action/card.php?id='.$idaction.($moreparam?'&'.$moreparam:''));
//				}
//				else
//				{
//					header("Location: ".DOL_URL_ROOT.'/comm/action/index.php'.($moreparam?'?'.$moreparam:''));
//				}
				exit;
			}
			else
			{
				// If error
				$db->rollback();
				$langs->load("errors");
				$error=$langs->trans($object->error);
				setEventMessage($error,'errors');
				$action = 'create'; $donotclearsession=1;
			}
		}
		else
		{
			$db->rollback();
			setEventMessages($object->error, $object->errors, 'errors');
			$action = 'create'; $donotclearsession=1;
		}
	}
}

/*
 * Action update event
 */

if ($action == 'update')
{

	if (empty($cancel))
	{
//		echo '<pre>';
//		var_dump($_REQUEST);
//		echo '</pre>';
//		die();
        $fulldayevent=GETPOST('fullday');
        $aphour=GETPOST('aphour');
        $apmin=GETPOST('apmin');
        $p2hour=GETPOST('p2hour');
        $p2min=GETPOST('p2min');
		$percentage=in_array(GETPOST('status'),array(-1,100))?GETPOST('status'):(in_array(GETPOST('complete'),array(-1,100))?GETPOST('complete'):GETPOST("percentage"));	// If status is -1 or 100, percentage is not defined and we must use status

	    // Clean parameters
		if ($aphour == -1) $aphour='0';
		if ($apmin == -1) $apmin='0';
		if ($p2hour == -1) $p2hour='0';
		if ($p2min == -1) $p2min='0';

		$object->fetch($id);
		$object->fetch_userassigned();

		$datep=dol_mktime($fulldayevent?'00':$aphour, $fulldayevent?'00':$apmin, 0, $_POST["apmonth"], $_POST["apday"], $_POST["apyear"]);
		$datef=dol_mktime($fulldayevent?'23':$p2hour, $fulldayevent?'59':$p2min, $fulldayevent?'59':'0', $_POST["apmonth"], $_POST["apday"], $_POST["apyear"]);
//		$datef=dol_mktime($fulldayevent?'23':$p2hour, $fulldayevent?'59':$p2min, $fulldayevent?'59':'0', $_POST["p2month"], $_POST["p2day"], $_POST["p2year"]);

		$object->fk_action   = dol_getIdFromCode($db, GETPOST("actioncode"), 'c_actioncomm');
		$object->label       = GETPOST("label");
		$object->datep       = $datep;
		$object->datef       = $datef;
		$object->datepreperform = $datepreperform;
		$object->percentage  = $percentage;
		$object->priority    = GETPOST("priority");
        $object->fulldayevent= GETPOST("fullday")?1:0;
		$object->location    = GETPOST('location');
		$object->socid       = GETPOST("socid");
        $object->groupoftask = GETPOST('groupoftask');
		$object->typenotification= GETPOST("typenotification");
		$object->period 	 = GETPOST("selperiod");
		$object->typeSetOfDate = GETPOST("typeSetOfDate");


        $object->contactid   = GETPOST("contactid",'int');

		//$object->societe->id = $_POST["socid"];			// deprecated
		//$object->contact->id = $_POST["contactid"];		// deprecated
		$object->fk_project  = GETPOST("projectid",'int');
		$object->note        = GETPOST("note");
		$object->pnote       = GETPOST("note");
		$object->fk_element	 = GETPOST("fk_element");
		$object->elementtype = GETPOST("elementtype");

		if (! $datef && $percentage == 100)
		{
			$error++; $donotclearsession=1;
			setEventMessages($langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv("DateEnd")),$object->errors,'errors');
			$action = 'edit';
		}

		$transparency=(GETPOST("transparency")=='on'?1:0);

		// Users
		$listofuserid=array();

		if (! empty($_SESSION['assignedtouser']))	// Now concat assigned users
		{
			// Restore array with key with same value than param 'id'
			$tmplist1=dol_json_decode($_SESSION['assignedtouser'], true); $tmplist2=array();
			foreach($tmplist1 as $key => $val)
			{
				if ($val['id'] > 0 && $val['id'] != $assignedtouser) $listofuserid[$val['id']]=$val;
			}
		}
		else {
			$assignedtouser=(! empty($object->userownerid) && $object->userownerid > 0 ? $object->userownerid : 0);
			if ($assignedtouser) $listofuserid[$assignedtouser]=array('id'=>$assignedtouser, 'mandatory'=>0, 'transparency'=>($user->id == $assignedtouser ? $transparency : ''));	// Owner first
		}

		$object->userassigned=array();	$object->userownerid=0; // Clear old content
		$i=0;
		foreach($listofuserid as $key => $val)
		{
			if ($i == 0) $object->userownerid = $val['id'];
			$object->userassigned[$val['id']]=array('id'=>$val['id'], 'mandatory'=>0, 'transparency'=>($user->id == $val['id'] ? $transparency : ''));
			$i++;
		}

		if (! empty($conf->global->AGENDA_ENABLE_DONEBY))
		{
			if (GETPOST("doneby")) $object->userdoneid=GETPOST("doneby","int");
		}

		// Check parameters
		if (! GETPOST('actioncode') > 0)
		{
			$error++; $donotclearsession=1;
			$action = 'edit';
			setEventMessage($langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv("Type")), 'errors');
		}
		else
		{
			$result=$cactioncomm->fetch(GETPOST('actioncode'));
		}
		if (empty($object->userownerid))
		{
			$error++; $donotclearsession=1;
			$action = 'edit';
			setEventMessage($langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv("ActionsOwnedBy")), 'errors');
		}

		// Fill array 'array_options' with data from add form
		$ret = $extrafields->setOptionalsFromPost($extralabels,$object);
		if ($ret < 0) $error++;

		if (! $error)
		{
			$db->begin();

			$result=$object->update($user);
//		echo '<pre>';
//		var_dump($result);
//		echo '</pre>';
//		die();
			if ($result > 0)
			{
				unset($_SESSION['assignedtouser']);

				$db->commit();
			}
			else
			{
				setEventMessages($object->error,$object->errors,'errors');
				$db->rollback();
			}
		}
	}

	if (! $error)
	{
//		echo '<pre>';
//		var_dump($object);
//		echo '</pre>';
//		die();

        if (! empty($backtopage)) {
			if (substr($backtopage, 0, 1) == "'" && substr($backtopage, mb_strlen($backtopage) - 1, 1) == "'") {
				$backtopage = substr($backtopage, 1, mb_strlen($backtopage) - 2);
			}
			unset($_SESSION['assignedtouser']);
			header("Location: " . $backtopage);
			exit;
		}elseif(!empty($object->socid)){
					if($object->type_code == 'AC_GLOBAL')
						$backtopage = '/dolibarr/htdocs/comm/action/chain_actions.php?action_id='.$object->id.'&mainmenu=global_task';
					elseif($object->type_code == 'AC_CURRENT')
						$backtopage = '/dolibarr/htdocs/comm/action/chain_actions.php?action_id='.$object->id.'&mainmenu=current_task';
					else
						$backtopage = '/dolibarr/htdocs/responsibility/'.$user->respon_alias.'/action.php?socid='.$object->socid.'&idmenu=10425&mainmenu=area';
//					$backtopage = '/dolibarr/htdocs/responsibility/'.$user->respon_alias.'/action.php?socid='.$object->socid.'&idmenu=10425&mainmenu=area';
//					var_dump($idaction, $object->type_code, $backtopage);
//					die('stop');
					$Location = "Location: ".str_replace("'",'', $backtopage);
                    header($Location);
		}
	}
}

/*
 * delete event
 */
if ($action == 'confirm_delete' && GETPOST("confirm") == 'yes')
{
	$object->fetch($id);

	if ($user->rights->agenda->myactions->delete
		|| $user->rights->agenda->allactions->delete)
	{
		$result=$object->delete();

		if ($result >= 0)
		{
			header("Location: index.php");
			exit;
		}
		else
		{
			setEventMessages($object->error,$object->errors,'errors');
		}
	}
}

/*
 * Action move update, used when user move an event in calendar by drag'n drop
 */
if ($action == 'mupdate')
{
    $object->fetch($id);
    $object->fetch_userassigned();

    $shour = dol_print_date($object->datep,"%H");
    $smin = dol_print_date($object->datep, "%M");

    $newdate=GETPOST('newdate','alpha');
    if (empty($newdate) || strpos($newdate,'dayevent_') != 0 )
    {
       header("Location: ".$backtopage);
        exit;
    }

    $datep=dol_mktime($shour, $smin, 0, substr($newdate,13,2), substr($newdate,15,2), substr($newdate,9,4));
    if ($datep!=$object->datep)
    {
        if (!empty($object->datef))
        {
            $object->datef+=$datep-$object->datep;
        }
        $object->datep=$datep;
        $result=$object->update($user);
        if ($result < 0)
        {
            setEventMessage($object->error,'errors');
            setEventMessage($object->errors,'errors');
        }
    }
    if (! empty($backtopage))
    {
        header("Location: ".$backtopage);
        exit;
    }
    else
    {
        $action='';
    }

}


/*
 * View
 */
//echo '<pre>';
//var_dump($_REQUEST);
//echo '</pre>';
//die();
$help_url='EN:Module_Agenda_En|FR:Module_Agenda|ES:M&omodulodulo_Agenda';
if ($action == 'create') {
	if(!isset($_REQUEST["duplicate_action"]))
		llxHeader('', $langs->trans("AddAction"), $help_url);
	else
		llxHeader('', $langs->trans("DuplicateAction"), $help_url);

}
elseif($action == 'edit') {
	if(!isset($_REQUEST["duplicate_action"]))
		llxHeader('', $langs->trans("EditAction"), $help_url);
	else
		llxHeader('', $langs->trans("DuplicateAction"), $help_url);
}

$form = new Form($db);
$formfile = new FormFile($db);
$formactions = new FormActions($db);
global $user;
print '<script type="text/javascript" src="/dolibarr/htdocs/comm/action/js/action.js'.($ext?'?'.$ext:'').'"></script>'."\n";
print '<script type="text/javascript"> var id_usr = '.$user->id.'</script>'."\n";

if ($action == 'create' && !isset($_REQUEST["duplicate_action"]))
{
	$contact = new Contact($db);
    print '<div class="tabBar">';
	print '<form id="addAssigned" action="'.$_SERVER['PHP_SELF'].'" method="POST">';
	print '<input type="hidden" id = "assignedJSON" name="assignedJSON" value="">';
	print '</form>';

	print '<div id="addassignpanel" style="position: relative; z-index: 0; width: 30px">
		<button style="width: 25px;height: 29px;" title="Додати користувачів зі списку" onclick="ShowaddAssignedUsersForm();"><img style="margin-left: -3px" src="../../../htdocs/theme/eldy/img/Add.png"></button>
	</div>';
	if (GETPOST("contactid"))
	{
		$result=$contact->fetch(GETPOST("contactid"));
		if ($result < 0) dol_print_error($db,$contact->error);
	}

	dol_set_focus("#label");

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
	            			$("#p2").removeAttr("disabled");
	            		} else {
	            			$(".fulldaystarthour").attr("disabled","disabled").val("00");
	            			$(".fulldaystartmin").attr("disabled","disabled").val("00");
	            			$(".fulldayendhour").attr("disabled","disabled").val("23");
	            			$(".fulldayendmin").attr("disabled","disabled").val("59");
	            			$("#p2").removeAttr("disabled");
	            		}
	            	}
                    setdatefields();
                    $("#fullday").change(function() {
                        setdatefields();
                    });
                    $("#selectcomplete").change(function() {
                        if ($("#selectcomplete").val() == 100)
                        {
                            if ($("#doneby").val() <= 0) $("#doneby").val(\''.$user->id.'\');
                        }
                        if ($("#selectcomplete").val() == 0)
                        {
                            $("#doneby").val(-1);
                        }
                   });
                   $("select#actioncode").change(function() {
                        if ($("select#actioncode").val() == "AC_RDV") $("#dateend").addClass("fieldrequired");
                        else $("#dateend").removeClass("fieldrequired");
                   });
               })';
        print '</script>'."\n";
    }

	print '<form id="formaction" name="formaction" action="'.$_SERVER['PHP_SELF'].'" method="POST">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="add">';
	print '<input type="hidden" name="socid" value="'.$_REQUEST['socid'].'">';
	print '<input type="hidden" id="showform1" value="0">';
	print '<input type="hidden" name="typeSetOfDate" id="type" value="">';
	print '<input type="hidden" name="error" id="error" value="'.$_REQUEST['error'].'">';
	print '<input type="hidden" id="mainmenu" name="mainmenu" value="'.$_REQUEST["mainmenu"].'">';
	print '<input type="hidden" id="parent_id" name="parent_id" value="'.$_REQUEST["parent_id"].'">';
	print '<input type="hidden" name="donotclearsession" value="1">';
	print '<input type="hidden" id = "backtopage" name="backtopage" value="'.($backtopage != '1' ? $backtopage : $_SERVER["HTTP_REFERER"]).'">';

	if (empty($conf->global->AGENDA_USE_EVENT_TYPE)) print '<input type="hidden" id="actioncode" name="actioncode" value="'.dol_getIdFromCode($db, $_REQUEST["actioncode"], 'c_actioncomm').'">';

	if (GETPOST("actioncode") == 'AC_RDV') print_fiche_titre($langs->trans("AddActionRendezVous"));
	else {
		if(!isset($_REQUEST["duplicate_action"]))
			print_fiche_titre($langs->trans("AddAnAction"));
		else
			print_fiche_titre($langs->trans("DuplicateAction"));
	}

	print '<table class="border" width="100%">';
//    // Type of event
//	if (! empty($conf->global->AGENDA_USE_EVENT_TYPE))
//	{
    print '<tr><td width="30%"><span class="fieldrequired">'.$langs->trans("ActionType").'</span></b></td><td>';
    $formactions->select_type_actions(GETPOST("actioncode")?GETPOST("actioncode"):$object->type_code, "actioncode","systemauto");
    print '</td></tr>';
//	}
	// Assigned to
	print '<tr><td class="nowrap">'.$langs->trans("ActionAffectedTo").'</td><td style="vertical-align:top">';
	$listofuserid=array();
//	var_dump(empty($donotclearsession));
//	die();
	if (empty($donotclearsession))
	{
		$assignedtouser=GETPOST("assignedtouser")?GETPOST("assignedtouser"):(! empty($object->userownerid) && $object->userownerid > 0 ? $object->userownerid : $user->id);
		if ($assignedtouser) $listofuserid[$assignedtouser]=array('id'=>$assignedtouser,'mandatory'=>0,'transparency'=>$object->transparency);	// Owner first
		$_SESSION['assignedtouser']=dol_json_encode($listofuserid);
	}
	elseif (!empty($_SESSION['assignedtouser']))
	{
		$listofuserid=dol_json_decode($_SESSION['assignedtouser'], true);
	}


	if(isset($_REQUEST["duplicate_action"])) {//if duplicate action, change author action
		$listofuserid[$user->id] = $listofuserid[array_keys($listofuserid)[0]];
		$listofuserid[$user->id]['id']=$user->id;
		foreach (array_keys($listofuserid) as $key) {
			if ($key != $user->id) {
				unset($listofuserid[$key]);
			}
		}
		$_SESSION['assignedtouser'] = dol_json_encode($listofuserid);

		$userlist=dol_json_decode($_SESSION['assignedtouser'], true);

		if(!isset($userlist[array_keys($userlist)[0]]["mandatory"])) {
			$_SESSION['assignedtouser'] = '{"' . $user->id . '":{"id":"' . $user->id . '","mandatory":0,"transparency":null}}';
		}
	}

	print $form->select_dolusers_forevent(($action=='create'?'add':'update'), 'assignedtouser', 1, '', 0, '', '', 0, 0, 0, 'AND u.statut != 0', 0, 1, 1);

//	if (in_array($user->id,array_keys($listofuserid))) print $langs->trans("MyAvailability").': <input id="transparency" type="checkbox" name="transparency"'.(((! isset($_GET['transparency']) && ! isset($_POST['transparency'])) || GETPOST('transparency'))?' checked="checked"':'').'> '.$langs->trans("Busy");
	print '</td></tr>';
    // Full day
    print '<tr style="display: none"><td>'.$langs->trans("EventOnFullDay").'</td><td><input type="checkbox" id="fullday" name="fullday" '.(GETPOST('fullday')?' checked="checked"':'').'></td></tr>';
    $period='';
	//GroupOfTask
	print '<tr><td width="10%">'.$langs->trans("GroupOfTask").'</td>';
	print '<td>';
	$percent=-1;
	$respon = array();

	if(count($listofuserid) == 1) {
		$respon[] = $user->respon_id;
		$formactions->select_groupoftask('groupoftask', $respon, GETPOST('groupoftask'));
	}else{
		$assigneduser = new User($db);
		foreach($listofuserid as $id_usr){
			if($id_usr['id'] != $user->id){
				$assigneduser->fetch($id_usr['id']);
				if(!in_array($assigneduser->respon_id,$respon))
					$respon[]=$assigneduser->respon_id;
			}
		}
//		var_dump($respon);
//		die();
		$formactions->select_groupoftask('groupoftask', $respon, GETPOST('groupoftask'));
	}

	print '</td></tr>';
    // ActionDescription
	if(!isset($_REQUEST['typeaction']) || $_REQUEST['typeaction'] != 'subaction' )
    	print '<tr><td valign="top">'.$langs->trans("Description").'</td><td>';
	else
    	print '<tr><td valign="top">'.$langs->trans("work_before_the_next_action").'</td><td>';
    require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
    $doleditor=new DolEditor('note',(GETPOST('note')?GETPOST('note'):$object->note),'',180,'dolibarr_notes','In',true,true,$conf->fckeditor->enabled,ROWS_6,90);
    $doleditor->Create();
    print '</td></tr>';
    // Note
    print '<tr><td valign="top">'.$langs->trans("Note").': що зробить, кінцева мета, підтвердження</td><td>';
    print $form->select_confirmdoc();
    print '</td></tr>';
	//Попередньо виконати до
	if(!isset($_REQUEST['typeaction']) || $_REQUEST['typeaction'] != 'subaction' ) {
		print '<tr><td class="nowrap">Попередньо виконати до</td><td colspan="3">';
		$form->select_date($datep ? $datep : $object->datep, 'preperform', 0, 0, 0, "action", 1, 0, 0, 0, 'fulldaystart');
		print '</td></tr>';
	}
	// Date start
	if(empty($_REQUEST["parent_id"])) {
		$datep = ($datep ? $datep : $object->datep);
		$datef = ($datef ? $datef : $object->datef);
	}else{
		$sql = "select datep, datep2, datepreperform, period, `code` from llx_actioncomm where id = ".$_REQUEST["parent_id"];
		$res = $db->query($sql);
		if(!$res)
			dol_print_error($db);
		$obj = $db->fetch_object($res);
//		echo '<pre>';
//		var_dump($_POST);
//		echo '</pre>';
//		die();

        $datep = new DateTime($obj->datep);
        $datef = new DateTime($obj->datep2);
		$datepreperform = new DateTime($obj->datepreperform);
		$newAction = new ActionComm($db);
		$period = '';
		if(!isset($_POST['typeaction'])||$_POST['typeaction']!='subaction')
        	$period = $obj->period;
//var_dump($period);
//		die();
		switch($period){
			case 'EveryDay':{
                $datep = new DateTime(($datep->format('d')+1).'.'.$datep->format('m').'.'.$datep->format('Y'). ' '.$datep->format('h').':'.$datep->format('i').':'.$datep->format('s'));
                $datef = new DateTime(($datef->format('d')+1).'.'.$datef->format('m').'.'.$datef->format('Y'). ' '.$datef->format('h').':'.$datef->format('i').':'.$datef->format('s'));
				$datepreperform = new DateTime(($datepreperform->format('d')+1).'.'.$datepreperform->format('m').'.'.$datepreperform->format('Y'). ' '.$datepreperform->format('h').':'.$datepreperform->format('i').':'.$datepreperform->format('s'));
			}
			case 'EveryWeek':{
                $datep = new DateTime(($datep->format('d')+7).'.'.$datep->format('m').'.'.$datep->format('Y'). ' '.$datep->format('h').':'.$datep->format('i').':'.$datep->format('s'));
                $datef = new DateTime(($datef->format('d')+7).'.'.$datef->format('m').'.'.$datef->format('Y'). ' '.$datef->format('h').':'.$datef->format('i').':'.$datef->format('s'));
				$datepreperform = new DateTime(($datepreperform->format('d')+7).'.'.$datepreperform->format('m').'.'.$datepreperform->format('Y'). ' '.$datepreperform->format('h').':'.$datepreperform->format('i').':'.$datepreperform->format('s'));
			}break;
			case 'EveryMonth':{
                $datep = new DateTime($datep->format('d').'.'.($datep->format('m')+1).'.'.$datep->format('Y'). ' '.$datep->format('h').':'.$datep->format('i').':'.$datep->format('s'));
                $datef = new DateTime($datef->format('d').'.'.($datef->format('m')+1).'.'.$datef->format('Y'). ' '.$datef->format('h').':'.$datef->format('i').':'.$datef->format('s'));
				$datepreperform = new DateTime($datepreperform->format('d').'.'.($datepreperform->format('m')+1).'.'.$datepreperform->format('Y'). ' '.$datepreperform->format('h').':'.$datepreperform->format('i').':'.$datepreperform->format('s'));
			}break;
			case 'Quarterly':{
                $datep = new DateTime($datep->format('d').'.'.($datep->format('m')+3).'.'.$datep->format('Y'). ' '.$datep->format('h').':'.$datep->format('i').':'.$datep->format('s'));
                $datef = new DateTime($datef->format('d').'.'.($datef->format('m')+3).'.'.$datef->format('Y'). ' '.$datef->format('h').':'.$datef->format('i').':'.$datef->format('s'));
				$datepreperform = new DateTime($datepreperform->format('d').'.'.($datepreperform->format('m')+3).'.'.$datepreperform->format('Y'). ' '.$datepreperform->format('h').':'.$datepreperform->format('i').':'.$datepreperform->format('s'));
			}break;
			case 'Annually':{
                $datep = new DateTime($datep->format('d').'.'.$datep->format('m').'.'.($datep->format('Y')+1). ' '.$datep->format('h').':'.$datep->format('i').':'.$datep->format('s'));
                $datef = new DateTime($datef->format('d').'.'.$datef->format('m').'.'.($datef->format('Y')+1). ' '.$datef->format('h').':'.$datef->format('i').':'.$datef->format('s'));
				$datepreperform = new DateTime($datepreperform->format('d').'.'.$datepreperform->format('m').'.'.($datepreperform->format('Y')+1). ' '.$datepreperform->format('h').':'.$datepreperform->format('i').':'.$datepreperform->format('s'));

			}break;
			default:{
//				$minutes = (mktime($datef->format('H'),$datef->format('i'),$datef->format('s'),$datef->format('m'),$datef->format('d'),$datef->format('Y'))-
//						mktime($datep->format('H'),$datep->format('i'),$datep->format('s'),$datep->format('m'),$datep->format('d'),$datep->format('Y')))/60;
				$minutes = $newAction->GetExecTime($obj->code);
//				var_dump($datef->format('Y-m-d H:i:s'),$datep->format('Y-m-d H:i:s'));
				$start = $newAction->GetFreeTime($datep->format('Y-m-d H:i:s'),$user->id, $minutes, 0);

				$datep = new DateTime($start);
				$sec = mktime($datep->format('H'),$datep->format('i'),$datep->format('s'),$datep->format('m'),$datep->format('d'),$datep->format('Y'));
				$sec += $minutes*60;
				$datef = new DateTime();
				$datef->setTimestamp($sec);
//				var_dump($datef);
//				die();
			}break;
		}
//        var_dump($datep);

        $datep = mktime($datep->format('H'),$datep->format('i'),$datep->format('s'),$datep->format('m'),$datep->format('d'),$datep->format('Y'));
        $datef = mktime($datef->format('H'),$datef->format('i'),$datef->format('s'),$datef->format('m'),$datef->format('d'),$datef->format('Y'));
        $datepreperform = mktime($datepreperform->format('H'),$datepreperform->format('i'),$datepreperform->format('s'),$datepreperform->format('m'),$datepreperform->format('d'),$datepreperform->format('Y'));

	}
//var_dump($datep);
//	die();
	if (GETPOST('datep','int',1)) $datep=dol_stringtotime(GETPOST('datep','int',1),0);
	print '<tr>';
	if(!isset($_REQUEST['typeaction']) || $_REQUEST['typeaction'] != 'subaction' )
		print '<td width="30%" class="nowrap"><span class="fieldrequired">'.$langs->trans("DateActionStart").'</span></td><td>';
	else
		print '<td width="30%" class="nowrap"><span class="fieldrequired">'.$langs->trans("DateAction").'</span></td><td>';
	if (GETPOST("afaire") == 1) $form->select_date($datep,'ap',1,1,0,"action",1,1,0,0,'fulldayend');
	else if (GETPOST("afaire") == 2) $form->select_date($datep,'ap',1,1,1,"action",1,1,0,0,'fulldayend');
	else $form->select_date($datep,'ap',1,1,1,"action",1,1,0,0,'fulldaystart');
	print '<span id="ShowFreeTime" onclick="ShowFreeTime('."'ap'".');" title="Переглянути наявність вільного часу" style="vertical-align: middle"><img src="/dolibarr/htdocs/theme/eldy/img/calendar.png"></span>  ';
	print '<span style="font-size: 12px">Необхідно часу  </span><input type="text" class="param exec_time" size="2" value="1" id = "exec_time_ap" name="exec_time_ap"><span style="font-size: 12px"> хвилин.</span></td></tr>';
//	echo '<pre>';
//	var_dump($_REQUEST);
//	echo '</pre>';
//	die();
	if(count($assignedtouser) <= 1 && $action == 'create' && (!isset($_REQUEST["typeaction"])||empty($_REQUEST["typeaction"]))) {//Якщо відбувається створення нової дії тільки для активного користувача
		print '<script> var user_id='.$user->id.'</script>';
		print '<tr class="global_current">';
		print '<td width="30%" class="nowrap" >' . $langs->trans("DateNextActionStart") . '</td><td>';
		$form->select_date($datep, 'dateNextAction', 1, 1, 1, "action", 1, 1, 0, 0, 'fulldayend', 'dtChangeNextDateAction');
		print '<span id="ShowFreeTime" onclick="ShowFreeTime('."'dateNextAction'".');" title="Переглянути наявність вільного часу" style="vertical-align: middle"><img src="/dolibarr/htdocs/theme/eldy/img/calendar.png"></span>  ';
		print '<span style="font-size: 12px">Необхідно часу  </span><input type="text" class="param exec_time" size="2" id = "exec_time_dateNextAction" name="exec_time_dateNextAction"><span style="font-size: 12px"> хвилин.</span></td></tr>';
		print '<tr class="global_current">';
		print '<td width="30%" class="nowrap" >Робота до/на наступних дій</td><td>';
		print '<textarea id="work_before_the_next_action" name="work_before_the_next_action" class="flat" cols="90" rows="6"></textarea></td></tr>';
		print "<script>
			$('select').change(function(e){
				if($.inArray(e.target.id, ['aphour','apmin','dateNextActionhour','dateNextActionmin'])>=0){
					var prefix;
					if(e.target.id.substr(e.target.id.length - 'hour'.length) == 'hour'){
						prefix = e.target.id.substr(0, e.target.id.length - 'hour'.length);
					}
					if(e.target.id.substr(e.target.id.length - 'min'.length) == 'min'){
						prefix = e.target.id.substr(0, e.target.id.length - 'min'.length);
					}
					var param = {
						action:'validateDataAction',
						date:($('#'+prefix+'year').val().length == 0?'':$('#'+prefix+'year').val()+'-'+$('#'+prefix+'month').val()+'-'+
							$('#'+prefix+'day').val()+' '+$('#'+prefix+'hour').val()+':'+$('#'+prefix+'min').val()),
						minutes:$('#exec_time_'+prefix).val(),
						id_usr: ".$user->id.",
						prioritet:$('#priority').val()
					}
					$.ajax({
						cache:false,
						data:param,
						success:function(result){
							$('#type').val('w');
							if(result == 0){
								$('#'+e.target.id).addClass('fielderrorSelBorder');
								$('#'+e.target.id).removeClass('validfieldSelBorder');


							}else{
								$('#'+e.target.id).addClass('validfieldSelBorder');
								$('#'+e.target.id).removeClass('fielderrorSelBorder');
							}

							if(!$('#'+prefix+'hour').hasClass('fielderrorSelBorder')&&
								!$('#'+prefix+'min').hasClass('fielderrorSelBorder'))
								$('#error').val(0);
							else
								$('#error').val(1);
							console.log(result);
						}
					})
				}
			});
		</script>";

	}
	if(!isset($_REQUEST['typeaction']) || $_REQUEST['typeaction'] != 'subaction' ){}
//		print '<style> #aphour, #apmin, #apButtonNow {display: none} </style>';
	else
		print '<input id="typeaction" type="hidden" value="subaction" name="typeaction">';
	// Date end
    if (GETPOST('datef','int',1)) $datef=dol_stringtotime(GETPOST('datef','int',1),0);
	if (empty($datef) && ! empty($datep) && ! empty($conf->global->AGENDA_AUTOSET_END_DATE_WITH_DELTA_HOURS))
	{
		$datef=dol_time_plus_duree($datep, $conf->global->AGENDA_AUTOSET_END_DATE_WITH_DELTA_HOURS, 'h');
	}
	print '<tr style="display: none"><td><span id="dateend"'.(GETPOST("actioncode") == 'AC_RDV'?' class="fieldrequired"':'').'>'.$langs->trans("DateActionEnd").'</span></td><td>';
	if (GETPOST("afaire") == 1) $form->select_date($datef,'p2',1,1,1,"action",1,1,0,0,'fulldayend');
	else if (GETPOST("afaire") == 2) $form->select_date($datef,'p2',1,1,1,"action",1,1,0,0,'fulldayend');
	else $form->select_date($datef,'p2',1,1,1,"action",1,1,0,0,'fulldayend');
	print '</td></tr>';

	// Status
	print '<tr style="display: none"><td width="10%">'.$langs->trans("Status").'</td>';
	print '<td>';
	$percent=-1;
	if (isset($_GET['status']) || isset($_POST['status'])) $percent=GETPOST('status');
	else if (isset($_GET['percentage']) || isset($_POST['percentage'])) $percent=GETPOST('percentage');
	else
	{
		if (GETPOST('complete') == '0' || GETPOST("afaire") == 1) $percent='0';
		else if (GETPOST('complete') == 100 || GETPOST("afaire") == 2) $percent=100;
	}
	$formactions->form_select_status_action('formaction',$percent,1,'complete');
	print '</td></tr>';
	// Realised by
	if (! empty($conf->global->AGENDA_ENABLE_DONEBY))
	{
		print '<tr><td class="nowrap">'.$langs->trans("ActionDoneBy").'</td><td>';
		print $form->select_dolusers(GETPOST("doneby")?GETPOST("doneby"):(! empty($object->userdoneid) && $percent==100?$object->userdoneid:0),'doneby',1);
		print '</td></tr>';
	}

	//type notification
	print '<tr id="typenotification" style="display: none"><td width="10%">'.$langs->trans("TypeNotification").'</td>';
	print '<td>';
	print $formactions->getTypeNotification();
	print '</td></tr>';
	    // Period

//		print '<tr id="period"><td>'.$langs->trans("Period").'</td><td colspan="3"></td></tr>';
	print '<tr id="period"><td>'.$langs->trans("Period").'</td><td colspan="3">'.$form->select_period('selperiod', $object->period).'</td></tr>';


	print '</table>';
	print '<br>';
	print '<table class="border" width="100%">';


	// Societe, contact
	print '<tr><td width="30%" class="nowrap">'.$langs->trans("ActionOnCompany").'</td><td>';
	if (GETPOST('socid','int') > 0)
	{
		$societe = new Societe($db);
		$societe->fetch(GETPOST('socid','int'));
		print $societe->getNomUrl(1);
		print '<input type="hidden" id="socid" name="socid" value="'.GETPOST('socid','int').'">';
	}
	else
	{

		$events=array();
		$events[]=array('method' => 'getContacts', 'url' => dol_buildpath('/core/ajax/contacts.php',1), 'htmlname' => 'contactid', 'params' => array('add-customer-contact' => 'disabled'));
		//For external user force the company to user company
		if (!empty($user->societe_id)) {
			$thirdparty_list = $form->select_thirdparty_list($user->societe_id,'socid','',1,1,0,'',0,0,$user->id);
		} else {
            $thirdparty_list = $form->select_thirdparty_list('','socid','',1,1,0,$events,'',0,0,$user->id);
		}
        $thirdparty_list = substr($thirdparty_list, 0, strpos($thirdparty_list, 'name')).' onchange="SocIdChange();" '.substr($thirdparty_list, strpos($thirdparty_list, 'name'));

        print $thirdparty_list;
	}
	print '</td></tr>';

	print '<tr><td class="nowrap">'.$langs->trans("ActionOnContact").'</td><td>';
	$form->select_contacts(GETPOST('socid','int'),GETPOST('contactid'),'contactid',1);
	print '</td></tr>';


	// Project
	if (! empty($conf->projet->enabled))
	{
		$formproject=new FormProjets($db);

		// Projet associe
		$langs->load("projects");

		print '<tr><td valign="top">'.$langs->trans("Project").'</td><td>';

		$numproject=$formproject->select_projects((! empty($societe->id)?$societe->id:0),GETPOST("projectid")?GETPOST("projectid"):'','projectid');
		if ($numproject==0)
		{
			print ' &nbsp; <a href="'.DOL_URL_ROOT.'/projet/card.php?socid='.$societe->id.'&action=create">'.$langs->trans("AddProject").'</a>';
		}
		print '</td></tr>';
	}
	if(!empty($origin) && !empty($originid))
	{
		include_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
		print '<tr><td>'.$langs->trans("LinkedObject").'</td>';
		print '<td colspan="3">'.dolGetElementUrl($originid,$origin,1).'</td></tr>';
		print '<input type="hidden" name="fk_element" size="10" value="'.GETPOST('originid').'">';
		print '<input type="hidden" name="elementtype" size="10" value="'.GETPOST('origin').'">';
	}

	if (GETPOST("datep") && preg_match('/^([0-9][0-9][0-9][0-9])([0-9][0-9])([0-9][0-9])$/',GETPOST("datep"),$reg))
	{
		$object->datep=dol_mktime(0,0,0,$reg[2],$reg[3],$reg[1]);
	}

	// Priority
	print '<tr><td class="nowrap">'.$langs->trans("Priority").'</td><td colspan="3">';
	print '<input type="text" id="priority" name="priority"  class="param_item" value="'.(GETPOST('priority')?GETPOST('priority'):($object->priority?$object->priority:'')).'" size="5">';
	print '</td></tr>';
    // Other attributes
    $parameters=array('id'=>$object->id);
    $reshook=$hookmanager->executeHooks('formObjectOptions',$parameters,$object,$action);    // Note that $action and $object may have been modified by hook


	if (empty($reshook) && ! empty($extrafields->attribute_label))
	{
		print $object->showOptionals($extrafields,'edit');
	}

	print '</table>';

	print '<center><br>';
	print '<input type="submit" class="button" value="'.$langs->trans("Add").'">';
	print ' &nbsp; &nbsp; ';
	print '<input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';
	print '</center>';
	print "</form>";
    print '</div>';
	print "<script>
				$('#formaction').submit(function(e){
					if($('#error').length>0&&$('#error').val()==1){
						alert('Дані на формі містять помилки.');
						return false;
					}
				});
		   </script>";
//    print '<style>
//            .tabBar{
//                width: 800px;
//                background-image: -moz-linear-gradient(center bottom , rgba(110, 110, 110, 0.5) 25%, rgba(210, 210, 210, 0.5) 100%);
//                border-color: #ccc #bbb #bbb;
//                border-radius: 6px;
//                border-style: solid;
//                border-width: 1px;
//                box-shadow: 3px 3px 4px #ddd;
//                color: #444;
//                margin: 0 0 14px;
//                padding: 9px 8px 8px;
//            }
//           </style>
//';
}
//var_dump($id);
//die();
// View or edit
if ($id > 0)
{
	$result1=$object->fetch($id);
	$result2=$object->fetch_thirdparty();
	$result3=$object->fetch_contact();
	$result4=$object->fetch_userassigned();
	$result5=$object->fetch_optionals($id,$extralabels);

	if ($result1 < 0 || $result2 < 0 || $result3 < 0 || $result4 < 0 || $result5 < 0)
	{
		dol_print_error($db,$object->error);
		exit;
	}

	if ($object->authorid > 0)		{ $tmpuser=new User($db); $res=$tmpuser->fetch($object->authorid); $object->author=$tmpuser; }
	if ($object->usermodid > 0)		{ $tmpuser=new User($db); $res=$tmpuser->fetch($object->usermodid); $object->usermod=$tmpuser; }


	/*
	 * Show tabs
	 */

	$head=actions_prepare_head($object);

	$now=dol_now();
	$delay_warning=$conf->global->MAIN_DELAY_ACTIONS_TODO*24*60*60;

	// Confirmation suppression action
	if ($action == 'delete')
	{
		print $form->formconfirm("card.php?id=".$id,$langs->trans("DeleteAction"),$langs->trans("ConfirmDeleteAction"),"confirm_delete",'','',1);
	}
//var_dump($action);
//	die();
	if ($action == 'edit' || $action == 'create' && isset($_REQUEST["duplicate_action"]))
	{
		$listofuserid = array();
		if(isset($_REQUEST["duplicate_action"])) {//if duplicate action, change author action
			$listofuserid = dol_json_decode($_SESSION['assignedtouser'],1);
			$listofuserid[$user->id] = $listofuserid[array_keys($listofuserid)[0]];
			$listofuserid[$user->id]['id']=$user->id;
			foreach (array_keys($listofuserid) as $key) {
				if ($key != $user->id) {
					unset($listofuserid[$key]);
				}
			}
			$_SESSION['assignedtouser'] = dol_json_encode($listofuserid);

			if(!isset($_SESSION['assignedtouser'])||empty($_SESSION['assignedtouser'])) {
				$_SESSION['assignedtouser'] = '{"' . $user->id . '":{"id":"' . $user->id . '","mandatory":0,"transparency":null}}';
			}
		}
		if(!isset($_REQUEST["duplicate_action"]))
        	print_fiche_titre($langs->trans("EditAction"));
		else
			print_fiche_titre($langs->trans("DuplicateAction"));
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
		print '<div id="addassignpanel" style="position: relative; z-index: 0; width: 30px">
			<button style="width: 25px;height: 29px;" title="Додати користувачів зі списку" onclick="ShowaddAssignedUsersForm();"><img style="margin-left: -3px" src="../../../htdocs/theme/eldy/img/Add.png"></button>
		</div>';
		print '<form id="addAssigned" action="'.$_SERVER['PHP_SELF'].'" method="POST">';
		print '<input type="hidden" id = "assignedJSON" name="assignedJSON" value="">';
		print '</form>';
        print '<form id="redirect" action="/dolibarr/htdocs/comm/action/result_action.php" method="get">
                <input type="hidden" name="backtopage" value="'.($backtopage != '1'? $backtopage : $_SERVER["HTTP_REFERER"]).'">
                <input type="hidden" name="mainmenu" value="'.$_REQUEST["mainmenu"].'">
                <input type="hidden" value="" id="redirect_actioncode" name="actioncode">';
		if(!isset($_REQUEST["duplicate_action"]))
			print '<input type="hidden" name="id" value="'.$id.'">
                <input type="hidden" name="action" value="edit">';
		else
			print '<input type="hidden" name="id" value="">
                <input type="hidden" name="action" value="add">';
        print '</form>';
		print '<form id = "formaction" name="formaction" action="'.$_SERVER['PHP_SELF'].'" method="POST">';
		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		print '<input type="hidden" name="socid" value="'.$_REQUEST["socid"].'">';
		print '<input type="hidden" name="type" id="type" value="">';
		if(!isset($_REQUEST["duplicate_action"])) {
			print '<input type="hidden" name="action" value="update">';
			print '<input type="hidden" name="id" value="' . $id . '">';
		}else {
			print '<input type="hidden" name="action" value="add">';
			print '<input type="hidden" name="id" value="">';
		}

		print '<input type="hidden" name="ref_ext" value="'.$object->ref_ext.'">';
		print '<input type="hidden" id="showform" value="0">';
		print '<input type="hidden" id="id_usr" value="'.$user->id.'">';
		if ($backtopage) print '<input type="hidden" name="backtopage" value="'.($backtopage != '1'? $backtopage : $_SERVER["HTTP_REFERER"]).'">';
		if (empty($conf->global->AGENDA_USE_EVENT_TYPE)) print '<input type="hidden" id="actioncode" name="actioncode" value="'.$object->type_code.'">';

		dol_fiche_head($head, 'card', $langs->trans("Action"),0,'action');

		print '<table class="border" width="100%">';

//		// Ref
//		print '<tr><td width="30%">'.$langs->trans("Ref").'</td><td colspan="3">'.$object->id.'</td></tr>';

//		// Title
//		print '<tr><td'.(empty($conf->global->AGENDA_USE_EVENT_TYPE)?' class="fieldrequired"':'').'>'.$langs->trans("Title").'</td><td colspan="3"><input type="text" name="label" size="50" value="'.$object->label.'"></td></tr>';

        // Type of event
//		if (! empty($conf->global->AGENDA_USE_EVENT_TYPE))
//		{
        print '<tr><td class="fieldrequired">'.$langs->trans("ActionType").'</td><td colspan="3">';
        $formactions->select_type_actions(GETPOST("actioncode")?GETPOST("actioncode"):$object->type_code, "actioncode","systemauto");
        print '</td></tr>';
//		}

		if(isset($_REQUEST["duplicate_action"]) && empty($datep)){
			$datep = time();
			$datef = $datep;
		}
		// Assigned to
		print '<tr><td class="nowrap">'.$langs->trans("ActionAssignedTo").'</td><td colspan="3">';
		$listofuserid=array();
		if (empty($donotclearsession))
		{
			if ($object->userownerid > 0) $listofuserid[$object->userownerid]=array('id'=>$object->userownerid,'transparency'=>$object->userassigned[$user->id]['transparency'],'answer_status'=>$object->userassigned[$user->id]['answer_status'],'mandatory'=>$object->userassigned[$user->id]['mandatory']);	// Owner first
			if (! empty($object->userassigned))	// Now concat assigned users
			{
				// Restore array with key with same value than param 'id'
				$tmplist1=$object->userassigned; $tmplist2=array();
				foreach($tmplist1 as $key => $val)
				{
					if ($val['id'] && $val['id'] != $object->userownerid) $listofuserid[$val['id']]=$val;
				}
			}
			$_SESSION['assignedtouser']=dol_json_encode($listofuserid);
		}
		else
		{
			if (!empty($_SESSION['assignedtouser']))
			{
				$listofuserid=dol_json_decode($_SESSION['assignedtouser'], true);
			}
		}
		print $form->select_dolusers_forevent(($action=='create'?'add':'update'), 'assignedtouser', 1, '', 0, '', '', 0, 0, 0, 'AND u.statut != 0');
//		if (in_array($user->id,array_keys($listofuserid))) print $langs->trans("MyAvailability").':  <input id="transparency" type="checkbox" name="transparency"'.($listofuserid[$user->id]['transparency']?' checked="checked"':'').'">'.$langs->trans("Busy");
		print '</td></tr>';
        // Full day event
        print '<tr style="display: none"><td>'.$langs->trans("EventOnFullDay").'</td><td colspan="3"><input type="checkbox" id="fullday" name="fullday" '.($object->fulldayevent?' checked="checked"':'').'></td></tr>';
		//GroupOfTask
		print '<tr><td width="10%">'.$langs->trans("GroupOfTask").'</td>';
		print '<td>';

		$percent=-1;
//		var_dump($object->groupoftask);
//		die();
		$respon = array();
		if(count($listofuserid) == 1) {
			$respon[] = $user->respon_id;
			$formactions->select_groupoftask('groupoftask', $respon, $object->groupoftask);
		}else{
			$assigneduser = new User($db);
			foreach($listofuserid as $id_usr){
				if($id_usr['id'] != $user->id){
				$assigneduser->fetch($id_usr['id']);
				if(!in_array($assigneduser->respon_id,$respon))
					$respon[]=$assigneduser->respon_id;
				}
			}
			$formactions->select_groupoftask('groupoftask', $respon, $object->groupoftask);
		}
        // Description
        print '<tr><td valign="top">'.$langs->trans("Description").'</td><td colspan="3">';
        // Editeur wysiwyg
        require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
        $doleditor=new DolEditor('note',$object->note,'',240,'dolibarr_notes','In',true,true,$conf->fckeditor->enabled,ROWS_5,90);
        $doleditor->Create();
        print '</td></tr>';
		// Note
		print '<tr><td valign="top">'.$langs->trans("Note").': що зробить, кінцева мета, підтвердження</td><td>';
//		echo '<pre>';
//		var_dump($object);
//		echo '</pre>';
//		die();
		print $form->select_confirmdoc('confirmdoc',$object->confirmdoc);
		print '</td></tr>';
		if($object->entity == 1) {
			print '<tr><td class="nowrap">Попередньо виконати до</td><td colspan="3">';
			$form->select_date($datepreperform ? $datepreperform->format('Y-m-d') : $object->datepreperform->format('Y-m-d'), 'preperform', 0, 0, 0, "action", 1, 0, 0, 0, 'fulldaystart');
			print '</td></tr>';
		}
		// Date start
		if($object->entity == 1)
			print '<tr><td class="nowrap"><span class="fieldrequired">'.$langs->trans("DateActionStart").'</span></td><td colspan="3">';
		elseif($object->entity == 0)
			print '<td width="30%" class="nowrap"><span class="fieldrequired">'.$langs->trans("DateAction").'</span></td><td>';

		if (GETPOST("afaire") == 1) $form->select_date($datep?$datep:$object->datep,'ap',1,1,0,"action",1,1,0,0,'fulldaystart');
		else if (GETPOST("afaire") == 2) $form->select_date($datep?$datep:$object->datep,'ap',1,1,1,"action",1,1,0,0,'fulldaystart');
		else $form->select_date($datep?$datep:$object->datep,'ap',1,1,1,"action",1,1,0,0,'fulldaystart');
		print '<span style="font-size: 12px">Необхідно часу  </span><input type="text" class="param" size="2" id = "exec_time" name="exec_time" value="'.((int)($object->datef-$object->datep)/60).'"><span style="font-size: 12px"> хвилин.</span></td></tr>';
		print '</td></tr>';
		if($object->entity == 1)
			print '<style> #aphour, #apmin, #apButtonNow {display: none} </style>';
		else
			print '<input id="typeaction" type="hidden" value="subaction" name="typeaction">';
		// Date end
		print '<tr style="display: none"><td>'.$langs->trans("DateActionEnd").'</td><td colspan="3">';
		if (GETPOST("afaire") == 1) $form->select_date($datef?$datef:$object->datef,'p2',1,1,1,"action",1,1,0,0,'fulldayend');
		else if (GETPOST("afaire") == 2) $form->select_date($datef?$datef:$object->datef,'p2',1,1,1,"action",1,1,0,0,'fulldayend');
		else $form->select_date($datef?$datef:$object->datef,'p2',1,1,1,"action",1,1,0,0,'fulldayend');
		print '</td></tr>';
		if($object->entity == 1) {
			// Status
			print '<tr><td class="nowrap">' . $langs->trans("Status") . ' / ' . $langs->trans("Percentage") . '</td><td colspan="3">';
			$percent = GETPOST("percentage") ? GETPOST("percentage") : $object->percentage;
			if (isset($_REQUEST["duplicate_action"]))
				$percent = -1;
			$formactions->form_select_status_action('formaction', $percent, 1);
			print '</td></tr>';
		}
		// Period
//    if (GETPOST("actioncode") == "AC_GLOBAL")
//    {
//    var_dump((GETPOST("actioncode") != "AC_GLOBAL"));
//        die(GETPOST("actioncode"));
//		echo '<pre>';
//		var_dump($_SESSION['assignedtouser']);
//		echo '</pre>';
//		die();
		print '<tr id="period"><td>'.$langs->trans("Period").'</td><td colspan="3">'.$form->select_period('selperiod', $object->period).'</td></tr>';
//    }
//        // Location
//	    if (empty($conf->global->AGENDA_DISABLE_LOCATION))
//	    {
//			print '<tr><td>'.$langs->trans("Location").'</td><td colspan="3"><input type="text" name="location" size="50" value="'.$object->location.'"></td></tr>';
//	    }
		// Realised by
		if (! empty($conf->global->AGENDA_ENABLE_DONEBY))
		{
			print '<tr><td class="nowrap">'.$langs->trans("ActionDoneBy").'</td><td colspan="3">';
			print $form->select_dolusers($object->userdoneid> 0?$object->userdoneid:-1,'doneby',1);
			print '</td></tr>';
		}

		//type notification
		print '<tr id="typenotification" style="display: none"><td width="10%">'.$langs->trans("TypeNotification").'</td>';
		print '<td>';
		print $formactions->getTypeNotification($object->typenotification);
		print '</td></tr>';
		print '</td></tr>';
//		var_dump($datepreperform);
//		die();
		if(empty($datepreperform)){
			$datepreperform = new DateTime();
		}
		print '</table>';

		print '<br><br>';

		print '<table class="border" width="100%">';

		// Thirdparty - Contact
		if ($conf->societe->enabled)
		{
			print '<tr><td width="30%">'.$langs->trans("ActionOnCompany").'</td>';
			print '<td>';
			$events=array();
			$events[]=array('method' => 'getContacts', 'url' => dol_buildpath('/core/ajax/contacts.php',1), 'htmlname' => 'contactid', 'params' => array('add-customer-contact' => 'disabled'));
			print $form->select_company($object->socid,'socid','',1,1,0,$events);
			print '</td>';
            print '</tr>';
            // Contact
            print '<tr><td>'.$langs->trans("Contact").'</td><td width="30%">';
            $form->select_contacts($object->socid, $object->contactid,'contactid',1);
            print '</td></tr>';
		}

		// Project
		if (! empty($conf->projet->enabled))
		{

			$formproject=new FormProjets($db);

			// Projet associe
			$langs->load("project");

			print '<tr><td width="30%" valign="top">'.$langs->trans("Project").'</td><td colspan="3">';
			$numprojet=$formproject->select_projects($object->socid,$object->fk_project,'projectid');
			if ($numprojet==0)
			{
				print ' &nbsp; <a href="../../projet/card.php?socid='.$object->socid.'&action=create">'.$langs->trans("AddProject").'</a>';
			}
			print '</td></tr>';
		}

		// Priority
		print '<tr><td nowrap width="30%">'.$langs->trans("Priority").'</td><td colspan="3">';
		print '<input id="priority" type="text" name="priority" value="'.($object->priority?$object->priority:'').'" size="5">';
		print '</td></tr>';

		// Object linked
		if (! empty($object->fk_element) && ! empty($object->elementtype))
		{
			include_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
			print '<tr><td>'.$langs->trans("LinkedObject").'</td>';
			print '<td colspan="3">'.dolGetElementUrl($object->fk_element,$object->elementtype,1).'</td></tr>';
		}
        // Other attributes
        $parameters=array('colspan'=>' colspan="3"', 'colspanvalue'=>'3', 'id'=>$object->id);
        $reshook=$hookmanager->executeHooks('formObjectOptions',$parameters,$object,$action);    // Note that $action and $object may have been modified by hook
		if (empty($reshook) && ! empty($extrafields->attribute_label))
		{
			print $object->showOptionals($extrafields,'edit');
		}

		print '</table>';

		dol_fiche_end();

		print '<center><input type="submit" class="button" name="edit" value="'.$langs->trans("Save").'">';
		print ' &nbsp; &nbsp; <input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';
		print '</center>';

		print '</form>';
//        print '</div>';
//		unset($_REQUEST["duplicate_action"]);

	}
//	else
//	{
//		dol_fiche_head($head, 'card', $langs->trans("Action"),0,'action');
//
//		// Affichage fiche action en mode visu
//		print '<table class="border" width="100%">';
//
//		$linkback = '<a href="'.DOL_URL_ROOT.'/comm/action/listactions.php">'.$langs->trans("BackToList").'</a>';
//
//		// Ref
//		print '<tr><td width="30%">'.$langs->trans("Ref").'</td><td colspan="3">';
//		print $form->showrefnav($object, 'id', $linkback, ($user->societe_id?0:1), 'id', 'ref', '');
//		print '</td></tr>';
//
//		// Type
//		if (! empty($conf->global->AGENDA_USE_EVENT_TYPE))
//		{
//			print '<tr><td>'.$langs->trans("Type").'1</td><td colspan="3">'.$object->type.'</td></tr>';
//		}
//
////		// Title
////		print '<tr><td>'.$langs->trans("Title").'</td><td colspan="3">'.$object->label.'</td></tr>';
//
//        // Full day event
//        print '<tr><td>'.$langs->trans("EventOnFullDay").'</td><td colspan="3">'.yn($object->fulldayevent).'</td></tr>';
//
//		$rowspan=4;
//		if (empty($conf->global->AGENDA_DISABLE_LOCATION)) $rowspan++;
//
//		// Date start
//		print '<tr><td width="30%">'.$langs->trans("DateActionStart").'</td><td colspan="3">';
//		if (! $object->fulldayevent) print dol_print_date($object->datep,'dayhour');
//		else print dol_print_date($object->datep,'day');
//		if ($object->percentage == 0 && $object->datep && $object->datep < ($now - $delay_warning)) print img_warning($langs->trans("Late"));
//		print '</td>';
//		print '</tr>';
//
//		// Date end
//		print '<tr><td>'.$langs->trans("DateActionEnd").'</td><td colspan="3">';
//        if (! $object->fulldayevent) print dol_print_date($object->datef,'dayhour');
//		else print dol_print_date($object->datef,'day');
//		if ($object->percentage > 0 && $object->percentage < 100 && $object->datef && $object->datef < ($now- $delay_warning)) print img_warning($langs->trans("Late"));
//		print '</td></tr>';
//
//		// Status
//		print '<tr><td class="nowrap">'.$langs->trans("Status").' / '.$langs->trans("Percentage").'</td><td colspan="3">';
//		print $object->getLibStatut(4);
//		print '</td></tr>';
//
//        // Location
//	    if (empty($conf->global->AGENDA_DISABLE_LOCATION))
//    	{
//			print '<tr><td>'.$langs->trans("Location").'</td><td colspan="3">'.$object->location.'</td></tr>';
//    	}
//
//		// Assigned to
//    	print '<tr><td width="30%" class="nowrap">'.$langs->trans("ActionAssignedTo").'</td><td colspan="3">';
//		$listofuserid=array();
//		if (empty($donotclearsession))
//		{
//			if ($object->userownerid > 0) $listofuserid[$object->userownerid]=array('id'=>$object->userownerid,'transparency'=>$object->transparency);	// Owner first
//			if (! empty($object->userassigned))	// Now concat assigned users
//			{
//				// Restore array with key with same value than param 'id'
//				$tmplist1=$object->userassigned; $tmplist2=array();
//				foreach($tmplist1 as $key => $val)
//				{
//					if ($val['id'] && $val['id'] != $object->userownerid) $listofuserid[$val['id']]=$val;
//				}
//			}
//			$_SESSION['assignedtouser']=dol_json_encode($listofuserid);
//		}
//		else
//		{
//			if (!empty($_SESSION['assignedtouser']))
//			{
//				$listofuserid=dol_json_decode($_SESSION['assignedtouser'], true);
//			}
//		}
//		print $form->select_dolusers_forevent('view','assignedtouser',1);
//		if (in_array($user->id,array_keys($listofuserid))) print $langs->trans("MyAvailability").': '.(($object->userassigned[$user->id]['transparency'] > 0)?$langs->trans("Busy"):$langs->trans("Available"));	// We show nothing if event is assigned to nobody
//		print '	</td></tr>';
//
//		// Done by
//		if ($conf->global->AGENDA_ENABLE_DONEBY)
//		{
//			print '<tr><td class="nowrap">'.$langs->trans("ActionDoneBy").'</td><td colspan="3">';
//			if ($object->userdoneid > 0)
//			{
//				$tmpuser=new User($db);
//				$tmpuser->fetch($object->userdoneid);
//				print $tmpuser->getNomUrl(1);
//			}
//			print '</td></tr>';
//		}
//
//		print '</table>';
//
//		print '<br><br>';
//
//		print '<table class="border" width="100%">';
//
//		// Third party - Contact
//		if ($conf->societe->enabled)
//		{
//			print '<tr><td width="30%">'.$langs->trans("ActionOnCompany").'</td><td>'.($object->thirdparty->id?$object->thirdparty->getNomUrl(1):$langs->trans("None"));
//			if (is_object($object->thirdparty) && $object->thirdparty->id > 0 && $object->type_code == 'AC_TEL')
//			{
//				if ($object->thirdparty->fetch($object->thirdparty->id))
//				{
//					print "<br>".dol_print_phone($object->thirdparty->phone);
//				}
//			}
//			print '</td>';
//			print '<td>'.$langs->trans("Contact").'</td>';
//			print '<td>';
//			if ($object->contactid > 0)
//			{
//				print $object->contact->getNomUrl(1);
//				if ($object->contactid && $object->type_code == 'AC_TEL')
//				{
//					if ($object->contact->fetch($object->contactid))
//					{
//						print "<br>".dol_print_phone($object->contact->phone_pro);
//					}
//				}
//			}
//			else
//			{
//				print $langs->trans("None");
//			}
//			print '</td></tr>';
//		}
//
//		// Project
//		if (! empty($conf->projet->enabled))
//		{
//			print '<tr><td width="30%" valign="top">'.$langs->trans("Project").'</td><td colspan="3">';
//			if ($object->fk_project)
//			{
//				$project=new Project($db);
//				$project->fetch($object->fk_project);
//				print $project->getNomUrl(1,'',1);
//			}
//			print '</td></tr>';
//		}
//
//		// Priority
//		print '<tr><td nowrap width="30%">'.$langs->trans("Priority").'</td><td colspan="3">';
//		print ($object->priority?$object->priority:'');
//		print '</td></tr>';
//
//		// Object linked
//		if (! empty($object->fk_element) && ! empty($object->elementtype))
//		{
//			include_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
//			print '<tr><td>'.$langs->trans("LinkedObject").'</td>';
//			print '<td colspan="3">'.dolGetElementUrl($object->fk_element,$object->elementtype,1).'</td></tr>';
//		}
//
//		// Description
//		print '<tr><td valign="top">'.$langs->trans("Description").'</td><td colspan="3">';
//		print dol_htmlentitiesbr($object->note);
//		print '</td></tr>';
//
//        // Other attributes
//		$parameters=array('colspan'=>' colspan="3"', 'colspanvalue'=>'3', 'id'=>$object->id);
//        $reshook=$hookmanager->executeHooks('formObjectOptions',$parameters,$object,$action);    // Note that $action and $object may have been modified by hook
//
//		print '</table>';
//
//		//Extra field
//		if (empty($reshook) && ! empty($extrafields->attribute_label))
//		{
//			print '<br><br><table class="border" width="100%">';
//			foreach($extrafields->attribute_label as $key=>$label)
//			{
//				$value=(isset($_POST["options_".$key])?$_POST["options_".$key]:(isset($object->array_options['options_'.$key])?$object->array_options['options_'.$key]:''));
//				print '<tr><td width="30%">'.$label.'</td><td>';
//				print $extrafields->showOutputField($key,$value);
//				print "</td></tr>\n";
//			}
//			print '</table>';
//		}
//
//		dol_fiche_end();
//	}


	/*
	 * Barre d'actions
	 */

//	print '<div class="tabsAction">';
//
//	$parameters=array();
//	$reshook=$hookmanager->executeHooks('addMoreActionsButtons',$parameters,$object,$action);    // Note that $action and $object may have been modified by hook
//	if (empty($reshook))
//	{
//		if ($action != 'edit')
//		{
//			if ($user->rights->agenda->allactions->create ||
//			   (($object->authorid == $user->id || $object->userownerid == $user->id) && $user->rights->agenda->myactions->create))
//			{
//				print '<div class="inline-block divButAction"><a class="butAction" href="card.php?action=edit&id='.$object->id.'">'.$langs->trans("Modify").'</a></div>';
//			}
//			else
//			{
//				print '<div class="inline-block divButAction"><a class="butActionRefused" href="#" title="'.$langs->trans("NotAllowed").'">'.$langs->trans("Modify").'</a></div>';
//			}
//
//			if ($user->rights->agenda->allactions->delete ||
//			   (($object->authorid == $user->id || $object->userownerid == $user->id) && $user->rights->agenda->myactions->delete))
//			{
//				print '<div class="inline-block divButAction"><a class="butActionDelete" href="card.php?action=delete&id='.$object->id.'">'.$langs->trans("Delete").'</a></div>';
//			}
//			else
//			{
//				print '<div class="inline-block divButAction"><a class="butActionRefused" href="#" title="'.$langs->trans("NotAllowed").'">'.$langs->trans("Delete").'</a></div>';
//			}
//		}
//	}
//
//	print '</div>';

//	if ($action != 'edit')
//	{
//		// Link to agenda views
//		print '<div id="agendaviewbutton">';
//		print '<form name="listactionsfiltermonth" action="'.DOL_URL_ROOT.'/comm/action/index.php" method="POST" style="float: left; padding-right: 10px;">';
//		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
//		print '<input type="hidden" name="action" value="show_month">';
//		print '<input type="hidden" name="year" value="'.dol_print_date($object->datep,'%Y').'">';
//		print '<input type="hidden" name="month" value="'.dol_print_date($object->datep,'%m').'">';
//		print '<input type="hidden" name="day" value="'.dol_print_date($object->datep,'%d').'">';
//		//print '<input type="hidden" name="day" value="'.dol_print_date($object->datep,'%d').'">';
//		print img_picto($langs->trans("ViewCal"),'object_calendar','class="hideonsmartphone"').' <input type="submit" style="min-width: 120px" class="button" name="viewcal" value="'.$langs->trans("ViewCal").'">';
//		print '</form>'."\n";
//		print '<form name="listactionsfilterweek" action="'.DOL_URL_ROOT.'/comm/action/index.php" method="POST" style="float: left; padding-right: 10px;">';
//		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
//		print '<input type="hidden" name="action" value="show_week">';
//		print '<input type="hidden" name="year" value="'.dol_print_date($object->datep,'%Y').'">';
//		print '<input type="hidden" name="month" value="'.dol_print_date($object->datep,'%m').'">';
//		print '<input type="hidden" name="day" value="'.dol_print_date($object->datep,'%d').'">';
//		//print '<input type="hidden" name="day" value="'.dol_print_date($object->datep,'%d').'">';
//		print img_picto($langs->trans("ViewCal"),'object_calendarweek','class="hideonsmartphone"').' <input type="submit" style="min-width: 120px" class="button" name="viewweek" value="'.$langs->trans("ViewWeek").'">';
//		print '</form>'."\n";
//		print '<form name="listactionsfilterday" action="'.DOL_URL_ROOT.'/comm/action/index.php" method="POST" style="float: left; padding-right: 10px;">';
//		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
//		print '<input type="hidden" name="action" value="show_day">';
//		print '<input type="hidden" name="year" value="'.dol_print_date($object->datep,'%Y').'">';
//		print '<input type="hidden" name="month" value="'.dol_print_date($object->datep,'%m').'">';
//		print '<input type="hidden" name="day" value="'.dol_print_date($object->datep,'%d').'">';
//		//print '<input type="hidden" name="day" value="'.dol_print_date($object->datep,'%d').'">';
//		print img_picto($langs->trans("ViewCal"),'object_calendarday','class="hideonsmartphone"').' <input type="submit" style="min-width: 120px" class="button" name="viewday" value="'.$langs->trans("ViewDay").'">';
//		print '</form>'."\n";
//		print '<form name="listactionsfilterperuser" action="'.DOL_URL_ROOT.'/comm/action/peruser.php" method="POST" style="float: left; padding-right: 10px;">';
//		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
//		print '<input type="hidden" name="action" value="show_peruser">';
//		print '<input type="hidden" name="year" value="'.dol_print_date($object->datep,'%Y').'">';
//		print '<input type="hidden" name="month" value="'.dol_print_date($object->datep,'%m').'">';
//		print '<input type="hidden" name="day" value="'.dol_print_date($object->datep,'%d').'">';
//		//print '<input type="hidden" name="day" value="'.dol_print_date($object->datep,'%d').'">';
//		print img_picto($langs->trans("ViewCal"),'object_calendarperuser','class="hideonsmartphone"').' <input type="submit" style="min-width: 120px" class="button" name="viewperuser" value="'.$langs->trans("ViewPerUser").'">';
//		print '</form>'."\n";
//		print '</div>';
//
//		if (empty($conf->global->AGENDA_DISABLE_BUILDDOC))
//		{
//			print '<div style="clear:both;">&nbsp;<br><br></div><div class="fichecenter"><div class="fichehalfleft">';
//            print '<a name="builddoc"></a>'; // ancre
//
//            /*
//             * Documents generes
//             */
//
//            $filedir=$conf->agenda->multidir_output[$conf->entity].'/'.$object->id;
//            $urlsource=$_SERVER["PHP_SELF"]."?socid=".$object->id;
//
//            $genallowed=$user->rights->agenda->myactions->create;
//	        $delallowed=$user->rights->agenda->myactions->delete;
//
//            $var=true;
//
//            $somethingshown=$formfile->show_documents('agenda',$object->id,$filedir,$urlsource,$genallowed,$delallowed,'',0,0,0,0,0,'','','',$object->default_lang);
//
//			print '</div><div class="fichehalfright"><div class="ficheaddleft">';
//
//
//			print '</div></div></div>';
//
//            print '<div style="clear:both;">&nbsp;</div>';
//	    }
//	}
}
print "<script>
    $(function($) {
        $.mask.definitions['~']='[+-]';
        $('#preperform').mask('99.99.9999');
    });
</script>";
print '
 <script type="text/javascript">
        $(document).ready(function(){

        	$.cookie("ChangeDate", false);
        	$("#actioncode").removeClass("flat");
            $("#actioncode").addClass("combobox");
            $("#actioncode").unbind("change");
            $("#contactid").removeClass("flat");
            $("#contactid").addClass("combobox");
            $("#socid").removeClass("flat");
            $("#socid").addClass("combobox");
            if($("#mainmenu").length>0 && $("#mainmenu").val().length>0){
				setActionCode();
            }

//				$("#addassignpanel").offset({top:$("#updateassignedtouser").offset().top-27,left:663});
//            console.log();
//            return;
//			console.log("'.$action.'"!="edit", "testoo");
			if("'.$action.'"!="edit")
            	ActionCodeChanged();
            $("#assignedtouser").width(350);
            if($("#addassignedtouser").length>0)
            	$("#addassignpanel").offset({top:$("#addassignedtouser").offset().top-1,left:717});
			if($("#updateassignedtouser").length>0){
				$("#addassignpanel").css("top", $("#updateassignedtouser").offset().top-93);
				$("#addassignpanel").css("left", $("#updateassignedtouser").offset().left+155);
//				 ({top:$("#updateassignedtouser").offset().top-42,left:663});

			}
            $("a#sendSMS").attr("id", "addAssignedUsers");
            $("div#sendSMSform").attr("id", "addAssignedUsersForm");
            $("b#phone_numbertitle").html("'.("Вкажіть користувачів, що пов'язані з дією").'");
            $("#addAssignedUsersForm").find("input").remove();
            $("#addAssignedUsersForm").find("textarea").remove();
            $("#addAssignedUsersForm").width(500);
            var buttons = $("#addAssignedUsersForm").find("button");
			buttons[0].innerText="Зберегти";
			buttons[0].onclick=function(){
				addAssignedUsers();
			};
			var assignedForm = $("#addAssignedUsersForm").find("form");
			assignedForm = assignedForm[0];
			assignedForm.id = "selectAssignedUser";
			assignedForm.innerHTML = "<select id=assegnedusers name=assignedusers[] size=20 class=combobox multiple>"+$("#assignedtouser").html()+"</select>";
			$("#assegnedusers").prepend($("<option value='.getUsersByRespon('purchase').'>Постачальники</option>"));
			$("#assegnedusers").prepend($("<option value='.getUsersByRespon('sale').'>Торгівельні агенти</option>"));
			$("#assegnedusers").prepend($("<option value='.getUsersByRespon('dir_depatment').'>Директори департаментів</option>"));
			$("#assegnedusers").prepend($("<option value='.getUsersByRespon('senior_manager').'>Старший відділу</option>"));
			$("#addAssignedUsersForm").find("select").width(500);
            $(".tabBar").width(800);
            $("#event_desc").on("click", redirect);
            $("#priority").on("change",ActionCodeChanged);
//            var link = "http://"+location.hostname+"/dolibarr/htdocs/comm/action/card.php?action=get_freetime&date=2016-01-21";
//            console.log(link);
//            $.ajax({
//            	url:
//            })
        });

        function ShowaddAssignedUsersForm(){
			location.href ="#addAssignedUsers";
			$("#addAssignedUsersForm").show();
        }
        function redirect(){
            $("#redirect").submit();
        }
        function SocIdChange(){
            if($("select#socid").val() == -1) return;
            var link = "http://"+location.hostname+"/dolibarr/htdocs/comm/action/card.php?action=get_contactlist&socid="+$("select#socid").val();
//            console.log(link);
            $.ajax({
                url: link,
                cahce: false,
                success: function(html){
                    $("select#contactid").find("option").remove();
                    var optionList = html.substr(strpos(html, "<option value="));
                    optionList = optionList.substr(0, strpos(optionList, "</select>"));
                    $("select#contactid").append(optionList);
                }
            })
        }
        function strpos( haystack, needle, offset){ // Find position of first occurrence of a string
            var i = haystack.indexOf( needle, offset ); // returns -1
            return i >= 0 ? i : false;
        }
//        $(window).mousedown(function(){
//        	var date = new Date($("#ap").val().substr(6,4), $("#ap").val().substr(3,2), $("#ap").val().substr(0,2));
//        	console.log(date);
//        })
        function dpChangeDay(id, format){
//        	return;
//        	console.log("test");
//            if(id == ""+id+""){
                $("#p2").val($("#"+id+"").val())
                $("#"+id+"day").val($("#"+id+"").val().substr(0,2));
                $("#"+id+"month").val($("#"+id+"").val().substr(3,2));
                $("#"+id+"year").val($("#"+id+"").val().substr(6,4));
                if(id == "ap"){
					$("#p2day").val($("#"+id+"day").val());
					$("#p2month").val($("#"+id+"month").val());
					$("#p2year").val($("#"+id+"year").val());
                }
                if($("#showform").val()!=0){
					var date = new Date($("#"+id+"").val().substr(6,4), $("#"+id+"").val().substr(3,2), $("#"+id+"").val().substr(0,2));
					var today = new Date();
					if(date>today){
						$("select#"+id+"hour").val("00");
						$("select#"+id+"min").val("00");
					}
                }else{
                	$("#showform").val(1);
                }
//            }
//            console.log(getParameterByName("action") != "edit", $.cookie("ChangeDate") == "true");
            if(getParameterByName("action") != "edit" || $.cookie("ChangeDate") == "true"){
//				setP2(0);
				console.log("#"+id+"min");
				CalcP($("#"+id+"").val()+" "+$("select#"+id+"hour").val()+":"+$("select#"+id+"min").val(), $("#exec_time_"+id).val(), ' . $user->id . ', id);//Розрахунок часу початку дії

				$("#"+id+"hour").removeClass("fielderrorSelBorder");
				$("#"+id+"min").removeClass("fielderrorSelBorder");
				$("#type").val("");
				$("#error").val(0);
            }
        }
        $("button").click(function(e){
        	$.cookie("ChangeDate", true);
        	console.log("set ChangeDate");
        })
        $(".exec_time").keypress(function(e){
        	if(e.keyCode == 13){
				CalcP2(e.target.id);
        		return false;
			}
//        	console.log(e.keyCode == 13);
        })
        function setP2(showalert){
        	console.log("setP2");

            if($("select#actioncode").val() == 0) return;
            else if($("select#actioncode").val()!=0){
            	console.log($("select#aphour").val() == -1 && $("select#apmin").val() == -1);
//				if($("select#aphour").val() == -1 && $("select#apmin").val() == -1){
////					console.log($("select#aphour").val());
////					$("select#aphour" [value='.'"08"'.']).attr("selected", "selected");
//					document.getElementById("aphour").value = "08";
//					document.getElementById("apmin").value = "00";
////					console.log($("select#aphour").val());
//				}
				var link = "http://"+location.hostname+"/dolibarr/htdocs/comm/action/card.php?action=get_exectime&code="+$("select#actioncode").val();
//                    console.log(link);
				$.ajax({
					url:link,
					cache: false,
					success: function(html){
						$("#exec_time_ap").val(html);
						if(("#exec_time_dateNextAction").length>0)
							$("#exec_time_dateNextAction").val(html);
						var hour = '.date('H').';
						var min = '.date('i').';
						if(hour<10)
							hour = "0"+hour;
						if(min<10)
							min = "0"+min;
						$("select#aphour").val(hour);
						$("select#apmin").val(min);

						$("select#aphour").bind("change", ApHourChanged);
						$("select#apmin").bind("change", ApHourChanged);
						$("#p2").val($("#ap").val())
						$("#apday").val($("#ap").val().substr(0,2));
						$("#apmonth").val($("#ap").val().substr(3,2));
						$("#apyear").val($("#ap").val().substr(6,4));
						CalcP($("#apyear").val()+"-"+$("#apmonth").val()+"-"+$("#apday").val()+" "+$("#aphour").val()+":"+$("#apmin").val(), $("#exec_time_ap").val(), '.$user->id.', "ap");
						$("#p2day").val($("#apday").val());
						$("#p2month").val($("#apmonth").val());
						$("#p2year").val($("#apyear").val());
//						return;
//						if($("select#aphour").val() == -1 && $("select#apmin").val() == -1){
////						link = "http://"+location.hostname+"/dolibarr/htdocs/comm/action/card.php?action=get_freetime&minute="+$("#exec_time").val()+"&date="+$("#apyear").val()+"-"+$("#apmonth").val()+"-"+$("#apday").val()+"&id_usr=' . $user->id . '&actioncode="+$("select#actioncode").val();
////						setTime(link);
//							CalcP($("#apyear").val()+"-"+$("#apmonth").val()+"-"+$("#apday").val(), $("#exec_time").val(), '.$user->id.');
//						}else
//							CalcP2();
					}
				})
			}else{
				if(showalert == 1)
					alert("Будь ласка вкажіть тип дії");
			}


        }
		function ApHourChanged(){
			CalcP($("#apyear").val()+"-"+$("#apmonth").val()+"-"+$("#apday").val()+" "+$("#aphour").val()+":"+$("#apmin").val(), $("#exec_time").val(), '.$user->id.');
		}
        function ActionCodeChanged(){
            if(!$("#ap").val()){
                var date = new Date();
                var month = date.getMonth()+1;
                var day = date.getDate();
                var year = date.getFullYear();
                $("#apday").val(day);
                $("#apmonth").val(month);
                $("#apyear").val(year);
                $("#ap").val((day<10 ? "0" : "") + day+"."+(month<10 ? "0" : "") + month+"."+year);
            }
////            console.log($("#aphour").val() ,$("#apmin").val());
//            if($("#aphour").val() == -1 || $("#apmin").val() == -1){
//                document.getElementById("aphour").value=formatDate(new Date(), "HH");
//                document.getElementById("apmin").value=formatDate(new Date(), "mm");
//            }
//            dpChangeDay("actioncode","dd.MM.yyyy");
			setP2(0);
//            if($("#actioncode").val() != 0)
//            	setP2();
            $("#redirect_actioncode").val($("input#actioncode").val());
//            console.log("showperiod", $("select#actioncode").val() == "AC_CURRENT");
            if($.inArray($("select#actioncode").val(), ["AC_GLOBAL","AC_CURRENT"]) >=0 && $("#parent_id").val().length == 0){
            	$("#apmin").hide();
            	$("#aphour").hide();
                $("#period").show();
                $("#typenotification").show();
                $(".global_current").show();
            }else{
            	$(".global_current").hide();
                $("#period").hide();
                $("#typenotification").hide();
            	$("#apmin").show();
            	$("#aphour").show();
			}

        }
    </script>';

        print '<script type="text/javascript">
			$(".param").keypress(function( b ){

					var C = /[0-9\x25\x27\x24\x23]/;

				  var a = b.which;

				   var c = String.fromCharCode(a);

					return !!(a==0||a==8||a==9||a==13||c.match(C));
			});
			$("#exec_time_ap").blur(function(e){
				CalcP2(e.target.id);
			});

        </script>';
//llxFooter();
llxPopupMenu();
$db->close();
exit();
function getActionStatus($action_id){
	global $db;
	$sql = "select percent from llx_actioncomm where id = ".$action_id;
	$res = $db->query($sql);
	if(!$res)
		dol_print_error($db);
	$obj = $db->fetch_object($res);
	return $obj->percent;
}
function changeDateAction($action_id, $newdate, $minutes, $type){
//	echo '<pre>';
//	var_dump($action_id, $newdate, $minutes, $type);
//	echo '</pre>';
//	die();
	global $db,$user;
	$sql = "select datep from llx_actioncomm where id = ".$action_id;
	$res = $db->query($sql);
	if(!$res)
		dol_print_error($db);
	$obj = $db->fetch_object($res);
	if($obj->datep == $newdate.':00')
		return 0;
	$date = new DateTime($newdate);
//	var_dump(date('Y-m-d H:i:s', mktime($date->format('H'), $date->format('i'), 0, $date->format('m'), $date->format('d'), $date->format('Y'))+ $minutes*60));
//	die();
	$Action = new ActionComm($db);
	$Action->fetch($action_id);

	$Action->percentage = -100;
	$Action->update($user);
	$Action->percentage = 0;
	$Action->parent_id = $Action->id;
	$Action->id = null;
	$usr_tmp = new User($db);
	$usr_tmp->fetch($Action->authorid);
	$Action->datep = dol_mktime($date->format('H'), $date->format('i'), 0, $date->format('m'), $date->format('d'), $date->format('Y'));
	$Action->datef = $Action->datep + $minutes*60;
	$Action->usermodid = $user->id;
	$Action->typeSetOfDate = $type;
//	echo '<pre>';
//	var_dump($Action);
//	echo '</pre>';
//	die();
	$Action->add($usr_tmp,0,false);
	return 1;
}
function getUsersByRespon($respon)
{
	global $db;
	$sql = "select `llx_user`.`rowid` from llx_user
		inner join `responsibility` on `responsibility`.`rowid`=`llx_user`.`respon_id`
		inner join `responsibility` res2 on `res2`.`rowid`=`llx_user`.`respon_id2`
		where 1
		and `responsibility`.active = 1
		and `llx_user`.`active`=1
		and (`responsibility`.`alias`='" .$respon . "' or res2.`alias`='" .$respon . "')";
	$res = $db->query($sql);
	$rowidList = array();
	while ($obj = $db->fetch_object($res)) {
		$rowidList[] = $obj->rowid;
	}
	return implode(',', $rowidList);
}