<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 15.11.2015
 * Time: 16:03
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
global $db,$conf,$langs;

$socid = GETPOST('socid', 'int');
if(empty($socid))
    $socid = $_REQUEST['socid'];
$Address = $langs->trans("AddressList");
llxHeader('',$Address,$help_url);
print_fiche_titre($Address);
$object = new Societe($db);
$object->fetch($socid);

$CategoryOfCustomer = $object->getCategoryOfCustomer();
$FormOfGoverment = $object->getFormOfGoverment();
$NewItem = $langs->trans('NewItem');
$Control = $langs->trans('Control');
$AddAddress = $langs->trans('AddAddress');
if(!empty($_REQUEST['action']) && $_REQUEST['action'] == 'edit')
    $action = '&action=edit';
else
    $action = '';
print '
        <div class="tabs" data-role="controlgroup" data-type="horizontal">
            <div class="inline-block tabsElem tabsElemActive">
                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/soc.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].$action.'&socid='.$object->id.'">'.$langs->trans('BasicInfo').'</a>
            </div>
            <div class="inline-block tabsElem">
                <a id="user" class="tabactive tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/societeaddress.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].$action.'&socid='.$object->id.'">'.$langs->trans('AddressList').'</a>
            </div>
            <div class="inline-block tabsElem">
                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/societecontact.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].$action.'&socid='.$object->id.'">'.$langs->trans('ContactList').'</a>
            </div>';
            $sql = "select case when `responsibility_param`.`fx_category_counterparty` is null then `responsibility_param`.`other_category` else `responsibility_param`.`fx_category_counterparty` end category_id, `responsibility`.`alias` from `responsibility`
                inner join `responsibility_param` on `responsibility_param`.`fx_responsibility` = `responsibility`.`rowid`
                where `responsibility`.`alias` in ('sale','purchase','marketing')";
            $res = $db->query($sql);
            if(!$res)
                dol_print_error($db);
            $sales_category = array();
            $purchase_category = array();
            $marketing_category = array();
            while($obj = $db->fetch_object($res)){
                if(!empty($obj->category_id)) {
                    switch ($obj->alias) {
                        case 'sale': {
                            $sales_category[] = $obj->category_id;
                        }
                            break;
                        case 'purchase': {
                            $purchase_category[] = $obj->category_id;
                        }
                            break;
                        case 'marketing': {
                            $marketing_category[] = $obj->category_id;
                        }
                            break;
                    }
                }
            }

                print '<div class="inline-block tabsElem">
                                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/economin_indicator.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].$action.'&socid='.$object->id.'">'.$langs->trans('EconomicData').'</a>
                            </div>';
            if(in_array($object->categoryofcustomer_id, $purchase_category)||in_array($object->categoryofcustomer_id, $marketing_category)) {
                print '<div class="inline-block tabsElem">
                                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/lineactive.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].$action.'&socid='.$object->id.'">'.$langs->trans('LineActive').'</a>
                            </div>';
            }
            print '<div class="inline-block tabsElem">
                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/finance.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].'&socid='.$object->id.'">'.$langs->trans('FinanceAndDetails').'</a>
            </div>
            <div class="inline-block tabsElem">
                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/partners.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].'&socid='.$object->id.'">'.$langs->trans('PartnersOfCustomer').'</a>
            </div>
        </div>';
//var_dump();
//die();
$TableParam = array();
$ColParam['title']=$langs->trans('Whom');
$ColParam['width']='450';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('KindAddress');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['sourcetable']='typeofaddress';
$ColParam['detailfield']='kindaddress';
$TableParam[]=$ColParam;
unset($ColParam['sourcetable']);
unset($ColParam['detailfield']);

$ColParam['title']=$langs->trans('Zip');
//$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('Country');
//$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['sourcetable']='countries';
$ColParam['detailfield']='country_id';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('Region');
//$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['sourcetable']='states';
$ColParam['detailfield']='state_id';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('Area');
//$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['sourcetable']='regions';
$ColParam['detailfield']='region_id';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('KindOfLocality');
//$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['sourcetable']='kindlocality';
$ColParam['detailfield']='kindlocality_id';
$TableParam[]=$ColParam;

unset($ColParam['sourcetable']);
unset($ColParam['detailfield']);

$ColParam['title']=$langs->trans('Location');
//$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('KindOfStreet');
//$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['sourcetable']='kindstreet';
$ColParam['detailfield']='kindofstreet_id';
$TableParam[]=$ColParam;

unset($ColParam['sourcetable']);
unset($ColParam['detailfield']);

