<?php
/* Copyright (C) 2005     	Patrick Rouillon    <patrick@rouillon.net>
 * Copyright (C) 2005-2011	Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012	Regis Houssin       <regis.houssin@capnetworks.com>
 * Copyright (C) 2011-2012	Philippe Grand      <philippe.grand@atoo-net.com>
 * Copyright (C) 2014		Charles-Fr Benke	<charles.fr@benke.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *     \file       htdocs/societe/societecontact.php
 *     \ingroup    societe
 *     \brief      Onglet de gestion des contacts additionnel d'une société
 */

require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/societe/societecontact_class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';

$socid = GETPOST('socid', 'int');
if(empty($socid))
    $socid = $_REQUEST['socid'];
$ContactList = $langs->trans("ContactList");
llxHeader('',$ContactList,$help_url);
print_fiche_titre($ContactList);
$object = new Societe($db);
$object->fetch($socid);
if(!empty($_REQUEST['action']) && $_REQUEST['action'] == 'edit')
    $action = '&action=edit';
else
    $action = '';
print '
        <div class="tabs" data-role="controlgroup" data-type="horizontal">
            <div class="inline-block tabsElem">
                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/soc.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].$action.'&socid='.$object->id.'">'.$langs->trans('BasicInfo').'</a>
            </div>
            <div class="inline-block tabsElem">
                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/societeaddress.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].$action.'&socid='.$object->id.'">'.$langs->trans('AddressList').'</a>
            </div>
            <div class="inline-block tabsElem tabsElemActive">
                <a id="user" class="tabactive tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/societecontact.php?mainmenu='.$_REQUEST['mainmenu'].'&idmenu='.$_REQUEST['idmenu'].$action.'&socid='.$object->id.'">'.$langs->trans('ContactList').'</a>
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

$CategoryOfCustomer = $object->getCategoryOfCustomer();
$FormOfGoverment = $object->getFormOfGoverment();
global $langs;
$NewItem = $langs->trans('NewItem');
$Control = $langs->trans('Control');
$AddContact = $langs->trans('AddContact');

$TableParam = array();
$ColParam['title']='';
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='100px';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='';
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;
$tablename = "llx_societe_contact";
$sql = 'select `llx_societe_contact`.rowid, subdivision, `llx_societe_contact`.`socid`, case when `llx_societe_contact`.town_id is not null or `llx_societe_contact`.town_id > 0 then
concat(trim(nametown), " ", trim(regions.name), " р-н. ", trim(states.name), " обл.") else `llx_societe_contact`.location end  as nametown,
`llx_post`.`postname`,`responsibility`.`name` as respon_name,lastname,firstname,work_phone,
call_work_phone,fax,call_fax,mobile_phone1,call_mobile_phone1,mobile_phone2,
call_mobile_phone2,email1,send_email1,email2,send_email2,skype,call_skype,
birthdaydate,send_birthdaydate, `llx_societe_contact`.`active`
from `llx_societe_contact`
left join `llx_c_ziptown` on `llx_c_ziptown`.rowid = `llx_societe_contact`.`town_id`
        left join states on states.rowid = llx_c_ziptown.fk_state
        left join regions on regions.rowid =  llx_c_ziptown.`fk_region`
left join `llx_post` on `llx_post`.`rowid`= `llx_societe_contact`.`post_id`
left join `responsibility` on `responsibility`.`rowid` = `llx_societe_contact`.`respon_id`
where `llx_societe_contact`.`socid`='.$socid.'
and `llx_societe_contact`.`active` = 1';
//die($sql);
$contacttable = new societecontact();
//var_dump($_REQUEST['sortfield']);
if(!isset($_REQUEST['sortfield']))
    $table = $contacttable->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme, null, null, $readonly = array(), false, !empty($_REQUEST['action']));
else
    $table = $contacttable->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme, $_REQUEST['sortfield'], $_REQUEST['sortorder'], false, !empty($_REQUEST['action']));
if(!empty($_REQUEST['action'])&&$_REQUEST['action'] == 'edit')
    $controlbtn = '        <div class="address_header">
            <div class="blockvmenupair" style="width: auto!important; height: 65px">
                <div class="menu_titre" style="width: 65px">
                    <b><?echo $Control?></b>
                </div>
                <div class="menu_top"></div>
                <div class="menu_contenu" style="float: left">
                    <form action="/dolibarr/htdocs/societe/addcontact.php" method="post">
                        <input id="url" name="url" type="hidden" value="'.$_SERVER['REQUEST_URI'].'">
                        <input id="mainmenu" name="mainmenu" type="hidden" value="'.$_REQUEST['mainmenu'].'">
                        <input id="idmenu" name="idmenu" type="hidden" value="'.$_REQUEST['idmenu'].'">
                        <input id="user_id" name="user_id" type="hidden" value="'.$user->id.'">
                        <input id="socid" name="socid" type="hidden" value="'.$socid.'">
                        <input id="action" name="action" type="hidden" value="add">
                        <button type="submit">&nbsp;&nbsp;&nbsp;&nbsp;'.$AddContact.'&nbsp;&nbsp;&nbsp;&nbsp;</button>
                    </form>
                </div>
                <div class="menu_end"></div>
            </div>
        </div>';
include DOL_DOCUMENT_ROOT.'/theme/'.$conf->theme.'/societecontact.html';

llxFooter();