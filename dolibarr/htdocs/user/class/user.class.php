<?php
/* Copyright (c) 2002-2007 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (c) 2002-2003 Jean-Louis Bergamo   <jlb@j1b.org>
 * Copyright (c) 2004-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2004      Sebastien Di Cintio  <sdicintio@ressource-toi.org>
 * Copyright (C) 2004      Benoit Mortier       <benoit.mortier@opensides.be>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2005      Lionel Cousteix      <etm_ltd@tiscali.co.uk>
 * Copyright (C) 2011      Herve Prot           <herve.prot@symeos.com>
 * Copyright (C) 2013-2014 Philippe Grand       <philippe.grand@atoo-net.com>
 * Copyright (C) 2013      Alexandre Spangaro   <alexandre.spangaro@gmail.com>
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
 *  \file       htdocs/user/class/user.class.php
 *	\brief      File of class to manage users
 *  \ingroup	core
 */

require_once DOL_DOCUMENT_ROOT .'/core/class/commonobject.class.php';


/**
 *	Class to manage Dolibarr users
 */
class User extends CommonObject
{
	public $element='user';
	public $table_element='user';
	protected $ismultientitymanaged = 1;	// 0=No test on entity, 1=Test with field entity, 2=Test with link by societe

	var $id=0;
	var $ref;
	var $ref_ext;
	var $ldap_sid;
	var $search_sid;
	var $lastname;
	var $firstname;
	var $note;
	var $email;
	var $skype;
	var $job;
	var $signature;
	var $office_phone;
	var $office_fax;
	var $user_mobile;
	var $admin;
	var $login;
	var $entity;

	//! Clear password in memory
	var $pass;
	//! Clear password in database (defined if DATABASE_PWD_ENCRYPTED=0)
	var $pass_indatabase;
	//! Encrypted password in database (always defined)
	var $pass_indatabase_crypted;

	var $datec;
	var $datem;

	//! If this is defined, it is an external user
	var $societe_id;	// deprecated
	var $contact_id;	// deprecated
	var $socid;
	var $contactid;

	var $fk_member;
	var $fk_user;
    var $post_id;
    var $subdiv_id;
    var $respon_id;
    var $respon_id2;
    var $respon_alias;
    var $respon_alias2;
	var $respon_array = array();
    var $usergroup_id;
    var $accessToken;
    var $id_connect;
    var $timePhoneConnect;

	var $clicktodial_url;
	var $clicktodial_login;
	var $clicktodial_password;
	var $clicktodial_poste;

	var $datelastlogin;
	var $datepreviouslogin;
	var $statut;
	var $photo;
	var $lang;

	var $rights;                        // Array of permissions user->rights->permx
	var $all_permissions_are_loaded;	/**< \private all_permissions_are_loaded */
	private $_tab_loaded=array();		// Array of cache of already loaded permissions

	var $conf;           			// To store personal config
	var $oldcopy;                	// To contains a clone of this when we need to save old properties of object

	var $users;						// To store all tree of users hierarchy
	var $parentof;					// To store an array of all parents for all ids.

	var $accountancy_code;			// Accountancy code in prevision of the complete accountancy module
	var $thm;						// Average cost of employee
	var $tjm;						// Average cost of employee
	var $salary;					// Monthly salary
	var $salaryextra;				// Monthly salary extra
	var $weeklyhours;				// Weekly hours
	var $required_pages;			//обов'язкові до перегляду сторінки

	var $color;						// Define background color for user in agenda

	/**
	 *    Constructor de la classe
	 *
	 *    @param   DoliDb  $db     Database handler
	 */
	function __construct($db)
	{
		$this->db = $db;

		// Preference utilisateur
		$this->liste_limit = 0;
		$this->clicktodial_loaded = 0;

		$this->all_permissions_are_loaded = 0;
		$this->admin=0;

		$this->conf				    = new stdClass();
		$this->rights				= new stdClass();
		$this->rights->user			= new stdClass();
		$this->rights->user->user	= new stdClass();
		$this->rights->user->self	= new stdClass();

	}

