<nav class="navbar navbar-default navbar-fixed-top">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="index.php">Academic Judge</a>
		</div>
		<div id="navbar" class="navbar-collapse collapse">
			<?php if ($_SESSION["userid"]) { ?>
			<ul class="nav navbar-nav">
				<?php if ($_SESSION["group"]=="admin") { ?>
					<li><a href=admin/index.php>Admin</a></li>
				<?php } ?>
			</ul>
			<ul class="nav navbar-nav navbar-right">
					<li><a href=user_info.php?user_id=<?=$_SESSION["userid"];?>><span class="glyphicon glyphicon-user"></span></a></li>
					<li style="border-left: 1px solid gray;"><a href=logout.php>Logout&nbsp;<span class="glyphicon glyphicon-log-out"></span></a></li>
			</ul>
			<?php } ?>
		</div>
		<!--/.nav-collapse -->
	</div>
</nav>
