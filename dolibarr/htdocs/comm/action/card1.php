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
//echo '<pre>';
//var_dump($_REQUEST);
//echo '</pre>';
//die();
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';

if($_GET['action']=='get_exectime'){

	$Action = new ActionComm($db);
	$exec_time = $Action->GetExecTime($_GET['code']);
    echo $exec_time;
    exit();

}elseif($_GET['action']=='get_contactlist'){
    require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
    $form = new Form($db);
    $list = $form->select_contacts($_GET['socid'], '','contactid',1);
    echo $list;
    exit();
}elseif($_GET['action']=='delete_action'){
	global $db;
    $sql = 'update llx_actioncomm set active = 0 where id='.$_GET['rowid'];
    $res = $db->query($sql);
    if(!$res){
        var_dump($sql);
        dol_print_error($db);
    }
    echo 1;
    exit();
}elseif($_GET['action']=='received_action'){
    global $db;
    $sql = 'update llx_actioncomm set dateconfirm = Now(), percent=0 where id='.$_GET['rowid'];
    $res = $db->query($sql);
    if(!$res){
        var_dump($sql);
        dol_print_error($db);
    }
    return 1;
    exit();
}elseif($_GET['action']=='shownote'){
	global $db;
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
    global $db;
	$sql = 'select period, datea from `llx_actioncomm` where id='.$_GET['rowid'];
	$res = $db->query($sql);
	if(!$res){
        dol_print_error($db);
    }
	$obj = $db->fetch_object($res);
	if(!empty($obj->period)){//Створюю таке саме завдання через вказаний інтервал часу
		$date = new DateTime($obj->datea);
		$mkDate = mktime('0','0','0',$date->format('m'),$date->format('d'),$date->format('Y'));
//		var_dump(date('d.m.Y',$mkDate));
		switch($obj->period){
			case 'EveryWeek':{
				$newDate=mktime('0','0','0',$date->format('m'),7+(int)$date->format('d'),$date->format('Y'));
			}break;
			case 'EveryMonth':{
				$newDate=mktime('0','0','0',1+(int)$date->format('m'),$date->format('d'),$date->format('Y'));
			}break;
			case 'Quarterly':{
				$newDate=mktime('0','0','0',3+(int)$date->format('m'),$date->format('d'),$date->format('Y'));
			}break;
			case 'Annually':{
				$newDate=mktime('0','0','0',$date->format('m'),$date->format('d'),1+(int)$date->format('Y'));
			}break;
		}
		$newAction = new ActionComm($db);
		$newAction->fetch($_GET['rowid']);
		$lengthOfTime = $newAction->datef-$newAction->datep;
		$newAction->datep = $newDate;
		$newAction->datef = $newDate+$lengthOfTime;
		$newAction->add($user);
	}
    $sql = 'update llx_actioncomm set dateSetExec = Now(), percent=100 where id='.$_GET['rowid'];
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
    exit();
}elseif($_GET['action']=='get_freetime'){

	global $db, $user;
	$Action = new ActionComm($db);
	$freetime = $Action->GetFreeTime($_GET['date'], $_GET['id_usr'], $_GET['minute'], $_GET['priority']);
//	echo '<pre>';
//	var_dump($freetime);
//	echo '</pre>';
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
$contactid=GETPOST('contactid','int');
$origin=GETPOST('origin','alpha');
$originid=GETPOST('originid','int');
if ($cancel)
{
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
$datef=dol_mktime($fulldayevent?'23':GETPOST("p2hour"), $fulldayevent?'59':GETPOST("p2min"), $fulldayevent?'59':'0', GETPOST("p2month"), GETPOST("p2day"), GETPOST("p2year"));

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
	$datef=dol_mktime($fulldayevent?'23':GETPOST("p2hour"), $fulldayevent?'59':GETPOST("p2min"), $fulldayevent?'59':'0', GETPOST("p2month"), GETPOST("p2day"), GETPOST("p2year"));

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
		$idaction=$object->add($user);
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
//					var_dump($idaction, $object->type_code, $backtopage);
//					die('stop');
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
		$datef=dol_mktime($fulldayevent?'23':$p2hour, $fulldayevent?'59':$p2min, $fulldayevent?'59':'0', $_POST["p2month"], $_POST["p2day"], $_POST["p2year"]);

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
		$object->period 	 = GETPOST("selperiod");

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

if ($action == 'create')
{
//	echo '<pre>';
//	var_dump($_POST);
//	echo '</pre>';
//	die();
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
	print '<input type="hidden" id="showform1" value="0">';
	print '<input type="hidden" id="mainmenu" name="mainmenu" value="'.$_REQUEST["mainmenu"].'">';
	print '<input type="hidden" name="parent_id" value="'.$_REQUEST["parent_id"].'">';
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

//    // Title
//	print '<tr><td'.(empty($conf->global->AGENDA_USE_EVENT_TYPE)?' class="fieldrequired"':'').'>'.$langs->trans("Title").'</td><td><input type="text" id="label" name="label" size="60" value="'.GETPOST('label').'"></td></tr>';

    // Type of event
//	if (! empty($conf->global->AGENDA_USE_EVENT_TYPE))
//	{
    print '<tr><td width="30%"><span class="fieldrequired">'.$langs->trans("ActionType").'</span></b></td><td>';
    $formactions->select_type_actions(GETPOST("actioncode")?GETPOST("actioncode"):$object->type_code, "actioncode","systemauto");
    print '</td></tr>';
//	}

    // Full day
    print '<tr><td>'.$langs->trans("EventOnFullDay").'</td><td><input type="checkbox" id="fullday" name="fullday" '.(GETPOST('fullday')?' checked="checked"':'').'></td></tr>';
    $period='';
	// Date start
	if(empty($_REQUEST["parent_id"])) {
		$datep = ($datep ? $datep : $object->datep);
		$datef = ($datef ? $datef : $object->datef);
	}else{
		$sql = "select datep, datep2, period from llx_actioncomm where id = ".$_REQUEST["parent_id"];
		$res = $db->query($sql);
		if(!$res)
			dol_print_error($db);
		$obj = $db->fetch_object($res);
        $datep = new DateTime($obj->datep);
        $datef = new DateTime($obj->datep2);
        $period = $obj->period;
		switch($obj->period){
			case 'EveryWeek':{
                $datep = new DateTime(($datep->format('d')+7).'.'.$datep->format('m').'.'.$datep->format('Y'). ' '.$datep->format('h').':'.$datep->format('i').':'.$datep->format('s'));
                $datef = new DateTime(($datef->format('d')+7).'.'.$datef->format('m').'.'.$datef->format('Y'). ' '.$datef->format('h').':'.$datef->format('i').':'.$datef->format('s'));
			}break;
			case 'EveryMonth':{
                $datep = new DateTime($datep->format('d').'.'.($datep->format('m')+1).'.'.$datep->format('Y'). ' '.$datep->format('h').':'.$datep->format('i').':'.$datep->format('s'));
                $datef = new DateTime($datef->format('d').'.'.($datef->format('m')+1).'.'.$datef->format('Y'). ' '.$datef->format('h').':'.$datef->format('i').':'.$datef->format('s'));
			}break;
			case 'Quarterly':{
                $datep = new DateTime($datep->format('d').'.'.($datep->format('m')+3).'.'.$datep->format('Y'). ' '.$datep->format('h').':'.$datep->format('i').':'.$datep->format('s'));
                $datef = new DateTime($datef->format('d').'.'.($datef->format('m')+3).'.'.$datef->format('Y'). ' '.$datef->format('h').':'.$datef->format('i').':'.$datef->format('s'));
			}break;
			case 'Annually':{
                $datep = new DateTime($datep->format('d').'.'.$datep->format('m').'.'.($datep->format('Y')+1). ' '.$datep->format('h').':'.$datep->format('i').':'.$datep->format('s'));
                $datef = new DateTime($datef->format('d').'.'.$datef->format('m').'.'.($datef->format('Y')+1). ' '.$datef->format('h').':'.$datef->format('i').':'.$datef->format('s'));
			}break;
		}
//        var_dump($datep);
//        die();
        $datep = mktime($datep->format('h'),$datep->format('i'),$datep->format('s'),$datep->format('m'),$datep->format('d'),$datep->format('Y'));
        $datef = mktime($datef->format('h'),$datef->format('i'),$datef->format('s'),$datef->format('m'),$datef->format('d'),$datef->format('Y'));
	}
	if (GETPOST('datep','int',1)) $datep=dol_stringtotime(GETPOST('datep','int',1),0);
	print '<tr><td width="30%" class="nowrap"><span class="fieldrequired">'.$langs->trans("DateActionStart").'</span></td><td>';
	if (GETPOST("afaire") == 1) $form->select_date($datep,'ap',1,1,0,"action",1,1,0,0,'fulldayend');
	else if (GETPOST("afaire") == 2) $form->select_date($datep,'ap',1,1,1,"action",1,1,0,0,'fulldayend');
	else $form->select_date($datep,'ap',1,1,1,"action",1,1,0,0,'fulldaystart');
	print '<span style="font-size: 12px">Необхідно часу  </span><input type="text" class="param" size="2" id = "exec_time" name="exec_time"><span style="font-size: 12px"> хвилин.</span></td></tr>';

	// Date end

    if (GETPOST('datef','int',1)) $datef=dol_stringtotime(GETPOST('datef','int',1),0);
	if (empty($datef) && ! empty($datep) && ! empty($conf->global->AGENDA_AUTOSET_END_DATE_WITH_DELTA_HOURS))
	{
		$datef=dol_time_plus_duree($datep, $conf->global->AGENDA_AUTOSET_END_DATE_WITH_DELTA_HOURS, 'h');
	}
	print '<tr><td><span id="dateend"'.(GETPOST("actioncode") == 'AC_RDV'?' class="fieldrequired"':'').'>'.$langs->trans("DateActionEnd").'</span></td><td>';
	if (GETPOST("afaire") == 1) $form->select_date($datef,'p2',1,1,1,"action",1,1,0,0,'fulldayend');
	else if (GETPOST("afaire") == 2) $form->select_date($datef,'p2',1,1,1,"action",1,1,0,0,'fulldayend');
	else $form->select_date($datef,'p2',1,1,1,"action",1,1,0,0,'fulldayend');
	print '</td></tr>';

	// Status
	print '<tr><td width="10%">'.$langs->trans("Status").'</td>';
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

	if (in_array($user->id,array_keys($listofuserid))) print $langs->trans("MyAvailability").': <input id="transparency" type="checkbox" name="transparency"'.(((! isset($_GET['transparency']) && ! isset($_POST['transparency'])) || GETPOST('transparency'))?' checked="checked"':'').'> '.$langs->trans("Busy");
	print '</td></tr>';



	// Realised by
	if (! empty($conf->global->AGENDA_ENABLE_DONEBY))
	{
		print '<tr><td class="nowrap">'.$langs->trans("ActionDoneBy").'</td><td>';
		print $form->select_dolusers(GETPOST("doneby")?GETPOST("doneby"):(! empty($object->userdoneid) && $percent==100?$object->userdoneid:0),'doneby',1);
		print '</td></tr>';
	}
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
	    // Period
//    if (GETPOST("actioncode") == "AC_GLOBAL")
//    {
//    var_dump((GETPOST("actioncode") != "AC_GLOBAL"));
//        die(GETPOST("actioncode"));
		print '<tr id="period"><td>'.$langs->trans("Period").'</td><td colspan="3">'.$form->select_period('selperiod', empty(GETPOST('selperiod'))?$period:GETPOST('selperiod')).'</td></tr>';
//    }
		print '<tr><td class="nowrap">Попередньо виконати до</td><td colspan="3">';
		$form->select_date($datep?$datep:$object->datep,'preperform',0,0,0,"action",1,0,0,0,'fulldaystart');
		print '</td></tr>';
	print '</table>';
	print '<br><br>';
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

    // ActionDescription
    print '<tr><td valign="top">'.$langs->trans("ActionDescription").'</td><td>';
    require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
    $doleditor=new DolEditor('note',(GETPOST('note')?GETPOST('note'):$object->note),'',180,'dolibarr_notes','In',true,true,$conf->fckeditor->enabled,ROWS_6,90);
    $doleditor->Create();
    print '</td></tr>';

    // Note
    print '<tr><td valign="top">'.$langs->trans("Note").': що зробить, кінцева мета, підтвердження</td><td>';
//    print '<script>
//        $(function(){
//                $("#confirmdoc").autocomplete({
//                //Определяем обратный вызов к результатам форматирования
//                source: function(req, add){
//
//                    //Передаём запрос на сервер
//                    $.getJSON("autocomplete.php?callback=?", req, function(data) {
//                        if(data == null){
//                            $("#confirmdoc").val(req["term"]);
//        //                    console.log($("#confirmdoc").val());
//                            add(null);
//                            return;
//                        }
//                        //Создаем массив для объектов ответа
//                        var suggestions = [];
//                        //Обрабатываем ответ
//                        $.each(data, function(i, val){
//                            suggestions.push(val.name);
//                        });
//
//                        //Передаем массив обратному вызову
//                        add(suggestions);
//                    });
//                },
//
//                //Определяем обработчик селектора
//                select: function(e, ui) {
//                    $("#confirmdoc").value = ui.item.value;
//                    console.log($("#confirmdoc").val());
//                    $(".ui-helper-hidden-accessible").remove();
//    //                        //Создаем форматированную переменную cust_name
//    //                        var cust_name = ui.item.value,
//    //                                        span = $("<span>").text(cust_name),
//    //                                        a = $("<a>").addClass("remove").attr({
//    //                                            href: "javascript:",
//    //                                            title: "Remove " + cust_name
//    //                                        }).text("x").appendTo(span);
//    //
//    //                                    //Добавляем cust_name к div cust_name
//    //                                    span.insertBefore("#confirmdoc");
//                },
//
//                //Определяем обработчик выбора
//                change: function() {
//                    //Сохраняем поле "Наименование" без изменений и в правильной позиции
//    //                        $("#confirmdoc").val("").css("top", 2);
//                }
//            });
//        });
//		</script>
//    ';
//    print '<input type="text" id="confirmdoc" name="confirmdoc" size="50" value="">
//        <img class="hideonsmartphone" border="0" title="" alt="" src="/dolibarr/htdocs/theme/eldy/img/info.png">';
    print $form->select_confirmdoc();
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

// View or edit
if ($id > 0 )
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
	if ($action == 'edit')
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

        // Full day event
        print '<tr><td>'.$langs->trans("EventOnFullDay").'</td><td colspan="3"><input type="checkbox" id="fullday" name="fullday" '.($object->fulldayevent?' checked="checked"':'').'></td></tr>';

		// Date start
		print '<tr><td class="nowrap"><span class="fieldrequired">'.$langs->trans("DateActionStart").'</span></td><td colspan="3">';
		if (GETPOST("afaire") == 1) $form->select_date($datep?$datep:$object->datep,'ap',1,1,0,"action",1,1,0,0,'fulldaystart');
		else if (GETPOST("afaire") == 2) $form->select_date($datep?$datep:$object->datep,'ap',1,1,1,"action",1,1,0,0,'fulldaystart');
		else $form->select_date($datep?$datep:$object->datep,'ap',1,1,1,"action",1,1,0,0,'fulldaystart');
		print '</td></tr>';
		// Date end
		print '<tr><td>'.$langs->trans("DateActionEnd").'</td><td colspan="3">';
		if (GETPOST("afaire") == 1) $form->select_date($datef?$datef:$object->datef,'p2',1,1,1,"action",1,1,0,0,'fulldayend');
		else if (GETPOST("afaire") == 2) $form->select_date($datef?$datef:$object->datef,'p2',1,1,1,"action",1,1,0,0,'fulldayend');
		else $form->select_date($datef?$datef:$object->datef,'p2',1,1,1,"action",1,1,0,0,'fulldayend');
		print '</td></tr>';

		// Status
		print '<tr><td class="nowrap">'.$langs->trans("Status").' / '.$langs->trans("Percentage").'</td><td colspan="3">';
		$percent=GETPOST("percentage")?GETPOST("percentage"):$object->percentage;
		if(isset($_REQUEST["duplicate_action"]))
			$percent = -1;
		$formactions->form_select_status_action('formaction',$percent,1);
		print '</td></tr>';
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
        // Location
	    if (empty($conf->global->AGENDA_DISABLE_LOCATION))
	    {
			print '<tr><td>'.$langs->trans("Location").'</td><td colspan="3"><input type="text" name="location" size="50" value="'.$object->location.'"></td></tr>';
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
		if (in_array($user->id,array_keys($listofuserid))) print $langs->trans("MyAvailability").':  <input id="transparency" type="checkbox" name="transparency"'.($listofuserid[$user->id]['transparency']?' checked="checked"':'').'">'.$langs->trans("Busy");
		print '</td></tr>';

		// Realised by
		if (! empty($conf->global->AGENDA_ENABLE_DONEBY))
		{
			print '<tr><td class="nowrap">'.$langs->trans("ActionDoneBy").'</td><td colspan="3">';
			print $form->select_dolusers($object->userdoneid> 0?$object->userdoneid:-1,'doneby',1);
			print '</td></tr>';
		}
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
	print '</td></tr>';
//		var_dump($datepreperform);
//		die();
		print '<tr><td class="nowrap">Попередньо виконати до</td><td colspan="3">';
		$form->select_date($datepreperform?$datepreperform->format('Y-m-d'):$object->datepreperform->format('Y-m-d'),'preperform',0,0,0,"action",1,0,0,0,'fulldaystart');
		print '</td></tr>';
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

        // Description
        print '<tr><td valign="top">'.$langs->trans("Description").'</td><td colspan="3">';
        // Editeur wysiwyg
        require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
        $doleditor=new DolEditor('note',$object->note,'',240,'dolibarr_notes','In',true,true,$conf->fckeditor->enabled,ROWS_5,90);
        $doleditor->Create();
        print '</td></tr>';

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
	else
	{
		dol_fiche_head($head, 'card', $langs->trans("Action"),0,'action');

		// Affichage fiche action en mode visu
		print '<table class="border" width="100%">';

		$linkback = '<a href="'.DOL_URL_ROOT.'/comm/action/listactions.php">'.$langs->trans("BackToList").'</a>';

		// Ref
		print '<tr><td width="30%">'.$langs->trans("Ref").'</td><td colspan="3">';
		print $form->showrefnav($object, 'id', $linkback, ($user->societe_id?0:1), 'id', 'ref', '');
		print '</td></tr>';

		// Type
		if (! empty($conf->global->AGENDA_USE_EVENT_TYPE))
		{
			print '<tr><td>'.$langs->trans("Type").'1</td><td colspan="3">'.$object->type.'</td></tr>';
		}

//		// Title
//		print '<tr><td>'.$langs->trans("Title").'</td><td colspan="3">'.$object->label.'</td></tr>';

        // Full day event
        print '<tr><td>'.$langs->trans("EventOnFullDay").'</td><td colspan="3">'.yn($object->fulldayevent).'</td></tr>';

		$rowspan=4;
		if (empty($conf->global->AGENDA_DISABLE_LOCATION)) $rowspan++;

		// Date start
		print '<tr><td width="30%">'.$langs->trans("DateActionStart").'</td><td colspan="3">';
		if (! $object->fulldayevent) print dol_print_date($object->datep,'dayhour');
		else print dol_print_date($object->datep,'day');
		if ($object->percentage == 0 && $object->datep && $object->datep < ($now - $delay_warning)) print img_warning($langs->trans("Late"));
		print '</td>';
		print '</tr>';

		// Date end
		print '<tr><td>'.$langs->trans("DateActionEnd").'</td><td colspan="3">';
        if (! $object->fulldayevent) print dol_print_date($object->datef,'dayhour');
		else print dol_print_date($object->datef,'day');
		if ($object->percentage > 0 && $object->percentage < 100 && $object->datef && $object->datef < ($now- $delay_warning)) print img_warning($langs->trans("Late"));
		print '</td></tr>';

		// Status
		print '<tr><td class="nowrap">'.$langs->trans("Status").' / '.$langs->trans("Percentage").'</td><td colspan="3">';
		print $object->getLibStatut(4);
		print '</td></tr>';

        // Location
	    if (empty($conf->global->AGENDA_DISABLE_LOCATION))
    	{
			print '<tr><td>'.$langs->trans("Location").'</td><td colspan="3">'.$object->location.'</td></tr>';
    	}

		// Assigned to
    	print '<tr><td width="30%" class="nowrap">'.$langs->trans("ActionAssignedTo").'</td><td colspan="3">';
		$listofuserid=array();
		if (empty($donotclearsession))
		{
			if ($object->userownerid > 0) $listofuserid[$object->userownerid]=array('id'=>$object->userownerid,'transparency'=>$object->transparency);	// Owner first
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
		print $form->select_dolusers_forevent('view','assignedtouser',1);
		if (in_array($user->id,array_keys($listofuserid))) print $langs->trans("MyAvailability").': '.(($object->userassigned[$user->id]['transparency'] > 0)?$langs->trans("Busy"):$langs->trans("Available"));	// We show nothing if event is assigned to nobody
		print '	</td></tr>';

		// Done by
		if ($conf->global->AGENDA_ENABLE_DONEBY)
		{
			print '<tr><td class="nowrap">'.$langs->trans("ActionDoneBy").'</td><td colspan="3">';
			if ($object->userdoneid > 0)
			{
				$tmpuser=new User($db);
				$tmpuser->fetch($object->userdoneid);
				print $tmpuser->getNomUrl(1);
			}
			print '</td></tr>';
		}

		print '</table>';

		print '<br><br>';

		print '<table class="border" width="100%">';

		// Third party - Contact
		if ($conf->societe->enabled)
		{
			print '<tr><td width="30%">'.$langs->trans("ActionOnCompany").'</td><td>'.($object->thirdparty->id?$object->thirdparty->getNomUrl(1):$langs->trans("None"));
			if (is_object($object->thirdparty) && $object->thirdparty->id > 0 && $object->type_code == 'AC_TEL')
			{
				if ($object->thirdparty->fetch($object->thirdparty->id))
				{
					print "<br>".dol_print_phone($object->thirdparty->phone);
				}
			}
			print '</td>';
			print '<td>'.$langs->trans("Contact").'</td>';
			print '<td>';
			if ($object->contactid > 0)
			{
				print $object->contact->getNomUrl(1);
				if ($object->contactid && $object->type_code == 'AC_TEL')
				{
					if ($object->contact->fetch($object->contactid))
					{
						print "<br>".dol_print_phone($object->contact->phone_pro);
					}
				}
			}
			else
			{
				print $langs->trans("None");
			}
			print '</td></tr>';
		}

		// Project
		if (! empty($conf->projet->enabled))
		{
			print '<tr><td width="30%" valign="top">'.$langs->trans("Project").'</td><td colspan="3">';
			if ($object->fk_project)
			{
				$project=new Project($db);
				$project->fetch($object->fk_project);
				print $project->getNomUrl(1,'',1);
			}
			print '</td></tr>';
		}

		// Priority
		print '<tr><td nowrap width="30%">'.$langs->trans("Priority").'</td><td colspan="3">';
		print ($object->priority?$object->priority:'');
		print '</td></tr>';

		// Object linked
		if (! empty($object->fk_element) && ! empty($object->elementtype))
		{
			include_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
			print '<tr><td>'.$langs->trans("LinkedObject").'</td>';
			print '<td colspan="3">'.dolGetElementUrl($object->fk_element,$object->elementtype,1).'</td></tr>';
		}

		// Description
		print '<tr><td valign="top">'.$langs->trans("Description").'</td><td colspan="3">';
		print dol_htmlentitiesbr($object->note);
		print '</td></tr>';

        // Other attributes
		$parameters=array('colspan'=>' colspan="3"', 'colspanvalue'=>'3', 'id'=>$object->id);
        $reshook=$hookmanager->executeHooks('formObjectOptions',$parameters,$object,$action);    // Note that $action and $object may have been modified by hook

		print '</table>';

		//Extra field
		if (empty($reshook) && ! empty($extrafields->attribute_label))
		{
			print '<br><br><table class="border" width="100%">';
			foreach($extrafields->attribute_label as $key=>$label)
			{
				$value=(isset($_POST["options_".$key])?$_POST["options_".$key]:(isset($object->array_options['options_'.$key])?$object->array_options['options_'.$key]:''));
				print '<tr><td width="30%">'.$label.'</td><td>';
				print $extrafields->showOutputField($key,$value);
				print "</td></tr>\n";
			}
			print '</table>';
		}

		dol_fiche_end();
	}


	/*
	 * Barre d'actions
	 */

	print '<div class="tabsAction">';

	$parameters=array();
	$reshook=$hookmanager->executeHooks('addMoreActionsButtons',$parameters,$object,$action);    // Note that $action and $object may have been modified by hook
	if (empty($reshook))
	{
		if ($action != 'edit')
		{
			if ($user->rights->agenda->allactions->create ||
			   (($object->authorid == $user->id || $object->userownerid == $user->id) && $user->rights->agenda->myactions->create))
			{
				print '<div class="inline-block divButAction"><a class="butAction" href="card.php?action=edit&id='.$object->id.'">'.$langs->trans("Modify").'</a></div>';
			}
			else
			{
				print '<div class="inline-block divButAction"><a class="butActionRefused" href="#" title="'.$langs->trans("NotAllowed").'">'.$langs->trans("Modify").'</a></div>';
			}

			if ($user->rights->agenda->allactions->delete ||
			   (($object->authorid == $user->id || $object->userownerid == $user->id) && $user->rights->agenda->myactions->delete))
			{
				print '<div class="inline-block divButAction"><a class="butActionDelete" href="card.php?action=delete&id='.$object->id.'">'.$langs->trans("Delete").'</a></div>';
			}
			else
			{
				print '<div class="inline-block divButAction"><a class="butActionRefused" href="#" title="'.$langs->trans("NotAllowed").'">'.$langs->trans("Delete").'</a></div>';
			}
		}
	}

	print '</div>';

	if ($action != 'edit')
	{
		// Link to agenda views
		print '<div id="agendaviewbutton">';
		print '<form name="listactionsfiltermonth" action="'.DOL_URL_ROOT.'/comm/action/index.php" method="POST" style="float: left; padding-right: 10px;">';
		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		print '<input type="hidden" name="action" value="show_month">';
		print '<input type="hidden" name="year" value="'.dol_print_date($object->datep,'%Y').'">';
		print '<input type="hidden" name="month" value="'.dol_print_date($object->datep,'%m').'">';
		print '<input type="hidden" name="day" value="'.dol_print_date($object->datep,'%d').'">';
		//print '<input type="hidden" name="day" value="'.dol_print_date($object->datep,'%d').'">';
		print img_picto($langs->trans("ViewCal"),'object_calendar','class="hideonsmartphone"').' <input type="submit" style="min-width: 120px" class="button" name="viewcal" value="'.$langs->trans("ViewCal").'">';
		print '</form>'."\n";
		print '<form name="listactionsfilterweek" action="'.DOL_URL_ROOT.'/comm/action/index.php" method="POST" style="float: left; padding-right: 10px;">';
		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		print '<input type="hidden" name="action" value="show_week">';
		print '<input type="hidden" name="year" value="'.dol_print_date($object->datep,'%Y').'">';
		print '<input type="hidden" name="month" value="'.dol_print_date($object->datep,'%m').'">';
		print '<input type="hidden" name="day" value="'.dol_print_date($object->datep,'%d').'">';
		//print '<input type="hidden" name="day" value="'.dol_print_date($object->datep,'%d').'">';
		print img_picto($langs->trans("ViewCal"),'object_calendarweek','class="hideonsmartphone"').' <input type="submit" style="min-width: 120px" class="button" name="viewweek" value="'.$langs->trans("ViewWeek").'">';
		print '</form>'."\n";
		print '<form name="listactionsfilterday" action="'.DOL_URL_ROOT.'/comm/action/index.php" method="POST" style="float: left; padding-right: 10px;">';
		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		print '<input type="hidden" name="action" value="show_day">';
		print '<input type="hidden" name="year" value="'.dol_print_date($object->datep,'%Y').'">';
		print '<input type="hidden" name="month" value="'.dol_print_date($object->datep,'%m').'">';
		print '<input type="hidden" name="day" value="'.dol_print_date($object->datep,'%d').'">';
		//print '<input type="hidden" name="day" value="'.dol_print_date($object->datep,'%d').'">';
		print img_picto($langs->trans("ViewCal"),'object_calendarday','class="hideonsmartphone"').' <input type="submit" style="min-width: 120px" class="button" name="viewday" value="'.$langs->trans("ViewDay").'">';
		print '</form>'."\n";
		print '<form name="listactionsfilterperuser" action="'.DOL_URL_ROOT.'/comm/action/peruser.php" method="POST" style="float: left; padding-right: 10px;">';
		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		print '<input type="hidden" name="action" value="show_peruser">';
		print '<input type="hidden" name="year" value="'.dol_print_date($object->datep,'%Y').'">';
		print '<input type="hidden" name="month" value="'.dol_print_date($object->datep,'%m').'">';
		print '<input type="hidden" name="day" value="'.dol_print_date($object->datep,'%d').'">';
		//print '<input type="hidden" name="day" value="'.dol_print_date($object->datep,'%d').'">';
		print img_picto($langs->trans("ViewCal"),'object_calendarperuser','class="hideonsmartphone"').' <input type="submit" style="min-width: 120px" class="button" name="viewperuser" value="'.$langs->trans("ViewPerUser").'">';
		print '</form>'."\n";
		print '</div>';

		if (empty($conf->global->AGENDA_DISABLE_BUILDDOC))
		{
			print '<div style="clear:both;">&nbsp;<br><br></div><div class="fichecenter"><div class="fichehalfleft">';
            print '<a name="builddoc"></a>'; // ancre

            /*
             * Documents generes
             */

            $filedir=$conf->agenda->multidir_output[$conf->entity].'/'.$object->id;
            $urlsource=$_SERVER["PHP_SELF"]."?socid=".$object->id;

            $genallowed=$user->rights->agenda->myactions->create;
	        $delallowed=$user->rights->agenda->myactions->delete;

            $var=true;

            $somethingshown=$formfile->show_documents('agenda',$object->id,$filedir,$urlsource,$genallowed,$delallowed,'',0,0,0,0,0,'','','',$object->default_lang);

			print '</div><div class="fichehalfright"><div class="ficheaddleft">';


			print '</div></div></div>';

            print '<div style="clear:both;">&nbsp;</div>';
	    }
	}
}