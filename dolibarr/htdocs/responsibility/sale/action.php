<?php
require $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/main.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/core/modules/societe/modules_societe.class.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/societe/societecontact_class.php';
global $user,$db;
$subdivUserID = array(0);

if(in_array('dir_depatment',array($user->respon_alias,$user->respon_alias2))){
    $sql = "select rowid from llx_user
        inner join (select subdiv_id from llx_user
        where rowid = ".$user->id.") subdiv on llx_user.subdiv_id = subdiv.subdiv_id
        where llx_user.active = 1";
    $res = $db->query($sql);
    while($obj = $db->fetch_object($sql)){
        $subdivUserID[]=$obj->rowid;
    }
}
if(empty($_REQUEST['socid'])&&!empty($_REQUEST['action_id'])){
    $action_id = $_REQUEST['action_id'];
    if(substr($action_id, 0,1) == '_')
        $action_id = substr($action_id,1);
    $sql = "select fk_soc from llx_actioncomm where id = ".$action_id;
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    $_REQUEST['socid'] = $obj->fk_soc;
}
if(isset($_REQUEST['action'])||isset($_POST['action'])){
    if($_REQUEST['action'] == 'loadcontactlist'){
        echo loadcontactlist($_REQUEST['contactid']);
        exit();
    }elseif($_POST['action'] == 'save'){
        saveaction();
        exit();
    }elseif($_REQUEST['action'] == 'getCallLink') {//Визначення інформації про наступний контакт, якому треба дзвонити
        echo getCallLink(explode(';',$_REQUEST['actionlist']));
        exit();
    }elseif($_REQUEST['action'] == 'save_not_saved_proposition') {
        echo save_not_saved_proposition();
        exit();
    }elseif($_REQUEST['action'] == 'getProposition') {
//    llxHeader("",'Close',"");

        echo getProposition($_REQUEST['socid']);
        exit();
    }elseif($_REQUEST['action'] == 'setCallID'){
        echo setCallID();
        exit();
    }elseif($_REQUEST['action'] == 'setCallingStatus'){
        echo setCallingStatus();
        exit();
    }elseif($_REQUEST['action'] == 'setCallLength'){
        echo setCallLength();
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
        echo showTitleProposition($_REQUEST['post_id'], $_REQUEST['lineactive'], $_REQUEST['contactid'], $_REQUEST['socid'], $_REQUEST['show_icon']);
        exit();
    }elseif($_REQUEST['action'] == 'showProposition'){
        echo showProposition($_REQUEST['id'],$_REQUEST['contactid']);
        exit();
    }elseif ($_REQUEST['action'] == 'getSmsProposition'){//вертає масив смс повідомлень, пов'язаних з пропозиціями
        echo showSmsProposition();
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

$sql = 'select `llx_societe_contact`.rowid, `llx_societe_contact`.`socid`, case when `llx_societe_contact`.`post_id` is null or `llx_societe_contact`.`post_id`= 0 then 27 else `llx_societe_contact`.`post_id` end post_id , subdivision,  concat(trim(nametown), " ", trim(regions.name), " р-н. ", trim(states.name), " обл.") as nametown,
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
//Перевірка на потребу корегування id_usr
$sql = "select count(*)iCount from llx_societe_action
    left join llx_user on llx_user.rowid = llx_societe_action.id_usr
    where socid = $socid
    and subdiv_id is null";
$res = $db->query($sql);
if(!$res)
    dol_print_error($db);
$obj = $db->fetch_object($res);
if($obj->iCount>0){
    $sql = "update llx_societe_action 
        left join llx_actioncomm 
        on llx_societe_action.action_id = `llx_actioncomm`.id
        set llx_societe_action.id_usr = `llx_actioncomm`.fk_user_author
        where llx_societe_action.socid = $socid
        and (llx_societe_action.id_usr is null or llx_societe_action.id_usr = 0)";
    if(!$res)
        dol_print_error($db);
}
$societe = new Societe($db);
$societe->fetch($_REQUEST['socid']);
$societe_name = $societe->name;
$langs;
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/responsibility/sale/action.html';
//llxFooter();
llxPopupMenu();
exit();
function save_not_saved_proposition(){
//    echo '<pre>';
//    var_dump($_REQUEST);
//    echo '</pre>';
//    die();
    global $db,$user;
    $sql = "update llx_actioncomm set percent = 80 where id = ".$_REQUEST["actionid"];
    $db->query($sql);
    $sql = "select rowid,`text` from `llx_c_proposition` where rowid in (".$_REQUEST["proposition_id"].")";
    $res = $db->query($sql);
    while($obj = $db->fetch_object($res)){
        $insertSQL="insert into llx_societe_action (`action_id`,`proposed_id`,`interesting`,`socid`,`contactid`,`callstatus`,
            `said`,`answer`,`argument`,`said_important`,`result_of_action`,`work_before_the_next_action`,`active`,`id_usr`)
            values(".$_REQUEST["actionid"].", ".$obj->rowid.", 1, ".$_REQUEST["socid"].", ".$_REQUEST["contactID"].", 5,
            '".$db->escape(trim($obj->text))."', ";
        $sql = "select ProductName from `llx_proposition_product` where fx_proposition = ".$obj->rowid;
        $prod_res = $db->query($sql);
        if($prod_res->num_rows)
            $insertSQL.="'";
        while($objProd = $db->fetch_object($prod_res)){
            $insertSQL.=$db->escape($objProd->ProductName)." потреба не занесена торговим;";
        }
        if($prod_res->num_rows)
            $insertSQL.="'";
        $insertSQL.=",'-','-','-','-',1,".$user->id.")";
        echo $insertSQL;
        $resInsert = $db->query($insertSQL);
        if(!$resInsert)
            dol_print_error($db);
    }
    //Визначаю
    return 1;
}
function setCallingStatus(){
    $_SESSION['last_call_id'] = $_SESSION['active_call_id'];
    $_SESSION['active_call_id'] = $_REQUEST['actionid'];
    $_SESSION['status']='calling';
    return 'call_status_setting';
}
function showSmsProposition(){
    global $db;
    $sql = "select sms_message from llx_c_proposition
        where now() between `begin` and `end`
        and sms_message is not null
        and active = 1";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    if($res->num_rows) {
        $out = "";
        while ($obj = $db->fetch_object($res)) {
            $out.="^^$obj->sms_message";
        }
    }else{
        $out = "";
    }
    if(empty($out))
        return 0;
    return $out;
}

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
function setCallLength(){
    global $db;
    if(!empty($_REQUEST['callID'])) {
        $start = new DateTime($_REQUEST['start']);
        $end = new DateTime($_REQUEST['end']);
        $diff = mktime($end->format('H'), $end->format('i'), $end->format('s'), $end->format('m'), $end->format('d'), $end->format('Y')) -
            mktime($start->format('H'), $start->format('i'), $start->format('s'), $start->format('m'), $start->format('d'), $start->format('Y'));
        $sql = "update llx_actioncomm set CallLength = " . $diff . " where callID = '" . $_REQUEST['callID'] . "' limit 1";
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        return $diff;
    }else
        return 0;
}
function setCallID(){
    global $db;
    $sql = "select id from llx_actioncomm
        where fk_contact = ".$_REQUEST['contactID']."
        and active = 1
        and percent < 99
        order by datep
        limit 1";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    $sql = "update llx_actioncomm set CallID = '".$_REQUEST['callID']."' where id = ".$obj->id;
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    return 1;
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
    if(!empty($_REQUEST['rowid'])) {
        $sql = "update llx_actioncomm set percent = 100 where id = " . $_REQUEST['rowid'];
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        $sql = "delete from llx_newactions where id = " . $_REQUEST['rowid'];
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
    }
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
    $LastActionID = empty($_REQUEST['actionid'])?getLastNotExecAction($contactid, date('Y-m-d',time())):$_REQUEST['actionid'];
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
    $out .='<div style="width: 100%; background-color: white"><button id="savebutton" onclick="GetFormSavingResultProposition('.$contactid.','.$LastActionID.', '.$proposed_id.', true);">Зберегти результати перемовин</button></div>';
    $out.='<style>
            div#BasicInformation, div#PriceOffers, div#OtherInformationOffers{
                font-size: 12px;
            }
        </style>
        <script>
            $(document).ready(function(){
                var top = 0;
                if($("div#Proposition").length>0){
                    top = $("div#Proposition").offset().top;                  
                }
                var item = $("div#Proposition'.$proposed_id.'").find(".pair")[0];
                var header = $("div#Proposition'.$proposed_id.'").find(".multiple_header_table")[0];
                console.log($(header), $(header).width(), $(item), $(item).width());                  
                var height = $(window).height() - 200;
                if(height<300)
                    height = 350;
                $("div#Proposition'.$proposed_id.'").find("tbody#bodyProposition").height(height);
                                
            })
//            $(\'.proposition\').click(function(e){
//                alert(\'test\');
//                console.log($(e).attr(\'z-index\'));
//            })            
        </script>';
    return $out;
}
function showTitleProposition($post_id, $lineactive, $contactid=0, $socid, $show_icon = false){
    global $db;
    if(empty($post_id) && empty($lineactive) && empty($contactid)){
        $sql = 'select `llx_c_proposition`.rowid, llx_c_proposition.action, `text`
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
        $sql = 'select `llx_c_proposition`.rowid, llx_c_proposition.action, `text` from  `llx_c_proposition`';
        $sql .= 'where rowid in('.$rowid.')';
    }else {
        $sql = 'select `llx_c_proposition`.rowid, llx_c_proposition.action, text, llx_c_proposition.prioritet
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
        select `llx_c_proposition`.rowid, llx_c_proposition.action, text, llx_c_proposition.prioritet
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
        if($_REQUEST['$_REQUEST'])
            $sql.=' order by case when `action` is null then 0 else `action` end desc, prioritet';
        else
            $sql.=' order by prioritet';

    }
//    echo '<pre>';
//    var_dump($_REQUEST);
//    echo '</pre>';
//die();
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    $out='<table class="setdate" style="background: #ffffff; width: 250px">
            <thead><tr class="multiple_header_table"><th class="middle_size" colspan="4" style="width: 100%">Виберіть пропозицію для посади '.$obj->postname.'</th>
            <a class="close" style="margin-left: -160px" onclick="ClosePopupMenu();" title="Закрити"></a>
                </tr>
                <tr class="multiple_header_table">
                    <th class="middle_size">№п/п</th>
                    <th class="middle_size">Заголовок</th>
                    <th class="middle_size">Цікаво</th>
                    <th class="middle_size">Не цікаво</th>
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
        $out .='<tr id="prop_'.$obj->rowid.'" class = "'.(fmod($num,2)==0?'impair':'pair').'" style = "cursor: pointer">
                    <td class="middle_size">'.($obj->action == 1 && $show_icon?'<img class="action" title="Поздоровити з днем народження" src="/dolibarr/htdocs/theme/eldy/img/birthday.png">':$num++).'</td >
                    <td class="middle_size">'.$obj->text.'</td >
                    <td class="middle_size" align = "center"><input id="intr_'.$obj->rowid.'_'.$contactid.'" type="checkbox" onclick = "showProposed('.$obj->rowid.', '.$contactid.');"></td >                    
                    <td class="middle_size" align = "center"><input id="unintr_'.$obj->rowid.'_'.$contactid.'"type="checkbox" onclick = "NotIterestingProposed('.$obj->rowid.', '.$contactid.');"></td >                    
                </tr >';
    }
    $out .='</tbody>
        </table>';
    return $out;
}
function getCallLink($actionlist){
    global $db;
    for($index = 0; $index<count($actionlist);$index++){
        if(empty($actionlist[$index])){
            unset($actionlist[$index--]);
        }
    }
    if(count($actionlist)) {
        $_SESSION['autocall'] = null;
    }
    if(empty($_SESSION['autocall'])) {
        for ($index = 0; $index < count($actionlist); $index++) {
            $_SESSION['autocall']['action_id'][] = $actionlist[$index];
        }
    }
    $_SESSION['autocall']['status'] = 'waiting';

//    if(!empty($_SESSION['autocall'])){
//        echo '<pre>';
//        var_dump($_SESSION['autocall']);
//        echo '</pre>';
//        die();
//    }
    while(empty($obj) || in_array($obj->percent, [100,-100])&&count($_SESSION['autocall'])) {
        $index=0;
        for($index;$index<count($_SESSION['autocall']['action_id']);$index++){
            if(!empty($_SESSION['autocall']['action_id'][$index]))
                break;
        }
        $sql = "select fk_soc, fk_contact, percent from llx_actioncomm where id = " . $_SESSION['autocall']['action_id'][$index];
//        var_dump($sql);
//        return;
        $res = $db->query($sql);
//        return $_SESSION['autocall'][0].$sql.'</br>'.var_dump($_SESSION['autocall']);
        if (!$res)
            unset($_SESSION['autocall']['action_id'][$index]);
        else
            $obj = $db->fetch_object($res);

        $_SESSION['autocall']['active_call_id'] = $_SESSION['autocall']['action_id'][$index];
    }
    unset($_SESSION['autocall']['action_id'][$index]);
    return '/dolibarr/htdocs/responsibility/sale/action.php?socid=' . $obj->fk_soc . '&idmenu=10425&autocall=1&mainmenu=area&contactID=' . $obj->fk_contact.'&actionid='.$_SESSION['autocall']['active_call_id'];
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
    global $db, $langs, $conf,$subdivUserID,$user;
    $object = new Societe($db);
    if($_REQUEST['socid']&&!in_array('dir_depatment',array($user->respon_alias,$user->respon_alias2))) {
        $object->Permission($_REQUEST['socid']);
    }
    $sql = "select `region_id` from `llx_societe` where rowid = ".$_REQUEST['socid'];
    $res_region = $db->query($sql);
    $region_id = $db->fetch_object($res_region);
    $sql = "select fk_id from `llx_user_regions` where fk_user = ".$user->id." and active = 1";
    $res_user_regions = $db->query($sql);
    $user_regions = array(0);
    while($reg_id = $db->fetch_object($res_user_regions)){
        $user_regions[] = $reg_id->fk_id;
    }
//    var_dump($user_regions);
//    die($region_id->region_id);
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
        `llx_societe_action`.`said_important`, `llx_societe_action`.`result_of_action`, `llx_societe_action`.`work_before_the_next_action`,`llx_actioncomm`.fk_contact,
        `llx_actioncomm`.fk_user_author author_id, `llx_societe_action`.work_before_the_next_action_mentor work_mentor, `llx_societe_action`.dtMentorChange date_mentor,`llx_societe_action`.id_usr, `llx_actioncomm`.`icon`
        from `llx_actioncomm`
        inner join (select code, libelle label from `llx_c_actioncomm` where active = 1 and (type = 'system' or type = 'user')) TypeCode on TypeCode.code = `llx_actioncomm`.code
        left join `llx_societe_action` on `llx_actioncomm`.id = `llx_societe_action`.`action_id`        
        left join `llx_societe_contact` on `llx_societe_contact`.rowid=case when `llx_actioncomm`.fk_contact is null then `llx_societe_action`.`contactid` else `llx_actioncomm`.fk_contact end
        left join `llx_user` on case when `llx_societe_action`.id_usr is null or `llx_societe_action`.id_usr = 0  then `llx_actioncomm`.fk_user_author else `llx_societe_action`.id_usr end  = `llx_user`.rowid
        where fk_soc = ".(empty($_REQUEST["socid"])?0:$_REQUEST["socid"])." and `llx_actioncomm`.`active` = 1
        union
        select '', concat('_',`llx_societe_action`.`rowid`) rowid, `llx_societe_action`.`rowid`, `llx_societe_action`.dtChange datep, `llx_societe_action`.dtChange as `datec`, `llx_user`.lastname,
        concat(case when `llx_societe_contact`.lastname is null then '' else `llx_societe_contact`.lastname end, ' ',
        case when `llx_societe_contact`.firstname is null then '' else `llx_societe_contact`.firstname end) as contactname, null, `llx_societe_action`.`said`, `llx_societe_action`.`answer`,`llx_societe_action`.`argument`,
        `llx_societe_action`.`said_important`, `llx_societe_action`.`result_of_action`, `llx_societe_action`.`work_before_the_next_action`,`llx_societe_action`.`contactid`,
        `llx_societe_action`.id_usr author_id, `llx_societe_action`.work_before_the_next_action_mentor work_mentor, `llx_societe_action`.dtMentorChange date_mentor,`llx_societe_action`.id_usr, ''
        from `llx_societe_action`
        left join `llx_user` on `llx_societe_action`.id_usr = `llx_user`.rowid
        left join `llx_societe_contact` on `llx_societe_contact`.rowid=`llx_societe_action`.`contactid`
        where `llx_societe_contact`.socid=".(empty($_REQUEST["socid"])?0:$_REQUEST["socid"])."
        and `llx_societe_action`.`action_id` is null
        and `llx_societe_action`.`active` = 1
        order by `datep` desc, `datec` desc";


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
            <td style="width: 80px" class="middle_size">&nbsp;</td>
            <td style="width: 80px" class="middle_size">&nbsp;</td>
            <td style="width: 100px" class="middle_size">&nbsp;</td>
            <td style="width: 80px" class="middle_size">&nbsp;</td>
            <td style="width: 50px" class="middle_size">&nbsp;</td>
            <td style="width: 80px" class="middle_size">&nbsp;</td>
            <td style="width: 80px" class="middle_size">&nbsp;</td>
            <td style="width: 80px" class="middle_size">&nbsp;</td>
            <td style="width: 80px" class="middle_size">&nbsp;</td>
            <td style="width: 80px" class="middle_size">&nbsp;</td>
            <td style="width: 80px" class="middle_size">&nbsp;</td>
            <td style="width: 80px" class="middle_size">&nbsp;</td>
            <td style="width: 80px" class="middle_size">&nbsp;</td>
            <td style="width: 80px" class="middle_size">&nbsp;</td>
            <td style="width: 35px" class="middle_size">&nbsp;</td>
            </tr>';
    }
    $num=0;

