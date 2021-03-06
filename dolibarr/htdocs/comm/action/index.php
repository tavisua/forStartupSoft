<?php
/* Copyright (C) 2001-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2003      Eric Seigne          <erics@rycks.com>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2011      Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2014      Cedric GROSS         <c.gross@kreiz-it.fr>
 * Copyright (C) 2015      Marcos García        <marcosgdf@gmail.com>
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
 *  \file       htdocs/comm/action/index.php
 *  \ingroup    agenda
 *  \brief      Home page of calendar events
 */
if($_REQUEST['action'] == 'birthday_remainder')
    define("NOLOGIN",1);
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
if($_REQUEST['action']=='get_actiondate'){
    $event = new ActionComm($db);
    echo $event->getFilterDate();
    exit();
}
if($_REQUEST['action']=='find_product'){

    exit();
}
if($_REQUEST['action']=='clearFilter'){
//    echo '<pre>';
//    var_dump($_REQUEST);
//    echo '</pre>';
//    die();
    exit();
}
if($_REQUEST['action']=='fixDnepr'){
    global $db;
    @set_time_limit(0);
    $sql = "select llx_actioncomm.id, fk_user_author, fk_user_action from llx_societe
        inner join `llx_societe_action` on `llx_societe_action`.`socid` = llx_societe.rowid
        inner join llx_actioncomm on llx_actioncomm.id = llx_societe_action.action_id
        where state_id = 3";
    $actions = [];
    $local_res = $db->query($sql);
    if(!$local_res)
        dol_print_error($db);
    while($obj = $db->fetch_object($local_res)){
        $actions[$obj->id]=[$obj->fk_user_author, $obj->fk_user_action];
    }
    $db_remout=getDoliDBInstance('mysqli', '185.67.1.101', 'vopimwkk_admin', 'C~~KiE3cDThX', 'vopimwkk_uspex2015', '3306');
    $remout_actions = [];
    $remout_res = $db_remout->query($sql);
    if(!$remout_res)
        dol_print_error($db_remout);
    while($obj = $db->fetch_object($remout_res)){
        $remout_actions[$obj->id]=[$obj->fk_user_author, $obj->fk_user_action];
    }
    $out = [];
    foreach ($actions as $key=>$value){
        if(!isset($remout_actions[$key]))
            $out[$key]=$value;
        else{
            if($remout_actions[$key][0]!=$value[0]||$remout_actions[$key][1]!=$value[1]){

                $out[$key]=$value;
            }
        }
    }
    foreach ($out as $key=>$value) {
        print implode('=>', $value).' / '.$key.'</br>';
        $sql = 'update llx_actioncomm set fk_user_author = '.$value[0].', fk_user_action = '.$value[1].' where id = '.$key;
        $remout_res = $db_remout->query($sql);
        if(!$remout_res)
            dol_print_error($db_remout);
    }
//    echo '<pre>';
//    var_dump($out);
//    echo '</pre>';

    exit();
}


require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/agenda.lib.php';
$now=dol_now();
$nowarray=dol_getdate($now);
$nowyear=$nowarray['year'];
$nowmonth=$nowarray['mon'];
$nowday=$nowarray['mday'];

if($_REQUEST['action'] == 'birthday_remainder'){
    global $user,$db;
    define("NOLOGIN",1);		// Не потрібно залогіниватись, якщо створюються автоматичні завдання по нагадуванню про день народження
    $responsibility = [];
    $sql = "select `llx_user_rights`.fk_user from `llx_user_rights` inner join llx_user on llx_user.rowid = `llx_user_rights`.fk_user where fk_id = 126 and llx_user.active = 1";
    $res = $db->query($sql);
    $usersID = [];
    while($obj = $db->fetch_object($res))
        $usersID[]=$obj->fk_user;

    $sql = "select fx_responsibility, fx_category_counterparty from  `responsibility_param`
        where fx_category_counterparty  in (5/*,7,8,9,10*/)";
    $res = $db->query($sql);
    while($obj = $db->fetch_object($res)){
        $responsibility[$obj->fx_category_counterparty][] = $obj->fx_responsibility;
    }
    $sql = "select  socid, `llx_societe_contact`.rowid as contact_id, `llx_societe_contact`.lastname, `llx_societe_contact`.firstname, birthdaydate, llx_societe.region_id, categoryofcustomer_id from `llx_societe_contact`
        inner join `llx_societe` on `llx_societe`.`rowid` = `llx_societe_contact`.`socid`
        where birthdaydate is not null
        and send_birthdaydate = 1
        and date(concat(date_format(now(), '%Y-'), date_format(date_add(birthdaydate, interval -10 day), '%m-%d'))) = date(".(!empty($_REQUEST['dates_action'])?"'".$_REQUEST['dates_action']."'":"now()").")
        and `categoryofcustomer_id` in (5/*,7,8,9,10*/)";
//    var_dump($sql);
//    die();
    $res = $db->query($sql);
    $i = 0;
    while($obj = $db->fetch_object($res)){
        switch ($obj->categoryofcustomer_id){
            case 5:{//Клієнти
                $user_congratulator = new User($db);
                $i = 0;
                require_once DOL_DOCUMENT_ROOT . '/comm/action/class/actioncomm.class.php';

                //Нагадування для маркетингу
                foreach ($usersID as $item) {
                    $date = new DateTime();
                    $user_congratulator->fetch($item);
                    $action = new ActionComm($db);
                    $exec_minuted = $action->GetExecTime('AC_CURRENT');
                    $freetime = $action->GetFreeTime(date('Y-m-d'), $item, $exec_minuted, 0);
                    $date = new DateTime($freetime);
                    $action->datep = mktime($date->format('h'), $date->format('i'), $date->format('s'), $date->format('m'), $date->format('d'), $date->format('Y'));
                    $action->datef = $action->datep + $exec_minuted * 60;
                    $action->type_code = 'AC_CURRENT';
                    $action->label = "Поздоровити з днем народження";
                    $action->period = 0;
                    $action->groupoftask = 1;
                    $action->socid = $obj->socid;
                    $action->contactid = $obj->contact_id;
                    $action->percentage = -1;
                    $action->priority = 0;
                    $action->authorid = $user_congratulator->id;
                    $action->note = "Поздоровити з днем народження";
                    $action->userassigned[] = array("id" => $user_congratulator->id, "transparency" => 1);
                    $action->userownerid = 1;
                    $action->fk_element = "";
                    $action->elementtype = "";
                    $action->add($user_congratulator);
                    echo $item.' маркетинг</br>';
                }
                $id_usr = getIDCongratulatorOnRegionID($obj->region_id, $responsibility[$obj->categoryofcustomer_id]);
                if($id_usr) {
                    $user_congratulator->fetch($id_usr);
                    $datebirth = new DateTime($obj->birthdaydate);
                    //завдання для торгівельного уточнити адресу
                    $date = new DateTime();
                    while (in_array($date->format('w'), [0,6])){
                        $date->add(new DateInterval('P1D'));

                    }
                    $remainder = new ActionComm($db);
                    $exec_minuted = $remainder->GetExecTime('AC_TEL');
                    $freetime = $remainder->GetFreeTime($nowyear . '-' . $date->format('m') . '-' . $date->format('d'), $id_usr, $exec_minuted, 0);
                    $date = new DateTime($remainder->GetFreeTime($nowyear . '-' . $date->format('m') . '-' . $date->format('d') . ' 8:0:0', $id_usr, $exec_minuted));
                    $datep = $date->getTimestamp();
                    $datef = $datep + $exec_minuted * 60;
                    $remainder->priority = 0;
                    $remainder->userownerid = $id_usr;
                    $remainder->fulldayevent = 0;
                    $remainder->typenotification = 'system';
                    $remainder->period = 0;
                    $remainder->groupoftask = 1;
                    $remainder->authorid = $id_usr;
                    $remainder->type_code = 'AC_TEL';
                    $remainder->percentage = 0;
                    $remainder->label = "Уточнити адресу до дня народження";
                    $remainder->typeSetOfDate = 'w';
                    $remainder->fk_project = 0;
                    $remainder->userassigned[] = array("id" => $id_usr, "transparency" => 1);
                    $remainder->datep = $datep;
                    $remainder->datef = $datef;
                    $remainder->socid = $obj->socid;
                    $remainder->contactid = $obj->contact_id;
                    $remainder->note = "Уточнити адресу до дня народження";
                    $remainder->add($user_congratulator, 'ondatep');
                    echo $remainder->id.'</br>';

                    //Нагадування для торгівельних агентів
                    foreach ([$datebirth->getTimestamp()] as $date) {
                        echo $id_usr.'</br>';
                        $date = dol_getdate($date + 7200);

                        $remainder = new ActionComm($db);
                        $exec_minuted = $remainder->GetExecTime('AC_TEL');
                        $freetime = $remainder->GetFreeTime($nowyear . '-' . $datebirth->format('m') . '-' . $datebirth->format('d'), $id_usr, $exec_minuted, 0);
                        $datep = (new DateTime($freetime))->getTimestamp();
                        $datef = $datep + $exec_minuted * 60;

                        $remainder->priority = 0;
                        $remainder->userownerid = $id_usr;
                        $remainder->fulldayevent = 0;
                        $remainder->typenotification = 'system';
                        $remainder->period = 0;
                        $remainder->groupoftask = 1;
                        $remainder->authorid = $id_usr;
                        $remainder->type_code = 'AC_TEL';
                        $remainder->percentage = 0;
                        $remainder->label = "Поздоровлення з днем народження";
                        $remainder->typeSetOfDate = 'w';
                        $remainder->fk_project = 0;
                        $remainder->userassigned[] = array("id" => $id_usr, "transparency" => 1);
//                    $datef = dol_mktime('10', '10', 0, $date['mon'], $date['mday'], $nowyear);
//$datef=dol_mktime($fulldayevent?'23':GETPOST("p2hour"), $fulldayevent?'59':GETPOST("p2min"), $fulldayevent?'59':'0', GETPOST("p2month"), GETPOST("p2day"), GETPOST("p2year"));
//                    $datep = dol_mktime('10', '00', 0, $date['mon'], $date['mday'], $nowyear);
                        $remainder->datep = $datep;
                        $remainder->datef = $datef;
                        $remainder->socid = $obj->socid;
                        $remainder->contactid = $obj->contact_id;
                        $remainder->icon = 'birthday.png';
//                $remainder->datepreperform = $dateprep;
                        $remainder->note = "Поздоровити " . $datebirth->format('d.m.') . " $obj->lastname $obj->firstname з днем народження";
                        $remainder->add($user_congratulator, 'ondatep');
                        echo $remainder->id.'</br>';

//                        echo '<pre>';
//                        var_dump($remainder);
//                        echo '</pre>';
//                        die();
                    }
                    //Завдання для маркетингу відправити вітальну листівку
                    $date = new DateTime();
                    $date->add(new DateInterval('P3D'));
                    while (in_array($date->format('w'), [0,6])){
                        $date->add(new DateInterval('P1D'));

                    }
                    foreach ($usersID as $item) {
                        $user_congratulator->fetch($item);
                        $remainder = new ActionComm($db);
                        $exec_minuted = $remainder->GetExecTime('AC_CURRENT');
                        $freetime = $remainder->GetFreeTime($nowyear . '-' . $date->format('m') . '-' . $date->format('d'), $id_usr, $exec_minuted, 0);
                        $date = new DateTime($remainder->GetFreeTime($nowyear . '-' . $date->format('m') . '-' . $date->format('d') . ' 8:0:0', $id_usr, $exec_minuted));
                        $datep = $date->getTimestamp();
                        $datef = $datep + $exec_minuted * 60;
                        $remainder->priority = 0;
                        $remainder->userownerid = $id_usr;
                        $remainder->fulldayevent = 0;
                        $remainder->typenotification = 'system';
                        $remainder->period = 0;
                        $remainder->groupoftask = 1;
                        $remainder->authorid = $user_congratulator->id;
                        $remainder->type_code = 'AC_CURRENT';
                        $remainder->percentage = 0;
                        $remainder->label = "Відправити вітальну листівку";
                        $remainder->typeSetOfDate = 'w';
                        $remainder->fk_project = 0;
                        $remainder->userassigned[] = array("id" => $id_usr, "transparency" => 1);
                        $remainder->datep = $datep;
                        $remainder->datef = $datef;
                        $remainder->socid = $obj->socid;
                        $remainder->contactid = $obj->contact_id;
                        $remainder->note = "Відправити вітальну листівку з днем народження для клієнта $obj->lastname $obj->firstname";
                        $remainder->add($user_congratulator, 'ondatep');
                        echo $remainder->id.'</br>';
                        break;
                    }
                }
                else{
                    echo 'not TA ='.$obj->socid.'</br>';
                }
            }break;
        }
    }
    die('1');
}
if($_REQUEST['action'] == 'getActionsNote'){//Вертає суть завдання
    $sql = "select trim(note) note from llx_actioncomm where id = ".$_REQUEST['action_id'];
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    print $obj->note;
    exit();
}
if($_REQUEST['action'] == 'findProduct'){
    global $db_price;
    echo '<pre>';
    var_dump($db_price);
    echo '</pre>';
    die();
}
if($_REQUEST['action'] == 'getAction'){
    global $db;
    $action = new ActionComm($db);
    $action->fetch($_REQUEST["actionid"]);
    print json_encode($action);
    exit();
}
if($_REQUEST['action'] == 'getAutoCallStatus') {
    if(empty($_SESSION['AutoCallStatus']))
        print '0';
    else
        print '1';
    exit();
}
if($_REQUEST['action'] == 'setAutoCallStatus') {
    $_SESSION['AutoCallStatus']=$_REQUEST['status'];
    print 'success';
    exit();
}
if($_REQUEST['action'] == 'getNotInterestingForm'){
    $out='<table class="scrolling-table" style="background: #ffffff; width: 100%">
            <input type="hidden" id="actionid" name="actionid" value="'.$LastActionID.'">
            <thead><tr class="multiple_header_table"><th class="middle_size" colspan="9" style="width: 100%">Суть пропозиції для посади </th>
            <a class="close" style="margin-left: -160px" onclick="ClosePopupMenu($(this));" title="Закрити"></a>
                </tr>
                </thead>
            <tbody  id="bodyProposition">';
    print $out;
    if(!empty($_REQUEST['title']))
        $said = $_REQUEST['title'];
    $object = new ArrayObject();
    if(!empty($_REQUEST['need'])) {
        $object->answer = $_REQUEST['need'];
    }
    $object->result_of_action = '';
    foreach ($_REQUEST["needs_array"] as $value) {
        if(!empty($object->result_of_action))
            $object->result_of_action.='; ';
        $object->result_of_action.=$value;
    }
    $object->need = $object->result_of_action;
    require_once DOL_DOCUMENT_ROOT.'/theme/eldy/responsibility/sale/not_interesting_form.html';
    exit();
}
if($_REQUEST['action'] == 'getInterestingForm'){
    $out='<table class="scrolling-table" style="background: #ffffff; width: auto">
            <input type="hidden" id="actionid" name="actionid" value="'.$LastActionID.'">
            <thead><tr class="multiple_header_table"><th class="middle_size" colspan="9" style="width: 100%">Суть пропозиції для посади </th>
            <a class="close" style="margin-left: -160px" onclick="ClosePopupMenu($(this));" title="Закрити"></a>
                </tr>
                </thead>
            <tbody  id="bodyProposition">';
    print $out;
    require_once DOL_DOCUMENT_ROOT.'/theme/eldy/responsibility/sale/not_interesting_form.html';
    exit();
}
if(in_array($_REQUEST['action'],  ['SaveResultAction','setNotInterestingProposed'])){
//    require_once $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/comm/action/result_action.php';
    if(empty($_REQUEST["actionid"])){
        $autoaction = new ActionComm($db);
        $_REQUEST["actionid"] = $autoaction->GetNotExecActionsID($_REQUEST['socid'],$_REQUEST['contactid'],null)[0];
    }
    save_resultaction($_REQUEST['rowid']);
    exit();
}
if($_REQUEST['action'] == 'autoCreateAction') {
    if(empty($_SESSION['autocall_id']))
        $_SESSION['autocall_id'] = [];
    $_SESSION['autocall_id'] = [];
    if(!in_array($_REQUEST["actionid"], $_SESSION['autocall_id'])) {
        $_SESSION['autocall_id'][]=$_REQUEST["actionid"];
        if(!$_REQUEST['onlymark']) {
            global $user;
            $autoaction = new ActionComm($db);
            $autoaction->fetch($_REQUEST["actionid"]);
            $date = new DateTime(date('Y-m-d 8:0:0', $autoaction->datep));
            $dirID = array(13, 18, 19, 27, 31, 36, 37, 41);//Директори
            $sql = "select post_id from llx_societe_contact where rowid = " . $autoaction->contactid;
            $res = $db->query($sql);
            $obj = $db->fetch_object($res);
            if (in_array($obj->post_id, $dirID))
                $date->add(new DateInterval('P10D'));
            else
                $date->add(new DateInterval('P7D'));
            if(in_array($date->format('N'), array(6,7))){//На перший робочий день
                $date->add(new DateInterval('P'.(8-$date->format('N')).'D'));
            }
            $exec_minuted = ($autoaction->datef - $autoaction->datep) / 60;
            $freetime = $autoaction->GetFreeTime($date->format("Y-m-d"), $user->id, $exec_minuted, 0);
            $date = new DateTime($freetime);
            $autoaction->datep = mktime($date->format('H'), $date->format('i'), $date->format('s'), $date->format('m'), $date->format('d'), $date->format('Y'));
            $autoaction->datef = $autoaction->datep + $exec_minuted * 60;
            $autoaction->percentage = -1;
            $autoaction->typeSetOfDate = 'a';
            $autoaction->add($user);
        }
    }
    echo $autoaction->id;
    exit();
}
if (! empty($conf->projet->enabled)) {
	require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
}

