<!DOCTYPE html>
<html>
<head>
	<? require DIR_WEB.'/module/head.php'; ?>
	<style type="text/css">
		body {
			/*padding-top: 40px;*/
			padding-bottom: 40px;
	    	background-color: #f5f5f5;
	  	}
	
		.form-signin {
			max-width: 300px;
		    padding: 19px 29px 29px;
		    margin: 0 auto 20px;
		    background-color: #fff;
		    border: 1px solid #e5e5e5;
		    -webkit-border-radius: 5px;
		       -moz-border-radius: 5px;
		            border-radius: 5px;
		    -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
		       -moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
		            box-shadow: 0 1px 2px rgba(0,0,0,.05);
		}
		
		.form-signin .form-signin-heading,
		.form-signin .checkbox {
			margin-bottom: 10px;
		}
		
		.form-signin input[type="text"],
		.form-signin input[type="password"] {
		    font-size: 16px;
		    height: auto;
		    margin-bottom: 15px;
		    padding: 7px 9px;
		}
	  
		.form-signin .btn_submit {
			text-align: right;
		}
	</style>
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