<?php
/**
 * Created by PhpStorm.
 * User: -tavis-
 * Date: 03.01.2016
 * Time: 9:57
 */
include($_SERVER['DOCUMENT_ROOT'].'/dolibarr/htdocs/theme/'.$conf->theme.'/responsibility/'.(in_array('purchase', array($user->respon_alias, $user->respon_alias2))?'purchase':'sale').'/current/task.html');
return;