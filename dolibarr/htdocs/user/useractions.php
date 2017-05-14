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

//$test = '{"6":{"id":"6","mandatory":0,"transparency":null},"7":{"id":"7","transparency":"on","mandatory":1}}';
//var_dump(json_decode($test));
//die();
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
    $user_mobile = str_replace($symbols,'', $object->user_mobile);
//    die($user_mobile);
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
$id_usr = $_REQUEST['id_usr'];
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
        $out.='<tr><td class="middle_size"><b>Робота до/на наступних дій</td><td class="small_size">'.$obj->work_before_the_next_action.'</td></tr>';
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
    $sql = "select `llx_actioncomm`.`id`, `llx_actioncomm`.`period`, `llx_actioncomm`.`confirmdoc`, round((UNIX_TIMESTAMP(datep2)-UNIX_TIMESTAMP(datep))/60,0) iMinute, `llx_actioncomm`.`datep2`, `llx_actioncomm`.`datepreperform`, `llx_actioncomm`.`dateconfirm`, `llx_user`.rowid as id_usr, `llx_user`.`lastname`, `llx_c_actioncomm`.`libelle` title,  `llx_actioncomm`.`code`,
        `llx_actioncomm`.`datep`, `llx_actioncomm`.`percent`,
        `llx_actioncomm`.`fk_user_author`, `llx_actioncomm_resources`.`fk_element`, `llx_actioncomm`.`datec`, `llx_actioncomm`.note
        from `llx_actioncomm`
        left join `llx_actioncomm_resources` on `llx_actioncomm_resources`.`fk_actioncomm` = `llx_actioncomm`.`id`
        left join llx_user on llx_user.rowid = `llx_actioncomm`.`fk_user_author`
        left join `llx_c_actioncomm` on `llx_c_actioncomm`.`code` = `llx_actioncomm`.`code`";
    if(empty($_REQUEST['kind']))
        $sql.= " where (`fk_user_author` = ".$user->id." and  `llx_actioncomm_resources`.`fk_element` = ".(empty($_GET['id_usr'])?"0":$_GET['id_usr'])."
        or `fk_user_author` = ".(empty($_GET['id_usr'])?"0":$_GET['id_usr'])." and `llx_actioncomm_resources`.`fk_element` = ".$user->id.")";
    elseif ($_REQUEST['kind'] == 'yourself'){
        if(empty($_REQUEST["filterdatas"])) {
            $sql .= " where (`fk_user_author` = " . (empty($_GET['id_usr']) ? "0" : $_GET['id_usr']) . " 
                or `llx_actioncomm_resources`.`fk_element` = " . (empty($_GET['id_usr']) ? "0" : $_GET['id_usr']) . ") ";
        }else{
//            var_dump($_REQUEST["filterdatas"]);
//            die();
            $filter = json_decode($_REQUEST["filterdatas"]);
            if(!empty($filter->performer)){
                $id_usr = (empty($_GET['id_usr']) ? "0" : $_GET['id_usr']);
                if($id_usr != $filter->performer)
                    $sql .= " where (`fk_user_author` = " . $id_usr . " 
                    and `llx_actioncomm_resources`.`fk_element` = " . $filter->performer . ") ";
                else
                    $sql .= " where (`fk_user_author` = " . $id_usr . " 
                    and `llx_actioncomm_resources`.`fk_element` is null or `fk_user_author` <> " . $id_usr . " 
                    and `llx_actioncomm_resources`.`fk_element` = " . $id_usr . " ) ";
            }elseif (!empty($filter->customer)){
                $id_usr = (empty($_GET['id_usr']) ? "0" : $_GET['id_usr']);
                if($id_usr != $filter->customer)
                    $sql .= " where (`fk_user_author` = " . (empty($_GET['id_usr']) ? "0" : $_GET['id_usr']) . " 
                    or `llx_actioncomm_resources`.`fk_element` = " . $filter->customer . ") ";
                else
                    $sql .= " where (`fk_user_author` = " . (empty($_GET['id_usr']) ? "0" : $_GET['id_usr']) . ") ";

            }else
                $sql .= " where (`fk_user_author` = " . (empty($_GET['id_usr']) ? "0" : $_GET['id_usr']) . " 
                or `llx_actioncomm_resources`.`fk_element` = " . (empty($_GET['id_usr']) ? "0" : $_GET['id_usr']) . ") ";
        }
        $sql.= " and `llx_actioncomm`.percent not in (100,-100,99)";
    }
    $sql.= " and `llx_actioncomm`.`code` in (select `code` from `llx_c_actioncomm` where type in ('user', 'system'))";
