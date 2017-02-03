<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 16.11.2015
 * Time: 10:13
 */


class SocAddress {
    var $rowid;
    var $whom;//кому
    var $kindaddress;//вид адреси
    var $Zip;//Індекс
    var $country_id;//Страна
    var $state_id;//Область
    var $region_id;//Район
    var $location;
    var $kindlocality_id;//Тип населеного пункту
    var $kindofstreet_id;//Тип вулиці
    var $street_name;//Назва вулиці
    var $NumberOfHouse;//номер будинку
    var $kindoffice_id;//назва офісу
    var $NumberOfOffice;//Номер офісу
    var $GPS;
    var $e_mail;
    var $site;
    var $WorkerCount;
    var $SendPost;
    var $SendEmail;
    var $socid;


    public function __construct()
    {
//        require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
    }

    public function createAddress($socid){
        global $db, $user;
//        echo '<pre>';
//        var_dump($this);
//        echo '</pre>';
//        die();
        $sql="insert into llx_societe_address(fk_soc,zip,whom,kindaddress,country_id,state_id,region_id,kindlocality_id,location,
              kindofstreet_id,street_name,numberofhouse,kindoffice_id,numberofoffice,gps,email,site,workercount,sendpost,sendemail,
              active,id_usr,dtChange)
        values(
        ".$socid.",".$this->Zip.",".(!empty($this->whom)?"'".$db->escape(trim($this->whom))."'":"null").",
        ".(!empty($this->kindaddress)?$this->kindaddress:"null").",
        ".(!empty($this->country_id)?$this->country_id:"null").",
        ".(!empty($this->state_id)?$this->state_id:"null").",
        ".(!empty($this->region_id)?$this->region_id:"null").",
        ".(!empty($this->kindlocality_id)?$this->kindlocality_id:"null").",
        ".(!empty($this->location)?"'".$db->escape($this->location)."'":"null").",
        ".(!empty($this->kindofstreet_id)?$this->kindofstreet_id:"null").",
        ".(!empty($this->street_name)?"'".$db->escape($this->street_name)."'":"null").",
        ".(!empty($this->NumberOfHouse)?"'".$this->NumberOfHouse."'":"null").",
        ".(!empty($this->kindoffice_id)?$this->kindoffice_id:"null").",
        ".(!empty($this->NumberOfOffice)?"'".$this->NumberOfOffice."'":"null").",
        ".(!empty($this->GPS)?"'".$this->GPS."'":"null").",
        ".(!empty($this->e_mail)?"'".$this->e_mail."'":"null").",
        ".(!empty($this->site)?"'".$this->site."'":"null").",
        ".(!empty($this->WorkerCount)?$this->WorkerCount:"null").",
        ".(!empty($this->SendPost)?$this->SendPost:"0").",
        ".(!empty($this->SendEmail)?$this->SendEmail:"0").",1,".$user->id.", Now())";
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
    }
    public function updateAddress(){
        global $db, $user;
        $sql = 'update llx_societe_address set ';
        $sql .= 'zip = '.(!empty($this->Zip)?"'".trim($this->Zip)."'":"null");
        $sql .= ', whom = '.(!empty($this->whom)?"'".$db->escape(trim($this->whom))."'":"null");
        $sql .= ', kindaddress = '.(!empty($this->kindaddress)?$this->kindaddress:"null");
        $sql .= ', country_id = '.(!empty($this->country_id)?$this->country_id:"null");
        $sql .= ', state_id = '.(!empty($this->state_id)?$this->state_id:"null");
        $sql .= ', region_id = '.(!empty($this->region_id)?$this->region_id:"null");
        $sql .= ', kindlocality_id = '.(!empty($this->kindlocality_id)?$this->kindlocality_id:"null");
        $sql .= ', location = '.(!empty($this->location)?"'".$db->escape($this->location)."'":"null");
        $sql .= ', kindofstreet_id = '.(!empty($this->kindofstreet_id)?$this->kindofstreet_id:"null");
        $sql .= ', street_name = '.(!empty($this->street_name)?"'".$this->street_name."'":"null");
        $sql .= ', numberofhouse = '.(!empty($this->NumberOfHouse)?"'".$this->NumberOfHouse."'":"null");
        $sql .= ', kindoffice_id = '.(!empty($this->kindoffice_id)?$this->kindoffice_id:"null");
        $sql .= ', numberofoffice = '.(!empty($this->NumberOfOffice)?"'".$this->NumberOfOffice."'":"null");
        $sql .= ', gps = '.(!empty($this->GPS)?"'".$this->GPS."'":"null");
        $sql .= ', email = '.(!empty($this->e_mail)?"'".$this->e_mail."'":"null");
        $sql .= ', site = '.(!empty($this->site)?"'".$this->site."'":"null");
        $sql .= ', workercount = '.(!empty($this->WorkerCount)?$this->WorkerCount:"null");
        $sql .= ', sendpost = '.(!empty($this->SendPost)?$this->SendPost:"0");
        $sql .= ', sendemail = '.(!empty($this->SendEmail)?$this->SendEmail:"0");
        $sql .= ', id_usr = '.$user->id;
        $sql .= ', dtChange = Now() where rowid='.$this->rowid;
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
    }
    public function fetch($rowid){
        global $db, $user;
        $sql = 'select zip, fk_soc, whom,kindaddress,country_id,state_id,region_id,kindlocality_id,location,kindofstreet_id,
        street_name,numberofhouse,kindoffice_id,numberofoffice,gps,email,site,workercount,sendpost,sendemail
        from `llx_societe_address` where rowid = '.$rowid;
        $res = $db->query($sql);
        $obj = $db->fetch_object($res);
        $this->rowid          = $rowid;
        $this->socid          = $obj->fk_soc;
        $this->Zip            = $obj->zip;
        $this->whom           = $obj->whom;
        $this->kindaddress    = $obj->kindaddress;
        $this->country_id     = $obj->country_id;
        $this->state_id       = $obj->state_id;
        $this->region_id      = $obj->region_id;
        $this->kindlocality_id= $obj->kindlocality_id;
        $this->location       = $obj->location;
        $this->kindofstreet_id= $obj->kindofstreet_id;
        $this->street_name    = $obj->street_name;
        $this->NumberOfHouse  = $obj->numberofhouse;
        $this->kindoffice_id  = $obj->kindoffice_id;
        $this->NumberOfOffice = $obj->numberofoffice;
        $this->GPS            = $obj->gps;
        $this->e_mail         = $obj->email;
        $this->site           = $obj->site;
        $this->WorkerCount    = $obj->workercount;
        $this->SendPost       = $obj->sendpost;
        $this->SendEmail      = $obj->sendemail;
    }
    public function selectKindAddress($htmlname, $kind=''){
        global $db;
        $out = '';
//        if(empty($kind)){
//            $out .= '<select class="combobox" name="'.$htmlname.'">';
//            $out .= '<option value="0">&nbsp;</option>';
//            $out .= '</select>';
//
//        }else {
            global $conf, $langs;
            $langs->load("dict");

            $sql = "select rowid, name from typeofaddress where 1";
            $sql .= " and active = 1 order by trim(name)";

            $resql = $db->query($sql);
            if ($resql) {
                $out .= '<select id = "'.$htmlname.'" class="combobox" name="' . $htmlname . '">';
                $num = $db->num_rows($resql);
                $i = 0;
                if ($num) {
                    $country = '';
                    $out .= '<option value="0">&nbsp;</option>';
                    while ($i < $num) {
                        $obj = $db->fetch_object($resql);
                        if (!empty($kind) && $kind == $obj->rowid) {
                            $out .= '<option value="' . $obj->rowid . '" selected="selected">' .$obj->name . '</option>';
                        } else {
                            $out .= '<option value="' . $obj->rowid . '">' . $obj->name . '</option>';
                        }
                        $i++;
                    }
                }
                $out .= '</select>';
            } else {
                dol_print_error($db);
            }
//        }
        return $out;
    }
    public function selectKindStreet($htmlname, $kind=''){
        global $db;
        $out = '';
//        if(empty($kind)){
//            $out .= '<select class="combobox" name="'.$htmlname.'">';
//            $out .= '<option value="0">&nbsp;</option>';
//            $out .= '</select>';
//
//        }else {
            global $conf, $langs;
            $langs->load("dict");

            $sql = "select rowid, name from kindstreet where 1";
            $sql .= " and active = 1 order by trim(name)";

            $resql = $db->query($sql);
            if ($resql) {
                $out .= '<select id = "'.$htmlname.'" class="combobox" name="' . $htmlname . '">';
                $num = $db->num_rows($resql);
                $i = 0;
                if ($num) {
                    $country = '';
                    $out .= '<option value="0">&nbsp;</option>';
                    while ($i < $num) {
                        $obj = $db->fetch_object($resql);
                        if (!empty($kind) && $kind == $obj->rowid) {
                            $out .= '<option value="' . $obj->rowid . '" selected="selected">' .$obj->name . '</option>';
                        } else {
                            $out .= '<option value="' . $obj->rowid . '">' . $obj->name . '</option>';
                        }
                        $i++;
                    }
                }
                $out .= '</select>';
                $out .= '<img class="hideonsmartphone" border="0" title="Для зміни списку необхідно зайти Налаштування->Вид вулиці" alt="" src="/dolibarr/htdocs/theme/eldy/img/info.png">';
            } else {
                dol_print_error($db);
            }
//        }
        return $out;
    }
    public function selectKindLocality($htmlname, $kind=''){
        global $db;
        $out = '';
//        if(empty($kind)){
//            $out .= '<select class="combobox" name="'.$htmlname.'">';
//            $out .= '<option value="0">&nbsp;</option>';
//            $out .= '</select>';
//
//        }else {
            global $conf, $langs;
            $langs->load("dict");

            $sql = "select rowid, name from kindlocality where 1";
            $sql .= " and active = 1 order by trim(name)";

            $resql = $db->query($sql);
            if ($resql) {
                $out .= '<select id = "'.$htmlname.'" class="combobox" name="' . $htmlname . '">';
                $num = $db->num_rows($resql);
                $i = 0;
                if ($num) {
                    $country = '';
                    $out .= '<option value="0">&nbsp;</option>';
                    while ($i < $num) {
                        $obj = $db->fetch_object($resql);
                        if (!empty($kind) && $kind == $obj->rowid) {
                            $out .= '<option value="' . $obj->rowid . '" selected="selected">' .$obj->name . '</option>';
                        } else {
                            $out .= '<option value="' . $obj->rowid . '">' . $obj->name . '</option>';
                        }
                        $i++;
                    }
                }
                $out .= '</select>';
                $out .= '<img class="hideonsmartphone" border="0" title="Для зміни списку необхідно зайти Налаштування->Вид населеного пункту" alt="" src="/dolibarr/htdocs/theme/eldy/img/info.png">';
            } else {
                dol_print_error($db);
            }
//        }
        return $out;
    }
    public function selectKindOffice($htmlname, $kind=''){
        global $db;
        $out = '';
//        if(empty($kind)){
//            $out .= '<select class="combobox" name="'.$htmlname.'">';
//            $out .= '<option value="0">&nbsp;</option>';
//            $out .= '</select>';
//
//        }else {
            global $conf, $langs;
            $langs->load("dict");

            $sql = "select rowid, name from kindoffice where 1";
            $sql .= " and active = 1 order by trim(name)";

            $resql = $db->query($sql);
            if ($resql) {
                $out .= '<select id = "'.$htmlname.'" class="combobox" name="' . $htmlname . '">';
                $num = $db->num_rows($resql);
                $i = 0;
                if ($num) {
                    $country = '';
                    $out .= '<option value="0">&nbsp;</option>';
                    while ($i < $num) {
                        $obj = $db->fetch_object($resql);
                        if (!empty($kind) && $kind == $obj->rowid) {
                            $out .= '<option value="' . $obj->rowid . '" selected="selected">' .$obj->name . '</option>';
                        } else {
                            $out .= '<option value="' . $obj->rowid . '">' . $obj->name . '</option>';
                        }
                        $i++;
                    }
                }
                $out .= '</select>';
                $out .= '<img class="hideonsmartphone" border="0" title="Для зміни списку необхідно зайти Налаштування->Вид офісу" alt="" src="/dolibarr/htdocs/theme/eldy/img/info.png">';
            } else {
                dol_print_error($db);
            }
//        }
        return $out;
    }

} 