if (! isset($conf->global->AGENDA_MAX_EVENTS_DAY_VIEW)) $conf->global->AGENDA_MAX_EVENTS_DAY_VIEW=3;

if (empty($conf->global->AGENDA_EXT_NB)) $conf->global->AGENDA_EXT_NB=5;
$MAXAGENDA=$conf->global->AGENDA_EXT_NB;

$filter=GETPOST("filter",'',3);
$filtert = GETPOST("usertodo","int",3)?GETPOST("usertodo","int",3):GETPOST("filtert","int",3);
$usergroup = GETPOST("usergroup","int",3);
$showbirthday = empty($conf->use_javascript_ajax)?GETPOST("showbirthday","int"):1;

// If not choice done on calendar owner, we filter on user.
if (empty($filtert) && empty($conf->global->AGENDA_ALL_CALENDARS))
{
	$filtert=$user->id;
}

$sortfield = GETPOST("sortfield",'alpha');
$sortorder = GETPOST("sortorder",'alpha');
$page = GETPOST("page","int");
if ($page == -1) { $page = 0; }
$limit = $conf->liste_limit;
$offset = $limit * $page;
if (! $sortorder) $sortorder="ASC";
if (! $sortfield) $sortfield="a.datec";

// Security check
$socid = GETPOST("socid","int");
if ($user->societe_id) $socid=$user->societe_id;
$result = restrictedArea($user, 'agenda', 0, '', 'myactions');

if ($socid < 0) $socid='';

$canedit=1;
if (! $user->rights->agenda->myactions->read) accessforbidden();
if (! $user->rights->agenda->allactions->read) $canedit=0;
if (! $user->rights->agenda->allactions->read || $filter =='mine')  // If no permission to see all, we show only affected to me
{
    $filtert=$user->id;
}

$action=GETPOST('action','alpha');
//$year=GETPOST("year");
$year=GETPOST("year","int")?GETPOST("year","int"):date("Y");
$month=GETPOST("month","int")?GETPOST("month","int"):date("m");
$week=GETPOST("week","int")?GETPOST("week","int"):date("W");
$day=GETPOST("day","int")?GETPOST("day","int"):0;
$pid=GETPOST("projectid","int",3);
$status=GETPOST("status");
$type=GETPOST("type");
$maxprint=(isset($_GET["maxprint"])?GETPOST("maxprint"):$conf->global->AGENDA_MAX_EVENTS_DAY_VIEW);
$actioncode=GETPOST("actioncode","alpha",3)?GETPOST("actioncode","alpha",3):(GETPOST("actioncode")=='0'?'0':'');

if ($actioncode == '') $actioncode=(empty($conf->global->AGENDA_DEFAULT_FILTER_TYPE)?'':$conf->global->AGENDA_DEFAULT_FILTER_TYPE);
if ($status == ''   && ! isset($_GET['status']) && ! isset($_POST['status'])) $status=(empty($conf->global->AGENDA_DEFAULT_FILTER_STATUS)?'':$conf->global->AGENDA_DEFAULT_FILTER_STATUS);
if (empty($action) && ! isset($_GET['action']) && ! isset($_POST['action'])) $action=(empty($conf->global->AGENDA_DEFAULT_VIEW)?'show_month':$conf->global->AGENDA_DEFAULT_VIEW);

if (GETPOST('viewcal') && $action != 'show_day' && $action != 'show_week')  {
    $action='show_month'; $day='';
}                                                   // View by month
if (GETPOST('viewweek') || $action == 'show_week') {
    $action='show_week'; $week=($week?$week:date("W")); $day=($day?$day:date("d"));
}  // View by week
if (GETPOST('viewday') || $action == 'show_day')  {
    $action='show_day'; $day=($day?$day:date("d"));
}                                  // View by day


$langs->load("agenda");
$langs->load("other");
$langs->load("commercial");

// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array('agenda'));


/*
 * Actions
 */

if (GETPOST("viewlist") || $action == 'show_list')
{
    $param='';
    foreach($_POST as $key => $val)
    {
        if ($key=='token') continue;
        $param.='&'.$key.'='.urlencode($val);
    }
    //print $param;
    header("Location: ".DOL_URL_ROOT.'/comm/action/listactions.php?'.$param);
    exit;
}

if (GETPOST("viewperuser") || $action == 'show_peruser')
{
    $param='';
    foreach($_POST as $key => $val)
    {
        if ($key=='token') continue;
        $param.='&'.$key.'='.urlencode($val);
    }
    //print $param;
    header("Location: ".DOL_URL_ROOT.'/comm/action/peruser.php?'.$param);
    exit;
}

if ($action =='delete_action')
{
    $event = new ActionComm($db);
    $event->fetch($actionid);
    $result=$event->delete();
}



/*
 * View
 */

$help_url='EN:Module_Agenda_En|FR:Module_Agenda|ES:M&oacute;dulo_Agenda';
llxHeader('',$langs->trans("Agenda"),$help_url);

