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
    var $LastID = 0;
    var $bExit = false;
    var $ExecutedPrecent = [100,-100,99];
    var $ActionsCode = array('AC_GLOBAL','AC_CURRENT','AC_PROJECT','AC_EDUCATION','AC_INITIATIV','AC_RDV');
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
        $sql = "select rowid from llx_user where subdiv_id = 21 and active = 1";
        $res = $this->db->query($sql);
        while($obj = $this->db->fetch_object($res)){
            $this->users[]=$obj->rowid;
        }
    }

    function RefreshRaport(){//Перебудова всього звіту
        set_time_limit(0);
//        $this->ClearRaport();
//
//        $sql = "select id from llx_actioncomm where datep between adddate(date(now()), interval -1 month) and adddate(date(now()), interval 1 month) and active = 1 and code <> 'AC_OTH_AUTO'";
//        $res = $this->db->query($sql);
//        if(!$res)
//            dol_print_error($this->db);
//
//        while($obj = $this->db->fetch_object($res)){
//            $this->SaveAction($obj->id);
//        }
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
        $sql = "select id from llx_actioncomm where id > $lastID and datep between adddate(date(now()), interval -1 month) and adddate(date(now()), interval 1 month) and active = 1 and code <> 'AC_OTH_AUTO'";
        $res = $this->db->query($sql);
        if(!$res)
            dol_print_error($this->db);
        while($obj = $this->db->fetch_object($res)){
            $this->SaveAction($obj->id);
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
        $res = $this->db->query($sql);
        $out = [];
        while ($item = $this->db->fetch_object($res)) {
            $html='';
            if($addTR)
                $html='<tr ' . ($bestvalue ? 'class ="bestvalue"' : '') . '>';
            if($action_code != 'ALL')
                $html .= '<td class="middle_size" colspan="3"><b>' . $title . '</b></td>' . $item->html;
            else
                $html .=  $item->html;
            if($addTR)
                $html .= '</tr>';
            $out[] = array('id'=>$item->id, 'html'=>$html);
        }
        if($res->num_rows == 0){//Якщо запит не вернув результат
            $html='';
            if($addTR)
                $html='<tr ' . ($bestvalue ? 'class ="bestvalue"' : '') . '>';
            $html.=$this->emptyItem;
            if($addTR)
                $html .= '</tr>';
            $out[] = array('id'=>$item->id, 'html'=>$html);
        }
//        if($action_code == 'AC_EDUCATION'){
//            echo '<pre>';
//            var_dump($out);
//            echo '</pre>';
//            die();
//        }

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
    function SaveAction($action_id){//Внесення змін до існуючого звіту
        require_once  DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
        $action = new ActionComm($this->db);
        $action->fetch($action_id);
        require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
        $useraction = new User($this->db);
        $useraction->fetch(array_keys($action->userassigned)[count(array_keys($action->userassigned))-1]);

        $this->Count++;
        echo $this->Count.' '.$action->id;
        $start = time();

        $actiondate = new DateTime(date('d.m.Y', $action->datep));
//        if($action->id == 733445) {
//            echo '<pre>';
//            var_dump($this->today, $actiondate, date_diff($this->today, $actiondate));
//            echo '</pre>';
//            die();
//        }
        if ((empty($this->users) || in_array($useraction->id, $this->users)) && date_diff($this->today, $actiondate)->days <= 31 && $action->active) {
                echo ' '.$useraction->id;
            foreach ($this->ClassList as $key => $value) {

                if (is_array($value) && !in_array($action->type_code, $this->ActionsCode)) {//Якщо дія пов'язана з напрямками і не є глобальною чи поточною

                    if(in_array($useraction->respon_alias, array_keys($value)))
                        $key = $value[$useraction->respon_alias];
                    elseif (in_array($useraction->respon_alias2, array_keys($value)))
                        $key = $value[$useraction->respon_alias2];

                    if (!empty($action->socid)) {

                        require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
                        $societe = new Societe($this->db);
                        $societe->fetch($action->socid);
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
    function CreateHTML($rowid, $title = ''){
//        return '';


        $html = '';
        $sql = "select * from $this->statistictable where rowid = $rowid";
        $res = $this->db->query($sql);
        if(!$res) {
            dol_print_error($this->db);
        }
        $array_item = $this->db->fetch_array($res);

//        $class_block = $array_item['class_block'];
//
//
////        var_dump($class_block);
//        switch ($class_block){
//            case 'regions':{
//                if($array_item['action_code'] != 'TOTAL') {
//                    $html .= ' id="region' . $array_item['id'] . '" class="regions' . $array_item['id_usr'] . ' region subtype middle_size">';
//                    //регіон
//                    $sql = "select regions.rowid, regions.name, states.name state_name from regions
//                    inner join states on states.rowid = regions.state_id
//                    where regions.rowid = " . $array_item['id'];
//
//                    $reg_res = $this->db->query($sql);
//
//                    $reg_obj = $this->db->fetch_object($reg_res);
//                    $state_name = $reg_obj->state_name;
//                    $symbols = array('а', 'о', 'у', 'и', 'і', 'ї', 'є', 'е', 'ю', 'я');
//
//                    for ($i = 3; $i <= mb_strlen($reg_obj->state_name, 'UTF-8'); $i++) {
//                        if (in_array(mb_substr($reg_obj->state_name, $i, 1, 'UTF-8'), $symbols)) {
//                            $state_name = mb_substr($reg_obj->state_name, 0, $i, 'UTF-8') . '.';
//                            break;
//                        }
//                    }
//                    $html .= '<td colspan="2">'
//                    . empty((int)$array_item['id']) ? 'Район не вказано' : '<a href="/dolibarr/htdocs/responsibility/sale/area.php?idmenu=10425&amp;mainmenu=area&amp;leftmenu=&amp;id_usr=' . $array_item['id_usr'] . '&amp;state_filter=' . $array_item['id'] . '" target="_blank">
//                        ' . $reg_obj->name . (!empty($state_name) ? ' (' . $state_name . ')' : '') . (empty($reg_res->num_rows) ? '' : '</a>') . '</td>';
//                }else{
//                    $html .= ' id="AC_CUST' . $array_item['id'] . '" class="userlist AC_CUST_' . $users[$array_item['id_usr']]['subdiv_id'] . ' region subtype middle_size">';
//                    if(empty($title)) {
//                        //прізвище та ім'я
//                        $html .= '<td colspan="2"><a href="/dolibarr/htdocs/responsibility/sale/area.php?idmenu=10425&amp;mainmenu=area&amp;leftmenu=&amp;id_usr=' . $array_item['id_usr'] . '" target="_blank">' .
//                            trim($users[$array_item['id_usr']]['lastname']) . ' ' . substr($users[$array_item['id_usr']]['firstname'], 0, 1) . '.</a></td>';
//                        //кнопка відображення районів
//                        $html .= '<td><button id="btnUsr' . $array_item['id_usr'] . '" onclick="getRegionsList(' . $array_item['id_usr'] . ', $(this));"><img id="imgUsr63" src="http://uspex2015.com.ua/dolibarr/htdocs/theme/eldy/img/1uparrow.png"></button></td>';
//                    }else{
//                        $html .= "<td colspan='2'>$title</td>";
//                    }
//                }
//            }break;
//            case 'userlist':{
//                $html.='id="'.$array_item['action_code'].' '.$array_item['id_usr'].$users[$array_item['id_usr']]['subdiv_id'].'" class="'.$array_item['action_code'].' '.$users[$array_item['id_usr']]['subdiv_id'].' userlist '.$array_item['action_code'].'_'.$users[$array_item['id_usr']]['subdiv_id'].'">';
//                $html .= '<td colspan="2">';
//                switch (trim($array_item['action_code'])) {
//                    case 'AC_CURRENT': {
//                        $html.='<a href="/dolibarr/htdocs/current_plan.php?idmenu=10423&amp;mainmenu=current_task&amp;leftmenu=&amp;id_usr=8" target="_blank">';
//                    }break;
//                    case 'AC_GLOBAL':{
//                        $html.='<a href="/dolibarr/htdocs/global_plan.php?idmenu=10423&amp;mainmenu=global_task&amp;leftmenu=&amp;id_usr=8" target="_blank">';
//                    }break;
//                }
//                $html.= trim($users[$array_item['id_usr']]['lastname']).' '.substr($users[$array_item['id_usr']]['firstname'], 0,1).'.</a></td>';
//            }break;
//            case 'subdivision':{
//                $html.='tr id="'.$array_item['action_code'].$array_item['id'].'" class="'.$array_item['action_code'].' subdivision">';
//                $html.='<td colspan="2">'.$subdivisions[$array_item['id']].'</td>';
//                $html.='<button id="btnSub'.$array_item['action_code'].$array_item['id'].'" onclick="ShowActionsByUsers('.$array_item['id'].', \''.$array_item['action_code'].'\', \'\')"><img id="imgSub'.$array_item['action_code'].$array_item['id'].'" src="/dolibarr/htdocs/theme/eldy/img/1downarrow.png"></button>';
//            }break;
//            case 'company':{
//                $html.='>';
//            }break;
//        }
        foreach ($this->prefix_fields as $prefix) {
            if ($prefix == 'future') {

                    $html .= '<td id="outstanding' . $array_item['id'] . '" style="text-align: center; cursor: pointer;" onclick="ShowOutStandingRegion(' . $array_item['id'] . ', ' . $array_item['id_usr'] . ');">' . $array_item['outstanding'] . '</td>';

                for ($i = count($this->fields)-1; $i >= 0; $i--) {
                    $html .= '<td>' . $array_item[$prefix . $this->fields[$i]] . '</td>';
                }
            } else {
                foreach ($this->fields as $field) {

                    $html .= '<td>' . $array_item[$prefix . $field] . '</a></td>';
                }
            }
        }
//        $html.='</tr>';

//        $html = str_replace('"','&quot;', $html);
//        $html = str_replace("'",'&#039;', $html);
//        die(htmlspecialchars($html));
        return $html;
    }
}