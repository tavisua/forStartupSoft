<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 14.06.2016
 * Time: 12:16
 */
global $user,$db,$langs;
require_once $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/main.inc.php';


if(isset($_GET['action'])&&$_GET['action']=='getRegionsStatistic'){
    echo getRegionsStatistic($_GET['state_id']);
    exit();
}
if(isset($_GET['action'])&&$_GET['action']=='getContactsStatistic'){
//    llxHeader();
//    echo '<table><tbody>'.getContactsStatistic($_GET['region_id']).'</tbody></table>';
    echo getContactsStatistic($_GET['region_id']);
    exit();
}

if(empty($_GET['page'])||$_GET == 'proposition'){
    $Title = $langs->trans('Proposition');
//$tbody = callStatistic();
    $thead = getPropositionHead();
    $tbody = getPropositionBody();
    $tbodyPropositionByPost = getPropositionByPost();
    include DOL_DOCUMENT_ROOT . '/theme/eldy/performance/proposition.html';
}
llxPopupMenu();
exit();
function getPropositionByPost(){
    global $db;
    $sql = "select  distinct `llx_post`.`rowid`, `llx_post`.`postname`
        from  `llx_c_proposition`
        inner join llx_proposition_properties on llx_proposition_properties.fk_proposition = `llx_c_proposition`.rowid
        inner join `llx_post` on `llx_post`.`rowid` = `llx_proposition_properties`.`fk_post`
        where 1
        and ((`llx_c_proposition`.`end` is not null and Now() between `llx_c_proposition`.`begin` and `llx_c_proposition`.`end`) or `llx_c_proposition`.`end` is null)
        order by postname";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $out = '';
    $count = 0;
    while($obj = $db->fetch_object($res)){
        $count++;
        $class = fmod($count,2)==1?("impair"):("pair");
        $out.='<tr class="'.$class.'">';
        $out.='<td class="middle_size">'.$obj->postname.'</td>';
        $out.='<td onclick="showTitleProposed('.$obj->rowid.',1,0, proposed'.$obj->rowid.');" style="width: 20px" id="proposed'.$obj->rowid.'">
        <img src="/dolibarr/htdocs/theme/eldy/img/strawberry.png" title="Заголовок"></td>';
        $out.='</tr>';
    }
    return $out;
}
function getContactsStatistic($region_id){
    global $db;
    $sql="select min(llx_c_proposition.rowid) rowid
        from llx_societe_action
        inner join `llx_c_proposition` on `llx_c_proposition`.`rowid` = llx_societe_action.proposed_id
        where 1 and `llx_c_proposition`.`active` = 1
        and llx_societe_action.active = 1
        and llx_societe_action.callstatus = 5
        group by substring(`llx_c_proposition`.`text`,1,10);";
    $res = $db->query($sql);
    $propositionID = array();
    while($obj = $db->fetch_object($res)){
        $propositionID[]=$obj->rowid;
    }
    $analogID=array();
    foreach($propositionID as $item){
        $sql = "select rowid from `llx_c_proposition`
            where active = 1
            and substring(`llx_c_proposition`.`text`,1,10) in
            (select substring(`llx_c_proposition`.`text`,1,10) prop from `llx_c_proposition`
            where rowid = ".$item.")";
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
        while($obj = $db->fetch_object($res)){
            if(!isset($analogID[$item])||!in_array($obj->rowid, $analogID[$item]))
                $analogID[$item][]=$obj->rowid;
        }
    }
    $sql = "select case when llx_societe_contact.rowid is not null then llx_societe_contact.rowid else 0 end rowid,
        case when llx_societe_contact.rowid is not null then concat('&#34;',llx_societe.nom,'&#34; ',case when llx_societe_contact.lastname is null then '' else llx_societe_contact.lastname end, case when length(llx_societe_contact.lastname)>0 then ' ' else '' end,
        llx_societe_contact.firstname) else 'Не встановлено' end lastname, llx_societe_action.proposed_id from llx_societe
        inner join llx_societe_action on llx_societe.rowid = llx_societe_action.socid
        left join llx_societe_contact on llx_societe_action.contactid = llx_societe_contact.rowid
        where region_id = ".$region_id."
        and proposed_id is not null
        and llx_societe.active = 1
        and llx_societe_action.callstatus = 5
        order by lastname";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $contacts = array();
    while($obj = $db->fetch_object($res)){
        $contacts[$obj->rowid][]=$obj->proposed_id;
    }
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();
    mysqli_data_seek($res,0);
    $out='';
    $lastID=-1;
    while($obj = $db->fetch_object($res)){
        if($lastID!=$obj->rowid) {
            $dSum = array();
            $out .= '<tr id="contact_' . $obj->rowid . '" class="lineactive contact_' . $region_id . ' region">';
            $out .= '<td>' . $obj->lastname . '</td>';
            $out .= '<td></td>';
            $date = new DateTime($obj->dtChange);
            foreach($propositionID as $prop_id){
                foreach($contacts[$obj->rowid] as $item){
                    if(in_array($item, $analogID[$prop_id])){
                        $dSum[$prop_id]++;
                    }
                }
                if(count($dSum)>0) {
                    $out .= '<td id="' . $obj->rowid . '_' . $item . '" class="middle_size" style="text-align: center">' . $dSum[$prop_id] . '</td>';
                }else{
                    $out .= '<td id="' . $obj->rowid . '_' . $item . '" class="middle_size" style="text-align: center"></td>';
                }
            }

            $out .= '<td class="middle_size" style="text-align: center">' . array_sum($dSum) . '</td>';
            $out .= '</tr>';
            $lastID=$obj->rowid;
        }
    }
    return $out;
}
function getRegionsStatistic($state_id){
    global $db;
    $sql = "select llx_societe.region_id, min(llx_societe_action.proposed_id) proposed_id, count(*) iCount
        from llx_societe_action
        inner join `llx_c_proposition` on `llx_c_proposition`.`rowid` = llx_societe_action.proposed_id
        inner join llx_societe on llx_societe.rowid = llx_societe_action.socid
        where 1 and `llx_c_proposition`.`active` = 1
        and llx_societe.state_id = ".$state_id."
        and llx_societe_action.active = 1
        and llx_societe_action.callstatus = 5
        group by llx_societe.region_id, substring(`llx_c_proposition`.`text`,1,10);";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $regions = array();
    while($obj = $db->fetch_object($res)){
        $regions[$obj->region_id][$obj->proposed_id] = $obj->iCount;
    }
//    echo '<pre>';
//    var_dump($sql);
//    echo '</pre>';
//    die();
    $sql="select min(llx_c_proposition.rowid) rowid
        from llx_societe_action
        inner join `llx_c_proposition` on `llx_c_proposition`.`rowid` = llx_societe_action.proposed_id
        where 1 and `llx_c_proposition`.`active` = 1
        and llx_societe_action.active = 1
        and llx_societe_action.callstatus = 5
        group by substring(`llx_c_proposition`.`text`,1,10);";
    $res = $db->query($sql);
    $propositionID = array();
    while($obj = $db->fetch_object($res)){
        $propositionID[]=$obj->rowid;
    }
    $analogID=array();
    foreach($propositionID as $item){
        $sql = "select rowid from `llx_c_proposition`
            where active = 1
            and substring(`llx_c_proposition`.`text`,1,10) in
            (select substring(`llx_c_proposition`.`text`,1,10) prop from `llx_c_proposition`
            where rowid = ".$item.")";
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
        while($obj = $db->fetch_object($res)){
            if(!isset($analogID[$item])||!in_array($obj->rowid, $analogID[$item]))
                $analogID[$item][]=$obj->rowid;
        }
    }
    $sql = "select rowid,`name` from regions
        where state_id = ".$state_id."
        and active = 1
        order by `name`";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $out='';
    while($obj = $db->fetch_object($res)){
        $out.='<tr id="region_'.$obj->rowid.'" class="state_'.$state_id.' region middle_size">';
        $out.='<td>'.$obj->name.'</td>';
//        $out.='<td></td>';
        $out.='<td><button id="btnRegion'.$obj->rowid.'" onclick="PropositionByContacts('.$obj->rowid.');"><img id="imgRegion'.$obj->rowid.'" src="/dolibarr/htdocs/theme/eldy/img/1downarrow.png"></button></td>';
        $dSum = 0;
        foreach($propositionID as $item){
            $value = '';
            if(isset($regions[$obj->rowid][$item]))
                $value = $regions[$obj->rowid][$item];
            else{
                foreach($analogID[$item] as $key){
                    if(isset($regions[$obj->rowid][$key])){
                        $value = $regions[$obj->rowid][$key];
                    }
                }
            }
            $dSum+=$value;
            $out.='<td id="'.$obj->rowid.'_'.$item.'" class="middle_size" style="text-align: center">'.$value.'</td>';
        }
        $out.='<td class="middle_size" style="text-align: center">'.$dSum.'</td>';
        $out.='</tr>';
    }
    return $out;
}
function getPropositionBody(){
    global $db;
    $sql = "select min(llx_c_proposition.rowid) rowid, count(*) iCount
        from llx_societe_action
        inner join `llx_c_proposition` on `llx_c_proposition`.`rowid` = llx_societe_action.proposed_id
        where 1 and `llx_c_proposition`.`active` = 1
        and llx_societe_action.active = 1
        and llx_societe_action.callstatus = 5
        group by substring(`llx_c_proposition`.`text`,1,40);";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $propositionID = array();
    while($obj = $db->fetch_object($res)){
        $propositionID[]=$obj->rowid;
    }
    $out='';
    //Вибираю статистику по областям
    $sql = "select llx_societe.state_id, min(llx_societe_action.proposed_id) proposed_id, count(*) iCount
        from llx_societe_action
        inner join `llx_c_proposition` on `llx_c_proposition`.`rowid` = llx_societe_action.proposed_id
        inner join llx_societe on llx_societe.rowid = llx_societe_action.socid
        where 1 and `llx_c_proposition`.`active` = 1
        and llx_societe_action.active = 1
        and llx_societe_action.callstatus = 5
        group by llx_societe.state_id, substring(`llx_c_proposition`.`text`,1,40);";
    $states = array();
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)){
        $states[$obj->state_id][$obj->proposed_id]=$obj->iCount;
    }