$form=new Form($db);
$companystatic=new Societe($db);
$contactstatic=new Contact($db);



$listofextcals=array();

// Define list of external calendars (global admin setup)
if (empty($conf->global->AGENDA_DISABLE_EXT))
{
    $i=0;
    while($i < $MAXAGENDA)
    {
        $i++;
        $source='AGENDA_EXT_SRC'.$i;
        $name='AGENDA_EXT_NAME'.$i;
        $color='AGENDA_EXT_COLOR'.$i;
        $buggedfile='AGENDA_EXT_BUGGEDFILE'.$i;
        if (! empty($conf->global->$source) && ! empty($conf->global->$name))
        {
        	// Note: $conf->global->buggedfile can be empty or 'uselocalandtznodaylight' or 'uselocalandtzdaylight'
        	$listofextcals[]=array('src'=>$conf->global->$source,'name'=>$conf->global->$name,'color'=>$conf->global->$color,'buggedfile'=>(isset($conf->global->buggedfile)?$conf->global->buggedfile:0));
        }
    }
}
// Define list of external calendars (user setup)
if (empty($user->conf->AGENDA_DISABLE_EXT))
{
	$i=0;
	while($i < $MAXAGENDA)
	{
		$i++;
		$source='AGENDA_EXT_SRC_'.$user->id.'_'.$i;
		$name='AGENDA_EXT_NAME_'.$user->id.'_'.$i;
		$color='AGENDA_EXT_COLOR_'.$user->id.'_'.$i;
		$enabled='AGENDA_EXT_ENABLED_'.$user->id.'_'.$i;
		$buggedfile='AGENDA_EXT_BUGGEDFILE_'.$user->id.'_'.$i;
		if (! empty($user->conf->$source) && ! empty($user->conf->$name))
		{
			// Note: $conf->global->buggedfile can be empty or 'uselocalandtznodaylight' or 'uselocalandtzdaylight'
			$listofextcals[]=array('src'=>$user->conf->$source,'name'=>$user->conf->$name,'color'=>$user->conf->$color,'buggedfile'=>(isset($user->conf->buggedfile)?$user->conf->buggedfile:0));
		}
	}
}

if (empty($action) || $action=='show_month')
{
    $prev = dol_get_prev_month($month, $year);
    $prev_year  = $prev['year'];
    $prev_month = $prev['month'];
    $next = dol_get_next_month($month, $year);
    $next_year  = $next['year'];
    $next_month = $next['month'];

    $max_day_in_prev_month = date("t",dol_mktime(0,0,0,$prev_month,1,$prev_year));  // Nb of days in previous month
    $max_day_in_month = date("t",dol_mktime(0,0,0,$month,1,$year));                 // Nb of days in next month
    // tmpday is a negative or null cursor to know how many days before the 1st to show on month view (if tmpday=0, 1st is monday)
    $tmpday = -date("w",dol_mktime(12,0,0,$month,1,$year,true))+2;		// date('w') is 0 fo sunday
    $tmpday+=((isset($conf->global->MAIN_START_WEEK)?$conf->global->MAIN_START_WEEK:1)-1);
    if ($tmpday >= 1) $tmpday -= 7;	// If tmpday is 0 we start with sunday, if -6, we start with monday of previous week.
    // Define firstdaytoshow and lastdaytoshow (warning: lastdaytoshow is last second to show + 1)
    $firstdaytoshow=dol_mktime(0,0,0,$prev_month,$max_day_in_prev_month+$tmpday,$prev_year);
    $next_day=7 - ($max_day_in_month+1-$tmpday) % 7;
    if ($next_day < 6) $next_day+=7;
    $lastdaytoshow=dol_mktime(0,0,0,$next_month,$next_day,$next_year);
}
if ($action=='show_week')
{
    $prev = dol_get_first_day_week($day, $month, $year);
    $prev_year  = $prev['prev_year'];
    $prev_month = $prev['prev_month'];
    $prev_day   = $prev['prev_day'];
    $first_day  = $prev['first_day'];
    $first_month= $prev['first_month'];
    $first_year = $prev['first_year'];

    $week = $prev['week'];

    $day = (int) $day;
    $next = dol_get_next_week($first_day, $week, $first_month, $first_year);
    $next_year  = $next['year'];
    $next_month = $next['month'];
    $next_day   = $next['day'];

    // Define firstdaytoshow and lastdaytoshow (warning: lastdaytoshow is last second to show + 1)
    $firstdaytoshow=dol_mktime(0,0,0,$first_month,$first_day,$first_year);
	$lastdaytoshow=dol_time_plus_duree($firstdaytoshow, 7, 'd');

    $max_day_in_month = date("t",dol_mktime(0,0,0,$month,1,$year));

    $tmpday = $first_day;
}
if ($action == 'show_day')
{
    $prev = dol_get_prev_day($day, $month, $year);
    $prev_year  = $prev['year'];
    $prev_month = $prev['month'];
    $prev_day   = $prev['day'];
    $next = dol_get_next_day($day, $month, $year);
    $next_year  = $next['year'];
    $next_month = $next['month'];
    $next_day   = $next['day'];

    // Define firstdaytoshow and lastdaytoshow (warning: lastdaytoshow is last second to show + 1)
    $firstdaytoshow=dol_mktime(0,0,0,$prev_month,$prev_day,$prev_year);
    $lastdaytoshow=dol_mktime(0,0,0,$next_month,$next_day,$next_year);
}
//print 'xx'.$prev_year.'-'.$prev_month.'-'.$prev_day;
//print 'xx'.$next_year.'-'.$next_month.'-'.$next_day;
//print dol_print_date($firstdaytoshow,'day');
//print dol_print_date($lastdaytoshow,'day');

$title=$langs->trans("DoneAndToDoActions");
if ($status == 'done') $title=$langs->trans("DoneActions");
if ($status == 'todo') $title=$langs->trans("ToDoActions");

$param='';
if ($actioncode || isset($_GET['actioncode']) || isset($_POST['actioncode'])) $param.="&actioncode=".$actioncode;
if ($status || isset($_GET['status']) || isset($_POST['status'])) $param.="&status=".$status;
if ($filter)  $param.="&filter=".$filter;
if ($filtert) $param.="&filtert=".$filtert;
if ($socid)   $param.="&socid=".$socid;
if ($showbirthday) $param.="&showbirthday=1";
if ($pid)     $param.="&projectid=".$pid;
if ($type)   $param.="&type=".$type;
if ($action == 'show_day' || $action == 'show_week' || $action == 'show_month') $param.='&action='.$action;
$param.="&maxprint=".$maxprint;

// Show navigation bar
if (empty($action) || $action=='show_month')
{
    $nav ="<a href=\"?year=".$prev_year."&amp;month=".$prev_month.$param."\">".img_previous($langs->trans("Previous"))."</a>\n";
    $nav.=" <span id=\"month_name\">".dol_print_date(dol_mktime(0,0,0,$month,1,$year),"%b %Y");
    $nav.=" </span>\n";
    $nav.="<a href=\"?year=".$next_year."&amp;month=".$next_month.$param."\">".img_next($langs->trans("Next"))."</a>\n";
    $nav.=" &nbsp; (<a href=\"?year=".$nowyear."&amp;month=".$nowmonth.$param."\">".$langs->trans("Today")."</a>)";
    $picto='calendar';
}
if ($action=='show_week')
{
    $nav ="<a href=\"?year=".$prev_year."&amp;month=".$prev_month."&amp;day=".$prev_day.$param."\">".img_previous($langs->trans("Previous"))."</a>\n";
    $nav.=" <span id=\"month_name\">".dol_print_date(dol_mktime(0,0,0,$first_month,$first_day,$first_year),"%Y").", ".$langs->trans("Week")." ".$week;
    $nav.=" </span>\n";
    $nav.="<a href=\"?year=".$next_year."&amp;month=".$next_month."&amp;day=".$next_day.$param."\">".img_next($langs->trans("Next"))."</a>\n";
    $nav.=" &nbsp; (<a href=\"?year=".$nowyear."&amp;month=".$nowmonth."&amp;day=".$nowday.$param."\">".$langs->trans("Today")."</a>)";
    $picto='calendarweek';
}
if ($action=='show_day')
{
    $nav ="<a href=\"?year=".$prev_year."&amp;month=".$prev_month."&amp;day=".$prev_day.$param."\">".img_previous($langs->trans("Previous"))."</a>\n";
    $nav.=" <span id=\"month_name\">".dol_print_date(dol_mktime(0,0,0,$month,$day,$year),"daytextshort");
    $nav.=" </span>\n";
    $nav.="<a href=\"?year=".$next_year."&amp;month=".$next_month."&amp;day=".$next_day.$param."\">".img_next($langs->trans("Next"))."</a>\n";
    $nav.=" &nbsp; (<a href=\"?year=".$nowyear."&amp;month=".$nowmonth."&amp;day=".$nowday.$param."\">".$langs->trans("Today")."</a>)";
    $picto='calendarday';
}

// Must be after the nav definition
$param.='&year='.$year.'&month='.$month.($day?'&day='.$day:'');
//print 'x'.$param;




$tabactive='';
if ($action == 'show_month') $tabactive='cardmonth';
if ($action == 'show_week') $tabactive='cardweek';
if ($action == 'show_day')  $tabactive='cardday';
if ($action == 'show_list') $tabactive='cardlist';

$paramnoaction=preg_replace('/action=[a-z_]+/','',$param);

$head = calendars_prepare_head($paramnoaction);

dol_fiche_head($head, $tabactive, $langs->trans('Agenda'), 0, 'action');
print_actions_filter($form,$canedit,$status,$year,$month,$day,$showbirthday,0,$filtert,0,$pid,$socid,$action,$listofextcals,$actioncode,$usergroup);
dol_fiche_end();

$showextcals=$listofextcals;
// Legend
if (! empty($conf->use_javascript_ajax))
{
	$s='';
	$s.='<script type="text/javascript">' . "\n";
	$s.='jQuery(document).ready(function () {' . "\n";
	$s.='jQuery("#check_birthday").click(function() { jQuery(".family_birthday").toggle(); });' . "\n";
	$s.='jQuery(".family_birthday").toggle();' . "\n";
	if ($action=="show_week" || $action=="show_month" || empty($action))
	{
    	$s.='jQuery( "td.sortable" ).sortable({connectWith: ".sortable", placeholder: "ui-state-highlight", items: "div.movable", receive: function( event, ui ) {';
    	$s.='var frm=jQuery("#move_event");frm.attr("action",ui.item.find("a.cal_event").attr("href")).children("#newdate").val(jQuery(event.target).closest("div").attr("id"));frm.submit();}});'."\n";
	}
  	$s.='});' . "\n";
	$s.='</script>' . "\n";

	$s.='<div class="nowrap clear float"><input type="checkbox" id="check_mytasks" name="check_mytasks" checked="true" disabled="disabled"> ' . $langs->trans("LocalAgenda").' &nbsp; </div>';
	if (is_array($showextcals) && count($showextcals) > 0)
	{
		$s.='<script type="text/javascript">' . "\n";
		$s.='jQuery(document).ready(function () {
				jQuery("table input[name^=\"check_ext\"]").click(function() {
					var name = $(this).attr("name");

					jQuery(".family_ext" + name.replace("check_ext", "")).toggle();
				});
			});' . "\n";
		$s.='</script>' . "\n";

		foreach ($showextcals as $val)
		{
			$htmlname = md5($val['name']);
			$s.='<div class="nowrap float"><input type="checkbox" id="check_ext' . $htmlname . '" name="check_ext' . $htmlname . '" checked="true"> ' . $val['name'] . ' &nbsp; </div>';
		}
	}
	$s.='<div class="nowrap float"><input type="checkbox" id="check_birthday" name="check_birthday"> '.$langs->trans("AgendaShowBirthdayEvents").' &nbsp; </div>';
}