$ColParam['title']=$langs->trans('Street');
//$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('NumberOfHouse');
//$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']= $langs->trans('KindOfOffice');
//$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['sourcetable']='kindoffice';
$ColParam['detailfield']='kindoffice_id';
$TableParam[]=$ColParam;

unset($ColParam['sourcetable']);
unset($ColParam['detailfield']);

$ColParam['title']=$langs->trans('NumberOfOffice');
//$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('GPS');
//$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

//$ColParam['title']=$langs->trans('e-mail');
//$ColParam['width']='200';
//$ColParam['align']='';
//$ColParam['class']='';
//$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('Active');
//$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$tablename = 'llx_societe_address';
$sql = 'select `llx_societe_address`.rowid, `llx_societe_address`.whom, `typeofaddress`.name as s_typeofaddress_name,
`llx_societe_address`.zip, `countries`.`label` as s_countries_label,
`states`.name as s_states_name, `regions`.name as s_regions_name, `kindlocality`.name as s_kindlocality_name,
`llx_societe_address`.location, `kindstreet`.name as s_kindstreet_name, `llx_societe_address`.street_name,
`llx_societe_address`.numberofhouse, `kindoffice`.name as s_kindoffice_name, `llx_societe_address`.numberofoffice,
`llx_societe_address`.gps, null,
`llx_societe_address`.workercount, "", `llx_societe_address`.sendpost, `llx_societe_address`.active
from `llx_societe_address`
left join typeofaddress on typeofaddress.`rowid` = `llx_societe_address`.kindaddress
left join `countries` on `countries`.`rowid` = `llx_societe_address`.country_id
left join `states` on `states`.`rowid` = `llx_societe_address`.state_id
left join `regions` on `regions`.`rowid` = `llx_societe_address`.region_id
left join `kindlocality` on `kindlocality`.`rowid` = `llx_societe_address`.kindlocality_id
left join `kindstreet` on `kindstreet`.`rowid` = `llx_societe_address`.kindofstreet_id
left join `kindoffice` on `kindoffice`.`rowid` = `llx_societe_address`.kindoffice_id
where fk_soc = '.$socid.' and `llx_societe_address`.active=1';
//echo '<pre>';
//var_dump($sql);
//echo '</pre>';
//die();

//include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/DBManager/dbBuilder.php';
//$dbBuilder = new dbBuilder();

$table = ShowTable($socid);
//$table = $dbBuilder->fShowTable($TableParam, $sql, "'" . $tablename . "'", $conf->theme, '', '', $readonly = array(), false);
if(!empty($_REQUEST['action'])&& $_REQUEST['action'] == 'edit'){
    $controlbtn = '<div class="address_header" id="addBtn" style="padding-top: 20px">
            <div class="blockvmenupair">
                <div class="menu_titre">
                    <b><?echo $Control?></b>
                </div>
                <div class="menu_top"></div>
                <div class="menu_contenu">
                    <form action="/dolibarr/htdocs/societe/addaddress.php" method="post">
                        <input id="url" name="url" type="hidden" value="'.$_SERVER['REQUEST_URI'].'">
                        <input id="mainmenu" name="mainmenu" type="hidden" value="'.$_REQUEST['mainmenu'].'">
                        <input id="idmenu" name="idmenu" type="hidden" value="'.$_REQUEST['idmenu'].'">
                        <input id="socid" name="socid" type="hidden" value="'.$socid.'">
                        <input id="action" name="action" type="hidden" value="add">
                        <button type="submit">&nbsp;&nbsp;&nbsp;&nbsp;'.$AddAddress.'&nbsp;&nbsp;&nbsp;&nbsp;</button>
                    </form>
                </div>
                <div class="menu_end"></div>
            </div>
        </div>';
}else{
    $controlbtn = '<div class="address_header" id="addBtn" style="padding-top: 20px">
        </div>';
}
include DOL_DOCUMENT_ROOT.'/theme/'.$conf->theme.'/societeaddress.html';

