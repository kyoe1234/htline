<?php
//require './include/startup.php';
session_start();
require_once('../../include/env.php');

require_once DIR_LIB.'/HBoard.php';

$post = HBoard::get_post($_GET['post_id']);
if ( !$post['id'] ) {
	Alert::back('존재하지 않는 글 입니다.');
}

// 타이틀
if ( $post['title'] ) {
    $g->var['layout_head_title'] = "호이톡 :: {$post['title']}";
} else {
    $g->var['layout_head_title'] = "호이톡 :: 자유롭게 글을 남기세요.";
}

// view 페이지 출력
$args = array(
	'content' => './module/post.php',
);
import('./layout/default.php', $args);
?>