$link='';
// Add link to show birthdays
if (empty($conf->use_javascript_ajax))
{
	$newparam=$param;   // newparam is for birthday links
    $newparam=preg_replace('/showbirthday=[0-1]/i','showbirthday='.(empty($showbirthday)?1:0),$newparam);
    if (! preg_match('/showbirthday=/i',$newparam)) $newparam.='&showbirthday=1';
    $link='<a href="'.$_SERVER['PHP_SELF'];
    $link.='?'.$newparam;
    $link.='">';
    if (empty($showbirthday)) $link.=$langs->trans("AgendaShowBirthdayEvents");
    else $link.=$langs->trans("AgendaHideBirthdayEvents");
    $link.='</a>';
}

print_fiche_titre($s,$link.' &nbsp; &nbsp; '.$nav, '');


// Get event in an array
$eventarray=array();

$sql = 'SELECT ';
if ($usergroup > 0) $sql.=" DISTINCT";
$sql.= ' a.id, a.label,';
$sql.= ' a.datep,';
$sql.= ' a.datep2,';
$sql.= ' a.percent,';
$sql.= ' a.fk_user_author,a.fk_user_action,';
$sql.= ' a.transparency, a.priority, a.fulldayevent, a.location,';
$sql.= ' a.fk_soc, a.fk_contact,';
$sql.= ' ca.code as type_code, ca.libelle as type_label';
$sql.= ' FROM '.MAIN_DB_PREFIX.'c_actioncomm as ca, '.MAIN_DB_PREFIX."actioncomm as a";
if (! $user->rights->societe->client->voir && ! $socid) $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe_commerciaux as sc ON a.fk_soc = sc.fk_soc";
// We must filter on assignement table
if ($filtert > 0 || $usergroup > 0) $sql.=", ".MAIN_DB_PREFIX."actioncomm_resources as ar";
if ($usergroup > 0) $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."usergroup_user as ugu ON ugu.fk_user = ar.fk_element";
$sql.= ' WHERE a.fk_action = ca.id';
$sql.= ' AND a.entity IN ('.getEntity('agenda', 1).')';
if ($actioncode) $sql.=" AND ca.code='".$db->escape($actioncode)."'";
if ($pid) $sql.=" AND a.fk_project=".$db->escape($pid);
if (! $user->rights->societe->client->voir && ! $socid) $sql.= " AND (a.fk_soc IS NULL OR sc.fk_user = " .$user->id . ")";
if ($socid > 0) $sql.= ' AND a.fk_soc = '.$socid;
// We must filter on assignement table
if ($filtert > 0 || $usergroup > 0) $sql.= " AND ar.fk_actioncomm = a.id AND ar.element_type='user'";
if ($action == 'show_day')
{
    $sql.= " AND (";
    $sql.= " (a.datep BETWEEN '".$db->idate(dol_mktime(0,0,0,$month,$day,$year))."'";
    $sql.= " AND '".$db->idate(dol_mktime(23,59,59,$month,$day,$year))."')";
    $sql.= " OR ";
    $sql.= " (a.datep2 BETWEEN '".$db->idate(dol_mktime(0,0,0,$month,$day,$year))."'";
    $sql.= " AND '".$db->idate(dol_mktime(23,59,59,$month,$day,$year))."')";
    $sql.= " OR ";
    $sql.= " (a.datep < '".$db->idate(dol_mktime(0,0,0,$month,$day,$year))."'";
    $sql.= " AND a.datep2 > '".$db->idate(dol_mktime(23,59,59,$month,$day,$year))."')";
    $sql.= ')';
}
else
{
    // To limit array
    $sql.= " AND (";
    $sql.= " (a.datep BETWEEN '".$db->idate(dol_mktime(0,0,0,$month,1,$year)-(60*60*24*7))."'";   // Start 7 days before
    $sql.= " AND '".$db->idate(dol_mktime(23,59,59,$month,28,$year)+(60*60*24*10))."')";            // End 7 days after + 3 to go from 28 to 31
    $sql.= " OR ";
    $sql.= " (a.datep2 BETWEEN '".$db->idate(dol_mktime(0,0,0,$month,1,$year)-(60*60*24*7))."'";
    $sql.= " AND '".$db->idate(dol_mktime(23,59,59,$month,28,$year)+(60*60*24*10))."')";
    $sql.= " OR ";
    $sql.= " (a.datep < '".$db->idate(dol_mktime(0,0,0,$month,1,$year)-(60*60*24*7))."'";
    $sql.= " AND a.datep2 > '".$db->idate(dol_mktime(23,59,59,$month,28,$year)+(60*60*24*10))."')";
    $sql.= ')';
}
if ($type) $sql.= " AND ca.id = ".$type;
if ($status == '0') { $sql.= " AND a.percent = 0"; }
if ($status == '-1') { $sql.= " AND a.percent = -1"; }	// Not applicable
if ($status == '50') { $sql.= " AND (a.percent > 0 AND a.percent < 100)"; }	// Running already started
if ($status == 'done' || $status == '100') { $sql.= " AND (a.percent = 100 OR (a.percent = -1 AND a.datep2 <= '".$db->idate($now)."'))"; }
if ($status == 'todo') { $sql.= " AND ((a.percent >= 0 AND a.percent < 100) OR (a.percent = -1 AND a.datep2 > '".$db->idate($now)."'))"; }
// We must filter on assignement table
if ($filtert > 0 || $usergroup > 0)
{
    $sql.= " AND (";
    if ($filtert > 0) $sql.= "ar.fk_element = ".$filtert;
    if ($usergroup > 0) $sql.= ($filtert>0?" OR ":"")." ugu.fk_usergroup = ".$usergroup;
    $sql.= ")";
}
// Sort on date
$sql.= ' ORDER BY datep';
//print $sql;

dol_syslog("comm/action/index.php", LOG_DEBUG);
$resql=$db->query($sql);
if ($resql)
{
    $num = $db->num_rows($resql);
    $i=0;
    while ($i < $num)
    {
        $obj = $db->fetch_object($resql);

        // Discard auto action if option is on
        if (! empty($conf->global->AGENDA_ALWAYS_HIDE_AUTO) && $obj->type_code == 'AC_OTH_AUTO')
        {
        	$i++;
        	continue;
        }

        // Create a new object action
        $event=new ActionComm($db);
        $event->id=$obj->id;
        $event->datep=$db->jdate($obj->datep);      // datep and datef are GMT date
        $event->datef=$db->jdate($obj->datep2);
        $event->type_code=$obj->type_code;
        $event->type_label=$obj->type_label;
        $event->libelle=$obj->label;
        $event->percentage=$obj->percent;
        $event->authorid=$obj->fk_user_author;		// user id of creator
        $event->userownerid=$obj->fk_user_action;	// user id of owner
        $event->fetch_userassigned();				// This load $event->userassigned
        $event->priority=$obj->priority;
        $event->fulldayevent=$obj->fulldayevent;
        $event->location=$obj->location;
        $event->transparency=$obj->transparency;

        $event->societe->id=$obj->fk_soc;
        $event->contact->id=$obj->fk_contact;

        // Defined date_start_in_calendar and date_end_in_calendar property
        // They are date start and end of action but modified to not be outside calendar view.
        if ($event->percentage <= 0)
        {
            $event->date_start_in_calendar=$event->datep;
            if ($event->datef != '' && $event->datef >= $event->datep) $event->date_end_in_calendar=$event->datef;
            else $event->date_end_in_calendar=$event->datep;
        }
        else
        {
            $event->date_start_in_calendar=$event->datep;
            if ($event->datef != '' && $event->datef >= $event->datep) $event->date_end_in_calendar=$event->datef;
            else $event->date_end_in_calendar=$event->datep;
        }
        // Define ponctual property
        if ($event->date_start_in_calendar == $event->date_end_in_calendar)
        {
            $event->ponctuel=1;
        }

        // Check values
        if ($event->date_end_in_calendar < $firstdaytoshow ||
        $event->date_start_in_calendar >= $lastdaytoshow)
        {
            // This record is out of visible range
        }
        else
        {
            if ($event->date_start_in_calendar < $firstdaytoshow) $event->date_start_in_calendar=$firstdaytoshow;
            if ($event->date_end_in_calendar >= $lastdaytoshow) $event->date_end_in_calendar=($lastdaytoshow-1);

            // Add an entry in actionarray for each day
            $daycursor=$event->date_start_in_calendar;
            $annee = date('Y',$daycursor);
            $mois = date('m',$daycursor);
            $jour = date('d',$daycursor);

            // Loop on each day covered by action to prepare an index to show on calendar
            $loop=true; $j=0;
            $daykey=dol_mktime(0,0,0,$mois,$jour,$annee);
            do
            {
                //if ($event->id==408) print 'daykey='.$daykey.' '.$event->datep.' '.$event->datef.'<br>';

                $eventarray[$daykey][]=$event;
                $j++;

                $daykey+=60*60*24;
                if ($daykey > $event->date_end_in_calendar) $loop=false;
            }
            while ($loop);

            //print 'Event '.$i.' id='.$event->id.' (start='.dol_print_date($event->datep).'-end='.dol_print_date($event->datef);
            //print ' startincalendar='.dol_print_date($event->date_start_in_calendar).'-endincalendar='.dol_print_date($event->date_end_in_calendar).') was added in '.$j.' different index key of array<br>';
        }
        $i++;

    }
}
else
{
    dol_print_error($db);
}

if ($showbirthday)
{
    // Add events in array
    $sql = 'SELECT sp.rowid, sp.lastname, sp.firstname, sp.birthday';
    $sql.= ' FROM '.MAIN_DB_PREFIX.'socpeople as sp';
    $sql.= ' WHERE (priv=0 OR (priv=1 AND fk_user_creat='.$user->id.'))';
    $sql.= " AND sp.entity IN (".getEntity('societe', 1).")";
    if ($action == 'show_day')
    {
        $sql.= ' AND MONTH(birthday) = '.$month;
        $sql.= ' AND DAY(birthday) = '.$day;
    }
    else
    {
        $sql.= ' AND MONTH(birthday) = '.$month;
    }
    $sql.= ' ORDER BY birthday';

    dol_syslog("comm/action/index.php", LOG_DEBUG);
    $resql=$db->query($sql);
    if ($resql)
    {
        $num = $db->num_rows($resql);
        $i=0;
        while ($i < $num)
        {
            $obj = $db->fetch_object($resql);
            $event=new ActionComm($db);
            $event->id=$obj->rowid; // We put contact id in action id for birthdays events
            $datebirth=dol_stringtotime($obj->birthday,1);
            //print 'ee'.$obj->birthday.'-'.$datebirth;
            $datearray=dol_getdate($datebirth,true);
            $event->datep=dol_mktime(0,0,0,$datearray['mon'],$datearray['mday'],$year,true);    // For full day events, date are also GMT but they wont but converted during output
            $event->datef=$event->datep;
            $event->type_code='BIRTHDAY';
            $event->libelle=$langs->trans("Birthday").' '.dolGetFirstLastname($obj->firstname,$obj->lastname);
            $event->percentage=100;
            $event->fulldayevent=true;

            $event->date_start_in_calendar=$event->datep;
            $event->date_end_in_calendar=$event->datef;
            $event->ponctuel=0;

            // Add an entry in actionarray for each day
            $daycursor=$event->date_start_in_calendar;
            $annee = date('Y',$daycursor);
            $mois = date('m',$daycursor);
            $jour = date('d',$daycursor);

            $loop=true;
            $daykey=dol_mktime(0,0,0,$mois,$jour,$annee);
            do
            {
                $eventarray[$daykey][]=$event;
                $daykey+=60*60*24;
                if ($daykey > $event->date_end_in_calendar) $loop=false;
            }
            while ($loop);
            $i++;
        }
    }
    else
    {
        dol_print_error($db);
    }
}

