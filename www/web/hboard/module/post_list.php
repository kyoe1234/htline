<?
$post_list = $_a['post_list'];
if ( !$post_list ) return;

foreach ( $post_list as $post ):
	$sql = "SELECT COUNT(ownerid) AS cnt FROM htline.hcomment WHERE ownerid = '{$post['id']}'";
	$comment_cnt = $g->db->fetch_val($sql);
?>
<div class="bs-docs-grid" style="margin-bottom: 15px">
	<!-- title, time -->
	<div class="row">
		<div class="span10">
			<? if ( $post['title'] ): ?>
			<span><a href="./post_view.php?post_id=<?=$post['id']?>"><?=htmlspecialchars($post['title'])?></a>&nbsp;&nbsp;-&nbsp;&nbsp;<?=mb_strimwidth($post['hid'], 0, 10)?></span>
			<? else: ?>
			<span><a href="./post_view.php?post_id=<?=$post['id']?>"><?=mb_strimwidth($post['hid'], 0, 10)?></a></span>
			<? endif; ?>
		</div>
		<div class="span2" style="text-align: right">
			<span><?=time_elapsed($post['createdate'])?></span>
		</div>
	</div>
	<!-- content -->
	<div class="row">
		<div class="span11" style="background: #e3e3e3; -webkit-border-radius: 5px; margin-left: 30px; padding-right: 70px">
			<p style="padding: 10px 10px 3px 10px;"><?=nl2br(autolink(htmlspecialchars($post['content'])))?></p>
		</div>
	</div>
	<!-- comment -->
	<div class="row">
		<div class="span10">
			<span></span>
		</div>
		<div class="span2" style="text-align: right">
			<span id="comment_cnt_<?=$post['id']?>">[<?=$comment_cnt?>]</span>
			<button class="btn dropdown-toggle btn-mini" data-toggle="dropdown" onclick="comment_show('<?=$post['id']?>')">comment <span class="caret"></span></button>
		</div>
	</div>

	<div id="hcmt_box_<?=$post['id']?>" style="display: none">
	<? import(DIR_WEB.'/hboard/module/hcomment.php', array('owner' => 'hboard', 'ownerid' => $post['id'])); ?>
	</div>
</div>
<? endforeach; ?>