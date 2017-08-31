<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 31.10.2016
 * Time: 5:56
 */

if(!empty($_REQUEST['action']) && in_array($_REQUEST['action'], array('check', 'sendmail')))
    define("NOLOGIN",1);// This means this output page does not require to be logged.
require $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/main.inc.php';
global $db,$user;
$SendActionType= ['night','after_phone','before_birsthday5'];
//echo '<pre>';
//var_dump($_REQUEST);
//echo '</pre>';
//die();

$action = empty($_POST['action'])?$_REQUEST['action']:$_POST['action'];
//var_dump($action, $_POST['action'], $_REQUEST['action']);
//die();
if($action == 'testmails'){
    if(!empty($_REQUEST['rowid']))
        $sql = "update llx_c_testmails set mail = '".$_REQUEST['email']."', active = ".($_REQUEST["active"]=='true'?1:0).", dateup = now(), id_usr = $user->id where rowid = ".$_REQUEST['rowid'];
    else
        $sql = "insert into llx_c_testmails(mail,active,id_usr)values('".$_REQUEST['email']."',".($_REQUEST["active"]=='true'?1:0).",$user->id)";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    else
        die('1');
}
if($action == 'show_testmails'){
    $title = "Список тестових email";
    llxHeader("",$title,"");
    $backtopage = $_SERVER['REQUEST_URI'];
    $sql = "select * from llx_c_testmails where active = 1";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $table = '<tbody>';
    while($obj = $db->fetch_object($res)){

        $table.='<tr id="'.$obj->rowid.'"><td id="'.$obj->rowid.'_mail">'.$obj->mail.'</td>
            <td>
                <img onclick="EditTestMail('.$obj->rowid.')" rowid="'.$obj->rowid.'" style="vertical-align: middle; cursor: pointer;" title="' . $langs->trans('Edit') . '" src="/dolibarr/htdocs/theme/eldy/img/edit.png">
                <img onclick="DeleteTestMail('.$obj->rowid.')" rowid="'.$obj->rowid.'" style="vertical-align: middle; cursor: pointer;" title="' . $langs->trans('Edit') . '" src="/dolibarr/htdocs/theme/eldy/img/delete.png">
            </td>';
    }
    $table.= '</tbody>';

    include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/mailing/testmails.html');
    exit();
}
if($action == 'check'){
//    define("NOLOGIN",1);		// This means this output page does not require to be logged.
    $sql = "select rowid
    from llx_mailing where 1 
    and date_valid is not null    
    and date_send is null 
    and statut = 1    
    and date_format(date_add(date_format(date_valid, '%Y-%m-%d %H:%i'), interval 2 hour), '%Y-%m-%d %H:%i')  <= '".date('Y-m-d H:i')."'";
//    phpinfo();
//    die($sql);
    $res = $db->query($sql);
//    var_dump();
//    die();
    if($res->num_rows == 0)
        die('0');
    if(!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)){
        AutoSendMail($obj->rowid);
    }
    die('1');
}

