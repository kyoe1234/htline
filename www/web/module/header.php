<?
// 오늘 방문자수
$today = date('Y-m-d');
$sql = "SELECT count(*) AS cnt FROM visitorlog
		WHERE createdate >= '{$today} 00:00:00'
				AND createdate <= '{$today} 23:59:59'";
$today_cnt = $g->db->fetch_val($sql);

// 메뉴 활성화
$phpself = dirname($_SERVER['PHP_SELF']);
if( strstr($phpself, '/about') ){
	$menu_slt['about'] = 'active';
} else {
	$menu_slt['home'] = 'active';
}
?>
<div class="navbar-wrapper">
	<!-- Wrap the .navbar in .container to center it within the absolutely positioned parent. -->
	<div class="container">
		<div class="navbar navbar-inverse">
			<div class="navbar-inner">
			<!-- Responsive Navbar Part 1: Button for triggering responsive navbar (not covered in tutorial). Include responsive CSS to utilize. -->
				<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</a>
				<a class="brand" href="/">HoiTalk</a>
				<!-- Responsive Navbar Part 2: Place all navbar contents you want collapsed withing .navbar-collapse.collapse. -->
				<div class="nav-collapse collapse">
					<span style="display: none"><?=$today_cnt?></span>
					<p class="navbar-text pull-right">
						<? if ( $g->au['id']): ?>
						<a href="#" onclick="return false;"class="navbar-link"><?=$g->au['nickname'] ? $g->au['nickname'] : $g->au['email']?></a>
						/ <a href="/sign/logout.php">logout</a>
						<? else: ?>
						<a href="/sign/index.php">Sign in/up</a>
						<? endif;?>
		            </p>
					<ul class="nav">
						<li class="<?=$menu_slt['home']?>"><a href="/">Home</a></li>
						<li class="<?=$menu_slt['about']?>"><a href="<?=URL_WEB?>/web/about">About</a></li>
						<li><a href="#contact">Contact</a></li>
						<!-- Read about Bootstrap dropdowns at http://twitter.github.com/bootstrap/javascript.html#dropdowns -->
						<li class="dropdown">
						<!--
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <b class="caret"></b></a>
							<ul class="dropdown-menu">
								<li><a href="#">Action</a></li>
								<li><a href="#">Another action</a></li>
								<li><a href="#">Something else here</a></li>
								<li class="divider"></li>
								<li class="nav-header">Nav header</li>
								<li><a href="#">Separated link</a></li>
								<li><a href="#">One more separated link</a></li>
							</ul>
							-->
						</li>
					</ul>
				</div>
				<!--/.nav-collapse -->
			</div>
			<!-- /.navbar-inner -->
		</div>
		<!-- /.navbar -->
	</div>
	<!-- /.container -->
</div>
