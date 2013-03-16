<?php
require './include/startup.php';
require_once DIR_LIB.'/HBoard.php';

$hid = md5(time());
$title = $_POST['title'];
$content = $_POST['content'];
$id = HBoard::add($hid, $title, $content, $warning);

if ( !$id ) {
	Alert::back($warning->text);
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
?>