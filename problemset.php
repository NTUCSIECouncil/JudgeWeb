<?php
session_start ();

if ($_SESSION["group"]!="user" and $_SESSION["group"]!="admin") {
	header ("Location: index.php");
	exit (0);	
}

require_once ("lib/php/dblink.php");
require_once ("lib/php/func.php");
?>
<?php
	$sql = "select * from problems where effective='Y' order by problem_id asc";
	$r = mysql_query ($sql) or die ("Invalid query: $sql");

	$n = 0;

	$now = get_full_date ();

	while ($problems[$n] = mysql_fetch_array ($r))
	{
		if ($problems[$n]["contest_id"]>0)
		{
			$contest_id = $problems[$n]["contest_id"];
			$sql = "select * from contests where contest_id=$contest_id";
			$r2 = mysql_query ($sql) or die ("Invalid query: $sql");

			$contest = mysql_fetch_array ($r2);

			if ($now < $contest["start_time"])
				continue;
			if ($contest["effective"] == 'N')
				continue;
			if ($contest["virtual"]=="Y" and $now<$contest["end_time"]) {
				if ($contest["personal"]=="Y" and get_start_by_user ($_SESSION["userid"], $contest_id)=="")
					continue;
				if ($contest["personal"]=="N" and get_start_by_team (get_team_name ($_SESSION["userid"]), $contest_id)=="")
					continue;
			}
		}

		$problem_id = $problems[$n]["problem_id"];
		$sql = "select submission_id from submission where problem_id=$problem_id and result='Accepted'";
		$r2 = mysql_query ($sql) or die ("Invalid query: $sql");
		$problems[$n]["AC"] = mysql_num_rows ($r2);
		$sql = "select submission_id from submission where problem_id=$problem_id";
		$r2 = mysql_query ($sql) or die ("Invalid query: $sql");
		$problems[$n]["total"] = mysql_num_rows ($r2);
		$n++;
	}
?>
<html>

<head>

<?php include_once ("html_header.php"); ?>

</head>

<body>
	<!-- mean -->
	<?php require ("menu.php") ?>
    <!-- page -->
    <div class="container jumbotron" style="padding-top: 3%;">
    	<h2 class="text-center">Problem Set</h2>
		<div style="margin-bottom: 1%; margin-top: 20px;">
			<table class="table table-hover">
				<tr>
					<th>Me</th>
					<th>ID</th>
					<th>Title</th>
					<th>Contest</th>
					<th>AC</th>
					<th>Ratio</th>
				</tr>

				<?php for ($i=0 ;$i<$n ;$i++) { ?>
				<tr>
					<!-- Me -->
					<td align=center>
						<?php
							if ($_SESSION["userid"])
							{
								$sql = "select submission_id from submission where user_id=".$_SESSION["userid"]." and problem_id=".$problems[$i]["problem_id"];
								$r = mysql_query ($sql) or die ("Invalid query: $sql");
								if (mysql_num_rows ($r)>0)
								{
									$sql = "select submission_id from submission where result='Accepted' and user_id=".$_SESSION["userid"]." and problem_id=".$problems[$i]["problem_id"];
									$r = mysql_query ($sql) or die ("Invalid query: $sql");
									if (mysql_num_rows ($r)>0)
										echo "<font color='green'>AC</font>";
									else
										echo "<font color='red'><b>==</b></font>";
								}
							}
						?>
					</td>
					<!-- ID -->
					<td>
						<a href="problem.php?id=<?=$problems[$i]["problem_id"]?>"><?=sprintf ("%04d", $problems[$i]["problem_id"])?></a>
					</td>
					<!-- Title -->
					<td><a href="problem.php?id=<?=$problems[$i]["problem_id"]?>"><?=$problems[$i]["title"]?></a></td>
					<!-- Contest -->
					<td><a href="contest_index.php?contest_id=<?=$problems[$i]["contest_id"]?>"><?=get_contest_name($problems[$i]["contest_id"])?></a></td>
					<!-- AC -->
					<td><a href="status.php?problem_id=<?=$problems[$i]["problem_id"]?>&result=Accepted"><?=$problems[$i]["AC"]?></a></td>
					<!-- Ratio -->
					<td>
						<?php
							if ($problems[$i]["total"]!=0)
								echo sprintf ("%.0f%%", 100*$problems[$i]["AC"]/$problems[$i]["total"]);
							else
								echo "0%";
						?>
					</td>
				</tr>
				<?php } ?>
			</table>
		</div>
    </div>
   	<!-- footer -->
	<?php require ("footer.php") ?>
</body>

</html>