//    if($user->id != $_GET['id_usr'])
//        $sql .= " and `llx_actioncomm`.`fk_user_author`<>`llx_actioncomm_resources`.`fk_element`";
//    else
//        $sql .= " and `llx_actioncomm`.`fk_user_author`=`llx_actioncomm_resources`.`fk_element`";

    $sql .= " and `llx_actioncomm`.`active` = 1 ";
    if(empty($_REQUEST['kind'])) {
        $sql .= " union
        select concat('_', `llx_users_action`.`rowid`) as id, '', '', '', '', '', '', `llx_user`.rowid as id_usr, `llx_user`.`lastname`, 'Перемовини', 'AC_CONVERSATION',
        `llx_users_action`.`dtChange`, 100,
        `llx_users_action`.`id_usr`, `llx_users_action`.`contactid`, `llx_users_action`.`dtChange`,''
        from `llx_users_action`
        left join llx_user on llx_user.rowid = `llx_users_action`.`id_usr` ";
        $sql .= "where (`llx_users_action`.`contactid` = " . $user->id . " and  `llx_users_action`.`id_usr` = " . (empty($_GET['id_usr']) ? "0" : $_GET['id_usr']) . "
        or  `llx_users_action`.`id_usr` = " . $user->id . " and `llx_users_action`.`contactid` = " . (empty($_GET['id_usr']) ? "0" : $_GET['id_usr']) . ")";
        $sql .= "   and `llx_users_action`.`active` = 1";
    }
    $sql .= " order by datec desc";
//
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();
//    die($sql);
    require_once $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/comm/action/class/actioncomm.class.php';
    $Actions = new ActionComm($db);
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $tmp_user = new User($db);
    $responsibility = array();//Сфера відповідальності
    $taskID = array();//Перелік ІД завдань
    while($obj = $db->fetch_object($res)){
        if(!isset($responsibility[$obj->id_usr])) {
            $tmp_user->fetch($obj->id_usr);
            $responsibility[$obj->id_usr]['alias'] = $tmp_user->respon_alias;
            $responsibility[$obj->id_usr]['lastname'] = $tmp_user->lastname;
        }
        if(!isset($responsibility[$obj->fk_element])) {
            $tmp_user->fetch($obj->fk_element);
            $responsibility[$obj->fk_element]['alias'] = $tmp_user->respon_alias;
            $responsibility[$obj->fk_element]['lastname'] = $tmp_user->lastname;
        }
        if(is_numeric($obj->id)&&!in_array($obj->id, $taskID)){
            $taskID[]=$obj->id;
        }
    }

    if(!isset($responsibility[$user->id])){
        $responsibility[$user->id]['alias']=$user->respon_alias;
        $responsibility[$user->id]['lastname']=$user->lastname;
    }
    $sql="select `llx_actioncomm`.`id`, `llx_c_groupoftask`.`name` from `llx_actioncomm`
    left join llx_c_groupoftask on `llx_c_groupoftask`.`rowid` = fk_groupoftask
    where id in (".implode(',', $taskID).")";
    $res_grouptask = $db->query($sql);
    $groupoftask = array();
    if($res_grouptask)
        while($obj = $db->fetch_object($res_grouptask)){
            if(!isset($groupoftask[$obj->id]))
                $groupoftask[$obj->id] = $obj->name;
        }

    if(count($taskID)>0) {

        $sql = "select `llx_societe_action`.`action_id` as rowid, max(`llx_societe_action`.`dtChange`) dtChange, `responsibility`.`alias`  from `llx_societe_action`
        left join `llx_user` on `llx_societe_action`.id_usr = `llx_user`.`rowid`
        left join `responsibility` on `responsibility`.`rowid`=`llx_user`.`respon_id`
        where 1 ";
        $sql .= " and `llx_societe_action`.`action_id` in (" . implode(',', $taskID) . ")";
        $sql .= "    and `llx_societe_action`.active = 1
        group by `llx_societe_action`.`action_id`, `responsibility`.`alias`;";
//  die($sql);
        $res_lastaction = $db->query($sql);
        if (!$res_lastaction) {
            dol_print_error($db);
        }
        if ($db->num_rows($res_lastaction) > 0) {
            while ($row = $db->fetch_object($res_lastaction)) {
                $alias = $row->alias;
                if($alias == $user->respon_alias && !empty($user->respon_alias2)) {
                    $alias = $user->respon_alias2;
                }
                if (!isset($lastaction[$row->rowid])) {
                    $date = new DateTime($row->dtChange);
                    $lastaction[$row->rowid] = $date->format('d.m.y');
//                    if($row->rowid == '35011'){
//                        var_dump($lastaction[$row->rowid]);
//                        die();
//                    }
                }
            }
        }
    }