llxFooter();
exit();
function ShowTable($socid){
//    require $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/main.inc.php';
    global $db,$conf;
//    $sql = "select * from llx_user";
    $sql = 'select `llx_societe_address`.rowid, `llx_societe_address`.whom, `typeofaddress`.name as s_typeofaddress_name,
        `llx_societe_address`.zip, `countries`.`label` as s_countries_label,
        `states`.name as s_states_name, `regions`.name as s_regions_name, `kindlocality`.name as s_kindlocality_name,
        `llx_societe_address`.location, `kindstreet`.name as s_kindstreet_name, `llx_societe_address`.street_name,
        `llx_societe_address`.numberofhouse, `kindoffice`.name as s_kindoffice_name, `llx_societe_address`.numberofoffice,
        `llx_societe_address`.gps, null,
        `llx_societe_address`.workercount, "", `llx_societe_address`.sendpost, `llx_societe_address`.active
        from `llx_societe_address`
        left join typeofaddress on typeofaddress.`rowid` = `llx_societe_address`.kindaddress
        left join `countries` on `countries`.`rowid` = `llx_societe_address`.country_id
        left join `states` on `states`.`rowid` = `llx_societe_address`.state_id
        left join `regions` on `regions`.`rowid` = `llx_societe_address`.region_id
        left join `kindlocality` on `kindlocality`.`rowid` = `llx_societe_address`.kindlocality_id
        left join `kindstreet` on `kindstreet`.`rowid` = `llx_societe_address`.kindofstreet_id
        left join `kindoffice` on `kindoffice`.`rowid` = `llx_societe_address`.kindoffice_id
        where fk_soc = '.$socid;
    $res = $db->query($sql);
//echo '<pre>';
//var_dump($sql);
//echo '</pre>';
//die();

    if(!$res)
        dol_print_error($db);



    $out='<tbody>';
    $num = 0;

    while($row = $db->fetch_array($res)){
        foreach (array_keys($row) as $key){
            if(is_numeric($key)){
                unset($row[$key]);
            }
        }
        $obj = (object)$row;
//        echo '<pre>';
//        var_dump($obj->sendpost == 1);
//        echo '</pre>';
//        die();
        $out.='<tr id="'.$obj->rowid.'" class = "'.(fmod($num++, 2)==0?'impair':'pair').' small_size">
            <td>'.$obj->whom.'</td>
            <td>'.$obj->s_typeofaddress_name.'</td>
            <td>'.$obj->zip.'</td>
            <td>'.$obj->s_countries_label.'</td>
            <td>'.$obj->s_states_name.'</td>
            <td>'.$obj->s_regions_name.'</td>
            <td>'.$obj->s_kindlocality_name.'</td>
            <td>'.$obj->location.'</td>
            <td>'.$obj->s_kindstreet_name.'</td>
            <td>'.$obj->street_name.'</td>
            <td>'.$obj->numberofhouse.'</td>
            <td>'.$obj->s_kindoffice_name.'</td>
            <td>'.$obj->numberofoffice.'</td>
            <td>'.$obj->s_kindoffice_name.'</td>
            <td>'.$obj->numberofoffice.'</td>
            <td>'.$obj->gps.'</td>
            <td>'.$obj->workercount.'</td>';
        if(!empty($_REQUEST['action'])&& $_REQUEST['action'] == 'edit') {
            if ($obj->sendpost == 1) {
                $out .= '<td class = "switch" id="' . $obj->rowid . 'sendpost"> <img id="img' . $obj->rowid . 'sendpost" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/switch_on.png" onclick="change_switch(' . $obj->rowid . ', ' . "'llx_societe_address'" . ', ' . "'sendpost'" . ');" > </td>';
            } else {
                $out .= '<td class = "switch" id="' . $obj->rowid . 'sendpost"> <img id="img' . $obj->rowid . 'sendpost" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/switch_off.png" onclick="change_switch(' . $obj->rowid . ', ' . "'llx_societe_address'" . ', ' . "'sendpost'" . ');"> </td>';
            }
            if ($obj->active == 1) {
                $out .= '<td class = "switch" id="' . $obj->rowid . 'sendpost"> <img id="img' . $obj->rowid . 'sendpost" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/switch_on.png" onclick="change_switch(' . $obj->rowid . ', ' . "'llx_societe_address'" . ', ' . "'active'" . ');" > </td>';
            } else {
                $out .= '<td class = "switch" id="' . $obj->rowid . 'sendpost"> <img id="img' . $obj->rowid . 'sendpost" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/switch_off.png" onclick="change_switch(' . $obj->rowid . ', ' . "'llx_societe_address'" . ', ' . "'active'" . ');"> </td>';
            }
            $out .= '<td class = "switch" id="' . $obj->rowid . 'edit"> <img id="img' . $obj->rowid . 'sendpost" src="' . DOL_URL_ROOT . '/theme/' . $conf->theme . '/img/edit.png" onclick="edit(' . $obj->rowid . ');"> </td>';
        }else{
            $out .= '<td></td><td></td><td></td>';
        }
            $out.='</tr>';
    }
    $out.='</tbody>';
    return $out;
}