if(in_array($action, array('update','insert'))) {
    if(!empty($_REQUEST['sendactiontype'])){
        $begin_period = "'".$_REQUEST['beginyear'].'-'.$_REQUEST['beginmonth'].'-'.$_REQUEST['beginday']."'";
        $end_period = "'".$_REQUEST['endyear'].'-'.$_REQUEST['endmonth'].'-'.$_REQUEST['endday']."'";
    }else{
        $begin_period = 'null'; 
        $end_period = 'null'; 
    }
}
//die($action);
if($action == 'sendmail'){

    require_once $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/cron/class/cronjob.class.php';
    $CronJob = new Cronjob($db);
    $CronJob->setStartCronStatus($_REQUEST['type']);
    set_time_limit(0);
    $out = array();
    if(isset($_REQUEST['id'])&&!empty($_REQUEST['id'])) {
        $sql = "select titre,body,postlist, responsibility from llx_mailing where rowid = " . $_REQUEST['id'];
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        $mess = $db->fetch_object($res);
        $subject = $mess->titre;
        $msgishtml = $mess->body;
//    $out['subject'] = $subject;
//    $out['mesg'] = $msgishtml;
        $mesg = $mess->body;
    }
//    $msgishtml='';
    $conf->notification->email_from=$conf->mailing->email_from;
    switch ($_REQUEST['type']){
        case 'test':{
            AutoSendMail($_REQUEST['id'],$_REQUEST['type']);
            exit();
//            $email = $user->email;
////            var_dump($user->email);
////            exit;
//            require_once DOL_DOCUMENT_ROOT.'/core/class/CMailFile.class.php';
//            $mailfile = new CMailFile(
//                $subject,
//                $email,
//                $user->lastname." ".mb_substr($user->firstname,0,1,'UTF-8').".<$user->email>",
//                $mesg,
//                array(),
//                array(),
//                array(),
//                '',
//                '',
//                0,
//                $msgishtml
//            );
////           $mailfile->smtps->_smtpsHost ='mail.shtorm.com';
//
////            echo '<pre>';
////            var_dump($mailfile->smtps->_smtpsHost);
////            echo '</pre>';
////            die();
//            $mailfile->sendfile();
//            /* получатели */
////            include_once '/dolibarr/htdocs/includes/nusoap/lib/Mail/smtp.php';
////            $params['host']='mail.shtorm.com';
////            $params['port']=25;
////            $params['auth']='true';
////            $params['username']='tavis';
////            $params['password']='pluton';
//////            $params['127.0.0.1'];
//////            $params['timeout'];
//////            $params['verp'];
//////            $params['debug'];
////
////            $smtp = new Mail_smtp($params);
////            $smtp->send();
        }break;
        default:{
            $mail_id = array();
            if(isset($_REQUEST['id'])&&!empty($_REQUEST['id']))
                $mail_id[]=$_REQUEST['id'];
            else{
                global $db;
                $sql = "select rowid from llx_mailing where 1 and statut=1 and date_valid is not null ";
                switch ($_REQUEST['type']) {
                    case 'after_phone':{
                        $sql.=" and send_after = 1 
                                    and now() between period_begin and period_end ";
                    }break;
                    case 'before_birsthday5':{
                        $sql.=" and send_after = 2 
                                    and now() between period_begin and period_end ";
                    }break;
                    default: {
                        $sql.=" and date_send is null and (send_after in (0,null))";
                    }
                }
                $res = $db->query($sql);
                if(!$res)
                    return 0;
                else{
                    while($obj = $db->fetch_object($res)){
                        $mail_id[] = $obj->rowid;
                    }
                }
            }
            foreach ($mail_id as $rowid) {
                AutoSendMail($rowid, $_REQUEST['type'], $_REQUEST["contactID"]);
            }
            return 1;
        }break;
        case 'prepared_sendmail':{
            define("NOLOGIN",1);// This means this output page does not require to be logged.
            //Роблю виборку контактів, які відповідають параметрам розсилки
            $societelist = PreparedEmailList($mess);
//            echo '<pre>';
//            var_dump($societelist["societelist"]);
//            echo '</pre>';
//            die();
            if(count($societelist) == 0)
                die(0);
            //Роблю виборку регіонів, для яких буде розіслана розсилка
            $sql = "select `region_id`, `state_id` from `llx_societe` where rowid in(".implode(',',$societelist["societelist"]).") and active = 1";
//            die($sql);

            $res = $db->query($sql);
            if(!$res)
                dol_print_error($db);
            $regions = array();
            while($obj = $db->fetch_object($res)){
                if(!empty($obj->region_id)&&!in_array($obj->region_id, $regions) /*&& $obj->state_id == 16*/)
                    $regions[] = $obj->region_id;
            }
            //Роблю виборку торгівельних агентів, для регіонів яких буде виконана розсилка
            $sql = "select distinct `llx_user`.rowid from `llx_user_regions`, `llx_user`
                where fk_id in (".implode(',',$regions).")
                and `llx_user_regions`.active = 1
                and `llx_user_regions`.fk_user = `llx_user`.rowid
                and `llx_user`.active = 1";
//            die($sql);
            $res = $db->query($sql);
            if(!$res)
                dol_print_error($db);
            $userslist = array();
            while($obj = $db->fetch_object($res)){
                $userslist[] = $obj->rowid;
//                break;
            }
//            $out['userlist']=$userslist;
            die(json_encode($userslist));
//            echo '<pre>';
//            var_dump($out);
//            echo '</pre>';
        }break;
    }
    $CronJob->setExecCronStatus($_REQUEST['type']);
    die('1');
}
if($action == 'createtask') {//Створюю завдання перевіряючому розсилку
//    global $db,$user;
    $sql = "select id from llx_actioncomm where subaction = 'validate' and subaction_id = ".$_REQUEST["mailid"]." and active = 1";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    if(empty($obj->id)) {
        require_once DOL_DOCUMENT_ROOT . '/comm/action/class/actioncomm.class.php';
        $action = new ActionComm($db);
        $exec_minuted = $action->GetExecTime('AC_CURRENT');
        $freetime = $action->GetFreeTime(date('Y-m-d'), $_REQUEST['valider_id'], $exec_minuted, 0);
        $date = new DateTime($freetime);
        $action->datep = mktime($date->format('h'), $date->format('i'), $date->format('s'), $date->format('m'), $date->format('d'), $date->format('Y'));
        $action->datef = $action->datep + $exec_minuted * 60;
        $action->type_code = 'AC_CURRENT';
        $action->order_id = $order_id;
        $action->label = "Перевірити розсилку";
        $action->period = 0;
        $action->percentage = -1;
        $action->priority = 0;
        $action->authorid = $user->id;
        $action->subaction = "validate";
        $action->subaction_id = $_REQUEST["mailid"];
        $action->note = 'Перевірити розсилку';
        $action->userassigned[] = array("id" => $user->id, "transparency" => 1);
        $action->userassigned[] = array("id" => $_REQUEST['valider_id'], "transparency" => 1);
        $action->userownerid = 1;
        $action->fk_element = "";
        $action->elementtype = "";
        $action->add($user);
        die('1');
    }else{
        die('0');
    }

}
if($action == 'showvalidaters'){//Виборка перевіряючих
    $validaters = $_POST['validaters'];
    $sql = 'select rowid, lastname, firstname from llx_user where rowid in ('.implode(',', $validaters).') and active = 1';
    $res = $db->query($sql);
    $out = '<a class="close" onclick="ClosePreviewTask()" title="Закрити"></a><table><thead >
        <tr class="middle_size multiple_header_table"><th>Прізвище перевіряючого</th></tr>        
        </thead>';
    $numrow=0;
    while($obj = $db->fetch_object($res)){
        $class = fmod($numrow++,2)==0?'impair':'pair';
        $out.='<tr id="'.$obj->rowid.'" class="'.$class.'">
            <td class="middle_size" style="cursor:pointer" onclick="SendToValider(['.$obj->rowid.', '.$_REQUEST['mail_id'].'])">'.trim($obj->lastname).'</td>
            </tr>';
    }
    die($out);
}
if($action == 'sendtovalider'){
    if(empty($_POST['valider_id'])){//Перевіряю хто може виконати завдання
        $sql = "select fk_user from `llx_user_rights` where fk_id = 223";
        $res = $db->query($sql);
        if($res->num_rows == 1){
            echo $db->fetch_object($res)->fk_user;
        }else{
            $out = '[';
            while($obj = $db->fetch_object($res)){
                $out.=$obj->fk_user.',';
            }
            $out=substr($out,0,strlen($out)-1).']';
            echo $out;
        }
    }
    die();
}
if($action == 'EditMail') {
    print '<div style="z-index: 10; position: fixed;margin-left: 50%"><a class="close"  onclick="ClosePreviewTask();" title="Закрити"></a></div><div>';
    print htmlspecialchars_decode($_REQUEST['html']);
    print '</div>';
    die();
}
if($action == 'htmlspecialchars_decode'){
    print '<div style="z-index: 10; position: fixed;margin-left: 50%"><a class="close"  onclick="ClosePreviewTask();" title="Закрити"></a></div><div>';
    $body = htmlspecialchars_decode($_REQUEST['html']);
    print str_replace('contactname', $user->firstname,$body);
    print '</div>';
    die();
}
if($action == 'insert'){
//    echo '<pre>';
//    var_dump($_REQUEST);
//    echo '</pre>';
//    die();
    if(!empty($_REQUEST['postlist']))
        $postlist = implode(',',$_REQUEST['postlist']);
    if(!empty($_REQUEST['responsibility']))
        $responsibility = implode(',',$_REQUEST['responsibility']);
    $sql = "insert into llx_mailing(statut,titre,body,fk_user_creat,`postlist`,`responsibility`,`inner`, `send_after`,`period_begin`,`period_end`)
      values(1, '".$db->escape($_REQUEST['theme'])."','".$db->escape($_REQUEST['body'])."',$user->id, 
      '".$postlist."', '".$responsibility."', ".(empty($_REQUEST['inner'])?0:$_REQUEST['inner']).", ".(empty($_REQUEST['sendactiontype'])?0:$_REQUEST['sendactiontype']).", $begin_period, $end_period)";
//    die($sql);
//    echo '</pre>';
//    header($_REQUEST['backtopage']);
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    header("Location: ".$_REQUEST['backtopage']);
    die();
}
//die($action);
if($action == 'delete'){
    $sql = "update llx_mailing 
          set statut = 0, fk_user_creat='" . $user->id . "' where rowid=" . $_REQUEST['rowid'];
    $res = $db->query($sql);
    if (!$res)
        dol_print_error($db);
    header("Location: " . $_SERVER["PHP_SELF"]);
    die();
}
if($action == 'update'){
    if(!empty($_REQUEST['postlist']))
        $postlist = implode(',',$_REQUEST['postlist']);
    if(!empty($_REQUEST['responsibility']))
        $responsibility = implode(',',$_REQUEST['responsibility']);
    $body = $db->escape($_REQUEST['body']);
//    $body = str_replace("'",'&#039;',$body);//Заміна одинарних кавичок
//    $body = str_replace('"','&quot;',$body);//Заміна двойних кавичок

    $sql = "update llx_mailing     
    set titre = '".$_REQUEST['theme']."', body='".$body."', send_after = ".(empty($_REQUEST['sendactiontype'])?0:$_REQUEST['sendactiontype']).
        ", period_begin=".$begin_period.", period_end=".$end_period.", fk_user_creat='".$user->id."', `postlist`='".$postlist."', 
        `responsibility`='".$responsibility."', `inner`=".(empty($_REQUEST['inner'])?0:$_REQUEST['inner'])." where rowid=".$_REQUEST['rowid'];
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);

    header("Location: ".$_SERVER["PHP_SELF"]);
    die();
}
if($action == 'edit'){
    $title = "Редагувати розсилку";
    llxHeader("",$title,"");
    $action = 'update';
    $sql = 'select titre,body,`postlist`,`responsibility`, `inner`, `send_after`,`period_begin`,`period_end` from llx_mailing where rowid = '.$_GET["rowid"];
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $mail = $db->fetch_object($res);
    $rowid = $_GET["rowid"];
    $theme = $mail->titre;
    $html = $mail->body;
//    var_dump($mail->inner);
//    die();
    if($mail->inner == 1)
        $mail->inner = 'checked = "checked"';
    else
        $mail->inner = "";
//    die($mail->inner);
    $begin = new DateTime('01'.date('.m.Y'));
    $unixtime = time();
    $end = new DateTime(date('t',$unixtime).date('.m.Y'));
    $backtopage = $_SERVER['REQUEST_URI'];
    if(!empty($mail->send_after)){
        if(empty($mail->period_begin)){
            $begin = "";
        }else{
            $begin = new DateTime($mail->period_begin);
        }
        if(empty($mail->period_end)){
            $end = "";
        }else{
            $end = new DateTime($mail->period_end);
        }        
    }else{
        $begin = null;
        $end = null;
    }
    $select = "<select id='sendactiontype' name='sendactiontype' class='combobox' onchange='setactiontype()'>";
    foreach ($SendActionType as $key=>$value){
        $select.="<option value='$key'".($mail->send_after == $key?"selected":"").">".$langs->trans($value)."</option>";
    }
    $select.="</select>";
//    echo '<pre>';
//    var_dump($SendActionType);
//    echo '</pre>';
//    die();
    include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/mailing/card.html');
    die();
}
if($action == 'create'){
    $title = "Створити розсилку";
    llxHeader("",$title,"");
    $action = 'insert';
    $backtopage = $_SERVER['REQUEST_URI'];
    include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/mailing/card.html');
    die();
}
//if($action == 'autosendmail'){
//    global $db,$user;
//    $sql = "select rowid, titre, body  from `llx_mailing`
//        where send_after_phone is not null
//        and now() between send_phone_period_begin and send_phone_period_end";
//    $res = $db->query($sql);
//    $mail = CreateSMTPMailer();
//
//    while($obj = $db->fetch_object($res)){
//        $sql = "select rowid from llx_mailing_cibles where fk_mailing = ".$obj->rowid." and fk_contact = ".$_REQUEST['contactid']."limit 1";
//        $mail_res = $db->query($sql);
//        if(!$mail_res->num_rows){
//            //Визначаю контактний мейл торгівельного
//            $sql = "select email, pass from `subdivision` where rowid = 7 and active = 1";
//
//
//        }
//    }
//    echo '<pre>';
//    var_dump($user);
//    echo '</pre>';
//    die();
//    echo 0;
//    die();
//}
//die($action);

