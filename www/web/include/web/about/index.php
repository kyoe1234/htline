<?php
require './include/startup.php';

$g->var['layout_head_title'] = 'HoiTalk: 호이톡에 대하여';
$args = array(
	'content' => './module/index.php',
);
import('./layout/default.php', $args);
?>
