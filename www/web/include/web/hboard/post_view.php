<?php
require './include/startup.php';
require_once DIR_LIB.'/HBoard.php';

// 타이틀
$g->var['layout_head_title'] = 'HTLine: 자유롭게 글을 남기세요.';

$post = HBoard::get_post($_GET['post_id']);
if ( !$post['id'] ) {
	Alert::back('존재하지 않는 글 입니다.');
}

// view 페이지 출력
$args = array(
	'content' => './module/post.php',
);
import('./layout/default.php', $args);
?>