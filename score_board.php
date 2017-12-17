<?php
session_start ();

require_once ("lib/php/dblink.php");
require_once ("lib/php/func.php");
require_once ("lib/php/security.php");
?>
<?php
	$result_penalty = [
		"Compile Error" 		=> 0,
		"Runtime Error" 		=> 20,
		"Time Limit Exceeded" 	=> 20,
		"Memory Limit Exceeded" => 20,
		"Output Limit Exceeded" => 20,
		"Wrong Answer" 			=> 20,
	];

	$settimeout_value = 30; // 30 sec

	$contest_id = mysql_real_escape_string(check_numeric($_GET["contest_id"]));

	$sql = "select * from contests where contest_id=".$contest_id;
	$r = mysql_query ($sql) or die ("Invalid query");

	if(mysql_num_rows($r) == 0) {
	   	header("Location: contest_index.php?contest_id=$contest_id");
		exit;
	}

	$contest = mysql_fetch_array ($r);

	$now = get_full_date();
	if ($now<$contest["start_time"] || ($now>get_date_after ($contest["end_time"], 1) && $_SESSION["group"]!="admin")) {
		header ("Location: contest_index.php?contest_id=$contest_id");
		exit;
	}

	$sql = "select * from problems where contest_id=$contest_id order by problem_id asc";
	$r = mysql_query ($sql) or die ("Invalid query");
	$prob_n = 0;

	while ($prob[$prob_n] = mysql_fetch_array ($r)) {
		$hash[$prob[$prob_n]["problem_id"]] = $prob[$prob_n]["effective"];
		$prob[$prob_n]["short"] = sprintf ("%c", 65+$prob_n);
		$prob_n++;
	}

	// TODO: virtual contest
	if ($contest["virtual"] == "N") {
		$sql = "SELECT * FROM `users`
				WHERE EXISTS (
					SELECT * FROM `submission`
					WHERE `users`.`user_id` = `submission`.`user_id`
					AND `submission`.`contest_id`=$contest_id
				)";
		$r = mysql_query($sql);
		$user_n = 0;
		while ($user[$user_n] = mysql_fetch_array($r)) {
			$userid2team[ $user[$user_n]["user_id"] ] = $user[$user_n]["teamname"];
			$user_n++;
		}

		$end = $contest["end_time"];
		if ((strtotime($contest["end_time"]) - strtotime($contest["start_time"]) >= 60) && get_full_date() < get_date_after ($contest["end_time"], 1) && $_SESSION["group"]!="admin") {
			$end = get_date_after ($contest["end_time"], -1);
		}

		$sql = "SELECT submission.user_id, problem_id, submit_time, result FROM submission 
				JOIN users ON users.user_id = submission.user_id
				WHERE contest_id=$contest_id AND submit_time < '$end' AND users.group <> 'admin'
				ORDER BY submit_time ASC";
		$r = mysql_query ($sql);

		while ($submit = mysql_fetch_array($r)) {
			//the problem is not available
			if ($hash[$submit["problem_id"]]=="N")
				continue;

			//decide indexing
			if ($contest["personal"] == "N") {
				$index = $userid2team[ $submit["user_id"] ];
			} else {
				$index = $submit["user_id"];
			}
			if (!$appear[$index]) {
				$appear[$index] = 1;
				$rec[$index]["solved"] = 0;
				$rec[$index]["penalty"] = 0;
			}

			//if already AC, skip
			if ($rec[$index][$submit["problem_id"]]["AC"]) {
				continue;
			}
			//if not judged yet, skip
			if ($submit["result"] == "waiting" || $submit["result"] == "Running") {
				continue;
			} else if ($submit["result"]=="Accepted") {
				$rec[$index][$submit["problem_id"]]["AC"] = 1;
				$rec[$index]["solved"] += 1;
				$rec[$index]["penalty"] += $rec[$index][$submit["problem_id"]]["penalty"];
				
				if ($contest["virtual"]=="Y") {
					if ($contest["personal"]=="Y")
						$base = get_start_by_user ($index, $contest_id);
					else
						$base = get_start_by_team ($index, $contest_id);
				} else {
					$base = $contest["start_time"];
				}
				$rec[$index][$submit["problem_id"]]["time"] = floor ((strtotime ($submit["submit_time"]) - strtotime ($base))/60);
				$rec[$index]["penalty"] += $rec[$index][$submit["problem_id"]]["time"];

				if (!$first[$submit["problem_id"]]) {
					$first[$submit["problem_id"]] = $index;
				}
			} else if ( $submit["result"]=="Compile Error" ||
						$submit["result"]=="Runtime Error" ||
						$submit["result"]=="Time Limit Exceeded" ||
						$submit["result"]=="Memory Limit Exceeded" ||
						$submit["result"]=="Output Limit Exceeded" ||
						$submit["result"]=="Wrong Answer" )
			{
				if ( $result_penalty[ $submit["result"] ] ) {
					$rec[$index][$submit["problem_id"]]["penalty"] += $result_penalty[ $submit["result"] ];
					$rec[$index][$submit["problem_id"]]["numof_times"] += 1;
				}			
			}
		}

		if(!$appear) {
			$appear = array();
			$data = array();
			$solved = array();
		}
		foreach ($appear as $index=>$value) {
			$data[$index] = array("solved" => $rec[$index]["solved"], "penalty" => $rec[$index]["penalty"], "index" => $index);
			$solved[$index] = $rec[$index]["solved"];
			$penalty[$index] = $rec[$index]["penalty"];
			$name[$index] = $index;
		}
	} else {
		echo "service unavailable for virtual contest!";
		exit(0);
	}

	array_multisort ($solved, SORT_DESC, $penalty, SORT_ASC, $name, SORT_ASC, $data);
