<?php
/* Copyright (C) 2002-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2011	   Juanjo Menent        <jmenent@2byte.es>
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
 *       \file       htdocs/comm/action/class/actioncomm.class.php
 *       \ingroup    agenda
 *       \brief      File of class to manage agenda events (actions)
 */
require_once DOL_DOCUMENT_ROOT.'/comm/action/class/cactioncomm.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';


/**
 *		Class to manage agenda events (actions)
 */
class ActionComm extends CommonObject
{
    public $element='action';
    public $table_element = 'actioncomm';
    public $table_rowid = 'id';
    protected $ismultientitymanaged = 1;	// 0=No test on entity, 1=Test with field entity, 2=Test with link by societe

    /**
     * Id of the event
     * @var int
     */
    var $id;

    /**
     * Id of the event. Use $id as possible
     * @var int
     */
    public $ref;

    var $type_id;		// Id into parent table llx_c_actioncomm (used only if option to use type is set)
    var $type_code;		// Code into parent table llx_c_actioncomm (used only if option to use type is set). With default setup, should be AC_OTH_AUTO or AC_OTH.
    var $type;			// Label into parent table llx_c_actioncomm (used only if option to use type is set)
    var $type_color;	// Color into parent table llx_c_actioncomm (used only if option to use type is set)
    var $code;			// Free code to identify action. Ie: Agenda trigger add here AC_TRIGGERNAME ('AC_COMPANY_CREATE', 'AC_PROPAL_VALIDATE', ...)
    var $typeSetOfDate; //Вариант встановлення початку та кінця виконання дії. Якщо null - автоматично. Якщо w - вручну

    var $label;

    /**
     * @var string
     * @deprecated Use $label
     */
    public $libelle;

    var $datec;			// Date creation record (datec)
    var $datem;			// Date modification record (tms)
    var $dateconfirm;   // Date confirm action

    /**
     * Object user that create action
     * @var User
     * @deprecated
     */
    var $author;

    /**
     * Object user that modified action
     * @var User
     * @deprecated
     */
    var $usermod;
    var $authorid;		// Id user that create action
    var $usermodid;		// Id user that modified action

    var $datep;			// Date action start (datep)
    var $datef;			// Date action end (datep2)
    var $datepreperform;
    /**
     * @var int -1=Unkown duration
     * @deprecated
     */
    var $durationp = -1;
    var $fulldayevent = 0;    // 1=Event on full day

    /**
     * Milestone
     * @var int
     * @deprecated Milestone is already event with end date = start date
     */
    var $punctual = 1;
    var $percentage = -1;    // Percentage
    var $location;      // Location
    var $period;        //Period ReExecution
    var $parent_id;     //Parent action id
    var $order_id;      //Linked order
    var $groupoftask;   //GroupOfTask
    var $entity = 1;        //Використовую для визначення базової(первинної) дії
    var $calc = 1; //Використовується для обчислення план дні де 1 позначені дії, які виконувались за місяць до поточної дати
// $conf->entity використовується, коли є декілька компаній. Тут буде використовуватись, 1 - коли створена головна дія. 0 - це піддія

	var $transparency;	// Transparency (ical standard). Used to say if people assigned to event are busy or not by event. 0=available, 1=busy, 2=busy (refused events)
    var $priority;      // Small int (0 By default)
    var $note;          // Description
    var $confirmdoc;          // Description

	var $userassigned = array();	// Array of user ids
    var $userownerid;		// Id of user owner
    var $userdoneid;	// Id of user done
    var $resultaction = array();
    var $typenotification;//Type notification

    /**
     * Object user of owner
     * @var User
     * @deprecated
     */
    var $usertodo;

    /**
     * Object user that did action
     * @var User
     * @deprecated
     */
    var $userdone;

    var $socid;
    var $contactid;
    var $callstatus;

    /**
     * Company linked to action (optional)
     * @var Societe|null
     */
    var $societe;

    /**
     * Contact linked to action (optional)
     * @var Contact|null
     */
    var $contact;

    //Піддії, що пов'язані з завданням. Наприклад відправка розсилки
    var $subaction;
    var $subaction_id;
    //Мотиватор-демотиватор
    var $motivator;
    var $demotivator;
    //Фахівець-оцінщик
    var $user_valuer;
    //Витрати
    var $planed_cost;
    var $fact_cost;
    /**
     * Id of project (optional)
     * @var int
     */
    var $fk_project;

    // Properties for links to other objects
    var $fk_element;    // Id of record
    var $elementtype;   // Type of record. This if property ->element of object linked to.

    // Ical
    var $icalname;
    var $icalcolor;

    var $actions=array();


    /**
     *      Constructor
     *
     *      @param		DoliDB		$db      Database handler
     */
    function __construct($db)
    {
        $this->db = $db;

        $this->societe = new stdClass();	// deprecated
        $this->contact = new stdClass();	// deprecated
    }

