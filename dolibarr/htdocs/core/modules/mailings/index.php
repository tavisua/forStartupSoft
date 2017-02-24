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
//echo '<pre>';
//var_dump($user);
//echo '</pre>';
//die();
$action = $_REQUEST['action'];

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
if($action == 'sendmail'){
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
            if(!$user->id) {
                $user->fetch($_REQUEST['id_usr']);
//                echo '<pre>';
//                var_dump($_REQUEST);
//                echo '</pre>';
//                die($user->email);
            }
            require_once DOL_DOCUMENT_ROOT.'/core/class/SendMailSmtpClass.php';
            $mailSMTP = new SendMailSmtpClass('shop@t-i-t.com.ua', '777722345', 'ssl://smtp.yandex.ua', 'Техніка і технології', 465);
// $mailSMTP = new SendMailSmtpClass('логин', 'пароль', 'хост', 'имя отправителя');

// заголовок письма
            $headers= "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=utf-8\r\n"; // кодировка письма
            $headers .= "From: 'Техніка і технології' <shop@t-i-t.com.ua>\r\n"; // от кого письмо
            $result =  $mailSMTP->send($user->email, $subject, $mesg, $headers); // отправляем письмо
// $result =  $mailSMTP->send('Кому письмо', 'Тема письма', 'Текст письма', 'Заголовки письма');
            llxHeader();
            if($result === true){
                echo "1";
            }else{
                echo "Письмо не отправлено. Ошибка: " . $result;
            }
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
        case 'sendmails':{
            $mail_id = array();
            if(isset($_REQUEST['id'])&&!empty($_REQUEST['id']))
                $mail_id[]=$_REQUEST['id'];
            else{
                global $db;
                $sql = "select rowid from llx_mailing where 1 and statut=1 and date_send is null and date_valid is not null";
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
                AutoSendMail($rowid);
            }
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
        $freetime = $action->GetFreeTime(date('Y-m-d'), $_REQUEST['valider_id'], $exec_minuted, 0, date('Y-m-d H:i:s'));
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
//    echo '<pre>';
//    var_dump($user->id);
//    echo '</pre>';
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
    print htmlspecialchars_decode(str_replace('contactname', $user->firstname, $_REQUEST['html']));
    print '</div>';
    die();
}
if($action == 'insert'){
    if(!empty($_REQUEST['postlist']))
        $postlist = implode(',',$_REQUEST['postlist']);
    if(!empty($_REQUEST['responsibility']))
        $responsibility = implode(',',$_REQUEST['responsibility']);
    $sql = "insert into llx_mailing(statut,titre,body,fk_user_creat,`postlist`,`responsibility`)
      values(1, '".$db->escape($_REQUEST['theme'])."','".$db->escape($_REQUEST['body'])."',$user->id, 
      '".$postlist."', '".$responsibility."')";
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
    $sql = "update llx_mailing 
    set titre = '".$_REQUEST['theme']."', body='".$db->escape($_REQUEST['body'])."', fk_user_creat='".$user->id."', `postlist`='".$postlist."', 
        `responsibility`='".$responsibility."' where rowid=".$_REQUEST['rowid'];
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
    $sql = 'select titre,body,`postlist`,`responsibility` from llx_mailing where rowid = '.$_GET["rowid"];
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $mail = $db->fetch_object($res);
    $rowid = $_GET["rowid"];
    $theme = $mail->titre;
    $html = $mail->body;
    $backtopage = $_SERVER['REQUEST_URI'];
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
function AutoSendMail($rowid){
//    $string = array('name'=>"%C2%B3%EA%F2%EE%F0");
//
//    die(http_build_url($string, 'flags_'));
    if($rowid == 0)
        return 1;
    global $db,$langs,$user,$conf;
    $sql = "select titre,body, postlist, responsibility from llx_mailing where rowid = ".$rowid;
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

    $sql = "select rtrim(email) email from llx_mailing_cibles where fk_mailing = ".$rowid;
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)){
        $postedlist[]=mb_strtolower($obj->email,'utf-8');
    }
    $sql = "select llx_societe.state_id, socid, email1, email2 from `llx_societe_contact`
            left join llx_societe on llx_societe.rowid = `llx_societe_contact`.socid
                    where 1 ";
    if(!empty($postlist)&&!empty($responsibility))
        $sql .= " and (post_id in (".$postlist.") or `respon_id` in (".$responsibility."))";
    elseif (!empty($postlist))
        $sql .= " and post_id in (".$postlist.")";
    elseif (!empty($responsibility))
        $sql .= " and respon_id in (".$responsibility.")";
    $sql.=" and ((email1 like '%@%' and send_email1 = 1) or (email2 like '%@%' and send_email2 = 1))
            and `llx_societe_contact`.active = 1 and `llx_societe`.active = 1";
//    $sql.=" and llx_societe.state_id in (11,17)";
//    $sql.=" order by llx_societe.state_id";
//    echo '<pre>';
//    var_dump(empty($postedlist));
//    echo '</pre>';
//    die();

    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
//    $societelist = array();
    $emaillist = array();
    while($obj = $db->fetch_object($res)){
        $add = false;
        if(empty($emaillist[$obj->state_id]))
            $emaillist[$obj->state_id] = array();
        if(!empty($obj->email1)&&strpos($obj->email1, '@') && (!empty($postedlist)?!in_array($obj->email1,$postedlist):true)&&
            (!empty($emaillist[$obj->state_id])?!in_array($obj->email1, $emaillist[$obj->state_id]):true)) {
            $emaillist[$obj->state_id][] = $obj->email1;
            $add = true;
        }elseif (!empty($obj->email2)&&strpos($obj->email2, '@')&& (!empty($postedlist)?!in_array($obj->email2,$postedlist):true) &&
                (!empty($emaillist[$obj->state_id])?!in_array($obj->email2, $emaillist[$obj->state_id]):true)) {
            $emaillist[$obj->state_id][] = $obj->email2;
            $add = true;
        }
//        if($add)
//            $societelist[]=$obj->socid;
    }
//    unset($emaillist);
//    $emaillist[12][]='tavis@shtorm.com';
//    $emaillist[12][]='tavis.ua@gmail.com';
//    $emaillist[12][]='mikhailv_viktor@mail.ru';
//    $emaillist[12][]='veravikt@ukr.net';
//    echo '<pre>';
//    var_dump($emaillist);
//    echo '</pre>';
//    die();
    require_once DOL_DOCUMENT_ROOT.'/core/class/SendMailSmtpClass.php';
    $mailSMTP = new SendMailSmtpClass('shop@t-i-t.com.ua', '777722345', 'ssl://smtp.yandex.ua', 'Техніка і технології', 465);
// $mailSMTP = new SendMailSmtpClass('логин', 'пароль', 'хост', 'имя отправителя');


// заголовок письма
    $headers= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=utf-8\r\n"; // кодировка письма
    $headers .= "From: 'Техніка і технології' <shop@t-i-t.com.ua>\r\n"; // от кого письмо


//echo '<pre>';
//var_dump($emaillist);
//echo '</pre>';
//die();
    $num = 0;
    foreach ($emaillist as $key=>$value){
        foreach ($value as $item) {
//            $item = 'ahrozahidrv@gmail.com';
            if(!in_array(strtolower(trim($item)), $postedlist)) {
                $sql = "insert into `llx_mailing_cibles`(fk_mailing,email,date_envoi) values(" . $rowid . ",'" . $item . "',now())";
                $res = $db->query($sql);
                if (!$res) {
                    dol_print_error($db);
                }else {
                    $postedlist[]=$item;
                    $result = true;
                    $result = $mailSMTP->send($item, $subject, $mesg, $headers); // отправляем письмо
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
    $sql = "update `llx_mailing` set date_send = now() where rowid = ".$rowid;
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
}
function MailingList(){
    global $db,$langs,$user;
//    echo '<pre>';
//    var_dump($user->rights->mailing);
//    echo '</pre>';
//    die();
    $sql = "select rowid,titre,body,date_creat,date_valid,date_send,fk_user_creat,fk_user_valid from `llx_mailing`
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
        //Action
        $out.='<td style="min-width:51px">
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