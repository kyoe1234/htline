<?php
require_once './include/startup.php';

$g->access->logout();

header('Location: /');
?>