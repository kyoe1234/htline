<?
$post_list = $_a['post_list'];
if ( !$post_list ) return;

foreach ( $post_list as $post ):
	$sql = "SELECT COUNT(ownerid) AS cnt FROM htline.hcomment WHERE ownerid = '{$post['id']}'";
	$comment_cnt = $g->db->fetch_val($sql);
?>
<div class="bs-docs-grid" style="margin-bottom: 15px">
	<div class="row show-grid">
		<div class="span12" data-original-title="">
			<div class="row">
				<div class="span10">
					<span><?=mb_strimwidth($post['hid'], 0, 10)?></span>
				</div>
				<div class="span2" style="text-align: right">
					<span><?=time_elapsed($post['createdate'])?></span>
				</div>
			</div>
		</div>
	</div>
	<div class="row show-grid">
		<div class="span12" style="background: #e3e3e3; -webkit-border-radius: 5px;">
			<p style="padding: 10px 10px 3px 10px;"><?=nl2br(autolink($post['content']))?></p>
		</div>
	</div>
	<div class="row show-grid">
		<div class="span12" data-original-title="">
			<div class="row">
				<div class="span10">
					<span></span>
				</div>
				<div class="span2" style="text-align: right">
					<span id="comment_cnt_<?=$post['id']?>">[<?=$comment_cnt?>]</span>
					<button class="btn dropdown-toggle btn-mini" data-toggle="dropdown" onclick="comment_show('<?=$post['id']?>')">comment <span class="caret"></span></button>
				</div>
			</div>
		</div>
	</div>

	<div id="hcmt_box_<?=$post['id']?>" style="display: none">
	<? import(DIR_WEB.'/hboard/module/hcomment.php', array('owner' => 'hboard', 'ownerid' => $post['id'])); ?>
	</div>
</div>
<? endforeach; ?>