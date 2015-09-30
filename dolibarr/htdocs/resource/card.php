<?php
/* Copyright (C) 2013-2014	Jean-François Ferry	<jfefe@aternatik.fr>
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
 *   	\file       resource/card.php
 *		\ingroup    resource
 *		\brief      Page to manage resource object
 */


// Change this following line to use the correct relative path (../, ../../, etc)
$res=0;
$res=@include("../main.inc.php");				// For root directory
if (! $res) $res=@include("../../main.inc.php");	// For "custom" directory
if (! $res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once 'class/resource.class.php';
require_once 'class/html.formresource.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/resource.lib.php';

// Load traductions files requiredby by page
$langs->load("resource");
$langs->load("companies");
$langs->load("other");
$langs->load("main");

// Get parameters
$id						= GETPOST('id','int');
$action					= GETPOST('action','alpha');
$ref					= GETPOST('ref');
$description			= GETPOST('description');
$fk_code_type_resource	= GETPOST('fk_code_type_resource','alpha');

// Protection if external user
if ($user->societe_id > 0)
{
	accessforbidden();
}

if( ! $user->rights->resource->read)
	accessforbidden();

$object = new Resource($db);

$hookmanager->initHooks(array('resource_card','globalcard'));
$parameters=array('resource_id'=>$id);
$reshook=$hookmanager->executeHooks('doActions',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
	/*******************************************************************
	* ACTIONS
	********************************************************************/

	if ($action == 'update' && ! $_POST["cancel"]  && $user->rights->resource->write )
	{
		$error=0;

		if (empty($ref))
		{
			$error++;
			setEventMessage($langs->trans("ErrorFieldRequired",$langs->transnoentities("Ref")), 'errors');
		}

		if (! $error)
		{
			$res = $object->fetch($id);
			if ( $res > 0 )
			{
				$object->ref          			= $ref;
				$object->description  			= $description;
				$object->fk_code_type_resource  = $fk_code_type_resource;

				$result=$object->update($user);
				if ($result > 0)
				{
					Header("Location: ".$_SERVER['PHP_SELF']."?id=".$object->id);
					exit;
				}
				else
				{
					setEventMessage($object->error, 'errors');
					$action='edit';
				}

			}
			else
			{
				setEventMessage($object->error,'errors');
				$action='edit';
			}
		}
		else
		{
			$action='edit';
		}
	}
}

/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/
$pagetitle = $langs->trans('ResourceCard');
llxHeader('',$pagetitle,'');

$form = new Form($db);
$formresource = new FormResource($db);

if ( $object->fetch($id) > 0 )
{
	$head=resourcePrepareHead($object);
	dol_fiche_head($head, 'resource', $langs->trans("ResourceSingular"),0,'resource@resource');


	if ($action == 'edit' )
	{

		if ( ! $user->rights->resource->write )
			accessforbidden('',0);

		/*---------------------------------------
		 * Edit object
		 */
		print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		print '<input type="hidden" name="action" value="update">';
		print '<input type="hidden" name="id" value="'.$object->id.'">';

		print '<table class="border" width="100%">';

		// Ref
		print '<tr><td width="20%">'.$langs->trans("ResourceFormLabel_ref").'</td>';
		print '<td><input size="12" name="ref" value="'.(GETPOST('ref') ? GETPOST('ref') : $object->ref).'"></td></tr>';

		// Type
		print '<tr><td width="20%">'.$langs->trans("ResourceType").'</td>';
		print '<td>';
		$ret = $formresource->select_types_resource($object->fk_code_type_resource,'fk_code_type_resource','',2);
		print '</td></tr>';

		// Description
		print '<tr><td valign="top">'.$langs->trans("Description").'</td>';
		print '<td>';
		print '<textarea name="description" cols="80" rows="'.ROWS_3.'">'.($_POST['description'] ? GETPOST('description','alpha') : $object->description).'</textarea>';
		print '</td></tr>';

		print '<tr><td align="center" colspan="2">';
		print '<input name="update" class="button" type="submit" value="'.$langs->trans("Modify").'"> &nbsp; ';
		print '<input type="submit" class="button" name="cancel" Value="'.$langs->trans("Cancel").'"></td></tr>';
		print '</table>';
		print '</form>';
	}
	else
	{
	    // Confirm deleting resource line
	    if ($action == 'delete')
	    {
	        print $form->formconfirm("card.php?&id=".$id,$langs->trans("DeleteResource"),$langs->trans("ConfirmDeleteResource"),"confirm_delete_resource",'','',1);
	    }

		/*---------------------------------------
		 * View object
		 */
		print '<table width="100%" class="border">';

		print '<tr><td style="width:35%">'.$langs->trans("ResourceFormLabel_ref").'</td><td>';
		$linkback = $objet->ref.' <a href="list.php">'.$langs->trans("BackToList").'</a>';
		print $form->showrefnav($object, 'id', $linkback,1,"rowid");
		print '</td>';
		print '</tr>';

		// Resource type
		print '<tr>';
		print '<td>' . $langs->trans("ResourceType") . '</td>';
		print '<td>';
		print $object->type_label;
		print '</td>';
		print '</tr>';

		// Description
		print '<tr>';
		print '<td>' . $langs->trans("ResourceFormLabel_description") . '</td>';
		print '<td>';
		print $object->description;
		print '</td>';
		print '</tr>';

		print '</table>';
	}

	print '</div>';

	/*
	 * Boutons actions
	*/
	print '<div class="tabsAction">';
	$parameters = array();
	$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been
	// modified by hook
	if (empty($reshook))
	{
		if ($action != "edit" )
		{
			// Edit resource
			if($user->rights->resource->write)
			{
				print '<div class="inline-block divButAction">';
				print '<a href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&amp;action=edit" class="butAction">'.$langs->trans('Edit').'</a>';
				print '</div>';
			}
		}
		if ($action != "delete" && $action != "edit")
		{
		    // Delete resource
		    if($user->rights->resource->delete)
		    {
		        print '<div class="inline-block divButAction">';
		        print '<a href="'.$_SERVER['PHP_SELF'].'?id='.$id.'&amp;action=delete" class="butActionDelete">'.$langs->trans('Delete').'</a>';
		        print '</div>';
		    }
		}
	}
	print '</div>';
}
else {
	dol_print_error();
}



// End of page
llxFooter();
$db->close();
