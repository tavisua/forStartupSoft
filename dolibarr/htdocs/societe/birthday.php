<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 22.02.2017
 * Time: 11:52
 */
require '../main.inc.php';
global $db;
//Визначаю яка буде дата через 10 днів
$sql = "select date_format(date_add(now(), interval 10 day), '%d.%m') dtDate";
$res = $db->query($sql);
if(!$res)
    dol_print_error($db);
$obj = $db->fetch_object($res);
$dtDate = $obj->dtDate;

//Визначаю контакти, яких потрібно буде поздоровити через 10 днів
$sql = "select `llx_societe_contact`.rowid, `llx_societe_contact`.socid, llx_societe.region_id from `llx_societe_contact`
    inner join llx_societe on llx_societe.rowid = `llx_societe_contact`.socid
    where `llx_societe_contact`.active = 1
    and llx_societe.active = 1
    and date_format(birthdaydate,'%d.%m') = '".$dtDate."'";

$res = $db->query($sql);
if(!$res)
    dol_print_error($db);

//Визначаю користувачів, яким потрібно дати команду підготувати листи поздоровлення, та нагадати подзвонити в день народження
