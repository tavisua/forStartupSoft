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
    }elseif($_REQUEST['action'] == 'getProposition') {
//    llxHeader("",'Close',"");

        echo getProposition($_REQUEST['socid']);
        exit();
    }elseif($_REQUEST['action'] == 'setStatus'){
        echo setStatus();
        exit();
    }elseif($_REQUEST['action'] == 'getContactList'){
        echo getContactList();
        exit();
    }elseif($_REQUEST['action'] == 'getLastNotExecAction'){
        echo getLastNotExecAction($_REQUEST['contactid']);
        exit();
    }elseif($_REQUEST['action'] == 'showCallStatus'){
        echo showCallStatus();
        exit();
    }elseif($_REQUEST['action'] == 'showTitleProposition'){

//        echo '<pre>';
//        var_dump($_REQUEST);
//        echo '</pre>';
        echo showTitleProposition($_REQUEST['post_id'], $_REQUEST['lineactive'], $_REQUEST['contactid'], $_REQUEST['socid']);
        exit();
    }elseif($_REQUEST['action'] == 'showProposition'){
        echo showProposition($_REQUEST['id'],$_REQUEST['contactid']);
        exit();
    }
}elseif(isset($_REQUEST['beforeload'])){
    llxHeader("",'Close',"");
    print '<script>
        $(document).ready(function(){
            close();
        })
    </script>';
    exit();
}


$refer_link = $_SERVER['HTTP_REFERER'];
$ActionArea = $langs->trans('ActionArea');
llxHeader("",$ActionArea,"");
if(isset($_REQUEST['action'])){
    if($_REQUEST['action'] == 'add'){
        print_fiche_titre($langs->trans('AddAction'));
        addaction();
        exit();
    }
}
print '<div style="width: 150px;float: left;">';

print_fiche_titre($ActionArea);

$object = new Societe($db);
$socid = empty($_REQUEST['socid'])?0:$_REQUEST['socid'];
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

$sql = 'select `llx_societe_contact`.rowid, `llx_societe_contact`.`socid`, `llx_societe_contact`.`post_id`, subdivision,  concat(trim(nametown), " ", trim(regions.name), " р-н. ", trim(states.name), " обл.") as nametown,
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
//echo '<pre>';
//var_dump($_REQUEST);
//echo '</pre>';
//die($sql);

$contacttable = new societecontact();
//var_dump($_REQUEST['sortfield']);

if(!isset($_REQUEST['sortfield'])) {
    $contact = $contacttable->fShowTable($TableParam, $sql, "'" . $tablename . "'", $conf->theme, null, null, $readonly = array(), false);
}else
    $contact = $contacttable->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme, $_REQUEST['sortfield'], $_REQUEST['sortorder']);
unset($TableParam);

$datep = new DateTime();

$actiontabe = ShowActionTable();
//var_dump(round($object->area,0));
//die();
$contactname = '';
if(!empty($_REQUEST['contactID'])){
    $societe = new Societe($db);
    $contactname = $societe->getContactname($_REQUEST['contactID']);
}
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/responsibility/sale/action.html';
//llxFooter();
llxPopupMenu();
exit();

function getContactList(){
    global $db;
    $sql = "select rowid, lastname, firstname from `llx_societe_contact`
        where socid = ".$_REQUEST['socid']."
        and active = 1";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $out='<table style="background-color: #fff">
        <thead>
        <tr class="multiple_header_table">
            <th>
                Вкажіть ім&#8242;я контакту
            </th>
        </tr>
        </thead>
        <tbody>';
    $out.='<tr id="0" class="middle_size" style="cursor:pointer" onclick="SetContactFilter(0);">';
    $out.='<td>Відобразити всіх</td>';
    $out.='</tr>';
    $count = 0;
    while($obj = $db->fetch_object($res)){
        $class = fmod($count,2)==1?("impair"):("pair");
        $out.='<tr id="'.$obj->rowid.'" class="middle_size '.$class.'" style="cursor:pointer" onclick="SetContactFilter('.$obj->rowid.');">';
        $out.='<td>'.trim($obj->lastname.' '.$obj->firstname).'</td>';
        $out.='</tr>';
        $count++;
    }
    $out.='</tbody></table>';
    return $out;
}
function setStatus(){
    global $db,$user;
    $sql = "insert into llx_societe_action(action_id,result_of_action,active,id_usr)
    values(".$_REQUEST['rowid'].", '".$_REQUEST['result_of_action']."', 1, ".$user->id.")";
//    var_dump($_REQUEST);
//    die();
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $sql = "update llx_actioncomm set percent = 100 where id = ".$_REQUEST['rowid'];
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    return 1;
}

