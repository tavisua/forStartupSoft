<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 16.11.2015
 * Time: 9:55
 */

require '../main.inc.php';
//echo '<pre>';
//var_dump($_POST);
//echo '</pre>';

require $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/societe/societecontact_class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
$soc_contact = new societecontact();
$form = new Form($db);

$formcompany = new FormCompany($db);
$action = GETPOST('action', 'alpha');
$url         = $_SERVER["HTTP_REFERER"];
if($action == 'save')
    $url = GETPOST('url', 'alpha');
$action_url = str_replace('edit', 'save',$_SERVER['REQUEST_URI']);

if($action =='cancel') {
    header('Location: ' . $_REQUEST['url']);
    exit;
}elseif($_REQUEST['action'] == 'edit'){
    $soc_contact->fetch($_REQUEST['rowid']);
    $action      = 'save';
    $socid       = $soc_contact->socid;
    $object = new  Societe($db);
    $object->fetch($socid);
    $CategoryOfCustomer = $object->getCategoryOfCustomer();
    $FormOfGoverment = $object->getFormOfGoverment();
    $countrycode = $object->getCountryCode();
    if(!empty($countrycode))
        $countrycode = '+'.$countrycode;
    $EditAddress = $langs->trans('EditContact');
    llxHeader('', $EditAddress, $help_url);
    print_fiche_titre($EditAddress);

    include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/addcontact.html');
    echo ob_get_clean();
    llxFooter();
    exit;
}elseif(GETPOST('action', 'alpha') == 'save'){
//    echo '<pre>';
//    var_dump($_POST);
//    echo '</pre>';
//    die();
    $url                        = htmlspecialchars(GETPOST('url', 'alpha'));
    $soc_contact->rowid                     = GETPOST('rowid');
    $soc_contact->subdivision               = GETPOST('subdivision');
    $soc_contact->post                      = GETPOST('post');
    $soc_contact->SphereOfResponsibility    = GETPOST('SphereOfResponsibility');
    $soc_contact->town_id                   = GETPOST('town_id');
    $soc_contact->lastname                  = GETPOST('lastname');
    $soc_contact->firstname                 = GETPOST('firstname');
    $soc_contact->work_phone                = GETPOST('work_phone');
    $soc_contact->call_work_phone           = GETPOST('call_work_phone');
    $soc_contact->fax                       = GETPOST('fax');
    $soc_contact->call_fax                  = GETPOST('call_fax');
    $soc_contact->mobile_phone1             = GETPOST('mobile_phone1');
    $soc_contact->call_mobile_phone1        = GETPOST('call_mobile_phone1');
    $soc_contact->mobile_phone2             = GETPOST('mobile_phone2');
    $soc_contact->call_mobile_phone2        = GETPOST('call_mobile_phone2');
    $soc_contact->email1                    = GETPOST('email1');
    $soc_contact->send_email1               = GETPOST('send_email1');
    $soc_contact->email2                    = GETPOST('email2');
    $soc_contact->send_email2               = GETPOST('send_email2');
    $soc_contact->skype                     = GETPOST('skype');
    $soc_contact->call_skype                = GETPOST('call_skype');
    $soc_contact->birthdaydate              = GETPOST('BirthdayDate');
    $soc_contact->send_birthdaydate         = GETPOST('send_birthdaydate');
    $soc_contact->socid                     = GETPOST('socid');
//    echo '<pre>';
//    var_dump($soc_contact);
//    echo '</pre>';
//    die();
    $error = 0;
//    if(empty($soc_contact->post))$error++;
//    if(empty($soc_contact->SphereOfResponsibility))$error++;
//    if(empty($soc_contact->lastname))$error++;
//    if(empty($soc_contact->firstname))$error++;
//    if(empty($soc_contact->work_phone))$error++;
//    if(empty($soc_contact->mobile_phone1))$error++;
//    if(empty($soc_contact->email1))$error++;

    if($error > 0) {
        $action = 'error';
    }else {
        if(empty($soc_contact->rowid)){
//            var_dump($soc_contact);
//            die();
            $soc_contact->createContact(GETPOST('socid'));
        }else{
//            die('update 92');
            $soc_contact->updateContact();
        }
//        die(GETPOST('url', 'alpha'));
        header('Location: ' . GETPOST('url', 'alpha'));
        exit();
    }
}

$socid = GETPOST('socid', 'int');
if(empty($socid))
    $socid = $_REQUEST['socid'];
//$url = $_SERVER["HTTP_REFERER"];
$object = new  Societe($db);
$object->fetch($socid);

$CategoryOfCustomer = $object->getCategoryOfCustomer();
$FormOfGoverment = $object->getFormOfGoverment();
$countrycode = $object->getCountryCode();
if(!empty($countrycode))
    $countrycode = '+'.$countrycode;
if(GETPOST('action', 'alpha') == 'add' ||$action == 'error') {
    $AddContact = $langs->trans('AddContact');
    llxHeader('', $AddContact, $help_url);
    print_fiche_titre($AddContact);

    include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/addcontact.html');
    echo ob_get_clean();
    llxFooter();
}


