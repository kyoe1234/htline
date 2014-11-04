<?php
//require_once './include/startup.php';
session_start();
require_once('../../include/env.php');

$g->access->logout();

header('Location: /');
?>