function showProposition($proposed_id,$contactid=0){
    global $db, $langs;
    $sql = 'select `begin`, `end`, `description`,  `text`
        from  `llx_c_proposition`
        where rowid = '.$proposed_id;
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    $LastActionID = getLastNotExecAction($contactid, date('Y-m-d',time()));
    $out='<table class="scrolling-table" style="background: #ffffff; width: auto">
            <input type="hidden" id="actionid" name="actionid" value="'.$LastActionID.'">
            <thead><tr class="multiple_header_table"><th class="middle_size" colspan="9" style="width: 100%">Суть пропозиції для посади '.$obj->postname.'</th>
            <a class="close" style="margin-left: -160px" onclick="ClosePopupMenu($(this));" title="Закрити"></a>
                </tr>
                </thead>
            <tbody  id="bodyProposition">';
    $beginProposition = new DateTime($obj->begin);
    if(!empty($obj->end)) {
        $endProposition = new DateTime($obj->end);
        $endProposition = $endProposition->format('d.m.Y');
    }else
        $endProposition = $obj->description;
    require_once DOL_DOCUMENT_ROOT.'/product/class/proposedProducts.class.php';
    $proposedPoducts = new proposedProducts($db);
    $tabody = $proposedPoducts->ShowProducts($proposed_id, true);

    $out .=$tabody;
//    $out .='<tr>
//                <td colspan="9"><button onclick="SaveResultProporition('.$contactid.');">Зберегти результати перемовин</button></td>
//            </tr>';
    $out .='</tbody></table>';
    $out .='<div style="width: 100%; background-color: "><button id="savebutton" onclick="SaveResultProporition('.$contactid.','.$LastActionID.');">Зберегти результати перемовин</button></div>';
    $out.='<style>
            div#BasicInformation, div#PriceOffers, div#OtherInformationOffers{
                font-size: 12px;
            }
        </style>
        <script>
            $(document).ready(function(){
                var top = 0;
                if($("div#Proposition").length>0)
                    top = $("div#Proposition").offset().top;
                var height = $(window).height() - 200;
                if(height<300)
                    height = 350;
                $("tbody#bodyProposition").height(height);
            })
        </script>';
    return $out;
}
function showTitleProposition($post_id, $lineactive, $contactid=0, $socid){
    global $db;
    if(empty($post_id) && empty($lineactive) && empty($contactid)){
        $sql = 'select `llx_c_proposition`.rowid, `text`
                from  `llx_c_proposition` where 1';
        $sql .= ' and ((`llx_c_proposition`.`end` is not null and Now() between `llx_c_proposition`.`begin` and `llx_c_proposition`.`end`) or `llx_c_proposition`.`end` is null)
            and `llx_c_proposition`.active = 1';
        $res = $db->query($sql);
//        llxHeader();
        $titleArray = array();
        while($obj = $db->fetch_object($res)){
            if(!isset($titleArray[trim($obj->text)]))
                $titleArray[trim($obj->text)]=$obj->rowid;
        }
        $rowid='';
        foreach($titleArray as $item=>$value){
            if(!empty($rowid))
                $rowid.=',';
            $rowid.=$value;
        }
        $sql = 'select `llx_c_proposition`.rowid, `text` from  `llx_c_proposition`';
        $sql .= 'where rowid in('.$rowid.')';
    }else {
        $sql = 'select `llx_c_proposition`.rowid, text, llx_c_proposition.prioritet
        from  `llx_c_proposition`
        inner join `llx_post` on `llx_post`.`rowid` = `llx_c_proposition`.`fk_post`
        where 1
        and `llx_c_proposition`.fk_post = ' . $post_id . '
        and `llx_c_proposition`.fk_lineactive = ' . $lineactive . '
        and ((`llx_c_proposition`.`end` is not null and Now() between `llx_c_proposition`.`begin` and `llx_c_proposition`.`end`) or `llx_c_proposition`.`end` is null)
        and `llx_c_proposition`.active = 1';
        if ($contactid != 0)
            $sql .= ' and `llx_c_proposition`.rowid not in (select distinct proposed_id from `llx_societe_action`
                where contactid=' . $contactid . '
                and proposed_id is not null
                and active = 1)';
        $sql.=" union
        select `llx_c_proposition`.rowid, text, llx_c_proposition.prioritet
            from `llx_c_proposition`
            inner join llx_proposition_properties on llx_proposition_properties.fk_proposition = `llx_c_proposition`.rowid
            inner join `llx_post` on `llx_post`.`rowid` = `llx_proposition_properties`.`fk_post`
            where 1
            and llx_proposition_properties.fk_post = " . $post_id . "
            and llx_proposition_properties.fk_lineactive = " . $lineactive . "
            and llx_proposition_properties.active = 1
            and ((`llx_c_proposition`.`end` is not null and Now() between `llx_c_proposition`.`begin` and `llx_c_proposition`.`end`) or `llx_c_proposition`.`end` is null)
            and `llx_c_proposition`.active = 1";
        if ($contactid != 0)
            $sql .= ' and `llx_c_proposition`.rowid not in (select distinct proposed_id from `llx_societe_action`
                where contactid=' . $contactid . '
                and proposed_id is not null
                and active = 1)';
        $sql.=' order by prioritet';
    }
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//die();
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    $out='<table class="setdate" style="background: #ffffff; width: 250px">
            <thead><tr class="multiple_header_table"><th class="middle_size" colspan="3" style="width: 100%">Виберіть пропозицію для посади '.$obj->postname.'</th>
            <a class="close" style="margin-left: -160px" onclick="ClosePopupMenu();" title="Закрити"></a>
                </tr>
                <tr class="multiple_header_table">
                    <th class="middle_size">№п/п</th>
                    <th class="middle_size">Заголовок</th>
                </tr>
                </thead>
            <tbody>';
    mysqli_data_seek($res, 0);
    $num = 1;
    while($obj = $db->fetch_object($res)) {
//        $begin = new DateTime($obj->begin);
//        if(!empty($obj->end)) {
//            $end = new DateTime($obj->end);
//            $end = $end->format('d.m.Y');
//        }else
//            $end = $obj->description;
        $out .='<tr class = "'.(fmod($num,2)==0?'impair':'pair').'" onclick = "showProposed('.$obj->rowid.', '.$contactid.');" style = "cursor: pointer">
                    <td class="middle_size">'.$num++.'</td >
                    <td class="middle_size">'.$obj->text.'</td >
                </tr >';
    }
    $out .='</tbody>
        </table>';
    return $out;
}
function getProposition($socid = 0){
    global $db;
    $sql = 'select fk_lineactive from llx_societe_lineactive where fk_soc = '.$socid. ' and active = 1';
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $lineactive = array();
    while($db->num_rows($res) && $obj = $db->fetch_object($res)){
        $lineactive[] = $obj->fk_lineactive;
    }
//    var_dump(count($lineactive));
//    die($sql);
    $sql ='select distinct `llx_post`.rowid, `llx_post`.postname, `llx_proposition_properties`.`fk_lineactive` from `llx_c_proposition`
       inner join `llx_proposition_properties` on `llx_proposition_properties`.`fk_proposition` = `llx_c_proposition`.rowid
        inner join `llx_post` on `llx_post`.`rowid` = `llx_proposition_properties`.`fk_post`
        where 1
        and (Now() between `llx_c_proposition`.`begin` and `llx_c_proposition`.`end` )
        or `llx_c_proposition`.`end` is null
        and `llx_c_proposition`.active = 1
        and `llx_proposition_properties`.`active` = 1
        union
        select distinct `llx_post`.rowid, `llx_post`.postname, `llx_c_proposition`.`fk_lineactive` from `llx_c_proposition`
        inner join `llx_post` on `llx_post`.`rowid` = `llx_c_proposition`.`fk_post`
        where 1
        and (Now() between `begin` and `end` )
        or `end` is null
        and `llx_c_proposition`.active = 1;';
    $res_prop = $db->query($sql);
    if(!$res_prop)
        dol_print_error($db);
    $out = '';
//    die($sql);
    while($obj = $db->fetch_object($res_prop)){
        if(empty($obj->fk_lineactive) || in_array($obj->fk_lineactive,$lineactive) || count($lineactive) == 0)
            $out .= '<tr>
                        <td class="middle_size" style="padding-left: 10px;" colspan="4" id="'.$obj->rowid.'">'.$obj->postname.'</td>
                    </tr>';
    }
//    llxHeader();
//    var_dump($out);
//    die();
    return $out;
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
function getLastNotExecAction($contactid, $date = null){
    global $db;
    $sql = 'select id from `llx_actioncomm`
        where fk_contact = '.$contactid.'
        and percent <> 100
        and active = 1';
    if(empty($date))
        $sql.=' order by datep desc limit 1';
    else
        $sql.=" and date(datep) = '".$date."'";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    if($db->num_rows($res)>0) {
        $obj = $db->fetch_object($res);
        return $obj->id;
    }else
        return 0;
//    var_dump($_REQUEST);
//    die();
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
function showCallStatus(){
    global $db;
    $sql = "select rowid, status from llx_c_callstatus where active = 1";
    $out='<table class="setdate" style="background: #ffffff; width: 250px">
            <thead><tr class="multiple_header_table"><th class="middle_size" colspan="3" style="width: 100%">Результат перемовин </th>
            <a class="close" style="margin-left: -160px" onclick="CloseCallStatus();" title="Закрити"></a>
                </tr>
                </thead>
            <tbody>';
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)){
        $out.='<tr><td id="status_id_'.$obj->rowid.'"  class="middle_size" style="cursor:pointer" onclick="selStatus('.$obj->rowid.', '.(empty($_GET['answer_id'])?0:$_GET['answer_id']).');">'.$obj->status.'</td></tr>';
    }
    $out.='</tbody></table>';
    return $out;
}
function ShowActionTable(){
    global $db, $langs, $conf;
    $sql = 'select fk_parent, datep from `llx_actioncomm` where fk_soc = '.(empty($_REQUEST['socid'])?0:$_REQUEST['socid']).' and fk_parent <> 0';
    $res = $db->query($sql);
    $nextaction = array();
    while($row = $db->fetch_object($res)){
        $nextaction[$row->fk_parent] = $row->datep;
    }
    $sql = "select `llx_actioncomm`.type, `llx_actioncomm`.id as rowid, `llx_societe_action`.`rowid` as answer_id, `llx_actioncomm`.`datep`, `llx_societe_action`.dtChange as `datec`, `llx_user`.lastname,
        concat(case when `llx_societe_contact`.lastname is null then '' else `llx_societe_contact`.lastname end,  ' ',
        case when `llx_societe_contact`.firstname is null then '' else `llx_societe_contact`.firstname end) as contactname,
        TypeCode.code kindaction, `llx_societe_action`.`said`, `llx_societe_action`.`answer`,`llx_societe_action`.`argument`,
        `llx_societe_action`.`said_important`, `llx_societe_action`.`result_of_action`, `llx_societe_action`.`work_before_the_next_action`,`llx_actioncomm`.fk_contact
        from `llx_actioncomm`
        inner join (select code, libelle label from `llx_c_actioncomm` where active = 1 and (type = 'system' or type = 'user')) TypeCode on TypeCode.code = `llx_actioncomm`.code
        left join `llx_societe_contact` on `llx_societe_contact`.rowid=`llx_actioncomm`.fk_contact
        left join `llx_societe_action` on `llx_actioncomm`.id = `llx_societe_action`.`action_id`
        left join `llx_user` on `llx_societe_action`.id_usr = `llx_user`.rowid
        where fk_soc = ".(empty($_REQUEST["socid"])?0:$_REQUEST["socid"])." and `llx_actioncomm`.`active` = 1
        union
        select '', concat('_',`llx_societe_action`.`rowid`) rowid, `llx_societe_action`.`rowid`, `llx_societe_action`.dtChange datep, `llx_societe_action`.dtChange as `datec`, `llx_user`.lastname,
        concat(case when `llx_societe_contact`.lastname is null then '' else `llx_societe_contact`.lastname end, ' ',
        case when `llx_societe_contact`.firstname is null then '' else `llx_societe_contact`.firstname end) as contactname, null, `llx_societe_action`.`said`, `llx_societe_action`.`answer`,`llx_societe_action`.`argument`,
        `llx_societe_action`.`said_important`, `llx_societe_action`.`result_of_action`, `llx_societe_action`.`work_before_the_next_action`,`llx_societe_action`.`contactid`
        from `llx_societe_action`
        left join `llx_user` on `llx_societe_action`.id_usr = `llx_user`.rowid
        left join `llx_societe_contact` on `llx_societe_contact`.rowid=`llx_societe_action`.`contactid`
        where `llx_societe_action`.socid=".(empty($_REQUEST["socid"])?0:$_REQUEST["socid"])."
        and `llx_societe_action`.`action_id` is null
        and `llx_societe_action`.`active` = 1
        order by `datep` desc";



//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();
//    die($sql);
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

//    var_dump($contactname);
//    die();
    while($row = $db->fetch_object($res)){
        if(empty($_REQUEST['contactID']) || $_REQUEST['contactID'] == $row->fk_contact) {
            $dtChange = new DateTime($row->datec);

            if (isset($nextaction[$row->rowid])) {
                $row->date_next_action = $nextaction[$row->rowid];
//            var_dump($nextaction[$row->rowid]);
//            die();
            }
            $dtNextAction = new DateTime($row->date_next_action);
            $dtDateMentor = new DateTime($row->date_mentor);
            $iconitem = '';
            $title = '';
            switch ($row->kindaction) {
                case 'AC_GLOBAL': {
                    $classitem = 'global_taskitem';
                    $iconitem = 'object_global_task.png';
                    $title = $langs->trans('ActionGlobalTask');
                }
                    break;
                case 'AC_CURRENT': {
                    $classitem = 'current_taskitem';
                    $iconitem = 'object_current_task.png';
                    $title = $langs->trans('ActionCurrentTask');
                }
                    break;
                case 'AC_RDV': {
                    $classitem = 'office_meetting_taskitem';
                    $iconitem = 'object_office_meetting_task.png';
                    $title = $langs->trans('ActionAC_RDV');
                }
                    break;
                case 'AC_TEL': {
                    $classitem = 'office_callphone_taskitem';
                    $iconitem = 'object_call2.png';
                    $title = $langs->trans('ActionAC_TEL');
                }
                    break;
                case 'AC_DEP': {
                    $classitem = 'departure_taskitem';
                    $iconitem = 'object_departure_task.png';
                    $title = $langs->trans('ActionDepartureMeeteng');
                }
                    break;
                default: {
                    $iconitem = 'object_call2.png';
                    $title = $langs->trans('ActionAC_TEL');
                }
                    break;
            }

            $dateaction = new DateTime($row->datep);
            $out .= '<tr class="' . (fmod($num++, 2) == 0 ? 'impair' : 'pair') . '">';
            $out .= '<td rowid="' . $row->rowid . '" id = "' . $row->rowid . 'dtAction" style="width: 80px" class="middle_size">' . (empty($row->datep) ? '' : ($dateaction->format('d.m.y') . '</br>' . $dateaction->format('H:i'))) .
                (!empty($row->type) ? '<div style="float: right; margin-top: -15px" title="Час початку дії встановлено вручну"><img src="/dolibarr/htdocs/theme/eldy/img/object_task.png"></div>' : '') . '</td>';
            $out .= '<td rowid="' . $row->rowid . '" id = "' . $row->rowid . 'dtChange" style="width: 80px" class="middle_size">' . (empty($row->datec) ? '' : $dtChange->format('d.m.y ').'</br>'.$dtChange->format('H:i')) . '</td>
            <td rowid="' . $row->rowid . '" id = "' . $row->rowid . 'lastname" style="width: 100px" class="middle_size">' . $row->lastname . '</td>
            <td rowid="' . $row->rowid . '" id = "' . $row->rowid . 'contactname" style="width: 80px" class="middle_size">' . $row->contactname . '</td>
            <td rowid="' . $row->rowid . '" id = "' . $row->rowid . 'kindaction" style="width: 50px; text-align: center;" class="middle_size" ><img src="/dolibarr/htdocs/theme/' . $conf->theme . '/img/' . $iconitem . '" title="' . $title . '"></td>
            <td rowid="' . $row->rowid . '" id = "' . $row->rowid . 'said" style="width: 80px" class="middle_size">' . (strlen($row->said) > 20 ? mb_substr($row->said, 0, 20, 'UTF-8') . '...' .
                    '<input id="_' . $row->rowid . 'said"  type="hidden" value="' . $row->said . '">'
                    : $row->said) . '</td>
            <td rowid="' . $row->rowid . '" id = "' . $row->rowid . 'answer" style="width: 80px" class="middle_size">' . (strlen($row->answer) > 20 ? mb_substr($row->answer, 0, 20, 'UTF-8') . '...' .
                    '<input id="_' . $row->rowid . 'answer"  type="hidden" value="' . $row->answer . '">' : $row->answer) . '</td>
            <td rowid="' . $row->rowid . '" id = "' . $row->rowid . 'argument" style="width: 80px" class="middle_size">' . (strlen($row->argument) > 20 ? mb_substr($row->argument, 0, 20, 'UTF-8') . '...' .
                    '<input id="_' . $row->rowid . 'argument" type="hidden" value="' . $row->argument . '">' : $row->argument) . '</td>
            <td rowid="' . $row->rowid . '" id = "' . $row->rowid . 'said_important" style="width: 80px" class="middle_size">' . (strlen($row->said_important) > 20 ? mb_substr($row->said_important, 0, 20, 'UTF-8') . '...' .
                    '<input id="_' . $row->rowid . 'said_important" type="hidden" value="' . $row->said_important . '">' : $row->said_important) . '</td>
            <td rowid="' . $row->rowid . '" answer_id="' . $row->answer_id . '" id = "' . $row->rowid . 'result_of_action" style="width: 80px" class="middle_size result_of_action">' . (strlen($row->result_of_action) > 20 ? mb_substr($row->result_of_action, 0, 20, 'UTF-8') . '...' .
                    '<input id="_' . $row->rowid . 'result_of_action" type="hidden" value="' . $row->result_of_action . '">' : $row->result_of_action) . '</td>
            <td rowid="' . $row->rowid . '" id = "' . $row->rowid . 'work_before_the_next_action" style="width: 80px" class="middle_size">' . (strlen($row->work_before_the_next_action) > 20 ? mb_substr($row->work_before_the_next_action, 0, 20, 'UTF-8') . '...' .
                    '<input id="_' . $row->rowid . 'work_before_the_next_action" type="hidden" value="' . $row->work_before_the_next_action . '">' : $row->work_before_the_next_action) . '</td>
            <td rowid="' . $row->rowid . '" id = "' . $row->rowid . 'date_next_action" style="width: 80px" class="middle_size">' . (empty($row->date_next_action) ? '' : $dtNextAction->format('d.m.y H:i:s')) . '</td>
            <td rowid="' . $row->rowid . '" id = "' . $row->rowid . 'work_before_the_next_action_mentor" style="width: 80px" class="middle_size">' . (strlen($row->work_mentor) > 20 ? mb_substr($row->work_mentor, 0, 20, 'UTF-8') . '...' .
                    '<input id="_' . $row->rowid . 'work_before_the_next_action_mentor" type="hidden" value="' . $row->work_mentor . '">' : $row->work_mentor) . '</td>
            <td rowid="' . $row->rowid . '" id = "' . $row->rowid . 'date_next_action_mentor" style="width: 80px" class="middle_size">' . (empty($row->date_mentor) ? '' : $dtDateMentor->format('d.m.y H:i:s')) . '</td>
            <td rowid="' . $row->rowid . '" id = "' . $row->rowid . 'action" style="width: 35px" class="middle_size"><script>
                 var click_event = "/dolibarr/htdocs/societe/addcontact.php?action=edit&mainmenu=companies&rowid=1";
                </script>';
//        $out.='<img onclick="" style="vertical-align: middle" title="'.$langs->trans('AddSubAction').'" src="/dolibarr/htdocs/theme/eldy/img/Add.png">';
            $out .= '<img onclick="EditAction(' . (substr($row->rowid, 0, 1) == '_' ? "'" . $row->rowid . "'" : $row->rowid) . ', ' . (empty($row->answer_id) ? '0' : $row->answer_id) . ', ' . "'" . (empty($row->kindaction) ? 'AC_TEL' : $row->kindaction) . "'" . ');" style="vertical-align: middle; cursor: pointer;" title="' . $langs->trans('Edit') . '" src="/dolibarr/htdocs/theme/eldy/img/edit.png">
                <img onclick="DelAction(' . (substr($row->rowid, 0, 1) == '_' ? "'" . $row->rowid . "'" : $row->rowid) . ');" style="vertical-align: middle; cursor: pointer;" title="' . $langs->trans('delete') . '" src="/dolibarr/htdocs/theme/eldy/img/delete.png">
            </td>
            </tr>';
        }
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