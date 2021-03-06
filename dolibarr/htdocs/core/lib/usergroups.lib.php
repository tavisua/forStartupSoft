<?php
/* Copyright (C) 2006-2012	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2010-2012	Regis Houssin		<regis.houssin@capnetworks.com>
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
 * or see http://www.gnu.org/
 */


/**
 *	    \file       htdocs/core/lib/usergroups.lib.php
 *		\brief      Ensemble de fonctions de base pour la gestion des utilisaterus et groupes
 */

/**
 * Prepare array with list of tabs
 *
 * @param   Object	$object		Object related to tabs
 * @return  array				Array of tabs to show
 */
function user_prepare_head($object)
{
	global $langs, $conf, $user;
//        echo '<pre>';
//        var_dump($object);
//        echo '</pre>';
//        die();
	$langs->load("users");

	$canreadperms=true;
	if (! empty($conf->global->MAIN_USE_ADVANCED_PERMS))
	{
		$canreadperms=($user->admin || ($user->id != $object->id && $user->rights->user->user_advance->readperms) || ($user->id == $object->id && $user->rights->user->self_advance->readperms));
	}

	$h = 0;
	$head = array();

    $head[$h][0] = DOL_URL_ROOT.'/user/card.php?id='.$object->id;
    $head[$h][1] = $langs->trans("UserCard");
    $head[$h][2] = 'user';
    $h++;
	
	$head[$h][0] = DOL_URL_ROOT.'/user/responsibility.php?id='.$object->id.'&mainmenu=tools&idmenu=5223';
    $head[$h][1] = 'Сфери відповідальності';
    $head[$h][2] = 'responsibility';
    $h++;

	$head[$h][0] = DOL_URL_ROOT.'/user/useractions.php?id_usr='.$object->id.'&mainmenu=tools&idmenu=5223&kind=yourself';
    $head[$h][1] = 'Активні завдання';
    $head[$h][2] = 'active_actions';
    $h++;

	//Добавляю ссылку на страницу закрепленных районов ответственности
//    if($object->respon_alias == 'sale' || $object->respon_alias2 == 'sale'){
	$responsibility = $object->getResponding($object->id, true);
//	echo '<pre>';
//	var_dump($responsibility);
//	echo '</pre>';
//	die();
	if(count(array_intersect($responsibility, array('sale','wholesale_purchase'))) > 0){
        $head[$h][0] = DOL_URL_ROOT.'/user/areas.php?id='.$object->id.'&mainmenu=tools&idmenu=5223';
        $head[$h][1] = $langs->trans("Areas");
        $head[$h][2] = 'areas';
        $h++;
    }
	if($object->rights->user->user->mentor){
		$head[$h][0] = DOL_URL_ROOT.'/user/mentor.php?id='.$object->id.'&mainmenu=tools&idmenu=5223';
		$head[$h][1] = $langs->trans("SubdivisionsMentor");
		$head[$h][2] = 'SubdivisionsMentor';
		$h++;
	}
	if(count(array_intersect($responsibility, array('service','purchase','wholesale_purchase'))) > 0){
        $head[$h][0] = DOL_URL_ROOT.'/user/lineactive.php?id='.$object->id.'&mainmenu=tools&idmenu=5223';
        $head[$h][1] = $langs->trans("PropLineActive");
        $head[$h][2] = 'lineactive';
        $h++;
    }
	if(count(array_intersect($responsibility, array('marketing'))) > 0){
        $head[$h][0] = DOL_URL_ROOT.'/user/lineactive_marketing.php?id='.$object->id.'&mainmenu=tools&idmenu=5223';
        $head[$h][1] = $langs->trans("LineActive");
        $head[$h][2] = 'lineactive';
        $h++;
    }
	if(count(array_intersect($responsibility, array('counter','corp_manager','purchase','paperwork','cadry','wholesale_purchase','logistika','jurist'))) > 0){
		$head[$h][0] = DOL_URL_ROOT.'/user/categories.php?id='.$object->id.'&mainmenu=tools&idmenu=5223';
        $head[$h][1] = $langs->trans("CategoriesContractors");
        $head[$h][2] = 'categories';
        $h++;
    }
	if(count(array_intersect($responsibility, array('paperwork'))) > 0){
		$head[$h][0] = DOL_URL_ROOT.'/user/states.php?id='.$object->id.'&mainmenu=tools&idmenu=5223';
        $head[$h][1] = $langs->trans("States");
        $head[$h][2] = 'states';
        $h++;
    }
//	if($object->respon_alias == 'wholesale_purchase'){
//        $head[$h][0] = DOL_URL_ROOT.'/user/lineactive.php?id='.$object->id.'&mainmenu=tools&idmenu=5223';
//        $head[$h][1] = $langs->trans("PropLineActive");
//        $head[$h][2] = 'lineactive';
//        $h++;
//    }
	if (! empty($conf->ldap->enabled) && ! empty($conf->global->LDAP_SYNCHRO_ACTIVE))
	{
		$langs->load("ldap");
	    $head[$h][0] = DOL_URL_ROOT.'/user/ldap.php?id='.$object->id;
	    $head[$h][1] = $langs->trans("LDAPCard");
	    $head[$h][2] = 'ldap';
	    $h++;
	}

	if ($canreadperms)
	{
		$head[$h][0] = DOL_URL_ROOT.'/user/perms.php?id='.$object->id;
		$head[$h][1] = $langs->trans("UserRights");
		$head[$h][2] = 'rights';
		$h++;
	}

    $head[$h][0] = DOL_URL_ROOT.'/user/param_ihm.php?id='.$object->id;
    $head[$h][1] = $langs->trans("UserGUISetup");
    $head[$h][2] = 'guisetup';
    $h++;

    if (! empty($conf->agenda->enabled))
    {
	    $head[$h][0] = DOL_URL_ROOT.'/user/agenda_extsites.php?id='.$object->id;
	    $head[$h][1] = $langs->trans("ExtSites");
	    $head[$h][2] = 'extsites';
	    $h++;
    }

    if (! empty($conf->clicktodial->enabled))
    {
        $head[$h][0] = DOL_URL_ROOT.'/user/clicktodial.php?id='.$object->id;
        $head[$h][1] = $langs->trans("ClickToDial");
	    $head[$h][2] = 'clicktodial';
        $h++;
    }

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    // $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
    // $this->tabs = array('entity:-tabname);   												to remove a tab
    complete_head_from_modules($conf,$langs,$object,$head,$h,'user');

    //Info on users is visible only by internal user
    if (empty($user->societe_id))
    {
		// Notes
        $nbNote = 0;
        if(!empty($object->note)) $nbNote++;
        $head[$h][0] = DOL_URL_ROOT.'/user/note.php?id='.$object->id;
        $head[$h][1] = $langs->trans("Note");
		if ($nbNote > 0) $head[$h][1].= ' <span class="badge">'.$nbNote.'</span>';
        $head[$h][2] = 'note';
        $h++;

        // Attached files
        require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
        $upload_dir = $conf->user->dir_output . "/" . $object->id;
        $nbFiles = count(dol_dir_list($upload_dir,'files',0,'','(\.meta|_preview\.png)$'));
        $head[$h][0] = DOL_URL_ROOT.'/user/document.php?userid='.$object->id;
        $head[$h][1] = $langs->trans("Documents");
        if($nbFiles > 0) $head[$h][1].= ' <span class="badge">'.$nbFiles.'</span>';
        $head[$h][2] = 'document';
        $h++;

    	$head[$h][0] = DOL_URL_ROOT.'/user/info.php?id='.$object->id;
    	$head[$h][1] = $langs->trans("Info");
    	$head[$h][2] = 'info';
    	$h++;
    }

    complete_head_from_modules($conf,$langs,$object,$head,$h,'user','remove');

	return $head;
}