//    echo '<pre>';
//    var_dump($states[12]);
//    echo '</pre>';
//    die();
    $sql="select rowid, `name` from states
        where active = 1
        union
        select '', 'Область не встановлено'
        order by `name`";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $num=0;
    while($obj = $db->fetch_object($res)){
        $class= fmod($num,2)==0?'impair':'pair';
        $out.='<tr id="'.$obj->rowid.'" class="'.$class.'">';
        $out.='<td>'.$obj->name.'</td>';
//        $out.='<td></td>';
        $out.='<td><button id="btnState'.$obj->rowid.'" onclick="PropositionByRegions('.$obj->rowid.');"><img id="imgState'.$obj->rowid.'" src="/dolibarr/htdocs/theme/eldy/img/1downarrow.png"></button></td>';
        $dSum = 0;
        foreach($propositionID as $item){
            $dSum+=$states[$obj->rowid][$item];
            $out.='<td class="middle_size" style="text-align: center">'.$states[$obj->rowid][$item].'</td>';
        }
        $out.='<td class="middle_size" style="text-align: center">'.$dSum.'</td>';
        $out.='</tr>';
        $num++;
    }
    return $out;
}
function getPropositionHead(){
    global $db;
    $sql="select count(distinct llx_societe.rowid)iCount from llx_societe
        inner join `llx_societe_contact` on `llx_societe_contact`.`socid` = llx_societe.rowid
        where llx_societe.`categoryofcustomer_id` = 5 and llx_societe.active = 1
        and `llx_societe_contact`.`post_id` in (select  distinct fk_post from `llx_c_proposition` where active = 1)";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    $obj = $db->fetch_object($res);
    $CustCount = $obj->iCount;
    $sql = "select count(*) iCount
        from llx_societe_action
        inner join `llx_c_proposition` on `llx_c_proposition`.`rowid` = llx_societe_action.proposed_id
        where 1 and `llx_c_proposition`.`active` = 1
        and llx_societe_action.active = 1
        group by substring(`llx_c_proposition`.`text`,1,40);";
    $res = $db->query($sql);
//    if(!$res)
//        dol_print_error($db);
//    $obj = $db->num_rows($res);
//    echo $CustCount.'</br>';
    $CustCount*=$db->num_rows($res);
//    echo $CustCount.'</br>';
    $out = '<tr class="multiple_header_table"><th rowspan="2" style="width: 250px">Область</th><th rowspan="2" style="width: 35px">&nbsp;</th>';
    $sql = "select trim(`llx_c_proposition`.`text`)text, min(llx_c_proposition.rowid) rowid, count(*) iCount
        from llx_societe_action
        inner join `llx_c_proposition` on `llx_c_proposition`.`rowid` = llx_societe_action.proposed_id
        where 1 and `llx_c_proposition`.`active` = 1
        and llx_societe_action.active = 1
        group by substring(`llx_c_proposition`.`text`,1,40);";
    $res = $db->query($sql);
    if(!$res)
        dol_print_error($db);
    while($obj = $db->fetch_object($res)){
//        echo htmlspecialchars($obj->text).'</br>';
//        var_dump(mb_strlen($obj->text, 'UTF-8')>10);
//        die('eee');
        $value = str_replace('"','&#34;',$obj->text);
        if(mb_strlen($obj->text, 'UTF-8')>5)
            $out.='<th id="'.$obj->rowid.'">'.mb_substr($value, 0, 5, 'UTF-8').'...<input type="hidden" id="title_'.$obj->rowid.'" value="'.$value.'"></th>';
        else
            $out.='<th id="'.$obj->rowid.'">'.mb_strlen($value, 'UTF-8').'</th>';
    }
    $out.='<th>Всього</th>';
    $out.='</tr><tr  class="multiple_header_table">';
    mysqli_data_seek($res,0);
    $dSum=0;
    while($obj = $db->fetch_object($res)) {
        $out.='<th class="percent">'.$obj->iCount.' ('.round($obj->iCount*100/$CustCount,3).'%)</th>';
        $dSum+=$obj->iCount;
    }
    $out.='<th class="percent">'.$dSum.' ('.round($dSum*100/$CustCount,3).'%)</th>';
    $out.='</tr>';
//    var_dump(htmlspecialchars($out));
//    die('test');
    return $out;
}