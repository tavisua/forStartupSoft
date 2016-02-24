<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 07.12.2015
 * Time: 12:43
 */



require '../main.inc.php';

require_once $_SERVER["DOCUMENT_ROOT"].'/dolibarr/htdocs/core/lib/functions.lib.php';

//echo '<pre>';
//var_dump($_POST);
//echo '</pre>';
//die();
global $user, $db;
if($_REQUEST['action']=='registerphone'){


    $user->fetch($_REQUEST['id_usr']);
//    if(empty($user->accessToken)){
        $user->accessToken = dol_hash(uniqid(mt_rand(), TRUE));
        $sql='insert into phone_connect (phonenumber,Hex,id_usr) values("'.$db->escape($_GET['phonenumber']).'","'.$user->accessToken.'", '.$user->id.')';
        $user->timePhoneConnect = dol_now();
//    die($sql);
        $res = $db->query($sql);
        if(!$res){
            dol_print_error($db);
        }
//    }else{
//
//    }

    exit();
}elseif($_REQUEST['action']=='sendSMS'||$_REQUEST['action']=='CallPhone'){
    $user->fetch($_REQUEST['id_usr']);
    if(!empty($user->id_connect)) {
        $sql = 'insert into phone_job(`id_connect`,`status`,';
        if ($_REQUEST['action'] == 'sendSMS')
            $sql .= '`sms`,`text`,';
        elseif ($_REQUEST['action'] == 'CallPhone')
            $sql .= '`call`,';
        $sql .= '`id_usr`)';
        $sql .= ' values(' . $user->id_connect . ",0, '".str_replace('*','+', $_GET['phonenumber'])."',";
        if ($_REQUEST['action'] == 'sendSMS')
            $sql .= "'".$_REQUEST['text']."',";
        $sql .= $_REQUEST['id_usr'].')';
//        die($sql);
        $res = $db->query($sql);
        if(!$res){
            dol_print_error($db);
        }
//        die($sql);
    }else{
        $fp = fopen('C:\temp\call.json', 'a+');
        fwrite($fp, '{"call":"'.str_replace('*','+', $_GET['phonenumber']).'"}');
    }
    exit();
}
elseif(GETPOST('action', 'alpha') == 'auth'){
//    die('test');
    include_once DOL_DOCUMENT_ROOT.'/core/lib/security2.lib.php';
    // Authentication mode
    if (empty($dolibarr_main_authentication)) $dolibarr_main_authentication='http,dolibarr';
    // Authentication mode: forceuser
    if ($dolibarr_main_authentication == 'forceuser' && empty($dolibarr_auto_user)) $dolibarr_auto_user='auto';
    // Set authmode
    $authmode=explode(',',$dolibarr_main_authentication);
    $login = checkLoginPassEntity(GETPOST('name', 'alpha'),GETPOST('pass', 'alpha'),1,$authmode);
    $http_status = 0;
//

    if($login){
        $sql ='select rowid from llx_user where login="'.trim($login).'" and active = 1 limit 1';
//        die($sql);
        $res = $db->query($sql);
        if(!$res){
            dol_print_error($db);
        }
        $obj = $db->fetch_object($res);
        include_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
        $login_user = new User($db);
        $login_user->fetch($obj->rowid);
        if(!empty($login_user->accessToken)) {
            $answer = array('answer' => 'ok', 'accessToken' => $login_user->accessToken);
            $http_status = 200;
        }else{
            $answer = array('answer'=>'error', 'error'=>"user_unauth");
            $http_status = 401;
        }
    }else{
        $answer = array('answer'=>'error', 'error'=>"user_unauth");
        $http_status = 401;
    }
    $obj = json_encode($answer);
    http_response_code($http_status);
//    header('Content-Type: application/json', true, $http_status);
    print $obj;
//    var_dump(http_build_query($data));
    exit();
}elseif(GETPOST('action', 'alpha') == 'poling'){

    $sql = "select rowid id_connect, UNIX_TIMESTAMP(dtChange) dtChange, id_usr  from `phone_connect` where `Hex` = '".GETPOST('accessToken', 'alpha')."'";
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
    $obj = $db->fetch_object($res);
//    var_dump($sql);
//    die();
    if($db->num_rows($res)==0 || dol_now()-3600>$obj->dtChange){
        $answer = array('answer'=>'error', 'error'=>"user_unauth");
        $http_status = 401;
    }else{
        $sql = 'select rowid uuid,`call`,`sms`,`text` from phone_job where id_connect = '.$obj->id_connect.' and status = 0 limit 1';
        $res = $db->query($sql);
        if(!$res){
            dol_print_error($db);
        }
        if(!$db->num_rows($res)){
            $answer = array('answer' => 'ok');
        }else{
            $activejob = $db->fetch_object($res);
            if(!empty($activejob->call)){
                $phones = explode(',', $activejob->call);
                $job = array('uuid' => $activejob->uuid, 'call' => json_encode($phones));
                $answer = array('answer' => 'ok', 'job' => $job);
            }elseif(!empty($activejob->sms)){
                $phones = explode(',', $activejob->sms);
                $sms = array('numbers'=>array($activejob->sms),'text'=>$activejob->text);
                $job = array('uuid' => $activejob->uuid,  'sms' => $sms);
                $answer = array('answer' => 'ok', 'job' => $job);
            }
        }
        $http_status = 200;
    }
//    switch(strlen(GETPOST('hex', 'alpha'))){
//        case 32:{
//            $answer = array('answer'=>'error', 'error'=>"user_unauth", 'accessToken'=>"");
//            $http_status = 401;
//        }break;
//        case 2:{
//            $answer = array('answer'=>'error', 'error'=>"user_unauth", 'accessToken'=>"");
//            $http_status = 401;
//        }break;
//        case 0:{
//            if(GETPOST('job', 'int')) {
//                switch(GETPOST('job', 'int')) {
//                    case 1: {
//                        $job = array('uuid' => 'job1', 'call' => '+380505223977', 'sms' => null);
//                        $answer = array('answer' => 'ok', 'job' => $job);
//                    }break;
//                    case 2:{
//                        $sms = array('numbers'=>'+380505223977,+380662094598,+380978059053','text'=>'test');
//                        $job = array('uuid' => 'job1', 'call' => null, 'sms' => $sms);
//                        $answer = array('answer' => 'ok', 'job' => $job);
//                    }
//                }
//            }else{
//                $answer = array('answer' => 'ok',  'job' => "null");
//            }
//            $http_status = 200;
//        }break;
//    }
    http_response_code($http_status);
    $obj = json_encode($answer);
    header('Content-Type: application/json', true, $http_status);
    print $obj;
    exit();
}elseif(GETPOST('action', 'alpha') == 'processing' || GETPOST('action', 'alpha') == 'finished'){
    if(GETPOST('action', 'alpha') == 'processing')$status = 1;
    elseif(GETPOST('action', 'alpha') == 'finished')$status = 2;
//    $sql = 'select id_usr from phone_connect where Hex="'.trim($_POST['hex']).'"';
    $sql = 'update phone_job set status = '.$status;
    if($status == 2)
        $sql.=', dtExec=Now()';
    $sql.=' where rowid='.trim($_POST['job']);
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
    $sql = 'update phone_connect set dtChange=Now() where Hex = "'.trim($_POST['accessToken']).'" limit 1';
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
    $answer = array('answer'=>'ok', 'accessToken'=>trim($_POST['accessToken']));
    http_response_code(200);
    $obj = json_encode($answer);
    header('Content-Type: application/json', true, $http_status);
    print $obj;
    exit();
}elseif($_REQUEST['action']=='viewform') {

    top_htmlhead('<meta charset="UTF-8">');
    print '<table style="border: solid 1px; width: 500px">';
    print '<form method="post" action="' . $_SERVER["PHP_SELF"] . '">';
    print '<input id="action" type="hidden" value="auth" name="action">';
    print '<tr><td colspan="2" style="align: center">Авторизация пользователя</td></tr>';
    print '<tr><td>name</td><td><input id="name" class="ui-autocomplete-input" type="text" autofocus="autofocus" value="" name="name" maxlength="55" size="60" autocomplete="off"></td></tr>';
    print '<tr><td>pass</td><td><input id="pass" class="ui-autocomplete-input" type="text" autofocus="autofocus" value="" name="pass" maxlength="55" size="60" autocomplete="off"></td></tr>';
    print '<tr><td></td><td><button id="send" type="submit">    Отправить    </button></td>';
    print '</form>';
    print '</table>';

    print '<table style="border: solid 1px; width: 500px">';
    print '<form method="post" action="' . $_SERVER["PHP_SELF"] . '">';
    print '<input id="action" type="hidden" value="poling" name="action">';
    print '<tr><td colspan="2" style="align: center">Poling с не действительным HEX</td></tr>';
    print '<tr><td>hex</td><td><input id="hex" class="ui-autocomplete-input" type="text" autofocus="autofocus" value="' . dol_hash(uniqid(mt_rand(), TRUE)) . '" name="accessToken" maxlength="32" size="60" autocomplete="off"></td></tr>';
    print '<tr><td></td><td><button id="send" type="submit">    Отправить    </button></td>';
    print '</form>';
    print '</table>';

    print '<table style="border: solid 1px; width: 500px">';
    print '<form method="post" action="' . $_SERVER["PHP_SELF"] . '">';
    print '<input id="action" type="hidden" value="poling" name="action">';
    print '<input id="hex" type="hidden" value="-1" name="accessToken">';
    print '<tr><td colspan="2" style="align: center">Poling с просроченным HEX</td></tr>';
    print '<tr><td></td><td><button id="send" type="submit">    Отправить    </button></td>';
    print '</form>';
    print '</table>';

    print '<table style="border: solid 1px; width: 500px">';
    print '<form method="post" action="' . $_SERVER["PHP_SELF"] . '">';
    print '<input id="action" type="hidden" value="poling" name="action">';
    print '<input id="hex" type="hidden" value="" name="accessToken">';
    print '<input id="hex" type="hidden" value="0" name="job">';
    print '<tr><td colspan="2" style="align: center">Poling с действительным HEX, но нет заданий</td></tr>';
    print '<tr><td></td><td><button id="send" type="submit">    Отправить    </button></td>';
    print '</form>';
    print '</table>';

    print '<table style="border: solid 1px; width: 500px">';
    print '<form method="post" action="' . $_SERVER["PHP_SELF"] . '">';
    print '<input id="action" type="hidden" value="poling" name="action">';
    print '<input id="hex" type="hidden" value="1" name="job">';
    print '<tr><td colspan="2" style="align: center">Poling с действительным HEX и заданием звонить</td></tr>';
    print '<tr><td></td><td><button id="send" type="submit">    Отправить    </button></td>';
    print '</form>';
    print '</table>';

    print '<table style="border: solid 1px; width: 500px">';
    print '<form method="post" action="' . $_SERVER["PHP_SELF"] . '">';
    print '<input id="action" type="hidden" value="poling" name="action">';
    print '<input id="hex" type="hidden" value="2" name="job">';
    print '<tr><td colspan="2" style="align: center">Poling с действительным HEX и заданием отправить смс рассылку</td></tr>';
    print '<tr><td></td><td><button id="send" type="submit">    Отправить    </button></td>';
    print '</form>';
    print '</table>';

    print '<table style="border: solid 1px; width: 500px">';
    print '<form method="post" action="' . $_SERVER["PHP_SELF"] . '">';
    print '<input id="action" type="hidden" value="processing" name="action">';
    print '<input id="uuid" type="hidden" value="12345" name="uuid">';
    print '<input id="hex" type="hidden" value="52f9d618ef8dbde98bcde61a084f7987" name="accessToken">';
    print '<tr><td colspan="2" style="align: center">Принято в работу</td></tr>';
    print '<tr><td></td><td><button id="send" type="submit">    Отправить    </button></td>';
    print '</form>';
    print '</table>';

    print '<table style="border: solid 1px; width: 500px">';
    print '<form method="post" action="' . $_SERVER["PHP_SELF"] . '">';
    print '<input id="action" type="hidden" value="finished" name="action">';
    print '<input id="uuid" type="hidden" value="12345" name="uuid">';
    print '<input id="hex" type="hidden" value="52f9d618ef8dbde98bcde61a084f7987" name="accessToken">';
    print '<tr><td colspan="2" style="align: center">Задача выполнена</td></tr>';
    print '<tr><td></td><td><button id="send" type="submit">    Отправить    </button></td>';
    print '</form>';
    print '</table>';

    exit();
}
function HexValid($Hex){

}
function getHexadecimal($login){
    global $db;
    $phone_user = new User($db);
    $phone_user->fetch('', $login, '', 1);
    $arr = array('a','b','c','d','e','f',
        'g','h','i','j','k','l',
        'm','n','o','p','r','s',
        't','u','v','x','y','z',
        'A','B','C','D','E','F',
        'G','H','I','J','K','L',
        'M','N','O','P','R','S',
        'T','U','V','X','Y','Z',
        '1','2','3','4','5','6',
        '7','8','9','0','.',',',
        '(',')','[',']','!','?',
        '&','^','%','@','*','$',
        '<','>','/','|','+','-',
        '{','}','`','~');
    // Генерируем код
    $hex = "";
    for($i = 0; $i < 32; $i++)
    {
        // Вычисляем случайный индекс массива
        $index = rand(0, count($arr) - 1);
        $hex .= $arr[$index];
    }
    $sql = 'insert into `phone_connect`(Hex,id_usr)values("'.$hex.'", '.$phone_user->id.')';
    $res = $db->query($sql);
    if(!$res){
        dol_print_error($db);
    }
    return $hex;
}