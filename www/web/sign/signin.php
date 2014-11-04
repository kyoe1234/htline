<?php
//require_once './include/startup.php';
session_start();
require_once('../../include/env.php');

$P = atrim($_POST);

// 로그인
$result = $g->access->login($P['email'], $P['loginpw']);
if ( !$result ) {
    $g->alert->back($warning->text);
}

// 메인페이지로 이동
header('Location: /');
?>