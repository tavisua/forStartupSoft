<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 15.12.2015
 * Time: 10:53
 */
require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/economic_indicator_class.php';

$socid = GETPOST('socid', 'int');
if(empty($socid))
    $socid = $_REQUEST['socid'];

$url = $_SERVER["HTTP_REFERER"];

//echo '<pre>';
//var_dump($soc_address);
//echo '</pre>';
$object = new  Societe($db);
$object->fetch($socid);
$CategoryOfCustomer = $object->getCategoryOfCustomer();
$FormOfGoverment = $object->getFormOfGoverment();
$EconomicIndicators = new EconomicIndicator($socid);

if(GETPOST('action', 'alpha') == 'add'){
    $Title = $langs->trans("AddParameters");
    llxHeader('',$Title,$help_url);
    print_fiche_titre($Title);
    include $_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/eldy/addparam.html';
    exit();
}


$Title = $langs->trans("EconomicIndicators");
llxHeader('',$Title,$help_url);
print_fiche_titre($Title);



include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/economic_indicator.html');
echo ob_get_clean();
llxFooter();