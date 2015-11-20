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
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
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
$CategoryOfCustomer = $object->getCategoryOfCustomer();
$FormOfGoverment = $object->getFormOfGoverment();
$NewItem = $langs->trans('NewItem');
$Control = $langs->trans('Control');
$AddContact = $langs->trans('AddContact');

include DOL_DOCUMENT_ROOT.'/theme/eldy/societecontact.html';

llxFooter();