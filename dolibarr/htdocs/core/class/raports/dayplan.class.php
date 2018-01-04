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
    var $statistictable = 'llx_raport_dayplan';
    var $tmp_table = 'tmp_action';
    var $today;
    var $Count = 0;
    var $LastID = 0;
    var $bExit = false;
    var $ExecutedPrecent = [100,-100,99];
    var $ActionsCode = array('AC_GLOBAL','AC_CURRENT','AC_PROJECT','AC_EDUCATION','AC_INITIATIV','AC_RDV','AC_DEP');
    var $RenamedCode = array('AC_RDV'=>'AC_CURRENT','AC_DEP'=>'AC_CURRENT');
    var $fields = ['_month','_week','_6','_5','_4','_3','_2','_1','_0'];
    var $prefix_fields = ['per', 'fact', 'future'];
    var $emptyItem;//Пуста строка
    var $users = [];//для яких користувачів будуємо план дні. Якщо пустий масив - для всіх

    var $flag = false;
    function __construct($db)
    {
        $this->db=$db;
        $this->today = new DateTime(date('Y-m-d'));
        for ($i = 0; $i<count($this->prefix_fields); $i++) {
            $this->emptyItem .= '<td></td>';
        }
//        $sql = "select rowid from llx_user where 1 and active = 1";
//        $res = $this->db->query($sql);
//        while($obj = $this->db->fetch_object($res)){
//            $this->users[]=$obj->rowid;
//        }
    }
    function LoadDataAtStatTable($resTable, $id, $class_block=null){
        $blocks_id = [];

        while($obj = $this->db->fetch_object($resTable)){
            //Заношу ІД блоку
            $subdiv_id = !empty($obj->subdiv_id)?$obj->subdiv_id:'';
            $id_tmp = (!empty($obj->id)?($obj->id):$id);
            if(!in_array($id_tmp,$blocks_id)){
                $sql = "insert into $this->statistictable (`id`".(empty($class_block)?'':", `class_block`").") values ('$id_tmp'".(empty($class_block)?'':", '$class_block$subdiv_id'").")";
                $res = $this->db->query($sql);
                if(!$res)
                    dol_print_error($this->db);
                $blocks_id[]=$id_tmp;
            }
            $sql = "update $this->statistictable set ";
            if($obj->iDayIndex > 0){//Майбутні дії
                $sql.=" future_month = case when future_month is null then 0 else future_month end + ".$obj->iCount;
                if($obj->iDayIndex<=7) {
                    $sql .= ", future_week = case when future_week is null then 0 else future_week end + " . $obj->iCount;
                    if($obj->iDayIndex<7){
                        $sql.=", future_".$obj->iDayIndex."=case when future_$obj->iDayIndex is null then 0 else future_$obj->iDayIndex end +". $obj->iCount;
                    }
                }
            }elseif ($obj->iDayIndex == 0){//Сьогодні
                if(in_array($obj->percent, [100,-100])){
                    $sql.=" fact_".$obj->iDayIndex."=case when fact_$obj->iDayIndex is null then 0 else fact_$obj->iDayIndex end +". $obj->iCount;
                    $sql.=", fact_month = case when fact_month is null then 0 else fact_month end + ".$obj->iCount;
                    $sql .= ", fact_week = case when fact_week is null then 0 else fact_week end + " . $obj->iCount;
                }else{
                    $sql.=" future_".$obj->iDayIndex."=case when future_$obj->iDayIndex is null then 0 else future_$obj->iDayIndex end +". $obj->iCount;
                }
                $sql.=", total_".$obj->iDayIndex."=case when total_$obj->iDayIndex is null then 0 else total_$obj->iDayIndex end +". $obj->iCount;
            }else{//Минулі дії
                if(in_array($obj->percent, [100,-100])) {//Фіксація фактично виконаних завдань
                    $sql.=" fact_month = case when fact_month is null then 0 else fact_month end + ".$obj->iCount;
                    if($obj->iDayIndex>=-7) {
                        $sql .= ", fact_week = case when fact_week is null then 0 else fact_week end + " . $obj->iCount;
                        if($obj->iDayIndex>-7){
                            $sql.=", fact_".abs($obj->iDayIndex)."=case when fact_".abs($obj->iDayIndex)." is null then 0 else fact_".abs($obj->iDayIndex)." end +". $obj->iCount;
                        }
                    }
                    $sql.=", ";
                }else{//Фіксація прострочених завдань
                    $sql.=" outstanding = case when outstanding is null then 0 else outstanding end + ".$obj->iCount;
                        if(!empty($class_block) && $this->strpos($class_block, array('userlist', 'regions'))){
                            $sql .= ", rowlist = concat(case when rowlist is null then '' else concat(rowlist,',') end, '" . $obj->rowlist."')";
//                            if(substr_count($obj->rowlist, ',')>0)
//                                die($sql);
                        }
                    $sql.=", ";
                }
                $sql.=" total_month = case when total_month is null then 0 else total_month end +". $obj->iCount;
                if($obj->iDayIndex>=-7) {
                    $sql .= ", total_week = case when total_week is null then 0 else total_week end + " . $obj->iCount;
                    if($obj->iDayIndex>-7){
                        $sql.=", total_".abs($obj->iDayIndex)."=case when total_".abs($obj->iDayIndex)." is null then 0 else total_".abs($obj->iDayIndex)." end +". $obj->iCount;
                    }
                }
            }
            $sql.=" where id='$id_tmp' and class_block ".(is_null($class_block)?"is null":" = '$class_block$subdiv_id'");
//            echo $sql.'</br>';
            $up_res = $this->db->query($sql);
            if(!$up_res)
                dol_print_error($this->db);
        }        
    }
    function strpos($haystack, $needle){
        foreach ($needle as $value){
            if(strpos($haystack, $value))
                return true;
        }
        return false;
    }
    function RefreshRaport(){//Перебудова всього звіту
        set_time_limit(0);
        $this->ClearRaport();
//        //Всього по компанії
        $sql = "select iDayIndex, sum(iCount) iCount, percent from $this->tmp_table group by iDayIndex, case when percent in (100, -100) then 100 else percent end";
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
        $this->LoadDataAtStatTable($res, 'AllTask');
        //Всього по компаніі по підрозділах
        $sql = "select llx_user.subdiv_id id, $this->tmp_table.iDayIndex, $this->tmp_table.percent, sum(iCount)iCount from tmp_action
            inner join llx_user on llx_user.rowid = $this->tmp_table.id_usr
            group by llx_user.subdiv_id, $this->tmp_table.iDayIndex, case when $this->tmp_table.percent in (100, -100) then 100 else $this->tmp_table.percent end";
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
        $this->LoadDataAtStatTable($res, 'AllTask', 'AllTask impare subdivision');
        //Всього по компанії глобальних
        $sql = "select iDayIndex, sum(iCount) iCount, percent from $this->tmp_table where code = 'AC_GLOBAL' group by iDayIndex, case when percent in (100, -100) then 100 else percent end";
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
        $this->LoadDataAtStatTable($res, 'AC_GLOBAL');
        //Всього по підрозілам по глобальних
        $sql = "select llx_user.subdiv_id id, $this->tmp_table.iDayIndex, $this->tmp_table.percent, sum(iCount)iCount from tmp_action
            inner join llx_user on llx_user.rowid = $this->tmp_table.id_usr
            where code = 'AC_GLOBAL'
            group by llx_user.subdiv_id, $this->tmp_table.iDayIndex, case when $this->tmp_table.percent in (100, -100) then 100 else $this->tmp_table.percent end";
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
        $this->LoadDataAtStatTable($res, 'AC_GLOBAL', 'AC_GLOBAL impare subdivision');
        //Всього по користувачам по глобальних в розрізі користувачів
        $sql = "select llx_user.rowid id, subdiv_id, $this->tmp_table.iDayIndex, $this->tmp_table.percent, sum(iCount)iCount, GROUP_CONCAT(rowlist SEPARATOR ',')rowlist  from tmp_action
            inner join llx_user on llx_user.rowid = $this->tmp_table.id_usr
            where code = 'AC_GLOBAL'
            group by llx_user.rowid, $this->tmp_table.iDayIndex, case when $this->tmp_table.percent in (100, -100) then 100 else $this->tmp_table.percent end";
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
        $this->LoadDataAtStatTable($res, 'AC_GLOBAL', 'AC_GLOBAL userlist AC_GLOBAL_');
        //Всього по компанії поточних
        $sql = "select iDayIndex, sum(iCount) iCount, percent from $this->tmp_table where code = 'AC_CURRENT' group by iDayIndex, case when percent in (100, -100) then 100 else percent end";
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
        $this->LoadDataAtStatTable($res, 'AC_CURRENT');
        //Всього по компаніі по поточних в розрізі підрозділів
        $sql = "select llx_user.subdiv_id id, $this->tmp_table.iDayIndex, $this->tmp_table.percent, sum(iCount)iCount from tmp_action
            inner join llx_user on llx_user.rowid = $this->tmp_table.id_usr
            where code = 'AC_CURRENT'
            group by llx_user.subdiv_id, $this->tmp_table.iDayIndex, case when $this->tmp_table.percent in (100, -100) then 100 else $this->tmp_table.percent end";
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
        $this->LoadDataAtStatTable($res, 'AC_CURRENT', 'AC_CURRENT impare subdivision');
        //Всього по компаніі по поточних в розрізі користувачів
        $sql = "select llx_user.rowid id, subdiv_id, $this->tmp_table.iDayIndex, $this->tmp_table.percent, sum(iCount)iCount, GROUP_CONCAT(rowlist SEPARATOR ',')rowlist from tmp_action
            inner join llx_user on llx_user.rowid = $this->tmp_table.id_usr
            where code = 'AC_CURRENT'
            group by llx_user.rowid, $this->tmp_table.iDayIndex, case when $this->tmp_table.percent in (100, -100) then 100 else $this->tmp_table.percent end";
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
        $this->LoadDataAtStatTable($res, 'AC_CURRENT', 'AC_CURRENT userlist AC_CURRENT_');

       // Всього по компанії по напрямках
        $sql = "select iDayIndex, sum(iCount) iCount, percent from $this->tmp_table where code in ('AC_RDV','AC_DEP','AC_TEL') group by iDayIndex, case when percent in (100, -100) then 100 else percent end";
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
        $this->LoadDataAtStatTable($res, 'AC_CUST');

        //Всього по напрямках в розрізі підрозділів
        $sql = "select llx_user.subdiv_id id, $this->tmp_table.iDayIndex, $this->tmp_table.percent, sum(iCount)iCount from tmp_action
            inner join llx_user on llx_user.rowid = $this->tmp_table.id_usr
            where code in ('AC_RDV','AC_DEP','AC_TEL')
            group by llx_user.subdiv_id, $this->tmp_table.iDayIndex, case when $this->tmp_table.percent in (100, -100) then 100 else $this->tmp_table.percent end";
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
        $this->LoadDataAtStatTable($res, 'AC_CUST', 'AC_CUST impare subdivision');

        //Всього по напрямках в розрізі користувачів
        $sql = "select llx_user.rowid id, subdiv_id, $this->tmp_table.iDayIndex, $this->tmp_table.percent, sum(iCount)iCount, GROUP_CONCAT(rowlist SEPARATOR ',')rowlist from tmp_action
            inner join llx_user on llx_user.rowid = $this->tmp_table.id_usr
            where code in ('AC_RDV','AC_DEP','AC_TEL')
            group by llx_user.rowid, $this->tmp_table.iDayIndex, case when $this->tmp_table.percent in (100, -100) then 100 else $this->tmp_table.percent end";

        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
        $this->LoadDataAtStatTable($res, 'AC_CUST', 'AC_CUST userlist subdiv_');

        //Всього по напрямках в розрізі районів
        $sql = "select case when regions.rowid is null then 0 else regions.rowid end id, llx_user.rowid subdiv_id, $this->tmp_table.iDayIndex, $this->tmp_table.percent, sum(iCount)iCount, GROUP_CONCAT(rowlist SEPARATOR ',')rowlist from tmp_action
            inner join llx_user on llx_user.rowid = $this->tmp_table.id_usr
            inner join llx_societe on llx_societe.rowid = $this->tmp_table.fk_soc
            left join regions on regions.rowid = llx_societe.region_id
            where code in ('AC_RDV','AC_DEP','AC_TEL')
            group by llx_user.rowid, regions.rowid, $this->tmp_table.iDayIndex, case when $this->tmp_table.percent in (100, -100) then 100 else $this->tmp_table.percent end";

        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
        $this->LoadDataAtStatTable($res, 'region', 'region user_');

        //Підрахунок відсотків
        foreach ($this->fields as $field){
            $sql = "update llx_raport_dayplan set `per$field` = round(100*`fact$field`/`total$field`)";
//            die($sql);
            $res = $this->db->query($sql);
            if(!$res)
                dol_print_error($this->db);
        }
        die('Всього по напрямках');
//        foreach ($responsibility as $key=>$user_respons){
//            $action_index = 0;
//            echo $key.'</br>';
//            echo '<pre>';
//            for($action_index; $action_index<=4; $action_index++){
//                $sql = "select datep, iExec, iCount from tmp_group_statistic where fk_user_action = ".$key." and code='".$this->ActionsCode[$action_index]."'";
//                $action_res = $this->db->query($sql);
//                if(!$action_res)
//                    dol_print_error($this->db);
//                $sql = 'select rowid from '.$this->statistictable.' where id_usr = '.$key.' and action_code = "'.$this->ActionsCode[$action_index].'"';
//                $res = $this->db->query($sql);
//                if(!$res->num_rows){
//                    $sql = "insert into ".$this->statistictable.'(action_code, id_usr)values("'.$this->ActionsCode[$action_index].'", '.$key.')';
//                    $this->db->query($sql);
//                }
//                while($obj = $this->db->fetch_object($action_res)){
//                    $datep = new DateTime($obj->datep);
//                    $sql = "update $this->statistictable set id_usr = ".$key;
//                    if($datep<$this->today){
//                        $sql.=", total_month = case when total_month is null then 0 else total_month end +".$obj->iCount;
//                        $sql.=", fact_month = case when fact_month is null then 0 else fact_month end +".$obj->iExec;
//                        $sql.=", per_month = ".round($obj->iExec*100/$obj->iCount);
//
//                        if($this->today->diff($datep)->d<=7){
//                            $sql.=", total_week = case when total_week is null then 0 else total_week end +".$obj->iCount;
//                            $sql.=", fact_week = case when fact_week is null then 0 else fact_week end +".$obj->iExec;
//                            $sql.=", per_week = ".round($obj->iExec*100/$obj->iCount);
//                        }
//                        if($obj->iExec != $obj->iCount){//Якщо кількість запланованих завдань не відповідає виконаним
//                            $sql_tmp = "select id from tmp_statistic where id_usr = ".$key." and datep = '".$datep->format("Y-m-d")."' and code = '".$this->ActionsCode[$action_index]."' and iExec = 0";
//                            $res_tmp = $this->db->query($sql_tmp);
//                            if(!$res_tmp)
//                                dol_print_error($this->db);
//                            $outstandings_id = [];
//                            while($obj_tmp = $this->db->fetch_object($res_tmp)){
//                                $outstandings_id[]=$obj_tmp->id;
//                            }
//                            if(count($outstandings_id)){
//                                $sql.=", outstanginsID=concat(case when outstanginsID is null then '' else concat(outstanginsID, ',') end, '".implode(",",$outstandings_id)."')";
//                                $sql.=", outstanding = case when outstanding is null then 0 else outstanding end+".($obj->iCount-$obj->iExec);
//                            }
//                        }
//                    }elseif($datep>$this->today){
//                        $sql.=", future_month = case when future_month is null then 0 else future_month end +".$obj->iCount;
//                        if($this->today->diff($datep)->d<=7) {
//                            $sql.=", future_week = case when future_week is null then 0 else future_week end +".$obj->iCount;
//
//                        }
//                    }
//                    if($this->today->diff($datep)->d < 7){
//                        if($datep<$this->today || !$this->today->diff($datep)->d){
//                            $sql.=", total_".$this->today->diff($datep)->d." = case when total_".$this->today->diff($datep)->d." is null then 0 else total_".$this->today->diff($datep)->d." end +".$obj->iCount;
//                            $sql.=", fact_".$this->today->diff($datep)->d." = case when fact_".$this->today->diff($datep)->d." is null then 0 else fact_".$this->today->diff($datep)->d." end +".$obj->iExec;
//                            $sql.=", per_".$this->today->diff($datep)->d." = ".round($obj->iExec*100/$obj->iCount);
//                            if(!$this->today->diff($datep)->d){
//                                $sql.=", future_".$this->today->diff($datep)->d." = case when future_".$this->today->diff($datep)->d." is null then 0 else future_".$this->today->diff($datep)->d." end +".($obj->iCount-$obj->iExec);
//                            }
//                        }elseif ($this->today->diff($datep)->d > 0){
//                            $sql.=", future_".$this->today->diff($datep)->d." = case when future_".$this->today->diff($datep)->d." is null then 0 else future_".$this->today->diff($datep)->d." end +".$obj->iCount;
//                        }
//                    }
//                    $sql.=" where id_usr = $key and action_code = '".$this->ActionsCode[$action_index]."'";
//                    $this->db->query($sql);
//                    echo $sql.'</br>';
//                }
//
//                echo $this->ActionsCode[$action_index].'</br>';
//            }
//            //Завантажую дії за напрямками
//
//            echo '</pre>';
//
//            foreach ($user_respons as $respon) {
//                switch ($respon){
//                    case 'sale':{//Продажі
//
//                    }break;
//                    case 'dir_depatment':{//Директор деп.
//
//                    }break;
//                    case 'service':{//Сервіс
//
//                    }break;
//                    case 'purchase':{//Постачання
//
//                    }break;
//                    case 'counter':{//Бухгалтерія
//
//                    }break;
//                    case 'marketing':{//Маркетинг
//
//                    }break;
//                    case 'jurist':{//Юрист
//
//                    }break;
//                    case 'paperwork':{//Діловодство
//
//                    }break;
//                    case 'cadry':{//Відділ кадрів
//
//                    }break;
//                    case 'corp_manager':{//Корп.управління
//
//                    }break;
//                    case 'gen_dir':{//Ген.директор
//
//                    }break;
//                    case 'wholesale_purchase':{//Оптові закупівлі
//
//                    }break;
//                }
//            }
//
//        }

die(1);

        $sql = "select fk_user_action id_usr from tmp_group_statistic group by fk_user_action";
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
        while($obj = $this->db->fetch_object($res)){
            $this->SaveAction($obj->id_usr);
        }
        $this->CreateHTMLItem();//побудова html відображення статистики
        $this->setLastID();
    }
    function UpdateRaport(){//Оновлення звіту
        set_time_limit(0);
        $sql = "select id from $this->statistictable where class_block = 'last_action'";
        $res = $this->db->query($sql);
        if($res->num_rows == 0)
            $lastID = 0;
        else {
            $obj = $this->db->fetch_object($res);
            $lastID = $obj->id;
        }
        $sql = "select date(datep) datep, code, case when llx_actioncomm_resources.fk_element is null then fk_user_action else llx_actioncomm_resources.fk_element end fk_user_action, 
            sum(case when `llx_actioncomm`.`percent` = 100 then 1 else 0 end) iExec, count(*) iCount from llx_actioncomm 
            left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = `llx_actioncomm`.`id`
            where 1 and `llx_actioncomm`.`id`> $lastID and datep between adddate(date(now()), interval -1 month) and adddate(date(now()), interval 1 month) and active = 1 and code <> 'AC_OTH_AUTO'
            group by date(datep), code, case when llx_actioncomm_resources.fk_element is null then fk_user_action else llx_actioncomm_resources.fk_element end";
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
        while($obj = $this->db->fetch_object($res)){
            $this->SaveAction($obj);
        }
        $this->CreateHTMLItem();//побудова html відображення статистики
        $this->setLastID();
    }
    function CreateHTMLItem(){
        //Перебудова html звіту на всіх рівнях
        $sql = "select rowid from $this->statistictable";
        $res = $this->db->query($sql);
        while($obj = $this->db->fetch_object($res)){
            $sql = "update $this->statistictable set html='".$this->CreateHTML($obj->rowid)."' where rowid = $obj->rowid";


            $res_update = $this->db->query($sql);
            if(!$res_update) {
                dol_print_error($this->db);
            }
        }
    }
    function BuildRaport($responsibility, $id_usr){
        require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
        $useraction = new User($this->db);
        $useraction->fetch($id_usr);
        $out='<tbody id="reference_body">';
        switch ($responsibility){
            case 'dir_depatment':{
                //Найкращий співробітник системи
                $sql = "select  statistic_action1.id from $this->statistictable where class_block = 'userlist' and action_code = 'TOTAL' order by fact_week desc limit 1";
                $res = $this->db->query($sql);
                $obj = $this->db->fetch_object($res);
                $tmp_user = new User($this->db);
                $tmp_user->fetch($obj->id);
                $sql = "select name from `subdivision` where rowid = ".$tmp_user->subdiv_id;
                $res = $this->db->query($sql);
                $obj = $this->db->fetch_object($res);
                $out.=$this->GetItem('userlist','TOTAL', $tmp_user,'Найкр.співр.сист. '.$tmp_user->lastname.' ('.$obj->name.')', true)[0]['html'];
                //Найкращий директор департамента
                $sql = "select  statistic_action1.id from $this->statistictable
                inner join llx_user on llx_user.rowid = statistic_action1.id
                inner join `responsibility` on `responsibility`.`rowid` = `llx_user`.`respon_id`
                left join `responsibility` resp on `resp`.`rowid` = `llx_user`.`respon_id2`                
                where 1
                and 'dir_depatment' in (`responsibility`.`alias`, `resp`.`alias`)
                and class_block = 'userlist' and action_code = 'TOTAL' order by fact_week desc limit 1";
                $res = $this->db->query($sql);
                $obj = $this->db->fetch_object($res);
                $tmp_user = new User($this->db);
                $tmp_user->fetch($obj->id);
                $sql = "select name from `subdivision` where rowid = ".$tmp_user->subdiv_id;
                $res = $this->db->query($sql);
                $obj = $this->db->fetch_object($res);
                $out.=$this->GetItem('userlist','TOTAL', $tmp_user,'Найкр.ДД.сист. '.$tmp_user->lastname.' ('.$obj->name.')', true)[0]['html'];
                //Найкращий департамент
                $sql = "select  statistic_action1.id, `subdivision`.`name` from `statistic_action1`
                    inner join `subdivision` on `subdivision`.`rowid` = statistic_action1.id
                    where class_block = 'subdivision' and action_code = 'TOTAL'
                    order by fact_week desc
                    limit 1";
                $res = $this->db->query($sql);
                $obj = $this->db->fetch_object($res);
                $out.=$this->GetItem('subdivision','TOTAL', $obj,'Найкр.деп.сист. '.$obj->name, true)[0]['html'];
            }break;
            case 'sale':{
                $out.=$this->GetItem('userlist','TOTAL',$useraction,'Всього задач')[0]['html'];
//                die('Сторінка на ремонті');
                $out.=$this->GetItem('userlist','AC_GLOBAL',$useraction,'Глобальні задачі(ТОПЗ)')[0]['html'];
                $out.=$this->GetItem('userlist','AC_CURRENT', $useraction,'Поточні задачі')[0]['html'];
                $out.=$this->GetItem('regions','TOTAL', $useraction,'Всього по напрямках')[0]['html'];
                $sql = "select id from $this->statistictable where class_block = 'regions' and action_code = 'TOTAL' and id in ";
                $sql.=" (select rowid from llx_user where subdiv_id = $useraction->subdiv_id and active = 1) order by total_week desc limit 1";
                $res = $this->db->query($sql);
                $obj = $this->db->fetch_object($res);
                if($id_usr == $obj->id)
                    $out.=$this->GetItem('regions','TOTAL', $useraction,'Найкращі показники по підрозділу ', true)[0]['html'];
                else{
                    $tmp_user = new User($this->db);
                    $tmp_user->fetch($obj->id);
                    $out.=$this->GetItem('regions','TOTAL', $tmp_user,'Найкращі показники по підрозділу '.$tmp_user->lastname, true)[0]['html'];
                }
                $regions = $this->GetItem('regions','ALL',$useraction, '', false, false);
                $sql = "select `regions`.rowid, `regions`.`name`, states.`name` states_name from `regions`
                    inner join states on states.rowid = `regions`.`state_id`
                    where `regions`.`active` = 1
                    and states.active = 1
                    order by `regions`.`name`, states_name";
                $res = $this->db->query($sql);
                $regionsList = [];
                while($obj = $this->db->fetch_object($res)){
                    $regionsList[$obj->rowid] = array('region'=>$obj->name, 'states_name'=>$obj->states_name);
                }

                foreach ($regions as $region){
                    if(!empty($regionsList[$region['id']])){
                        $region_name = '<td>'.$regionsList[$region['id']]['states_name'].'</td><td>'.$regionsList[$region['id']]['region'].'</td>';
                    }else{
                        $region_name = '<td>Район</td><td>не вказано</td>';
                    }
                    $out.= '<tr>'.$region_name.'<td></td>'.$region['html'].'</tr>';

                }
                $out.=$this->GetItem('userlist','AC_PROJECT',$useraction,'Проекти',true)[0]['html'];
                $out.=$this->GetItem('userlist','AC_EDUCATION',$useraction,'Навчання',true)[0]['html'];
                $out.=$this->GetItem('userlist','AC_INITIATIV',$useraction,'Ініціативи',true)[0]['html'];
            }break;
        }
        $out.='</tbody>';
        return $out;
    }
    function GetItem($class_block, $action_code, $object, $title='title', $bestvalue = false, $addTR = true){
        switch ($class_block) {
            default: {
                $sql = "select id,html from $this->statistictable where id_usr = $object->id and class_block = '$class_block'";
                if ($action_code == 'ALL')
                    $sql .= " and action_code <> 'TOTAL'";
                else
                    $sql .= " and action_code='$action_code'";
            }break;
            case 'subdivision':{
                if ($action_code != 'ALL'){
                    $sql = "select id,html from $this->statistictable where id = $object->id and class_block = '$class_block' and action_code = '$action_code'";
                }
            }break;
        }
//        global $user;
//        if($user->id == 125)
//            die($sql);
        $res = $this->db->query($sql);
        $out = [];

        if($this->db->num_rows($res) == 0){
            if ($addTR)
                $html = '<tr ' . ($bestvalue ? 'class ="bestvalue"' : '') . '>';
            for ($i = 0; $i < 29; $i++) {
                $html .= '<td '.($i==0?'colspan=3':'').'>'.($i==0?'<b>' . $title . '</b>':'&nbsp;').'</td>';
            }
            if ($addTR)
                $html .= '</tr>';
            $out[] = array('id' => 0, 'html' => $html);
        }else {
            while ($item = $this->db->fetch_object($res)) {
                $html = '';
                if ($addTR)
                    $html = '<tr ' . ($bestvalue ? 'class ="bestvalue"' : '') . '>';
                if ($action_code != 'ALL')
                    $html .= '<td class="middle_size" colspan="3"><b>' . $title . '</b></td>' . $item->html;
                else
                    $html .= $item->html;
                if ($addTR)
                    $html .= '</tr>';

                $out[] = array('id' => $item->id, 'html' => $html);
            }
            if ($res->num_rows == 0) {//Якщо запит не вернув результат
                $html = '';
                if ($addTR)
                    $html = '<tr ' . ($bestvalue ? 'class ="bestvalue"' : '') . '>';
                $html .= $this->emptyItem;
                if ($addTR)
                    $html .= '</tr>';
                $out[] = array('id' => $item->id, 'html' => $html);
            }
//        if($action_code == 'AC_EDUCATION'){
//            echo '<pre>';
//            var_dump($out);
//            echo '</pre>';
//            die();
//        }
        }
        return $out;
    }
    function CalcStatisticBlock($class_block, $useraction = null, $action_code=null, $id=null){//Розрахунок блоку статистики
        if(is_array($class_block))
            return 1;
        //якщо дія відноситься до глобальних чи поточних дій - перепризначаю блок на userlist
        if(in_array($action_code, $this->ActionsCode)&&!in_array($class_block, array('userlist', 'subdivision','company')))
            $class_block = 'userlist';
        
        if(!empty($useraction)){
            $RequiredBlock = ['id','class_block','id_usr'];
            if(in_array($useraction->respon_alias, array_keys($this->ClassList[0])))
                $key = $this->ClassList[0][$useraction->respon_alias];
            elseif (in_array($useraction->respon_alias2, array_keys($this->ClassList[0])))
                $key = $this->ClassList[0][$useraction->respon_alias2];
            $sql = "select rowid from $this->statistictable where id = ";
            switch ($class_block){
                default:{
                    if($action_code != 'TOTAL')//Якщо визначається наявність підсумкової строки - прописую ід користувача, в іншому випадку - прописую id напрямку
                        $sql.= empty($id)?'0':$id;
                    else
                        $sql.= empty($id)?$useraction->id:$id;
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

            $res = $this->db->query($sql);
            if(!$res) {
                dol_print_error($this->db);

            }
            $sql = '';
            $add = !$res->num_rows;
            if($add){//Додавання нового запису
                $sql = "insert into $this->statistictable(" . implode(',', $RequiredBlock);
                foreach ($this->fields as $field){
                    $sql.=',total'.$field.',fact'.$field.',future'.$field;
                }

                $sql.=",`outstanding`, `action_code`";
                $sql.=')select ';
                if($action_code != 'TOTAL') {
                    switch ($class_block) {
                        case 'userlist': {
                            $sql .= $useraction->id;
                        }
                            break;
                        case 'subdivision': {
                            $sql .= $useraction->subdiv_id;
                        }
                            break;
                        case 'company':{
                            $sql .= '0';
                        }break;
                        default: {
                            $sql .= empty($id)?$useraction->id:$id;
                        }
                    }
                }else{
                    switch ($class_block) {
                        default:{
                            $sql .= empty($id)?$useraction->id:$id;
                        }break;
                        case 'userlist': {
                            $sql .= $useraction->id;
                        }
                            break;
                        case 'subdivision': {
                            $sql .= $useraction->subdiv_id;
                        }
                            break;
                        case 'company': {
                            $sql .= '0';
                        }
                    }
                }

                $sql.=",'$class_block'";
                if( !in_array($action_code, array_merge(array('TOTAL'),$this->ActionsCode))|| in_array($class_block, array('subdivision', 'company'))){
                    $sql .= ',0';
                }else{//Якщо визначається рівень статистики окрім по компанії та підрозділу - вставляю ід користувача
                    $sql .= ','.$useraction->id;
                }


                foreach ($this->fields as $field){
                    $sql.=",sum(total".$field."),sum(fact$field),sum(future$field)";
                }
                $sql.=",sum(`outstanding`), ".(empty($action_code)?" null":"'$action_code'");
            }

            switch ($class_block) {
                default:{
                    if($action_code == 'TOTAL'){
                        if($add){
                            $sql .= " from $this->statistictable where 1 and class_block = '$class_block' and (action_code is null or action_code <> 'TOTAL')
                                and id_usr = $useraction->id";
                        } else {
                            $sql = "update $this->statistictable, (select 1";
                            foreach ($this->fields as $field) {
                                $sql .= ",sum(total" . $field . ") s_total" . $field . ",sum(fact$field) s_fact$field,sum(future$field) s_future$field";
                            }
                            $sql .= ", sum(`outstanding`) s_outstanding";
                            $sql .= " from $this->statistictable where 1 and class_block='$class_block' and (action_code is null or action_code <> 'TOTAL') and id_usr = $useraction->id) stat";
                            $sql .= " set ";
                            foreach ($this->fields as $key => $field) {
                                if ($key) $sql .= ',';
                                $sql .= "total$field=s_total$field, fact$field=s_fact$field, future$field=s_future$field";
                            }
                            $sql .=", outstanding = s_outstanding";
                            $sql .= " where 1 and class_block='$class_block' and action_code = 'TOTAL' and id_usr = $useraction->id";
                        }
                    }else{
//                        if($add){
//                            $sql .= " from $this->statistictable where 1 and class_block = '$class_block' and (action_code is null or action_code <> 'TOTAL')
//                                and id_usr = $useraction->id";
//                        }
                    }
                }break;
                case 'userlist': {
                    if($action_code != 'TOTAL') {
                        if ($add) {
                            $sql .= " from $this->statistictable where 1 and id_usr = $useraction->id and class_block='$key'";
                        } else {
                            if (empty($action_code)) {
                                $sql = "update $this->statistictable";
                                $sql .= ", (select 1";
                                foreach ($this->fields as $field) {
                                    $sql .= ",sum(total" . $field . ") s_total" . $field . ",sum(fact$field) s_fact$field,sum(future$field) s_future$field";
                                }
                                $sql .= ", sum(`outstanding`) s_outstanding";
                                $sql .= " from $this->statistictable where 1 and id_usr = $useraction->id and class_block='$key') stat";
                                $sql .= " set ";
                                foreach ($this->fields as $key => $field) {
                                    if ($key) $sql .= ',';
                                    $sql .= "total$field=s_total$field, fact$field=s_fact$field, future$field=s_future$field";
                                }
                                $sql .= ",`outstanding`=`s_outstanding`";
                                $sql .= " where 1 and id = $useraction->id and class_block='$class_block' and action_code " . (empty($action_code) ? ' is null' : "='$action_code'");
                            } else {
                                $sql = "";
                            }
                        }
                    }else{
                        if($add){
                            $sql .= " from $this->statistictable where 1 and (class_block in('regions','userlist') and (action_code is null or action_code <> 'TOTAL') and id_usr = $useraction->id)";
                        } else {
                            $sql = "update $this->statistictable, (select 1";
                            foreach ($this->fields as $field) {
                                $sql .= ",sum(total" . $field . ") s_total" . $field . ",sum(fact$field) s_fact$field,sum(future$field) s_future$field";
                            }
                            $sql .= ", sum(`outstanding`) s_outstanding";
                            $sql .= " from $this->statistictable where 1 and class_block in('regions','userlist') and (action_code is null or action_code <> 'TOTAL') and id_usr = $useraction->id) stat";
                            $sql .= " set ";
                            foreach ($this->fields as $key => $field) {
                                if ($key) $sql .= ',';
                                $sql .= "total$field=s_total$field, fact$field=s_fact$field, future$field=s_future$field";
                            }
                            $sql .= ",`outstanding`=`s_outstanding`";
                            $sql .= " where 1 and class_block='$class_block' and action_code = 'TOTAL' and id = $useraction->id";
                        }
//                        if(!$add)
//                            die($sql);
                    }
                }break;
                case 'company':{
                    if($action_code != 'TOTAL') {
                        if ($add) {
                            $sql .= " from $this->statistictable where 1 and class_block='subdivision' and action_code" . (empty($action_code) ? " is null" : "='$action_code'");
                        } else {
                            $sql = "update $this->statistictable, (select 1";
                            foreach ($this->fields as $field) {
                                $sql .= ",sum(total" . $field . ") s_total" . $field . ",sum(fact$field) s_fact$field,sum(future$field) s_future$field";
                            }
                            $sql .= " from $this->statistictable where 1 and class_block='subdivision' and action_code " . (empty($action_code) ? ' is null' : "='$action_code'") . ") stat";
                            $sql .= " set ";
                            foreach ($this->fields as $key => $field) {
                                if ($key) $sql .= ',';
                                $sql .= "total$field=s_total$field, fact$field=s_fact$field, future$field=s_future$field";
                            }
                            $sql .= " where 1 and class_block='$class_block' and action_code " . (empty($action_code) ? ' is null' : "='$action_code'");
                        }
                    }
                    else{
                        if($add){
                            $sql .= " from $this->statistictable where 1 and class_block = 'company' and (action_code is null or action_code <> 'TOTAL')";
//                            die($sql);
                        } else {
                            $sql = "update $this->statistictable, (select 1";
                            foreach ($this->fields as $field) {
                                $sql .= ",sum(total" . $field . ") s_total" . $field . ",sum(fact$field) s_fact$field,sum(future$field) s_future$field";
                            }
                            $sql .= " from $this->statistictable where 1 and class_block='company' and (action_code is null or action_code <> 'TOTAL')) stat";
                            $sql .= " set ";
                            foreach ($this->fields as $key => $field) {
                                if ($key) $sql .= ',';
                                $sql .= "total$field=s_total$field, fact$field=s_fact$field, future$field=s_future$field";
                            }
                            $sql .= " where 1 and class_block='$class_block' and action_code = 'TOTAL'";
                        }
                    }
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
                        if(empty($action_code) || $action_code == 'TOTAL') {
                            $sql .= " from $this->statistictable where 1 and id in (" . implode(',', $usersID) . ") " .
                                    " and class_block not in ('userlist','subdivision','company') and action_code = 'TOTAL'";
                        }else{
                            $sql .= " from $this->statistictable where 1 and id in (" . implode(',', $usersID) . ") 
                                and class_block = 'userlist' and action_code = '$action_code'";
                        }
//                        else{
//                            $sql .= " from $this->statistictable where 1 and id = $useraction->subdiv_id
//                                and class_block = '$class_block' and (action_code <>'$action_code' or action_code is null)";
//                        }
                    }else{
                        $sql = "update $this->statistictable, (select 1";
                        foreach ($this->fields as $field){
                            $sql.=",sum(total".$field.") s_total".$field.",sum(fact$field) s_fact$field,sum(future$field) s_future$field";
                        }
                        if($action_code =='TOTAL') {
                            $sql .= " from $this->statistictable where 1 and id = $useraction->subdiv_id and " .
                                "class_block ='subdivision' and (action_code <> 'TOTAL' or action_code is null)";
                        }elseif (empty($action_code)){
                            $sql .= " from $this->statistictable where 1 and id in (" . implode(',', $usersID) . ") " .
                                " and class_block not in ('userlist','subdivision','company') and action_code = 'TOTAL'";
                        }elseif (!empty($action_code) && $action_code !='TOTAL'){
                            $sql .= " from $this->statistictable where 1 and id in (" . implode(',', $usersID) . ") and " .
                                "class_block = 'userlist' and action_code='$action_code'";
                        }else{
                            $sql .= " from $this->statistictable where 1 and id = $useraction->subdiv_id and class_block = '$class_block' and (action_code <>'$action_code' or action_code is null)";
                        }
                        $sql.=") stat set ";
                        foreach ($this->fields as $key=>$field){
                            if($key)$sql.=',';
                            $sql.="total$field=s_total$field, fact$field=s_fact$field, future$field=s_future$field";
                        }
                        $sql.=" where 1 and id = $useraction->subdiv_id and class_block='$class_block' and action_code ".(empty($action_code)?' is null':"='$action_code'");
                    }
                }break;
            }
//           if($useraction->id == 150 && $action_code == 'TOTAL'){
//                echo '<pre>';
//                var_dump('sql',$sql);
//                echo '</pre>';
//            }
            if(!empty($sql)) {
                $res = $this->db->query($sql);
                if (!$res) {
                    dol_print_error($this->db);
                    var_dump($add);
                    die($sql);

                }
            }

//            die('test');

        }
        foreach ($this->fields as $field) {
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
    function SaveAction($userID){//Внесення змін до існуючого звіту
//        require_once  DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
//        $action = new ActionComm($this->db);
//        $action->fetch($action_id);
//
        require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
        $useraction = new User($this->db);
        $useraction->fetch($userID);
        $sql = "select * from tmp_group_statistic where fk_user_action = ".$userID;
        $res = $this->db->query($sql);
        var_dump($res);
        $sql = "select * from tmp_statistic where fk_user_action = ".$userID;
        $res = $this->db->query($sql);
        var_dump($res);
        die();
        $this->Count++;
        echo $this->Count;
        $start = time();

        $actiondate = new DateTime(date('d.m.Y', $actions->datep));
//        echo '<pre>';
//        var_dump((count($this->users)==0 || in_array($useraction->id, $this->users)) && date_diff($this->today, $actiondate)->days <= 31 && $action->active);
//        echo '</pre>';
//        die();
        if ((count($this->users)==0 || in_array($userID, $this->users))) {
                echo ' '.$userID;

            foreach ($this->ClassList as $key => $value) {

                if (is_array($value) && !in_array($actions->code, $this->ActionsCode)) {//Якщо дія пов'язана з напрямками і не є глобальною чи поточною

                    if(in_array($useraction->respon_alias, array_keys($value)))
                        $key = $value[$useraction->respon_alias];
                    elseif (in_array($useraction->respon_alias2, array_keys($value)))
                        $key = $value[$useraction->respon_alias2];

                    if (!empty($action->socid)) {
                        $this->UserActions($societe->region_id, $key, $action, $useraction);
                    }else
                        $this->UserActions(0, $key, $action, $useraction);

                        $this->CalcStatisticBlock($key, $useraction,'TOTAL');

//                    $this->CalcStatisticBlock($key, $useraction);
                }else{
//                    echo $value.'</br>';
                    switch ($value){
                        case 'userlist':{
                            if(in_array($action->type_code, $this->ActionsCode)){
                                $this->UserActions($useraction->id, $value, $action, $useraction);
                            }

                        }break;
                        default:{//subdivision, company, по напрямкам
//                            if(733445 == $action->id) {
//                                echo '<pre>';
//                                var_dump($value);
//                                echo '</pre>';
//                                die();
//                            }
                            $this->CalcStatisticBlock($value, $useraction, in_array($action->type_code, $this->ActionsCode)?$action->type_code:'', $action_id);
                        }break;
                    }
                    $this->CalcStatisticBlock($value, $useraction,'TOTAL');
                }
            }
//            $this->CalcStatisticBlock('company', $useraction, 'TOTAL');
        }
        $this->LastID = $action_id;
        $long = time()-$start;
        echo ' '.$long.'</br>';
    }
    function UserActions($id, $class_block, $action, $useraction){
        if(!empty($this->RenamedCode[$action->type_code]))
            $action->type_code = $this->RenamedCode[$action->type_code];
        $actiondate = new DateTime(date('d.m.Y', $action->datep));
        $RequiredBlock = ['id','class_block','id_usr','action_code'];
        if(in_array($class_block, $this->ClassList[0])) {
            $sql = "select rowid from $this->statistictable where id=" . (empty($id) ? 0 : $id) . " and class_block = '$class_block' and id_usr=$useraction->id and action_code<>'TOTAL'";
        }else {
            $sql = "select rowid from $this->statistictable where id=" . (empty($id) ? 0 : $id) . " and class_block = '$class_block' and action_code='$action->type_code' and id_usr=$useraction->id";
        }
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
        $date_diff = date_diff($this->today, $actiondate)->days;

        $add = !$res->num_rows;
        $date_tmp = date('Y-m-d', $action->datep);
        if($add){
            $sql = "insert into $this->statistictable(".implode(',',$RequiredBlock);
            if($this->today>$actiondate || $this->today==$actiondate && in_array($action->percentage,$this->ExecutedPrecent)){
                $sql.=",total_month";
                if($date_diff<=7)
                    $sql.=",total_week";
                if($date_diff<7)
                    $sql.=",total_$date_diff";

                //Поля, якщо дія виконана
                if(in_array($action->percentage,$this->ExecutedPrecent)){
                        $sql .= ",fact_month";
                        if ($date_diff <= 7)
                            $sql .= ",fact_week";
                        if ($date_diff < 7)
                            $sql .= ",fact_$date_diff";
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
                    }elseif ($action->type_code == 'AC_TEL' && !$this->CallStatus($action->id)){
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
            $outstanding = false;
            if(!in_array($action->percentage,$this->ExecutedPrecent) && $this->today>$actiondate){
                $outstandingsID = $this->cutOutstandingID($id,$action->id,$action->type_code,$class_block,$useraction->id);
                $this->setOutstandingsID($id,$class_block,$action, $useraction->id, $outstandingsID);
                $outstanding = true;
            }
            $sql = "update $this->statistictable set";

            $sql.=" outstanding = ".$this->getOutstandingActionCount($id,$class_block,$action->type_code,$useraction->id);

            if($this->today>=$actiondate){
                $sql.=",total_month=case when total_month is null then 0 else total_month end +1";
                if($date_diff<=7)
                    $sql.=",total_week=case when total_week is null then 0 else total_week end +1";
                if($date_diff<7)
                    $sql.=",total_$date_diff=case when total_$date_diff is null then 0 else total_$date_diff end+1";
                if(in_array($action->percentage,$this->ExecutedPrecent)){
                    if($action->type_code != 'AC_TEL' || $this->CallStatus($action->id)) {

                        $sql .= ",fact_month=case when fact_month is null then 0 else fact_month end +1";
                        if ($date_diff <= 7)
                            $sql .= ",fact_week=case when fact_week is null then 0 else fact_week end  +1";
                        if ($date_diff < 7)
                            $sql .= ",fact_$date_diff= case when fact_$date_diff is null then 0 else fact_$date_diff end+1";
                    }
                }else{
                    $sql .= ",future_month=case when future_month is null then 0 else future_month end+1";
                    if ($date_diff <= 7)
                        $sql .= ",future_week= case when future_week is null then 0 else future_week end+1";
                    if ($date_diff < 7)
                        $sql .= ",future_$date_diff= case when future_$date_diff is null then 0 else future_$date_diff end+1";
                }
            }else if(!in_array($action->percentage,$this->ExecutedPrecent)){
                $sql .= ",future_month=case when future_month is null then 0 else future_month end+1";
                if ($date_diff <= 7)
                    $sql .= ",future_week= case when future_week is null then 0 else future_week end+1";
                if ($date_diff < 7)
                    $sql .= ",future_$date_diff= case when future_$date_diff is null then 0 else future_$date_diff end+1";

            }
            $sql.=" where id=".(empty($id)?0:$id)." and class_block='$class_block' and action_code='$action->type_code' and id_usr=$useraction->id";
        }

        $res = $this->db->query($sql);
        if(!$res) {
            dol_print_error($this->db);
//            echo '<pre>';
//            var_dump($id, $class_block, $useraction, $sql);
//            echo '</pre>';
//            die('test');
        }
//        if($useraction->id == 150){
//            var_dump('$date_tmp', $sql);
//        }
//        if($useraction->id == 150 && !in_array($action->percentage,$this->ExecutedPrecent)){
//            var_dump($action->percentage, $sql);
//        }
//        if(empty($id)?0:$id == 378 && $useraction->id == 150 && $this->today>=$actiondate){
//            echo '<pre>';
//            var_dump($sql);
//            echo '</pre>';
//            die();
//        }
        //Підрахунок статистики
        $this->CalcStatisticBlock($class_block,$useraction,$action->type_code,empty($id)?0:$id);
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
                $this->LastID = $obj->id;
                $this->setLastID();
                return $obj->id;
            }else
                return 0;
        }
    }
    function setLastID(){
        $sql = "select rowid from $this->statistictable where class_block = 'last_action'";
        $res = $this->db->query($sql);
        if($res->num_rows == 0)
            $sql = "insert into $this->statistictable(class_block,id) values('last_action', $this->LastID)";
        else
            $sql = "update $this->statistictable set id = ".$this->LastID." where class_block = 'last_action'";
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
    function CreateStaticPage(){
        $sql = "select llx_user.rowid, resp1.alias alias1, resp2.alias alias2 from llx_user
            left join `responsibility` resp1 on resp1.rowid = llx_user.respon_id
            left join `responsibility` resp2 on resp2.rowid = llx_user.respon_id2
            where llx_user.active = 1
            and llx_user.rowid>2
            and resp1.active = 1
            and resp2.active = 1";
        $res = $this->db->query($sql);
        while($obj = $this->db->fetch_object($res)){
            if($obj->rowid == 5){
                $alias = $this->GetPageProfile(array($obj->alias1, $obj->alias2));
                $html = $this->CreateHTML($obj->rowid, $alias);
            }
        }
    }
    function GetPageProfile($alias){
        if(in_array('gen_dir', $alias))
            return 'gen_dir';
        elseif (in_array('corp_manager', $alias))
            return 'gen_dir';
        elseif (in_array('dir_depatment', $alias))
            return 'dir_depatment';
        elseif (array_intersect(array('marketing','sale','jurist','service','purchase','logistika','cadry','counter','paperwork'), $alias))
            return 'sale';
    }
    function CreateHTML($rowid, $alias){
        $html = '<tbody id="reference_body">';
        return $html;
    }
}