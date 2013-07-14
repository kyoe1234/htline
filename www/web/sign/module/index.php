<div class="container">

	<form class="form-signin" action="./signin.php" method="post">
		<h2 class="form-signin-heading">HoiTalk signin</h2>
		<input type="text" name="email" class="input-block-level" placeholder="Email address">
		<input type="password" name="loginpw" class="input-block-level" placeholder="Password">
		<!--
		<label class="checkbox">
		  <input type="checkbox" value="remember-me"> Remember me
		</label>
		-->
		<div class="btn_submit">
			<button class="btn btn-large btn-primary" type="submit">login</button>
		</div>
	</form>
	
	<form class="form-signin" action="./signup.php" method="post">
		<h2 class="form-signin-heading">HoiTalk signup</h2>
		<input type="text" name="nick" class="input-block-level" placeholder="Nick Name">
		<input type="text" name="email" class="input-block-level" placeholder="Email address">
		<input type="password" name="loginpw" class="input-block-level" placeholder="Password">
		<!--
		<label class="checkbox">
		  <input type="checkbox" value="remember-me"> Remember me
		</label>
		-->
		<div class="btn_submit">
			<button class="btn btn-large btn-primary" type="submit">signup</button>
		</div>
	</form>

</div> <!-- /container -->