<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 05.11.2015
 * Time: 10:00
 */

$TitleToday = $langs->trans('TitleToday');
$Today = date('d.m.Y');
$Worker = $langs->trans('worker');
$State = $langs->trans('Region');
$Area = $langs->trans('Area');
$AsOfTheDate = $langs->trans('AsOfTheOfDate');
//die($TitleToday);
//echo '<pre>';
//var_dump($user);
//echo '</pre>';
//die();

include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/responsibility/sale/header.html');