    /**
     *    Add an action/event into database.
     *    $this->type_id OR $this->type_code must be set.
     *
     *    @param	User	$user      		Object user making action
     *    @param    int		$notrigger		1 = disable triggers, 0 = enable triggers
     *    @return   int 		        	Id of created event, < 0 if KO
     */
    function GetLastAction($action_id, $name){
        global $db;
        $sql = 'select id, datep from llx_actioncomm
        inner join (select fk_parent rowid from llx_actioncomm where id='.$action_id.') parent on parent.rowid=llx_actioncomm.id
        where active = 1';
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
        $result = $db->fetch_array($res);
//        var_dump($sql);
//        die();
        return $result[$name];
    }
    function getDatesByPeriod($dayofweek, $period){
        $daysofweek=['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        $dayindex = array_search($dayofweek, $daysofweek);
        $date_tmp = $period[0];
//        echo $dayindex.'</br>';
        $out = [];
//        echo '<pre>';
        while($date_tmp<=$period[1]){
//            var_dump($date_tmp, date_format($date_tmp,'w')==$dayindex, date_format($date_tmp,'w'));
            if(date_format($date_tmp,'w') == $dayindex) {
                $item = new DateTime(date_format($date_tmp,'Y-m-d H:i:s'));
                $out[] = $item;
            }
            date_add($date_tmp, date_interval_create_from_date_string('1 days'));
        }
//        echo '</pre>';
        return $out;
    }
    function getAuthorID($action_id){
        global $db;
        $sql = "select fk_user_author,note from llx_actioncomm where id = ".$action_id;
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
        $obj = $db->fetch_object($res);
        return $obj->fk_user_author;
    }
    function getGroupActions($action_id){
    $actions = array($action_id);
    global $db;
    $sql = "select fk_user_author,note from llx_actioncomm where id = ".$action_id;
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    $id_usr = $obj->fk_user_author;
    $task = $obj->note;
    $direction = array(-1,1);
    foreach ($direction as $item) {
        $tmp_id = $action_id+$item;
        $result = true;
        while ($result) {
            $sql = "select fk_user_author,note from llx_actioncomm where id = " . $tmp_id.' and active = 1';
            $res = $db->query($sql);
            if (!$res)
                dol_print_error($db);
            $obj = $db->fetch_object($res);
            $result = ($id_usr == $obj->fk_user_author && $task == $obj->note);
            if ($result) {
                $actions[] = $tmp_id;
                $tmp_id=$tmp_id+$item;
            }
        }
    }
    return $actions;
}    
    function GetFutureActionDate($action_id){
        $last_actionID = max($this->GetChainActions($action_id));
        global $db;
        $sql = "select datep2 from llx_actioncomm where id = ".$last_actionID;
        $res = $db->query($sql);
        if(!$res)
            $db->lasterror();
        $obj = $db->fetch_object($res);
        if(!empty($obj->datep2)) {
            $date = new DateTime($obj->datep2);
            return $date->format('Y.m.d H:i:s');
        }else
            return 'null';
    }
    function getAuthorIDLastResultAction($action_id){
        global $db;
        $sql = "select id_usr from llx_societe_action where `llx_societe_action`.`action_id` = $action_id and active = 1 order by dtChange desc limit 1";
        $res = $db->query($sql);
        if($res->num_rows == 0)
            return 0;
        $obj = $db->fetch_object($res);
        return $obj->id_usr;
    }
    function GetChainActions($action_id){
        if(empty($action_id))
            return array(0);
        $chain_actions = array();
        $chain_actions[]=$action_id;

        //Завантажую всі батьківські ІД
        while($action_id = $this->GetLastAction($action_id, 'id')){
            array_unshift($chain_actions, $action_id);
        }
        //Завантажую всі наступні ІД
        while($tmp_ID = $this->GetNextAction($chain_actions, 'id')){
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
//        var_dump($chain_actions);
//        die();
        return $chain_actions;
    }
    function validateDateAction($date, $id_usr, $minutes, $prioritet){
        $valid_date = new DateTime($date);

        $mk_valid_begin = dol_mktime($valid_date->format('H'),$valid_date->format('i'),$valid_date->format('s'),$valid_date->format('m'),$valid_date->format('d'),$valid_date->format('Y'));
        $mk_valid_end = $mk_valid_begin+$minutes*60;
//            echo '<pre>';
//            var_dump($valid_date);
//            var_dump($mk_valid_date);
//            echo '</pre>';
//            die();
        $freetime = $this->GetFreeTimePeriod($date, $id_usr, $prioritet, true);
//            echo '<pre>';
//            var_dump($freetime);
//            echo '</pre>';
//            die();
        foreach($freetime as $period) {
            $begin = new DateTime($period[2].' '.$period[0]);
            $mk_begin = dol_mktime($begin->format('H'),$begin->format('i'),$begin->format('s'),$begin->format('m'),$begin->format('d'),$begin->format('Y'));
            $mk_end = $mk_begin+$period[1]*60;
            if($mk_valid_begin>=$mk_begin&&$mk_valid_end<=$mk_end)
                return 1;
//            echo '<pre>';
//            var_dump($mk_valid_date-$mk_begin);
//            echo '</pre>';
//            die();
        }
        return 0;
    }

    function Received_Action($action_id)
    {
        global $db, $user;
        $out = [];
        if(substr($action_id, 0, 1) != '_') {
            $sql = "select code from llx_actioncomm where id = ".$action_id;
            $res = $db->query($sql);
            if (!$res) {
                var_dump($sql);
                dol_print_error($db);
            }
            $obj = $db->fetch_object($res);
            $out['code']= $obj->code;
            $out['id']= $action_id;
            $sql = 'update llx_actioncomm set `dateconfirm` = case when `dateconfirm` is null then Now() else `dateconfirm` end, `new`=0, `percent`= case when `percent` = -1 then 0 else `percent` end  where id=' . $action_id;
//	die($sql);
            $res = $db->query($sql);
            if (!$res) {
                var_dump($sql);
                dol_print_error($db);
            }
            $sql = 'update llx_societe_action set `new`=0  where action_id=' . $action_id;
//	die($sql);
            $res = $db->query($sql);
            if (!$res) {
                var_dump($sql);
                dol_print_error($db);
            }
        }else{
            $action_id = -substr($action_id, 1);
            $sql = "select llx_actioncomm.fk_soc socid, action_id, llx_societe_action.socid socid2 from llx_societe_action
              inner join llx_actioncomm on llx_actioncomm.id = llx_societe_action.action_id 
              where rowid =".abs($action_id);
//            die($sql);
            $res = $db->query($sql);
            if (!$res) {
                var_dump($sql);
                dol_print_error($db);
            }
            $obj = $db->fetch_object($res);
            $out['socid']= $obj->socid;
            $out['id']= $obj->action_id;
            if($obj->socid != $obj->socid2){
                $sql = 'update llx_societe_action set socid='.$obj->socid.' where rowid = '.abs($action_id);
                $res = $db->query($sql);
                if (!$res) {
                    var_dump($sql);
                    dol_print_error($db);
                }
            }
        }
        $sql = "delete from llx_newactions where id_usr = $user->id and id_action = $action_id";
        $res = $db->query($sql);
        if (!$res) {
            var_dump($sql);
            dol_print_error($db);
        }
        $out['result'] = 1;
        return json_encode($out);
//        echo json_encode($out);
    }
    function getAssignedUser($action_id, $arrayonly = false){
        global $db;
        if(empty($action_id))
            $action_id = $this->id;
        $chain_actions = $this->GetChainActions($action_id);
        $users_id = array();

        //Завантажую id користувачів, які пов'язані з діями
        $sql = "select llx_actioncomm.fk_user_author, llx_actioncomm.fk_user_action, `llx_actioncomm_resources`.`fk_element`from llx_actioncomm
            left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = llx_actioncomm.id
            where llx_actioncomm.id in (".implode(',', $chain_actions).")";
//        var_dump($sql);
//        die();
        $res = $db->query($sql);
        while ($obj = $db->fetch_object($res)){
            if(!in_array($obj->fk_user_author, $users_id))
                $users_id[]= $obj->fk_user_author;
            if(!empty($obj->fk_user_action)&&$obj->fk_user_action!=$obj->fk_user_author)
                $users_id[]= $obj->fk_user_action;
            if(!empty($obj->fk_element)&&$obj->fk_element!=$obj->fk_user_author)
                $users_id[]= $obj->fk_element;             
        }
        //Завантажую id користувачів, які пов'язані з результатами перемовин
        $sql = "select id_usr from `llx_societe_action` where action_id in(".implode(',', $chain_actions).") and active = 1";
        $res = $db->query($sql);
        while ($obj = $db->fetch_object($res)){
            if(!in_array($obj->id_usr, $users_id))
                $users_id[]= $obj->id_usr;
        }

        if($arrayonly)
            return $users_id;

        $sql = "select  llx_user.rowid, llx_user.lastname, llx_user.firstname from llx_user            
            where rowid in (".implode(',', $users_id).")
            order by lastname, firstname";

        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
        $out = '<select id="assignUser" class="flat" name="assignUser">';
        $out.='<option value="-1">Необхідно вибрати</option>';
        while($obj = $db->fetch_object($res)){
            $out.='<option value="'.$obj->rowid.'">'.$obj->lastname.' '.$obj->firstname.'</option>';
        }
        $out.='</select>';
        return $out;
    }
    function GetNextAction($actions_id, $name){
        foreach ($actions_id as $item =>$value){
            if(empty($value))
                unset($actions_id[$item]);
        }
        if(empty($actions_id))
            return array(0);
        global $db;
        $sql = 'select id, datep from llx_actioncomm
        where fk_parent in('.implode(',',$actions_id).')';
        $sql.=' and active = 1';
//        echo '<pre>';
//        var_dump($sql);
//        echo '</pre>';
//        die();
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
        $out = array();
        while($result = $db->fetch_array($res))
            $out[] = $result[$name];
        return $out;
    }
    function GetExecTime($code){
        global $db;

        if(!is_numeric(substr($code, 0, 1)))
            $sql = "select exec_time from llx_c_actioncomm where active = 1 and code='".$code."' limit 1";
        else
            $sql = "select exec_time from llx_c_actioncomm where id='".$code."' limit 1";
        $res = $db->query($sql);
        if(!$res){
            var_dump($sql);
            dol_print_error($db);
        }
        $obj = $db->fetch_object($res);
        $exec_time = $obj->exec_time;
        if(empty($exec_time))
            $exec_time = 0;
        return $exec_time;
    }
    function GetFreeTime($inputdate, $id_usr, $minutes, $prioritet = 0){
        if(empty($prioritet))$prioritet = 0;
//        var_dump(substr_count($inputdate, ':'));
//        die();
        if(substr_count($inputdate, ':') == 1)
            $starttime = $inputdate.":00";
        $date = new DateTime($inputdate);

        $PlanTime = 0;
        while(!$PlanTime) {
            if($date->format('w')>0 && $date->format('w')<6)
                $PlanTime = $this->GetFirstFreeTime($date->format('Y-m-d H:i'), $id_usr, $minutes, $prioritet, $starttime);
            $date = new DateTime(date('Y-m-d', mktime(8,0,0,$date->format('m'),$date->format('d'),$date->format('Y'))+ 86400));
        }
//        var_dump($PlanTime);
//        die();
        return $PlanTime;
    }
    function GetFirstFreeTime($date, $id_usr, $minutes, $prioritet = 0, $starttime){
        $freetime = $this->GetFreeTimePeriod($date, $id_usr, $prioritet);
        $date = new DateTime($date);
//        echo '<pre>';
//        var_dump($freetime);
//        echo '</pre>';
//        die();
        if(empty($starttime))
             $starttime = time();
        else{
            $dtStartTime = new DateTime($starttime);
            $starttime = dol_mktime($dtStartTime->format('H'),$dtStartTime->format('i'),$dtStartTime->format('s'),$dtStartTime->format('m'),$dtStartTime->format('d'),$dtStartTime->format('Y'));
//            var_dump($starttime);
//            die();
        }
        if($starttime<time()) {
            $starttime = time();
        }
        $num = 0;
        foreach($freetime as $period){

            $num++;
            $nexttime = 0;
            if(isset($freetime[$num])){
                $nextjob = $freetime[$num];
                $nexttime = dol_mktime(intval(substr($nextjob[0], 0,2)), intval(substr($nextjob[0], 3,2)), intval(substr($nextjob[0], 6,2)), intval(substr($nextjob [2], 6,2)), intval(substr($nextjob[2], 8,2)), intval(substr($nextjob[2], 0,4)));
            }


            $itemDate = dol_mktime(intval(substr($period[0], 0,2)), intval(substr($period[0], 3,2)), intval(substr($period[0], 6,2)), intval(substr($period[2], 5,2)), intval(substr($period[2], 8,2)), intval(substr($period[2], 0,4)));
            $dtDate = new DateTime();
//            var_dump($period, intval(substr($period[0], 0,2)), intval(substr($period[0], 3,2)), intval(substr($period[0], 6,2)), intval(substr($period[2], 5,2)), intval(substr($period[2], 8,2)), intval(substr($period[2], 0,4)));
//            die();
            $dtDate->setTimestamp($itemDate);
//            var_dump($minutes<=$period[1] && ($itemDate >= $starttime || $num == count($freetime)) && $dtDate->format('H')>=8 && $dtDate->format('H')<=18 &&
//                ($dtDate->format('H')>=12&& $dtDate->format('H')<14  && $dtDate->format('Y-m-d') == $date->format('Y-m-d')));
//            die();
            if($minutes<=$period[1] && ($itemDate >= $starttime || count($freetime) == $num) && $dtDate->format('H')>=8 && !( $dtDate->format('H')>=12&& $dtDate->format('H')<14) && $dtDate->format('Y-m-d') == $date->format('Y-m-d')) {
//                var_dump($itemDate >= $starttime);
                $tmp_date = new DateTime($period[2].' '.$period[0]);
                $mk_tmp_date = dol_mktime($tmp_date->format('H'),$tmp_date->format('i'),$tmp_date->format('s'),$tmp_date->format('m'),$tmp_date->format('d'),$tmp_date->format('Y'));
//                var_dump($num, $period[2].' '.$period[0]);
//                die('test');
                if($mk_tmp_date>=$starttime) {
//                    die($period[2] . ' ' . $period[0]);

                    return $period[2] . ' ' . $period[0];
                }
            }
            if($minutes<=$period[1] && ($itemDate >= $starttime || $num == count($freetime)) && $dtDate->format('H')>=8 && $dtDate->format('H')<=18 &&
                ($dtDate->format('H')>=12&& $dtDate->format('H')<14  && $dtDate->format('Y-m-d') == $date->format('Y-m-d'))){
                $tmp_date = new DateTime($period[2].' 14:00:00');
                $mk_tmp_date = dol_mktime($tmp_date->format('H'),$tmp_date->format('i'),$tmp_date->format('s'),$tmp_date->format('m'),$tmp_date->format('d'),$tmp_date->format('Y'));
//                var_dump( $itemDate+$period[1]*60-$mk_tmp_date);
//                die('test');
                if($mk_tmp_date>=$starttime && $minutes<=$itemDate+$period[1]*60-$mk_tmp_date ) {
//                    die($period[2] . ' 14:00:00');
                    return $period[2] . ' 14:00:00';
                }
            }elseif($minutes<=$period[1]) {
                $tmp_date = new DateTime($period[2].' '.$period[0]);

                $mk_tmp_date = dol_mktime($tmp_date->format('H'),$tmp_date->format('i'),$tmp_date->format('s'),$tmp_date->format('m'),$tmp_date->format('d'),$tmp_date->format('Y'));
                $mk_endperiod = $mk_tmp_date+$period[1]*60;

                //Час початку наступного вільного періоду
                $next_period = $freetime[$num];
                $next_date = new DateTime($next_period[2].' '.$next_period[0]);
                $mk_next_date = dol_mktime($next_date->format('H'),$next_date->format('i'),$next_date->format('s'),$next_date->format('m'),$next_date->format('d'),$next_date->format('Y'));
                if($mk_tmp_date<=$starttime&&$mk_next_date>$starttime&&($starttime+$minutes*60)<=$mk_endperiod){
                    return date('Y-m-d H:i:s', $starttime);
                }
            }elseif($dtDate->format('H')>=14){
                $tmp_date = new DateTime($period[2].' '.$period[0]);
                $mk_tmp_date = dol_mktime($tmp_date->format('H'),$tmp_date->format('i'),$tmp_date->format('s'),$tmp_date->format('m'),$tmp_date->format('d'),$tmp_date->format('Y'));
//                var_dump(date('Y-m-d H:i:s', $mk_tmp_date));
//                die('test');
                if($mk_tmp_date>=$starttime) {
//                    die($period[2] . ' ' . $period[0]);
                    return $period[2] . ' ' . $period[0];
                }
                if($minutes<=($nexttime - $starttime)/60 && $starttime < $nexttime){
                    return date('Y-m-d H:i:s', $starttime);
                }
            }

            if($nexttime == 0 || ($minutes<=$period[1] && $minutes<=($nexttime - $starttime)/60 && $starttime < $nexttime && ($dtDate->format('H')<12&& $dtDate->format('H')>=14))){ //Виконується, коли до наступної дії є час чи дія перша на сьогодні
                $tmp_date = new DateTime($period[2].' '.$period[0]);
                $mk_endperiod = dol_mktime($tmp_date->format('H'),$tmp_date->format('i'),$tmp_date->format('s'),$tmp_date->format('m'),$tmp_date->format('d'),$tmp_date->format('Y'))+$period[1]*60;
//                var_dump(($mk_endperiod-$starttime)/60);
                if(date('H', $starttime)==0 && date('i', $starttime) == 0 && date('s', $starttime) == 0){
//                    var_dump(date('Y-m-d 08:i:s', $starttime));
//                    die('test');
                    return date('Y-m-d 08:i:s', $starttime);
                }
                if($minutes<=($mk_endperiod-$starttime)/60) {
                    return date('Y-m-d H:i:s', $starttime);
                }

            }
//            var_dump()
//            if($minutes<=$period[1] && $nexttime == 0){ //Коли після останньої дії є вільний час до кінця робочого дня
//                return  $period[2].' '.$period[0];
//            }
        }
//        var_dump($itemDate, $dtDate, $date);
//        die();
        if($minutes<=$period[1] && $itemDate < $starttime &&!empty($dtDate)&&$dtDate->format('H')>=8 && $dtDate->format('H')<=18 && $dtDate->format('Y-m-d') == $date->format('Y-m-d')) {
//            var_dump(date('Y-m-d H:i:s', $starttime));
//            die('test');
            return date('Y-m-d H:i:s', $starttime);
        }
        if($minutes<=$period[1] && $itemDate >= $starttime && $dtDate->format('H')>=8 && $dtDate->format('H')<=18 && $dtDate->format('Y-m-d') == $date->format('Y-m-d')){
//            var_dump($mk_tmp_date>=$starttime);
//            die('test');
            if($mk_tmp_date>=$starttime && $minutes<=$itemDate+$period[1]*60-$mk_tmp_date )
                return  $period[2].' '.$period[0];
        }
        return 0;
    }
    function GetFreeTimePeriod($date, $id_usr, $prioritet, $fullday = false){
        global $db;
        $date = new DateTime($date);
        $sql = "select `llx_actioncomm`.`id`, `llx_actioncomm`.`datep`, `llx_actioncomm`.`datep2` from `llx_actioncomm`
            left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = `llx_actioncomm`.id
            where `llx_actioncomm`.`datep` between '".$date->format('Y-m-d')."' and adddate('".$date->format('Y-m-d')."', interval 1 day)
            and fk_action in
              (select id from `llx_c_actioncomm`
              where `type` in ('system', 'user'))
                and (case when `llx_actioncomm_resources`.fk_element is null then `llx_actioncomm`.fk_user_author else `llx_actioncomm_resources`.fk_element end = ".$id_usr.")
            and `llx_actioncomm`.`priority` = ".(empty($prioritet)?0:$prioritet)."
            and `llx_actioncomm`.`active` = 1
            and (`llx_actioncomm`.hide is null or `llx_actioncomm`.hide <> 1)
            order by `llx_actioncomm`.`datep`, `llx_actioncomm`.`datep2`";

        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db); //and (`llx_actioncomm_resources`.`fk_element`= ".$id_usr." or (`llx_actioncomm`.`fk_user_author`= ".$id_usr." and `llx_actioncomm`.id not in (select `llx_actioncomm_resources`.`fk_actioncomm` from `llx_actioncomm_resources` where `llx_actioncomm_resources`.`fk_element`= ".$id_usr.")))
        $Now = new DateTime();
//        var_dump($sql);
//        die();
        if($Now<$date) {
            if(!$fullday)
                $time = mktime(8, 0, 0, $date->format('m'), $date->format('d'), $date->format('Y'));
            else
                $time = mktime(0, 0, 0, $date->format('m'), $date->format('d'), $date->format('Y'));
        }else
            $time = mktime($Now->format('H'),$Now->format('i'),$Now->format('s'),$date->format('m'),$date->format('d'),$date->format('Y'));

        $freetime = array();
        while($obj = $db->fetch_object($res)){
            $tmp_date = new DateTime($obj->datep);
            $tmp_mk = mktime($tmp_date->format('H'), $tmp_date->format('i'),$tmp_date->format('s'),$tmp_date->format('m'), $tmp_date->format('d'),$tmp_date->format('Y'));

            if(($tmp_mk - $time)/60>0) {
                $freetime[] = array(date('H.i.s', $time), ($tmp_mk - $time)/60, $date->format('Y-m-d'));
            }
            $tmp_date = new DateTime($obj->datep2);
            $time = mktime($tmp_date->format('H'), $tmp_date->format('i'),$tmp_date->format('s'),$tmp_date->format('m'), $tmp_date->format('d'),$tmp_date->format('Y'));
        }
        if(!$fullday)
            $tmp_mk = mktime(19,0,0,$date->format('m'),$date->format('d'),$date->format('Y'));
        else
            $tmp_mk = mktime(23,59,0,$date->format('m'),$date->format('d'),$date->format('Y'));
//        var_dump(($tmp_mk - $time));
//        die();
        if(($tmp_mk - $time)/60>0) {
            $freetime[] = array(date('H.i.s', $time), ($tmp_mk - $time)/60, $date->format('Y-m-d'));
        }
//        echo '<pre>';
//        var_dump($freetime);
//        echo '</pre>';
//        die();
        return $freetime;
    }

    function getFilterDate(){
        global $user, $db, $langs;
        $typeaction='AC_GLOBAL';
        if($_REQUEST["typeaction"]=='current_task')
            $typeaction='AC_CURRENT';
        $datetype = 'datep2';
        if($_REQUEST['datetype']=='prepareddate')
            $datetype = 'datepreperform';
        $sql = "select distinct date(".$datetype.") date from `llx_actioncomm`
            left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = `llx_actioncomm`.id
            where (`llx_actioncomm_resources`.`fk_element` = ".$user->id." or `llx_actioncomm`.`fk_user_author` = ".$user->id.")
            and `llx_actioncomm`.`code` = '".$typeaction."'
            and `llx_actioncomm`.`percent` <> 100
            and `llx_actioncomm`.`active` = 1
            order by date(".$datetype.")";
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
        $dates = array();
        if($db->num_rows($res)>0)
            while($obj = $db->fetch_object($res)){
                $dates[]=$obj->date;
            }
        $month = array();
        foreach($dates as $date){
            $date = new DateTime($date);
            $month['Month'.$date->format('m')][]=$date->format('d.m.Y');
        }
        $out='<form id="setDateFilter" action="" method="post">
                    <input id="dates" name="filterdates" type="hidden">
                    <input name="datetype" type="hidden" value="'.$_REQUEST['datetype'].'">
               </form>
        <table class="setdate" style="background: #ffffff; width: 80px">
        <a class="close"  onclick="CloseDatesMenu();" title="Закрити"></a>
            <thead><tr class="multiple_header_table"><th class="middle_size" colspan="3" style="width: 100%">Виберіть дату </th>
                </tr>
                </thead>
            <tbody><tr><td><div id="selDates" class="middle_size">';
        foreach(array_keys($month) as $key){
            $out.='<ul id="'.$key.'" class="month"><img id="img'.$key.'" src="/dolibarr/htdocs/theme/eldy/img/check.png"> '.$langs->trans($key).'</ul>';
            foreach($month[$key] as $date){
                $date = new DateTime($date);
                $out.='<li id="'.$date->format('d_m').'" class="dates '.$key.'" > <img id="date'.$date->format('d_m').'" src="/dolibarr/htdocs/theme/eldy/img/check.png" dateVal="'."'".$date->format('Y-m-d')."'".'"> '.$date->format('d.m.').'</li>';
            }
        }
        $out.='</div></td></tr>';
        $out.='<tr><td><button onclick="setDateFilter();">Застосувати</button></tr></tbody></table>';
        $out.="<script>
            function setDateFilter(){
                var imgs = $('div#selDates').find('img');
                $('#dates').val('');
                for(var img = 0; img<imgs.length; img++){
                    if(imgs[img].id.substr(0,4) == 'date' && imgs[img].src.substr(imgs[img].src.length -'uncheck.png'.length) != 'uncheck.png'){
                        $('#dates').val($('#dates').val()+$('#'+imgs[img].id).attr('dateVal') +',');
//                        console.log(imgs[img].src);
                    }
                }
                $('#dates').val($('#dates').val().substr(0, $('#dates').val().length-1));
                $('#setDateFilter').submit();
            }
            $('li').click(function(e){
                var id;
                if(e.target.id.substr(0,4) == 'date'){
                    id = e.target.id.substr(4);
                    var img = $('img#'+e.target.id);
                }else{
                    id = e.target.id;
                    var img = $('li#'+id).find('img');
                }
                console.log(id);


                var src = img.attr('src');
                var check = false;
                if(src.substr(src.length-'uncheck.png'.length) == 'uncheck.png'){
                    img.attr('src', '/dolibarr/htdocs/theme/eldy/img/check.png');
                    check = true;
                }else{
                    img.attr('src', '/dolibarr/htdocs/theme/eldy/img/uncheck.png');
                }
                var className = $('li#'+id).attr('class').replace('dates ', '');
                img = $('ul#'+className).find('img');
                if(!check){
                    img.attr('src', '/dolibarr/htdocs/theme/eldy/img/uncheck.png');
                }
            })
            $('ul').click(function(e){
                var id;
                if(e.target.id.substr(0,3) != 'img'){
                    var img = $('ul#'+e.target.id).find('img');
                    id = e.target.id;
                }else{
                    var img = $('#'+e.target.id);
                    id = e.target.id.substr(3);
                }
//                console.log(id);
                var check = false;
                var src = img.attr('src');
//                console.log(e.target.id);
                if(src.substr(src.length-'uncheck.png'.length) == 'uncheck.png'){
                    img.attr('src', '/dolibarr/htdocs/theme/eldy/img/check.png');
                    check = true;
                }else{
                    img.attr('src', '/dolibarr/htdocs/theme/eldy/img/uncheck.png');
                }
                var subImg = $('li.'+id).find('img');
                subImg.attr('src', img.attr('src'));
            })
        </script>";
//        var_dump($out);
//        die();
        if(count($dates)==0)
            $out='дату не встановлено';

        return $out;
    }
    function getExecuters($action_id){
        global $db;
        $sql = "select fk_element from `llx_actioncomm_resources` where `fk_actioncomm` = " . $action_id;
        $res = $db->query($sql);
        $out = '{ ';
        if ($db->num_rows($res) == 0) {
            $sql = "select llx_user.rowid, llx_user.lastname, llx_user.firstname  from llx_actioncomm inner join llx_user on llx_user.rowid = fk_user_author where id = " . $action_id;
            $res = $db->query($sql);
            $obj = $db->fetch_object($res);
            $out .= '"'.$obj->rowid . '" : "' . $obj->lastname . ' ' . mb_substr($obj->firstname, 0, 1, 'UTF-8').'"' ;
        } else {
            while ($obj = $db->fetch_object($res)){
                $sql = "select llx_user.rowid, llx_user.lastname, llx_user.firstname from llx_user where llx_user.rowid = " . $obj->fk_element;
                $res_tmp = $db->query($sql);
                $obj_tmp = $db->fetch_object($res_tmp);
                if(strlen($out)>2)
                    $out.=',';
                $out .= '"'.$obj_tmp->rowid . '" : "' . $obj_tmp->lastname . ' ' . mb_substr($obj_tmp->firstname, 0, 1, 'UTF-8').'"' ;
            }
        }
        $out .= ' }';
        $out = json_decode($out, true);
        return $out;
    }
    function setOuterdueStatus($action_id){
        global $db, $user;
        $chain_actions = $this->GetChainActions($action_id);

        $sql = "select id, `llx_actioncomm_resources`.`rowid`, new, datec, fk_parent, datep, percent, fk_user_author, fk_user_action, overdue, `llx_actioncomm_resources`.`fk_element` 
            from llx_actioncomm 
            left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = llx_actioncomm.id
            where id in (".implode(',', $chain_actions).") and active = 1";
//        echo '<pre>';
//        var_dump($sql);
//        echo '</pre>';
//        die();
        $res = $db->query($sql);

        if(!$res)
            dol_print_error($db);

        if(count($chain_actions) == 1 && $res->num_rows) {//Якщо одне завдання, задіяно два виконавця один з яких автор - підчищаю  `llx_actioncomm_resources` аби прострочені виконавцем завдання не відображались у замовника
            while ($obj = $db->fetch_object($res)) {
                if($obj->fk_element == $obj->fk_user_author && $obj->fk_user_action == $obj->fk_user_author){
                    $sql_set = "delete from llx_actioncomm_resources where rowid = ".$obj->rowid;
                    $db->query($sql_set);
                }
            }
            $res = $db->query($sql);
        }
        $executers = array();
        //Завантажую завдання по виконавцям

        while($obj = $db->fetch_object($res)){
            $executers[!empty($obj->fk_element)?$obj->fk_element:$obj->fk_user_action][]= $obj->fk_user_author!=$obj->fk_element&&!empty($obj->fk_element)?($obj->id):(-$obj->id);//Знак мінус - якщо користувач ставить завдання собі
        }
        mysqli_data_seek($res, 0);
        //Завантажую інформацію по всім завданням
        $actions = array();
        while($obj = $db->fetch_object($res)) {
            $actions[$obj->id] = array('new'=>$obj->new, 'datec'=>$obj->datec, 'fk_parent'=>$obj->fk_parent,'datep'=>$obj->datep, 'percent'=>$obj->percent,
                'fk_user_author'=>$obj->fk_user_author, 'fk_user_action'=>$obj->fk_user_action, 'overdue'=>$obj->overdue, 'fk_element'=>$obj->fk_element);
        }
        $res = $db->query($sql);

        $overdue = null;

        while($obj = $db->fetch_object($res)){
            $date = new DateTime($obj->datep);
            $now = new DateTime(date('Y-M-d'));
            $interval = $now->diff($date);
            $sql_set = "update llx_actioncomm set overdue = NULL where id = ".$obj->id;

            $obj->overdue = null;
            $res_set = $db->query($sql_set);
            if(!$res_set)
                dol_print_error($db);

            if(empty($obj->fk_parent) || !in_array($actions[$obj->fk_parent]['percent'], array(99,100,-100,-1))) {
//                echo '<pre>';
//                var_dump($this->getWorkdayCount($date, $now) > 1 && !in_array($obj->percent, array(99, 100, -100)) && ($obj->new || !$this->getElemMoreThanVal($executers[!empty($obj->fk_element) ? $obj->fk_element : $obj->fk_user_action], $action_id)));
//                echo '</pre>';
//                die();

                if ($this->getWorkdayCount($date, $now) > 1 && (!in_array($obj->percent, array(99, 100, -100)) || $obj->datec< $now && $obj->percent == -1) && ($obj->new || !$this->getElemMoreThanVal($executers[!empty($obj->fk_element) ? $obj->fk_element : $obj->fk_user_action], $action_id))) {//Позначаю за невиконані завдання, які не були відкриті на протязі доби, або не заплановані піддії
//                $set_sql = "update llx_actioncomm set overdue = 1 where id = " . $obj->id;
//                $db->query($set_sql);
                    $obj->overdue = 1;
                }


                if (!$obj->overdue && !in_array($obj->percent, array(99, 100, -100)) && $obj->fk_user_author == $obj->fk_user_action && $date < $now) {//Завдання не виконане, якщо виконавець прострочив собою заплановану піддію
                    $obj->overdue = 1;
                }

                if (!$obj->overdue && !in_array($obj->percent, array(99, 100, -100)) && !empty($executers[$obj->fk_user_author]) && in_array(-$obj->id, $executers[$obj->fk_user_author])) {//Завдання, що плануються виконавцем самому собі більше ніж два робочих дня після додаткової піддії замовником та наявності не прийнятих в цій цепочці задач
                    if ($this->getWorkdayCount($date, $now) > 2) {
                        $obj->overdue = 1;
                    }
                }
            }
//            if($obj->id == 522998){
//                var_dump($obj->overdue);
//                die();
//            }

            if($obj->overdue){
                $overdue = 1;
                $sql_set = "update llx_actioncomm set overdue = 1 where id = ".$obj->id;
                $res_set = $db->query($sql_set);
                if(!$res_set)
                    dol_print_error($db);
            }
        }
        return $overdue;
    }
    function setFutureDataAction($rowid){
        global $db;
        $chain_action = $this->GetChainActions($rowid);
        if(count($chain_action)>0) {
            $sql = "select datep maxdate from llx_actioncomm where id = ".max($chain_action);
//			var_dump($sql);
//			die();
            $res = $db->query($sql);
            if (!$res) {
                dol_print_error($db);
            }
            $obj = $db->fetch_object($res);
            $date = new DateTime($obj->maxdate);
            if (count($chain_action) > 1)
                $sql = "update llx_actioncomm set datefutureaction = 
					'" . $date->format('Y-m-d H:i:s') . "' where id in (" . (implode(',', $chain_action)) . ")";
            else
                $sql = "update llx_actioncomm set datefutureaction = null where id in (" . (implode(',', $chain_action)) . ")";
            $res = $db->query($sql);
            if (!$res) {
                dol_print_error($db);
            }
        }
    }
    function getWorkdayCount($date_from, $date_to){//Повертає кількість робочих днів у вибраному періоді
        $out = 0;

        $date_tmp = new DateTime($date_from->format('Y-m-d H:i:s'));

        while($date_tmp<=$date_to){
            if(date('w',$date_tmp->getTimestamp())>=1 && date('w',$date_tmp->getTimestamp())<=5) {
                $out++;

            }
            $date_tmp->add(new DateInterval('P1D'));

        }
        return $out;
    }
    function getElemMoreThanVal($array, $val = 0){//Повертає true, якщо є дія користувача після зазначеного ІД
        foreach ($array as $item=>$value){
            if(abs($value) < $val)
                return true;
        }
        return false;
    }
    function setActionStatusNotActual($action_id, $including_sel_id = false, $subaction = false){
        global $db, $user;
        $actions = $this->GetChainActions($action_id);
        if(!$including_sel_id) {
            unset($actions[array_search($action_id, $actions)]);
        }
        //Откримую всіх авторів дій
        $sql = "select id, fk_user_author from llx_actioncomm where id in (".implode(',', $actions).")";
        $res = $db->query($sql);
        $authors = array();
        while($obj = $db->fetch_object($res)){
            $authors[$obj->id] = $obj->fk_user_author;
        }
//        var_dump($actions);
//        die();
        foreach ($actions as $action){
            if($authors[$action] == $user->id) {
                $executers = $this->getExecuters($action);
                if (count($executers))
                    $id_usrs = array_keys($executers);
                else
                    $id_usrs = array();
                if (!count($id_usrs) || $id_usrs[0] == $user->id && count($id_usrs) == 1 || $subaction) {
                    $sql = "update llx_actioncomm set percent = -100, overdue = null where id = " . $action . " and percent != 100 and active = 1";
                    $res = $db->query($sql);
                    if (!$res)
                        dol_print_error($db);
                }
            }
        }
    }
    function add($user,$when_show='oncreate',$notrigger=0)
    {
//        echo '<pre>';
//        var_dump($this);
//        echo '</pre>';
//        die();
        global $langs, $conf, $hookmanager;

        $error = 0;
        $now = dol_now();
        // Check parameters
        if (empty($this->userownerid)) {
            $this->errors[] = 'ErrorPropertyUserowneridNotDefined';
            return -1;
        }

        // Clean parameters
        $this->label = dol_trunc(trim($this->label), 128);
        $this->location = dol_trunc(trim($this->location), 128);
        $this->note = dol_htmlcleanlastbr(trim($this->note));
        if (empty($this->percentage)) $this->percentage = 0;
        if (empty($this->priority) || !is_numeric($this->priority)) $this->priority = 0;
        if (empty($this->fulldayevent)) $this->fulldayevent = 0;
        if (empty($this->punctual)) $this->punctual = 0;
        if (empty($this->transparency)) $this->transparency = 0;
        if ($this->percentage > 100) $this->percentage = 100;
        //if ($this->percentage == 100 && ! $this->dateend) $this->dateend = $this->date;
        if (!empty($this->datep) && !empty($this->datef)) $this->durationp = ($this->datef - $this->datep);        // deprecated
        //if (! empty($this->date)  && ! empty($this->dateend)) $this->durationa=($this->dateend - $this->date);
        if (!empty($this->datep) && !empty($this->datef) && $this->datep > $this->datef) $this->datef = $this->datep;
        //if (! empty($this->date)  && ! empty($this->dateend) && $this->date > $this->dateend) $this->dateend=$this->date;
        if (!isset($this->fk_project) || $this->fk_project < 0) $this->fk_project = 0;
        if ($this->elementtype == 'facture') $this->elementtype = 'invoice';
        if ($this->elementtype == 'commande') $this->elementtype = 'order';
        if ($this->elementtype == 'contrat') $this->elementtype = 'contract';

        if (is_object($this->contact) && $this->contact->id > 0 && !($this->contactid > 0)) $this->contactid = $this->contact->id;        // For backward compatibility. Using this->contact->xx is deprecated

        $userownerid = $this->userownerid;
        $userdoneid = $this->userdoneid;

        // Be sure assigned user is defined as an array of array('id'=>,'mandatory'=>,...).
        if (empty($this->userassigned) || count($this->userassigned) == 0 || !is_array($this->userassigned))
            $this->userassigned = array($userownerid => array('id' => $userownerid));

        if (!$this->type_id || !$this->type_code) {
            $key = empty($this->type_id) ? $this->type_code : $this->type_id;

            // Get id from code
            $cactioncomm = new CActionComm($this->db);
            $result = $cactioncomm->fetch($key);

            if ($result > 0) {
                $this->type_id = $cactioncomm->id;
                $this->type_code = $cactioncomm->code;
            } else if ($result == 0) {
                $this->error = 'Failed to get record with id ' . $this->type_id . ' code ' . $this->type_code . ' from dictionary "type of events"';
                return -1;
            } else {
                $this->error = $cactioncomm->error;
                return -1;
            }
        }

        // Check parameters
        if (!$this->type_id) {
            $this->error = "ErrorWrongParameters";
            return -1;
        }

        $this->db->begin();
//        var_dump(array_keys($this->userassigned));
//        die();
        for ($i = count(array_keys($this->userassigned))>1?1:0; $i < count(array_keys($this->userassigned)); $i++) {

            $correctdate = false;
            $cdatep=0;
            $cdatef=0;
//        echo "<pre>";
//        var_dump($this->userassigned);
//        echo "</pre>";
//        die();
            if(count(array_keys($this->userassigned))>1){
                $correctdate = true;
                $minute = ($this->datef-$this->datep)/60;
//                var_dump($this->datep);
//                die();
                $freedate = new DateTime($this->GetFreeTime(date('Y-m-d H:i',$this->datep),array_keys($this->userassigned)[$i],$minute, $this->priority));

                $cdatep = mktime($freedate->format('H'),$freedate->format('i'),$freedate->format('s'),$freedate->format('m'),$freedate->format('d'),$freedate->format('Y'));
                $cdatef = $cdatep+$minute*60;
//                if(array_keys($this->userassigned)[$i] == 66) {
//                    var_dump(array_keys($this->userassigned)[$i], $freedate, date('H:i:s', $cdatef));
//                    die();
//                }
            }

//            echo ($i) . '</br>';
            $sql = "INSERT INTO " . MAIN_DB_PREFIX . "actioncomm";
            $sql .= "(datec,";
            $sql .= "datep,";
            $sql .= "datep2,";
            $sql .= "dateconfirm,";
            $sql .= "durationp,";    // deprecated
            $sql .= "datepreperform,";
            $sql .= "type,";
            $sql .= "fk_action,";
            $sql .= "code,";
            $sql .= "fk_soc,";
            $sql .= "fk_parent,";
            $sql .= "fk_project,";
            $sql .= "note,";
            $sql .= "fk_contact,";
            $sql .= "fk_user_author,";
            $sql .= "fk_user_action,";
            $sql .= "fk_user_done,";
            $sql .= "label,percent,priority,fulldayevent,location,punctual,";
            $sql .= "transparency,";
            $sql .= "fk_element,";
            $sql .= "elementtype,";
            $sql .= "entity,";
            $sql .= "confirmdoc,";
            $sql .= "period,";
            $sql .= "fk_groupoftask,";
            $sql .= "fk_order_id,";
            $sql .= "new,";
            $sql .= "subaction,";
            $sql .= "subaction_id,";
            $sql .= "planed_cost,";
            $sql .= "fact_cost,";
            $sql .= "motivator,";
            $sql .= "demotivator,";
            $sql .= "fk_user_valuer,";
            $sql .= "typenotification,";
            $sql .= "when_show";
            $sql .= ") VALUES (";
            $sql .= "'" . $this->db->idate($now) . "',";
            if(!$correctdate) {
                $sql .= (strval($this->datep) != '' ? "'" . $this->db->idate($this->datep) . "'" : "null") . ",";
                $sql .= (strval($this->datef) != '' ? "'" . $this->db->idate($this->datef) . "'" : "null") . ",";
            }else{
                $sql .= (strval($cdatep) != '' ? "'" . $this->db->idate($cdatep) . "'" : "null") . ",";
                $sql .= (strval($cdatef) != '' ? "'" . $this->db->idate($cdatef) . "'" : "null") . ",";
            }
            $sql .= ((isset($this->dateconfirm) && !empty($this->dateconfirm)) ? "'" . $this->dateconfirm . "'" : "null") . ",";

            $sql .= ((isset($this->durationp) && $this->durationp >= 0 && $this->durationp != '') ? "'" . $this->durationp . "'" : "null") . ",";    // deprecated
            $sql .= (strval($this->datepreperform) != '' ? "'" . $this->db->idate($this->datepreperform) . "'" : "null") . ",";
            $sql .= (isset($this->typeSetOfDate) ? "'".$this->typeSetOfDate."'" : "null") . ",";
            $sql .= (isset($this->type_id) ? $this->type_id : "null") . ",";
            $sql .= (isset($this->type_code) ? " '" . $this->type_code . "'" : "null") . ",";
            $sql .= ((isset($this->socid) && $this->socid > 0) ? " '" . $this->socid . "'" : "null") . ",";
            $sql .= ((isset($this->parent_id) && $this->parent_id > 0) ? " '" . $this->parent_id . "'" : 0) . ",";
            $sql .= ((isset($this->fk_project) && $this->fk_project > 0) ? " '" . $this->fk_project . "'" : "null") . ",";
            $sql .= " '" . $this->db->escape($this->note) . "',";
            $sql .= ((isset($this->contactid) && $this->contactid > 0) ? "'" . $this->contactid . "'" : "null") . ",";
            $sql .= (isset($user->id) && $user->id > 0 ? "'" . $user->id . "'" : "null") . ",";
            $sql .= ($userownerid > 0 ? "'" . $userownerid . "'" : "null") . ",";
            $sql .= ($userdoneid > 0 ? "'" . $userdoneid . "'" : "null") . ",";
            $sql .= "'" . $this->db->escape($this->label) . "','" . $this->percentage . "','" . $this->priority . "','" . $this->fulldayevent . "','" . $this->db->escape($this->location) . "','" . $this->punctual . "',";
            $sql .= "'" . $this->transparency . "',";
            $sql .= (!empty($this->fk_element) ? $this->fk_element : "null") . ",";
            $sql .= (!empty($this->elementtype) ? "'" . $this->elementtype . "'" : "null") . ",";
// $conf->entity використовується, коли є декілька компаній. Тут буде використовуватись, 1 - коли створена головна дія. 0 - це піддія
//            $sql .= $conf->entity . ",";
            $sql .= $this->entity . ",";

            $sql .= "'" . $this->confirmdoc . "',";
            $sql .=  (!empty($this->period)?("'".$this->period."'"):"null") . ",";
            $sql .= "'" . $this->groupoftask . "',";
            $sql .= " " . (!empty($this->order_id) ? "'" . $this->order_id . "'" : "null"). ",";
            $sql .= (isset($this->type_code)&&!in_array($this->type_code, array('AC_PROP','AC_COM','AC_FAC','AC_SHIP','AC_SUP_ORD','AC_SUP_INV','AC_OTH_AUTO','AC_OTH')))?'1,':'0,';
            $sql .= (!empty($this->subaction) ? "'" . $this->subaction . "'" : "null"). ","
                .(!empty($this->subaction_id)?$this->subaction_id: "null"). ", ".
                (!empty($this->planed_cost)?"'".$this->planed_cost."'": "null"). ", ".
                (!empty($this->fact_cost)?"'".$this->fact_cost."'": "null"). ", ".
                (!empty($this->motivator)?"'".$this->motivator."'": "null"). ", ".
                (!empty($this->demotivator)?"'".$this->demotivator."'": "null"). ", ".
                (!empty($this->user_valuer)?$this->user_valuer: "null"). ", '".
                $this->typenotification . "',";
            $sql .= "'$when_show'";
            $sql .= ")";

            dol_syslog(get_class($this) . "::add", LOG_DEBUG);
            $resql=$this->db->query($sql);
//            var_dump($sql);
//            die();
            if(!$resql)
                dol_print_error($this->db);
//            $resql = 1;
//            echo '<pre>';
//            var_dump(array_keys($this->userassigned)[$i]);
//            echo '</pre>';

            if ($resql) {

                $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . "actioncomm", "id");
                
                if(!empty($this->parent_id)){
                    $chain_action = $this->GetChainActions($this->parent_id);
                    $sql = "update llx_actioncomm set datelastaction = Now(),
                      datefutureaction = case when datefutureaction is null or datefutureaction<'".$this->db->idate($this->datep)."' then '".$this->db->idate($this->datep)."' else datefutureaction end  where id in (".implode(',', $chain_action).") and active = 1";

                    $res = $this->db->query($sql);
                    if(!$res)
                        dol_print_error($this->db);
                }
                // Now insert assignedusers
                if (!$error) {
//                    for($k=0;$k<count(array_keys($this->userassigned));$k++){
                    if ($i > 0){
//                        var_dump($this->userassigned[array_keys($this->userassigned)[$i]]) . '</br>';
                        $val = $this->userassigned[array_keys($this->userassigned)[$i]];
                        if (! is_array($val))	// For backward compatibility when val=id
                        {
                            $val=array('id'=>$val);
                        }
//                        if($this->socid == 8649) {
//                            llxHeader();
//                            var_dump($user->id != $val['id'], $user->id ,  $val['id'], $this->userassigned[array_keys($this->userassigned)[$i]]);
//                            die();
//                        }
                        if($user->id != $val['id']) {
                            $sql = "INSERT INTO " . MAIN_DB_PREFIX . "actioncomm_resources(fk_actioncomm, element_type, fk_element, mandatory, transparency, answer_status)";
                            $sql .= " VALUES(" . $this->id . ", 'user', " . $val['id'] . ", " . (empty($val['mandatory']) ? '0' : $val['mandatory']) . ", " . (empty($val['transparency']) ? '0' : $val['transparency']) . ", " . (empty($val['answer_status']) ? '0' : $val['answer_status']) . ")";

                            $resql = $this->db->query($sql);
                            if (!$resql) {
    //                            dol_print_error($this->db);
                                $error++;
                                $this->errors[] = $this->db->lasterror();
                            }
                        }
                    }
                    //Виконую додаткові дії пов'язані з піддіями
                    switch ($this->subaction){
                        case 'sendmail':{
                            $sql = 'update `llx_mailing`
                                    set 
                                    date_valid = case when date_valid is null then now() else date_valid end
                                    where rowid = '.$this->subaction_id;
                            $res = $this->db->query($sql);
                            if(!$res)
                                $this->errors[] = $this->db->lasterror();
                        }break;
                    }
                    //Встановлюю відмітку "не актуальне" для підій, в яких автор і замовник одне лице
//                    $this->setActionStatusNotActual($this->id);


//                    foreach($this->userassigned as $key => $val)
//                    {
//                        if (! is_array($val))	// For backward compatibility when val=id
//                        {
//                            $val=array('id'=>$val);
//                        }
//                        if($user->id != $val['id']) {
//                            $sql = "INSERT INTO " . MAIN_DB_PREFIX . "actioncomm_resources(fk_actioncomm, element_type, fk_element, mandatory, transparency, answer_status)";
//                            $sql .= " VALUES(" . $this->id . ", 'user', " . $val['id'] . ", " . (empty($val['mandatory']) ? '0' : $val['mandatory']) . ", " . (empty($val['transparency']) ? '0' : $val['transparency']) . ", " . (empty($val['answer_status']) ? '0' : $val['answer_status']) . ")";
//
//                            $resql = $this->db->query($sql);
//                            if (!$resql) {
//    //                            dol_print_error($this->db);
//                                $error++;
//                                $this->errors[] = $this->db->lasterror();
//                            }
//                        }
//    //					var_dump($sql);exit;
//                    }
                }
                //var_dump($error);exit;
                if((isset($this->socid) && $this->socid > 0)){
                    $postfix = '';
                    if(in_array('sale', array($user->respon_alias,$user->respon_alias2)))
                        $postfix = 'comerc';
                    else if(in_array('purchase', array($user->respon_alias,$user->respon_alias2)))
                        $postfix = 'service';
                    if(!empty($postfix)) {
                        $date = new DateTime($this->db->idate($this->datep));
                        $sql = "update llx_societe set futuredate" . $postfix . "='" . $date->format('Y-m-d') . "' where rowid = " . $this->socid;
                        $res = $this->db->query($sql);
                        if (!$res)
                            dol_print_error($this->db);
                    }
                        $this->setDateAction($this->socid);//Оновлення інформації про останні і майбутні контакти
                }

            }
            else
            {
                $this->db->rollback();
                $this->error=$this->db->lasterror();
                return -1;
            }
            //Сповіщення про створення нової дії
            if(count(array_keys($this->userassigned))>1)
                $when_show = 'oncreate';
            else
                $when_show = 'ondatep';
//            echo '<pre>';
//            var_dump($this->code);
//            echo '</pre>';
//            die();

            if(!in_array($this->code, array('AC_COMPANY_CREATE', 'AC_OTH_AUTO'))) {
                $from = !empty($val['id']) ? $val['id'] : $this->authorid;
                if(!empty($from))
                    $this->ShowAction(!empty($val['id']) ? $val['id'] : $this->authorid, empty($this->authorid) ? $user->id : $this->authorid, $this->id, $when_show);
            }
//            die('test');

        }
        if (! $error)
        {
            $action='create';

            // Actions on extra fields (by external module or standard code)
            // FIXME le hook fait double emploi avec le trigger !!
            $hookmanager->initHooks(array('actioncommdao'));
            $parameters=array('actcomm'=>$this->id);
            $reshook=$hookmanager->executeHooks('insertExtraFields',$parameters,$this,$action);    // Note that $action and $object may have been modified by some hooks
            if (empty($reshook))
            {
                if (empty($conf->global->MAIN_EXTRAFIELDS_DISABLED)) // For avoid conflicts if trigger used
                {
                    $result=$this->insertExtraFields();
                    if ($result < 0)
                    {
                        $error++;
                    }
                }
            }
            else if ($reshook < 0) $error++;
        }

        if (! $error && ! $notrigger)
        {
            // Call trigger
            $result=$this->call_trigger('ACTION_CREATE',$user);
            if ($result < 0) { $error++; }
            // End call triggers
        }

       if (! $error){
           $this->db->commit();
            return $this->id;
       }else{
            $this->db->rollback();
            return -1;
       }
//        $sql = "INSERT INTO ".MAIN_DB_PREFIX."actioncomm";
//        $sql.= "(datec,";
//        $sql.= "datep,";
//        $sql.= "datep2,";
//        $sql.= "durationp,";	// deprecated
//        $sql.= "fk_action,";
//        $sql.= "code,";
//        $sql.= "fk_soc,";
//        $sql.= "fk_parent,";
//        $sql.= "fk_project,";
//        $sql.= "note,";
//        $sql.= "fk_contact,";
//        $sql.= "fk_user_author,";
//        $sql.= "fk_user_action,";
//        $sql.= "fk_user_done,";
//        $sql.= "label,percent,priority,fulldayevent,location,punctual,";
//        $sql.= "transparency,";
//        $sql.= "fk_element,";
//        $sql.= "elementtype,";
//        $sql.= "entity,";
//        $sql.= "confirmdoc,";
//        $sql.= "period,";
//        $sql.= "fk_groupoftask,";
//        $sql.= "fk_order_id";
//        $sql.= ") VALUES (";
//        $sql.= "'".$this->db->idate($now)."',";
//        $sql.= (strval($this->datep)!=''?"'".$this->db->idate($this->datep)."'":"null").",";
//        $sql.= (strval($this->datef)!=''?"'".$this->db->idate($this->datef)."'":"null").",";
//        $sql.= ((isset($this->durationp) && $this->durationp >= 0 && $this->durationp != '')?"'".$this->durationp."'":"null").",";	// deprecated
//        $sql.= (isset($this->type_id)?$this->type_id:"null").",";
//        $sql.= (isset($this->type_code)?" '".$this->type_code."'":"null").",";
//        $sql.= ((isset($this->socid) && $this->socid > 0)?" '".$this->socid."'":"null").",";
//        $sql.= ((isset($this->parent_id) && $this->parent_id > 0)?" '".$this->parent_id."'":0).",";
//        $sql.= ((isset($this->fk_project) && $this->fk_project > 0)?" '".$this->fk_project."'":"null").",";
//        $sql.= " '".$this->db->escape($this->note)."',";
//        $sql.= ((isset($this->contactid) && $this->contactid > 0)?"'".$this->contactid."'":"null").",";
//        $sql.= (isset($user->id) && $user->id > 0 ? "'".$user->id."'":"null").",";
//        $sql.= ($userownerid>0?"'".$userownerid."'":"null").",";
//        $sql.= ($userdoneid>0?"'".$userdoneid."'":"null").",";
//        $sql.= "'".$this->db->escape($this->label)."','".$this->percentage."','".$this->priority."','".$this->fulldayevent."','".$this->db->escape($this->location)."','".$this->punctual."',";
//        $sql.= "'".$this->transparency."',";
//        $sql.= (! empty($this->fk_element)?$this->fk_element:"null").",";
//        $sql.= (! empty($this->elementtype)?"'".$this->elementtype."'":"null").",";
//        $sql.= $conf->entity.",";
//        $sql.= "'".$this->confirmdoc."',";
//        $sql.= "'".$this->period."',";
//        $sql.= "'".$this->groupoftask."',";
//        $sql.= " ".(! empty($this->order_id)?"'".$this->order_id."'":"null");
//        $sql.= ")";
////        var_dump($sql);
//        dol_syslog(get_class($this)."::add", LOG_DEBUG);
//        $resql=$this->db->query($sql);
//        if ($resql)
//        {
//            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."actioncomm","id");
////            var_dump($error);
////            die();
//            // Now insert assignedusers
//			if (! $error)
//			{
//				foreach($this->userassigned as $key => $val)
//				{
//			        if (! is_array($val))	// For backward compatibility when val=id
//			        {
//			        	$val=array('id'=>$val);
//			        }
//                    if($user->id != $val['id']) {
//                        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "actioncomm_resources(fk_actioncomm, element_type, fk_element, mandatory, transparency, answer_status)";
//                        $sql .= " VALUES(" . $this->id . ", 'user', " . $val['id'] . ", " . (empty($val['mandatory']) ? '0' : $val['mandatory']) . ", " . (empty($val['transparency']) ? '0' : $val['transparency']) . ", " . (empty($val['answer_status']) ? '0' : $val['answer_status']) . ")";
//
//                        $resql = $this->db->query($sql);
//                        if (!$resql) {
////                            dol_print_error($this->db);
//                            $error++;
//                            $this->errors[] = $this->db->lasterror();
//                        }
//                    }
////					var_dump($sql);exit;
//				}
//			}
////var_dump($error);exit;
//            if (! $error)
//            {
//            	$action='create';
//
//	            // Actions on extra fields (by external module or standard code)
//				// FIXME le hook fait double emploi avec le trigger !!
//            	$hookmanager->initHooks(array('actioncommdao'));
//	            $parameters=array('actcomm'=>$this->id);
//	            $reshook=$hookmanager->executeHooks('insertExtraFields',$parameters,$this,$action);    // Note that $action and $object may have been modified by some hooks
//	            if (empty($reshook))
//	            {
//	            	if (empty($conf->global->MAIN_EXTRAFIELDS_DISABLED)) // For avoid conflicts if trigger used
//	            	{
//	            		$result=$this->insertExtraFields();
//	            		if ($result < 0)
//	            		{
//	            			$error++;
//	            		}
//	            	}
//	            }
//	            else if ($reshook < 0) $error++;
//            }
//
//            if (! $error && ! $notrigger)
//            {
//                // Call trigger
//                $result=$this->call_trigger('ACTION_CREATE',$user);
//                if ($result < 0) { $error++; }
//                // End call triggers
//            }
//
//            if (! $error)
//            {
//            	$this->db->commit();
//            	return $this->id;
//            }
//            else
//           {
//	           	$this->db->rollback();
//	           	return -1;
//            }
//        }
//        else
//        {
//            $this->db->rollback();
//            $this->error=$this->db->lasterror();
//            return -1;
//        }

    }