	/**
	 *	Load a user from database with its id or ref (login)
	 *
	 *	@param	int		$id		       		Si defini, id a utiliser pour recherche
	 * 	@param  string	$login       		Si defini, login a utiliser pour recherche
	 *	@param  string	$sid				Si defini, sid a utiliser pour recherche
	 * 	@param	int		$loadpersonalconf	Also load personal conf of user (in $user->conf->xxx)
	 * 	@return	int							<0 if KO, 0 not found, >0 if OK
	 */
	function fetch_userID($login,$pass){
		global $db;
		if($login == 'test' && $pass == $login)
			return 100;
		$sql = "select rowid from llx_user where login = '".$login."' and pass = '".$pass."'";
//		die($sql);
		$res = $db->query($sql);
		if(!$res) {
			dol_print_error($db);
			exit();
		}
		$obj = $db->fetch_object($res);
		return $obj->rowid;
	}
	function fetch($id='', $login='',$sid='',$loadpersonalconf=1)
	{
		global $conf, $user;

		// Clean parameters
		$login=trim($login);

		// Get user
		$sql = "SELECT u.rowid, u.lastname, u.firstname, u.email, u.job, u.skype, u.signature, u.office_phone, u.office_fax, u.user_mobile,";
		$sql.= " u.admin, u.login, u.note,";
		$sql.= " u.pass, u.pass_crypted, u.pass_temp,";
		$sql.= " u.fk_societe, u.fk_socpeople, u.fk_member, u.fk_user, u.ldap_sid,";
		$sql.= " u.statut, u.lang, u.entity,";
		$sql.= " u.datec as datec,";
		$sql.= " u.tms as datem,";
		$sql.= " u.datelastlogin as datel,";
		$sql.= " u.datepreviouslogin as datep,";
		$sql.= " u.photo as photo,";
		$sql.= " u.openid as openid,";
		$sql.= " u.accountancy_code,";
		$sql.= " u.thm,";
        $sql.=" u.subdiv_id, u.respon_id, u.respon_id2, u.usergroup_id, u.post_id, ";
		$sql.= " u.tjm,";
//		$sql.= " u.salary,";
		$sql.= " u.salaryextra,";
		$sql.= " u.weeklyhours,";
		$sql.= " u.color,";
		$sql.= " u.ref_int, u.ref_ext, responsibility.alias as respon_alias, respon2.alias as respon_alias2";
		$sql.= " FROM ".MAIN_DB_PREFIX."user as u left join responsibility on u.respon_id=responsibility.rowid left join responsibility respon2 on u.respon_id2=respon2.rowid";

		if ((empty($conf->multicompany->enabled) || empty($conf->multicompany->transverse_mode)) && (! empty($user->entity)))
		{
			$sql.= " WHERE u.entity IN (0,".$conf->entity.")";
		}
		else
		{
			$sql.= " WHERE u.entity IS NOT NULL";
		}

		if ($sid)    // permet une recherche du user par son SID ActiveDirectory ou Samba
		{
			$sql.= " AND (u.ldap_sid = '".$sid."' OR u.login = '".$this->db->escape($login)."') LIMIT 1";
		}
		else if ($login)
		{
			$sql.= " AND u.login = '".$this->db->escape($login)."'";
		}
		else
		{
			$sql.= " AND u.rowid = ".$id;
		}

		dol_syslog(get_class($this)."::fetch", LOG_DEBUG);

		$result = $this->db->query($sql);

		if ($result)
		{
			$obj = $this->db->fetch_object($result);
			if ($obj)
			{
				$this->id 			= $obj->rowid;
				$this->ref 			= $obj->rowid;

				$this->ref_int 		= $obj->ref_int;
				$this->ref_ext 		= $obj->ref_ext;

				$this->ldap_sid 	= $obj->ldap_sid;
				$this->lastname		= $obj->lastname;
				$this->firstname 	= $obj->firstname;

				$this->login		= $obj->login;
				$this->pass_indatabase = $obj->pass;
				$this->pass_indatabase_crypted = $obj->pass_crypted;
				$this->pass			= $obj->pass;
				$this->pass_temp	= $obj->pass_temp;
				$this->office_phone	= $obj->office_phone;
				$this->office_fax   = $obj->office_fax;
				$this->user_mobile  = $obj->user_mobile;
				$this->email		= $obj->email;
				$this->skype		= $obj->skype;
				$this->job			= $obj->job;
				$this->signature	= $obj->signature;
				$this->admin		= $obj->admin;
				$this->note			= $obj->note;
				$this->statut		= $obj->statut;
				$this->photo		= $obj->photo;
				$this->openid		= $obj->openid;
				$this->lang			= $obj->lang;
				$this->entity		= $obj->entity;
				$this->accountancy_code		= $obj->accountancy_code;
				$this->thm			= $obj->thm;
				$this->tjm			= $obj->tjm;
				$this->salary		= $obj->salary;
				$this->salaryextra	= $obj->salaryextra;
				$this->weeklyhours	= $obj->weeklyhours;
				$this->color		= $obj->color;

                $this->usergroup_id = $obj->usergroup_id;
                $this->respon_id    = $obj->respon_id;
                $this->respon_id2    = $obj->respon_id2;
                $this->respon_alias = $obj->respon_alias;
                $this->respon_alias2 = $obj->respon_alias2;
                $this->subdiv_id    = $obj->subdiv_id;
                $this->post_id      = $obj->post_id;

				$this->datec				= $this->db->jdate($obj->datec);
				$this->datem				= $this->db->jdate($obj->datem);
				$this->datelastlogin		= $this->db->jdate($obj->datel);
				$this->datepreviouslogin	= $this->db->jdate($obj->datep);

				$this->societe_id           = $obj->fk_societe;		// deprecated
				$this->contact_id           = $obj->fk_socpeople;	// deprecated
				$this->socid                = $obj->fk_societe;
				$this->contactid            = $obj->fk_socpeople;
				$this->fk_member            = $obj->fk_member;
				$this->fk_user        		= $obj->fk_user;

                $sql = 'select rowid, Hex, UNIX_TIMESTAMP(dtChange) dtChange from phone_connect where id_usr='.$this->id.' order by dtChange desc limit 1';
                $result = $this->db->query($sql);

                if($this->db->num_rows($result)>0){
                    $obj = $this->db->fetch_object($result);
                    if(dol_now()-3600<=$obj->dtChange){
                        $this->timePhoneConnect = $obj->dtChange;
                        $this->accessToken = $obj->Hex;
                        $this->id_connect = $obj->rowid;
//                        var_dump($this->id_connect);
//                        die('$this->id_connect');
                    }
                }
//				echo '<pre>';
//				var_dump($this);
//				echo '</pre>';
//				die();

				// Retreive all extrafield for thirdparty
				// fetch optionals attributes and labels
				require_once(DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php');
				$extrafields=new ExtraFields($this->db);
				$extralabels=$extrafields->fetch_name_optionals_label($this->table_element,true);
				$this->fetch_optionals($this->id,$extralabels);

				$this->db->free($result);
			}
			else
			{
				$this->error="USERNOTFOUND";
				dol_syslog(get_class($this)."::fetch user not found", LOG_DEBUG);

				$this->db->free($result);
				return 0;
			}
		}
		else
		{
			$this->error=$this->db->error();
			return -1;
		}

		// To get back the global configuration unique to the user
		if ($loadpersonalconf)
		{
			$sql = "SELECT param, value FROM ".MAIN_DB_PREFIX."user_param";
			$sql.= " WHERE fk_user = ".$this->id;
			$sql.= " AND entity = ".$conf->entity;
			//dol_syslog(get_class($this).'::fetch load personalized conf', LOG_DEBUG);
			$resql=$this->db->query($sql);
			if ($resql)
			{
				$num = $this->db->num_rows($resql);
				$i = 0;
				while ($i < $num)
				{
					$obj = $this->db->fetch_object($resql);
					$p=(! empty($obj->param)?$obj->param:'');
					if (! empty($p)) $this->conf->$p = $obj->value;
					$i++;
				}
				$this->db->free($resql);
			}
			else
			{
				$this->error=$this->db->error();
				return -2;
			}
		}

		return 1;
	}

	/**
	 *  Add a right to the user
	 *
	 * 	@param	int		$rid			id du droit a ajouter
	 *  @param  string	$allmodule		Ajouter tous les droits du module allmodule
	 *  @param  string	$allperms		Ajouter tous les droits du module allmodule, perms allperms
	 *  @param	int		$entity			Entity to use
	 *  @return int						> 0 if OK, < 0 if KO
	 */
	function addrights($rid, $allmodule='', $allperms='', $entity='')
	{
		global $conf;

		$entity = (! empty($entity)?$entity:$conf->entity);

		dol_syslog(get_class($this)."::addrights $rid, $allmodule, $allperms, $entity");
		$err=0;
		$whereforadd='';

		$this->db->begin();

		if (! empty($rid))
		{
			// Si on a demande ajout d'un droit en particulier, on recupere
			// les caracteristiques (module, perms et subperms) de ce droit.
			$sql = "SELECT module, perms, subperms";
			$sql.= " FROM ".MAIN_DB_PREFIX."rights_def";
			$sql.= " WHERE id = '".$this->db->escape($rid)."'";
			$sql.= " AND entity = ".$entity;

			$result=$this->db->query($sql);
			if ($result) {
				$obj = $this->db->fetch_object($result);
				$module=$obj->module;
				$perms=$obj->perms;
				$subperms=$obj->subperms;
			}
			else {
				$err++;
				dol_print_error($this->db);
			}

			// Where pour la liste des droits a ajouter
			$whereforadd="id=".$this->db->escape($rid);
			// Ajout des droits induits
			if (! empty($subperms))   $whereforadd.=" OR (module='$module' AND perms='$perms' AND (subperms='lire' OR subperms='read'))";
			else if (! empty($perms)) $whereforadd.=" OR (module='$module' AND (perms='lire' OR perms='read') AND subperms IS NULL)";
		}
		else {


			// On a pas demande un droit en particulier mais une liste de droits
			// sur la base d'un nom de module de de perms
			// Where pour la liste des droits a ajouter
			if (! empty($allmodule)) $whereforadd="module='".$this->db->escape($allmodule)."'";
			if (! empty($allperms))  $whereforadd=" AND perms='".$this->db->escape($allperms)."'";
		}

		// Ajout des droits trouves grace au critere whereforadd
		if (! empty($whereforadd))
		{
			//print "$module-$perms-$subperms";
			$sql = "SELECT id";
			$sql.= " FROM ".MAIN_DB_PREFIX."rights_def";
			$sql.= " WHERE ".$whereforadd;
			$sql.= " AND entity = ".$entity;

			$result=$this->db->query($sql);
			if ($result)
			{
				$num = $this->db->num_rows($result);
				$i = 0;
				while ($i < $num)
				{
					$obj = $this->db->fetch_object($result);
					$nid = $obj->id;

					$sql = "DELETE FROM ".MAIN_DB_PREFIX."user_rights WHERE fk_user = ".$this->id." AND fk_id=".$nid;
					if (! $this->db->query($sql)) $err++;
					$sql = "INSERT INTO ".MAIN_DB_PREFIX."user_rights (fk_user, fk_id) VALUES (".$this->id.", ".$nid.")";
//                    var_dump($sql);
//                    die();
					if (! $this->db->query($sql)) $err++;

					$i++;
				}
			}
			else
			{
				$err++;
				dol_print_error($this->db);
			}
		}

		if ($err) {
			$this->db->rollback();
			return -$err;
		}
		else {
			$this->db->commit();
			return 1;
		}

	}


	/**
	 *  Remove a right to the user
	 *
	 *  @param	int		$rid        Id du droit a retirer
	 *  @param  string	$allmodule  Retirer tous les droits du module allmodule
	 *  @param  string	$allperms   Retirer tous les droits du module allmodule, perms allperms
	 *  @param	int		$entity			Entity to use
	 *  @return int         		> 0 if OK, < 0 if OK
	 */
	function delrights($rid, $allmodule='', $allperms='', $entity='')
	{
		global $conf;

		$err=0;
		$wherefordel='';
		$entity = (! empty($entity)?$entity:$conf->entity);

		$this->db->begin();

		if (! empty($rid))
		{
			// Si on a demande supression d'un droit en particulier, on recupere
			// les caracteristiques module, perms et subperms de ce droit.
			$sql = "SELECT module, perms, subperms";
			$sql.= " FROM ".MAIN_DB_PREFIX."rights_def";
			$sql.= " WHERE id = '".$this->db->escape($rid)."'";
			$sql.= " AND entity = ".$entity;

			$result=$this->db->query($sql);
			if ($result) {
				$obj = $this->db->fetch_object($result);
				$module=$obj->module;
				$perms=$obj->perms;
				$subperms=$obj->subperms;
			}
			else {
				$err++;
				dol_print_error($this->db);
			}

			// Where pour la liste des droits a supprimer
			$wherefordel="id=".$this->db->escape($rid);
			// Suppression des droits induits
			if ($subperms=='lire' || $subperms=='read') $wherefordel.=" OR (module='$module' AND perms='$perms' AND subperms IS NOT NULL)";
			if ($perms=='lire' || $perms=='read')       $wherefordel.=" OR (module='$module')";
		}
		else {
			// On a demande suppression d'un droit sur la base d'un nom de module ou perms
			// Where pour la liste des droits a supprimer
			if (! empty($allmodule)) $wherefordel="module='".$this->db->escape($allmodule)."'";
			if (! empty($allperms))  $wherefordel=" AND perms='".$this->db->escape($allperms)."'";
		}

		// Suppression des droits selon critere defini dans wherefordel
		if (! empty($wherefordel))
		{
			//print "$module-$perms-$subperms";
			$sql = "SELECT id";
			$sql.= " FROM ".MAIN_DB_PREFIX."rights_def";
			$sql.= " WHERE $wherefordel";
			$sql.= " AND entity = ".$entity;

			$result=$this->db->query($sql);
			if ($result)
			{
				$num = $this->db->num_rows($result);
				$i = 0;
				while ($i < $num)
				{
					$obj = $this->db->fetch_object($result);
					$nid = $obj->id;

					$sql = "DELETE FROM ".MAIN_DB_PREFIX."user_rights";
					$sql.= " WHERE fk_user = ".$this->id." AND fk_id=".$nid;
					if (! $this->db->query($sql)) $err++;

					$i++;
				}
			}
			else
			{
				$err++;
				dol_print_error($this->db);
			}
		}

		if ($err) {
			$this->db->rollback();
			return -$err;
		}
		else {
			$this->db->commit();
			return 1;
		}

	}


	/**
	 *  Clear all permissions array of user
	 *
	 *  @return	void
	 *  @see	getrights
	 */
	function clearrights()
	{
		dol_syslog(get_class($this)."::clearrights reset user->rights");
		$this->rights='';
		$this->all_permissions_are_loaded=false;
		$this->_tab_loaded=array();
	}


	/**
	 *	Load permissions granted to user into object user
	 *
	 *	@param  string	$moduletag    Limit permission for a particular module ('' by default means load all permissions)
	 *	@return	void
	 *  @see	clearrights
	 */
	function getmentors($id){
		$sql = "select fk_id as id from " . MAIN_DB_PREFIX . "user_subdiv_mentor as u where u.fk_user= ".$id." and active = 1";
//        $sql = 'select rowid as id from regions limit 10';
		dol_syslog(get_class($this) . '::getregions', LOG_DEBUG);
		$resql = $this->db->query($sql);
		$setting = array(0);
		while($row = $this->db->fetch_array($resql)){
			$setting[]=$row['id'];
		}
//        var_dump($sql);
//        die();
		return $setting;
	}
    function getregions($id)
    {
		if(empty($id))
			$id=$this->id;
        $sql = "select fk_id as id from " . MAIN_DB_PREFIX . "user_regions as ur where ur.fk_user= ".$id." and active = 1";
//        $sql = 'select rowid as id from regions limit 10';
        dol_syslog(get_class($this) . '::getregions', LOG_DEBUG);
        $resql = $this->db->query($sql);
        $setting = array();
        while($row = $this->db->fetch_array($resql)){
            $setting[]=$row['id'];
        }
//        var_dump($setting);
//        die();
        return $setting;
    }
	function getUserList()
	{
		
		
	}		
	function getrights($moduletag='')
	{
		global $conf;
//		var_dump($this->rights);
//		die();
		if ($moduletag && isset($this->_tab_loaded[$moduletag]) && $this->_tab_loaded[$moduletag])
		{
			// Le fichier de ce module est deja charge
			return;
		}

		if ($this->all_permissions_are_loaded)
		{
			// Si les permissions ont deja ete charge pour ce user, on quitte
			return;
		}

		// Recuperation des droits utilisateurs + recuperation des droits groupes

		// D'abord les droits utilisateurs
		$sql = "SELECT r.module, r.perms, r.subperms";
		$sql.= " FROM ".MAIN_DB_PREFIX."user_rights as ur";
		$sql.= ", ".MAIN_DB_PREFIX."rights_def as r";
		$sql.= " WHERE r.id = ur.fk_id";
		$sql.= " AND r.entity IN (0,".(! empty($conf->multicompany->enabled) && ! empty($conf->multicompany->transverse_mode)?"1,":"").$conf->entity.")";
		$sql.= " AND ur.fk_user= ".$this->id;
		$sql.= " AND r.perms IS NOT NULL";
		if ($moduletag) $sql.= " AND r.module = '".$this->db->escape($moduletag)."'";

		dol_syslog(get_class($this).'::getrights', LOG_DEBUG);

		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$obj = $this->db->fetch_object($resql);

				$module=$obj->module;
				$perms=$obj->perms;
				$subperms=$obj->subperms;
//					if($obj->module == 'user') {
//						die('ha');
//					}
//				if($obj->module == 'users'){
//
//				}
				if ($perms)
				{
					if (! isset($this->rights) || ! is_object($this->rights)) $this->rights = new stdClass(); // For avoid error
					if (! isset($this->rights->$module) || ! is_object($this->rights->$module)) $this->rights->$module = new stdClass();
					if ($subperms)
					{
						if (! isset($this->rights->$module->$perms) || ! is_object($this->rights->$module->$perms)) $this->rights->$module->$perms = new stdClass();
						$this->rights->$module->$perms->$subperms = 1;
					}
					else
					{
						$this->rights->$module->$perms = 1;
					}

				}
				$i++;
			}
			$this->db->free($resql);
		}

		// Maintenant les droits groupes
		$sql = "SELECT r.module, r.perms, r.subperms";
		$sql.= " FROM ".MAIN_DB_PREFIX."usergroup_rights as gr,";
		$sql.= " ".MAIN_DB_PREFIX."usergroup_user as gu,";
		$sql.= " ".MAIN_DB_PREFIX."rights_def as r";
		$sql.= " WHERE r.id = gr.fk_id";
		if (! empty($conf->multicompany->enabled) && ! empty($conf->multicompany->transverse_mode)) {
			$sql.= " AND gu.entity IN (0,".$conf->entity.")";
		} else {
			$sql.= " AND r.entity = ".$conf->entity;
		}
		$sql.= " AND gr.fk_usergroup = gu.fk_usergroup";
		$sql.= " AND gu.fk_user = ".$this->id;
		$sql.= " AND r.perms IS NOT NULL";
		if ($moduletag) $sql.= " AND r.module = '".$this->db->escape($moduletag)."'";