function group_prepare_head($object)
{
	global $langs, $conf, $user;

	$canreadperms=true;
//	if (! empty($conf->global->MAIN_USE_ADVANCED_PERMS))
//	{
//		$canreadperms=($user->admin || $user->rights->user->group_advance->readperms);
//	}

//	$h = 0;
//	$head = array();
//
//    $head[$h][0] = DOL_URL_ROOT.'/user/group/card.php?id='.$object->id;
//    $head[$h][1] = $langs->trans("GroupCard");
//    $head[$h][2] = 'group';
//    $h++;

//	if (! empty($conf->ldap->enabled) && ! empty($conf->global->LDAP_SYNCHRO_ACTIVE))
//	{
//		$langs->load("ldap");
//	    $head[$h][0] = DOL_URL_ROOT.'/user/group/ldap.php?id='.$object->id;
//	    $head[$h][1] = $langs->trans("LDAPCard");
//	    $head[$h][2] = 'ldap';
//	    $h++;
//	}
//
//	if ($canreadperms)
//	{
//		$head[$h][0] = DOL_URL_ROOT.'/user/group/perms.php?id='.$object->id;
//		$head[$h][1] = $langs->trans("GroupRights");
//		$head[$h][2] = 'rights';
//		$h++;
//	}

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    // $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
    // $this->tabs = array('entity:-tabname);   												to remove a tab
    complete_head_from_modules($conf,$langs,$object,$head,$h,'group');

    complete_head_from_modules($conf,$langs,$object,$head,$h,'group','remove');

    return $head;
}



