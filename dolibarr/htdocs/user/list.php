<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 18.05.2016
 * Time: 11:32
 */
require $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
$form = new Form($db);
$object = new User($db);


//$table = ShowTable();

$HourlyPlan = $langs->trans('Coworkers');
llxHeader("",$HourlyPlan,"");
print_fiche_titre($langs->trans('Coworkers'));
if($_REQUEST['list']=='contactlist') {
    $tbody = showDictActionToAddress();
    include $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/theme/eldy/users/contactlist.html';
}else {
    $tbody = showUserList();
    include $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/theme/eldy/users/userlist.html';
}
exit();
function showUserList(){

    global $db,$langs;
    $sql = "select `llx_user`.rowid, `llx_user`.login email, `llx_user`.lastname, `llx_user`.firstname,  `llx_user`.`office_phone`, `llx_user`.`skype`,
        `subdivision`.`name` as s_subdivision_name, `llx_usergroup`.`nom` as s_llx_usergroup_nom, `responsibility`.alias, `llx_post`.`postname`
        from `llx_user` left join `subdivision` on `llx_user`.`subdiv_id`= `subdivision`.rowid
        left join `llx_usergroup` on `llx_user`.`usergroup_id`=`llx_usergroup`.rowid
        left join `responsibility` on `responsibility`.rowid = `llx_user`.respon_id
        left join `llx_post` on `llx_post`.`rowid` = `llx_user`.`post_id`
        where `llx_user`.active=1
        and `subdivision`.`name` is not null
        and `llx_user`.login not in ('test')";
//echo '<pre>';
//var_dump($_POST);
//echo '</pre>';
//die();
    if(isset($_POST["subdiv_id"])&&!empty($_POST["subdiv_id"]))
        $sql.=" and `llx_user`.`subdiv_id`=".$_POST["subdiv_id"];
    if(isset($_POST["respon_id"])&&!empty($_POST["respon_id"]))
        $sql.=" and `llx_user`.`respon_id`=".$_POST["respon_id"];
    if(isset($_POST["post_id"])&&!empty($_POST["post_id"]))
        $sql.=" and `llx_user`.`post_id`=".$_POST["post_id"];
    if(isset($_POST["lastname"])&&!empty($_POST["lastname"]))
        $sql.=" and `llx_user`.`lastname` like '%".$_POST["lastname"]."%'";
    if(isset($_POST["firstname"])&&!empty($_POST["firstname"]))
        $sql.=" and `llx_user`.`firstname` like '%".$_POST["firstname"]."%'";


    $sql.=" order by s_subdivision_name, `llx_user`.`lastname`, `llx_user`.`firstname`";
   $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $out='<tbody id="userlist">';
    $count=0;
    $symbols = explode(',', '(,), ,+,-');

    while($obj = $db->fetch_object($res)){
        $class = fmod($count, 2) != 1 ? ("impair") : ("pair");
        $out.='<tr id="rowid_'.$obj->rowid.'" class="'.$class.'">';
        $out.='<td class="middle_size" style="width:152px"id="s_subdivision_name_'.$obj->rowid.'">'.$obj->s_subdivision_name.'</td>';
        $out.='<td class="middle_size" style="width:186px"id="alias_'.$obj->rowid.'">'.$langs->trans($obj->alias).'</td>';
        $out.='<td class="middle_size" style="width:152px"id="postname_'.$obj->rowid.'">'.$obj->postname.'</td>';
        $out.='<td class="middle_size" style="width:148px"id="lastname_'.$obj->rowid.'"><a href="/dolibarr/htdocs/user/useractions.php?id_usr='.$obj->rowid.'">'.$obj->lastname.'</a></td>';
        $out.='<td class="middle_size" style="width:147px"id="firstname_'.$obj->rowid.'">'.$obj->firstname.'</td>';
        $phone = str_replace($symbols,'', $obj->office_phone);
        if(!empty($obj->office_phone)) {
            $phonelink = '<a onclick="Call('.$phone.', '."'users'".', '.$obj->rowid.');">';
            $out .= '<td class="middle_size" style="width:148px"><table class="phone"><tbody><tr>';
            $out .= '<td class="middle_size" style="width:130px"= id="office_phone_' . $obj->rowid . '">' . $phonelink . $obj->office_phone . '</a></td>';
            $out .= '<td class="middle_size" id="sms_' . $obj->rowid . '" onclick="showSMSform(' . $phone . ');" style="width: 20px"><img src="/dolibarr/htdocs/theme/eldy/img/object_sms.png"></td>';
            $out .= '</tr></tbody></table></td>';
        }else{
            $out.='<td></td>';
        }
        $out.='<td class="middle_size" style="width:205px"id="email_'.$obj->rowid.'"><a href="mailto:'.$obj->email.'">'.$obj->email.'</a></td>';
        $out.='<td class="middle_size" style="width:148px"id="skype_'.$obj->rowid.'"><a href="skype:'.$obj->skype.'?call">'.$obj->skype.'</a></td>';
        $out.='<td style="width:127px" class="emptycol">&nbsp;</td>';
        $out.='</tr>';
        $count++;
    }
    $out.='</tbody>';
    return $out;
}
function showDictActionToAddress(){
    global $db;
    $sql = "select `llx_c_actiontoaddress`.`rowid`,  `llx_c_groupoforgissues`.`issues` fk_groupissues, case when `llx_c_actiontoaddress`.`fk_subdivision` = -1 then 'Всі підрозділи' else `subdivision`.`name` end fk_subdivision,
    `llx_c_actiontoaddress`.`action`, `llx_c_actiontoaddress`.`responsible`,`llx_c_actiontoaddress`.`directly_responsible`
	from llx_c_actiontoaddress
	left join `llx_c_groupoforgissues` on `llx_c_groupoforgissues`.`rowid` = `llx_c_actiontoaddress`.`fk_groupissues`
	left join `subdivision` on `subdivision`.`rowid` = `llx_c_actiontoaddress`.`fk_subdivision`
	where 1 ";
    if(isset($_POST["fk_groupissues"])&&$_POST["fk_groupissues"]!='-1'){//Група завдань
        $sql.=" and `llx_c_groupoforgissues`.`rowid` = ".$_POST["fk_groupissues"];
    }
    if(isset($_POST["fk_subdivision"])&&$_POST["fk_subdivision"]>0){//Підрозділ
        $sql.=" and `subdivision`.`rowid` = ".$_POST["fk_subdivision"];
    }
    if(isset($_POST["action"])&&!empty($_POST["action"])) {//Підрозділ
        $sql.=" and `llx_c_actiontoaddress`.`action` like '%".$_POST["action"]."%'";
    }
    if(isset($_POST["responsible"])&&!empty($_POST["responsible"])) {//Відповідальний
        $sql.=" and `llx_c_actiontoaddress`.`responsible` like '%".$_POST["responsible"]."%'";
    }
    if(isset($_POST["responsible"])&&!empty($_POST["responsible"])) {//Відповідальний
        $sql.=" and `llx_c_actiontoaddress`.`directly_responsible` like '%".$_POST["directly_responsible"]."%'";
    }
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $out='<tbody id="dict">';
    $count=0;
    while($obj = $db->fetch_object($res)){
        $class = fmod($count, 2) != 1 ? ("impair") : ("pair");
        $out.='<tr id="rowid_'.$obj->rowid.'" class="'.$class.'">';
        $out.='<td style="width: 205px"id="fk_groupissues_'.$obj->rowid.'">'.$obj->fk_groupissues.'</td>';
        $out.='<td style="width: 143px" id="fk_subdivision_'.$obj->rowid.'">'.$obj->fk_subdivision.'</td>';
        $out.='<td style="width: 205px" id="action_'.$obj->rowid.'">'.$obj->action.'</td>';
        $out.='<td style="width: 200px"id="responsible_'.$obj->rowid.'">'.$obj->responsible.'</td>';
        $out.='<td style="width: 233px" id="directly_responsible_'.$obj->rowid.'">'.$obj->directly_responsible.'</td>';
        $out.='<td style="width: 127px">&nbsp;</td>';
        $out.='</tr>';
        $count++;
    }
    $out.='</tbody>';
    return $out;
}

