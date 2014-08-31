<?php
//error_reporting(E_ALL);
session_start();
require './include/startup.php';

// 방문자 로그를 기록한다. (1분안에 재방문시엔 기록하지 않음)
$log_time = date('Y-m-d H:i:s', strtotime('-1 minutes', time()));
$sql = "SELECT ip FROM visitorlog
		WHERE ip = '{$_SERVER['REMOTE_ADDR']}'
			AND createdate > '{$log_time}'";
$ip = $g->db->fetch_val($sql);

if ( !$ip ) {
	$sql = "INSERT visitorlog SET
		ip = '{$_SERVER['REMOTE_ADDR']}',
		createdate = NOW()";
	$g->db->query($sql);
}

// 타이틀
$g->var['layout_head_title'] = 'HTLine: 자유롭게 글을 남기세요.';

// list 페이지 출력
$args = array(
	'post_form' => './module/post_form.php',
	'content' => './module/post.php',
);

import('./layout/default.php', $args);
?>