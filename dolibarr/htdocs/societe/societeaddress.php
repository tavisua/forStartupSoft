<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 15.11.2015
 * Time: 16:03
 */
require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';

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
print '
        <div class="tabs" data-role="controlgroup" data-type="horizontal">
            <div class="inline-block tabsElem tabsElemActive">
                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/soc.php?mainmenu=companies&idmenu='.$_REQUEST['idmenu'].'&action=edit&socid='.$object->id.'">'.$langs->trans('BasicInfo').'</a>
            </div>
            <div class="inline-block tabsElem">
                <a id="user" class="tabactive tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/societeaddress.php?mainmenu=companies&idmenu='.$_REQUEST['idmenu'].'&action=edit&socid='.$object->id.'">'.$langs->trans('AddressList').'</a>
            </div>
            <div class="inline-block tabsElem">
                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/societecontact.php?mainmenu=companies&idmenu='.$_REQUEST['idmenu'].'&action=edit&socid='.$object->id.'">'.$langs->trans('ContactList').'</a>
            </div>
            <div class="inline-block tabsElem">
                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/soc.php?mainmenu=companies&idmenu='.$_REQUEST['idmenu'].'&action=edit&socid='.$object->id.'">'.$langs->trans('EconomicData').'</a>
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
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('Country');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['sourcetable']='countries';
$ColParam['detailfield']='country_id';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('Region');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['sourcetable']='states';
$ColParam['detailfield']='state_id';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('Area');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['sourcetable']='regions';
$ColParam['detailfield']='region_id';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('KindOfLocality');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['sourcetable']='kindlocality';
$ColParam['detailfield']='kindlocality_id';
$TableParam[]=$ColParam;

unset($ColParam['sourcetable']);
unset($ColParam['detailfield']);

$ColParam['title']=$langs->trans('Location');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('KindOfStreet');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['sourcetable']='kindstreet';
$ColParam['detailfield']='kindofstreet_id';
$TableParam[]=$ColParam;

unset($ColParam['sourcetable']);
unset($ColParam['detailfield']);

$ColParam['title']=$langs->trans('Street');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('NumberOfHouse');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']= $langs->trans('KindOfOffice');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$ColParam['sourcetable']='kindoffice';
$ColParam['detailfield']='kindoffice_id';
$TableParam[]=$ColParam;

unset($ColParam['sourcetable']);
unset($ColParam['detailfield']);

$ColParam['title']=$langs->trans('NumberOfOffice');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('GPS');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('e-mail');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']=$langs->trans('Active');
$ColParam['width']='200';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$tablename = 'llx_societe_address';
$sql = 'select `llx_societe_address`.rowid, `llx_societe_address`.whom, `typeofaddress`.name as s_typeofaddress_name,
`llx_societe_address`.zip, `countries`.`label` as s_countries_label,
`states`.name as s_states_name, `regions`.name as s_regions_name, `kindlocality`.name as s_kindlocality_name,
`llx_societe_address`.location, `kindstreet`.name as s_kindstreet_name, `llx_societe_address`.street_name,
`llx_societe_address`.numberofhouse, `kindoffice`.name as s_kindoffice_name, `llx_societe_address`.numberofoffice,
`llx_societe_address`.gps,`llx_societe_address`.email,`llx_societe_address`.site, null,
`llx_societe_address`.workercount, "", `llx_societe_address`.sendpost,
`llx_societe_address`.sendemail, `llx_societe_address`.active
from `llx_societe_address`
left join typeofaddress on typeofaddress.`rowid` = `llx_societe_address`.kindaddress
left join `countries` on `countries`.`rowid` = `llx_societe_address`.country_id
left join `states` on `states`.`rowid` = `llx_societe_address`.state_id
left join `regions` on `regions`.`rowid` = `llx_societe_address`.state_id
left join `kindlocality` on `kindlocality`.`rowid` = `llx_societe_address`.kindlocality_id
left join `kindstreet` on `kindstreet`.`rowid` = `llx_societe_address`.kindofstreet_id
left join `kindoffice` on `kindoffice`.`rowid` = `llx_societe_address`.kindoffice_id
where fk_soc = '.$socid.' and `llx_societe_address`.active=1';

include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/DBManager/dbBuilder.php';
$db = new dbBuilder();


$table = $db->fShowTable($TableParam, $sql, "'" . $tablename . "'", $conf->theme, '', '', $readonly = array(), false);

include DOL_DOCUMENT_ROOT.'/theme/'.$conf->theme.'/societeaddress.html';

llxFooter();