		dol_syslog(get_class($this).'::getrights', LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$obj = $this->db->fetch_object($resql);

				$module=$obj->module;
				$perms=$obj->perms;
				$subperms=$obj->subperms;

				if ($perms)
				{
					if (! isset($this->rights) || ! is_object($this->rights)) $this->rights = new stdClass(); // For avoid error
					if (! isset($this->rights->$module) || ! is_object($this->rights->$module)) $this->rights->$module = new stdClass();
					if ($subperms)
					{
						if (! isset($this->rights->$module->$perms) || ! is_object($this->rights->$module->$perms)) $this->rights->$module->$perms = new stdClass();
						$this->rights->$module->$perms->$subperms = 1;
					}
					else
					{
						$this->rights->$module->$perms = 1;
					}

				}
				$i++;
			}
			$this->db->free($resql);
		}

		// For backward compatibility
		if (isset($this->rights->propale))
		{
			$this->rights->propal = $this->rights->propale;
		}

		if (! $moduletag)
		{
			// Si module etait non defini, alors on a tout charge, on peut donc considerer
			// que les droits sont en cache (car tous charges) pour cet instance de user
			$this->all_permissions_are_loaded=1;
		}
		else
		{
			// Si module defini, on le marque comme charge en cache
			$this->_tab_loaded[$moduletag]=1;
		}
	}

	/**
	 *  Change status of a user
	 *
	 *	@param	int		$statut		Status to set
	 *  @return int     			<0 if KO, 0 if nothing is done, >0 if OK
	 */
	function setstatus($statut)
	{
		global $conf,$langs,$user;

		$error=0;

		// Check parameters
		if ($this->statut == $statut) return 0;
		else $this->statut = $statut;

		$this->db->begin();

		// Deactivate user
		$sql = "UPDATE ".MAIN_DB_PREFIX."user";
		$sql.= " SET statut = ".$this->statut;
		$sql.= " WHERE rowid = ".$this->id;
		$result = $this->db->query($sql);

		dol_syslog(get_class($this)."::setstatus", LOG_DEBUG);
		if ($result)
		{
            // Call trigger
            $result=$this->call_trigger('USER_ENABLEDISABLE',$user);
            if ($result < 0) { $error++; }
            // End call triggers
		}

		if ($error)
		{
			$this->db->rollback();
			return -$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
	}


	/**
	 *    	Delete the user
	 *
	 * 		@return		int		<0 if KO, >0 if OK
	 */
	function delete()
	{
		global $user,$conf,$langs;

		$error=0;

		$this->db->begin();

		$this->fetch($this->id);

		dol_syslog(get_class($this)."::delete", LOG_DEBUG);

		// Remove rights
		$sql = "DELETE FROM ".MAIN_DB_PREFIX."user_rights WHERE fk_user = ".$this->id;

		if (! $error && ! $this->db->query($sql))
		{
			$error++;
        	$this->error = $this->db->lasterror();
		}

		// Remove group
		$sql = "DELETE FROM ".MAIN_DB_PREFIX."usergroup_user WHERE fk_user  = ".$this->id;
		if (! $error && ! $this->db->query($sql))
		{
			$error++;
        	$this->error = $this->db->lasterror();
		}

		// If contact, remove link
		if ($this->contact_id)
		{
			$sql = "UPDATE ".MAIN_DB_PREFIX."socpeople SET fk_user_creat = null WHERE rowid = ".$this->contact_id;
			if (! $error && ! $this->db->query($sql))
			{
				$error++;
	        	$this->error = $this->db->lasterror();
			}
		}

		// Remove extrafields
		if ((! $error) && (empty($conf->global->MAIN_EXTRAFIELDS_DISABLED))) // For avoid conflicts if trigger used
        {
			$result=$this->deleteExtraFields();
			if ($result < 0)
			{
           		$error++;
           		dol_syslog(get_class($this)."::delete error -4 ".$this->error, LOG_ERR);
           	}
        }

		// Remove user
        if (! $error)
        {
	        $sql = "DELETE FROM ".MAIN_DB_PREFIX."user WHERE rowid = ".$this->id;
	       	dol_syslog(get_class($this)."::delete", LOG_DEBUG);
	       	if (! $this->db->query($sql))
	       	{
	       		$error++;
	       		$this->error = $this->db->lasterror();
	       	}
        }

		if (! $error)
		{
            // Call trigger
            $result=$this->call_trigger('USER_DELETE',$user);
	        if ($result < 0)
	        {
	            $error++;
	            $this->db->rollback();
			    return -1;
	        }
            // End call triggers

			$this->db->commit();
			return 1;
		}
		else
		{
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 *  Create a user into database
	 *
	 *  @param	User	$user        	Objet user qui demande la creation
	 *  @param  int		$notrigger		1 ne declenche pas les triggers, 0 sinon
	 *  @return int			         	<0 si KO, id compte cree si OK
	 */
	function create($user,$notrigger=0)
	{
		global $conf,$langs;
		global $mysoc;

		// Clean parameters
		$this->login = trim($this->login);
		if (! isset($this->entity)) $this->entity=$conf->entity;	// If not defined, we use default value

		dol_syslog(get_class($this)."::create login=".$this->login.", user=".(is_object($user)?$user->id:''), LOG_DEBUG);

		// Check parameters
		if (! empty($conf->global->USER_MAIL_REQUIRED) && ! isValidEMail($this->email))
		{
			$langs->load("errors");
			$this->error = $langs->trans("ErrorBadEMail",$this->email);
			return -1;
		}

		$this->datec = dol_now();

		$error=0;
		$this->db->begin();

		$sql = "SELECT login FROM ".MAIN_DB_PREFIX."user";
		$sql.= " WHERE login ='".$this->db->escape($this->login)."'";
		$sql.= " AND entity IN (0,".$this->db->escape($conf->entity).")";

		dol_syslog(get_class($this)."::create", LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$this->db->free($resql);

			if ($num)
			{
				$this->error = 'ErrorLoginAlreadyExists';
				dol_syslog(get_class($this)."::create ".$this->error, LOG_WARNING);
				$this->db->rollback();
				return -6;
			}
			else
			{
				$sql = "INSERT INTO ".MAIN_DB_PREFIX."user (datec,login,ldap_sid,entity,subdiv_id,skype,usergroup_id,post_id,active,id_usr,dtChange,respon_id,respon_id2)";
				$sql.= " VALUES('".$this->db->idate($this->datec)."','".$this->db->escape($this->login)."','".$this->ldap_sid."',".$this->db->escape($this->entity).",".
                    $this->subdiv_id.",'".$this->skype."',".$this->usergroup_id.", ".$this->post_id.",1,".$user->id.",Now(),".$this->respon_id.",".$this->respon_id2.")";
//                var_dump($sql);
//                die();
				$result=$this->db->query($sql);

				dol_syslog(get_class($this)."::create", LOG_DEBUG);
				if ($result)
				{
					$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."user");

					// Set default rights
//					if ($this->set_default_rights() < 0)
//					{
//						$this->error='ErrorFailedToSetDefaultRightOfUser';
//						$this->db->rollback();
//						return -5;
//					}

					// Update minor fields
					$result = $this->update($user,1,1);
					if ($result < 0)
					{
						$this->db->rollback();
						return -4;
					}

					if (! empty($conf->global->STOCK_USERSTOCK_AUTOCREATE))
					{
						require_once DOL_DOCUMENT_ROOT.'/product/stock/class/entrepot.class.php';
						$langs->load("stocks");
						$entrepot = new Entrepot($this->db);
						$entrepot->libelle = $langs->trans("PersonalStock",$this->getFullName($langs));
						$entrepot->description = $langs->trans("ThisWarehouseIsPersonalStock",$this->getFullName($langs));
						$entrepot->statut = 1;
						$entrepot->country_id = $mysoc->country_id;
						$entrepot->create($user);
					}

					if (! $notrigger)
					{
                        // Call trigger
                        $result=$this->call_trigger('USER_CREATE',$user);
            	        if ($result < 0) { $error++; }
                        // End call triggers
					}

					if (! $error)
					{
						$this->db->commit();
						return $this->id;
					}
					else
					{
						//$this->error=$interface->error;
						dol_syslog(get_class($this)."::create ".$this->error, LOG_ERR);
						$this->db->rollback();
						return -3;
					}
				}
				else
				{
					$this->error=$this->db->lasterror();
					$this->db->rollback();
					return -2;
				}
			}
		}
		else
		{
			$this->error=$this->db->lasterror();
			$this->db->rollback();
			return -1;
		}
	}


	/**
	 *  Create a user from a contact object. User will be internal but if contact is linked to a third party, user will be external
	 *
	 *  @param	Contact	$contact    Object for source contact
	 * 	@param  string	$login      Login to force
	 *  @param  string	$password   Password to force
	 *  @return int 				<0 if error, if OK returns id of created user
	 */
	function create_from_contact($contact,$login='',$password='')
	{
		global $conf,$user,$langs;

		$error=0;

		// Positionne parametres
		$this->admin		= 0;
		$this->lastname		= $contact->lastname;
		$this->firstname	= $contact->firstname;
		$this->email		= $contact->email;
    	$this->skype 		= $contact->skype;
		$this->office_phone	= $contact->phone_pro;
		$this->office_fax	= $contact->fax;
		$this->user_mobile	= $contact->phone_mobile;
		$this->address      = $contact->address;
		$this->zip          = $contact->zip;
		$this->town         = $contact->town;
		$this->state_id     = $contact->state_id;
		$this->country_id   = $contact->country_id;

		if (empty($login)) $login=strtolower(substr($contact->firstname, 0, 4)) . strtolower(substr($contact->lastname, 0, 4));
		$this->login = $login;

		$this->db->begin();

		// Cree et positionne $this->id
		$result=$this->create($user);
		if ($result > 0)
		{
			$sql = "UPDATE ".MAIN_DB_PREFIX."user";
			$sql.= " SET fk_socpeople=".$contact->id;
			if ($contact->socid) $sql.=", fk_societe=".$contact->socid;
			$sql.= " WHERE rowid=".$this->id;
			$resql=$this->db->query($sql);

			dol_syslog(get_class($this)."::create_from_contact", LOG_DEBUG);
			if ($resql)
			{
                // Call trigger
                $result=$this->call_trigger('USER_CREATE_FROM_CONTACT',$user);
                if ($result < 0) { $error++; $this->db->rollback(); return -1; }
                // End call triggers

				$this->db->commit();
				return $this->id;
			}
			else
			{
				$this->error=$this->db->error();

				$this->db->rollback();
				return -1;
			}
		}
		else
		{
			// $this->error deja positionne
			dol_syslog(get_class($this)."::create_from_contact - 0");

			$this->db->rollback();
			return $result;
		}

	}

	/**
	 *  Create a user into database from a member object
	 *
	 *  @param	Adherent	$member		Object member source
	 * 	@param	string		$login		Login to force
	 *  @return int						<0 if KO, if OK, return id of created account
	 */
	function create_from_member($member,$login='')
	{
		global $conf,$user,$langs;

		// Positionne parametres
		$this->admin = 0;
		$this->lastname     = $member->lastname;
		$this->firstname    = $member->firstname;
		$this->email        = $member->email;
		$this->fk_member    = $member->id;
		$this->pass         = $member->pass;
		$this->address      = $member->address;
		$this->zip          = $member->zip;
		$this->town         = $member->town;
		$this->state_id     = $member->state_id;
		$this->country_id   = $member->country_id;

		if (empty($login)) $login=strtolower(substr($member->firstname, 0, 4)) . strtolower(substr($member->lastname, 0, 4));
		$this->login = $login;

		$this->db->begin();

		// Create and set $this->id
		$result=$this->create($user);
		if ($result > 0)
		{
			$newpass=$this->setPassword($user,$this->pass);
			if (is_numeric($newpass) && $newpass < 0) $result=-2;

			if ($result > 0 && $member->fk_soc)	// If member is linked to a thirdparty
			{
				$sql = "UPDATE ".MAIN_DB_PREFIX."user";
				$sql.= " SET fk_societe=".$member->fk_soc;
				$sql.= " WHERE rowid=".$this->id;

				dol_syslog(get_class($this)."::create_from_member", LOG_DEBUG);
				$resql=$this->db->query($sql);
				if ($resql)
				{
					$this->db->commit();
					return $this->id;
				}
				else
				{
					$this->error=$this->db->lasterror();

					$this->db->rollback();
					return -1;
				}
			}
		}

		if ($result > 0)
		{
			$this->db->commit();
			return $this->id;
		}
		else
		{
			// $this->error deja positionne
			$this->db->rollback();
			return -2;
		}
	}

	/**
	 *    Assign rights by default
	 *
	 *    @return     Si erreur <0, si ok renvoi le nbre de droits par defaut positionnes
	 */
	function set_default_rights()
	{
		global $conf;

		$sql = "SELECT id FROM ".MAIN_DB_PREFIX."rights_def";
		$sql.= " WHERE bydefault = 1";
		$sql.= " AND entity = ".$conf->entity;

		$resql=$this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;
			$rd = array();
			while ($i < $num)
			{
				$row = $this->db->fetch_row($resql);
				$rd[$i] = $row[0];
				$i++;
			}
			$this->db->free($resql);
		}
		$i = 0;
		while ($i < $num)
		{

			$sql = "DELETE FROM ".MAIN_DB_PREFIX."user_rights WHERE fk_user = $this->id AND fk_id=$rd[$i]";
			$result=$this->db->query($sql);

			$sql = "INSERT INTO ".MAIN_DB_PREFIX."user_rights (fk_user, fk_id) VALUES ($this->id, $rd[$i])";
			$result=$this->db->query($sql);
			if (! $result) return -1;
			$i++;
		}

		return $i;
	}

	/**
	 *  	Update a user into database (and also password if this->pass is defined)
	 *
	 *		@param	User	$user				User qui fait la mise a jour
	 *    	@param  int		$notrigger			1 ne declenche pas les triggers, 0 sinon
	 *		@param	int		$nosyncmember		0=Synchronize linked member (standard info), 1=Do not synchronize linked member
	 *		@param	int		$nosyncmemberpass	0=Synchronize linked member (password), 1=Do not synchronize linked member
	 *    	@return int 		        		<0 si KO, >=0 si OK
	 */
	function getContactResponsibility($htmlname='responsibility', $size=10,$addParam){

		$sql='select distinct `responsibility`.`rowid`, `responsibility`.`name` from llx_societe';
		if($addParam != 'all')
			$sql.= ' inner join (select fk_id from `llx_user_regions` where fk_user = '.$this->id.' and active = 1) regions on regions.fk_id = llx_societe.region_id';
		$sql.=' inner join `llx_societe_contact` on `llx_societe_contact`.`socid`=`llx_societe`.`rowid`
			inner join `responsibility` on `responsibility`.`rowid` = `llx_societe_contact`.`respon_id`
			where 1';
//		if($addParam != 'all')
//			$sql .=' and `fk_user_creat` = '.$this->id;
		$sql.='	and `responsibility`.`active` = 1
			and `llx_societe_contact`.`active` = 1
			and llx_societe.`active` = 1
			order by `responsibility`.`name`';
		$res = $this->db->query($sql);
		$out =  '<select name = "'.$htmlname.'[]" id="'.$htmlname.'" size="'.$size.'" '.($size>1?"multiple":"").' class="combobox" >';
		if($this->db->num_rows($res)>0) {
			for ($i = 0; $i < $this->db->num_rows($res); $i++) {
				$obj = $this->db->fetch_object($res);
				$out .= '<option  value="'.$obj->rowid.'" >'.trim($obj->name).'</option>';
			}
		}
		$out .= '</select>';
		return $out;
	}
	function getContactPostsList($htmlname='postlist', $size=10,$addParam,$post_id=0){
		
//		echo '<pre>';
//		var_dump($htmlname, $size,$addParam);
//		echo '</pre>';
//		die();
		$sql='select distinct `llx_post`.`rowid`, `llx_post`.`postname` from llx_societe ';
		if($addParam != 'all')
			$sql.= ' inner join (select fk_id from `llx_user_regions` where fk_user = '.$this->id.' and active = 1) regions on regions.fk_id = llx_societe.region_id';
		$sql.='   inner join `llx_societe_contact` on `llx_societe_contact`.`socid`=`llx_societe`.`rowid`
			inner join `llx_post` on `llx_post`.`rowid` = `llx_societe_contact`.`post_id`
			where 1';
//			$sql .=' and `fk_user_creat` = '.$this->id;
		$sql.=' and `llx_post`.`active` = 1
			and `llx_societe_contact`.`active` = 1
			and llx_societe.`active` = 1
			order by `llx_post`.`postname`';
		$res = $this->db->query($sql);
//		echo '<pre>';
//		var_dump($sql);
//		echo '</pre>';
//		die();
		if(!$res)
			dol_print_error($this->db);
		$out =  '<select name = "'.$htmlname.'[]" id="'.$htmlname.'" size="'.$size.'" '.($size>1?"multiple":"").' class="combobox">';
		if($this->db->num_rows($res)>0) {
			for ($i = 0; $i < $this->db->num_rows($res); $i++) {
				$obj = $this->db->fetch_object($res);
				$out .= '<option  value="'.$obj->rowid.'" '.($obj->rowid == $post_id?'selected = "selected"':'').'>'.trim($obj->postname).'</option>';
			}
		}
		$out .= '</select>';
		return $out;
	}
	function getAreasList($region_id, $htmlname='state_filter', $size=1, $event ='this.form.submit()', $addParam='', $countryID='226'){

		if($this->respon_alias == 'sale'|| $this->respon_alias2 == 'sale' || $addParam == 'all') {
			if ($addParam == 'all')
				$sql = 'select regions.rowid, regions.state_id, trim(states.name) as states_name, trim(regions.name) as regions_name from states, regions
					where 1 and regions.state_id=states.rowid order by regions_name asc, states_name asc';
			else
				$sql = 'select regions.rowid, regions.state_id, trim(states.name) as states_name, trim(regions.name) as regions_name from states, regions, ' . MAIN_DB_PREFIX . 'user_regions ur
					where ur.fk_user=' . $this->id . ' and ur.active = 1 and ur.fk_id=regions.rowid and regions.state_id=states.rowid order by regions_name asc, states_name asc';
		}elseif (!empty($countryID)){
			$sql = 'select regions.rowid, regions.state_id, trim(states.name) as states_name, trim(regions.name) as regions_name from states, regions
					where 1 and states.country_id = '.$countryID.' and regions.state_id=states.rowid order by regions_name asc, states_name asc';
		}elseif($this->respon_alias == 'dir_depatment' || $this->respon_alias == 'gen_dir') {
			$sql = 'select regions.rowid, regions.state_id, trim(states.name) as states_name, trim(regions.name) as regions_name
				from states
				inner join regions on `regions`.`state_id` = `states`.`rowid`
				inner join llx_user_regions on `llx_user_regions`.`fk_id` = `regions`.`rowid`
				inner join `llx_user` on `llx_user`.`rowid` = `llx_user_regions`.`fk_user`
				where `llx_user`.`subdiv_id` = '.$this->subdiv_id.'
				and `llx_user_regions`.active = 1
				and `llx_user`.`active` = 1';

		}

//		var_dump($this->respon_alias, $this->respon_alias2);
//		die($sql);
		$res = $this->db->query($sql);
		$out =  '<select name = "'.$htmlname.'" id="'.$htmlname.'" size="'.$size.'" '.($size>1?"multiple":"").' class="combobox" '.(!empty($event)?'onchange="'.$event.'"':'').'>';
			$out .='<option value="0" class="multiple_header_table">Відобразити все</option>\r\n';
		if($this->db->num_rows($res)>0) {
			for ($i = 0; $i < $this->db->num_rows($res); $i++) {
				$obj = $this->db->fetch_object($res);
		//        if($region_id == 0) {
		//            $region_id = $obj->rowid;
		//        }
				$selected = $region_id == $obj->rowid;
				if(!$selected)
					$out .= '<option state_id="'.$obj->state_id.'" value="'.$obj->rowid.'" >'.trim($obj->regions_name).' ('.decrease_word($obj->states_name).')</option>';
				else {
					$out .= '<option state_id="'.$obj->state_id.'" value="' . $obj->rowid . '" selected = "selected" >' . trim($obj->regions_name) . ' (' . decrease_word($obj->states_name) . ')</option>';
//					$state_id = ;
				}
			}
		}
		$sql = "select rowid, name from `category_counterparty`
			where `name` like 'Перевіз%' or `name` like 'Навантаж%'
			and active = 1 order by `name`";
		$res = $this->db->query($sql);
		while($obj = $this->db->fetch_object($res)) {
			$out .= '<option value="category_id_'.$obj->rowid.'" category_id="'.$obj->rowid.'">'.trim($obj->name).'</option>';
		}
		if($addParam == 'all'){
			$out .= '<option value="workers" category_id="0">Співробітники "Техніки і Технології"</option>';
		}
		$out .= '</select>';
		return $out;
	}
	function getCategoriesContractor($id_usr){
		$sql = 'select case when fk_categories <> 0 then fk_categories else other_categories end fk_categories from llx_user_categories_contractor where fk_user = '.(empty($id_usr)?$this->id:$id_usr).' and active = 1';
//		die($sql);
		$res = $this->db->query($sql);
		if(!$res)
			dol_print_error($this->db);
		$categories = array();
		while($obj = $this->db->fetch_object($res)){
			$val = is_numeric($obj->fk_categories)?$obj->fk_categories:"'".$obj->fk_categories."'";
			if(!in_array($obj->fk_categories, $categories))
				$categories[]=$val;
		}

		return $categories;
	}
	function getResponding($id_usr, $viewalias=false){
		$respon = array();
		if(!empty($this->respon_id)&&!in_array($this->respon_id,$respon))
			$respon[]=$this->respon_id;
		if(!empty($this->respon_id2)&&!in_array($this->respon_id2,$respon))
			$respon[]=$this->respon_id2;
		$sql = "select fk_respon, `responsibility`.alias from `llx_user_responsibility` 
					inner join `responsibility` on `responsibility`.rowid = `llx_user_responsibility`.fk_respon
					where fk_user = ".$id_usr." and `llx_user_responsibility`.active = 1";
		if(count($respon))
			$sql.="
				 union
				 select rowid, alias
				 from `responsibility`
				 where rowid in (".implode(',',$respon).")";
		$res = $this->db->query($sql);
		$respon = array();

		if(!$res)
			dol_print_error($this->db);
		while($obj = $this->db->fetch_object($res)){
			if($viewalias) {
				if (!empty($obj->alias)&&!in_array($obj->alias, $respon))
					$respon[] = $obj->alias;
			}else {
				if (!empty($obj->fk_respon)&&!in_array($obj->fk_respon, $respon))
					$respon[] = $obj->fk_respon;
			}
		}
		return $respon;
	}
	function getLineActive($id_usr){

		$id_usr = (empty($id_usr)?$this->id:$id_usr);
//		$sql = 'select fk_lineactive from llx_user_lineactive where fk_user = '.$id_usr.' and active = 1';
//
//		$res = $this->db->query($sql);
//		if(!$res)
//			dol_print_error($this->db);
		$lineactive = array(0);
//		while($obj = $this->db->fetch_object($res)){
//			if(!in_array($obj->fk_lineactive, $lineactive))
//				$lineactive[]=$obj->fk_lineactive;
//		}
		$responsibility = $this->getResponding($id_usr, true);

		$sql = "select res.alias, res2.alias alias2 from llx_user
			left join `responsibility` res on res.rowid = llx_user.respon_id
			left join `responsibility` res2 on res2.rowid = llx_user.respon_id2
			where llx_user.rowid = ".$id_usr;
		$resAlias = $this->db->query($sql);
		if(!$resAlias)
			dol_print_error($this->db);
		$object = $this->db->fetch_object($resAlias);
		if(count(array_intersect($responsibility, array('service','purchase','wholesale_purchase'))) > 0) {
			foreach ($lineactive as $item) {
				$sql = 'select ' . $item . ' as category_id
              union
              select category_id from `oc_category`
              where parent_id=' . $item . '
              union
              select parent_id from `oc_category`
              where category_id=' . $item;
				$res = $this->db->query($sql);
				if (!$res)
					dol_print_error($this->db);
				while ($obj = $this->db->fetch_object($res)) {
					if (!in_array($obj->category_id, $lineactive))
						$lineactive[] = $obj->category_id;
				}
			}
		}
		if(count(array_intersect($responsibility, array('marketing'))) > 0) {
			$sql = "select fk_lineactive from `llx_user_lineactive`
				where `fk_user` = ".$id_usr."
				and active = 1
				and page is null";
			$res = $this->db->query($sql);
			if (!$res)
				dol_print_error($this->db);
			while ($obj = $this->db->fetch_object($res)) {
				if (!in_array($obj->fk_lineactive, $lineactive))
					$lineactive[] = $obj->fk_lineactive;
			}
		}

//		var_dump(implode(',',$lineactive));
//		die();
		return $lineactive;
	}
	function update($user,$notrigger=0,$nosyncmember=0,$nosyncmemberpass=0)
	{
		global $conf, $langs, $hookmanager;

		$nbrowsaffected=0;
		$error=0;

		dol_syslog(get_class($this)."::update notrigger=".$notrigger.", nosyncmember=".$nosyncmember.", nosyncmemberpass=".$nosyncmemberpass);

		// Clean parameters
		$this->lastname     = trim($this->lastname);
		$this->firstname    = trim($this->firstname);
		$this->login        = trim($this->login);
		$this->pass         = trim($this->pass);
		$this->office_phone = trim($this->office_phone);
		$this->office_fax   = trim($this->office_fax);
		$this->user_mobile  = trim($this->user_mobile);
		$this->email        = trim($this->email);
		$this->skype        = trim($this->skype);
		$this->job    		= trim($this->job);
		$this->signature    = trim($this->signature);
		$this->note         = trim($this->note);
		$this->openid       = trim(empty($this->openid)?'':$this->openid);    // Avoid warning
		$this->admin        = $this->admin?$this->admin:0;
		$this->respon_id    = $this->respon_id?$this->respon_id:0;
		$this->respon_id2    = $this->respon_id2?$this->respon_id2:0;
		$this->address		= empty($this->address)?'':$this->address;
		$this->zip			= empty($this->zip)?'':$this->zip;
		$this->town			= empty($this->town)?'':$this->town;
		$this->accountancy_code = trim($this->accountancy_code);
		$this->color 		= empty($this->color)?'':$this->color;

		// Check parameters
		if (! empty($conf->global->USER_MAIL_REQUIRED) && ! isValidEMail($this->email))
		{
			$langs->load("errors");
			$this->error = $langs->trans("ErrorBadEMail",$this->email);
			return -1;
		}

		$this->db->begin();

		// Mise a jour autres infos
		$sql = "UPDATE ".MAIN_DB_PREFIX."user SET";
		$sql.= " lastname = '".$this->db->escape($this->lastname)."'";
		$sql.= ", firstname = '".$this->db->escape($this->firstname)."'";
		$sql.= ", login = '".$this->db->escape($this->login)."'";
		$sql.= ", admin = ".$this->admin;
		$sql.= ", respon_id = ".$this->respon_id;
		$sql.= ", respon_id2 = ".$this->respon_id2;
		$sql.= ", subdiv_id = ".$this->subdiv_id;
		$sql.= ", post_id = ".$this->post_id;
		$sql.= ", address = '".$this->db->escape($this->address)."'";
		$sql.= ", zip = '".$this->db->escape($this->zip)."'";
		$sql.= ", town = '".$this->db->escape($this->town)."'";
		$sql.= ", fk_state = ".((! empty($this->state_id) && $this->state_id > 0)?"'".$this->db->escape($this->state_id)."'":"null");
		$sql.= ", fk_country = ".((! empty($this->country_id) && $this->country_id > 0)?"'".$this->db->escape($this->country_id)."'":"null");
		$sql.= ", office_phone = '".$this->db->escape($this->office_phone)."'";
		$sql.= ", office_fax = '".$this->db->escape($this->office_fax)."'";
		$sql.= ", user_mobile = '".$this->db->escape($this->user_mobile)."'";
		$sql.= ", email = '".$this->db->escape($this->email)."'";
		$sql.= ", skype = '".$this->db->escape($this->skype)."'";
		$sql.= ", job = '".$this->db->escape($this->job)."'";
		$sql.= ", signature = '".$this->db->escape($this->signature)."'";
		$sql.= ", accountancy_code = '".$this->db->escape($this->accountancy_code)."'";
		$sql.= ", color = '".$this->db->escape($this->color)."'";
		$sql.= ", note = '".$this->db->escape($this->note)."'";
		$sql.= ", photo = ".($this->photo?"'".$this->db->escape($this->photo)."'":"null");
		$sql.= ", openid = ".($this->openid?"'".$this->db->escape($this->openid)."'":"null");
		$sql.= ", fk_user = ".($this->fk_user > 0?"'".$this->db->escape($this->fk_user)."'":"null");
		if (isset($this->thm) || $this->thm != '')                 $sql.= ", thm= ".($this->thm != ''?"'".$this->db->escape($this->thm)."'":"null");
		if (isset($this->tjm) || $this->tjm != '')                 $sql.= ", tjm= ".($this->tjm != ''?"'".$this->db->escape($this->tjm)."'":"null");
//		if (isset($this->salary) || $this->salary != '')           $sql.= ", salary= ".($this->salary != ''?"'".$this->db->escape($this->salary)."'":"null");
		if (isset($this->salaryextra) || $this->salaryextra != '') $sql.= ", salaryextra= ".($this->salaryextra != ''?"'".$this->db->escape($this->salaryextra)."'":"null");
		$sql.= ", weeklyhours= ".($this->weeklyhours != ''?"'".$this->db->escape($this->weeklyhours)."'":"null");
		$sql.= ", entity = '".$this->entity."'";
		$sql.= " WHERE rowid = ".$this->id;
//die($sql);
		dol_syslog(get_class($this)."::update", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$nbrowsaffected+=$this->db->affected_rows($resql);

			// Update password
			if ($this->pass)
			{
				if ($this->pass != $this->pass_indatabase && $this->pass != $this->pass_indatabase_crypted)
				{
					// Si mot de passe saisi et different de celui en base
					$result=$this->setPassword($user,$this->pass,0,$notrigger,$nosyncmemberpass);
					if (! $nbrowsaffected) $nbrowsaffected++;
				}
			}

			// If user is linked to a member, remove old link to this member
			if ($this->fk_member > 0)
			{
				$sql = "UPDATE ".MAIN_DB_PREFIX."user SET fk_member = NULL where fk_member = ".$this->fk_member;
				dol_syslog(get_class($this)."::update", LOG_DEBUG);
				$resql = $this->db->query($sql);
				if (! $resql) { $this->error=$this->db->error(); $this->db->rollback(); return -5; }
			}
			// Set link to user
			$sql = "UPDATE ".MAIN_DB_PREFIX."user SET fk_member =".($this->fk_member>0?$this->fk_member:'null')." where rowid = ".$this->id;
			dol_syslog(get_class($this)."::update", LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (! $resql) { $this->error=$this->db->error(); $this->db->rollback(); return -5; }

			if ($nbrowsaffected)	// If something has changed in data
			{
				if ($this->fk_member > 0 && ! $nosyncmember)
				{
					require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent.class.php';

					// This user is linked with a member, so we also update members informations
					// if this is an update.
					$adh=new Adherent($this->db);
					$result=$adh->fetch($this->fk_member);

					if ($result >= 0)
					{
						$adh->firstname=$this->firstname;
						$adh->lastname=$this->lastname;
						$adh->login=$this->login;
						$adh->pass=$this->pass;
						$adh->societe=(empty($adh->societe) && $this->societe_id ? $this->societe_id : $adh->societe);

						$adh->email=$this->email;
						$adh->skype=$this->skype;
						$adh->phone=$this->office_phone;
						$adh->phone_mobile=$this->user_mobile;

						$adh->note=$this->note;

						$adh->user_id=$this->id;
						$adh->user_login=$this->login;

						$result=$adh->update($user,0,1);
						if ($result < 0)
						{
							$this->error=$luser->error;
							dol_syslog(get_class($this)."::update ".$this->error,LOG_ERR);
							$error++;
						}
					}
					else
					{
						$this->error=$adh->error;
						$error++;
					}
				}
			}

			$action='update';

			// Actions on extra fields (by external module or standard code)
			// FIXME le hook fait double emploi avec le trigger !!
			$hookmanager->initHooks(array('userdao'));
			$parameters=array('socid'=>$this->id);
			$reshook=$hookmanager->executeHooks('insertExtraFields',$parameters,$this,$action);    // Note that $action and $object may have been modified by some hooks
			if (empty($reshook))
			{
				if (empty($conf->global->MAIN_EXTRAFIELDS_DISABLED)) // For avoid conflicts if trigger used
				{
					$result=$this->insertExtraFields();
					if ($result < 0)
					{
						$error++;
					}
				}
			}
			else if ($reshook < 0) $error++;

			if (! $error && ! $notrigger)
			{
                // Call trigger
                $result=$this->call_trigger('USER_MODIFY',$user);
                if ($result < 0) { $error++; }
                // End call triggers
			}

			if (! $error)
			{
				$this->db->commit();
				return $nbrowsaffected;
			}
			else
			{
				dol_syslog(get_class($this)."::update error=".$this->error,LOG_ERR);
				$this->db->rollback();
				return -1;
			}
		}
		else
		{
			$this->error=$this->db->lasterror();
			$this->db->rollback();
			return -2;
		}

	}

	/**
	 *    Mise a jour en base de la date de derniere connexion d'un utilisateur
	 *	  Fonction appelee lors d'une nouvelle connexion
	 *
	 *    @return     <0 si echec, >=0 si ok
	 */
	function update_last_login_date()
	{
		$now=dol_now();
		$sql = "update ".MAIN_DB_PREFIX."user SET";
		$sql.= " datefirsttodaylogin = '".$this->db->idate($now)."'";
		$sql.= " WHERE rowid = ".$this->id;
		$sql.= " AND (date(datefirsttodaylogin) <> date(now()) OR datefirsttodaylogin is null)";
//		echo '<pre>';
//		echo $sql;
//		echo '</pre>';
//		die();
		$resql = $this->db->query($sql);
		if(!$resql)
			dol_print_error($this->db);
		//------------
		$sql = "UPDATE ".MAIN_DB_PREFIX."user SET";
		$sql.= " datepreviouslogin = datelastlogin,";
		$sql.= " datelastlogin = '".$this->db->idate($now)."',";
		$sql.= " tms = tms";    // La date de derniere modif doit changer sauf pour la mise a jour de date de derniere connexion
		$sql.= " WHERE rowid = ".$this->id;

		dol_syslog(get_class($this)."::update_last_login_date user->id=".$this->id." ".$sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$this->datepreviouslogin=$this->datelastlogin;
			$this->datelastlogin=$now;
			return 1;
		}
		else
		{
			$this->error=$this->db->lasterror().' sql='.$sql;
			return -1;
		}
	}


	/**
	 *  Change password of a user
	 *
	 *  @param	User	$user             		Object user of user making change
	 *  @param  string	$password         		New password in clear text (to generate if not provided)
	 *	@param	int		$changelater			1=Change password only after clicking on confirm email
	 *	@param	int		$notrigger				1=Does not launch triggers
	 *	@param	int		$nosyncmember	        Do not synchronize linked member
	 *  @return string 			          		If OK return clear password, 0 if no change, < 0 if error
	 */
	function setPassword($user, $password='', $changelater=0, $notrigger=0, $nosyncmember=0)
	{
		global $conf, $langs;
		require_once DOL_DOCUMENT_ROOT .'/core/lib/security2.lib.php';

		$error=0;

		dol_syslog(get_class($this)."::setPassword user=".$user->id." password=".preg_replace('/./i','*',$password)." changelater=".$changelater." notrigger=".$notrigger." nosyncmember=".$nosyncmember, LOG_DEBUG);

		// If new password not provided, we generate one
		if (! $password)
		{
			$password=getRandomPassword(false);
		}

		// Crypte avec md5
		$password_crypted = dol_hash($password);

		// Mise a jour
		if (! $changelater)
		{
		    if (! is_object($this->oldcopy)) $this->oldcopy=dol_clone($this);

		    $this->db->begin();

		    $sql = "UPDATE ".MAIN_DB_PREFIX."user";
			$sql.= " SET pass_crypted = '".$this->db->escape($password_crypted)."',";
			$sql.= " pass_temp = null";
			if (! empty($conf->global->DATABASE_PWD_ENCRYPTED))
			{
				$sql.= ", pass = null";
			}
			else
			{
				$sql.= ", pass = '".$this->db->escape($password)."'";
			}
			$sql.= " WHERE rowid = ".$this->id;

			dol_syslog(get_class($this)."::setPassword", LOG_DEBUG);
			$result = $this->db->query($sql);
			if ($result)
			{
				if ($this->db->affected_rows($result))
				{
					$this->pass=$password;
					$this->pass_indatabase=$password;
					$this->pass_indatabase_crypted=$password_crypted;

					if ($this->fk_member && ! $nosyncmember)
					{
						require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent.class.php';

						// This user is linked with a member, so we also update members informations
						// if this is an update.
						$adh=new Adherent($this->db);
						$result=$adh->fetch($this->fk_member);

						if ($result >= 0)
						{
							$result=$adh->setPassword($user,$this->pass,0,1);	// Cryptage non gere dans module adherent
							if ($result < 0)
							{
								$this->error=$adh->error;
								dol_syslog(get_class($this)."::setPassword ".$this->error,LOG_ERR);
								$error++;
							}
						}
						else
						{
							$this->error=$adh->error;
							$error++;
						}
					}

					dol_syslog(get_class($this)."::setPassword notrigger=".$notrigger." error=".$error,LOG_DEBUG);

					if (! $error && ! $notrigger)
					{
                        // Call trigger
                        $result=$this->call_trigger('USER_NEW_PASSWORD',$user);
                        if ($result < 0) { $error++; $this->db->rollback(); return -1; }
                        // End call triggers
					}

					$this->db->commit();
					return $this->pass;
				}
				else
				{
				    $this->db->rollback();
					return 0;
				}
			}
			else
			{
			    $this->db->rollback();
				dol_print_error($this->db);
				return -1;
			}
		}
		else
		{
			// We store clear password in password temporary field.
			// After receiving confirmation link, we will crypt it and store it in pass_crypted
			$sql = "UPDATE ".MAIN_DB_PREFIX."user";
			$sql.= " SET pass_temp = '".$this->db->escape($password)."'";
			$sql.= " WHERE rowid = ".$this->id;

			dol_syslog(get_class($this)."::setPassword", LOG_DEBUG);	// No log
			$result = $this->db->query($sql);
			if ($result)
			{
				return $password;
			}
			else
			{
				dol_print_error($this->db);
				return -3;
			}
		}
	}


	/**
	 *  Send new password by email
	 *
	 *  @param	User	$user           Object user that send email
	 *  @param	string	$password       New password
	 *	@param	int		$changelater	1=Change password only after clicking on confirm email
	 *  @return int 		            < 0 si erreur, > 0 si ok
	 */
	function send_password($user, $password='', $changelater=0)
	{
		global $conf,$langs;
		global $dolibarr_main_url_root;

		require_once DOL_DOCUMENT_ROOT.'/core/class/CMailFile.class.php';

		$msgishtml=0;

		// Define $msg
		$mesg = '';

		$outputlangs=new Translate("",$conf);
		if (isset($this->conf->MAIN_LANG_DEFAULT)
		&& $this->conf->MAIN_LANG_DEFAULT != 'auto')
		{	// If user has defined its own language (rare because in most cases, auto is used)
			$outputlangs->getDefaultLang($this->conf->MAIN_LANG_DEFAULT);
		}
		else
		{	// If user has not defined its own language, we used current language
			$outputlangs=$langs;
		}

		$outputlangs->load("main");
		$outputlangs->load("errors");
		$outputlangs->load("users");
		$outputlangs->load("other");

		$subject = $outputlangs->transnoentitiesnoconv("SubjectNewPassword");

		// Define $urlwithroot
		//$urlwithouturlroot=preg_replace('/'.preg_quote(DOL_URL_ROOT,'/').'$/i','',trim($dolibarr_main_url_root));
		//$urlwithroot=$urlwithouturlroot.DOL_URL_ROOT;		// This is to use external domain name found into config file
		$urlwithroot=DOL_MAIN_URL_ROOT;						// This is to use same domain name than current

		if (! $changelater)
		{
			$mesg.= $outputlangs->transnoentitiesnoconv("RequestToResetPasswordReceived").".\n";
			$mesg.= $outputlangs->transnoentitiesnoconv("NewKeyIs")." :\n\n";
			$mesg.= $outputlangs->transnoentitiesnoconv("Login")." = ".$this->login."\n";
			$mesg.= $outputlangs->transnoentitiesnoconv("Password")." = ".$password."\n\n";
			$mesg.= "\n";
			$url = $urlwithroot.'/';
			$mesg.= $outputlangs->transnoentitiesnoconv("ClickHereToGoTo", $conf->global->MAIN_APPLICATION_TITLE).': '.$url."\n\n";
			$mesg.= "--\n";
			$mesg.= $user->getFullName($outputlangs);	// Username that make then sending
		}
		else
		{
			$mesg.= $outputlangs->transnoentitiesnoconv("RequestToResetPasswordReceived")."\n";
			$mesg.= $outputlangs->transnoentitiesnoconv("NewKeyWillBe")." :\n\n";
			$mesg.= $outputlangs->transnoentitiesnoconv("Login")." = ".$this->login."\n";
			$mesg.= $outputlangs->transnoentitiesnoconv("Password")." = ".$password."\n\n";
			$mesg.= "\n";
			$mesg.= $outputlangs->transnoentitiesnoconv("YouMustClickToChange")." :\n";
			$url = $urlwithroot.'/user/passwordforgotten.php?action=validatenewpassword&username='.$this->login."&passwordhash=".dol_hash($password);
			$mesg.= $url."\n\n";
			$mesg.= $outputlangs->transnoentitiesnoconv("ForgetIfNothing")."\n\n";
			dol_syslog(get_class($this)."::send_password url=".$url);
		}
        $mailfile = new CMailFile(
            $subject,
            $this->email,
            $conf->notification->email_from,
            $mesg,
            array(),
            array(),
            array(),
            '',
            '',
            0,
            $msgishtml
        );
//		echo '<pre>';
//		var_dump($this);
//		echo '</pre>';
//		die();

		if ($mailfile->sendfile())
		{
			return 1;
		}
		else
		{
			$langs->trans("errors");
			$this->error=$langs->trans("ErrorFailedToSendPassword").' '.$mailfile->error;
			return -1;
		}
	}

	/**
	 * 		Renvoie la derniere erreur fonctionnelle de manipulation de l'objet
	 *
	 * 		@return    string      chaine erreur
	 */
	function error()
	{
		return $this->error;
	}


	/**
	 *    	Read clicktodial information for user
	 *
	 * 		@return		<0 if KO, >0 if OK
	 */
	function fetch_clicktodial()
	{
		$sql = "SELECT url, login, pass, poste ";
		$sql.= " FROM ".MAIN_DB_PREFIX."user_clicktodial as u";
		$sql.= " WHERE u.fk_user = ".$this->id;

		$resql = $this->db->query($sql);
		if ($resql)
		{
			if ($this->db->num_rows($resql))
			{
				$obj = $this->db->fetch_object($resql);

				$this->clicktodial_url = $obj->url;
				$this->clicktodial_login = $obj->login;
				$this->clicktodial_password = $obj->pass;
				$this->clicktodial_poste = $obj->poste;
			}

			$this->clicktodial_loaded = 1;	// Data loaded (found or not)

			$this->db->free($resql);
			return 1;
		}
		else
		{
			$this->error=$this->db->error();
			return -1;
		}
	}

	/**
	 *  Update clicktodial info
	 *
	 *  @return	void
	 */
	function update_clicktodial()
	{
		$this->db->begin();

		$sql = "DELETE FROM ".MAIN_DB_PREFIX."user_clicktodial";
		$sql .= " WHERE fk_user = ".$this->id;

		dol_syslog(get_class($this).'::update_clicktodial', LOG_DEBUG);
		$result = $this->db->query($sql);

		$sql = "INSERT INTO ".MAIN_DB_PREFIX."user_clicktodial";
		$sql .= " (fk_user,url,login,pass,poste)";
		$sql .= " VALUES (".$this->id;
		$sql .= ", '". $this->db->escape($this->clicktodial_url) ."'";
		$sql .= ", '". $this->db->escape($this->clicktodial_login) ."'";
		$sql .= ", '". $this->db->escape($this->clicktodial_password) ."'";
		$sql .= ", '". $this->db->escape($this->clicktodial_poste) ."')";

		dol_syslog(get_class($this).'::update_clicktodial', LOG_DEBUG);
		$result = $this->db->query($sql);
		if ($result)
		{
			$this->db->commit();
			return 1;
		}
		else
		{
			$this->db->rollback();
			$this->error=$this->db->lasterror();
			return -1;
		}
	}


	/**
	 *  Add user into a group
	 *
	 *  @param	int	$group      Id of group
	 *  @param  int		$entity     Entity
	 *  @param  int		$notrigger  Disable triggers
	 *  @return int  				<0 if KO, >0 if OK
	 */
	function SetInGroup($group, $entity, $notrigger=0)
	{
		global $conf, $langs, $user;

		$error=0;

		$this->db->begin();

		$sql = "DELETE FROM ".MAIN_DB_PREFIX."usergroup_user";
		$sql.= " WHERE fk_user  = ".$this->id;
		$sql.= " AND fk_usergroup = ".$group;
		$sql.= " AND entity = ".$entity;

		$result = $this->db->query($sql);

		$sql = "INSERT INTO ".MAIN_DB_PREFIX."usergroup_user (entity, fk_user, fk_usergroup)";
		$sql.= " VALUES (".$entity.",".$this->id.",".$group.")";

		$result = $this->db->query($sql);
		if ($result)
		{
			if (! $error && ! $notrigger)
			{
			    $this->newgroupid=$group;

			    // Call trigger
                $result=$this->call_trigger('USER_SETINGROUP',$user);
	            if ($result < 0) { $error++; }
                // End call triggers
			}

			if (! $error)
			{
				$this->db->commit();
				return 1;
			}
			else
			{
				dol_syslog(get_class($this)."::SetInGroup ".$this->error, LOG_ERR);
				$this->db->rollback();
				return -2;
			}
		}
		else
		{
			$this->error=$this->db->lasterror();
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 *  Remove a user from a group
	 *
	 *  @param	int   $group       Id of group
	 *  @param  int		$entity      Entity
	 *  @param  int		$notrigger   Disable triggers
	 *  @return int  			     <0 if KO, >0 if OK
	 */
	function RemoveFromGroup($group, $entity, $notrigger=0)
	{
		global $conf,$langs,$user;

		$error=0;

		$this->db->begin();

		$sql = "DELETE FROM ".MAIN_DB_PREFIX."usergroup_user";
		$sql.= " WHERE fk_user  = ".$this->id;
		$sql.= " AND fk_usergroup = ".$group;
		$sql.= " AND entity = ".$entity;

		$result = $this->db->query($sql);
		if ($result)
		{
			if (! $error && ! $notrigger)
			{
			    $this->oldgroupid=$group;

			    // Call trigger
                $result=$this->call_trigger('USER_REMOVEFROMGROUP',$user);
                if ($result < 0) { $error++; }
                // End call triggers
			}

			if (! $error)
			{
				$this->db->commit();
				return 1;
			}
			else
			{
				$this->error=$interface->error;
				dol_syslog(get_class($this)."::RemoveFromGroup ".$this->error, LOG_ERR);
				$this->db->rollback();
				return -2;
			}
		}
		else
		{
			$this->error=$this->db->lasterror();
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 *  Return a link to the user card (with optionaly the picto)
	 * 	Use this->id,this->lastname, this->firstname
	 *
	 *	@param	int		$withpicto		Include picto in link (0=No picto, 1=Inclut le picto dans le lien, 2=Picto seul)
	 *	@param	string	$option			On what the link point to
	 *	@return	string					String with URL
	 */
	function getNomUrl($withpicto=0,$option='')
	{
		global $langs;

		$result='';

//		$lien = '<a href="'.DOL_URL_ROOT.'/user/card.php?id='.$this->id.'">';
//		$lienfin='</a>';
		$lien='';
		$lienfin='';
		if ($withpicto)
		{
			$result.=($lien.img_object($langs->trans("ShowUser"),'user').$lienfin);
			if ($withpicto != 2) $result.=' ';
		}
		$result.=$lien.$this->getFullName($langs,'',0,24).$lienfin;
		return $result;
	}

	/**
	 *  Renvoie login clicable (avec eventuellement le picto)
	 *
	 *	@param	int		$withpicto		Inclut le picto dans le lien
	 *	@param	string	$option			Sur quoi pointe le lien
	 *	@return	string					Chaine avec URL
	 */
	function getLoginUrl($withpicto=0,$option='')
	{
		global $langs;

		$result='';

		$lien = '<a href="'.DOL_URL_ROOT.'/user/card.php?id='.$this->id.'">';
		$lienfin='</a>';

		if ($option == 'xxx')
		{
			$lien = '<a href="'.DOL_URL_ROOT.'/user/card.php?id='.$this->id.'">';
			$lienfin='</a>';
		}

		if ($withpicto) $result.=($lien.img_object($langs->trans("ShowUser"),'user').$lienfin.' ');
		$result.=$lien.$this->login.$lienfin;
		return $result;
	}

	/**
	 *  Retourne le libelle du statut d'un user (actif, inactif)
	 *
	 *  @param	int		$mode          0=libelle long, 1=libelle court, 2=Picto + Libelle court, 3=Picto, 4=Picto + Libelle long, 5=Libelle court + Picto
	 *  @return	string 			       Label of status
	 */
	function getLibStatut($mode=0)
	{
		return $this->LibStatut($this->statut,$mode);
	}

	/**
	 *  Renvoi le libelle d'un statut donne
	 *
	 *  @param	int		$statut        	Id statut
	 *  @param  int		$mode          	0=libelle long, 1=libelle court, 2=Picto + Libelle court, 3=Picto, 4=Picto + Libelle long, 5=Libelle court + Picto
	 *  @return string 			       	Label of status
	 */
	function LibStatut($statut,$mode=0)
	{
		global $langs;
		$langs->load('users');

		if ($mode == 0)
		{
			$prefix='';
			if ($statut == 1) return $langs->trans('Enabled');
			if ($statut == 0) return $langs->trans('Disabled');
		}
		if ($mode == 1)
		{
			if ($statut == 1) return $langs->trans('Enabled');
			if ($statut == 0) return $langs->trans('Disabled');
		}
		if ($mode == 2)
		{
			if ($statut == 1) return img_picto($langs->trans('Enabled'),'statut4').' '.$langs->trans('Enabled');
			if ($statut == 0) return img_picto($langs->trans('Disabled'),'statut5').' '.$langs->trans('Disabled');
		}
		if ($mode == 3)
		{
			if ($statut == 1) return img_picto($langs->trans('Enabled'),'statut4');
			if ($statut == 0) return img_picto($langs->trans('Disabled'),'statut5');
		}
		if ($mode == 4)
		{
			if ($statut == 1) return img_picto($langs->trans('Enabled'),'statut4').' '.$langs->trans('Enabled');
			if ($statut == 0) return img_picto($langs->trans('Disabled'),'statut5').' '.$langs->trans('Disabled');
		}
		if ($mode == 5)
		{
			if ($statut == 1) return $langs->trans('Enabled').' '.img_picto($langs->trans('Enabled'),'statut4');
			if ($statut == 0) return $langs->trans('Disabled').' '.img_picto($langs->trans('Disabled'),'statut5');
		}
	}


	/**
	 *	Retourne chaine DN complete dans l'annuaire LDAP pour l'objet
	 *
	 *	@param	array	$info		Info array loaded by _load_ldap_info
	 *	@param	int		$mode		0=Return full DN (uid=qqq,ou=xxx,dc=aaa,dc=bbb)
	 *								1=
	 *								2=Return key only (uid=qqq)
	 *	@return	string				DN
	 */
	function _load_ldap_dn($info,$mode=0)
	{
		global $conf;
		$dn='';
		if ($mode==0) $dn=$conf->global->LDAP_KEY_USERS."=".$info[$conf->global->LDAP_KEY_USERS].",".$conf->global->LDAP_USER_DN;
		if ($mode==1) $dn=$conf->global->LDAP_USER_DN;
		if ($mode==2) $dn=$conf->global->LDAP_KEY_USERS."=".$info[$conf->global->LDAP_KEY_USERS];
		return $dn;
	}

	/**
	 *	Initialize the info array (array of LDAP values) that will be used to call LDAP functions
	 *
	 *	@return		array		Tableau info des attributs
	 */
	function _load_ldap_info()
	{
		global $conf,$langs;

		$info=array();

		// Object classes
		$info["objectclass"]=explode(',',$conf->global->LDAP_USER_OBJECT_CLASS);

		$this->fullname=$this->getFullName($langs);

		// Champs
		if ($this->fullname && ! empty($conf->global->LDAP_FIELD_FULLNAME))   $info[$conf->global->LDAP_FIELD_FULLNAME] = $this->fullname;
		if ($this->lastname && ! empty($conf->global->LDAP_FIELD_NAME))       $info[$conf->global->LDAP_FIELD_NAME] = $this->lastname;
		if ($this->firstname && ! empty($conf->global->LDAP_FIELD_FIRSTNAME)) $info[$conf->global->LDAP_FIELD_FIRSTNAME] = $this->firstname;
		if ($this->login && ! empty($conf->global->LDAP_FIELD_LOGIN))         $info[$conf->global->LDAP_FIELD_LOGIN] = $this->login;
		if ($this->login && ! empty($conf->global->LDAP_FIELD_LOGIN_SAMBA))   $info[$conf->global->LDAP_FIELD_LOGIN_SAMBA] = $this->login;
		if ($this->pass && ! empty($conf->global->LDAP_FIELD_PASSWORD))       $info[$conf->global->LDAP_FIELD_PASSWORD] = $this->pass;	// this->pass = mot de passe non crypte
		if ($this->ldap_sid && ! empty($conf->global->LDAP_FIELD_SID))        $info[$conf->global->LDAP_FIELD_SID] = $this->ldap_sid;
		if ($this->societe_id > 0)
		{
			$soc = new Societe($this->db);
			$soc->fetch($this->societe_id);

			$info["o"] = $soc->lastname;
			if ($soc->client == 1)      $info["businessCategory"] = "Customers";
			if ($soc->client == 2)      $info["businessCategory"] = "Prospects";
			if ($soc->fournisseur == 1) $info["businessCategory"] = "Suppliers";
		}
		if ($this->address && ! empty($conf->global->LDAP_FIELD_ADDRESS))     $info[$conf->global->LDAP_FIELD_ADDRESS] = $this->address;
		if ($this->zip && ! empty($conf->global->LDAP_FIELD_ZIP))             $info[$conf->global->LDAP_FIELD_ZIP] = $this->zip;
		if ($this->town && ! empty($conf->global->LDAP_FIELD_TOWN))           $info[$conf->global->LDAP_FIELD_TOWN] = $this->town;
		if ($this->office_phone && ! empty($conf->global->LDAP_FIELD_PHONE))  $info[$conf->global->LDAP_FIELD_PHONE] = $this->office_phone;
		if ($this->user_mobile && ! empty($conf->global->LDAP_FIELD_MOBILE))  $info[$conf->global->LDAP_FIELD_MOBILE] = $this->user_mobile;
		if ($this->office_fax && ! empty($conf->global->LDAP_FIELD_FAX))	     $info[$conf->global->LDAP_FIELD_FAX] = $this->office_fax;
		if ($this->note && ! empty($conf->global->LDAP_FIELD_DESCRIPTION))    $info[$conf->global->LDAP_FIELD_DESCRIPTION] = $this->note;
		if ($this->email && ! empty($conf->global->LDAP_FIELD_MAIL))          $info[$conf->global->LDAP_FIELD_MAIL] = $this->email;
    	if ($this->skype && ! empty($conf->global->LDAP_FIELD_SKYPE))          $info[$conf->global->LDAP_FIELD_SKYPE] = $this->skype;

		if ($conf->global->LDAP_SERVER_TYPE == 'egroupware')
		{
			$info["objectclass"][4] = "phpgwContact"; // compatibilite egroupware

			$info['uidnumber'] = $this->id;

			$info['phpgwTz']      = 0;
			$info['phpgwMailType'] = 'INTERNET';
			$info['phpgwMailHomeType'] = 'INTERNET';

			$info["phpgwContactTypeId"] = 'n';
			$info["phpgwContactCatId"] = 0;
			$info["phpgwContactAccess"] = "public";

			if (dol_strlen($this->egroupware_id) == 0)
			{
				$this->egroupware_id = 1;
			}

			$info["phpgwContactOwner"] = $this->egroupware_id;

			if ($this->email) $info["rfc822Mailbox"] = $this->email;
			if ($this->phone_mobile) $info["phpgwCellTelephoneNumber"] = $this->phone_mobile;
		}

		return $info;
	}


	/**
	 *  Initialise an instance with random values.
	 *  Used to build previews or test instances.
	 *	id must be 0 if object instance is a specimen.
	 *
	 *  @return	void
	 */
	function initAsSpecimen()
	{
		global $user,$langs;

		$now=dol_now();

		// Initialise parametres
		$this->id=0;
		$this->ref = 'SPECIMEN';
		$this->specimen=1;

		$this->lastname='DOLIBARR';
		$this->firstname='SPECIMEN';
		$this->note='This is a note';
		$this->email='email@specimen.com';
    	$this->skype='tom.hanson';
		$this->office_phone='0999999999';
		$this->office_fax='0999999998';
		$this->user_mobile='0999999997';
		$this->admin=0;
		$this->login='dolibspec';
		$this->pass='dolibspec';
		//$this->pass_indatabase='dolibspec';									Set after a fetch
		//$this->pass_indatabase_crypted='e80ca5a88c892b0aaaf7e154853bccab';	Set after a fetch
		$this->datec=$now;
		$this->datem=$now;

		$this->datelastlogin=$now;
		$this->datepreviouslogin=$now;
		$this->statut=1;

		//$this->societe_id = 1;	For external users
		//$this->contact_id = 1;	For external users
		$this->entity = 1;
	}

	/**
	 *  Load info of user object
	 *
	 *  @param  int		$id     Id of user to load
	 *  @return	void
	 */
	function info($id)
	{
		$sql = "SELECT u.rowid, u.login as ref, u.datec,";
		$sql.= " u.tms as date_modification, u.entity, u.lastname, u.firstname";
		$sql.= " FROM ".MAIN_DB_PREFIX."user as u";
		$sql.= " WHERE u.rowid = ".$id;

		$result=$this->db->query($sql);
		if ($result)
		{
			if ($this->db->num_rows($result))
			{
				$obj = $this->db->fetch_object($result);

				$this->id = $obj->rowid;

				$this->ref			     = (! $obj->ref) ? $obj->rowid : $obj->ref;
				$this->date_creation     = $this->db->jdate($obj->datec);
				$this->date_modification = $this->db->jdate($obj->date_modification);
				$this->entity            = $obj->entity;
                $this->lastname          = $obj->lastname;
                $this->firstname         = $obj->firstname;
			}

			$this->db->free($result);

		}
		else
		{
			dol_print_error($this->db);
		}
	}


	/**
	 *    Return number of mass Emailing received by this contacts with its email
	 *
	 *    @return       int     Number of EMailings
	 */
	function getNbOfEMailings()
	{
		$sql = "SELECT count(mc.email) as nb";
		$sql.= " FROM ".MAIN_DB_PREFIX."mailing_cibles as mc";
		$sql.= " WHERE mc.email = '".$this->db->escape($this->email)."'";
		$sql.= " AND mc.statut=1";      // -1 erreur, 0 non envoye, 1 envoye avec succes
		$resql=$this->db->query($sql);
		if ($resql)
		{
			$obj = $this->db->fetch_object($resql);
			$nb=$obj->nb;

			$this->db->free($resql);
			return $nb;
		}
		else
		{
			$this->error=$this->db->error();
			return -1;
		}
	}

	/**
	 *  Return number of existing users
	 *
	 *  @param	string	$limitTo	Limit to 'active' or 'superadmin' users
	 *  @param	bool	$all		Return for all entities
	 *  @return int  				Number of users
	 */
	function getNbOfUsers($limitTo='active', $all=false)
	{
		global $conf;

		$sql = "SELECT count(rowid) as nb";
		$sql.= " FROM ".MAIN_DB_PREFIX."user";
		if ($limitTo == 'superadmin')
		{
			$sql.= " WHERE entity = 0";
		}
		else
		{
			if ($all) $sql.= " WHERE entity > 0"; // all users except superadmins
			else $sql.= " WHERE entity = ".$conf->entity;
			if ($limitTo == 'active') $sql.= " AND statut = 1";
		}

		$resql=$this->db->query($sql);
		if ($resql)
		{
			$obj = $this->db->fetch_object($resql);
			$nb=$obj->nb;

			$this->db->free($resql);
			return $nb;
		}
		else
		{
			$this->error=$this->db->error();
			return -1;
		}
	}

	/**
	 *  Update user using data from the LDAP
	 *
	 *  @param	ldapuser	$ldapuser	Ladp User
	 *
	 *  @return int  				<0 if KO, >0 if OK
	 */
	function update_ldap2dolibarr(&$ldapuser)
	{
		// TODO: Voir pourquoi le update met à jour avec toutes les valeurs vide (global $user écrase ?)
		global $user, $conf;

		$this->firstname=$ldapuser->{$conf->global->LDAP_FIELD_FIRSTNAME};
		$this->lastname=$ldapuser->{$conf->global->LDAP_FIELD_NAME};
		$this->login=$ldapuser->{$conf->global->LDAP_FIELD_LOGIN};
		$this->pass=$ldapuser->{$conf->global->LDAP_FIELD_PASSWORD};
		$this->pass_indatabase_crypted=$ldapuser->{$conf->global->LDAP_FIELD_PASSWORD_CRYPTED};

		$this->office_phone=$ldapuser->{$conf->global->LDAP_FIELD_PHONE};
		$this->user_mobile=$ldapuser->{$conf->global->LDAP_FIELD_MOBILE};
		$this->office_fax=$ldapuser->{$conf->global->LDAP_FIELD_FAX};
		$this->email=$ldapuser->{$conf->global->LDAP_FIELD_MAIL};
		$this->skype=$ldapuser->{$conf->global->LDAP_FIELD_SKYPE};
		$this->ldap_sid=$ldapuser->{$conf->global->LDAP_FIELD_SID};

		$this->job=$ldapuser->{$conf->global->LDAP_FIELD_TITLE};
		$this->note=$ldapuser->{$conf->global->LDAP_FIELD_DESCRIPTION};

		$result = $this->update($user);

		dol_syslog(get_class($this)."::update_ldap2dolibarr result=".$result, LOG_DEBUG);

		return $result;
	}


	/**
	 * Return and array with all instanciated children users of current user
	 *
	 * @return	void
	 */
	function get_children()
	{
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."user";
		$sql.= " WHERE fk_user = ".$this->id;

		dol_syslog(get_class($this)."::get_children result=".$result, LOG_DEBUG);
		$res  = $this->db->query($sql);
		if ($res)
		{
			$users = array ();
			while ($rec = $this->db->fetch_array($res))
			{
				$user = new User($this->db);
				$user->fetch($rec['rowid']);
				$users[] = $user;
			}
			return $users;
		}
		else
		{
			dol_print_error($this->db);
			return -1;
		}
	}


	/**
	 * 	Load this->parentof that is array(id_son=>id_parent, ...)
	 *
	 *	@return		int		<0 if KO, >0 if OK
	 */
	private function load_parentof()
	{
		global $conf;

		$this->parentof=array();

		// Load array[child]=parent
		$sql = "SELECT fk_user as id_parent, rowid as id_son";
		$sql.= " FROM ".MAIN_DB_PREFIX."user";
		$sql.= " WHERE fk_user <> 0";
		$sql.= " AND entity IN (".getEntity('user',1).")";

		dol_syslog(get_class($this)."::load_parentof", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql)
		{
			while ($obj= $this->db->fetch_object($resql))
			{
				$this->parentof[$obj->id_son]=$obj->id_parent;
			}
			return 1;
		}
		else
		{
			dol_print_error($this->db);
			return -1;
		}
	}

	/**
	 * 	Reconstruit l'arborescence hierarchique des users sous la forme d'un tableau
	 *	Set and return this->users that is an array sorted according to tree with arrays of:
	 *				id = id user
	 *				lastname
	 *				firstname
	 *				fullname = nom avec chemin complet du user
	 *				fullpath = chemin complet compose des id: "_grandparentid_parentid_id"
	 *
	 *  @param      int		$deleteafterid      Removed all users including the leaf $deleteafterid (and all its child) in user tree.
	 *	@return		array		      		  	Array of users $this->users. Note: $this->parentof is also set.
	 */
	function get_full_tree($deleteafterid=0)
	{
		global $conf,$user;

		$this->users = array();

		// Init this->parentof that is array(id_son=>id_parent, ...)
		$this->load_parentof();

		// Init $this->users array
		$sql = "SELECT DISTINCT u.rowid, u.firstname, u.lastname, u.fk_user, u.login, u.statut, u.entity";	// Distinct reduce pb with old tables with duplicates
		$sql.= " FROM ".MAIN_DB_PREFIX."user as u";
		if(! empty($conf->multicompany->enabled) && $conf->entity == 1 && (! empty($conf->multicompany->transverse_mode) || (! empty($user->admin) && empty($user->entity))))
		{
			$sql.= " WHERE u.entity IS NOT NULL";
		}
		else
		{
			$sql.= " WHERE u.entity IN (".getEntity('user',1).")";
		}
		dol_syslog(get_class($this)."::get_full_tree get user list", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$i=0;
			while ($obj = $this->db->fetch_object($resql))
			{
				$this->users[$obj->rowid]['rowid'] = $obj->rowid;
				$this->users[$obj->rowid]['id'] = $obj->rowid;
				$this->users[$obj->rowid]['fk_user'] = $obj->fk_user;
				$this->users[$obj->rowid]['firstname'] = $obj->firstname;
				$this->users[$obj->rowid]['lastname'] = $obj->lastname;
				$this->users[$obj->rowid]['login'] = $obj->login;
				$this->users[$obj->rowid]['statut'] = $obj->statut;
				$this->users[$obj->rowid]['entity'] = $obj->entity;
				$i++;
			}
		}
		else
		{
			dol_print_error($this->db);
			return -1;
		}

		// We add the fullpath property to each elements of first level (no parent exists)
		dol_syslog(get_class($this)."::get_full_tree call to build_path_from_id_user", LOG_DEBUG);
		foreach($this->users as $key => $val)
		{
			$this->build_path_from_id_user($key,0);	// Process a branch from the root user key (this user has no parent)
		}

		// Exclude leaf including $deleteafterid from tree
		if ($deleteafterid)
		{
			//print "Look to discard user ".$deleteafterid."\n";
			$keyfilter1='^'.$deleteafterid.'$';
			$keyfilter2='_'.$deleteafterid.'$';
			$keyfilter3='^'.$deleteafterid.'_';
			$keyfilter4='_'.$deleteafterid.'_';
			foreach($this->users as $key => $val)
			{
				if (preg_match('/'.$keyfilter1.'/',$val['fullpath']) || preg_match('/'.$keyfilter2.'/',$val['fullpath'])
					|| preg_match('/'.$keyfilter3.'/',$val['fullpath']) || preg_match('/'.$keyfilter4.'/',$val['fullpath']))
				{
					unset($this->users[$key]);
				}
			}
		}

		dol_syslog(get_class($this)."::get_full_tree dol_sort_array", LOG_DEBUG);
		$this->users=dol_sort_array($this->users, 'fullname', 'asc', true, false);

		//var_dump($this->users);

		return $this->users;
	}

	/**
	 * 	Return list of all childs users in herarchy.
	 *
	 *	@return		array		      		  	Array of user id lower than user. This overwrite this->users.
	 */
	function getAllChildIds()
	{
		// Init this->users
		$this->get_full_tree();

		$idtoscan=$this->id;
		$childids=array();

		dol_syslog("Build childid for id = ".$idtoscan);
		foreach($this->users as $id => $val)
		{
			//var_dump($val['fullpath']);
			if (preg_match('/_'.$idtoscan.'_/', $val['fullpath'])) $childids[$val['id']]=$val['id'];
		}

		return $childids;
	}

	/**
	 *	For user id_user and its childs available in this->users, define property fullpath and fullname
	 *
	 * 	@param		int		$id_user		id_user entry to update
	 * 	@param		int		$protection		Deep counter to avoid infinite loop
	 *	@return		void
	 */
	function build_path_from_id_user($id_user,$protection=1000)
	{
		dol_syslog(get_class($this)."::build_path_from_id_user id_user=".$id_user." protection=".$protection, LOG_DEBUG);

		if (! empty($this->users[$id_user]['fullpath']))
		{
			// Already defined
			dol_syslog(get_class($this)."::build_path_from_id_user fullpath and fullname already defined", LOG_WARNING);
			return;
		}

		// Define fullpath and fullname
		$this->users[$id_user]['fullpath'] = '_'.$id_user;
		$this->users[$id_user]['fullname'] = $this->users[$id_user]['lastname'];
		$i=0; $cursor_user=$id_user;

		while ((empty($protection) || $i < $protection) && ! empty($this->parentof[$cursor_user]))
		{
			$this->users[$id_user]['fullpath'] = '_'.$this->parentof[$cursor_user].$this->users[$id_user]['fullpath'];
			$this->users[$id_user]['fullname'] = $this->users[$this->parentof[$cursor_user]]['lastname'].' >> '.$this->users[$id_user]['fullname'];
			$i++; $cursor_user=$this->parentof[$cursor_user];
		}

		// We count number of _ to have level
		$this->users[$id_user]['level']=dol_strlen(preg_replace('/[^_]/i','',$this->users[$id_user]['fullpath']));

		return;
	}

}

