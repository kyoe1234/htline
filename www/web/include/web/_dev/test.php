<?php
require_once './include/startup.php';
require_once DIR_LIB.'/HBoard.php';
require_once DIR_LIB.'/HComment.php';

//if ( preg_match('/.*[가-힣]+.*/', 'aaabbbc發cc') ) {
if(preg_match("/[\xA1-\xFE][\xA1-\xFE]/", 'aaabbbccc')) { 
    echo 'aa';
} else {
    echo 'bb';
}

exit;
$ignore_ip_list = array('218.107.132.66','202.122.130.8','122.154.97.126','61.55.141.11');
foreach ( $ignore_ip_list as $ip ) {
	$sql = "INSERT ignoreip SET
				ip = '{$ip}',
				type = 'Y',
				createdate = NOW()";
	//$g->db->query($sql);
	echo $ip."\n";
}


exit;
function autolink2($contents) {
	$pattern = "/(http|https|ftp|mms):\/\/[0-9a-z-]+(\.[_0-9a-z-]+)+(:[0-9]{2,4})?\/?";       // domain+port
	$pattern .= "([\.~_0-9a-z-]+\/?)*";                                                                                                                                                                                             // sub roots
	$pattern .= "(\S+\.[_0-9a-z]+)?";                                                                                                                                                                                                    // file & extension string
	$pattern .= "(\?[_0-9a-z#%&=\-\+]+)*/i";                                                                                                                                                                               // parameters
	$replacement = "<a href=\"\\0\" target=\"_blank\">\\0</a>";
	return preg_replace($pattern, $replacement, $contents, -1);
}

$content = '가나다http://daum.netㅋㅋㅋ 룰루랄라http://hoitalk.comㅇㄹㅇ';
echo autoLink2($content);


exit;

$sql = "SELECT COUNT(ownerid) AS cnt FROM hcomment WHERE ownerid = 4";
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
$sql = "SELECT * FROM hboard
		{$where}
		ORDER BY id DESC
		LIMIT {$limit_over}";
$post_list = $g->db->fetch_all($sql);
print_r($post_list);

exit;
$result = $g->db->fetch_all("SELECT * FROM hboard");
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