/**
 * Prepare array with list of tabs
 *
 * @return  array				Array of tabs to show
 */
function user_admin_prepare_head()
{
	global $langs, $conf, $user;

	$langs->load("users");
	$h=0;

    $head[$h][0] = DOL_URL_ROOT.'/admin/user.php';
    $head[$h][1] = $langs->trans("Parameters");
    $head[$h][2] = 'card';
    $h++;

    $head[$h][0] = DOL_URL_ROOT.'/user/admin/user_extrafields.php';
    $head[$h][1] = $langs->trans("ExtraFields");
    $head[$h][2] = 'attributes';
    $h++;

   $head[$h][0] = DOL_URL_ROOT.'/user/admin/group_extrafields.php';
    $head[$h][1] = $langs->trans("ExtraFields")." ".$langs->trans("Groups");
    $head[$h][2] = 'attributes_group';
    $h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	// $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
	// $this->tabs = array('entity:-tabname);   												to remove a tab
	complete_head_from_modules($conf,$langs,null,$head,$h,'useradmin');

	complete_head_from_modules($conf,$langs,null,$head,$h,'useradmin','remove');

	return $head;
}



/**
 * Prepare array with list of tabs
 *
 * @param   Object	$object		Object related to tabs
 * @param	array	$aEntities	Entities array
 * @return  array				Array of tabs
 */
function entity_prepare_head($object, $aEntities)
{
	global $mc;

	$head = array();

	foreach($aEntities as $entity)
	{
		$mc->getInfo($entity);
		$head[$entity][0] = $_SERVER['PHP_SELF'].'?id='.$object->id.'&amp;entity='.$entity;
		$head[$entity][1] = $mc->label;
		$head[$entity][2] = $entity;
	}

	return $head;
}

/**
 * 	Show list of themes. Show all thumbs of themes
 *
 * 	@param	User|null	$fuser				User concerned or null for global theme
 * 	@param	int			$edit				1 to add edit form
 * 	@param	boolean		$foruserprofile		Show for user profile view
 * 	@return	void
 */