//    echo '<pre>';
//    var_dump($lastaction);
//    echo '</pre>';
//    die();

    if($db->num_rows($res)>0)
        mysqli_data_seek($res,0);
    $out="<tbody id='actions'>";
    if($db->num_rows($res)==0){
        $out.='<tr class="pair">';
        $width = array(30,50,85,85,85,85,70,70,105,70,63,65,62,60,46,62,58,60,60,55,55,55);
        for($i=0;$i<22;$i++)
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
            $out.='<td class="small_size" style="text-align:center; width: 29px"><img src="/dolibarr/htdocs/theme/' . $conf->theme . '/img/' . $iconitem . '"></td>';//Вид дії
            $out.='<td class="small_size" style="width: 49px">'.$datec->format('d.m.y').'</td>';
            $out.='<td class="small_size" style="width: 84px">'.$langs->trans($responsibility[$obj->id_usr]['alias']).'</td>';
            $out.='<td class="small_size" style="width: 84px">'.$obj->lastname.'</td>';
            if(empty($_REQUEST['kind']) && $obj->id_usr != $user->id) {
                $out .= '<td class="small_size" style="width: 84px">' . $langs->trans($responsibility[$user->id]['alias']) . '</td>';
                $out .= '<td class="small_size" style="width: 84px">' . $user->lastname . '</td>';
            }
            else{
                $out .= '<td class="small_size" style="width: 84px">' . $langs->trans($responsibility[$obj->fk_element]['alias']) . '</td>';
                $out .= '<td class="small_size" style="width: 84px">' . $responsibility[$obj->fk_element]['lastname'] . '</td>';
            }
            $out.='<td class="small_size" style="width: 69px">'.$groupoftask[$obj->id].'</td>';
            $out.='<td class="small_size" style="width: 69px">'.(mb_strlen($obj->note, 'UTF-8')>10?mb_substr($obj->note, 0, 10, 'UTF-8').'...<input id="'.$obj->id.'" type="hidden" value="'.$obj->note.'">':$obj->note).'</td>';
            $out.='<td class="small_size" style="width: 104px">'.$obj->confirmdoc.'</td>';
            $date_prep='';
            if(!empty($obj->datepreperform))
                $date_prep = new DateTime($obj->datepreperform);
            $out.='<td class="small_size" style="width: 69px">'.(!empty($date_prep)?$date_prep->format('d.m.Y'):'').'</td>';
            $deadline = '';
            if(!empty($obj->datep2))
                $deadline = new DateTime($obj->datep2);
            $out.='<td style="width:62px" class="small_size">'.(!empty($deadline)?($deadline->format('d.m.y').'</br>'.$deadline->format('H:i')):'').'</td>';
            $date_confirm = '';
            if(!empty($obj->dateconfirm))
                $date_confirm = new DateTime($obj->dateconfirm);
            $out.='<td class="small_size" style="width: 64px">'.(!empty($date_confirm)?$date_confirm->format('d.m.Y'):'').'</td>';
            //Дії виконавця
            $lastactionvalue='';
            if(is_numeric($obj->id))
                $onclick = 'href="/dolibarr/htdocs/comm/action/chain_actions.php?action_id=' . $obj->id . '&mainmenu=' . $mainmenu . '"';
            else
                $onclick = 'onclick="EditConversation('.substr($obj->id,1).');"';

//            if($obj->id == '35011'){
//                var_dump($lastaction[$obj->id]);
//                die();
//            }
            if(is_numeric($obj->id)&&isset($lastaction[$obj->id]))
                $lastactionvalue = $lastaction[$obj->id];

//            if(is_numeric($obj->id)) {
                if (empty($lastactionvalue)) {
                    $lastactionvalue = '<img src="/dolibarr/htdocs/theme/eldy/img/object_action.png">';
                } else {
//                    if('35011'!=$obj->id) {
//                        var_dump($obj->id);
//                        die($lastaction[$obj->id]);
//                    }
                    $date = new DateTime($lastactionvalue);
                    $lastactionvalue = $date->format('d.m.');
                }
//            }
            $out .= '<td style="width:61px;text-align: center" class="small_size"><a '.$onclick.'>' . $lastactionvalue . '</a></td>';
            $out .= '<td style="width:59px;text-align: center"><a '.$onclick.'><img src="/dolibarr/htdocs/theme/eldy/img/object_action.png"></a></td>';
//            }
            $out .= '<td style="width:45px" class="middle_size">'.$obj->iMinute.'</td>';
            //Перевірка наставником
            $out .= '<td style="width:60px;text-align: center">'.(is_numeric($obj->id)?'<img src="/dolibarr/htdocs/theme/eldy/img/object_action.png"':'').'</td>';
            $out .= '<td style="width:60px;text-align: center">'.(is_numeric($obj->id)?'<img src="/dolibarr/htdocs/theme/eldy/img/object_action.png"':'').'</td>';
            //Період виконання
            $out .= '<td style="width:59px" class="small_size">'.mb_strtolower($langs->trans($obj->period), 'UTF-8').'</td>';
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
            $lastactionvalue='';
            if($obj->percent <= 98)
                $lastactionvalue = ($langs->trans($status));
            elseif($obj->percent == 99)
                $lastactionvalue = '<img src="/dolibarr/htdocs/theme/eldy/img/BWarning.png" title="Задачу виконано" style=width: 50px;">';
            else
                $lastactionvalue = 'Виконано';
            $out.='<td class="small_size" style="text-align: center; width:54px; '.($datep < $date&&$obj->percent <= 98?'background:rgb(255, 0, 0);':'').'">'.$lastactionvalue.'</td>';
            if ($obj->fk_user_author == $user->id && is_numeric($obj->id))
                $out .= '<td style=" text-align: center; width:60px; ">' . ($obj->percent != '100' ? ('<img src="/dolibarr/htdocs/theme/eldy/img/uncheck.png" onclick="ConfirmExec(' . $obj->id . ');" id="confirm' . $obj->id . '">') : '') . '</td>';
            else
                $out .= '<td  style="width:51px">&nbsp;</td>';
//            if(in_array($obj->code, array('AC_GLOBAL', 'AC_CURRENT', 'AC_CONVERSATION'))) {
//                if(is_numeric($obj->id))
//                    $onclick = 'href="/dolibarr/htdocs/comm/action/chain_actions.php?action_id=' . $obj->id . '&mainmenu=' . $mainmenu . '"';
//                else
//                    $onclick = 'onclick="PreviewConversation('.substr($obj->id,1).');"';
//                $out .= '<td id="'.$obj->id.'" style="text-align: center;  width:54px; "><a '.$onclick.'><img title="Переглянути" src="/dolibarr/htdocs/theme/eldy/img/preview.png"></a></td>';
//            }else
//                $out.='<td  >&nbsp;</td>';
            if($_REQUEST['kind'] == 'yourself'){
                $out .= '<td  style="width:51px; horiz-align: center">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" onclick="selAction($(this))" class="selectALL" id="checkbox_'.$obj->id.'"></td>';
                $out .= '<td  >&nbsp;</td>';
            }else {
                if ($obj->fk_user_author == $user->id) {
                    if (is_numeric($obj->id))
                        $onclick = 'EditAction(' . $obj->id . ', ' . "'" . $obj->code . "'" . ');';
                    else
                        $onclick = 'EditConversation(' . substr($obj->id, 1) . ');';
                    $out .= '<td  style="text-align: center;  width:54px;"><img id="img_' . $obj->id . '" src="/dolibarr/htdocs/theme/eldy/img/edit.png" style="cursor: pointer;" onclick="' . $onclick . '" title="' . (is_numeric($obj->id) ? 'Редагувати завдання' : 'Редагувати перемовини') . '"></td>';
                    if (is_numeric($obj->id))
                        $onclick = 'ConfirmDelTask(' . $obj->id . ');';
                    else
                        $onclick = 'DelConversation(' . substr($obj->id, 1) . ');';
                    $out .= '<td style="text-align: center;  width:34px;"><img title="' . (is_numeric($obj->id) ? 'Видалити завдання' : 'Видалити перемовини') . '" src="/dolibarr/htdocs/theme/eldy/img/delete.png" onclick="' . $onclick . '" id="confirm' . $obj->id . '"></td>';
                } else
                    $out .= '<td  >&nbsp;</td><td  >&nbsp;</td>';
            }
            $out.='</tr>';

        }
    }
    $out.="</tbody>";
    return $out;
}
