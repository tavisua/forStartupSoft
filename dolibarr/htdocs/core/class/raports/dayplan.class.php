<?php

/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 24.07.2017
 * Time: 17:17
 */
class DayPlan
{
    var $db;
    var $ClassList = array(array('sale'=>'regions', 'purchase'=>'CategoryCounterParty'), 'userlist', 'subdivision', 'company');
    var $statistictable = 'statistic_action1';
    var $today;
    var $Count = 0;
    var $ExecutedPrecent = [100,-100,99];

    function __construct($db)
    {
        $this->db=$db;
        $this->today = new DateTime();
    }

    function RefreshRaport(){//Перебудова всього звіту
        set_time_limit(0);
        $this->ClearRaport();

        $sql = "select id from llx_actioncomm where datep between adddate(date(now()), interval -1 month) and adddate(date(now()), interval 1 month) and active = 1 and code <> 'AC_OTH_AUTO'";
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
        while($obj = $this->db->fetch_object($res)){
            $this->SaveAction($obj->id);
        }

//        //Розрахунок статистики
//        foreach ($this->ClassList as $key => $value) {
//            if (is_array($value)) {
//                foreach ($value as $array_key=>$class_block){
//                    $this->CalcStatisticBlock($class_block);
//                }
//            }else {
//                $sql = "select distinct id_usr from statistic_action1 where class_block='regions'";
//                $res = $this->db->query($sql);
//                if(!$res)
//                    dol_print_error($this->db);
//                while ($obj = $this->db->fetch_object($res)) {
//                    require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
//                    $useraction = new User($this->db);
//                    $useraction->fetch($obj->id_usr);
//                    $this->CalcStatisticBlock($value, $useraction);
//                }
//            }
//        }
    }
    function CalcStatisticBlock($class_block, $useraction = null, $action_code=null, $id=null){//Розрахунок блоку статистики
        $fields = ['_month','_week','_6','_5','_4','_3','_2','_1','_0'];
        if(!empty($useraction)){
            $RequiredBlock = ['id','class_block','id_usr'];
            if(in_array($useraction->respon_alias, array_keys($this->ClassList[0])))
                $key = $this->ClassList[0][$useraction->respon_alias];
            elseif (in_array($useraction->respon_alias2, array_keys($this->ClassList[0])))
                $key = $this->ClassList[0][$useraction->respon_alias2];
            $sql = "select rowid from $this->statistictable where id = ";
            switch ($class_block){
                case 'regions':{
                    $sql.= empty($id)?'0':$id;
                }break;
                case 'userlist':{
                    $sql.=$useraction->id;
                }break;
                case 'subdivision':{
                    $sql.=$useraction->subdiv_id;
                }break;
                case 'company':{
                    $sql.='0';
                }break;
            }
            $sql.=" and action_code".(empty($action_code)?" is null":"='$action_code'")." and class_block ='$class_block' limit 1";
//            if($class_block == 'company')
//                die($sql);
            $res = $this->db->query($sql);
            if(!$res)
                dol_print_error($this->db);
            $sql = '';
            $add = !$res->num_rows;
            if($add){//Додавання нового запису
                $sql = "insert into $this->statistictable(" . implode(',', $RequiredBlock);
                foreach ($fields as $field){
                    $sql.=',total'.$field.',fact'.$field.',future'.$field;
                }
                $sql.=",`outstanding`, `action_code`";
                $sql.=')select ';
                switch ($class_block) {
                    case 'userlist':{
                        $sql.=$useraction->id;
                    }break;
                    case 'subdivision':{
                        $sql.=$useraction->subdiv_id;
                    }break;
                    default:{
                        $sql.='0';
                    }
                }

                $sql.=",'$class_block',0";


                foreach ($fields as $field){
                    $sql.=",sum(total".$field."),sum(fact$field),sum(future$field)";
                }
                $sql.=",sum(`outstanding`), ".(empty($action_code)?" null":"'$action_code'");
            }
            switch ($class_block) {
                case 'userlist': {
                    if($add) {
                        $sql.=" from $this->statistictable where 1 and id_usr = $useraction->id and class_block='$key'";
                    }else{
                        if(empty($action_code)) {
                            $sql = "update $this->statistictable";
                            $sql.=", (select 1";
                            foreach ($fields as $field) {
                                $sql .= ",sum(total" . $field . ") s_total" . $field . ",sum(fact$field) s_fact$field,sum(future$field) s_future$field";
                            }
                            $sql .= ", sum(`outstanding`) s_outstanding";
                            $sql.=" from $this->statistictable where 1 and id_usr = $useraction->id and class_block='$key') stat";
                            $sql.=" set ";
                            foreach ($fields as $key => $field) {
                                if ($key) $sql .= ',';
                                $sql .= "total$field=s_total$field, fact$field=s_fact$field, future$field=s_future$field";
                            }
                            $sql .= ",`outstanding`=`s_outstanding`";
                            $sql.=" where 1 and id = $useraction->id and class_block='$class_block' and action_code ".(empty($action_code)?' is null':"='$action_code'");
                        }else{
                            $sql = "";
                        }
                    }
                }break;
                case 'company':{
                    if($action_code != 'TOTAL') {
                        if ($add) {
                            $sql .= " from $this->statistictable where 1 and class_block='subdivision' and action_code" . (empty($action_code) ? " is null" : "='$action_code'");
                        } else {
                            $sql = "update $this->statistictable, (select 1";
                            foreach ($fields as $field) {
                                $sql .= ",sum(total" . $field . ") s_total" . $field . ",sum(fact$field) s_fact$field,sum(future$field) s_future$field";
                            }
                            $sql .= " from $this->statistictable where 1 and class_block='subdivision' and action_code " . (empty($action_code) ? ' is null' : "='$action_code'") . ") stat";
                            $sql .= " set ";
                            foreach ($fields as $key => $field) {
                                if ($key) $sql .= ',';
                                $sql .= "total$field=s_total$field, fact$field=s_fact$field, future$field=s_future$field";
                            }
                            $sql .= " where 1 and class_block='$class_block' and action_code " . (empty($action_code) ? ' is null' : "='$action_code'");
                        }
                    }
                    else{
                        if($add){
                            $sql .= " from $this->statistictable where 1 and class_block = 'company' and (action_code is null or action_code <> 'TOTAL')";
                        } else {
                            $sql = "update $this->statistictable, (select 1";
                            foreach ($fields as $field) {
                                $sql .= ",sum(total" . $field . ") s_total" . $field . ",sum(fact$field) s_fact$field,sum(future$field) s_future$field";
                            }
                            $sql .= " from $this->statistictable where 1 and class_block='company' and (action_code is null or action_code <> 'TOTAL')) stat";
                            $sql .= " set ";
                            foreach ($fields as $key => $field) {
                                if ($key) $sql .= ',';
                                $sql .= "total$field=s_total$field, fact$field=s_fact$field, future$field=s_future$field";
                            }
                            $sql .= " where 1 and class_block='$class_block' and action_code = 'TOTAL'";
                        }
                    }
//                    if($action_code == 'TOTAL' && !$add)
//                        die($sql);
                }break;
                case 'subdivision':{
                    $sql_tmp = "select rowid from llx_user where subdiv_id = $useraction->subdiv_id and active = 1";
                    $res = $this->db;
                    if(!$res)
                        dol_print_error($this->db);
                    $res = $this->db->query($sql_tmp);
                    $usersID = [];
                    while($obj = $this->db->fetch_object($res)){
                        $usersID[]=$obj->rowid;
                    }
                    if($add){
                        $sql.=" from $this->statistictable where 1 and id in (".implode(',',$usersID).") and class_block='userlist' and action_code".(empty($action_code)?" is null":"='$action_code'");
                    }else{
                        $sql = "update $this->statistictable, (select 1";
                        foreach ($fields as $field){
                            $sql.=",sum(total".$field.") s_total".$field.",sum(fact$field) s_fact$field,sum(future$field) s_future$field";
                        }
                        $sql.=" from $this->statistictable where 1 and id in (".implode(',',$usersID).") and class_block='userlist' and action_code ".(empty($action_code)?' is null':"='$action_code'").") stat";
                        $sql.=" set ";
                        foreach ($fields as $key=>$field){
                            if($key)$sql.=',';
                            $sql.="total$field=s_total$field, fact$field=s_fact$field, future$field=s_future$field";
                        }
                        $sql.=" where 1 and id = $useraction->subdiv_id and class_block='$class_block' and action_code ".(empty($action_code)?' is null':"='$action_code'");
                    }
                }break;
            }
            if(!empty($sql)) {
                $res = $this->db->query($sql);
                if (!$res) {
                    dol_print_error($this->db);
                }
            }

//            die('test');

        }
        foreach ($fields as $field) {
            $sql = "update $this->statistictable set per$field = round(100*fact$field/total$field) 
              where 1 ";
            switch ($class_block) {
                case 'userlist': {
                    $sql .= "and id = $useraction->id and class_block='$class_block' and action_code " . (empty($action_code) ? ' is null' : "='$action_code'") . " limit 1";
                }break;
                case 'subdivision':{
                    $sql.="and id = $useraction->subdiv_id and class_block='$class_block' and action_code ".(empty($action_code)?' is null':"='$action_code'")." limit 1";
                }break;
                case 'company':{
                    $sql .= "and class_block='$class_block' and action_code ".(empty($action_code)?' is null':"='$action_code'")." limit 1";
                }break;
            }
            $res = $this->db->query($sql);
//            if($class_block == 'userlist') {
//                echo $sql . ' '.$useraction->id.' $action_code='.$action_code.'</br>';
//            }
            if(!$res)
                dol_print_error($this->db);
        }
    }
    function SaveAction($action_id){//Внесення змін до існуючого звіту
        require_once  DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
        $action = new ActionComm($this->db);
        $action->fetch($action_id);
        require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
        $useraction = new User($this->db);
        $useraction->fetch(array_keys($action->userassigned)[count(array_keys($action->userassigned))-1]);


        $actiondate = new DateTime(date('d.m.Y', $action->datep));

        if (in_array($useraction->id, [63,69])  && date_diff($this->today, $actiondate)->days <= 31) {
            foreach ($this->ClassList as $key => $value) {
                if (is_array($value)) {
                    if(in_array($useraction->respon_alias, array_keys($value)))
                        $key = $value[$useraction->respon_alias];
                    elseif (in_array($useraction->respon_alias2, array_keys($value)))
                        $key = $value[$useraction->respon_alias2];
                    switch ($key) {
                        case 'regions': {
                            if (!empty($action->socid)) {
                                require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
                                $societe = new Societe($this->db);
                                $societe->fetch($action->socid);
                                $this->UserActions($societe->region_id, $key, $action, $useraction);
                            }
                        }break;
                    }
//                    $this->CalcStatisticBlock($key, $useraction);
                }else{
//                    echo $value.'</br>';
                    switch ($value){
                        case 'userlist':{
                            if(in_array($action->type_code, array('AC_CURRENT','AC_GLOBAL'))){
                                $this->UserActions($useraction->id, $value, $action, $useraction);
                            }else{
                                $this->CalcStatisticBlock($value, $useraction);
                            }
                        }break;
                        default:{//subdivision, company
                            $this->CalcStatisticBlock($value, $useraction, in_array($action->type_code, array('AC_CURRENT','AC_GLOBAL'))?$action->type_code:null);
                        }break;
                    }
                }
            }
            $this->CalcStatisticBlock('company', $useraction, 'TOTAL');
        }
    }
    function UserActions($id, $class_block, $action, $useraction){

        $actiondate = new DateTime(date('d.m.Y', $action->datep));
        $RequiredBlock = ['id','class_block','id_usr','action_code'];
        if(in_array($class_block, $this->ClassList[0]))
            $sql = "select rowid from $this->statistictable where id=".(empty($id)?0:$id)." and class_block = '$class_block' and id_usr=$useraction->id";
        else
            $sql = "select rowid from $this->statistictable where id=".(empty($id)?0:$id)." and class_block = '$class_block' and action_code='$action->type_code' and id_usr=$useraction->id";
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
        $date_diff = date_diff($this->today, $actiondate)->days;
        if(!$res->num_rows){
            $sql = "insert into $this->statistictable(".implode(',',$RequiredBlock);
            if($this->today>$actiondate || $this->today==$actiondate && in_array($action->percentage,$this->ExecutedPrecent)){
                $sql.=",total_month";
                if($date_diff<=7)
                    $sql.=",total_week";
                if($date_diff<7)
                    $sql.=",total_$date_diff";

                //Поля, якщо дія виконана
                if(in_array($action->percentage,$this->ExecutedPrecent)){
                    if($action->type_code != 'AC_TEL' || $this->CallStatus($action->id)) {
                        if($class_block == 'userlist' && $action->type_code == 'AC_CURRENT'){
                            $this->Count++;
                            echo $this->Count.' '.$action->id.'</br>';
                        }
                        $sql .= ",fact_month";
                        if ($date_diff <= 7)
                            $sql .= ",fact_week";
                        if ($date_diff < 7)
                            $sql .= ",fact_$date_diff";
                    }
                }else if($this->today>$actiondate){
                    $sql.=",outstanding,outstanginsID";
                }
                //Статистика
                $sql.=",per_month";
                if($date_diff<=7)
                    $sql.=",per_week";
                if($date_diff<7)
                    $sql.=",per_$date_diff";
                //Значення
                $sql.=")values(".(empty($id)?0:$id).",'$class_block',$useraction->id,'$action->type_code',1";
                if($date_diff<=7)
                    $sql.=",1";
                if($date_diff<7)
                    $sql.=",1";
                if(in_array($action->percentage,$this->ExecutedPrecent)){
                    if($action->type_code != 'AC_TEL' || $this->CallStatus($action->id)) {
                        $sql .= ",1";
                        if ($date_diff <= 7)
                            $sql .= ",1";
                        if ($date_diff < 7)
                            $sql .= ",1";
                    }
                    //Статистика
                    $sql.=",100";
                    if($date_diff<=7)
                        $sql.=",100";
                    if($date_diff<7)
                        $sql.=",100";
                }else if($this->today>$actiondate){
                    $sql.=",1,'$$action->id'";
                    //Статистика
                    $sql.=",0";
                    if($date_diff<=7)
                        $sql.=",0";
                    if($date_diff<7)
                        $sql.=",0";
                }
            }else{
                $sql.=",future_month";
                if($date_diff<=7)
                    $sql.=",future_week";
                if($date_diff<7)
                    $sql.=",future_$date_diff";
                //Значення
                $sql.=")values(".(empty($id)?0:$id).",'$class_block',$useraction->id,'$action->type_code',1";
                if($date_diff<=7)
                    $sql.=",1";
                if($date_diff<7)
                    $sql.=",1";
            }
            $sql.=")";
        }else{
//            if($action->percentage == 100 && $action->type_code == 'AC_TEL'){
//                var_dump($this->CallStatus($action->id));
//                die();
//            }
            if(!in_array($action->percentage,$this->ExecutedPrecent) && $this->today>$actiondate){
                $outstandingsID = $this->cutOutstandingID($id,$action->id,$action->type_code,$class_block,$useraction->id);
                $this->setOutstandingsID($id,$class_block,$action, $useraction->id, $outstandingsID);
            }
            $sql = "update $this->statistictable set";

            $sql.=" outstanding = ".$this->getOutstandingActionCount($id,$class_block,$action->type_code,$useraction->id);

            if($this->today>$actiondate){
                $sql.=",total_month=total_month+1";
                if($date_diff<=7)
                    $sql.=",total_week=case when total_week is null then 0 else total_week end +1";
                if($date_diff<7)
                    $sql.=",total_$date_diff=case when total_$date_diff is null then 0 else total_$date_diff end+1";
                if(in_array($action->percentage,$this->ExecutedPrecent)){
                    if($action->type_code != 'AC_TEL' || $this->CallStatus($action->id)) {
                        if($class_block == 'userlist' && $action->type_code == 'AC_CURRENT'){
                            $this->Count++;
                            echo $this->Count.' '.$action->id.'</br>';
                        }
                        $sql .= ",fact_month=case when fact_month is null then 0 else fact_month end +1";
                        if ($date_diff <= 7)
                            $sql .= ",fact_week=case when fact_week is null then 0 else fact_week end  +1";
                        if ($date_diff < 7)
                            $sql .= ",fact_$date_diff= case when fact_$date_diff is null then 0 else fact_$date_diff end+1";
                    }
                }
            }else{
                $sql.=",future_month=case when future_month is null then 0 else future_month end+1";
                if($date_diff<=7)
                    $sql.=",future_week=  case when future_week is null then 0 else future_week end+1";
                if($date_diff<7)
                    $sql.=",future_$date_diff= case when future_$date_diff is null then 0 else future_$date_diff end+1";
            }
            $sql.=" where id=".(empty($id)?0:$id)." and class_block='$class_block' and action_code='$action->type_code' and id_usr=$useraction->id";
        }
//        echo '<pre>';
//        var_dump($date_diff, $sql);
//        echo '</pre>';
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
        $this->setLastID($action->id);
        //Підрахунок статистики
        $this->CalcStatisticBlock($class_block,$useraction,$action->type_code,(empty($id)?0:$id));
    }
    function CallStatus($action_id){
//        $actions = '620982,626122,628906,629756,638071,640984,642726,643319,643582,643604,651107,651307,652410,653639,656338,657511,660977,664014,664276,664282,664410,664517,665224,666800,671025,673110,676615,693982,703180';
//        $actions = explode(',',$actions);
        $sql = "select rowid from `llx_societe_action` where `llx_societe_action`.`action_id` = $action_id and `llx_societe_action`.`callstatus` = 5 and active = 1 limit 1";
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
//        if(in_array($action_id, $actions)){
//            var_dump($res->num_rows>0);
//            die($sql);
//        }

        return $res->num_rows>0;
    }
    function cutOutstandingID($id,$action_id,$action_code,$class_block,$id_usr){
        $out = $this->getOutstandingsID($id,$class_block,$action_code,$id_usr);
        if(strpos($out, '$'.$action_id)>=0){
            $out=str_replace('$'.$action_id, '', $out);
        }
        return $out;
    }
    function getOutstandingsID($id,$class_block,$action_code,$id_usr){
        $sql = "select outstanginsID from $this->statistictable where class_block = '$class_block' and action_code = '$action_code' and id_usr = $id_usr and id = ".(empty($id)?0:$id);
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
        if(!$res->num_rows)
            return '';
        else {
            $obj = $this->db->fetch_object($res);
            return $obj->outstanginsID;
        }
    }
    function setOutstandingsID($id,$class_block,$action,$id_usr, $outstanginsID){
        $sql = "update $this->statistictable set outstanginsID = concat('$outstanginsID','$$action->id') 
          where id=".(empty($id)?0:$id)." and class_block='$class_block' and action_code = '$action->type_code' and id_usr=$id_usr";
//        echo $sql.'</br>';
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
        return 1;
    }
    function getOutstandingActionCount($id,$class_block,$action_code,$id_usr){
        $sql = "select outstanginsID from $this->statistictable where class_block = '$class_block' and action_code = '$action_code' and id_usr = $id_usr and id = ".(empty($id)?0:$id);
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
        if(!$res->num_rows)
            return 0;
        else {
            $obj = $this->db->fetch_object($res);
            return substr_count($obj->outstanginsID, '$');
        }
    }
    function getLastID(){
        $sql = "select id from $this->statistictable where class_block = 'last_action'";
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
        if($res->num_rows > 0) {
            $obj = $this->db->fech_object($res);
            return $obj->id;
        }else{
            $sql = "select max(id) id from llx_actioncomm where datep < adddate(date(now()), interval -1 month)";
            $res = $this->db->query($sql);
            if(!$res)
                dol_print_error($this->db);
            if($res->num_rows > 0) {
                $obj = $this->db->fetch_object($res);
                $this->setLastID($obj->id, true);
                return $obj->id;
            }else
                return 0;
        }
    }
    function setLastID($action_id, $additem = false){
        if($additem)
            $sql = "insert into $this->statistictable(class_block,id) values('last_action', $action_id)";
        else
            $sql = "update $this->statistictable set id = ".$action_id." where class_block = 'last_action'";
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
        return 1;
    }
    function ClearRaport(){
        $sql = "delete from $this->statistictable";
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
        else
            return 1;
    }

}