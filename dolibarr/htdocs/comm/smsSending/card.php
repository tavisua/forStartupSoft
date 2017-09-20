<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 21.04.2016
 * Time: 18:47
 */

require $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';

$action = $_REQUEST['action'];
//global $user;
//echo '<pre>';
//var_dump($_REQUEST);
//echo '</pre>';
//die();

//die($action);
if($action == 'add') {
    llxHeader('',$langs->trans('NewMailing'));
    print_fiche_titre($langs->trans("NewMailing"));
}elseif($action == 'edit'){
    llxHeader('',$langs->trans('EditMailing'));
    print_fiche_titre($langs->trans("EditMailing"));
}elseif($action == 'getStatus'){
    echo getStatusSending();
    exit();
}elseif($action == 'getCustomers'){
    $result = getCustomers($_REQUEST['type'], !empty($_REQUEST['test']));
    echo $result;
//    echo '1';
    exit();
}elseif($action == 'mailing'){
    echo sending();
    exit();
}elseif($action == 'getAreas'){
    echo $user->getAreasList(0, 'areas', 10, '', '', $_GET['country_id']);
    exit();
}
$userphone='';
if(!empty($user->user_mobile))
    $userphone=$user->user_mobile;
elseif(!empty($user->office_phone))
    $userphone=$user->office_phone;
$userphone = str_replace('+','',$userphone);
$userphone = str_replace('(','',$userphone);
$userphone = str_replace(')','',$userphone);
$userphone = str_replace('-','',$userphone);
$userphone = str_replace(' ','',$userphone);

