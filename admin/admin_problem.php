<?php
session_start ();
if ($_SESSION["group"]!="admin"){
	header ("Location: ../index.php");
	exit(0);
}

require_once ("../lib/php/dblink.php");
require_once ("../lib/php/func.php");

if ($_GET["del"]) {
	$id = $_GET["del"];
	$sql = "delete from problems where problem_id=$id";
	mysql_query ($sql) or die ("Invalid query: $sql");
	$sql = "delete from submission where problem_id=$id";
	mysql_query ($sql) or die ("Invalid query: $sql");
}

if ($_GET["rejudge"]) {
	$id = $_GET["rejudge"];
	$sql = "update submission set result='waiting' where problem_id=$id";
	mysql_query ($sql) or die ("Invalid query: $sql");
	log_action ("rejudge ".sprintf ("%04d", $id));
	header ("Location: admin_problem.php");
}

if ($_POST["submit"]=="save") {
	foreach ($_POST["effective"] as $id=>$effective) {
		$sql = "update problems set effective='$effective' where problem_id=$id";
		mysql_query ($sql) or die ("Invalid query: $sql");
	}
}

// tmt: find total number of pages
$sql = "select COUNT(*) from problems";
$r = mysql_query($sql) or die("Invalid query: $sql");
$get_pages = mysql_fetch_row($r);
$total_pages = $get_pages[0];

// bee: change the order to desc
$sql = "select * from problems order by problem_id desc";
// tmt: add pages
$PageSize = 50;
if ($_GET["pagesize"]){
	if(intval($_GET["pagesize"])!=0)
		$PageSize = intval($_GET["pagesize"]);
}
if ($_GET["page"]){
	$pagenum = $_GET["page"];
	if ($pagenum <= 0) $pagenum	= 1;
	$thispage = $pagenum;
	$pagenum = $_GET["page"] * $PageSize - $PageSize;
	$sql.= " LIMIT $pagenum, $PageSize";
}else if(!isset($_GET["all"])){
	$sql.= " LIMIT $PageSize";
	$thispage = 1;
}

$r = mysql_query ($sql) or die ("Invalid query: $sql");

$n = 0;

while ($probs[$n] = mysql_fetch_array ($r)) {
	$probs[$n]["AC"] = $probs[$n]["solved"];
	$probs[$n]["total"] = $probs[$n]["submission"];

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
	<h2 class="text-center">Problem Set</h2><hr>
	<center>
		<a href="admin_problem_add.php"><font size=2>新增題目</font></a>&nbsp;
		|| &nbsp; <a href="admin_problem.php?all">All</a> &nbsp; || &nbsp; Page:
		<?php
			$total_pages = ($total_pages + $PageSize-1)/$PageSize;
			for($i = 1; $i <= $total_pages; $i++) {
				if($i != $thispage){
					?><a href=admin_problem.php?page=<?=$i?>><u><?=$i?></u></a> <?php
				}else{
					?><b><?=$i?></b> <?php
				}
			}
		?>
		<br><br>
		<form action=admin_problem.php method=post>
			<table class="table table-hover">
				<tr>
					<th>ID</th>
					<th>標題</th>
					<th>AC</th>
					<th>Ratio</th>
					<th>contest</th>
					<th>effective</th>
					<th>刪除</th>
					<th>Rejudge</th>
				</tr>
				<?php	for ($i=0 ;$i<$n ;$i++)	{
					$id = $probs[$i]["problem_id"];
				?>
				<tr>
					<td><a href="admin_problem_edit.php?id=<?=$id?>"><?=sprintf ("%04d", $id)?></a></td>
					<td><a href="problem_view.php?id=<?=$id?>"><?=$probs[$i]["title"]?></a>
						<?php
							if ($probs[$i]["execute_type"] == 1) {
								echo " <span class=Interactive><b>[Interactive]</b></span> ";
							}
							if ($probs[$i]["judge_type"] != 0) {
								echo " <span class=SpecialJudge><b>[Special Judge]</b></span> ";
							}
							if (!file_exists ("../testdata/subtest0/$id.in"))
								echo "<span class=note><b>[no input]</b></span> ";
							if (!file_exists ("../testdata/subtest0/$id.out"))
								echo "<span class=note><b>[no output]</b></span> ";
						?>
					</td>
					<td><?=$probs[$i]["AC"]?></td>
					<td>
						<?php
							if ($probs[$i]["total"]!=0)
								echo sprintf ("%.0lf%%", 100.0*$probs[$i]["AC"]/$probs[$i]["total"]);
							else
								echo "0%";
						?>
					</td>
					<td><a href=admin_contest_index.php?contest_id=<?=$probs[$i]["contest_id"]?>><?=get_contest_name ($probs[$i]["contest_id"])?></a></td>
					<td>
						<?=gen_radio_html ("effective[$id]", "Y", $probs[$i]["effective"], "Y")?>
						<?=gen_radio_html ("effective[$id]", "N", $probs[$i]["effective"], "N")?>
					</td>
					<td><a href="admin_problem.php?del=<?=$id?>" onClick="return confirm('delete problem <?=sprintf ("%04d", $id)?>?');">DEL</a></td>
					<td><a href="admin_problem.php?rejudge=<?=$id?>" onCLick="return confirm('rejudge problem <?=sprintf ("%04d", $id)?>?');">Rejudge</a></td>
				</tr>
				<?php } ?>
			</table>
			<br>
			<input type=submit name=submit value=save>
		</form></center>
	</div>
	<!-- footer -->
	<?php require ("../footer.php") ?>
</body>

</html>