    /**
     *    Load object from database
     *
     *    @param	int		$id     Id of action to get
     *    @param	string	$ref    Ref of action to get
     *    @return	int				<0 if KO, >0 if OK
     */
    function fetch($id, $ref='')
    {
        global $langs;

        $sql = "SELECT a.id,";
        $sql.= " a.id as ref,";
        $sql.= " a.ref_ext,";
        $sql.= " a.datep,";
        $sql.= " a.datep2,";
        $sql.= " a.durationp,";	// deprecated
        $sql.= " a.datec,";
        $sql.= " a.datepreperform,";
        $sql.= " a.tms as datem,";
        $sql.= " a.code, a.label, a.note,";
        $sql.= " a.fk_soc,";
        $sql.= " a.fk_groupoftask,";
        $sql.= " a.fk_project,";
        $sql.= " a.fk_user_author, a.fk_user_mod,";
        $sql.= " a.fk_user_action, a.fk_user_done,";
        $sql.= " a.fk_contact, a.percent as percentage,";
        $sql.= " a.fk_element, a.elementtype,";
        $sql.= " a.priority, a.entity, a.fulldayevent, a.location, a.punctual, a.transparency,";
        $sql.= " c.id as type_id, c.code as type_code, c.libelle,";
        $sql.= " s.nom as socname,";
        $sql .= "a.planed_cost,";
        $sql .= "a.fact_cost,";
        $sql .= "a.motivator,";
        $sql .= "a.demotivator,";
        $sql .= "a.fk_user_valuer,";
        $sql.= " u.firstname, u.lastname as lastname, a.period, a.confirmdoc, a.typenotification";
        $sql.= " FROM ".MAIN_DB_PREFIX."actioncomm as a ";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_actioncomm as c ON a.fk_action=c.id ";
        $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."user as u on u.rowid = a.fk_user_author";
        $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s on s.rowid = a.fk_soc";
        $sql.= " WHERE ";
        if ($ref) $sql.= " a.id=".$ref;		// No field ref, we use id
        else $sql.= " a.id=".$id;

        dol_syslog(get_class($this)."::fetch", LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
        	$num=$this->db->num_rows($resql);
            if ($num)
            {
                $obj = $this->db->fetch_object($resql);

                $this->id        = $obj->id;
                $this->ref       = $obj->ref;
                $this->ref_ext   = $obj->ref_ext;
                $this->confirmdoc = $obj->confirmdoc;
                $this->entity = $obj->entity;
                // Properties of parent table llx_c_actioncomm (will be deprecated in future)
                $this->type_id   = $obj->type_id;
                $this->type_code = $obj->type_code;
                $transcode=$langs->trans("Action".$obj->type_code);
                $type_libelle=($transcode!="Action".$obj->type_code?$transcode:$obj->libelle);
                $this->type      = $type_libelle;

				$this->code					= $obj->code;
                $this->label				= $obj->label;
                $this->datep				= $this->db->jdate($obj->datep);
                $this->datef				= $this->db->jdate($obj->datep2);
                $this->datepreperform       = $this->db->jdate($obj->datepreperform);
//				$this->durationp			= $this->durationp;					// deprecated
                $this->period               = $obj->period;
                $this->groupoftask          = $obj->fk_groupoftask;

                $this->datec   				= $this->db->jdate($obj->datec);
                $this->datem   				= $this->db->jdate($obj->datem);

                $this->note					= $obj->note;
                $this->percentage			= $obj->percentage;

                $this->authorid             = $obj->fk_user_author;
                $this->usermodid			= $obj->fk_user_mod;

                if (!is_object($this->author)) $this->author = new stdClass(); // For avoid warning
                $this->author->id			= $obj->fk_user_author;		// deprecated
                $this->author->firstname	= $obj->firstname;			// deprecated
                $this->author->lastname		= $obj->lastname;			// deprecated
                if (!is_object($this->usermod)) $this->usermod = new stdClass(); // For avoid warning
                $this->usermod->id			= $obj->fk_user_mod;		// deprecated

                $this->userownerid			= $obj->fk_user_action;
                $this->userdoneid			= $obj->fk_user_done;
                $this->priority				= $obj->priority;
                $this->fulldayevent			= $obj->fulldayevent;
                $this->location				= $obj->location;
                $this->transparency			= $obj->transparency;
                $this->punctual				= $obj->punctual;

                $this->socid				= $obj->fk_soc;			// To have fetch_thirdparty method working
                $this->contactid			= $obj->fk_contact;		// To have fetch_contact method working
                $this->fk_project			= $obj->fk_project;		// To have fetch_project method working

                $this->societe->id			= $obj->fk_soc;			// deprecated
                $this->contact->id			= $obj->fk_contact;		// deprecated
                $this->planed_cost          = $obj->planed_cost;//Заплановані витрати
                $this->fact_cost            = $obj->fact_cost;  //Фактичні витрати
                $this->motivator            = $obj->motivator;  //Мотиватор
                $this->demotivator          = $obj->demotivator;//Демотиватор
                $this->fk_user_valuer       = $obj->fk_user_valuer;//Фахівець-оцінщик

                $this->fk_element			= $obj->fk_element;
                $this->elementtype			= $obj->elementtype;
                $this->typenotification		= $obj->typenotification;//Type notification
                $this->fetch_userassigned();
            }
            $this->db->free($resql);
        }
        else
        {
            $this->error=$this->db->lasterror();
            return -1;
        }
        $sql = 'select `rowid`,`callstatus`, `said`,`answer`, `argument`,`said_important`,`fact_cost`,`result_of_action`,`work_before_the_next_action`
        from llx_societe_action where 1 and '.(empty($_REQUEST['answer_id'])?('action_id='.$id):('rowid='.$_REQUEST['answer_id'])). ' limit 1';
//        var_dump($sql);
//        die();
        $resql=$this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            if ($num) {
                $obj = $this->db->fetch_object($resql);
                $this->resultaction['rowid']                         = $obj->rowid;
                $this->resultaction['said']                          = $obj->said;
                $this->resultaction['answer']                        = $obj->answer;
                $this->resultaction['argument']                      = $obj->argument;
                $this->resultaction['said_important']                = $obj->said_important;
                $this->resultaction['result_of_action']              = $obj->result_of_action;
                $this->resultaction['work_before_the_next_action']   = $obj->work_before_the_next_action;
                $this->resultaction['fact_cost']                     = $obj->fact_cost;
                if($this->code	== 'AC_TEL'){
                    $this->callstatus = $obj->callstatus;
                }
            }
        }

