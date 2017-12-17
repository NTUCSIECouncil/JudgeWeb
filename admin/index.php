<?php
session_start ();

if ($_SESSION["group"] != "admin") {
	header ("Location: ../index.php");
	exit (0);
}

require_once ("../lib/php/dblink.php");
require_once ("../lib/php/func.php");

if ($_GET["filter"]) {
	$filter = $_GET["filter"];
	$cond = "where action like '%$filter%'";
}

$sql = "select log_id from log $cond";
$r = mysql_query ($sql) or die ("Invalid query: $sql");
$n = mysql_num_rows ($r);

if ($_GET["page"])
	$page = $_GET["page"];
else
	$page = 1;

$total = floor (($n-1)/20) + 1;

$start = ($page-1)*20;
$limit = "limit $start, 20";

$sql = "select * from log $cond order by time desc $limit";
$r = mysql_query ($sql) or die ("Invalid query: $sql");
$n = 0;

while ($log[$n] = mysql_fetch_array ($r)) {
	$n++;
}
?>
<html>

<head>

<?php include_once ("admin_html_header.php"); ?>

</head>

<body>
	<!-- mean -->
	<?php require ("menu.php") ?>
	<!-- page -->
	<div class="container jumbotron" style="padding-top: 3%;"> 
    	<h2 class="text-center">System Log</h2><hr>
		<div class="text-center">
			<span class=note><?=$page?> / <?=$total?></span><br>
			<?php
				if ($page!=1)
					echo "<a href='index.php?filter=$filter&page=".($page-1)."'>Newer</a> << ";
				else
					echo "Newer << ";
			?>
			<a href="index.php">ALL</a> | 
			<a href="index.php?filter=login">login</a> | 
			<a href="index.php?filter=start">start virtual contest</a> | 
			<a href="index.php?filter=rejudge">rejudge</a>
			<?php
				if ($page!=$total)
					echo " >> <a href='index.php?filter=$filter&page=".($page+1)."'>Older</a>";
				else
					echo " >> Older";
			?>		
		</div>
		<div style="padding-right: 0%; padding-left: 0%; padding-top: 1%;">
			<table class="table table-hover table-condensed">
			<?php for ($i=0 ;$i<$n ;$i++) { ?>
				<tr>
					<td align="right"><?=$log[$i]["time"]?></td>
					<td style="padding-left: 4%;"><b>
						<a href=../user_info.php?user_id=<?=$log[$i]["user_id"]?>><?=$log[$i]["username"]?></a></b>
					</td>
					<td><?=$log[$i]["action"]?></td>
					<td><?=$log[$i]["IP"]?></td>
				</tr>
			<?php	}	?>
			</table>
		</div>
	</div>
	<!-- footer -->
	<?php require ("../footer.php") ?>
</body>

</html>
