<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 02.11.2016
 * Time: 8:45
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
global $db;
$object = new User($db);
$form = new Form($db);
$userlist=ShowUserList();
include DOL_DOCUMENT_ROOT . '/theme/eldy/comm/raports/activitycompany.html';

exit();
function ShowUserList(){
    global $db;
    $sql = "select `llx_user`.rowid, subdiv_id, subdivision.name, lastname, firstname, `datelastlogin`,
case when date(datefirsttodaylogin) <> date(now()) then null else datefirsttodaylogin end datefirsttodaylogin, case when `office_phone` is null then `user_mobile` else `office_phone` end phone
from llx_user
inner join `subdivision` on `subdivision`.`rowid` = `llx_user`.`subdiv_id`
where `llx_user`.active = 1
and login <> 'admin'
order by case when date(datefirsttodaylogin) <> date(now()) then null else datefirsttodaylogin end, lastname";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $out = '';
    $num = 0;
    while($obj = $db->fetch_object($res)){
        if(!empty($obj->datefirsttodaylogin)) {
            $lastlogin = new DateTime($obj->datefirsttodaylogin);
//            $lastlogin = $lastlogin->format("d.m.Y") . '</br>' . $lastlogin->format("H:i");
            $lastlogin = $lastlogin->format("H:i");
        }else{
            $lastlogin = "Ще не розпочали";
        }
//        var_dump($lastlogin->format("d.m.Y"));
//        die();
        $class_row = fmod($num,2)==0?'impair':'pair';
        $out.='<tr class="subdiv'.$obj->subdiv_id.' '.$class_row.'">';
        $out.="<td class='_subdivision middle_size'>$obj->name</td>";
        $out.="<td class='_lastname middle_size'>$obj->lastname</td>";
        $out.="<td class='_firstname middle_size'>$obj->firstname</td>";
        $out.="<td class='_phone middle_size'>$obj->phone</td>";
        $out.="<td class='_lastlogin middle_size'>$lastlogin</td>";
        $out.='</tr>';
        $num++;
    }
    return $out;
}
//$sql = "select * from llx_actioncomm
//where datep between adddate(date(now()), interval -1 month) and adddate(date(now()), interval 1 month)
//and active = 1
//and llx_actioncomm.`code` in (select `code` from llx_c_actioncomm
//                    where active = 1
//                    and `type` in ('system','user')
//                    and `code` not in ('AC_GLOBAL','AC_CURRENT'))";
//$int = time();
//$resActionList = $db->query($sql);
//if(!$resActionList)
//    dol_print_error($db);
//while($obj = $db->fetch_object($resActionList)){
//
//}
//exit();