if (count($listofextcals))
{
    require_once DOL_DOCUMENT_ROOT.'/comm/action/class/ical.class.php';
    foreach($listofextcals as $extcal)
    {
        $url=$extcal['src'];    // Example: https://www.google.com/calendar/ical/eldy10%40gmail.com/private-cde92aa7d7e0ef6110010a821a2aaeb/basic.ics
        $namecal = $extcal['name'];
        $colorcal = $extcal['color'];
        $buggedfile = $extcal['buggedfile'];
        //print "url=".$url." namecal=".$namecal." colorcal=".$colorcal." buggedfile=".$buggedfile;
        $ical=new ICal();
        $ical->parse($url);

        // After this $ical->cal['VEVENT'] contains array of events, $ical->cal['DAYLIGHT'] contains daylight info, $ical->cal['STANDARD'] contains non daylight info, ...
        //var_dump($ical->cal); exit;
        $icalevents=array();
        if (is_array($ical->get_event_list())) $icalevents=array_merge($icalevents,$ical->get_event_list());        // Add $ical->cal['VEVENT']
        if (is_array($ical->get_freebusy_list())) $icalevents=array_merge($icalevents,$ical->get_freebusy_list());  // Add $ical->cal['VFREEBUSY']

        if (count($icalevents)>0)
        {
            // Duplicate all repeatable events into new entries
            $moreicalevents=array();
            foreach($icalevents as $icalevent)
            {
                if (isset($icalevent['RRULE']) && is_array($icalevent['RRULE'])) //repeatable event
                {
                    //if ($event->date_start_in_calendar < $firstdaytoshow) $event->date_start_in_calendar=$firstdaytoshow;
                    //if ($event->date_end_in_calendar > $lastdaytoshow) $event->date_end_in_calendar=($lastdaytoshow-1);
                    if ($icalevent['DTSTART;VALUE=DATE']) //fullday event
                    {
                        $datecurstart=dol_stringtotime($icalevent['DTSTART;VALUE=DATE'],1);
                        $datecurend=dol_stringtotime($icalevent['DTEND;VALUE=DATE'],1)-1;  // We remove one second to get last second of day
                    }
                    else if (is_array($icalevent['DTSTART']) && ! empty($icalevent['DTSTART']['unixtime']))
                    {
                        $datecurstart=$icalevent['DTSTART']['unixtime'];
                        $datecurend=$icalevent['DTEND']['unixtime'];
                        if (! empty($ical->cal['DAYLIGHT']['DTSTART']) && $datecurstart)
                        {
                            //var_dump($ical->cal);
                            $tmpcurstart=$datecurstart;
                            $tmpcurend=$datecurend;
                            $tmpdaylightstart=dol_mktime(0,0,0,1,1,1970,1) + (int) $ical->cal['DAYLIGHT']['DTSTART'];
                            $tmpdaylightend=dol_mktime(0,0,0,1,1,1970,1) + (int) $ical->cal['STANDARD']['DTSTART'];
                            //var_dump($tmpcurstart);var_dump($tmpcurend); var_dump($ical->cal['DAYLIGHT']['DTSTART']);var_dump($ical->cal['STANDARD']['DTSTART']);
                            // Edit datecurstart and datecurend
                            if ($tmpcurstart >= $tmpdaylightstart && $tmpcurstart < $tmpdaylightend) $datecurstart-=((int) $ical->cal['DAYLIGHT']['TZOFFSETTO'])*36;
                            else $datecurstart-=((int) $ical->cal['STANDARD']['TZOFFSETTO'])*36;
                            if ($tmpcurend >= $tmpdaylightstart && $tmpcurstart < $tmpdaylightend) $datecurend-=((int) $ical->cal['DAYLIGHT']['TZOFFSETTO'])*36;
                            else $datecurend-=((int) $ical->cal['STANDARD']['TZOFFSETTO'])*36;
                        }
                        // datecurstart and datecurend are now GMT date
                        //var_dump($datecurstart); var_dump($datecurend); exit;
                    }
                    else
                    {
                        // Not a recongized record
                        dol_syslog("Found a not recognized repeatable record with unknown date start", LOG_ERR);
                        continue;
                    }
                    //print 'xx'.$datecurstart;exit;

                    $interval=(empty($icalevent['RRULE']['INTERVAL'])?1:$icalevent['RRULE']['INTERVAL']);
                    $until=empty($icalevent['RRULE']['UNTIL'])?0:dol_stringtotime($icalevent['RRULE']['UNTIL'],1);
                    $maxrepeat=empty($icalevent['RRULE']['COUNT'])?0:$icalevent['RRULE']['COUNT'];
                    if ($until && ($until+($datecurend-$datecurstart)) < $firstdaytoshow) continue;  // We discard repeatable event that end before start date to show
                    if ($datecurstart >= $lastdaytoshow) continue;                                   // We discard repeatable event that start after end date to show

                    $numofevent=0;
                    while (($datecurstart < $lastdaytoshow) && (empty($maxrepeat) || ($numofevent < $maxrepeat)))
                    {
                        if ($datecurend >= $firstdaytoshow)    // We add event
                        {
                            $newevent=$icalevent;
                            unset($newevent['RRULE']);
                            if ($icalevent['DTSTART;VALUE=DATE'])
                            {
                                $newevent['DTSTART;VALUE=DATE']=dol_print_date($datecurstart,'%Y%m%d');
                                $newevent['DTEND;VALUE=DATE']=dol_print_date($datecurend+1,'%Y%m%d');
                            }
                            else
                            {
                                $newevent['DTSTART']=$datecurstart;
                                $newevent['DTEND']=$datecurend;
                            }
                            $moreicalevents[]=$newevent;
                        }
                        // Jump on next occurence
                        $numofevent++;
                        $savdatecurstart=$datecurstart;
                        if ($icalevent['RRULE']['FREQ']=='DAILY')
                        {
                            $datecurstart=dol_time_plus_duree($datecurstart, $interval, 'd');
                            $datecurend=dol_time_plus_duree($datecurend, $interval, 'd');
                        }
                        if ($icalevent['RRULE']['FREQ']=='WEEKLY')
                        {
                            $datecurstart=dol_time_plus_duree($datecurstart, $interval, 'w');
                            $datecurend=dol_time_plus_duree($datecurend, $interval, 'w');
                        }
                        elseif ($icalevent['RRULE']['FREQ']=='MONTHLY')
                        {
                            $datecurstart=dol_time_plus_duree($datecurstart, $interval, 'm');
                            $datecurend=dol_time_plus_duree($datecurend, $interval, 'm');
                        }
                        elseif ($icalevent['RRULE']['FREQ']=='YEARLY')
                        {
                            $datecurstart=dol_time_plus_duree($datecurstart, $interval, 'y');
                            $datecurend=dol_time_plus_duree($datecurend, $interval, 'y');
                        }
                        // Test to avoid infinite loop ($datecurstart must increase)
                        if ($savdatecurstart >= $datecurstart)
                        {
                            dol_syslog("Found a rule freq ".$icalevent['RRULE']['FREQ']." not managed by dolibarr code. Assume 1 week frequency.", LOG_ERR);
                            $datecurstart+=3600*24*7;
                            $datecurend+=3600*24*7;
                        }
                    }
                }
            }
            $icalevents=array_merge($icalevents,$moreicalevents);

            // Loop on each entry into cal file to know if entry is qualified and add an ActionComm into $eventarray
            foreach($icalevents as $icalevent)
            {
            	//var_dump($icalevent);

                //print $icalevent['SUMMARY'].'->'.var_dump($icalevent).'<br>';exit;
                if (! empty($icalevent['RRULE'])) continue;    // We found a repeatable event. It was already split into unitary events, so we discard general rule.

                // Create a new object action
                $event=new ActionComm($db);
                $addevent = false;
                if (isset($icalevent['DTSTART;VALUE=DATE'])) // fullday event
                {
                    // For full day events, date are also GMT but they wont but converted using tz during output
                    $datestart=dol_stringtotime($icalevent['DTSTART;VALUE=DATE'],1);
                    $dateend=dol_stringtotime($icalevent['DTEND;VALUE=DATE'],1)-1;  // We remove one second to get last second of day
                    //print 'x'.$datestart.'-'.$dateend;exit;
                    //print dol_print_date($dateend,'dayhour','gmt');
                    $event->fulldayevent=true;
                    $addevent=true;
                }
                elseif (!is_array($icalevent['DTSTART'])) // not fullday event (DTSTART is not array. It is a value like '19700101T000000Z' for 00:00 in greenwitch)
                {
                    $datestart=$icalevent['DTSTART'];
                    $dateend=$icalevent['DTEND'];
                    $addevent=true;
                }
                elseif (isset($icalevent['DTSTART']['unixtime']))	// File contains a local timezone + a TZ (for example when using bluemind)
                {
                    $datestart=$icalevent['DTSTART']['unixtime'];
                    $dateend=$icalevent['DTEND']['unixtime'];
                    // $buggedfile is set to uselocalandtznodaylight if conf->global->AGENDA_EXT_BUGGEDFILEx = 'uselocalandtznodaylight'
                    if ($buggedfile === 'uselocalandtznodaylight')	// unixtime is a local date that does not take daylight into account, TZID is +1 for example for 'Europe/Paris' in summer instead of 2
                    {
                    	// TODO
                    }
                    // $buggedfile is set to uselocalandtzdaylight if conf->global->AGENDA_EXT_BUGGEDFILEx = 'uselocalandtzdaylight' (for example with bluemind)
                    if ($buggedfile === 'uselocalandtzdaylight')	// unixtime is a local date that does take daylight into account, TZID is +2 for example for 'Europe/Paris' in summer
                    {
                    	$localtzs = new DateTimeZone(preg_replace('/"/','',$icalevent['DTSTART']['TZID']));
                    	$localtze = new DateTimeZone(preg_replace('/"/','',$icalevent['DTEND']['TZID']));
                    	$localdts = new DateTime(dol_print_date($datestart,'dayrfc','gmt'), $localtzs);
                    	$localdte = new DateTime(dol_print_date($dateend,'dayrfc','gmt'), $localtze);
						$tmps=-1*$localtzs->getOffset($localdts);
						$tmpe=-1*$localtze->getOffset($localdte);
						$datestart+=$tmps;
						$dateend+=$tmpe;
						//var_dump($datestart);
                    }
                    $addevent=true;
                }

                if ($addevent)
                {
                    $event->id=$icalevent['UID'];
                    $event->icalname=$namecal;
                    $event->icalcolor=$colorcal;
                    $usertime=0;    // We dont modify date because we want to have date into memory datep and datef stored as GMT date. Compensation will be done during output.
                    $event->datep=$datestart+$usertime;
                    $event->datef=$dateend+$usertime;
                    $event->type_code="ICALEVENT";

                    if($icalevent['SUMMARY']) $event->libelle=$icalevent['SUMMARY'];
                    elseif($icalevent['DESCRIPTION']) $event->libelle=dol_nl2br($icalevent['DESCRIPTION'],1);
                    else $event->libelle = $langs->trans("ExtSiteNoLabel");

                    $event->date_start_in_calendar=$event->datep;

                    if ($event->datef != '' && $event->datef >= $event->datep) $event->date_end_in_calendar=$event->datef;
                    else $event->date_end_in_calendar=$event->datep;

                    // Define ponctual property
                    if ($event->date_start_in_calendar == $event->date_end_in_calendar)
                    {
                        $event->ponctuel=1;
                        //print 'x'.$datestart.'-'.$dateend;exit;
                    }

                    // Add event into $eventarray if date range are ok.
                    if ($event->date_end_in_calendar < $firstdaytoshow || $event->date_start_in_calendar >= $lastdaytoshow)
                    {
                        //print 'x'.$datestart.'-'.$dateend;exit;
                        //print 'x'.$datestart.'-'.$dateend;exit;
                        //print 'x'.$datestart.'-'.$dateend;exit;
                        // This record is out of visible range
                    }
                    else
                    {
                        if ($event->date_start_in_calendar < $firstdaytoshow) $event->date_start_in_calendar=$firstdaytoshow;
                        if ($event->date_end_in_calendar >= $lastdaytoshow) $event->date_end_in_calendar=($lastdaytoshow - 1);

                        // Add an entry in actionarray for each day
                        $daycursor=$event->date_start_in_calendar;
                        $annee = date('Y',$daycursor);
                        $mois = date('m',$daycursor);
                        $jour = date('d',$daycursor);

                        // Loop on each day covered by action to prepare an index to show on calendar
                        $loop=true; $j=0;
                        // daykey must be date that represent day box in calendar so must be a user time
                        $daykey=dol_mktime(0,0,0,$mois,$jour,$annee);
                        $daykeygmt=dol_mktime(0,0,0,$mois,$jour,$annee,true,0);
                        do
                     {
                            //if ($event->fulldayevent) print dol_print_date($daykeygmt,'dayhour','gmt').'-'.dol_print_date($daykey,'dayhour','gmt').'-'.dol_print_date($event->date_end_in_calendar,'dayhour','gmt').' ';
                            $eventarray[$daykey][]=$event;
                            $daykey+=60*60*24;  $daykeygmt+=60*60*24;   // Add one day
                            if (($event->fulldayevent ? $daykeygmt : $daykey) > $event->date_end_in_calendar) $loop=false;
                        }
                        while ($loop);
                    }
                }
            }
        }
    }
}

