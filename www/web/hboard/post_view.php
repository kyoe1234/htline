<?php
require './include/startup.php';
require_once DIR_LIB.'/HBoard.php';

$post = HBoard::get_post($_GET['post_id']);
if ( !$post['id'] ) {
	Alert::back('존재하지 않는 글 입니다.');
}

// 타이틀
$g->var['layout_head_title'] = "호이톡 :: {$post['title']}";

// view 페이지 출력
$args = array(
	'content' => './module/post.php',
);
import('./layout/default.php', $args);
?>
