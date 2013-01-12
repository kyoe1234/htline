<?php
require_once './include/startup.php';

$tmp = ( $_a ) ? $_a : $_GET;

$offset_id = $tmp['offset_id'];

$limit = 2;

// 조건문 생성

$where = '';
if ( $offset_id ) {
	$where = "WHERE id <= {$offset_id}";
}

// 더보기 유무
$limit_over = $limit + 1;
$sql = "SELECT * FROM htline.hboard
		{$where}
		ORDER BY id DESC
		LIMIT {$limit_over}";
$post_list = $g->db->fetch_all($sql);

$more_view = count($post_list) > $limit ? true : false;
if ( $more_view ) {
	// 마지막 하나 제거
	$row = array_pop($post_list);
	$offset_id = $row['id'];
} else {
	$offset_id = 0;
}

// 리스트를 가져온다.
$args = array(
	'post_list' => $post_list,
);

$html = import_ob(DIR_WEB.'/hboard/module/post_list.php', $args);

## json ##
// 가져온 데이터를 json 인코딩

echo json_encode(array(
	'result' => true,
	'offset_id' => $offset_id,
	'html' => $html,
));
?>