$maxnbofchar=18;
$cachethirdparties=array();
$cachecontacts=array();
$cacheusers=array();

// Define theme_datacolor array
$color_file = DOL_DOCUMENT_ROOT."/theme/".$conf->theme."/graph-color.php";
if (is_readable($color_file))
{
    include_once $color_file;
}
if (! is_array($theme_datacolor)) $theme_datacolor=array(array(120,130,150), array(200,160,180), array(190,190,220));


if (empty($action) || $action == 'show_month')      // View by month
{
    $newparam=$param;   // newparam is for birthday links
    $newparam=preg_replace('/showbirthday=/i','showbirthday_=',$newparam);	// To avoid replacement when replace day= is done
    $newparam=preg_replace('/action=show_month&?/i','',$newparam);
    $newparam=preg_replace('/action=show_week&?/i','',$newparam);
    $newparam=preg_replace('/day=[0-9]+&?/i','',$newparam);
    $newparam=preg_replace('/month=[0-9]+&?/i','',$newparam);
    $newparam=preg_replace('/year=[0-9]+&?/i','',$newparam);
    $newparam=preg_replace('/viewcal=[0-9]+&?/i','',$newparam);
    $newparam=preg_replace('/showbirthday_=/i','showbirthday=',$newparam);	// Restore correct parameter
    $newparam.='&viewcal=1';
    echo '<table width="100%" class="nocellnopadd cal_month">';
    echo ' <tr class="liste_titre">';
    $i=0;
    while ($i < 7)
    {
        echo '  <td align="center">'.$langs->trans("Day".(($i+(isset($conf->global->MAIN_START_WEEK)?$conf->global->MAIN_START_WEEK:1)) % 7))."</td>\n";
        $i++;
    }
    echo " </tr>\n";

    $todayarray=dol_getdate($now,'fast');
    $todaytms=dol_mktime(0, 0, 0, $todayarray['mon'], $todayarray['mday'], $todayarray['year']);

    // In loops, tmpday contains day nb in current month (can be zero or negative for days of previous month)
    //var_dump($eventarray);
    for ($iter_week = 0; $iter_week < 6 ; $iter_week++)
    {
        echo " <tr>\n";
        for ($iter_day = 0; $iter_day < 7; $iter_day++)
        {
        	/* Show days before the beginning of the current month (previous month)  */
            if ($tmpday <= 0)
            {
                $style='cal_other_month cal_past';
        		if ($iter_day == 6) $style.=' cal_other_month_right';
                echo '  <td class="'.$style.' nowrap" width="14%" valign="top">';
                show_day_events($db, $max_day_in_prev_month + $tmpday, $prev_month, $prev_year, $month, $style, $eventarray, $maxprint, $maxnbofchar, $newparam);
                echo "  </td>\n";
            }
            /* Show days of the current month */
            elseif ($tmpday <= $max_day_in_month)
            {
                $curtime = dol_mktime(0, 0, 0, $month, $tmpday, $year);
                $style='cal_current_month';
                if ($iter_day == 6) $style.=' cal_current_month_right';
                $today=0;
                if ($todayarray['mday']==$tmpday && $todayarray['mon']==$month && $todayarray['year']==$year) $today=1;
                if ($today) $style='cal_today';
                if ($curtime < $todaytms) $style.=' cal_past';
				//var_dump($todayarray['mday']."==".$tmpday." && ".$todayarray['mon']."==".$month." && ".$todayarray['year']."==".$year.' -> '.$style);
                echo '  <td class="'.$style.' nowrap" width="14%" valign="top">';
                show_day_events($db, $tmpday, $month, $year, $month, $style, $eventarray, $maxprint, $maxnbofchar, $newparam);
                echo "  </td>\n";
            }
            /* Show days after the current month (next month) */
            else
			{
                $style='cal_other_month';
                if ($iter_day == 6) $style.=' cal_other_month_right';
                echo '  <td class="'.$style.' nowrap" width="14%" valign="top">';
                show_day_events($db, $tmpday - $max_day_in_month, $next_month, $next_year, $month, $style, $eventarray, $maxprint, $maxnbofchar, $newparam);
                echo "</td>\n";
            }
            $tmpday++;
        }
        echo " </tr>\n";
    }
    echo "</table>\n";
    echo '<form id="move_event" action="" method="POST"><input type="hidden" name="action" value="mupdate">';
    echo '<input type="hidden" name="backtopage" value="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">';
    echo '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    echo '<input type="hidden" name="newdate" id="newdate">' ;
    echo '</form>';

}
elseif ($action == 'show_week') // View by week
{
    $newparam=$param;   // newparam is for birthday links
    $newparam=preg_replace('/showbirthday=/i','showbirthday_=',$newparam);	// To avoid replacement when replace day= is done
    $newparam=preg_replace('/action=show_month&?/i','',$newparam);
    $newparam=preg_replace('/action=show_week&?/i','',$newparam);
    $newparam=preg_replace('/day=[0-9]+&?/i','',$newparam);
    $newparam=preg_replace('/month=[0-9]+&?/i','',$newparam);
    $newparam=preg_replace('/year=[0-9]+&?/i','',$newparam);
    $newparam=preg_replace('/viewweek=[0-9]+&?/i','',$newparam);
    $newparam=preg_replace('/showbirthday_=/i','showbirthday=',$newparam);	// Restore correct parameter
    $newparam.='&viewweek=1';
    echo '<table width="100%" class="nocellnopadd cal_month">';
    echo ' <tr class="liste_titre">';
    $i=0;
    while ($i < 7)
    {
        echo '  <td align="center">'.$langs->trans("Day".(($i+(isset($conf->global->MAIN_START_WEEK)?$conf->global->MAIN_START_WEEK:1)) % 7))."</td>\n";
        $i++;
    }
    echo " </tr>\n";

    echo " <tr>\n";

    for ($iter_day = 0; $iter_day < 7; $iter_day++)
    {
        // Show days of the current week
		$curtime = dol_time_plus_duree($firstdaytoshow, $iter_day, 'd');
		$tmparray = dol_getdate($curtime,'fast');
		$tmpday = $tmparray['mday'];
		$tmpmonth = $tmparray['mon'];
		$tmpyear = $tmparray['year'];

        $style='cal_current_month';
        if ($iter_day == 6) $style.=' cal_other_month_right';
        $today=0;
        $todayarray=dol_getdate($now,'fast');
        if ($todayarray['mday']==$tmpday && $todayarray['mon']==$tmpmonth && $todayarray['year']==$tmpyear) $today=1;
        if ($today) $style='cal_today';

        echo '  <td class="'.$style.'" width="14%" valign="top">';
        show_day_events($db, $tmpday, $tmpmonth, $tmpyear, $month, $style, $eventarray, 0, $maxnbofchar, $newparam, 1, 300);
        echo "  </td>\n";
    }
    echo " </tr>\n";

    echo "</table>\n";
    echo '<form id="move_event" action="" method="POST"><input type="hidden" name="action" value="mupdate">';
    echo '<input type="hidden" name="backtopage" value="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">';
    echo '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    echo '<input type="hidden" name="newdate" id="newdate">' ;
    echo '</form>';
}
else    // View by day
{
    $newparam=$param;   // newparam is for birthday links
    $newparam=preg_replace('/action=show_month&?/i','',$newparam);
    $newparam=preg_replace('/action=show_week&?/i','',$newparam);
    $newparam=preg_replace('/viewday=[0-9]+&?/i','',$newparam);
    $newparam.='&viewday=1';
    // Code to show just one day
    $style='cal_current_month cal_current_month_oneday';
    $today=0;
    $todayarray=dol_getdate($now,'fast');
    if ($todayarray['mday']==$day && $todayarray['mon']==$month && $todayarray['year']==$year) $today=1;
    //if ($today) $style='cal_today';

    $timestamp=dol_mktime(12,0,0,$month,$day,$year);
    $arraytimestamp=dol_getdate($timestamp);
    echo '<table width="100%" class="nocellnopadd cal_month">';
    echo ' <tr class="liste_titre">';
    echo '  <td align="center">'.$langs->trans("Day".$arraytimestamp['wday'])."</td>\n";
    echo " </tr>\n";
    echo " <tr>\n";
    echo '  <td class="'.$style.'" width="14%" valign="top">';
    $maxnbofchar=80;
    show_day_events($db, $day, $month, $year, $month, $style, $eventarray, 0, $maxnbofchar, $newparam, 1, 300);
    echo "</td>\n";
    echo " </tr>\n";
    echo '</table>';
}


