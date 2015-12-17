<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 07.12.2015
 * Time: 12:43
 */
require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
top_htmlhead('<meta charset="UTF-8">');
//echo '<pre>';
//var_dump($GLOBALS);
//echo '</pre>';
//die();


if(GETPOST('action', 'alpha') == 'auth'){
    include_once DOL_DOCUMENT_ROOT.'/core/lib/security2.lib.php';
    $login = checkLoginPassEntity(GETPOST('name', 'alpha'),GETPOST('pass', 'alpha'),1,$authmode);
    if($login){
        $hex = dol_hash(uniqid(mt_rand(),TRUE));
        $answer = array('answer'=>'ok', 'error'=>"200", 'accessToken'=>$hex);
    }else{
        $answer = array('answer'=>'error', 'error'=>"401", 'accessToken'=>"");
    }
    http_response_code($answer['error']);
    $obj = json_encode($answer);
    print $obj;
    exit();
}elseif(GETPOST('action', 'alpha') == 'poling'){
//    echo strlen(GETPOST('hex', 'alpha'));
    switch(strlen(GETPOST('hex', 'alpha'))){
        case 32:{
            $answer = array('answer'=>'error', 'error'=>"401", 'accessToken'=>"");
        }break;
        case 2:{
            $answer = array('answer'=>'error', 'error'=>"401", 'accessToken'=>"");
        }break;
        case 0:{
            if(GETPOST('job', 'int')) {
                switch(GETPOST('job', 'int')) {
                    case 1: {
                        $job = array('uuid' => 'job1', 'call' => '+380507775511', 'sms' => null);
                        $answer = array('answer' => 'ok', 'error' => "", 'accessToken' => "", 'job' => $job);
                    }break;
                    case 2:{
                        $sms = array('numbers'=>'+380505223977,+380662094598,+380978059053','text'=>'test');
                        $job = array('uuid' => 'job1', 'call' => null, 'sms' => $sms);
                        $answer = array('answer' => 'ok', 'error' => "", 'accessToken' => "", 'job' => $job);
                    }
                }
            }else{
                $answer = array('answer' => 'ok', 'error' => "200", 'accessToken' => "", 'job' => "null");
            }
        }break;
    }
    http_response_code($answer['error']);
    $obj = json_encode($answer);
    print $obj;
    exit();
}elseif(GETPOST('action', 'alpha') == 'progress' || GETPOST('action', 'alpha') == 'finished'){
    $answer = array('answer'=>'ok', 'error'=>"200", 'accessToken'=>"f5633f5a05d61091249ac7d4746eeff0");
    http_response_code($answer['error']);
    $obj = json_encode($answer);
    print $obj;
    exit();
}
print '<table style="border: solid 1px; width: 500px">';
print '<form method="post" action="'.$_SERVER["PHP_SELF"].'">';
print '<input id="action" type="hidden" value="auth" name="action">';
print '<tr><td colspan="2" style="align: center">Авторизация пользователя</td></tr>';
print '<tr><td>name</td><td><input id="name" class="ui-autocomplete-input" type="text" autofocus="autofocus" value="" name="name" maxlength="55" size="60" autocomplete="off"></td></tr>';
print '<tr><td>pass</td><td><input id="pass" class="ui-autocomplete-input" type="text" autofocus="autofocus" value="" name="pass" maxlength="55" size="60" autocomplete="off"></td></tr>';
print '<tr><td></td><td><button id="send" type="submit">    Отправить    </button></td>';
print '</form>';
print '</table>';

print '<table style="border: solid 1px; width: 500px">';
print '<form method="post" action="'.$_SERVER["PHP_SELF"].'">';
print '<input id="action" type="hidden" value="poling" name="action">';
print '<tr><td colspan="2" style="align: center">Poling с не действительным HEX</td></tr>';
print '<tr><td>hex</td><td><input id="hex" class="ui-autocomplete-input" type="text" autofocus="autofocus" value="'.dol_hash(uniqid(mt_rand(),TRUE)).'" name="hex" maxlength="32" size="60" autocomplete="off"></td></tr>';
print '<tr><td></td><td><button id="send" type="submit">    Отправить    </button></td>';
print '</form>';
print '</table>';

print '<table style="border: solid 1px; width: 500px">';
print '<form method="post" action="'.$_SERVER["PHP_SELF"].'">';
print '<input id="action" type="hidden" value="poling" name="action">';
print '<input id="hex" type="hidden" value="-1" name="hex">';
print '<tr><td colspan="2" style="align: center">Poling с просроченным HEX</td></tr>';
print '<tr><td></td><td><button id="send" type="submit">    Отправить    </button></td>';
print '</form>';
print '</table>';

print '<table style="border: solid 1px; width: 500px">';
print '<form method="post" action="'.$_SERVER["PHP_SELF"].'">';
print '<input id="action" type="hidden" value="poling" name="action">';
print '<input id="hex" type="hidden" value="" name="hex">';
print '<input id="hex" type="hidden" value="0" name="job">';
print '<tr><td colspan="2" style="align: center">Poling с действительным HEX, но нет заданий</td></tr>';
print '<tr><td></td><td><button id="send" type="submit">    Отправить    </button></td>';
print '</form>';
print '</table>';

print '<table style="border: solid 1px; width: 500px">';
print '<form method="post" action="'.$_SERVER["PHP_SELF"].'">';
print '<input id="action" type="hidden" value="poling" name="action">';
print '<input id="hex" type="hidden" value="1" name="job">';
print '<tr><td colspan="2" style="align: center">Poling с действительным HEX и заданием звонить</td></tr>';
print '<tr><td></td><td><button id="send" type="submit">    Отправить    </button></td>';
print '</form>';
print '</table>';

print '<table style="border: solid 1px; width: 500px">';
print '<form method="post" action="'.$_SERVER["PHP_SELF"].'">';
print '<input id="action" type="hidden" value="poling" name="action">';
print '<input id="hex" type="hidden" value="2" name="job">';
print '<tr><td colspan="2" style="align: center">Poling с действительным HEX и заданием отправить смс рассылку</td></tr>';
print '<tr><td></td><td><button id="send" type="submit">    Отправить    </button></td>';
print '</form>';
print '</table>';

print '<table style="border: solid 1px; width: 500px">';
print '<form method="post" action="'.$_SERVER["PHP_SELF"].'">';
print '<input id="action" type="hidden" value="progress" name="action">';
print '<input id="hex" type="hidden" value="52f9d618ef8dbde98bcde61a084f7987" name="job">';
print '<tr><td colspan="2" style="align: center">Принято в работу</td></tr>';
print '<tr><td></td><td><button id="send" type="submit">    Отправить    </button></td>';
print '</form>';
print '</table>';

print '<table style="border: solid 1px; width: 500px">';
print '<form method="post" action="'.$_SERVER["PHP_SELF"].'">';
print '<input id="action" type="hidden" value="finished" name="action">';
print '<input id="hex" type="hidden" value="52f9d618ef8dbde98bcde61a084f7987" name="job">';
print '<tr><td colspan="2" style="align: center">Задача выполнена</td></tr>';
print '<tr><td></td><td><button id="send" type="submit">    Отправить    </button></td>';
print '</form>';
print '</table>';

exit();
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