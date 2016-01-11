<?php
require $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/societe/modules_societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/societecontact_class.php';

if(isset($_REQUEST['action'])||isset($_POST['action'])){
    if($_REQUEST['action'] == 'loadcontactlist'){
        echo loadcontactlist($_REQUEST['contactid']);
        exit();
    }elseif($_POST['action'] == 'save'){
        saveaction();
        exit();
    }
}

//echo '<pre>';
//var_dump($_SERVER);
//echo '</pre>';
//die();
$ActionArea = $langs->trans('ActionArea');
llxHeader("",$ActionArea,"");
if(isset($_REQUEST['action'])){
    if($_REQUEST['action'] == 'add'){
        print_fiche_titre($langs->trans('AddAction'));
        addaction();
        exit();
    }
}

print_fiche_titre($ActionArea);

$object = new Societe($db);
$socid = $_REQUEST['socid'];
$result=$object->fetch($socid);
$soc_contact = new societecontact();
$TableParam = array();
$ColParam['title']='';
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='100px';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$sql = 'select `llx_societe_contact`.rowid, subdivision,  concat(trim(nametown), " ", trim(regions.name), " р-н. ", trim(states.name), " обл.") as nametown,
`llx_post`.`postname`,`responsibility`.`name` as respon_name,lastname,firstname,work_phone,
call_work_phone,fax,call_fax,mobile_phone1,call_mobile_phone1,mobile_phone2,
call_mobile_phone2,email1,send_email1,email2,send_email2,skype,call_skype,
birthdaydate,send_birthdaydate
from `llx_societe_contact`
left join `llx_c_ziptown` on `llx_c_ziptown`.rowid = `llx_societe_contact`.`town_id`
        left join states on states.rowid = llx_c_ziptown.fk_state
        left join regions on regions.rowid =  llx_c_ziptown.`fk_region`
left join `llx_post` on `llx_post`.`rowid`= `llx_societe_contact`.`post_id`
left join `responsibility` on `responsibility`.`rowid` = `llx_societe_contact`.`respon_id`
where `llx_societe_contact`.`socid`='.$socid.'
and `llx_societe_contact`.`active` = 1';
//die($sql);
$contacttable = new societecontact();
//var_dump($_REQUEST['sortfield']);
if(!isset($_REQUEST['sortfield']))
    $contact = $contacttable->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme, null, null, $readonly = array(), false);
else
    $contact = $contacttable->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme, $_REQUEST['sortfield'], $_REQUEST['sortorder']);
unset($TableParam);

$datep = new DateTime();

