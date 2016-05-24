<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 21.05.2016
 * Time: 18:23
 */
require $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/main.inc.php';
if(isset($_REQUEST['beforeload'])){
    llxHeader("",'Close',"");
    print '<script>
        $(document).ready(function(){
            close();
        })
    </script>';
    exit();
}
if($_REQUEST['action']=='getConversation'){
    echo getConversation($_REQUEST['rowid']);
    exit();
}elseif($_REQUEST['action']=='delConversation'){
    global $db,$user;
    $sql = "update llx_users_action set active = 0, id_usr=".$user->id.' where rowid='.$_REQUEST['rowid'];
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    echo '1';
    exit();
}

require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
$form = new Form($db);
$object = new User($db);


//$table = ShowTable();
$object->fetch($_GET['id_usr']);
$HourlyPlan = $langs->trans('UserActions');
llxHeader("",$HourlyPlan,"");
print_fiche_titre($langs->trans('UserActions').' '.$object->lastname.' '.$object->firstname);

$symbols = explode(',', '(,), ,+,-');
$office_number = str_replace($symbols,'', $object->office_phone);
//echo '<pre>';
//var_dump($object);
//echo '</pre>';
//die();
$phonenumber = '<table><td><a onclick="Call('.$office_number.', '."'users'".', '.$_GET['id_usr'].');">'.$object->office_phone.'</a></td><td onclick="showSMSform('.$office_number.');"><img src="/dolibarr/htdocs/theme/eldy/img/object_sms.png"></td></table>';
if(!empty($object->user_mobile)) {
    $user_mobile = str_replace($symbols,'', $obj->office_phone);
    $phonenumber='<table>
            <tr>
                <td><a onclick="Call('.$office_number.', '."'users'".', '.$_GET['id_usr'].');">'.$object->office_phone.'</a></td>
                <td onclick="showSMSform('.$office_number.');"><img src="/dolibarr/htdocs/theme/eldy/img/object_sms.png"></td>
            </tr>
            <tr>
                <td><a onclick="Call('.$user_mobile.', '."'users'".', '.$_GET['id_usr'].');">'.$object->user_mobile.'</a></td>
                <td onclick="showSMSform('.$user_mobile.');"><img src="/dolibarr/htdocs/theme/eldy/img/object_sms.png"></td>
            </tr></table>';
}
$actiontabe = getActions();

include $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/theme/eldy/users/useractions.html';
//echo $user->id.' '.$_GET['id_usr'];
llxPopupMenu();
exit();