$form = new Form($db);
if ($_REQUEST['addParam'] == 'addFindParam'){
//    echo '<pre>';
//    var_dump(DOL_DOCUMENT_ROOT.'/theme/'.$conf->theme.substr(str_replace('\\', '/', __DIR__), strlen(DOL_DOCUMENT_ROOT)));
//    var_dump($user->respon_alias, $user->respon_alias2);
//    die();
    for($i=0; $i<2; $i++) {
        $tmp_file = DOL_DOCUMENT_ROOT.'/theme/'.$conf->theme.substr(str_replace('\\', '/', __DIR__), strlen(DOL_DOCUMENT_ROOT)).'/'.array($user->respon_alias, $user->respon_alias2)[$i].'/param.html';
        if(file_exists($tmp_file)) {

            //Визначаю які категорії додатково відкриваються у користувача
            $tmp_sql = "select distinct case when fx_category_counterparty is null then other_category else fx_category_counterparty end cat_id from `responsibility_param`
                    inner join llx_user_responsibility on llx_user_responsibility.fk_respon = `responsibility_param`.fx_responsibility
                    where llx_user_responsibility.fk_user = $user->id
                    and llx_user_responsibility.active = 1
                    or `responsibility_param`.fx_responsibility in ($user->respon_id,$user->respon_id2)";
            $tmp_res = $db->query($tmp_sql);
            $categories = [];
            while ($obj = $db->fetch_object($tmp_res)) {
                if (!in_array($obj->cat_id, $categories))
                    $categories[] = is_numeric($obj->cat_id) ? $obj->cat_id : "'$obj->cat_id'";
            }
            $sql .= ' and llx_societe.`categoryofcustomer_id` in (' . (implode(',', $categories)) . ')';
            $_REQUEST['categories'] = (implode(',', $categories));
            if (count($categories) > 1) {
                $tmp_sql = "select rowid, `name` from  `category_counterparty` where rowid in (" . implode(',', $categories) . ") and active = 1";
                $tmp_res = $db->query($tmp_sql);
                $FindingParam = '<td>Категорії контрагентів</td><td></td><td><select id="category_id" class="combobox" name="category_id">';
                $FindingParam .= '<option value="-1" selected="selected">Відобразити всі</option>';
                while ($obj = $db->fetch_object($tmp_res)) {
                    $FindingParam .= '<option value="' . $obj->rowid . '">' . $obj->name . '</option>';
                }
                $FindingParam .= '</select></td>';
            }
            //Напрямки діяльності для постачання
            $LineActive = $form->selectLineAction([], 'lineaction', 10);
            include $tmp_file;
//            var_dump($html);
//            die();
            break;
        }
    }
//    echo '</pre>';

//    var_dump( $_REQUEST['categories'] );
    exit();
}
include DOL_DOCUMENT_ROOT.'/theme/eldy/comm/sending.html';
exit();
function getStatusSending(){
    global $db,$user;
    $sql="select max(rowid) rowid from llx_smssending where id_usr=".$user->id;
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    $sql = "select status from llx_smssending where rowid=".$obj->rowid;
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    return $obj->status;
}
function sending(){
    global $db, $user;
    //GetLastMessageID
    $sqlMessID = "select rowid from llx_smssending where id_usr = ".$user->id." and status = 0 order by rowid desc limit 1";
    $resMessID = $db->query($sqlMessID);
    if(!$resMessID)
        dol_print_error($db);
    if($db->num_rows($resMessID) == 0) {
        //Save message
        $sql = "insert into llx_smssending(message,status,id_usr)
        values('" . trim($_REQUEST['message']) . "', 0, " . $user->id . ")";
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        $resMessID = $db->query($sqlMessID);
    }
    $obj = $db->fetch_object($resMessID);
    $MessID = $obj->rowid;
    if(isset($_REQUEST['lastpack'])&&$_REQUEST['lastpack']=='true'){
        $sql = 'update llx_smssending set status=1 where rowid='.$MessID;
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
    }
    //SaveContactList
    $out = '';
    foreach($_REQUEST['contacts'] as $contact){
        $phone = $contact['phone'];
        $phone = str_replace('+','',$phone);
        $phone = str_replace('(','',$phone);
        $phone = str_replace(')','',$phone);
        $phone = str_replace('-','',$phone);
        $phone = str_replace(' ','',$phone);
        $out.=$phone.';';
        $sql = 'insert into llx_smssending_target(fk_sending,fk_soc,fk_contact,phone)
          values('.$MessID.','.$contact['socid'].','.$contact['contactID'].','."'".$phone."'".')';
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
    }
    $out=substr($out,0,strlen($out)-1);
    return $out;
}
function getCustomers($type, $test=false){
//    die($type);
    global $db, $user;
//    echo '<pre>';
//    var_dump(strlen($_REQUEST['areas'])==0&&$_REQUEST['country_id']==226, count($_REQUEST["areas"]), strlen($_REQUEST['areas']));
//    echo '</pre>';
//    die();
    if(count($_REQUEST['areas'])==0&&$_REQUEST['country_id']==226)
        die('Необхідно вказати для яких користувачів буде розсилка');
    $sql = '';
    if(!empty($_REQUEST['areas'])&&in_array('workers', $_REQUEST['areas']) && $_REQUEST['areas'][0] == 0){
        $sql = "select llx_user.rowid, 0 socid, `states`.`name` state_name,
                '' as form_gov, '' region_name, 'Техніка і Технології' as nom, '' postname,
                llx_user.lastname, llx_user.firstname, llx_user.email email1, '' email2, llx_user.office_phone mobile_phone1,
                '' mobile_phone2, '' `value`, 1 `active`
          from llx_user
        inner join `subdivision` on `subdivision`.`rowid`=llx_user.subdiv_id
        inner join `states` on `states`.`rowid`= `subdivision`.`state_id`
        where llx_user.active = 1";
        if ($_REQUEST['type'] == 'email')
            $sql.=" and llx_user.email like '%@%'";
        elseif ($_REQUEST['type'] == 'sms')
            $sql.=" and mobile_phone1 is not null";
    }
    $inarea = false;
    if(count($_REQUEST['areas'])>0) {
        if($_REQUEST['areas'][0] != 0||$_REQUEST['country_id']!=226){
            if($_REQUEST['areas']=='')
                $_REQUEST['areas'][0]=0;
            foreach ($_REQUEST['areas'] as $value) {
                if (is_numeric($value)) {
                    $inarea = true;
                    break;
                }
            }
        }else {
            $sql_tmp = 'select fk_id from `llx_user_regions` where fk_user = '.$user->id.' and active = 1';
            $res = $db->query($sql_tmp);
            if(!$res)
                dol_print_error($db);

            while($area = $db->fetch_object($res)){
                $areas[]=$area->fk_id;
            }
            $_REQUEST['areas'] = $areas;
            $inarea = true;
        }
    }elseif($_REQUEST['country_id']!=226){
        $inarea = true;
    }


    if($inarea) {
        if(!empty($sql))
            $sql.=' union ';
        $sql .= 'select `llx_societe_contact`.`rowid`, `llx_societe_contact`.`socid`, `states`.`name` state_name,
        `formofgavernment`.name as form_gov, `regions`.`name` region_name, `llx_societe`.`nom`, `llx_post`.`postname`,
        llx_societe_contact.lastname, llx_societe_contact.firstname, llx_societe_contact.email1, llx_societe_contact.email2, llx_societe_contact.mobile_phone1,
        llx_societe_contact.mobile_phone2, `llx_societe_classificator`.`value`, case when `llx_societe_classificator`.`active` is null then 1 else `llx_societe_classificator`.`active` end `active`
        from llx_societe
        inner join `llx_societe_contact` on `llx_societe_contact`.`socid` = `llx_societe`.`rowid`
        left join `llx_post` on `llx_societe_contact`.`post_id` = `llx_post`.`rowid`
        left join `regions` on `regions`.`rowid` = `llx_societe`.`region_id`
        left join `states` on `states`.`rowid` = `llx_societe`.`state_id`
        left join `formofgavernment` on `formofgavernment`.`rowid` = llx_societe.`formofgoverment_id`';

        if (!empty($_REQUEST["from"]) || !empty($_REQUEST["to"])) {
            $sql .= ' inner join llx_societe_classificator on llx_societe_classificator.soc_id = `llx_societe`.`rowid`';
        } else
            $sql .= ' left join llx_societe_classificator on llx_societe_classificator.soc_id = `llx_societe`.`rowid`';


//    $sql.=' where 1 and fk_user_creat ='.$user->id;
        $sql .= ' where 1';
        if (in_array('sale', array($user->respon_alias, $user->respon_alias2))) {
            $sql .= ' and llx_societe.`categoryofcustomer_id` = 5';
            $FindingParam = '<td>Кількість землі </td>
            <td></td>
            <td colspan="3">від&nbsp;&nbsp;<input type="text" id="from" name="from" size="4">&nbsp;&nbsp;до&nbsp;&nbsp;<input type="text" id="to" name="to" size="4">&nbsp;&nbsp;га.</td>';
        }elseif (count(array_intersect(array('logistika','purchase'), array($user->respon_alias, $user->respon_alias2)))){
            if(isset($_REQUEST['category_id'])&&!empty($_REQUEST['category_id'])){
                $categories = explode(',', $_REQUEST['category_id']);
                if(count($categories)==1)
                    $sql .= ' and llx_societe.`categoryofcustomer_id` = '.$_REQUEST['category_id'];
                else{
                    $filter = '-1';
                    for($i=0; $i<count($categories);$i++) {
                        if(is_numeric($categories[$i])) {
                            $filter.=','.$categories[$i];
                        }
                    }
                    if($filter != '-1'){
                        $sql .= ' and llx_societe.`categoryofcustomer_id` in ('.$filter.')';
                    }
                }
            }
        }elseif (!empty($_REQUEST['country_id'])){
            $sql .= ' and llx_societe.`fk_pays` = '.$_REQUEST['country_id'];
        }
        //Фільтр по напрямкам діяльності
        if(isset($_REQUEST['lineaction']) && !empty($_REQUEST['lineaction'])){
            $lineaction = $_REQUEST['lineaction'];
            $tmp = $_REQUEST['lineaction'];
            while(count($tmp)){
                $tmp_sql = "select category_id from oc_category where parent_id in (".implode(',',$tmp).")";
                $tmp_res = $db->query($tmp_sql);
                $tmp = [];
                while($item = $db->fetch_object($tmp_res)){
                    $tmp[]=$item->category_id;
                }
                if(count($tmp))
                    $lineaction = array_merge($lineaction, $tmp);
                $lineaction = array_unique($lineaction);
            }
            $tmp_sql = "select distinct fk_soc from `llx_societe_lineactive`
                where fk_lineactive in (".implode(',',$lineaction).") and active = 1";
            $tmp_res = $db->query($tmp_sql);
            $tmp = [0];
            while($item = $db->fetch_object($tmp_res)){
                $tmp[]=$item->fk_soc;
            }
            $sql .= ' and llx_societe.rowid in ('.implode(',',$tmp).')';
        }
        if (!isset($_REQUEST['addParam']) || empty($_REQUEST['addParam']))
            $sql .= ' and region_id in (select fk_id from llx_user_regions where fk_user = ' . $user->id . ' and active = 1) ';

        $sql .= ' and llx_societe.active = 1';
        $sql .= ' and `llx_societe_contact`.active = 1';
        if (!(empty($_REQUEST["areas"]) || count($_REQUEST["areas"]) == 1 && $_REQUEST["areas"][0] == 0))
            $sql .= ' and region_id in (' . implode(',', $_REQUEST["areas"]) . ')';
        $add = false;
        if (!(empty($_REQUEST["postlist"]) || count($_REQUEST["postlist"]) == 1 && $_REQUEST["postlist"][0] == 0) &&
            !(empty($_REQUEST["responsibility"]) || count($_REQUEST["responsibility"]) == 1 && $_REQUEST["responsibility"][0] == 0)
        ) {
            $sql .= ' and(';
            $add = true;
        }
        if (!(empty($_REQUEST["postlist"]) || count($_REQUEST["postlist"]) == 1 && $_REQUEST["postlist"][0] == 0))
            $sql .= ($add ? '' : ' and') . ' `llx_societe_contact`.`post_id` in (' . implode(',', $_REQUEST["postlist"]) . ')';
        if (!(empty($_REQUEST["responsibility"]) || count($_REQUEST["responsibility"]) == 1 && $_REQUEST["responsibility"][0] == 0))
            $sql .= ($add ? ' or' : ' and') . ' `llx_societe_contact`.`respon_id` in (' . implode(',', $_REQUEST["responsibility"]) . ')';
        if (!(empty($_REQUEST["postlist"]) || count($_REQUEST["postlist"]) == 1 && $_REQUEST["postlist"][0] == 0) &&
            !(empty($_REQUEST["responsibility"]) || count($_REQUEST["responsibility"]) == 1 && $_REQUEST["responsibility"][0] == 0)
        )
            $sql .= ')';

        if (!empty($_REQUEST["from"]) && !empty($_REQUEST["to"])) {
            $sql .= ' and llx_societe_classificator.value between ' . $_REQUEST["from"] . ' and ' . $_REQUEST["to"];
        } elseif (!empty($_REQUEST["from"])) {
            $sql .= ' and llx_societe_classificator.value >= ' . $_REQUEST["from"];
        } elseif (!empty($_REQUEST["to"])) {
            $sql .= ' and llx_societe_classificator.value <= ' . $_REQUEST["to"];
        }
        if ($_REQUEST['type'] == 'sms')
            $sql .= ' and (call_mobile_phone1 = 1 or call_mobile_phone2 = 1)';
        elseif ($_REQUEST['type'] == 'email')
            $sql .= ' and (send_email1 = 1 or send_email2 = 1)';

//    $sql .=' and `llx_societe_classificator`.`active` = 1';
        $sql .= ' order by state_name, region_name, nom, lastname';
    }
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();
    if($test){
        if(!empty($sql))
            $sql.=' union ';
        $sql .= "select llx_societe.rowid, llx_societe.nom, llx_societe_contact.email1, llx_societe_contact.email2 from `llx_societe_contact`
            inner join llx_societe on llx_societe.rowid = `llx_societe_contact`.`socid`
            where email1 like '%t-i-t%'";
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
        $emaillist = [];
        while($obj = $db->fetch_object($res)){
            if(!empty($obj->email1)&&empty($emaillist[$obj->email1])){
                $emaillist[$obj->email1]=array('nom'=>$obj->nom,'rowid'=>$obj->rowid);
            }
            if(!empty($obj->email2)&&empty($emaillist[$obj->email2])){
                $emaillist[$obj->email2]=array('nom'=>$obj->nom,'rowid'=>$obj->rowid);
            }
        }
        $sql = '';
        $sql.=" select 1 active, 0 as rowid, 'admin' as nom, 'tavis.ua@gmail.com' email1, 'mikhailov_viktor@mail.ru' email2";
//        foreach ($emaillist as $key=>$value){
//            if(!empty($sql))
//                $sql.=' union ';
//            $sql.=" select 1 active, ".$value['rowid']." as rowid, '".$value['nom']."' as nom, '$key' email1, '' email2";
//        }
    }
    if(empty($sql))
        die('Необхідно вказати для яких користувачів буде розсилка');
    $out = '';
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $num = 1;
    $contact_ids = [];
    while($obj = $db->fetch_object($res)){
        if($obj->active && !in_array($obj->rowid, $contact_ids)) {
            $contact_ids[]=$obj->rowid;
            $value = '';
            if($type == 'sms') {
                if (empty($obj->mobile_phone1))
                    $value = $obj->mobile_phone2;
                else $value = $obj->mobile_phone1;
                $value = str_replace(' ', '', $value);
            }elseif($type == 'email'){
                if (empty($obj->email1))
                    $value = $obj->email2;
                else $value = $obj->email1;
            }
//            var_dump($type, $value, $obj->email1, $obj->email2, !empty($value));
//            die();
            if (!empty($value)) {
                $class = fmod($num, 2) == 0 ? 'impair' : 'pair';
                $out .= '<tr id = "' . $obj->rowid . '" socid="' . $obj->socid . '" class="secondpage ' . $class . '">
            <td class="middle_size">' . $num++ . '&nbsp;</td>
            <td class="middle_size">' . trim($obj->region_name) . ' (' . decrease_word($obj->state_name) . ')</td>
            <td class="middle_size">' . trim($obj->form_gov) . ' "' . trim($obj->nom) . '"</td>
            <td class="middle_size">' . trim($obj->lastname) . '</td>
            <td class="middle_size">' . trim($obj->postname) . '</td>
            <td class="middle_size" style="white-space: nowrap;">' . round($obj->value) . ' га. </td>';
                $out .= '<td class="middle_size" style="word-wrap: normal">' . $value . '</td></tr>';
            }
        }
    }

//    echo '<pre>';
//    var_dump(htmlspecialchars($out));
//    echo '</pre>';
//    die();
    return $out;
}