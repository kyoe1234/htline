<?php
require_once './include/startup.php';
require_once DIR_LIB.'/Join.php';

$P = atrim($_POST);

$result = Join::signup($P['email'], $P['loginpw'], $P['loginpw'], $P['nick'], $warning);
if ( !$result ) {
    $g->alert->back($warning->text);
}

// 로그인
$g->access->login($P['email'], $P['loginpw']);

// 메인페이지로 이동
header('Location: /');
?>