/* TODO Export
 print '
<a href="" id="actionagenda_ical_link"><img src="'.DOL_URL_ROOT.'/theme/common/ical.gif" border="0"/></a>
<a href="" id="actionagenda_vcal_link"><img src="'.DOL_URL_ROOT.'/theme/common/vcal.gif" border="0"/></a>
<a href="" id="actionagenda_rss_link"><img src="'.DOL_URL_ROOT.'/theme/common/rss.gif"  border="0"/></a>

<script>
$("#actionagenda_rss_link").attr("href","/public/agenda/agendaexport.php?format=rss&type=ActionAgenda&exportkey=dolibarr&token="+getToken()+"&status="+getStatus()+"&userasked="+getUserasked()+"&usertodo="+getUsertodo()+"&userdone="+getUserdone()+"&year="+getYear()+"&month="+getMonth()+"&day="+getDay()+"&showbirthday="+getShowbirthday()+"&action="+getAction()+"&projectid="+getProjectid()+"");
$("#actionagenda_ical_link").attr("href","/public/agenda/agendaexport.php?format=ical&type=ActionAgenda&exportkey=dolibarr&token="+getToken()+"&status="+getStatus()+"&userasked="+getUserasked()+"&usertodo="+getUsertodo()+"&userdone="+getUserdone()+"&year="+getYear()+"&month="+getMonth()+"&day="+getDay()+"&showbirthday="+getShowbirthday()+"&action="+getAction()+"&projectid="+getProjectid()+"");
$("#actionagenda_vcal_link").attr("href","/public/agenda/agendaexport.php?format=vcal&type=ActionAgenda&exportkey=dolibarr&token="+getToken()+"&status="+getStatus()+"&userasked="+getUserasked()+"&usertodo="+getUsertodo()+"&userdone="+getUserdone()+"&year="+getYear()+"&month="+getMonth()+"&day="+getDay()+"&showbirthday="+getShowbirthday()+"&action="+getAction()+"&projectid="+getProjectid()+"");
</script>
';
*/

llxFooter();

$db->close();


/**
 * Show event of a particular day
 *
 * @param	DoliDB	$db              Database handler
 * @param   int		$day             Day
 * @param   int		$month           Month
 * @param   int		$year            Year
 * @param   int		$monthshown      Current month shown in calendar view
 * @param   string	$style           Style to use for this day
 * @param   array	$eventarray      Array of events
 * @param   int		$maxprint        Nb of actions to show each day on month view (0 means no limit)
 * @param   int		$maxnbofchar     Nb of characters to show for event line
 * @param   string	$newparam        Parameters on current URL
 * @param   int		$showinfo        Add extended information (used by day and week view)
 * @param   int		$minheight       Minimum height for each event. 60px by default.
 * @return	void
 */
function getIDCongratulatorOnRegionID($region, $responsibility){//Визначаю корис
    global $db;
    $sql = "select llx_user.rowid from llx_user_regions
        inner join llx_user on llx_user.rowid = llx_user_regions.fk_user
        where fk_id = $region
        and llx_user_regions.active = 1
        and llx_user.active = 1
        and (llx_user.respon_id in (".implode(',', $responsibility).") or llx_user.respon_id2 in (".implode(',', $responsibility)."))
        order by llx_user_regions.dtChange desc
        limit 1";
    $res = $db->query($sql);
    if($res->num_rows > 0){
        $obj = $db->fetch_object($res);
        return $obj->rowid;
    }else
        return 0;
}
function save_resultaction($rowid, $createaction = false, $action_id = null){
    global $user, $db;
//    var_dump($_REQUEST);
//    die();
    $socid = $_REQUEST['socid'];
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

//        echo '<pre>';
//        var_dump($_REQUEST['action'], $socid);
//        echo '</pre>';
//        die();
        $sql .= $user->id.(empty($_REQUEST['proposed_id'])?'':(','.($_REQUEST['action']=='setNotInterestingProposed'?0:1))).")";

    }else {
        $sql = "select socid from `llx_societe_action` where rowid = ".$rowid;
        $res = $db->query($sql);
        $obj = $db->fetch_object($res);
        $socid = $obj->socid;
        require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
        $societe = new Societe($db);
        $societe->fetch($socid);
        $societe->save_societe_need($_REQUEST['result_of_action']);

        $sql = 'update llx_societe_action set ';
        $sql.='`contactid`='.(empty($_REQUEST['contactid'])?'null':$_REQUEST['contactid']).', ';
        $sql.='`callstatus`='.(empty($newdate)?(empty($_REQUEST['callstatus'])?'null':$_REQUEST['callstatus']):'null').', ';
        $sql.='`said`='.(empty($_REQUEST['said'])?'null':"'".$db->escape($_REQUEST['said'])."'").', ';
        $sql.='`answer`='.(empty($_REQUEST['answer'])?'null':"'".$db->escape($_REQUEST['answer'])."'").', ';
        $sql.='`argument`='.(empty($_REQUEST['argument'])?'null':"'".$db->escape($_REQUEST['argument'])."'").', ';
        $sql.='`said_important`='.(empty($_REQUEST['said_important'])?'null':"'".$db->escape($_REQUEST['said_important'])."'").', ';
        $sql.='`result_of_action`='.(empty($_REQUEST['result_of_action'])?'null':"'".$db->escape($_REQUEST['result_of_action'])."'").', ';
        $sql.='`work_before_the_next_action`='.(empty($_REQUEST['work_before_the_next_action'])?'null':"'".$db->escape($_REQUEST['work_before_the_next_action'])."'").', ';
        $need = [];
        for($i=0; $i<count($_REQUEST['need']); $i++){
            if(!empty($_REQUEST['need'][$i]))
                $need[]=$_REQUEST['productsname'][$i].': '.$_REQUEST['need'][$i];
        }
        $sql.='`need`="'.($db->escape(implode(';', $need))).'", ';
        $sql.='`fact_cost`='.(empty($_REQUEST['fact_cost'])?'null':$db->escape($_REQUEST['fact_cost'])).', ';
        $sql.='`id_usr`='.$user->id.' ';
//        if(!empty($_REQUEST['proposed_id'])){
//            $sql.=',`interesting`='.($_REQUEST['action']=='setNotInterestingProposed'?0:1);
//        }
//        $sql.='`new`=1 ';
        $sql.=' where rowid='.$rowid;
    }
//    die($sql);
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }

    if(isset($_REQUEST['actioncode'])&&$_REQUEST['actioncode']=='AC_TEL'&&!empty($_REQUEST["actionid"])){//Незважаючи на те, дозвонився АТ чи ні, після збереження результатів виконання дзвінка встановлюю що дзвінок виконано
        $sql = "update llx_actioncomm set percent = 100 where id=".$_REQUEST["actionid"];
        $res = $db->query($sql);
        if(!$res){
            dol_print_error($db);
        }
    }
    if(!empty($rowid))
        die($rowid);
    else{
        $sql = "select max(rowid) rowid from llx_societe_action where id_usr = ".$user->id;
        $res = $db->query($sql);
        $obj = $db->fetch_object($res);
        die($obj->rowid);
    }
}

