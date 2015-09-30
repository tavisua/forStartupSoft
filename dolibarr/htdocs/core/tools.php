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

$langs->load("companies");
$langs->load("other");

// Security check
$socid=0;
if ($user->societe_id > 0) $socid=$user->societe_id;



/*
 * View
 */

$socstatic=new Societe($db);

llxHeader("",$langs->trans("Tools"),"");

$text=$langs->trans("Tools");

print_fiche_titre($text);

// Show description of content
//print $langs->trans("ToolsDesc").'<br><br>';
$TableParam = array();
$ColParam['title']='Название меню';
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='Отображение в браузере';
$ColParam['width']='';
$ColParam['align']='';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='URL';
$ColParam['width']='';
$ColParam['align']='left';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='Порядковый номер';
$ColParam['width']='';
$ColParam['align']='left';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='Видимость';
$ColParam['width']='100';
$ColParam['align']='left';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='Активное';
$ColParam['width']='100';
$ColParam['align']='left';
$ColParam['class']='';
$TableParam[]=$ColParam;

$ColParam['title']='Действия';
$ColParam['width']='250';
$ColParam['align']='left';
$ColParam['class']='';
$TableParam[]=$ColParam;

//$ColParam['title']='';
//$ColParam['width']='50';
//$ColParam['align']='right';
//$ColParam['class']='';
//$TableParam[]=$ColParam;

$sql = 'SELECT m.rowid, m.mainmenu, m.titre, m.url, m.position, m.show, m.active
FROM llx_menu as m
WHERE m.fk_menu = 0
AND m.usertype IN (0,2) ORDER BY m.position, m.rowid';
include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/DBManager/db.php';
$db = new dbMysqli();
$table = $db->fShowTable($TableParam, $sql);

ob_start();

include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/admin/tools/template/menu_manager.html');

echo ob_get_clean();


llxFooter();

//$db->close();
