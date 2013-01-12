<?php
require './include/startup.php';
require_once DIR_LIB.'/HBoard.php';

$hid = md5(time());
$content = $_POST['content'];
$id = HBoard::add($hid, $content, $warning);

if ( !$id ) {
    Alert::back($warning->text);
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
?>