//    var_dump($contactname);
//    die();
    $mentor = false;
    if(!empty($_REQUEST['id_usr'])) {
        $subdivIDs = $user->getmentors($user->id);
        $sql = "select subdiv_id from llx_user where rowid = ".$_REQUEST['id_usr'].' and active = 1';
        $resSubdiv = $db->query($sql);
        if(!$resSubdiv)
            dol_print_error($db);
        $obj = $db->fetch_object($resSubdiv);
        $mentor = in_array($obj->subdiv_id, $subdivIDs);
    }

    while($row = $db->fetch_object($res)){
        if(empty($_REQUEST['contactID']) || $_REQUEST['contactID'] == $row->fk_contact) {
            $dtChange = new DateTime($row->datec);

            if (isset($nextaction[$row->rowid])) {
                $row->date_next_action = $nextaction[$row->rowid];
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
            if(!empty($row->icon)){
                $iconitem = $row->icon;
            }
            $dateaction = new DateTime($row->datep);
            $type_icon = '';
            switch ($row->type){
                case 'w':{
                    $type_icon = '<div style="float: right; margin-top: -15px" title="Час початку дії встановлено вручну"><img src="/dolibarr/htdocs/theme/eldy/img/object_task.png"></div>';
                }break;
                case 'a':{
                    $type_icon = '<div style="float: right; margin-top: -15px" title="Дія створена автоматично"><img src="/dolibarr/htdocs/theme/eldy/img/object_task.png"></div>';
                }break;
            }
//            if(755273 == $row->answer_id){
//                var_dump($row->result_of_action);
//                die();
//            }
            $out .= '<tr class="' . (fmod($num++, 2) == 0 ? 'impair' : 'pair') . '" name="'.$row->rowid.'">';
            $out .= '<td rowid="' . $row->rowid . '" id = "' . $row->rowid . 'dtAction" style="width: 80px" class="middle_size">' . (empty($row->datep) ? '' : ($dateaction->format('d.m.y') . '</br>' . $dateaction->format('H:i'))) .
                $type_icon . '</td>';
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
            <td '.(empty($row->result_of_action)?' title="Натисніть, аби вказати результат перемовин"':'').' rowid="' . $row->rowid . '" answer_id="' . $row->answer_id . '" id = "' . $row->rowid . 'result_of_action" style="width: 80px; '.(empty($row->result_of_action)?'text-align:right; background: url(/dolibarr/htdocs/theme/eldy/img/hand.png) no-repeat; opacity:0.1':'').'" class="middle_size result_of_action">' . (strlen($row->result_of_action) > 20 ? mb_substr($row->result_of_action, 0, 20, 'UTF-8') . '...' .
                    '<input id="_' . $row->rowid . 'result_of_action" type="hidden" value="' . $row->result_of_action . '">' : $row->result_of_action) . '</td>
            <td rowid="' . $row->rowid . '" id = "' . $row->rowid . 'work_before_the_next_action" style="width: 80px" class="middle_size">' . (strlen($row->work_before_the_next_action) > 20 ? mb_substr($row->work_before_the_next_action, 0, 20, 'UTF-8') . '...' .
                    '<input id="_' . $row->rowid . 'work_before_the_next_action" type="hidden" value="' . $row->work_before_the_next_action . '">' : $row->work_before_the_next_action) . '</td>
            <td rowid="' . $row->rowid . '" id = "' . $row->rowid . 'date_next_action" style="width: 80px" class="middle_size">' . (empty($row->date_next_action) ? '' : $dtNextAction->format('d.m.y H:i:s')) . '</td>
            <td rowid="' . $row->rowid . '" id = "' . $row->rowid . 'work_before_the_next_action_mentor" style="width: 80px" class="middle_size mentor">' . (strlen($row->work_mentor) > 20 ? mb_substr($row->work_mentor, 0, 20, 'UTF-8') . '...' .
                    '<input id="_' . $row->rowid . 'work_before_the_next_action_mentor" type="hidden" value="' . $row->work_mentor . '">' : $row->work_mentor) . '</td>
            <td rowid="' . $row->rowid . '" id = "' . $row->rowid . 'date_next_action_mentor" style="width: 80px" class="middle_size mentor">' . (empty($row->date_mentor) ? '' : $dtDateMentor->format('d.m.y')) . '</td>
            <td rowid="' . $row->rowid . '" id = "' . $row->rowid . 'action" style="width: 35px" class="middle_size"><script>
                 var click_event = "/dolibarr/htdocs/societe/addcontact.php?action=edit&mainmenu=companies&rowid=1";
                </script>';
