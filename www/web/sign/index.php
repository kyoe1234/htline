<?php
require_once './include/startup.php';

$g->layout->title = '로그인';

$args = array(
    'content' => './module/index.php',
);
import('./layout/default.php', $args);
?>