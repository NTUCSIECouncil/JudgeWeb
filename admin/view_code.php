<?php
session_start ();
require_once ("../lib/php/dblink.php");
require_once ("../lib/php/func.php");

if ($_SESSION["group"]!="admin") {
	header ("Location: ../index.php");
	exit (0);
}

$id = $_GET["id"];

$sql = "select * from submission where submission_id=$id";
$r = mysql_query ($sql) or die ("Invalid query: $sql");
$code = mysql_fetch_array ($r);

?>

<html>

<head>
<script src="../lib/javascript/highlight.js"></script>
<script>hljs.initHighlightingOnLoad();</script>
<?php include_once ("admin_html_header.php"); ?>

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
		<p><b>Result : <?=$code["result"]?></b></p>
		<?php
			echo "<pre class=\"viewcode\"><code class=\"c++\">".htmlspecialchars($code["code"])."</code></pre>";
		?>

	</div>
	<!-- footer -->
	<?php require ("../footer.php") ?>
</body>
<script type="text/javascript">
	function closewin() {
		window.open('', '_self', ''); window.close();
	}
</script>

</html>