function getConversation($rowid){
    global $db, $user;
    $out='<table class="setdate" style="background: #ffffff; width: 250px">
            <thead><tr class="multiple_header_table"><th class="middle_size" colspan="3" style="width: 100%">Перегляд перемовини</th>
            <a class="close" style="margin-left: -160px" onclick="CloseConversation();" title="Закрити"></a>
                </tr>
                </thead>
            <tbody>';
    $sql = "select said,answer,argument,said_important,result_of_action,work_before_the_next_action
        from llx_users_action
        where rowid = ".$rowid;
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    if(!empty($obj->said))
        $out.='<tr><td class="middle_size"><b>Що сказали</b></td><td class="small_size">'.$obj->said.'</td></tr>';
    if(!empty($obj->answer))
        $out.='<tr><td class="middle_size"><b>Що відповів</b></td><td class="small_size">'.$obj->answer.'</td></tr>';
    if(!empty($obj->argument))
        $out.='<tr><td class="middle_size"><b>Чим аргументував</b></td><td class="small_size">'.$obj->argument.'</td></tr>';
    if(!empty($obj->said_important))
        $out.='<tr><td class="middle_size"><b>Що важливого сказав</b></td><td class="small_size">'.$obj->said_important.'</td></tr>';
    if(!empty($obj->result_of_action))
        $out.='<tr><td class="middle_size"><b>Результат перемовин</b></td><td class="small_size">'.$obj->result_of_action.'</td></tr>';
    if(!empty($obj->work_before_the_next_action))
        $out.='<tr><td class="middle_size"><b>Робота до/на наступних дій</</td><td class="small_size">'.$obj->work_before_the_next_action.'</td></tr>';
    $out.='</tbody></table>';
    return $out;
}
function getActions(){
    global $db, $user, $conf, $langs;
    print '<form id="addaction" method="post" action="/dolibarr/htdocs/comm/action/card.php">
                        <input type="hidden" value="/dolibarr/htdocs/current_plan.php?idmenu=10423&mainmenu=current_task&leftmenu=" name="backtopage">
                        <input type="hidden" value="create" id="edit_action" name="action">
                        <input type="hidden" value="current_task" name="mainmenu">
                        <input type="hidden" value="AC_CURRENT" name="actioncode">
                        <input type="hidden" value="" name="datep">
                        <input type="hidden" name="id" value="" id="action_id">
                    </form>';
    $sql = "select `llx_actioncomm`.`id`, `llx_user`.`lastname`, `llx_c_actioncomm`.`libelle` title,  `llx_actioncomm`.`code`, `llx_actioncomm`.`datep`, `llx_actioncomm`.`percent`, `llx_actioncomm`.`fk_user_author`, `llx_actioncomm_resources`.`fk_element`, `llx_actioncomm`.`datec`, `llx_actioncomm`.note from `llx_actioncomm`
        left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = `llx_actioncomm`.`id`
        left join llx_user on llx_user.rowid = `llx_actioncomm`.`fk_user_author`
        left join `llx_c_actioncomm` on `llx_c_actioncomm`.`code` = `llx_actioncomm`.`code`
        where `fk_user_author` in (".$user->id.",".(empty($_GET['id_usr'])?"0":$_GET['id_usr']).")
        and `llx_actioncomm`.`code` in (select `code` from `llx_c_actioncomm` where type in ('user', 'system'))
        and `llx_actioncomm_resources`.`fk_element` in (".$user->id.",".(empty($_GET['id_usr'])?"0":$_GET['id_usr']).")
        and `llx_actioncomm`.`fk_user_author`<>`llx_actioncomm_resources`.`fk_element`
        and `llx_actioncomm`.`active` = 1
        union
        select concat('_', `llx_users_action`.`rowid`) as id, `llx_user`.`lastname`, 'Перемовини', 'AC_CONVERSATION', `llx_users_action`.`dtChange`, 100, `llx_users_action`.`id_usr`, `llx_users_action`.`contactid`, `llx_users_action`.`dtChange`,''
        from `llx_users_action`
        left join llx_user on llx_user.rowid = `llx_users_action`.`id_usr`
        where (`llx_users_action`.`contactid` in (".$user->id.",".(empty($_GET['id_usr'])?"0":$_GET['id_usr']).")
        or `llx_users_action`.`id_usr` in (".$user->id.",".(empty($_GET['id_usr'])?"0":$_GET['id_usr'])."))
        and `llx_users_action`.`active` = 1
        order by datec desc";
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();
//    die($sql);
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $out="<tbody id='actions'>";
    if($db->num_rows($res)==0){
        $out.='<tr class="pair">';
        $width = array(85,85,55,63,55,60,55,55,55);
        for($i=0;$i<9;$i++)
            $out.='<td style="width: '.$width[$i].'px">&nbsp;</td>';
        $out.='</tr>';
    }else{
        $count = 0;
        $date = new DateTime();
        while($obj = $db->fetch_object($res)){
//            $class = fmod($count, 2) != 1 ? ("impair") : ("pair");
            switch (trim($obj->code)) {
                case 'AC_CONVERSATION':{
                    $classitem = 'conversation';
                    $iconitem = 'horn.png';
                    $mainmenu= 'coworkers';
                }break;
                case 'AC_GLOBAL': {
                    $classitem = 'global_taskitem';
                    $iconitem = 'object_global_task.png';
                    $mainmenu= 'global_task';
                }
                    break;
                case 'AC_CURRENT': {
                    $classitem = 'current_taskitem';
                    $iconitem = 'object_current_task.png';
                    $mainmenu= 'current_task';
                }
                    break;
                case 'AC_RDV': {
                    $classitem = 'office_meetting_taskitem';
                    $iconitem = 'object_office_meetting_task.png';
                    $mainmenu= 'area';
                }
                    break;
                case 'AC_TEL': {
                    $classitem = 'office_callphone_taskitem';
                    $iconitem = 'object_call.png';
                    $mainmenu= 'area';
                }
                    break;
                case 'AC_DEP': {
                    $classitem = 'departure_taskitem';
                    $iconitem = 'object_departure_task.png';
                    $mainmenu= 'area';
                }
                    break;
            }
            $datec = new DateTime($obj->datec);
            $datep = new DateTime($obj->datep);
            $out.='<tr class="'.$classitem.'" title="' . $langs->trans($obj->title) .'" id="'.$obj->id.'">';
            $out.='<td class="small_size" style="width: 84px">'.$datec->format('d.m.Y').'</td>';
            $out.='<td class="small_size" style="width: 84px">'.$obj->lastname.'</td>';
            $out.='<td class="small_size" style="text-align:center; width: 54px"><img src="/dolibarr/htdocs/theme/' . $conf->theme . '/img/' . $iconitem . '"></td>';
            $out.='<td class="small_size" style="width: 62px">'.$datep->format('d.m.Y').'</td>';
            $style = 'style="';
            $deadline = new DateTime($obj->datep2);
            if($obj->percent < 98) {
                if ($datep < $date) {
                    $style = 'style="background:rgb(255, 0, 0)';
                } elseif ($deadline == $date) {
                    $style = 'style="background:rgb(0, 255, 0)';
                }
                if ($obj->percent == "-1")
                    $status = 'ActionNotRunning';
                elseif ($obj->percent == 0)
                    $status = 'ActionRunningNotStarted';
                elseif ($obj->percent > 0 && $obj->percent < 98)
                    $status = 'ActionRunningShort';
                else
                    $status = 'ActionDoneShort';
            }
            $value='';
            if($obj->percent <= 98)
                $value = ($langs->trans($status));
            elseif($obj->percent == 99)
                $value = '<img src="/dolibarr/htdocs/theme/eldy/img/BWarning.png" title="Задачу виконано" style=width: 50px;">';
            else
                $value = 'Виконано';
            $out.='<td class="small_size" style="text-align: center; width:54px; '.($datep < $date&&$obj->percent <= 98?'background:rgb(255, 0, 0);':'').'">'.$value.'</td>';
            if($obj->fk_user_author == $user->id && is_numeric($obj->id))
                $out.='<td style=" text-align: center; width:60px; "><img src="/dolibarr/htdocs/theme/eldy/img/uncheck.png" onclick="ConfirmExec(' . $obj->id . ');" id="confirm' . $obj->id . '"></td>';
            else
                $out.='<td  style="width:51px">&nbsp;</td>';
            if(in_array($obj->code, array('AC_GLOBAL', 'AC_CURRENT', 'AC_CONVERSATION'))) {
                if(is_numeric($obj->id))
                    $onclick = 'href="/dolibarr/htdocs/comm/action/chain_actions.php?action_id=' . $obj->id . '&mainmenu=' . $mainmenu . '"';
                else
                    $onclick = 'onclick="PreviewConversation('.substr($obj->id,1).');"';
                $out .= '<td id="'.$obj->id.'" style="text-align: center;  width:54px; "><a '.$onclick.'><img title="Переглянути" src="/dolibarr/htdocs/theme/eldy/img/preview.png"></a></td>';
            }else
                $out.='<td  >&nbsp;</td>';
            if($obj->fk_user_author == $user->id) {
                if(is_numeric($obj->id))
                    $onclick = 'EditAction(' .$obj->id. ', ' . "'" . $obj->code . "'" . ');';
                else
                    $onclick = 'EditConversation('.substr($obj->id,1).');';
                $out .= '<td  style="text-align: center;  width:54px;"><img id="img_' . $obj->id . '" src="/dolibarr/htdocs/theme/eldy/img/edit.png" style="cursor: pointer;" onclick="'.$onclick.'" title="'.(is_numeric($obj->id)?'Редагувати завдання':'Редагувати перемовини').'"></td>';
                if(is_numeric($obj->id))
                    $onclick = 'ConfirmDelTask(' . $obj->id . ');';
                else
                    $onclick = 'DelConversation('.substr($obj->id,1).');';
                $out .='<td style="text-align: center;  width:34px;"><img title="'.(is_numeric($obj->id)?'Видалити завдання':'Видалити перемовини').'" src="/dolibarr/htdocs/theme/eldy/img/delete.png" onclick="'.$onclick.'" id="confirm' . $obj->id . '"></td>';
            }else
                $out.='<td  >&nbsp;</td><td  >&nbsp;</td>';
            $out.='</tr>';

        }
    }
    $out.="</tbody>";
    return $out;
}