$actiontabe = ShowActionTable();
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/responsibility/sale/action.html';
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
function selectcontact($socid, $contactid=0){
    global $db;
    $out='<select id="contact" class="combobox" size="1" name="contact" onchange="loadcontactlist();">'."\r\n";
    $out.='<option disabled="disabled" value="0">Виберіть контакт</option>';
//    $out.='<option value="0"></option>';
    $sql = 'select rowid, lastname from llx_societe_contact
      where socid='.$socid.' and active=1 order by lastname';
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
//    var_dump($db->num_rows($res));
//    die();
    while($obj=$db->fetch_object($res)){
        $selected = $contactid == $obj->rowid;
        $out.='<option '.($selected?'selected="selected"':'').' value="'.$obj->rowid.'">'.$obj->lastname.'</option>';
    }
    $out.='</select>';
    return $out;
}
function saveaction(){
    global $user, $db;
//    echo '<pre>';
//    var_dump($_REQUEST);
//    echo '</pre>';
    if(empty($_REQUEST['rowid'])){
        $sql='insert into llx_societe_action(`master_action`,`socid`,`contactid`,`kindaction`,`said`,`answer`,
          `argument`,`said_important`,`result_of_action`,`work_before_the_next_action`,`date_next_action`,`id_usr`) values(';
        if(empty($_REQUEST['master_action'])) $sql.='null,';
        else $sql.=$_REQUEST['master_action'].',';
        $sql.=$_REQUEST['socid'].',';
        $sql.=$_REQUEST['contact'].',';
        $sql.='"'.trim($_REQUEST['contactlist']).'",';
        if(empty($_REQUEST['said'])) $sql.='null,';
        else $sql.='"'.$_REQUEST['said'].'",';
        if(empty($_REQUEST['answer'])) $sql.='null,';
        else $sql.='"'.$_REQUEST['answer'].'",';
        if(empty($_REQUEST['argument'])) $sql.='null,';
        else $sql.='"'.$_REQUEST['argument'].'",';
        if(empty($_REQUEST['said_important'])) $sql.='null,';
        else $sql.='"'.$_REQUEST['said_important'].'",';
        if(empty($_REQUEST['result_of_action'])) $sql.='null,';
        else $sql.='"'.$_REQUEST['result_of_action'].'",';
        if(empty($_REQUEST['work_before_the_next_action'])) $sql.='null,';
        else $sql.='"'.$_REQUEST['work_before_the_next_action'].'",';
        if(empty($_REQUEST['date_next_action'])) $sql.='null,';
        else {
            $date = new DateTime($_REQUEST['date_next_action']);
            $value = $date->format('Y-m-d');
            $sql .= '"' .$value . '",';
        }
        $sql .= $user->id.")";
    }else {
        $sql = '';
    }
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
}
function loadcontactlist($contactid){
    global $db;
    $sql='select work_phone, fax, mobile_phone1, mobile_phone2, email1, email2, skype from llx_societe_contact
      where rowid='.$contactid;

    $out='';
    $out.='<option disabled="disabled" value="0">Виберіть вид контакту</option>';
    $out.='<option  value="meeting">Зустріч</option>';
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
    $obj=$db->fetch_object($res);
    if(!empty($obj->work_phone))
        $out.='<option  value="work_phone">Роб.тел. '.$obj->work_phone.'</option>';
    if(!empty($obj->fax))
        $out.='<option  value="work_phone">Факс '.$obj->fax.'</option>';
    if(!empty($obj->mobile_phone1))
        $out.='<option  value="mobile_phone1">моб.тел. '.$obj->mobile_phone1.'</option>';
    if(!empty($obj->mobile_phone2))
        $out.='<option  value="mobile_phone2">моб.тел. '.$obj->mobile_phone2.'</option>';
    if(!empty($obj->email1))
        $out.='<option  value="email1">e-mail '.$obj->email1.'</option>';
    if(!empty($obj->email2))
        $out.='<option  value="email2">e-mail '.$obj->email2.'</option>';
    if(!empty($obj->skype))
        $out.='<option  value="skype">skype '.$obj->skype.'</option>';
//    $out.='</select>';
    return $out;
}
function ShowActionTable(){
    global $db, $langs, $conf;
//    $sql = 'select `llx_societe_action`.`rowid`, `llx_societe_action`.`socid`, trim(`llx_societe_contact`.`lastname`) as contactname,`kindaction`,trim(`said`) said,
//    trim(`answer`) answer, trim(`argument`) argument, trim(`said_important`) said_important,
//    trim(`result_of_action`) result_of_action,trim(`work_before_the_next_action`) work_before_the_next_action, `date_next_action`,
//    `llx_societe_action`.`dtChange`, `llx_user`.lastname, trim(`work_before_the_next_action_mentor`) work_mentor, `date_next_action_mentor` date_mentor
//        from `llx_societe_action`
//        inner join `llx_user` on `llx_societe_action`.id_usr = `llx_user`.rowid
//        inner join `llx_societe_contact` on `llx_societe_action`.`contactid`
//        where `llx_societe_action`.`socid`='.$_REQUEST['socid'].' and `llx_societe_action`.active=1 order by dtChange desc';
    $sql='select `llx_actioncomm`.id as rowid, "" as `datec`, "" lastname, concat(case when `llx_societe_contact`.lastname is null then "" else `llx_societe_contact`.lastname end,
    case when `llx_societe_contact`.firstname is null then "" else `llx_societe_contact`.firstname end) as contactname, TypeCode.code kindaction from `llx_actioncomm`
    left join `llx_user` on `llx_actioncomm`.fk_user_author = `llx_user`.rowid
    inner join (select code, libelle label from `llx_c_actioncomm` where active = 1 and (type = "system" or  type = "user")) TypeCode on TypeCode.code = `llx_actioncomm`.code
    left join `llx_societe_contact` on `llx_societe_contact`.rowid=`llx_actioncomm`.fk_contact
    where fk_soc = '.$_REQUEST['socid'];
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
    $out = '<tbody>';