function show_theme($fuser,$edit=0,$foruserprofile=false)
{
    global $conf,$langs,$bc;

    //$dirthemes=array(empty($conf->global->MAIN_FORCETHEMEDIR)?'/theme':$conf->global->MAIN_FORCETHEMEDIR.'/theme');
    $dirthemes=array('/theme');
    if (! empty($conf->modules_parts['theme']))		// Using this feature slow down application
    {
    	foreach($conf->modules_parts['theme'] as $reldir)
    	{
	    	$dirthemes=array_merge($dirthemes,(array) ($reldir.'theme'));
    	}
    }
    $dirthemes=array_unique($dirthemes);
	// Now dir_themes=array('/themes') or dir_themes=array('/theme','/mymodule/theme')

    $selected_theme='';
    if (empty($foruserprofile)) $selected_theme=$conf->global->MAIN_THEME;
    else $selected_theme=((is_object($fuser) && ! empty($fuser->conf->MAIN_THEME))?$fuser->conf->MAIN_THEME:'');

    $colspan=2;
    if ($foruserprofile) $colspan=4;

    $thumbsbyrow=6;
    print '<table class="noborder" width="100%">';

    $var=false;

    // Title
    if ($foruserprofile)
    {
    	print '<tr class="liste_titre"><th width="25%">'.$langs->trans("Parameter").'</th><th width="25%">'.$langs->trans("DefaultValue").'</th>';
    	print '<th colspan="2">&nbsp;</th>';
	    print '</tr>';

	    print '<tr '.$bc[$var].'>';
	    print '<td>'.$langs->trans("DefaultSkin").'</td>';
	    print '<td>'.$conf->global->MAIN_THEME.'</td>';
	    print '<td align="left" class="nowrap" width="20%"><input '.$bc[$var].' name="check_MAIN_THEME"'.($edit?'':' disabled').' type="checkbox" '.($selected_theme?" checked":"").'> '.$langs->trans("UsePersonalValue").'</td>';
	    print '<td>&nbsp;</td>';
	    print '</tr>';
    }
    else
    {
    	print '<tr class="liste_titre"><th width="35%">'.$langs->trans("DefaultSkin").'</th>';
    	print '<th align="right">';
    	$url='http://www.dolistore.com/lang-en/4-skins';
    	if (preg_match('/fr/i',$langs->defaultlang)) $url='http://www.dolistore.com/lang-fr/4-themes';
    	//if (preg_match('/es/i',$langs->defaultlang)) $url='http://www.dolistore.com/lang-es/4-themes';
    	print '<a href="'.$url.'" target="_blank">';
    	print $langs->trans('DownloadMoreSkins');
    	print '</a>';
    	print '</th></tr>';

    	print '<tr '.$bc[$var].'>';
    	print '<td>'.$langs->trans("ThemeDir").'</td>';
    	print '<td>';
    	foreach($dirthemes as $dirtheme)
    	{
    		echo '"'.$dirtheme.'" ';
    	}
    	print '</td>';
    	print '</tr>';
    }

    $var=!$var;
    print '<tr '.$bc[$var].'><td colspan="'.$colspan.'">';

    print '<table class="nobordernopadding" width="100%"><tr><td><div align="center">';

    $i=0;
    foreach($dirthemes as $dir)
    {
    	//print $dirroot.$dir;exit;
    	$dirtheme=dol_buildpath($dir,0);	// This include loop on $conf->file->dol_document_root
    	$urltheme=dol_buildpath($dir,1);

    	if (is_dir($dirtheme))
    	{
    		$handle=opendir($dirtheme);
    		if (is_resource($handle))
    		{
    			while (($subdir = readdir($handle))!==false)
    			{
    				if (is_dir($dirtheme."/".$subdir) && substr($subdir, 0, 1) <> '.'
    						&& substr($subdir, 0, 3) <> 'CVS' && ! preg_match('/common|phones/i',$subdir))
    				{
    					// Disable not stable themes
    					//if ($conf->global->MAIN_FEATURES_LEVEL < 1 && preg_match('/bureau2crea/i',$subdir)) continue;

    					print '<div class="inline-block" style="margin-top: 10px; margin-bottom: 10px; margin-right: 20px; margin-left: 20px;">';
    					$file=$dirtheme."/".$subdir."/thumb.png";
    					$url=$urltheme."/".$subdir."/thumb.png";
    					if (! file_exists($file)) $url=$urltheme."/common/nophoto.jpg";
    					print '<a href="'.$_SERVER["PHP_SELF"].($edit?'?action=edit&theme=':'?theme=').$subdir.(GETPOST("optioncss")?'&optioncss='.GETPOST("optioncss",'alpha',1):'').($fuser?'&id='.$fuser->id:'').'" style="font-weight: normal;" alt="'.$langs->trans("Preview").'">';
    					if ($subdir == $conf->global->MAIN_THEME) $title=$langs->trans("ThemeCurrentlyActive");
    					else $title=$langs->trans("ShowPreview");
    					print '<img src="'.$url.'" border="0" width="80" height="60" alt="'.$title.'" title="'.$title.'" style="margin-bottom: 5px;">';
    					print '</a><br>';
    					if ($subdir == $selected_theme)
    					{
    						print '<input '.($edit?'':'disabled').' type="radio" '.$bc[$var].' style="border: 0px;" checked name="main_theme" value="'.$subdir.'"> <b>'.$subdir.'</b>';
    					}
    					else
    					{
    						print '<input '.($edit?'':'disabled').' type="radio" '.$bc[$var].' style="border: 0px;" name="main_theme" value="'.$subdir.'"> '.$subdir;
    					}
						print '</div>';

    					$i++;
    				}
    			}
    		}
    	}
    }

    print '</div></td></tr></table>';

    print '</td></tr>';
    print '</table>';
}

