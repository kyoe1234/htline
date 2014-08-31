<?php
require './include/startup.php';
require_once DIR_LIB.'/HComment.php';

function response($result, $msg = '', $errcode = '') {
	global $g;

	if ( $_GET['json'] ) {
		if ( $result ) {
			$html = import_ob(DIR_WEB.'/hboard/module/hcomment.php', array('owner' => $_GET['owner'], 'ownerid' => $_GET['owner_id']));
		}

		$sql = "SELECT COUNT(ownerid) AS cnt FROM hcomment WHERE ownerid = '{$_GET['owner_id']}'";
		$comment_cnt = $g->db->fetch_val($sql);

		echo json_encode(array(
			'result' => $result,
			'msg' => $msg,
			'html' => $html,
			'comment_cnt' => $comment_cnt,
			'errcode' => $errcode,
		));
		exit;
	} else {
		if ( $result ) {
			// 자신을 호출한 페이지로 이동
			header("Location: {$_SERVER['HTTP_REFERER']}#comment-{$hcomment_id}");
			exit;
		} else {
			Alert::back($msg);
		}
	}
}

$owner = $_GET['owner'];
$owner_id = $_GET['owner_id'];
$hid = md5(time());
$content = $_POST['content'];

$hcomment_id = HComment::add($owner, $owner_id, $hid, $content, $warning);
$result = !empty($hcomment_id);

if ( !$result ) {
	response(false, $warning->text, $warning->code);
}

response(true);
?>