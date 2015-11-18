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
        $sql="insert into llx_societe_address(fk_soc, whom,kindaddress,country_id,state_id,region_id,kindlocality_id,location,
              kindofstreet_id,street_name,numberofhouse,numberofoffice,gps,email,site,workercount,sendpost,sendemail,
              active,id_usr,dtChange)
        values(
        ".$socid.",'".$this->whom."',".$this->kindaddress.",".$this->country_id.",".$this->state_id.",".$this->region_id.",
        ".$this->kindlocality_id.",'".$this->location."',".$this->kindofstreet_id.",'".$this->street_name."','".$this->NumberOfHouse."',
        '".$this->NumberOfOffice."','".$this->GPS."','".$this->e_mail."','".$this->site."',".$this->WorkerCount.",
        ".$this->SendPost.",".$this->SendEmail.",1,".$user->id.", Now())";
        $res = $db->query($sql);
        if(!$res)
            dol_print_error($db);
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