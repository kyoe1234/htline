<?php
require_once DIR_LIB.'/common/aes.php';
require_once DIR_LIB.'/common/functions.php';
require_once DIR_LIB.'/common/Action.php';
require_once DIR_LIB.'/common/Alert.php';
require_once DIR_LIB.'/common/URI.php';
require_once DIR_LIB.'/common/Warning.php';
require_once DIR_LIB.'/GlobalObject.php';
//require_once DIR_LIB.'/Account.php';
//require_once DIR_LIB.'/User.php';


## 전역 객체 ##
global $g;
$g = GlobalObject::singleton();

$g->var['init_title'] = 'HTLine';
?>