//    var_dump($sql);
//    die();
    if($db->num_rows($res)==0){
        $out .= '<tr class="impair">
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 100px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 50px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="widtd: 80px" class="middle_size">&nbsp;</td>
            <td style="width: 35px" class="middle_size">&nbsp;</td>
            </tr>';
    }
    $num=0;
    while($row = $db->fetch_object($res)){
        $dtChange = new DateTime($row->datec);
        $dtNextAction = new DateTime($row->date_next_action);
        $dtDateMentor = new DateTime($row->date_mentor);
        $iconitem='';
        $title='';
        switch($row->kindaction){
            case 'AC_GLOBAL':{
                $classitem = 'global_taskitem';
                $iconitem = 'object_global_task.png';
                $title=$langs->trans('ActionGlobalTask');
            }break;
            case 'AC_CURRENT':{
                $classitem = 'current_taskitem';
                $iconitem = 'object_current_task.png';
                $title=$langs->trans('ActionCurrentTask');
            }break;
            case 'AC_RDV':{
                $classitem = 'office_meetting_taskitem';
                $iconitem = 'object_office_meetting_task.png';
                $title=$langs->trans('ActionAC_RDV');
            }break;
            case 'AC_TEL':{
                $classitem = 'office_callphone_taskitem';
                $iconitem = 'object_call2.png';
                $title=$langs->trans('ActionAC_TEL');
            }break;
            case 'AC_DEP':{
                $classitem = 'departure_taskitem';
                $iconitem = 'object_departure_task.png';
                $title=$langs->trans('ActionDepartureMeeteng');
            }break;
        }
        $out .= '<tr class="'.(fmod($num++, 2)==0?'impair':'pair').'">
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'dtChange" style="widtd: 80px" class="middle_size">'.(empty($row->datec)?'':$dtChange->format('d.m.y H:i:s')).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'lastname" style="widtd: 100px" class="middle_size">'.$row->lastname.'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'contactname" style="widtd: 80px" class="middle_size">'.$row->contactname.'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'kindaction" style="widtd: 50px; text-align: center;" class="middle_size" ><img src="/dolibarr/htdocs/theme/'.$conf->theme.'/img/'.$iconitem.'" title="'.$title.'"></td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'said" style="widtd: 80px" class="middle_size">'.(strlen($row->said)>20?substr($row->said, 0, 20).'...':$row->said).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'answer" style="widtd: 80px" class="middle_size">'.(strlen($row->answer)>20?substr($row->answer, 0, 20).'...':$row->answer).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'argument" style="widtd: 80px" class="middle_size">'.(strlen($row->argument)>20?substr($row->argument, 0, 20).'...':$row->argument).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'said_important" style="widtd: 80px" class="middle_size">'.(strlen($row->said_important)>20?substr($row->said_important, 0, 20).'...':$row->said_important).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'result_of_action" style="widtd: 80px" class="middle_size">'.(strlen($row->result_of_action)>20?substr($row->result_of_action, 0, 20).'...':$row->result_of_action).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'work_before_the_next_action" style="widtd: 80px" class="middle_size">'.(strlen($row->work_before_the_next_action)>20?substr($row->work_before_the_next_action, 0, 20).'...':$row->work_before_the_next_action).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'date_next_action" style="widtd: 80px" class="middle_size">'.(empty($row->date_next_action)?'':$dtNextAction->format('d.m.y H:i:s')).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'work_before_the_next_action_mentor" style="widtd: 80px" class="middle_size">'.(strlen($row->work_mentor)>20?substr($row->work_mentor, 0, 20).'...':$row->work_mentor).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'date_next_action_mentor" style="widtd: 80px" class="middle_size">'.(empty($row->date_mentor)?'':$dtDateMentor->format('d.m.y H:i:s')).'</td>
            <td rowid="'.$row->rowid.'" id = "'.$row->rowid.'action" style="width: 35px" class="middle_size"><script>
                 var click_event = "/dolibarr/htdocs/societe/addcontact.php?action=edit&mainmenu=companies&rowid=1";
                </script>
                <img id="img_1" "="" onclick="" style="vertical-align: middle" title="'.$langs->trans('AddSubAction').'" src="/dolibarr/htdocs/theme/eldy/img/Add.png">
                <img id="img_1" "="" onclick="location.href=click_event" style="vertical-align: middle" title="'.$langs->trans('Edit').'" src="/dolibarr/htdocs/theme/eldy/img/edit.png">
            </td>
            </tr>';
    }
//        <th style="width: 80px" class="middle_size">Дата і час внесення</th>
//            <th style="width: 100px" class="middle_size">Хто від нас вносив</th>
//            <th style="width: 80px" class="middle_size">З ким діяли</th>
//            <th style="width: 50px" class="middle_size">Вид дій</th>
//            <th style="width: 80px" class="middle_size">Що йому озвучили</th>
//            <th style="width: 80px" class="middle_size">Що він відповів</th>
//            <th style="width: 80px" class="middle_size">Чим аргументував</th>
//            <th style="width: 80px" class="middle_size">Що важливого сказав</th>
//            <th style="width: 80px" class="middle_size">Результат дій (резюме переговорника)</th>
//            <th style="width: 80px" class="middle_size">Робота до/на наступних дій</th>
//            <th style="width: 80px" class="middle_size">Дата наст.дій</th>
//            <th style="width: 80px" class="middle_size">Робота до/на наступних дій (завдання наставника)</th>
//            <th style="width: 80px" class="middle_size">Запропонована дата виконання наставником</th>
    $out .= '</tbody>';
    return $out;
}