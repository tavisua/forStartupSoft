<?php
    /* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
     * Copyright (C) 2004-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
     * Copyright (C) 2005-2010 Regis Houssin        <regis.houssin@capnetworks.com>
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
     *       \file       htdocs/core/tools.php
     *       \brief      Home page for top menu tools
     */

    require '../main.inc.php';

//    $langs->load("companies");
//    $langs->load("other");

    // Security check
    $socid=0;
    if ($user->societe_id > 0) $socid=$user->societe_id;



    /*
     * View
     */

    $socstatic=new Societe($db);

    $title = $langs->trans("Tools");
    llxHeader("",$title,"");
    print_fiche_titre($title);
    $MenuManager = $langs->trans('MenuManager');
    $MenuUsersAndGroups = $langs->trans('MenuUsersAndGroups');
    $Post = $langs->trans('aPost');
    $SphereOfResponsibility = $langs->trans('SphereOfResponsibility');
    $CategoryCustomer = $langs->trans('CategoryCustomer');
    $FormOfGovernment = $langs->trans('FormOfGovernment');
    $KindAddress = $langs->trans('KindAddress');
    $Country = $langs->trans('Country');
    $SubDivision = $langs->trans('SubDivision');
    $Region = $langs->trans('Region');
    $Area = $langs->trans('Area');
    $KindLocality = $langs->trans('KindOfLocality');
    $KindOfStreet = $langs->trans('KindOfStreet');
    $KindOfOffice = $langs->trans('KindOfOffice');
    $Location = $langs->trans('Location');
    $Currency = $langs->trans('Currency');
    $Trademark = $langs->trans('Trademark');
    $KindOfTransport = $langs->trans('KindOfTransport');
    $GroupOfMaterials = $langs->trans('GroupOfMaterials');
    $Permission = $langs->trans('Permissions');
    $Classifycation = $langs->trans('Classifycation');
    $Unit = $langs->trans('Unit');
    $theme = $conf->theme;



    include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/eldy/tools.html');

    llxFooter();

    //$db->close();
