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
print '
        <div class="tabs" data-role="controlgroup" data-type="horizontal">
            <div class="inline-block tabsElem tabsElemActive">
                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/soc.php?mainmenu=companies&idmenu='.$_REQUEST['idmenu'].'&action=edit&socid='.$object->id.'">'.$langs->trans('BasicInfo').'</a>
            </div>
            <div class="inline-block tabsElem">
                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/societeaddress.php?mainmenu=companies&idmenu='.$_REQUEST['idmenu'].'&action=edit&socid='.$object->id.'">'.$langs->trans('AddressList').'</a>
            </div>
            <div class="inline-block tabsElem">
                <a id="user" class="tabactive tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/societecontact.php?mainmenu=companies&idmenu='.$_REQUEST['idmenu'].'&action=edit&socid='.$object->id.'">'.$langs->trans('ContactList').'</a>
            </div>
            <div class="inline-block tabsElem">
                <a id="user" class="tab inline-block" data-role="button" href="/dolibarr/htdocs/societe/soc.php?mainmenu=companies&idmenu='.$_REQUEST['idmenu'].'&action=edit&socid='.$object->id.'">'.$langs->trans('EconomicData').'</a>
            </div>
        </div>';


$CategoryOfCustomer = $object->getCategoryOfCustomer();
$FormOfGoverment = $object->getFormOfGoverment();
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

$sql = 'select `llx_societe_contact`.rowid, subdivision,  concat(trim(nametown), " ", trim(regions.name), " р-н. ", trim(states.name), " обл.") as nametown,
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

$contacttable = new societecontact();
//var_dump($_REQUEST['sortfield']);
if(!isset($_REQUEST['sortfield']))
    $table = $contacttable->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme, null, null, $readonly = array(), false);
else
    $table = $contacttable->fShowTable($TableParam, $sql, "'".$tablename."'", $conf->theme, $_REQUEST['sortfield'], $_REQUEST['sortorder']);

include DOL_DOCUMENT_ROOT.'/theme/'.$conf->theme.'/societecontact.html';

llxFooter();