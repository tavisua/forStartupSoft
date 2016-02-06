<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 05.02.2016
 * Time: 9:06
 */
//echo '<pre>';
//var_dump($_SERVER);
//echo '</pre>';
//die();
require $_SERVER['DOCUMENT_ROOT'] . '/dolibarr/htdocs/main.inc.php';

header("Location: http://".$_SERVER["SERVER_NAME"]."/dolibarr/htdocs/responsibility/".$user->respon_alias."/day_plan.php?idmenu=10419&mainmenu=plan_of_days&leftmenu=");