<?php
require_once './include/startup.php';
require_once DIR_LIB.'/HBoard.php';
require_once DIR_LIB.'/HComment.php';


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