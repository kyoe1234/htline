<?php
require_once './include/startup.php';
require_once DIR_LIB.'/HBoard.php';
require_once DIR_LIB.'/HComment.php';

$sql = "SELECT COUNT(ownerid) AS cnt FROM htline.hcomment WHERE ownerid = 4";
$comment_cnt = $g->db->fetch_val($sql);
echo $comment_cnt;

exit;
echo date('Y-m-d H:i:s', time()).'<br />';
echo strtotime(date('Y-m-d H:i:s', time())).'<br />';
echo strtotime('2012-12-29 17:32:02').'<br /><br />';

echo time_elapsed('2012-12-29 17:32:02');

exit;
$limit = 20;

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
print_r($post_list);

exit;
$result = $g->db->fetch_all("SELECT * FROM htline.hboard");
print_r($result);

exit;
echo md5(time());

exit;
$owner = 'hboard';
$owner_id = '1';
$hid = 'alalal';
$content = '댓글테스트 입니다.2';
$comment_id = HComment::add($owner, $owner_id, $hid, $content);
echo $comment_id.'<br />';

$result = HComment::totalcount($owner, $owner_id);
echo $result;
echo '<br />';

$result = $g->db->fetch_row('select * from hcomment');
print_r($result);

exit;
//$id = HBoard::add('kyoe', '테스트 글 입니다.');
//HBoard::modify(1, '테스트 글 입니다. 2');
//HBoard::set_blind(1, 'Y');
//echo 'id: '.$id.'<br />';

$result = $g->db->fetch_row('select * from hboard');
print_r($result);

?>