//        $out.='<img onclick="" style="vertical-align: middle" title="'.$langs->trans('AddSubAction').'" src="/dolibarr/htdocs/theme/eldy/img/Add.png">';
//            echo '<pre>';
//            var_dump($row->author_id!=$user->id,!in_array($region_id->region_id, $user_regions),$row->rowid!=0,(in_array('gen_dir', array($user->respon_alias,$user->respon_alias2))));
//            echo '</pre>';
//            die();
//            echo '<pre>';
//            var_dump($user->rights->user);
//            echo '</pre>';
//            die();
            if($user->rights->user->user->mentor && $mentor ||(in_array('gen_dir', array($user->respon_alias,$user->respon_alias2)))){
                if(empty($row->date_mentor))
                    $out .= '<img id="img_1"  onclick="SetRemarkOfMentor(' .(substr($row->rowid, 0, 1) == '_'?'0, '.substr($row->rowid,1): $row->rowid) . ');" style="vertical-align: middle; cursor: pointer;" title="' . $langs->trans('SetRemarkOfMentor') . '" src="/dolibarr/htdocs/theme/eldy/img/filenew.png">';
                else
                    $out .= '<img onclick="EditMentorRemark(' . (substr($row->rowid, 0, 1) == '_' ? "'" . $row->rowid . "'" : $row->rowid) . ', ' . (empty($row->answer_id) ? '0' : $row->answer_id) . ', ' . "'" . (empty($row->kindaction) ? 'AC_TEL' : $row->kindaction) . "'" . ');" style="vertical-align: middle; cursor: pointer;" title="' . $langs->trans('edit_mentor') . '" src="/dolibarr/htdocs/theme/eldy/img/edit.png">
                    <img onclick="DelMentorRemark(' . (substr($row->rowid, 0, 1) == '_' ? "'" . $row->rowid . "'" : $row->rowid) . ');" style="vertical-align: middle; cursor: pointer;" title="' . $langs->trans('delete_mentor') . '" src="/dolibarr/htdocs/theme/eldy/img/delete.png">';

            }else {
                $out .= '<img id="prev'.$row->rowid.'" onclick="PreviewActionNote('.$row->rowid.');" style="vertical-align: middle; cursor: pointer;" title="Подивитись зміст завдання" src="/dolibarr/htdocs/theme/eldy/img/preview.png">
                <img onclick="EditAction(' . (substr($row->rowid, 0, 1) == '_' ? "'" . $row->rowid . "'" : $row->rowid) . ', ' . (empty($row->answer_id) ? '0' : $row->answer_id) . ', ' . "'" . (empty($row->kindaction) ? 'AC_TEL' : $row->kindaction) . "'" . ');" style="vertical-align: middle; cursor: pointer;" title="' . $langs->trans('Edit') . '" src="/dolibarr/htdocs/theme/eldy/img/edit.png">
                <img onclick="DelAction(' . (substr($row->rowid, 0, 1) == '_' ? "'" . $row->rowid . "'" : $row->rowid) . ');" style="vertical-align: middle; cursor: pointer;" title="' . $langs->trans('delete') . '" src="/dolibarr/htdocs/theme/eldy/img/delete.png">';
            }
            $out .= '</td>
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