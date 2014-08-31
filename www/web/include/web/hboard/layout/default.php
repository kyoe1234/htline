<!DOCTYPE html>
<html>
	<? require DIR_WEB.'/module/head.php'; ?>
</head>

<body>

<? require DIR_WEB.'/module/header.php'; ?>

<div class="container">
	<?
	// 글 등록 폼
	if ( $_a['post_form'] ) {
		import($_a['post_form']);
	}
	?>

	<div id="post_container">
	<?
	// 글 목록
	if ( $_a['content'] ) {
		import($_a['content']);
	}
	?>
	</div>
</div>
<hr />
<? require DIR_WEB.'/module/footer.php'; ?>

</body>
</html>