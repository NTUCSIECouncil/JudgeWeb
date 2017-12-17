<?php
session_start ();
if ($_SESSION["group"]!="admin") {
	header ("Location: ../index.php");
	exit (0);
}
require_once ("../lib/php/dblink.php");
require_once ("../lib/php/func.php");
$id = $_GET["id"];

$sql = "select * from problems where problem_id=$id";
$r = mysql_query ($sql) or die ("Invalid query: $sql");

$problem = mysql_fetch_array ($r);


$contest_id = $problem["contest_id"];


$sql = "select * from contests where contest_id=$contest_id";
$r = mysql_query ($sql) or die ("Invalid query: $sql");

$contest = mysql_fetch_array ($r);

$now = get_full_date ();
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
		<div style="padding-right: 10%; padding-left: 10%;">
			<?php
				$sql = "select * from problems where problem_id<$id and contest_id=$contest_id";
				$r = mysql_query ($sql) or die ("Invalid query: $sql");
				$index = mysql_num_rows ($r);
			?>
			<div class="text-center" >
				<h2 style="font-size: 3.5em;"><?=sprintf ("Problem %c - ", 65+$index).$problem["title"]?></h2>
				<p style="font-size: 1.2em;"><b>Problem id : <?=sprintf ("%04d", $id)?></b></p>	
			</div>
			<p><b>Judge Type :</b></p>
			<?php
				echo "<p style=\"text-indent: 2em;\">";
				if ($problem["execute_type"] == 1 || $problem["judge_type"] != 0) {
					if ($problem["execute_type"] == 1)
						echo "<font style=\"color: blue;\">[Interactive]</font> ";
					if ($problem["judge_type"] != 0)
						echo "<font style=\"color: red;\">[Special Judge]</font> ";
				} else {
					echo "[Regular Judge] ";
				}
				echo "</P>";
			?>
			<p><b>Task Description :</b></p>
			<div class="problemview"><?=$problem["description"]?></div>
			<p><b>Input Format :</b></p>
			<div class="problemview"><?=$problem["input"]?></div>
			<p><b>Output Format :</b></p>
			<div class="problemview"><?=$problem["output"]?></div>
			<?php
				echo "<ul style=\"font-size: 1.2em;\">";
				if ($problem["execute_type"] == 1) {
					echo "<li>This is an interactive problem, ".
						"please make sure you have added fflush(stdout) at every time you output.</li>";
				}
				if ($problem["judge_type"] >= 10 && $problem["judge_type"] <= 25) {
					$precision = $problem["judge_type"] - 10;
					echo "<li>Your submission will be judged by a special judging program.<br>".
						 "Any answer with a relative or absolute error at most 1e-$precision will be accepted.</li>";
				} else if ($problem["judge_type"] != 0) {
					echo "<li>Your submission will be judged by a special judging program.<br>".
						 "Please read the output format carefully.</li>";
				}
				echo "</ul>";
			?>
			<p><b>Limit :</b></p>
			<p style="text-indent: 2em; font-size: 1.2em;">Time Limit: <?=$problem["time_limit"]?> sec</p>
			<p style="text-indent: 2em; font-size: 1.2em;">Output Limit: <?=$problem["output_limit"]/2?> kB</p>
			<p><b>Sample Input :</b></p>
			<?php if ($problem["sample_input"]!="") { ?>
<pre>
<?php echo $problem["sample_input"]?>
</pre>
			<?php } else { ?>
<pre>
None Input
</pre>		
			<?php } ?>	
			<?php
				if ($problem["execute_type"] == 1) {
					echo "<ul style=\"font-size: 1.2em;\"><li>This is an interactive problem, ".
						"the sample input is just a sample.</li></ul>";
				}
			?>
			<p><b>Sample Output :</b></p>
			<?php if ($problem["sample_output"]!="") { ?>
<pre>
<?php echo $problem["sample_output"]?>
</pre>
			<?php } else { ?>
<pre>
None output
</pre>		
			<?php } ?>
			<?php if ($problem["hint"]!="") { ?>
			<p><b>Hint :</b></p>
<pre>
<?=$problem["hint"]?>
</pre>
			<?php } ?>
<!-- 		
			<?php
				// if (!$no_tag_display) {
			?>
			<p><b>Tags :</b></p>
			<span>
			<?php 
				// if (sizeof($problem["tags"])>0) 
				// {
				// 	foreach ($problem["tags"] as $tag)
				// 	{
				// 		echo "<a href='volume.php?tag_id=".$tag['id']."'>";
				// 		echo $tag['value'];
				// 		echo "</a>";
				// 		echo " ";
				// 		echo "(by ";
				// 		echo "<a href='user_info.php?user_id=".$tag['user_id']."'>";
				// 		echo get_username($tag['user_id']);
				// 		echo "</a>";
				// 		echo ")<br>";
				// 	}
				// }
			?>
				<form method=post action="problem.php?id=<?=$id?>">
					<input type="text" name="tag">
					<button type="submit" class="btn btn-default" name="submit" value="Add New Tag">Submit</button>
				</form>
			</span>
			<?php
				// }
			?>
-->
		</div>
		<div class="text-center">
			<button onclick="location.href='../status.php?problem_id=<?=$id?>'" type="button" class="btn btn-default" >Status</button>
			<button onclick="location.href='../status.php?problem_id=<?=$id?>&result=Accepted'" type="button" class="btn btn-default" >Status(AC)</button>
		</div>
	</div>
	<!-- footer -->
	<?php require ("../footer.php") ?>
</body>

</html>