//echo '<pre>';
//var_dump($_REQUEST['action']);
//echo '</pre>';
//die();

$title = "Розсилка";
llxHeader("",$title,"");
print_fiche_titre($title);
llxLoadingForm();
$table = MailingList();
llxPopupMenu();
if(empty($conf->theme))
    $conf->theme='eldy';
//var_dump(, $user->conf->MAIN_THEME);
//die();

include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/mailing/index.html');
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/static_content/layout/pagination.phtml';
exit();

function PreparedEmailList($mess, $states_id = array()){
    global $db;
    $postlist = $mess->postlist;
    $responsibility = $mess->responsibility;
    $sql = "select socid, email1, email2 from `llx_societe_contact`
                    where 1 ";
    if(!empty($postlist)&&!empty($responsibility))
        $sql .= " and (post_id in (".$postlist.") or `respon_id` in (".$responsibility."))";
    elseif (!empty($postlist))
        $sql .= " and post_id in (".$postlist.")";
    elseif (!empty($responsibility))
        $sql .= " and respon_id in (".$responsibility.")";
    $sql.=" and ((email1 like '%@%' and send_email1 = 1) or (email2 like '%@%' and send_email2 = 1))
                    and active = 1";

    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $societelist = array();
    $emaillist = array();
    while($obj = $db->fetch_object($res)){
        $add = false;
        if(strpos($obj->email1, '@')) {
            $emaillist[] = $obj->email1;
            $add = true;
        }elseif (strpos($obj->email2, '@')) {
            $emaillist[] = $obj->email2;
            $add = true;
        }
        if($add)
            $societelist[]=$obj->socid;
    }

    return array('societelist'=>$societelist, 'emaillist'=>$emaillist);
}
function CreateSMTPMailer($email="shop@t-i-t.com.ua", $pass="123qaz"){
    require_once DOL_DOCUMENT_ROOT.'/core/class/mailing/PHPMailerAutoload.php';
    date_default_timezone_set('Etc/UTC');
//Create a new PHPMailer instance
    $mail = new PHPMailer;

//Tell PHPMailer to use SMTP
    $mail->isSMTP();

//Enable SMTP debugging
// 0 = off (for production use)
// 1 = client messages
// 2 = client and server messages
    $mail->SMTPDebug = 0;

//Ask for HTML-friendly debug output
    $mail->Debugoutput = 'html';

//Set the hostname of the mail server
    $mail->Host = 'zmx6.vps.cloud.net.ua';
//    $mail->Host = 'server.uspex2015.com.ua';
// use
// $mail->Host = gethostbyname('smtp.gmail.com');
// if your network does not support SMTP over IPv6

//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
    $mail->Port = 587;

    $mail->CharSet = 'utf-8';

//Set the encryption system to use - ssl (deprecated) or tls
    $mail->SMTPSecure = 'tls';

//Whether to use SMTP authentication
    $mail->SMTPAuth = true;

//Username to use for SMTP authentication - use full email address for gmail
    $mail->Username = $email;
//    $mail->Username = "shop@uspex2015.com.ua";

//Password to use for SMTP authentication
    $mail->Password = $pass;
//    $mail->Password = "N7x6EnqV";

//Set who the message is to be sent from
    $mail->setFrom($email, 'Техніка і технології');
//    $mail->setFrom('shop@uspex2015.com.ua', 'Техніка і технології');
    return $mail;
}
function AutoSendMail($rowid, $type='', $contact_id=0){
//    $string = array('name'=>"%C2%B3%EA%F2%EE%F0");
//

    if($rowid == 0)
        return 1;
    global $db,$langs,$user,$conf;
    $sql = "select titre,body, postlist, responsibility, `inner` from llx_mailing where rowid = ".$rowid;
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $mess = $db->fetch_object($res);
    $subject =  $mess->titre;
    $msgishtml = $mess->body;
    $mesg = $mess->body;
    $conf->notification->email_from=$conf->mailing->email_from;
    $postlist = $mess->postlist;
    $responsibility = $mess->responsibility;

    $sql = "select rtrim(email) email from llx_mailing_cibles where fk_mailing = " . $rowid;
    $res = $db->query($sql);
    if (!$res)
        dol_print_error($db);
    $postedlist = [];
    while ($obj = $db->fetch_object($res)) {
        $postedlist[] = mb_strtolower($obj->email, 'utf-8');
    }

//    var_dump(empty($mess->inner));
//    die('test');
    if(empty($mess->inner)) {
        $sql = "select llx_societe.state_id, socid, email1, email2 from `llx_societe_contact`
            left join llx_societe on llx_societe.rowid = `llx_societe_contact`.socid
                    where 1 ";
        if (!empty($postlist) && !empty($responsibility))
            $sql .= " and (post_id in (" . $postlist . ") or `respon_id` in (" . $responsibility . "))";
        elseif (!empty($postlist))
            $sql .= " and post_id in (" . $postlist . ")";
        elseif (!empty($responsibility))
            $sql .= " and respon_id in (" . $responsibility . ")";
        if($type == 'before_birsthday5'){//Поздоровлення за 5 днів до Д.Н.
            $sql .= " and date(concat(year(now()), '-', month(`birthdaydate`), '-', day(`birthdaydate`))) = adddate(date(now()), interval 5 day)";
        }elseif ($type == 'after_phone'){
            $sql.= " and `llx_societe_contact`.`rowid` = ".$contact_id;
        }else {
            $sql .= " and ((email1 like '%@%' and send_email1 = 1) or (email2 like '%@%' and send_email2 = 1))
            and `llx_societe_contact`.active = 1 and `llx_societe`.active = 1";
        }
    }else{
        $user_id = array();
        if(!empty($postlist)){
            $sql = "select rowid from llx_user where post_id in ($postlist) and active = 1";
            $res = $db->query($sql);
            if(!$res)
                dol_print_error($db);
            while($obj = $db->fetch_object($res)){
                if(!in_array($obj->rowid, $user_id))
                    $user_id[] = $obj->rowid;
            }
        }
        if(!empty($responsibility)){
            $sql = "select rowid from llx_user where (respon_id in ($responsibility) or respon_id2 in ($responsibility)) and active = 1
                    union
                    select fk_user from `llx_user_responsibility` where fk_respon in ($responsibility) and active = 1";
            $res = $db->query($sql);
            if(!$res)
                dol_print_error($db);
            while($obj = $db->fetch_object($res)){
                if(!in_array($obj->rowid, $user_id))
                    $user_id[] = $obj->rowid;
            }
        }
        $sql = "select email email1, '' email2 from llx_user where rowid in (".implode(',',$user_id).")";

    }
//    $sql.=" and llx_societe.state_id in (11,17)";
//    $sql.=" order by llx_societe.state_id";

    set_time_limit(0);
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
//    $societelist = array();
    $emaillist = array();
    if($type == 'test') {
        $sql = 'select mail from llx_c_testmails where active = 1';
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        while ($obj = $db->fetch_object($res)) {
            $emaillist[0][] = mb_strtolower($obj->mail, 'utf-8');
        }
    }else if ($type == 'control'){
        $sql = "select llx_societe_contact.lastname, `llx_societe`.state_id, llx_societe_contact.firstname, llx_societe_contact.email1, llx_societe_contact.email2 from llx_societe
            left join `llx_societe_contact` on `llx_societe_contact`.`socid` = `llx_societe`.`rowid`
            where nom like '%контроль%'";
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
        while ($obj = $db->fetch_object($res)) {
            if(!empty($obj->email1))
                $emaillist[$obj->state_id][] = mb_strtolower($obj->llx_societe_contact.lastname.'<'.$obj->email1.'>', 'utf-8');
            if(!empty($obj->email2))
                $emaillist[$obj->state_id][] = mb_strtolower($obj->llx_societe_contact.lastname.'<'.$obj->email2.'>', 'utf-8');
        }
    }else {

        while ($obj = $db->fetch_object($res)) {
            $add = false;
            if (empty($emaillist[$obj->state_id]))
                $emaillist[$obj->state_id] = array();
            if (!empty($obj->email1) && strpos($obj->email1, '@') && (!empty($postedlist) ? !in_array($obj->email1, $postedlist) : true) &&
                (!empty($emaillist[$obj->state_id]) ? !in_array($obj->email1, $emaillist[$obj->state_id]) : true)
            ) {
                $emaillist[$obj->state_id][] = $obj->email1;
                $add = true;
            } elseif (!empty($obj->email2) && strpos($obj->email2, '@') && (!empty($postedlist) ? !in_array($obj->email2, $postedlist) : true) &&
                (!empty($emaillist[$obj->state_id]) ? !in_array($obj->email2, $emaillist[$obj->state_id]) : true)
            ) {
                $emaillist[$obj->state_id][] = $obj->email2;
                $add = true;
            }
//        if($add)
//            $societelist[]=$obj->socid;
        }
    }
//    unset($emaillist);
//    $emaillist[12][]='tavis@shtorm.com';
//    $emaillist[12][]='tavis.ua@gmail.com';
//    $emaillist[12][]='mikhailv_viktor@mail.ru';
//    $emaillist[12][]='veravikt@ukr.net';
//    var_dump(base64_encode('shop@t-i-t.com.ua'));
//    die();
//    die(DOL_DOCUMENT_ROOT.'/core/class/mailings/PHPMailerAutoload.php');
    $mail = CreateSMTPMailer();
//Set the subject line
    $mail->Subject = $mess->titre;
    if ($type == 'after_phone' && $mail->Username == 'shop@t-i-t.com.ua'){
        $sql = "select email, pass from subdivision where state_id = ".array_keys($emaillist)[0].' and email is not null and pass is not null';
        $res = $db->query($sql);


        if($res->num_rows){
            $obj = $db->fetch_object($res);
            $mail->Username = $obj->email;
            $mail->Password = $obj->pass;
        }
    }
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
//$mail->msgHTML(file_get_contents('test.html'), dirname(__FILE__));
    $mail->msgHTML($mess->body);
//$mail->msgHTML(substr($mess->body, 0, strpos($mess->body,'<div class="block bt-picture-text block-menu-processed">')).'  List-unsubscribe: <a href="t-i-t.com.ua">Отписаться</a>');

//Replace the plain text body with one created manually
    $mail->AltBody = $mess->titre;
//    if (!$mail->send()) {
//        echo "Mailer Error: " . $mail->ErrorInfo;
//    } else {
//        echo "Message sent!";
//    }
//    die();
//        echo '<pre>';
//        var_dump($emaillist);
//        echo '</pre>';
//        die();

    $num = 0;
    foreach ($emaillist as $key=>$value){

        foreach ($value as $item) {
            if(!in_array(strtolower(trim($item)), $postedlist)) {
                $mail->clearAddresses();
                $mail->addAddress($item,'');
                if($type != 'test') {
                    $sql = "insert into `llx_mailing_cibles`(fk_mailing,email,date_envoi) values(" . $rowid . ",'" . $item . "',now())";
                    $res = $db->query($sql);
                }

                if (!$res&&$type != 'test') {
                    dol_print_error($db);
                }else {
                    $postedlist[]=mb_strtolower(trim($item),'utf-8');
                    $result = false;
//                    die($_REQUEST['type']);
//                    if(in_array($_REQUEST['type'], array('sendmails', 'control'))) {
                        if (!$mail->send()) {// отправляем письмо
                            $result = false;
                            echo "Mailer Error: " . $mail->ErrorInfo.$item;
                        } else {
                            $result = true;
                            echo "Message sent to $item!</br>";
                        }
//                        if($result)
//                            echo 'Вдало '.$item.'</br>';
//                        else
//                            echo 'Не вдало '.$item.'</br>';
//                    }
//                    $result = false;
                    if (!$result) {
                        $sql = "update llx_mailing_cibles set statut = -1 where rowid = " . $db->last_insert_id(MAIN_DB_PREFIX . "mailing_cibles");
                        $res = $db->query($sql);
                        if (!$res) {
                            dol_print_error($db);
                        }
                    }else{
                        $num++;
                        if($num == 50){
                            set_time_limit(0);
                            $num=0;
                            sleep(6);
                        }
                    }
                }
            }
        }
    }
    if($_REQUEST['type'] == 'sendmails') {
        $sql = "update `llx_mailing` set date_send = now() where rowid = " . $rowid;
        $res = $db->query($sql);
        if (!$res)
            dol_print_error($db);
    }
}
function MailingList(){
    global $db,$langs,$user,$SendActionType;

    $sql = "select rowid,titre,body,date_creat,date_valid,date_send,fk_user_creat,fk_user_valid,send_after,period_begin,period_end from `llx_mailing`
        where statut = 1
        order by date_creat desc";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $out = '<tbody>';
    $i = 0;
    while($obj = $db->fetch_object($res)){

// style="min-width:80px"
// style="min-width:50px"
        $class = fmod($i, 2) != 1 ? ("impair") : ("pair");

        $out.='<tr id="'.$obj->rowid.'" class='.$class.'>';
        $date_creat = new DateTime($obj->date_creat);
        $out.='<td id="titre_'.$obj->rowid.'" style="min-width:180px" class="middle_size">'.mb_substr($obj->titre,0,25,'UTF-8').(mb_strlen($obj->titre, 'UTF-8')>25?'...':'').'</td>';
        $out.='<td id="fulltitre_'.$obj->rowid.'" style="display:none">'.(mb_strlen($obj->titre, 'UTF-8')>25?$obj->titre:'').'</td>';
        $out.='<td id="body_'.$obj->rowid.'" style="display: none"  class="middle_size">'.htmlspecialchars($obj->body).'</td>';
        $out.='<td style="min-width:121px" class="middle_size">'.$date_creat->format('d.m.y H:i').'</td>';
        $date_valid = new DateTime($obj->date_valid);
        $out.='<td style="min-width:121px" class="middle_size">'.(!empty($obj->date_valid)?$date_valid->format('d.m.y H:i'):'&nbsp;&nbsp;&nbsp;').'</td>';
        $date_send = new DateTime($obj->date_send);
        $out.='<td style="min-width:121px" class="middle_size">'.(!empty($obj->date_send)?$date_send->format('d.m.y H:i'):'&nbsp;&nbsp;&nbsp;').'</td>';
        //SendActionType
        $out.='<td style="min-width:121px; text-align: center" class="middle_size">'.$langs->trans($SendActionType[empty($obj->send_after)?0:$obj->send_after]).'</td>';
        $begin_period = new DateTime($obj->period_begin);
        $end_period = new DateTime($obj->period_end);
        $out.='<td style="min-width:121px" class="middle_size">'.(!empty($obj->period_begin)?$begin_period->format('d.m.y'):'&nbsp;&nbsp;&nbsp;').'</td>';
        $out.='<td style="min-width:121px" class="middle_size">'.(!empty($obj->period_end)?$end_period->format('d.m.y'):'&nbsp;&nbsp;&nbsp;').'</td>';
        //Action
        $out.='<td style="min-width:85px">
                <img onclick="Preview($(this))" rowid="'.$obj->rowid.'" style="vertical-align: middle; cursor: pointer;" title="' . $langs->trans('Preview') . '" src="/dolibarr/htdocs/theme/eldy/img/preview.png">';
        if($user->rights->mailing->creer){
            $out.='<img onclick="Edit($(this))" rowid="'.$obj->rowid.'" style="vertical-align: middle; cursor: pointer;" title="' . $langs->trans('Edit') . '" src="/dolibarr/htdocs/theme/eldy/img/edit.png">';
        }
        if($user->rights->mailing->mailing_advance->delete || $user->rights->mailing->supprimer)
            $out.='<img onclick="Delete($(this))" rowid="'.$obj->rowid.'" style="vertical-align: middle; cursor: pointer;" title="' . $langs->trans('delete') . '" src="/dolibarr/htdocs/theme/eldy/img/delete.png"> ';

        $out.='<img onclick="TestMail($(this))" rowid="'.$obj->rowid.'" style="vertical-align: middle; cursor: pointer;" title="Тестове повідомлення" src="/dolibarr/htdocs/theme/eldy/img/mail.png"> ';
//        echo '<pre>';
//        var_dump($user->rights->mailing, $user->rights->mailing->valider);
//        echo '</pre>';
//        die();
        if($user->rights->mailing->creer) {
            if ($user->rights->mailing->valider)
                $out .= '<img onclick="SendMail($(this))" rowid="' . $obj->rowid . '" style="vertical-align: middle; cursor: pointer;" title="Відправити розсилку" src="/dolibarr/htdocs/theme/eldy/img/sendmail.png">';
            else
                $out .= '<img onclick="SendToValider($(this))" rowid="' . $obj->rowid . '" style="vertical-align: middle; cursor: pointer;" title="Відправити на перевірку" src="/dolibarr/htdocs/theme/eldy/img/sendmail.png">';
        }
        $out.='</td>';
        $out.='</tr>';
        $i++;
    }
    $out.='</tbody>';
    return $out;
}
//llxFooter();