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

	$contest_id = check_numeric($_GET["contest_id"]);

	$sql = "select * from contests where contest_id=$contest_id and effective='Y'";
	$r = mysql_query ($sql) or die ("Invalid query: $sql");

	$contest = mysql_fetch_array ($r);

	$now = get_full_date ();

	if (!$contest or $now<$contest["start_time"]) {
		header ("Location: index.php");
		exit (0);
	}

	if ($now > $contest["end_time"])
		$contest_has_ended = 1;

	if ($contest["virtual"]=="Y" and $contest["end_time"]>$now) {
		$_SESSION["contest"] = $contest["title"];
		if ($contest["personal"]=="Y")
			record_start_by_user ($_SESSION["userid"], $contest["contest_id"]);
		else
			record_start_by_team (get_team_name ($_SESSION["userid"]), $contest["contest_id"]);
	}

	$sql = "select * from problems where contest_id=$contest_id order by problem_id asc";
	$r = mysql_query ($sql) or die ("Invalid query: $sql");

	$n = 0;
	while ($prob[$n] = mysql_fetch_array ($r)) { $n++; }
?>
<html>

<head>

<?php include_once ("html_header.php"); ?>

</head>

<body>
	<!-- mean -->
	<?php require ("menu_contest.php"); ?>
	<!-- page -->
	<div class="container jumbotron" style="padding-top: 3%;">
		<h2 class="text-center"><?=$contest["title"]?></h2><hr>
		<h3 class="text-center" style="padding-top: 2%;">
			<?php
				$time = strtotime ($contest["end_time"]) - strtotime (get_full_date());

				if ($contest["virtual"]=="Y" and get_full_date()<$contest["end_time"])
				{
					if ($contest["personal"]=="Y")
						$start_time = get_start_by_user ($_SESSION["userid"], $contest["contest_id"]);
					else
						$start_time = get_start_by_team (get_team_name ($_SESSION["userid"]), $contest["contest_id"]);

					$time = strtotime ($start_time)+$contest["duration"]*3600-strtotime (get_full_date());

					$time2 = strtotime ($contest["end_time"]) - strtotime (get_full_date());

					if ($time2<$time)
						$time = $time2;
				}

				if ($time <=0)
					echo "Contest is over.";
				else {
					$s = $time%60;
					$time = floor ($time/60);
					$m = $time%60;
					$h = floor ($time/60);
					printf ("<font id=\"font0\">Count Down: <span id=\"timer0\">%02d:%02d:%02d</span></font>", $h, $m, $s);
				}
			?>
		</h3>
		<div style="margin: 15%; margin-bottom: 1%; margin-top: 5%;">
			<table class="table table-hover">
				<tr>
					<th>Problem Name</th>
					<th>Time Limit</th>
					<th>Judge Type</th>
				</tr>

				<?php for ($i=0 ;$i<$n ;$i++) { ?>
					<?php 
						if ($prob[$i]["effective"]=="N") continue;
						$id = $prob[$i]["problem_id"];
					?>
				<tr>
					<td>
					<?php
						$alphabet = chr(65+$i);
						if ($contest_has_ended) {
							echo "<a href=\"problem.php?id=".$id."\">".$alphabet."&nbsp;-&nbsp;".$prob[$i]["title"]."</a>";
						} else {
							echo "<a href=\"contest_view.php?id=".$id."&contest_id=".$contest_id."\">".$alphabet."&nbsp;-&nbsp;".$prob[$i]["title"]."</a>";
						}
					?>
					</td>
					<td><?=$prob[$i]["time_limit"]?>s</td>
					<td>
					<?php
						if ($prob[$i]["execute_type"] == 1 || $prob[$i]["judge_type"] != 0) {
							if ($prob[$i]["execute_type"] == 1)
								echo "<font style=\"color: blue;\">[Interactive]</font> ";
							if ($prob[$i]["judge_type"] != 0)
								echo "<font style=\"color: red;\">[Special Judge]</font> ";
						} else {
							echo "[Regular Judge] ";
						}
					?>
					</td>
				</tr>
				<?php } ?>
			</table>
		</div>
		<div class="text-center">
			<button onclick="location.href='index.php'" type="button" class="btn btn-default">回首頁</button>
		</div>
	</div>
	<!-- footer -->
	<?php require ("footer.php") ?>
</body>
<script>
	timer0=document.getElementById('timer0');
	h=<?=$h?>; m=<?=$m?>; s=<?=$s?>;
	//set time counter: special thanks to DarkKnight
	var counter_id = setInterval( function (){
		if(--s< 0) { s+=60; m--; }
		if(m  < 0) { m+=60; h--; }
		if(h == 0 && m == 0 && s == 0) { location.reload(); }
		if(timer0!=undefined){
			if(h==0) {
				font0 = document.getElementById('font0');
				font0.color = "red";
			}
			timer0.innerHTML=''+(h<10?'0':'')+h+':'+(m<10?'0':'')+m+':'+(s<10?'0':'')+s;
		}
	}, 1000);
</script>
</html>