?>

<html>

<head>

<?php include_once ("html_header.php"); ?>

</head>

<body>
	<!-- mean -->
	<?php require ("menu_contest.php") ?>
    <!-- page -->
    <div class="container jumbotron" style="padding-top: 3%;"> 
		<!-- <h2 class="text-center"><a href=contest_index.php?contest_id=<?=$contest_id?>><?=$contest["title"]?></a></h2><hr> -->
		<!-- timer -->
		<div class="text-center">
			<?php
				$h=$m=$s=-1;
				$time = strtotime ($contest["end_time"]) - strtotime (get_full_date());
				if ($time <= 0) {
					echo "Final Standing";
				}
				else
				{
					$s = $time%60;
					$time = floor ($time/60);
					$m = $time%60;
					$h = floor ($time/60);

					echo "<h3 style=\"padding-top: 1%;\"><font id=\"font0\">Count Down: <span id=\"timer0\">".sprintf ("%02d:%02d:%02d", $h, $m, $s)."</span></font></h3>";
					echo "[Last Cache Count Down: ".sprintf("%02d:%02d:%02d",$h,$m,$s)."]";
				}
				if (get_full_date()>=get_date_after($contest["end_time"], -1) && get_full_date()<$contest["end_time"] && $_SESSION["group"]!="admin"){
					echo "<br>Scoreboard is not updating until contest over";
				}
			?>	
		</div>
		<!-- score board -->
		<div style="margin-bottom: 1%; margin-top: 1%; border: 3px solid gray; padding: 3px;">
			<table class="table" style="margin-bottom: 0;">
				<tr>
					<th style="text-align:center">Rank</th>
					<th>&nbsp;
						<?php
							if ($contest["personal"]=="Y"){ echo "<b>User</b>"; }
							else{ echo "<b>Team</b>"; }
						?>
					</th>
					<th style="text-align:center">Solved</th>
					<th style="border-right: 2px dashed black;">Time</th>
					<?php for ($i=0 ;$i<$prob_n ;$i++) { ?>
						<?php if ($prob[$i]["effective"]=="N") { continue; } ?>
						<th>&nbsp;&nbsp;
							<a href=contest_view.php?contest_id=<?=$contest_id?>&id=<?=$prob[$i]["problem_id"]?>>
								<?=$prob[$i]["short"]?>
							</a>
						</th>
					<?php } ?>
					<?php if ($contest["virtual"]=="Y") { ?>
						<th>start time</th>
					<?php } ?>
				</tr>
				<?php $i = 1;?>
				<?php foreach ($data as $key=>$value) { ?>
				<?php
					if (!$appear [$value["index"]]) {
						continue;
					}

					if($value["index"]=="DarkTmt" || $value["index"]=="DarkTA") {
						continue;
					}
				?>
				<tr>
					<?php
						$BorderRadius = "style=\"border-radius: 20px 5px 5px 20px;\"";
						if( $i == 1 ) {
							$top3color = "background=\"lib/img/gold.jpg\"".$BorderRadius;
						} else if ( $i == 2 ) {
							$top3color = "background=\"lib/img/silver.jpg\"".$BorderRadius;
						} else if ( $i == 3 ) {
							$top3color = "background=\"lib/img/copper.jpg\"".$BorderRadius;
						} else { $top3color = "bgcolor=\"lightgray\"".$BorderRadius; }
			    	?>				
					<td <?=$top3color?> align="center"><b><?=$i?></b></td>
					<td>&nbsp;
						<?php 
							if ($contest["personal"]=="Y") {
						?>
								<a href="user_info.php?user_id=<?=$value["index"]?>"><b><?=get_username ($value["index"])?></b></a>
						<?php
							} else {
								$tmp = htmlspecialchars($value["index"]);
								if (strlen($tmp) < 16) {
									echo "<b>";
								} else {
									$tmp = substr($tmp, 0, 12)."...";
									$teamid = "team".$i;
						?>
									<b onmouseover="fadeInTeamName('<?=$teamid?>')" onmouseout="fadeOutTeamName('<?=$teamid?>')">
									<span class="teamname-hover" id="<?=$teamid?>">
										<a href='user_info.php?teamname=<?=rawurlencode($value['index'])?>'><?=$value['index']?></a>
									</span>
						<?php
								}
						?>
						<a href="user_info.php?teamname=<?=rawurlencode($value["index"])?>"><?=$tmp?></a></b>
						<?php  } ?>
					</td>
					<!-- Sloved -->
					<td align="center">
						<?php  echo "<b>".$value["solved"]."</b>"; ?>
					</td>
					<!-- penalty -->
					<td style="border-right: 2px dashed black;" ><b><?=$value["penalty"]?></b></td>
					<!-- all problems -->
					<?php for ($j=0 ;$j<$prob_n ;$j++) { ?>
					<?php
						if ($prob[$j]["effective"]=="N") { continue; }

						if ($first[$prob[$j]["problem_id"]] == $value["index"]) {
							$prob_color = "bgcolor=\"green\"";
						} else if( $rec[$value["index"]][$prob[$j]["problem_id"]]["AC"] ) {
							$prob_color = "bgcolor=\"lightgreen\"";
						} else if ($rec[$value["index"]][$prob[$j]["problem_id"]]["numof_times"] != 0) {
							$prob_color = "bgcolor=\"#FF8888\"";
						} else { $prob_color = ""; }
			    	?>
			    		<td <?=$prob_color?> style="padding-left: 1%;">
				    		<b>
					    		<?=$rec[$value["index"]][$prob[$j]["problem_id"]]["numof_times"]/1?> /
					<?php 
								if ( $rec[$value["index"]][$prob[$j]["problem_id"]]["AC"] ) {
									echo $rec[$value["index"]][$prob[$j]["problem_id"]]["time"];
								}
								else{ echo "---"; }
					?>
							</b>
						</td>
					<?php } ?>
					<?php
						if ($contest["virtual"]=="Y") {
							if ($contest["personal"]=="Y") {
								echo "<td class=statusform><b>".get_start_by_user ($value["index"], $contest_id)."</b></td>\n";
							} else {
								echo "<td class=statusform><b>".get_start_by_team ($value["index"], $contest_id)."</b></td>\n";
							}
						}
					?>
				</tr>
				<?php $i++; ?>
				<?php } ?>
			</table>
		</div>
    </div>
   	<!-- footer -->
	<?php require ("footer.php") ?>
