<?php
//
// File generated by Dolibarr installer 3.7.1 on 09 сен 2015
//
// Take a look at conf.php.example file for an example of conf.php file
// and explanations for all possibles parameters.
//
//echo'<pre>';
//var_dump($_SERVER["CONTEXT_DOCUMENT_ROOT"]);
//echo'</pre>';
//die();
$dolibarr_main_url_root='http://uspex2015.com/dolibarr/htdocs';
global $dolibarr_main_db_name, $dolibarr_main_db_user, $dolibarr_main_db_pass, $dolibarr_main_db_host;

$dolibarr_main_db_host='127.0.0.1';
$dolibarr_main_db_port='3306';

if(in_array($_SERVER['SERVER_NAME'], array('uspex2015.com','uspex2015.com1'))) {
    $dolibarr_main_document_root=$_SERVER["CONTEXT_DOCUMENT_ROOT"].'/dolibarr/htdocs';
    $dolibarr_main_db_name = 'vopimwkk_uspex2015';
    $dolibarr_main_db_user = 'admin';
    $dolibarr_main_db_pass = 'Gjdeqs4wqpYxGnKm';
//    $dolibarr_main_document_root='D:/OpenServer/domains/uspex2015.com/dolibarr/htdocs';
//    $dolibarr_main_data_root='c:/dolibarr/dolibarr_documents';
}else{
    $dolibarr_main_document_root=$_SERVER["DOCUMENT_ROOT"].'/dolibarr/htdocs';
    $dolibarr_main_db_name = 'vopimwkk_uspex2015';
    $dolibarr_main_db_user = 'vopimwkk_admin';
    $dolibarr_main_db_pass = 'C~~KiE3cDThX';
}
$dolibarr_main_db_prefix='llx_';
$dolibarr_main_db_type='mysqli';
$dolibarr_main_db_character_set='utf8';
$dolibarr_main_db_collation='utf8_general_ci';
$dolibarr_main_authentication='uspex2015';

// Specific settings
$dolibarr_main_prod='0';
$dolibarr_nocsrfcheck='0';
$dolibarr_main_force_https='0';
$dolibarr_main_cookie_cryptkey='92dd243b67331588a8a9b342b9a3d6cd';
$dolibarr_mailing_limit_sendbyweb='0';

//$dolibarr_lib_TCPDF_PATH='';
//$dolibarr_lib_FPDF_PATH='';
//$dolibarr_lib_FPDI_PATH='';
//$dolibarr_lib_ADODB_PATH='';
//$dolibarr_lib_GEOIP_PATH='';
//$dolibarr_lib_NUSOAP_PATH='';
//$dolibarr_lib_PHPEXCEL_PATH='';
//$dolibarr_lib_ODTPHP_PATH='';
//$dolibarr_lib_ODTPHP_PATHTOPCLZIP='';
//$dolibarr_js_CKEDITOR='';
//$dolibarr_js_JQUERY='';
//$dolibarr_js_JQUERY_UI='';
//$dolibarr_js_JQUERY_FLOT='';

//$dolibarr_font_DOL_DEFAULT_TTF='';
//$dolibarr_font_DOL_DEFAULT_TTF_BOLD='';
?>