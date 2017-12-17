<?php
session_start ();

if ($_SESSION["group"]!="user" and $_SESSION["group"]!="admin") {
	header ("Location: index.php");
	exit (0);	
}

require_once ("lib/php/dblink.php");
require_once ("lib/php/func.php");
require_once ("lib/php/security.php");
?>
<?php
	$id = check_numeric($_GET["id"]);
	if($id!=intval($id))
		header ("Location: status.php");

	$sql = "select * from submission where submission_id=$id";
	$r = mysql_query ($sql) or die ("Invalid query: $sql");
	$code = mysql_fetch_array ($r);

	//if in contest, can see teammate's code
	if($_GET["contest_id"] && isset($_SESSION["userid"])){
		$contest_id = check_numeric($_GET["contest_id"]);
		$sql = "select contest_id from problems where problem_id={$code["problem_id"]}";
		$s = mysql_query($sql) or die("Invalid query: $sql");
		$arr = mysql_fetch_array($s);

		if($arr["contest_id"] == $contest_id){
			$sql = "select start_time, end_time from contests where contest_id=$contest_id";
			$s = mysql_query($sql) or die("Invalid query: $sql");
			$contest = mysql_fetch_array($s);
			$end_time = strtotime($contest["end_time"]);
			$start_time = strtotime($contest["start_time"]);
			$sub_time = strtotime($code["submit_time"]);
		}
	}

	if (($_SESSION["userid"]!=$code["user_id"] && !isset($i_see_my_team)) && ($_SESSION["group"]!="admin") ) {
		header ("Location: status.php");
		exit (0);
	}
?>

<html>

<head>
<script src="lib/javascript/highlight.js"></script>
<script>hljs.initHighlightingOnLoad();</script>
<?php include_once ("html_header.php"); ?>

</head>

<body>
	<!-- mean -->
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
				<ul class="nav navbar-nav navbar-right">
					<li style="border-left: 1px solid gray;">
						<a onclick="closewin();">Close&nbsp;<span class="glyphicon glyphicon-log-out"></span></a>
					</li>
				</ul>
			</div>
			<!--/.nav-collapse -->
		</div>
	</nav>
	<!-- page -->
	<div class="container jumbotron" style="padding-top: 3%;">
		<p><b>Problem ID : <?=sprintf ("%04d", $code["problem_id"])?> &nbsp;|&nbsp; Submission ID : <?=$code["submission_id"]?></b></p>
		<p><b>User : <?=get_username($code["user_id"])?> -- <?=htmlspecialchars(get_team_name($code["user_id"]))?></b></p>
		<p><b>Result : <?=$code["result"]?></b></p>
		<?php if($_SESSION["userid"] == $code["user_id"] || $_SESSION["group"]=="admin"){ ?>
			<p><a href=download_code.php?id=<?=$id?>>Download</a></p>
		<?php }?>

		<?php
			echo "<pre class=\"viewcode\"><code class=\"c++\">".htmlspecialchars($code["code"])."</code></pre>";
		?>

	</div>
	<!-- footer -->
	<?php require ("footer.php") ?>
</body>
<script type="text/javascript">
	function closewin() {
		window.open('', '_self', ''); window.close();
	}
</script>
</html>