</body>
<!-- timer -->
<script language='javascript'>
    h=<?=$h?>;
    m=<?=$m?>;
    s=<?=$s?>;
    if(h == 0) {
        font0 = document.getElementById('font0');
        font0.color = 'red';
    }
	function timer(){
		if(--s<0)s+=60,m--;
		if(m<0)m+=60,h--;
		if(h<0)location.reload();
		if(timer0!=undefined){
			if(h==0)
			{
				font0 = document.getElementById('font0');
				font0.color = 'red';
			}
			timer0.innerHTML=''+(h<10?'0':'')+h+':'+(m<10?'0':'')+m+':'+(s<10?'0':'')+s;
		}
	}
	if (h >= 0)
		setInterval(timer, 1000);
	timer0 = document.getElementById("timer0");
</script>
<!-- teamnamehover -->
<script language='javascript'>
	var transforming = 0;
	function fadeInTeamName(tmp) {
		if (transforming == 0) {
			transforming = 1;
			jQuery('#'+tmp).fadeIn('fast', function(){transforming = 0;});
		}
	}
	function fadeOutTeamName(tmp) {
		if (transforming == 0) {
			transforming = 1;
			jQuery('#'+tmp).fadeOut('fast', function(){transforming = 0;});
		}
	}
</script>
<!-- auto reload -->
<script type=text/javascript>
	setTimeout(function() {window.location.reload()}, <?=$settimeout_value?>*1000);
</script>
</html>
