<?php
// 글 목록을 가져온다.

$args = array(
	'offset_id' => 0,
);

$json = import_ob(DIR_WEB.'/hboard/post_more.php', $args);
$data = json_decode($json);
?>
<div id="post_list">
	<?=$data->html?>
</div>
<div id="btn_post_more">
	<input type="button" value="더 보기" class="btn btn-large btn-block btn-primary">
</div>

<script type="text/javascript">
$(function () {
	var offset_id = <?=$data->offset_id?>;
	$('#btn_post_more').click(function () {
		var data = {offset_id: offset_id};
		$.getJSON('/hboard/post_more.php', data, function (obj) {
			offset_id = obj.offset_id;
			$('#post_list').append(obj.html);
			//wbprofilelayer.exec();
			if ( !obj.offset_id  ) {
				$('#btn_post_more').hide();
			}
		});
		return false;
	});
});

function comment_add (post_id) {
	var frm = document.forms['frm_hcomment_'+post_id];

	var param = $(frm).serialize();
	var url = frm.action + '?json=1&owner=hboard&owner_id='+post_id;
	$.post(url, param, function (obj) {
		if ( !obj.result ) {
		} else {
			$('#hcmt_group_'+post_id).replaceWith(obj.html);
			//wbprofilelayer.exec();
		}
	}, 'json');
}

function comment_show (id) {
	if ( $('#hcmt_box_'+id).css('display') == 'none' ) {
		$('#hcmt_box_'+id).show();
	} else {
		$('#hcmt_box_'+id).hide();
	}
}
</script>