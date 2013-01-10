<?php
require './include/startup.php';
require_once DIR_LIB.'/HComment.php';

$owner = $_a['owner'];
$owner_id = $_a['ownerid'];
?>

<div id="hcmt_group_<?=$owner_id?>" class="row show-grid">
	<?
	$sql = "SELECT * FROM htline.hcomment
			WHERE owner = 'hboard'
				AND ownerid = {$owner_id}
			ORDER BY rootid ASC, id ASC";
	$row_list = $g->db->fetch_all($sql);

	$hcomment_list = array();
	foreach ( $row_list as $row ) {
		if ( $row['id'] == $row['rootid'] ) {
			// id와 rootid가 동일하면 부모글
			$hcomment_list[$row['id']] = $row;
		} else {
			// 동일하지 않다면 부모글의 자식글
			$hcomment_list[$row['rootid']]['child_list'][$row['id']] = $row;
		}
	}
	?>
	<? foreach ( $hcomment_list as $comment ): ?>
	<div class="span11 offset1" style="background: whiteSmoke; margin-bottom: 10px; border-style:solid;border-color: white; -webkit-border-radius: 10px;">
		<div class="row">
			<div class="span9">
				<span style="color: #808080; padding: 0px 10px 0px 10px"><?=mb_strimwidth($comment['hid'], 0, 10)?></span>
			</div>
			<div class="span2" style="text-align: right">
				<span style="color: #808080; padding: 0px 10px 0px 10px"><?=time_elapsed($comment['createdate'])?></span>
			</div>
		</div>
		<p style="padding: 10px 10px 3px 10px"><?=nl2br($comment['content'])?></p>
	</div>

	<? endforeach; ?>
	<div class="span11 offset1" data-original-title="">
		<form name="frm_hcomment_<?=$owner_id?>" method="post" action="/hboard/comment_add.php" class="row">
			<input type="hidden" name="owner_id" value="<?=$owner_id?>" />
			<div class="span11">
				댓글 작성
				<div class="row">
					<div class="span9">
						<textarea name="content" rows="4" cols="20" style="margin-left: 0px; margin-right: 0px; width: 700px;"></textarea>
					</div>
					<div class="span2">
						<input type="button" value="글 등록" onclick="comment_add('<?=$owner_id?>')" class="btn btn-large btn-block btn-primary">
					</div>
				</div>
			</div>
		</form>
	</div>
</div>