function show_day_events($db, $day, $month, $year, $monthshown, $style, &$eventarray, $maxprint=0, $maxnbofchar=16, $newparam='', $showinfo=0, $minheight=60)
{
    global $user, $conf, $langs;
    global $action, $filter, $filtert, $status, $actioncode;	// Filters used into search form
    global $theme_datacolor;
    global $cachethirdparties, $cachecontacts, $cacheusers, $colorindexused;

    print "\n".'<div id="dayevent_'.sprintf("%04d",$year).sprintf("%02d",$month).sprintf("%02d",$day).'" class="dayevent">';

    // Line with title of day
    $curtime = dol_mktime(0, 0, 0, $month, $day, $year);
    print '<table class="nobordernopadding" width="100%">'."\n";

    print '<tr><td align="left" class="nowrap">';
    print '<a href="'.DOL_URL_ROOT.'/comm/action/index.php?';
    print 'action=show_day&day='.str_pad($day, 2, "0", STR_PAD_LEFT).'&month='.str_pad($month, 2, "0", STR_PAD_LEFT).'&year='.$year;
    print $newparam;
    print '">';
    if ($showinfo) print dol_print_date($curtime,'daytextshort');
    else print dol_print_date($curtime,'%d');
    print '</a>';
    print '</td><td align="right" class="nowrap">';
    if ($user->rights->agenda->myactions->create || $user->rights->agenda->allactions->create)
    {
    	$newparam.='&month='.str_pad($month, 2, "0", STR_PAD_LEFT).'&year='.$year;

        //$param='month='.$monthshown.'&year='.$year;
        $hourminsec='100000';
        print '<a href="'.DOL_URL_ROOT.'/comm/action/card.php?action=create&datep='.sprintf("%04d%02d%02d",$year,$month,$day).$hourminsec.'&backtopage='.urlencode($_SERVER["PHP_SELF"].($newparam?'?'.$newparam:'')).'">';
        print img_picto($langs->trans("NewAction"),'edit_add.png');
        print '</a>';
    }
    print '</td></tr>'."\n";

    // Line with td contains all div of each events
    print '<tr height="'.$minheight.'"><td valign="top" colspan="2" class="sortable" style="padding-bottom: 2px;">';
	print '<div style="width: 100%; position: relative;">';

    //$curtime = dol_mktime (0, 0, 0, $month, $day, $year);
    $i=0; $nummytasks=0; $numother=0; $numbirthday=0; $numical=0; $numicals=array();
    $ymd=sprintf("%04d",$year).sprintf("%02d",$month).sprintf("%02d",$day);

    $nextindextouse=count($colorindexused);	// At first run this is 0, so fist user has 0, next 1, ...
	//print $nextindextouse;

    foreach ($eventarray as $daykey => $notused)
    {
        $annee = date('Y',$daykey);
        $mois = date('m',$daykey);
        $jour = date('d',$daykey);
        if ($day==$jour && $month==$mois && $year==$annee)
        {
            foreach ($eventarray[$daykey] as $index => $event)
            {
                if ($i < $maxprint || $maxprint == 0 || ! empty($conf->global->MAIN_JS_SWITCH_AGENDA))
                {
					$keysofuserassigned=array_keys($event->userassigned);

                	$ponct=($event->date_start_in_calendar == $event->date_end_in_calendar);

                    // Define $color (Hex string like '0088FF') and $cssclass of event
                    $color=-1; $colorindex=-1;
       				if (in_array($user->id, $keysofuserassigned))
					{
						$nummytasks++; $cssclass='family_mytasks';

						if (empty($cacheusers[$event->userownerid]))
						{
							$newuser=new User($db);
							$newuser->fetch($event->userownerid);
							$cacheusers[$event->userownerid]=$newuser;
						}
						//var_dump($cacheusers[$event->userownerid]->color);

                    	// We decide to choose color of owner of event (event->userownerid is user id of owner, event->userassigned contains all users assigned to event)
                    	if (! empty($cacheusers[$event->userownerid]->color)) $color=$cacheusers[$event->userownerid]->color;
                    }
                    else if ($event->type_code == 'ICALEVENT')
                    {
                    	$numical++;
                    	if (! empty($event->icalname)) {
                    		if (! isset($numicals[dol_string_nospecial($event->icalname)])) {
                    			$numicals[dol_string_nospecial($event->icalname)] = 0;
                    		}
                    		$numicals[dol_string_nospecial($event->icalname)]++;
                    	}
                    	$color=$event->icalcolor;
                    	$cssclass=(! empty($event->icalname)?'family_ext'.md5($event->icalname):'family_other unmovable');
                    }
                    else if ($event->type_code == 'BIRTHDAY')
                    {
                    	$numbirthday++; $colorindex=2; $cssclass='family_birthday unmovable'; $color=sprintf("%02x%02x%02x",$theme_datacolor[$colorindex][0],$theme_datacolor[$colorindex][1],$theme_datacolor[$colorindex][2]);
                    }
                    else
                 	{
                 		$numother++; $cssclass='family_other';

						if (empty($cacheusers[$event->userownerid]))
						{
							$newuser=new User($db);
							$newuser->fetch($event->userownerid);
							$cacheusers[$event->userownerid]=$newuser;
						}
						//var_dump($cacheusers[$event->userownerid]->color);

                    	// We decide to choose color of owner of event (event->userownerid is user id of owner, event->userassigned contains all users assigned to event)
                    	if (! empty($cacheusers[$event->userownerid]->color)) $color=$cacheusers[$event->userownerid]->color;
                 	}
                    if ($color == -1)	// Color was not forced. Set color according to color index.
                    {
                    	// Define color index if not yet defined
                    	$idusertouse=($event->userownerid?$event->userownerid:0);
                    	if (isset($colorindexused[$idusertouse]))
                    	{
                    		$colorindex=$colorindexused[$idusertouse];	// Color already assigned to this user
                    	}
                    	else
                    	{
                   			$colorindex=$nextindextouse;
                   			$colorindexused[$idusertouse]=$colorindex;
                    		if (! empty($theme_datacolor[$nextindextouse+1])) $nextindextouse++;	// Prepare to use next color
                    	}
                    	//print '|'.($color).'='.($idusertouse?$idusertouse:0).'='.$colorindex.'<br>';
						// Define color
                    	$color=sprintf("%02x%02x%02x",$theme_datacolor[$colorindex][0],$theme_datacolor[$colorindex][1],$theme_datacolor[$colorindex][2]);
                  	}
                    $cssclass=$cssclass.' '.$cssclass.'_day_'.$ymd;

                    // Defined style to disable drag and drop feature
                    if ($event->type_code =='AC_OTH_AUTO')
                    {
                        $cssclass.= " unmovable";
                    }
                    else if ($event->date_end_in_calendar && date('Ymd',$event->date_start_in_calendar) != date('Ymd',$event->date_end_in_calendar))
                    {
                        $tmpyearend    = date('Y',$event->date_end_in_calendar);
                        $tmpmonthend   = date('m',$event->date_end_in_calendar);
                        $tmpdayend     = date('d',$event->date_end_in_calendar);
                        if ($tmpyearend == $annee && $tmpmonthend == $mois && $tmpdayend == $jour)
                        {
                            $cssclass.= " unmovable";
                        }
                    }
                    else $cssclass.= " movable";

                    $h=''; $nowrapontd=1;
                    if ($action == 'show_day')  { $h='height: 100%; '; $nowrapontd=0; }
                    if ($action == 'show_week') { $h='height: 100%; '; $nowrapontd=0; }

                    // Show rect of event
                    print "\n";
                    print '<!-- start event '.$i.' --><div id="event_'.$ymd.'_'.$i.'" class="event '.$cssclass.'"';
                    //print ' style="height: 100px;';
                    //print ' position: absolute; top: 40px; width: 50%;';
                    //print '"';
                    print '>';
                    print '<ul class="cal_event" style="'.$h.'">';	// always 1 li per ul, 1 ul per event
                    print '<li class="cal_event" style="'.$h.'">';
                    print '<table class="cal_event'.(empty($event->transparency)?'':' cal_event_busy').'" style="'.$h;
                    print 'background: #'.$color.'; background: -webkit-gradient(linear, left top, left bottom, from(#'.$color.'), to(#'.dol_color_minus($color,1).'));';
                    //if (! empty($event->transparency)) print 'background: #'.$color.'; background: -webkit-gradient(linear, left top, left bottom, from(#'.$color.'), to(#'.dol_color_minus($color,1).'));';
                    //else print 'background-color: transparent !important; background: none; border: 1px solid #bbb;';
                    print ' -moz-border-radius:4px;" width="100%"><tr>';
                    print '<td class="'.($nowrapontd?'nowrap ':'').'cal_event'.($event->type_code == 'BIRTHDAY'?' cal_event_birthday':'').'">';
                    if ($event->type_code == 'BIRTHDAY') // It's a birthday
                    {
                        print $event->getNomUrl(1,$maxnbofchar,'cal_event','birthday','contact');
                    }
                    if ($event->type_code != 'BIRTHDAY')
                    {
                        // Picto
                        if (empty($event->fulldayevent))
                        {
                            //print $event->getNomUrl(2).' ';
                        }

                        // Date
                        if (empty($event->fulldayevent))
                        {
                            //print '<strong>';
                            $daterange='';

                            // Show hours (start ... end)
                            $tmpyearstart  = date('Y',$event->date_start_in_calendar);
                            $tmpmonthstart = date('m',$event->date_start_in_calendar);
                            $tmpdaystart   = date('d',$event->date_start_in_calendar);
                            $tmpyearend    = date('Y',$event->date_end_in_calendar);
                            $tmpmonthend   = date('m',$event->date_end_in_calendar);
                            $tmpdayend     = date('d',$event->date_end_in_calendar);
                            // Hour start
                            if ($tmpyearstart == $annee && $tmpmonthstart == $mois && $tmpdaystart == $jour)
                            {
                                $daterange.=dol_print_date($event->date_start_in_calendar,'%H:%M');
                                if ($event->date_end_in_calendar && $event->date_start_in_calendar != $event->date_end_in_calendar)
                                {
                                    if ($tmpyearstart == $tmpyearend && $tmpmonthstart == $tmpmonthend && $tmpdaystart == $tmpdayend)
                                    $daterange.='-';
                                    //else
                                    //print '...';
                                }
                            }
                            if ($event->date_end_in_calendar && $event->date_start_in_calendar != $event->date_end_in_calendar)
                            {
                                if ($tmpyearstart != $tmpyearend || $tmpmonthstart != $tmpmonthend || $tmpdaystart != $tmpdayend)
                                {
                                    $daterange.='...';
                                }
                            }
                            // Hour end
                            if ($event->date_end_in_calendar && $event->date_start_in_calendar != $event->date_end_in_calendar)
                            {
                                if ($tmpyearend == $annee && $tmpmonthend == $mois && $tmpdayend == $jour)
                                $daterange.=dol_print_date($event->date_end_in_calendar,'%H:%M');
                            }
                            //print $daterange;
                            if ($event->type_code != 'ICALEVENT')
                            {
                                $savlabel=$event->libelle;
                                $event->libelle=$daterange;
                                print $event->getNomUrl(0);
                                $event->libelle=$savlabel;
                            }
                            else
                            {
                                print $daterange;
                            }
                            //print '</strong> ';
                            print "<br>\n";
                        }
                        else
						{
                            if ($showinfo)
                            {
                                print $langs->trans("EventOnFullDay")."<br>\n";
                            }
                        }

                        // Show title
                        if ($event->type_code == 'ICALEVENT') print dol_trunc($event->libelle,$maxnbofchar);
                        else print $event->getNomUrl(0,$maxnbofchar,'cal_event');

                        if ($event->type_code == 'ICALEVENT') print '<br>('.dol_trunc($event->icalname,$maxnbofchar).')';

                        // If action related to company / contact
                        $linerelatedto='';$length=16;
                        if (! empty($event->societe->id) && ! empty($event->contact->id)) $length=round($length/2);
                        if (! empty($event->societe->id) && $event->societe->id > 0)
                        {
                            if (! isset($cachethirdparties[$event->societe->id]) || ! is_object($cachethirdparties[$event->societe->id]))
                            {
                                $thirdparty=new Societe($db);
                                $thirdparty->fetch($event->societe->id);
                                $cachethirdparties[$event->societe->id]=$thirdparty;
                            }
                            else $thirdparty=$cachethirdparties[$event->societe->id];
                            if (! empty($thirdparty->id)) $linerelatedto.=$thirdparty->getNomUrl(1,'',$length);
                        }
                        if (! empty($event->contact->id) && $event->contact->id > 0)
                        {
                            if (! is_object($cachecontacts[$event->contact->id]))
                            {
                                $contact=new Contact($db);
                                $contact->fetch($event->contact->id);
                                $cachecontacts[$event->contact->id]=$contact;
                            }
                            else $contact=$cachecontacts[$event->contact->id];
                            if ($linerelatedto) $linerelatedto.=' / ';
                            if (! empty($contact->id)) $linerelatedto.=$contact->getNomUrl(1,'',$length);
                        }
                        if ($linerelatedto) print '<br>'.$linerelatedto;
                    }

                    // Show location
                    if ($showinfo)
                    {
                        if ($event->location)
                        {
                            print '<br>';
                            print $langs->trans("Location").': '.$event->location;
                        }
                    }

                    print '</td>';
                    // Status - Percent
                    print '<td align="right" class="nowrap">';
                    if ($event->type_code != 'BIRTHDAY' && $event->type_code != 'ICALEVENT') print $event->getLibStatut(3,1);
                    else print '&nbsp;';
                    print '</td></tr></table>';
                    print '</li>';
                    print '</ul>';
                    print '</div><!-- end event '.$i.' -->'."\n";
                    $i++;
                }
                else
                {
                	print '<a href="'.DOL_URL_ROOT.'/comm/action/index.php?action='.$action.'&maxprint=0&month='.$monthshown.'&year='.$year;
                    print ($status?'&status='.$status:'').($filter?'&filter='.$filter:'');
                    print ($filtert?'&filtert='.$filtert:'');
                    print ($actioncode!=''?'&actioncode='.$actioncode:'');
                    print '">'.img_picto("all","1downarrow_selected.png").' ...';
                    print ' +'.(count($eventarray[$daykey])-$maxprint);
                    print '</a>';
                    break;
                    //$ok=false;        // To avoid to show twice the link
                }
            }

            break;
        }
    }
    if (! $i) print '&nbsp;';

    if (! empty($conf->global->MAIN_JS_SWITCH_AGENDA) && $i > $maxprint && $maxprint)
    {
        print '<div id="more_'.$ymd.'">'.img_picto("all","1downarrow_selected.png").' +'.$langs->trans("More").'...</div>';
        //print ' +'.(count($eventarray[$daykey])-$maxprint);
        print '<script type="text/javascript">'."\n";
        print 'jQuery(document).ready(function () {'."\n";
        print 'jQuery("#more_'.$ymd.'").click(function() { reinit_day_'.$ymd.'(); });'."\n";

        print 'function reinit_day_'.$ymd.'() {'."\n";
        print 'var nb=0;'."\n";
        // TODO Loop on each element of day $ymd and start to toggle once $maxprint has been reached
        print 'jQuery(".family_mytasks_day_'.$ymd.'").toggle();';
        print '}'."\n";

        print '});'."\n";

        print '</script>'."\n";
    }

    print '</div>';
    print '</td></tr>';

    print '</table></div>'."\n";
}


/**
 * Change color with a delta
 *
 * @param	string	$color		Color
 * @param 	int		$minus		Delta
 * @return	string				New color
 */
function dol_color_minus($color, $minus)
{
	$newcolor=$color;
	$newcolor[0]=((hexdec($newcolor[0])-$minus)<0)?0:dechex((hexdec($newcolor[0])-$minus));
	$newcolor[2]=((hexdec($newcolor[2])-$minus)<0)?0:dechex((hexdec($newcolor[2])-$minus));
	$newcolor[4]=((hexdec($newcolor[4])-$minus)<0)?0:dechex((hexdec($newcolor[4])-$minus));
	return $newcolor;
}