        return $num;

    }
    function getFromToUserIDs($action_id){
        $author_id = $this->getAuthorID($action_id);
        $authorIDLastResultItem = $this->getAuthorIDLastResultAction($action_id);
        if($authorIDLastResultItem == 0 || $author_id == $authorIDLastResultItem){
             $this->fetch($action_id);
            $for_user = array_keys($this->userassigned)[1];
            $from_user = $author_id;
        }else{
            $for_user = $author_id;
            $from_user = $authorIDLastResultItem;
        }
        return array('for'=>$for_user, 'from'=>$from_user);
    }
    function ShowAction($id_usr, $from_user, $action_id, $when_show){
//        echo '<pre>';
//        var_dump($id_usr, $from_user, $action_id, $when_show);
//        echo '</pre>';
//        die();
        global $db;
        $sql = "select count(*) iCount from llx_newactions where id_usr = $id_usr and id_action = $action_id";
        $res = $db->query($sql);
        $obj_count = $db->fetch_object($res);
        if($obj_count->iCount == 0){
            $sql = "insert into llx_newactions(id_usr,from_user,id_action,when_show) values($id_usr,$from_user,$action_id,'$when_show')";
            $res = $db->query($sql);
            if(!$res)
                $db->lasterror();
        }
        if($res)
            return 1;
    }
    function getUsersIDFromSociete($socid){
        global $db;
        $sql = "select distinct `llx_user_regions`.fk_user, llx_societe.region_id from llx_societe
            inner join `responsibility_param` on `responsibility_param`.`fx_category_counterparty` = llx_societe.`categoryofcustomer_id`
            inner join `llx_user_regions` on `llx_user_regions`.`fk_id` = llx_societe.region_id
            inner join `llx_user` on llx_user.rowid = `llx_user_regions`.fk_user
            where llx_societe.rowid = $socid
            and `llx_user_regions`.`active` = 1
            and (`llx_user`.`respon_id` = `responsibility_param`.`fx_responsibility` or `llx_user`.`respon_id2` = `responsibility_param`.`fx_responsibility`)
            and `llx_user`.`active` = 1;";
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
        $usersID = [];
        while($obj = $db->fetch_object($res)){
            $usersID[]=$obj->fk_user;    
        }
        return $usersID;    
    }
    function setDateAction($socid){
        global $db;
        //Остання дата співпраці
        $sql = "select max(dtChange) dtDate from llx_societe_action where socid = " . $socid;
        $up_res = $db->query($sql);
        $up_obj = $db->fetch_object($up_res);
        if (!empty($up_obj->dtDate)) {
            $date = new DateTime($up_obj->dtDate);
            $sql = "update llx_societe set lastdate = '" . $date->format('Y-m-d') . "' where rowid = " . $socid;
            $up_res = $db->query($sql);
            if (!$up_res)
                dol_print_error($db);
        }
        //Остання і майбутня дата взаємодії
        $sql = "select `llx_societe_action`.active, `llx_actioncomm`.`percent`, `llx_actioncomm`.datep from llx_actioncomm
        left join `llx_societe_action` on `action_id` = llx_actioncomm.id
        where fk_soc = $socid
        and code <> 'AC_OTH_AUTO'
        and llx_actioncomm.active = 1
        order by `llx_actioncomm`.datep desc
        limit 2";
        $up_res = $db->query($sql);
        if ($up_res->num_rows) {
            while ($up_obj = $db->fetch_object($up_res)) {
                if (empty($up_obj->active) && $up_obj->percent <= 0) {
                    $date = new DateTime($up_obj->datep);
                    $sql = "update llx_societe set futuredatecomerc = '" . $date->format('Y-m-d') . "' where rowid = " . $socid;
                    $db->query($sql);
                } elseif (!empty($up_obj->active) && $up_obj->percent == 100) {
                    $date = new DateTime($up_obj->datep);
                    $sql = "update llx_societe set lastdatecomerc = '" . $date->format('Y-m-d') . "' where rowid = " . $socid;
                    $db->query($sql);
                }
            }
        }
    }/**
     *    Initialize this->userassigned array with list of id of user assigned to event
     *
     *    @return	int				<0 if KO, >0 if OK
     */
    function fetch_userassigned()
    {
        $sql ="SELECT fk_actioncomm, element_type, fk_element, answer_status, mandatory, transparency";
		$sql.=" FROM ".MAIN_DB_PREFIX."actioncomm_resources";
		$sql.=" WHERE element_type = 'user' AND fk_actioncomm = ".$this->id;
		$resql2=$this->db->query($sql);
		if ($resql2)
		{
			$this->userassigned=array();

			// If owner is known, we must but id first into list
			if ($this->userownerid > 0) $this->userassigned[$this->userownerid]=array('id'=>$this->userownerid);	// Set first so will be first into list.

            while ($obj = $this->db->fetch_object($resql2))
            {
            	if ($obj->fk_element > 0) $this->userassigned[$obj->fk_element]=array('id'=>$obj->fk_element, 'mandatory'=>$obj->mandatory, 'answer_status'=>$obj->answer_status, 'transparency'=>$obj->transparency);
            	if (empty($this->userownerid)) $this->userownerid=$obj->fk_element;	// If not defined (should not happened, we fix this)
            }

        	return 1;
		}
		else
		{
			dol_print_error($this->db);
			return -1;
		}
    }

    /**
     *    Delete event from database
     *
     *    @param    int		$notrigger		1 = disable triggers, 0 = enable triggers
     *    @return   int 					<0 if KO, >0 if OK
     */
    function delete($notrigger=0)
    {
        global $user,$langs,$conf;

        $error=0;

        $this->db->begin();

        $sql = "DELETE FROM ".MAIN_DB_PREFIX."actioncomm";
        $sql.= " WHERE id=".$this->id;

        dol_syslog(get_class($this)."::delete", LOG_DEBUG);
        $res=$this->db->query($sql);
        if ($res < 0) {
        	$this->error=$this->db->lasterror();
        	$error++;
        }

        // Removed extrafields
        if (! $error) {
        	$result=$this->deleteExtraFields();
          	if ($result < 0)
           	{
           		$error++;
           		dol_syslog(get_class($this)."::delete error -3 ".$this->error, LOG_ERR);
           	}
        }

        if (!$error)
        {
            if (! $notrigger)
            {
                // Call trigger
                $result=$this->call_trigger('ACTION_DELETE',$user);
                if ($result < 0) { $error++; }
                // End call triggers
            }

            if (! $error)
            {
                $this->db->commit();
                return 1;
            }
            else
            {
                $this->db->rollback();
                return -2;
            }
        }
        else
        {
            $this->db->rollback();
            $this->error=$this->db->lasterror();
            return -1;
        }
    }

    /**
     *    Update action into database
     *	  If percentage = 100, on met a jour date 100%
     *
     *    @param    User	$user			Object user making change
     *    @param    int		$notrigger		1 = disable triggers, 0 = enable triggers
     *    @return   int     				<0 if KO, >0 if OK
     */
    function update($user,$notrigger=0)
    {
        global $langs,$conf,$hookmanager;

        $error=0;

        // Clean parameters
        $this->label=trim($this->label);
        $this->note=trim($this->note);
        if (empty($this->percentage))    $this->percentage = 0;
        if (empty($this->priority) || ! is_numeric($this->priority)) $this->priority = 0;
        if (empty($this->transparency))  $this->transparency = 0;
        if (empty($this->fulldayevent))  $this->fulldayevent = 0;
        if ($this->percentage > 100) $this->percentage = 100;
        //if ($this->percentage == 100 && ! $this->dateend) $this->dateend = $this->date;
        if ($this->datep && $this->datef)   $this->durationp=($this->datef - $this->datep);		// deprecated
        //if ($this->date  && $this->dateend) $this->durationa=($this->dateend - $this->date);
        if ($this->datep && $this->datef && $this->datep > $this->datef) $this->datef=$this->datep;
        //if ($this->date  && $this->dateend && $this->date > $this->dateend) $this->dateend=$this->date;
        if ($this->fk_project < 0) $this->fk_project = 0;

        // Check parameters
//        if ($this->percentage == 0 && $this->userdoneid > 0)
//        {
//            $this->error="ErrorCantSaveADoneUserWithZeroPercentage";
//            return -1;
//        }

        $socid=($this->socid?$this->socid:((isset($this->societe->id) && $this->societe->id > 0) ? $this->societe->id : 0));
        $contactid=($this->contactid?$this->contactid:((isset($this->contact->id) && $this->contact->id > 0) ? $this->contact->id : 0));
		$userownerid=($this->userownerid?$this->userownerid:0);
		$userdoneid=($this->userdoneid?$this->userdoneid:0);

        $this->db->begin();

        $sql = "UPDATE ".MAIN_DB_PREFIX."actioncomm ";
        $sql.= " SET `new`= 1, label = ".($this->label ? "'".$this->db->escape($this->label)."'":"null");
        if ($this->fk_action > 0) $sql.= ", fk_action = '".$this->fk_action."'";
//        var_dump($this->planed_cost);
//        die();
        if($this->authorid == $user->id && $this->percentage == 100 || $this->percentage != 100)
            $sql.= ", percent = '".$this->percentage."'";
        $sql.= ", datep = ".(strval($this->datep)!='' ? "'".$this->db->idate($this->datep)."'" : 'null');
        $sql.= ", datep2 = ".(strval($this->datef)!='' ? "'".$this->db->idate($this->datef)."'" : 'null');
        $sql.= ", datepreperform = ".(strval(date('Y-m-d',$this->datepreperform))!='' ? "'".$this->db->idate(date('Y-m-d',$this->datepreperform))."'" : 'null');
        $sql.= ", durationp = ".(isset($this->durationp) && $this->durationp >= 0 && $this->durationp != ''?"'".$this->durationp."'":"null");	// deprecated
        $sql.= ", note = ".($this->note ? "'".$this->db->escape($this->note)."'":"null");
        $sql.= ", type = ".($this->typeSetOfDate ? "'".$this->db->escape($this->typeSetOfDate)."'":"null");
        $sql.= ", fk_project =". ($this->fk_project > 0 ? "'".$this->fk_project."'":"null");
        $sql.= ", fk_soc =". ($socid > 0 ? "'".$socid."'":"null");
        $sql.= ", fk_contact =". ($contactid > 0 ? "'".$contactid."'":"null");
        $sql.= ", priority = '".$this->priority."'";
        $sql.= ", fulldayevent = '".$this->fulldayevent."'";
        $sql.= ", location = ".($this->location ? "'".$this->db->escape($this->location)."'":"null");
        $sql.= ", transparency = '".$this->transparency."'";
        $sql.= ", typenotification = '".$this->typenotification."'";
        $sql.= ", fk_user_mod = '".$user->id."'";
        $sql.= ", fk_user_action=".($userownerid > 0 ? "'".$userownerid."'":"null");
        $sql.= ", fk_user_done=".($userdoneid > 0 ? "'".$userdoneid."'":"null");
        $sql.= ", fk_groupoftask=".($this->groupoftask > 0 ? $this->groupoftask :"null");
        $sql.= ", period=".(!empty($this->period) ?("'".$this->period."'") :"null");
        $sql.= ", planed_cost=".(!empty($this->planed_cost) ?("'".$this->planed_cost."'") :"null");
        $sql.= ", fact_cost=".(!empty($this->fact_cost) ?("'".$this->fact_cost."'") :"null");
        $sql.= ", motivator=".(!empty($this->motivator) ?("'".$this->motivator."'") :"null");
        $sql.= ", demotivator=".(!empty($this->demotivator) ?("'".$this->demotivator."'") :"null");
        $sql.= ", fk_user_valuer=".(!empty($this->user_valuer) ?$this->fk_user_valuer :"null");
        if (! empty($this->fk_element)) $sql.= ", fk_element=".($this->fk_element?$this->fk_element:"null");
        if (! empty($this->elementtype)) $sql.= ", elementtype=".($this->elementtype?"'".$this->elementtype."'":"null");
        $sql.= " WHERE id=".$this->id;
//		echo '<pre>';
//		var_dump($sql);
//		echo '</pre>';
//		die();
//        die($sql);
        dol_syslog(get_class($this)."::update", LOG_DEBUG);
        if ($this->db->query($sql))
        {
			$action='update';

        	// Actions on extra fields (by external module or standard code)
			// FIXME le hook fait double emploi avec le trigger !!
        	$hookmanager->initHooks(array('actioncommdao'));
        	$parameters=array('actcomm'=>$this->id);
        	$reshook=$hookmanager->executeHooks('insertExtraFields',$parameters,$this,$action);    // Note that $action and $object may have been modified by some hooks
        	if (empty($reshook))
        	{
        		if (empty($conf->global->MAIN_EXTRAFIELDS_DISABLED)) // For avoid conflicts if trigger used
        		{
        			$result=$this->insertExtraFields();
        			if ($result < 0)
        			{
        				$error++;
        			}
        		}
        	}
        	else if ($reshook < 0) $error++;

            // Now insert assignedusers
			if (! $error)
			{
				$sql ="DELETE FROM ".MAIN_DB_PREFIX."actioncomm_resources where fk_actioncomm = ".$this->id." AND element_type = 'user'";
				$resql = $this->db->query($sql);

				foreach($this->userassigned as $key => $val)
				{
                    if($user->id != $val['id']) {
                        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "actioncomm_resources(fk_actioncomm, element_type, fk_element, mandatory, transparency, answer_status)";
                        $sql .= " VALUES(" . $this->id . ", 'user', " . $val['id'] . ", " . (empty($val['manadatory']) ? '0' : $val['manadatory']) . ", " . (empty($val['transparency']) ? '0' : $val['transparency']) . ", " . (empty($val['answer_status']) ? '0' : $val['answer_status']) . ")";

                        $resql = $this->db->query($sql);
                        if (!$resql) {
                            $error++;
                            $this->errors[] = $this->db->lasterror();
                        }
                    }
					//var_dump($sql);exit;
				}
			}

            if (! $error && ! $notrigger)
            {
                // Call trigger
                $result=$this->call_trigger('ACTION_MODIFY',$user);
                if ($result < 0) { $error++; }
                // End call triggers
            }

            if (! $error)
            {
                $this->db->commit();
                return 1;
            }
            else
            {
                $this->db->rollback();
                dol_syslog(get_class($this)."::update ".join(',',$this->errors),LOG_ERR);
                return -2;
            }
        }
        else
        {
            $this->db->rollback();
            $this->error=$this->db->lasterror();
            return -1;
        }
    }

    /**
     *   Load all objects with filters
     *
     *   @param		DoliDb	$db				Database handler
     *   @param		int		$socid			Filter by thirdparty
     * 	 @param		int		$fk_element		Id of element action is linked to
     *   @param		string	$elementtype	Type of element action is linked to
     *   @param		string	$filter			Other filter
     *   @param		string	$sortfield		Sort on this field
     *   @param		string	$sortorder		ASC or DESC
     *   @return	array or string			Error string if KO, array with actions if OK
     */
    static function setCronJobStatus($jobname){
        
    }
    static function getActions($db, $socid=0, $fk_element=0, $elementtype='', $filter='', $sortfield='', $sortorder='')
    {
        global $conf, $langs;

        $resarray=array();

        $sql = "SELECT a.id";
        $sql.= " FROM ".MAIN_DB_PREFIX."actioncomm as a";
        $sql.= " WHERE a.entity = ".$conf->entity;
        if (! empty($socid)) $sql.= " AND a.fk_soc = ".$socid;
        if (! empty($elementtype))
        {
            if ($elementtype == 'project') $sql.= ' AND a.fk_project = '.$fk_element;
            else $sql.= " AND a.fk_element = ".$fk_element." AND a.elementtype = '".$elementtype."'";
        }
        if (! empty($filter)) $sql.= $filter;
		if ($sortorder && $sortfield) $sql.=$db->order($sortfield, $sortorder);

        dol_syslog(get_class()."::getActions", LOG_DEBUG);
        $resql=$db->query($sql);
        if ($resql)
        {
            $num = $db->num_rows($resql);

            if ($num)
            {
                for($i=0;$i<$num;$i++)
                {
                    $obj = $db->fetch_object($resql);
                    $actioncommstatic = new ActionComm($db);
                    $actioncommstatic->fetch($obj->id);
                    $resarray[$i] = $actioncommstatic;
                }
            }
            $db->free($resql);
            return $resarray;
        }
        else
       {
            return $db->lasterror();
        }
    }

    /**
     *      Load indicators for dashboard (this->nbtodo and this->nbtodolate)
     *
     *      @param	User	$user   Objet user
     *      @return int     		<0 if KO, >0 if OK
     */
    
    function load_board($user)
    {
        global $conf, $user;

        $now=dol_now();

        $this->nbtodo=$this->nbtodolate=0;

        $sql = "SELECT a.id, a.datep as dp";
        $sql.= " FROM (".MAIN_DB_PREFIX."actioncomm as a";
        $sql.= ")";
        if (! $user->rights->societe->client->voir && ! $user->societe_id) $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe_commerciaux as sc ON a.fk_soc = sc.fk_soc";
        $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON a.fk_soc = s.rowid";
        $sql.= " WHERE a.percent >= 0 AND a.percent < 100";
        $sql.= " AND a.entity = ".$conf->entity;
        if (! $user->rights->societe->client->voir && ! $user->societe_id) $sql.= " AND (a.fk_soc IS NULL OR sc.fk_user = " .$user->id . ")";
        if ($user->societe_id) $sql.=" AND a.fk_soc = ".$user->societe_id;
        if (! $user->rights->agenda->allactions->read) $sql.= " AND (a.fk_user_author = ".$user->id . " OR a.fk_user_action = ".$user->id . " OR a.fk_user_done = ".$user->id . ")";

        $resql=$this->db->query($sql);
        if ($resql)
        {
            // This assignment in condition is not a bug. It allows walking the results.
            while ($obj=$this->db->fetch_object($resql))
            {
                $this->nbtodo++;
                if (isset($obj->dp) && $this->db->jdate($obj->dp) < ($now - $conf->actions->warning_delay)) $this->nbtodolate++;
            }
            return 1;
        }
        else
        {
            $this->error=$this->db->error();
            return -1;
        }
    }


    /**
     *      Charge les informations d'ordre info dans l'objet facture
     *
     *      @param	int		$id       	Id de la facture a charger
     *		@return	void
     */
    function info($id)
    {
        $sql = 'SELECT ';
        $sql.= ' a.id,';
        $sql.= ' datec,';
        $sql.= ' code,';
        $sql.= ' tms as datem,';
        $sql.= ' fk_user_author,';
        $sql.= ' fk_user_mod';
        $sql.= ' FROM '.MAIN_DB_PREFIX.'actioncomm as a';
        $sql.= ' WHERE a.id = '.$id;

        dol_syslog(get_class($this)."::info", LOG_DEBUG);
        $result=$this->db->query($sql);
        if ($result)
        {
            if ($this->db->num_rows($result))
            {
                $obj = $this->db->fetch_object($result);
                $this->id = $obj->id;
                $this->code = $obj->code;
                if ($obj->fk_user_author)
                {
                    $cuser = new User($this->db);
                    $cuser->fetch($obj->fk_user_author);
                    $this->user_creation     = $cuser;
                }
                if ($obj->fk_user_mod)
                {
                    $muser = new User($this->db);
                    $muser->fetch($obj->fk_user_mod);
                    $this->user_modification = $muser;
                }

                $this->date_creation     = $this->db->jdate($obj->datec);
                $this->date_modification = $this->db->jdate($obj->datem);
            }
            $this->db->free($result);
        }
        else
        {
            dol_print_error($this->db);
        }
    }


    /**
     *    	Return label of status
     *
     *    	@param	int		$mode           0=libelle long, 1=libelle court, 2=Picto + Libelle court, 3=Picto, 4=Picto + Libelle long, 5=Libelle court + Picto
     *      @param  int		$hidenastatus   1=Show nothing if status is "Not applicable"
     *    	@return string          		String with status
     */
    function getLibStatut($mode,$hidenastatus=0)
    {
        return $this->LibStatut($this->percentage,$mode,$hidenastatus);
    }

    /**
     *		Return label of action status
     *
     *    	@param	int		$percent        Percent
     *    	@param  int		$mode           0=Long label, 1=Short label, 2=Picto+Short label, 3=Picto, 4=Picto+Short label, 5=Short label+Picto, 6=Very short label+Picto
     *      @param  int		$hidenastatus   1=Show nothing if status is "Not applicable"
     *    	@return string		    		Label
     */
    function LibStatut($percent,$mode,$hidenastatus=0)
    {
        global $langs;

        if ($mode == 0)
        {
        	if ($percent==-1 && ! $hidenastatus) return $langs->trans('StatusNotApplicable');
        	else if ($percent==0) return $langs->trans('StatusActionToDo').' (0%)';
        	else if ($percent > 0 && $percent < 100) return $langs->trans('StatusActionInProcess').' ('.$percent.'%)';
        	else if ($percent >= 100) return $langs->trans('StatusActionDone').' (100%)';
        }
        else if ($mode == 1)
        {
        	if ($percent==-1 && ! $hidenastatus) return $langs->trans('StatusNotApplicable');
        	else if ($percent==0) return $langs->trans('StatusActionToDo');
        	else if ($percent > 0 && $percent < 100) return $percent.'%';
        	else if ($percent >= 100) return $langs->trans('StatusActionDone');
        }
        else if ($mode == 2)
        {
        	if ($percent==-1 && ! $hidenastatus) return img_picto($langs->trans('StatusNotApplicable'),'statut9').' '.$langs->trans('StatusNotApplicable');
        	else if ($percent==0) return img_picto($langs->trans('StatusActionToDo'),'statut1').' '.$langs->trans('StatusActionToDo');
        	else if ($percent > 0 && $percent < 100) return img_picto($langs->trans('StatusActionInProcess'),'statut3').' '. $percent.'%';
        	else if ($percent >= 100) return img_picto($langs->trans('StatusActionDone'),'statut6').' '.$langs->trans('StatusActionDone');
        }
        else if ($mode == 3)
        {
        	if ($percent==-1 && ! $hidenastatus) return img_picto($langs->trans("Status").': '.$langs->trans('StatusNotApplicable'),'statut9');
        	else if ($percent==0) return img_picto($langs->trans("Status").': '.$langs->trans('StatusActionToDo').' (0%)','statut1');
        	else if ($percent > 0 && $percent < 100) return img_picto($langs->trans("Status").': '.$langs->trans('StatusActionInProcess').' ('.$percent.'%)','statut3');
        	else if ($percent >= 100) return img_picto($langs->trans("Status").': '.$langs->trans('StatusActionDone').' (100%)','statut6');
        }
        else if ($mode == 4)
        {
        	if ($percent==-1 && ! $hidenastatus) return img_picto($langs->trans('StatusNotApplicable'),'statut9').' '.$langs->trans('StatusNotApplicable');
        	else if ($percent==0) return img_picto($langs->trans('StatusActionToDo'),'statut1').' '.$langs->trans('StatusActionToDo').' (0%)';
        	else if ($percent > 0 && $percent < 100) return img_picto($langs->trans('StatusActionInProcess'),'statut3').' '.$langs->trans('StatusActionInProcess').' ('.$percent.'%)';
        	else if ($percent >= 100) return img_picto($langs->trans('StatusActionDone'),'statut6').' '.$langs->trans('StatusActionDone').' (100%)';
        }
        else if ($mode == 5)
        {
        	if ($percent==-1 && ! $hidenastatus) return img_picto($langs->trans('StatusNotApplicable'),'statut9');
        	else if ($percent==0) return '0% '.img_picto($langs->trans('StatusActionToDo'),'statut1');
        	else if ($percent > 0 && $percent < 100) return $percent.'% '.img_picto($langs->trans('StatusActionInProcess').' - '.$percent.'%','statut3');
        	else if ($percent >= 100) return $langs->trans('StatusActionDone').' '.img_picto($langs->trans('StatusActionDone'),'statut6');
        }
        else if ($mode == 6)
        {
        	if ($percent==-1 && ! $hidenastatus) return img_picto($langs->trans('StatusNotApplicable'),'statut9');
        	else if ($percent==0) return '0% '.img_picto($langs->trans('StatusActionToDo'),'statut1');
        	else if ($percent > 0 && $percent < 100) return $percent.'% '.img_picto($langs->trans('StatusActionInProcess').' - '.$percent.'%','statut3');
        	else if ($percent >= 100) return img_picto($langs->trans('StatusActionDone'),'statut6');
        }
        return '';
    }

    /**
     *    	Return URL of event
     *      Use $this->id, $this->type_code, $this->label and $this->type_label
     *
     * 		@param	int		$withpicto			0=Pas de picto, 1=Inclut le picto dans le lien, 2=Picto seul
     *		@param	int		$maxlength			Nombre de caracteres max dans libelle
     *		@param	string	$classname			Force style class on a link
     * 		@param	string	$option				''=Link to action,'birthday'=Link to contact
     * 		@param	int		$overwritepicto		1=Overwrite picto
     *		@return	string						Chaine avec URL
     */
    function getNomUrl($withpicto=0,$maxlength=0,$classname='',$option='',$overwritepicto=0)
    {
        global $conf,$langs;

        $result='';
        if ($option=='birthday') $lien = '<a '.($classname?'class="'.$classname.'" ':'').'href="'.DOL_URL_ROOT.'/contact/perso.php?id='.$this->id.'">';
        else $lien = '<a '.($classname?'class="'.$classname.'" ':'').'href="'.DOL_URL_ROOT.'/comm/action/card.php?id='.$this->id.'">';
        $lienfin='</a>';
        $label=$this->label;
        if (empty($label)) $label=$this->libelle;	// For backward compatibility
        //print 'rrr'.$this->libelle.'-'.$withpicto;

        if ($withpicto == 2)
        {
            $libelle=$label;
        	if (! empty($conf->global->AGENDA_USE_EVENT_TYPE)) $libelle=$langs->transnoentities("Action".$this->type_code);
            $libelleshort='';
        }
        else
       {
       		$libelle=(empty($this->libelle)?$label:$this->libelle.(($label && $label != $this->libelle)?' '.$label:''));
       		if (! empty($conf->global->AGENDA_USE_EVENT_TYPE) && empty($libelle)) $libelle=($langs->transnoentities("Action".$this->type_code) != "Action".$this->type_code)?$langs->transnoentities("Action".$this->type_code):$this->type_label;
       		$libelleshort=dol_trunc($libelle,$maxlength);
        }

        if ($withpicto)
        {
        	if (! empty($conf->global->AGENDA_USE_EVENT_TYPE))	// Add code into ()
        	{
        		 $libelle.=(($this->type_code && $libelle!=$langs->transnoentities("Action".$this->type_code) && $langs->transnoentities("Action".$this->type_code)!="Action".$this->type_code)?' ('.$langs->transnoentities("Action".$this->type_code).')':'');
        	}
            $result.=$lien.img_object($langs->trans("ShowAction").': '.$libelle,($overwritepicto?$overwritepicto:'action')).$lienfin;
        }
        if ($withpicto==1) $result.=' ';
        $result.=$lien.$libelleshort.$lienfin;
        return $result;
    }


    /**
     *		Export events from database into a cal file.
     *
     *		@param	string		$format			'vcal', 'ical/ics', 'rss'
     *		@param	string		$type			'event' or 'journal'
     *		@param	int			$cachedelay		Do not rebuild file if date older than cachedelay seconds
     *		@param	string		$filename		Force filename
     *		@param	array		$filters		Array of filters
     *		@return int     					<0 if error, nb of events in new file if ok
     */
    function build_exportfile($format,$type,$cachedelay,$filename,$filters)
    {
        global $conf,$langs,$dolibarr_main_url_root,$mysoc;

        require_once (DOL_DOCUMENT_ROOT ."/core/lib/xcal.lib.php");
        require_once (DOL_DOCUMENT_ROOT ."/core/lib/date.lib.php");
        require_once (DOL_DOCUMENT_ROOT ."/core/lib/files.lib.php");

        dol_syslog(get_class($this)."::build_exportfile Build export file format=".$format.", type=".$type.", cachedelay=".$cachedelay.", filename=".$filename.", filters size=".count($filters), LOG_DEBUG);

        // Check parameters
        if (empty($format)) return -1;

        // Clean parameters
        if (! $filename)
        {
            $extension='vcs';
            if ($format == 'ical') $extension='ics';
            $filename=$format.'.'.$extension;
        }

        // Create dir and define output file (definitive and temporary)
        $result=dol_mkdir($conf->agenda->dir_temp);
        $outputfile=$conf->agenda->dir_temp.'/'.$filename;

        $result=0;

        $buildfile=true;
        $login='';$logina='';$logind='';$logint='';

        $now = dol_now();

        if ($cachedelay)
        {
            $nowgmt = dol_now();
            include_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
            if (dol_filemtime($outputfile) > ($nowgmt - $cachedelay))
            {
                dol_syslog(get_class($this)."::build_exportfile file ".$outputfile." is not older than now - cachedelay (".$nowgmt." - ".$cachedelay."). Build is canceled");
                $buildfile = false;
            }
        }

        if ($buildfile)
        {
            // Build event array
            $eventarray=array();

            $sql = "SELECT a.id,";
            $sql.= " a.datep,";		// Start
            $sql.= " a.datep2,";	// End
            $sql.= " a.durationp,";			// deprecated
            $sql.= " a.datec, a.tms as datem,";
            $sql.= " a.label, a.code, a.note, a.fk_action as type_id,";
            $sql.= " a.fk_soc,";
            $sql.= " a.fk_user_author, a.fk_user_mod,";
            $sql.= " a.fk_user_action,";
            $sql.= " a.fk_contact, a.percent as percentage,";
            $sql.= " a.fk_element, a.elementtype,";
            $sql.= " a.priority, a.fulldayevent, a.location, a.punctual, a.transparency,";
            $sql.= " u.firstname, u.lastname,";
            $sql.= " s.nom as socname,";
            $sql.= " c.id as type_id, c.code as type_code, c.libelle";
            $sql.= " FROM (".MAIN_DB_PREFIX."c_actioncomm as c, ".MAIN_DB_PREFIX."actioncomm as a)";
            $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."user as u on u.rowid = a.fk_user_author";	// Link to get author of event for export
            $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s on s.rowid = a.fk_soc";
			// We must filter on assignement table
			if ($filters['logint'] || $filters['login']) $sql.=", ".MAIN_DB_PREFIX."actioncomm_resources as ar";
			$sql.= " WHERE a.fk_action=c.id";
            $sql.= " AND a.entity = ".$conf->entity;
            foreach ($filters as $key => $value)
            {
                if ($key == 'notolderthan' && $value != '') $sql.=" AND a.datep >= '".$this->db->idate($now-($value*24*60*60))."'";
                if ($key == 'year')         $sql.=" AND a.datep BETWEEN '".$this->db->idate(dol_get_first_day($value,1))."' AND '".$this->db->idate(dol_get_last_day($value,12))."'";
                if ($key == 'id')           $sql.=" AND a.id=".(is_numeric($value)?$value:0);
                if ($key == 'idfrom')       $sql.=" AND a.id >= ".(is_numeric($value)?$value:0);
                if ($key == 'idto')         $sql.=" AND a.id <= ".(is_numeric($value)?$value:0);
                if ($key == 'project')      $sql.=" AND a.fk_project=".(is_numeric($value)?$value:0);
    	        // We must filter on assignement table
				if ($key == 'logint' || $key == 'login') $sql.= " AND ar.fk_actioncomm = a.id AND ar.element_type='user'";
                if ($key == 'logina')
                {
                    $logina=$value;
                    $userforfilter=new User($this->db);
                    $result=$userforfilter->fetch('',$value);
                    $sql.= " AND a.fk_user_author = ".$userforfilter->id;
                }
                if ($key == 'logint' || $key == 'login')
                {
                    $logint=$value;
                    $userforfilter=new User($this->db);
                    $result=$userforfilter->fetch('',$value);
                    $sql.= " AND ar.fk_element = ".$userforfilter->id;
                }
            }
            $sql.= " AND a.datep IS NOT NULL";		// To exclude corrupted events and avoid errors in lightning/sunbird import
            $sql.= " ORDER by datep";
            //print $sql;exit;

            dol_syslog(get_class($this)."::build_exportfile select events", LOG_DEBUG);
            $resql=$this->db->query($sql);
            if ($resql)
            {
                // Note: Output of sql request is encoded in $conf->file->character_set_client
                // This assignment in condition is not a bug. It allows walking the results.
                while ($obj=$this->db->fetch_object($resql))
                {
                    $qualified=true;

                    // 'eid','startdate','duration','enddate','title','summary','category','email','url','desc','author'
                    $event=array();
                    $event['uid']='dolibarragenda-'.$this->db->database_name.'-'.$obj->id."@".$_SERVER["SERVER_NAME"];
                    $event['type']=$type;
                    $datestart=$this->db->jdate($obj->datep)-(empty($conf->global->AGENDA_EXPORT_FIX_TZ)?0:($conf->global->AGENDA_EXPORT_FIX_TZ*3600));
                    $dateend=$this->db->jdate($obj->datep2)-(empty($conf->global->AGENDA_EXPORT_FIX_TZ)?0:($conf->global->AGENDA_EXPORT_FIX_TZ*3600));
                    $duration=($datestart && $dateend)?($dateend - $datestart):0;
                    $event['summary']=$obj->label.($obj->socname?" (".$obj->socname.")":"");
                    $event['desc']=$obj->note;
                    $event['startdate']=$datestart;
                    $event['enddate']=$dateend;		// Not required with type 'journal'
                    $event['duration']=$duration;	// Not required with type 'journal'
                    $event['author']=dolGetFirstLastname($obj->firstname, $obj->lastname);
                    $event['priority']=$obj->priority;
                    $event['fulldayevent']=$obj->fulldayevent;
                    $event['location']=$obj->location;
                    $event['transparency']=(($obj->transparency > 0)?'OPAQUE':'TRANSPARENT');		// OPAQUE (busy) or TRANSPARENT (not busy)
                    $event['punctual']=$obj->punctual;
                    $event['category']=$obj->libelle;	// libelle type action
					// Define $urlwithroot
					$urlwithouturlroot=preg_replace('/'.preg_quote(DOL_URL_ROOT,'/').'$/i','',trim($dolibarr_main_url_root));
					$urlwithroot=$urlwithouturlroot.DOL_URL_ROOT;			// This is to use external domain name found into config file
					//$urlwithroot=DOL_MAIN_URL_ROOT;						// This is to use same domain name than current
                    $url=$urlwithroot.'/comm/action/card.php?id='.$obj->id;
                    $event['url']=$url;
                    $event['created']=$this->db->jdate($obj->datec)-(empty($conf->global->AGENDA_EXPORT_FIX_TZ)?0:($conf->global->AGENDA_EXPORT_FIX_TZ*3600));
                    $event['modified']=$this->db->jdate($obj->datem)-(empty($conf->global->AGENDA_EXPORT_FIX_TZ)?0:($conf->global->AGENDA_EXPORT_FIX_TZ*3600));

                    if ($qualified && $datestart)
                    {
                        $eventarray[$datestart]=$event;
                    }
                }
            }
            else
            {
                $this->error=$this->db->lasterror();
                return -1;
            }

            $langs->load("agenda");

            // Define title and desc
            $more='';
            if ($login)  $more=$langs->transnoentities("User").' '.$login;
            if ($logina) $more=$langs->transnoentities("ActionsAskedBy").' '.$logina;
            if ($logint) $more=$langs->transnoentities("ActionsToDoBy").' '.$logint;
            if ($logind) $more=$langs->transnoentities("ActionsDoneBy").' '.$logind;
            if ($more)
            {
                $title='Dolibarr actions '.$mysoc->name.' - '.$more;
                $desc=$more;
                $desc.=' ('.$mysoc->name.' - built by Dolibarr)';
            }
            else
            {
                $title='Dolibarr actions '.$mysoc->name;
                $desc=$langs->transnoentities('ListOfActions');
                $desc.=' ('.$mysoc->name.' - built by Dolibarr)';
            }

            // Create temp file
            $outputfiletmp=tempnam($conf->agenda->dir_temp,'tmp');  // Temporary file (allow call of function by different threads
            @chmod($outputfiletmp, octdec($conf->global->MAIN_UMASK));

            // Write file
            if ($format == 'vcal') $result=build_calfile($format,$title,$desc,$eventarray,$outputfiletmp);
            if ($format == 'ical') $result=build_calfile($format,$title,$desc,$eventarray,$outputfiletmp);
            if ($format == 'rss')  $result=build_rssfile($format,$title,$desc,$eventarray,$outputfiletmp);

            if ($result >= 0)
            {
                if (dol_move($outputfiletmp,$outputfile,0,1)) $result=1;
                else
                {
                	$this->error='Failed to rename '.$outputfiletmp.' into '.$outputfile;
                    dol_syslog(get_class($this)."::build_exportfile ".$this->error, LOG_ERR);
                    dol_delete_file($outputfiletmp,0,1);
                    $result=-1;
                }
            }
            else
            {
                dol_syslog(get_class($this)."::build_exportfile build_xxxfile function fails to for format=".$format." outputfiletmp=".$outputfile, LOG_ERR);
                dol_delete_file($outputfiletmp,0,1);
                $langs->load("errors");
                $this->error=$langs->trans("ErrorFailToCreateFile",$outputfile);
            }
        }

        return $result;
    }

    /**
     *  Initialise an instance with random values.
     *  Used to build previews or test instances.
     *	id must be 0 if object instance is a specimen.
     *
     *  @return	void
     */
    function initAsSpecimen()
    {
        global $user;

        $now=dol_now();

        // Initialise parametres
        $this->id=0;
        $this->specimen=1;

        $this->type_code='AC_OTH';
        $this->code='AC_SPECIMEN_CODE';
        $this->label='Label of event Specimen';
        $this->datec=$now;
        $this->datem=$now;
        $this->datep=$now;
        $this->datef=$now;
        $this->author=$user;
        $this->usermod=$user;
        $this->usertodo=$user;
        $this->fulldayevent=0;
        $this->punctual=0;
        $this->percentage=0;
        $this->location='Location';
        $this->transparency=1;	// 1 means opaque
        $this->priority=1;
        $this->note = 'Note';

        $this->userownerid=$user->id;
        $this->userassigned[$user->id]=array('id'=>$user->id, 'transparency'=> 1);
    }

}

