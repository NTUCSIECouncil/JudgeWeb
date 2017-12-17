<?php
session_start ();

require_once ("lib/php/dblink.php");
require_once ("lib/php/func.php");
require_once ("lib/php/security.php");
?>
<?php
	if ($_GET["page"]) {
		$page = check_numeric($_GET["page"]);
	} else { $page = 1; }

	$start = ($page-1)*10;
	$limit = "limit $start, 10";

	$cond = 1;
	$order_by = "";
	$ordering = "";
	$iscontesting = 0;

	if (isset($_GET["rejudge"])) {
		$id = $_GET["rejudge"];
		$sql = "update submission set result='waiting' where submission_id=$id";
		mysql_query ($sql) or die ("Invalid query: $sql");
		log_action ("rejudge submission_id=".$id);
		header("Location:".$_SERVER["HTTP_REFERER"]);
	}

	if ( $_GET["problem_id"] ) {
		$problem_id = check_numeric($_GET["problem_id"]);
		$cond .= " and problem_id=".$problem_id;
		
		$sql = "select contest_id from problems where problem_id=$problem_id";
		$r = mysql_query($sql) or die("Invalid query $sql");
		$tmp = mysql_fetch_array($r);
		$sql = "select contest_id, start_time, end_time from contests where contest_id={$tmp["contest_id"]}";
		$r = mysql_query($sql) or die("Invalid query $r");
		$newhash[$problem_id] = mysql_fetch_array($r);

		if(strtotime($newhash[$problem_id]["end_time"]) - strtotime($newhash[$problem_id]["start_time"]) <= 36000 && strtotime($newhash[$problem_id]["end_time"]) >= strtotime(get_full_date())) {
			$iscontesting = 1;
		}

		if(isset($_GET["orderbytime"]) && $iscontesting==0) {
			$ordering = "&orderbytime";
			//$order_group_by = "GROUP BY user_id ";
			if(isset($_GET["desc"])) {
				$i_set_ordering = 2;
				$order_by = "time desc, ";
				$ordering .= "&desc";
			} else {
				$i_set_ordering = 1;
				$order_by = "time asc, ";
			}
		}
	}

	$order_by .= "submission_id desc";

	if($_SESSION["userid"] || $_SESSION["group"]=="admin" && $_GET["teamname"]) {
		$my_team_name = get_team_name($_SESSION["userid"]);
		if($_SESSION["group"]=="admin") {
			$my_team_name = check_teamname($_GET["teamname"]);
			$admin_set_team_name = "&teamname=".$my_team_name;
		}
		$sql = "select user_id from users where teamname=\"$my_team_name\"";
		$r = mysql_query($sql) or die("Invalid query $sql");
		$rn = mysql_num_rows($r);
		$sql_my_team_mate = "(";

		for($i=0;$i<$rn;$i++) {
			$tmparr = mysql_fetch_array($r);
			if($i>0)
				$sql_my_team_mate .= " OR ";
			$sql_my_team_mate .= "user_id = {$tmparr["user_id"]}";
		}
		$sql_my_team_mate .= ")";
	}
	if( $_GET["contest_id"]) {
		$contest_id = check_numeric($_GET["contest_id"]);

		$sql = "select * from contests where contest_id=$contest_id";
		$r = mysql_query ($sql) or die ("Invalid query: $sql");
		$contest = mysql_fetch_array ($r);
		$end = $contest["end_time"];
	}

	if ($_GET["user_id"]) {
		$user_id = check_numeric($_GET["user_id"]);
		if($_GET["contest_id"] && $contest["personal"]=='N' &&  $user_id==$_SESSION["userid"] || $_SESSION["group"]=="admin" && $_GET["teamname"]) {
			$i_see_my_team = 1;
			$cond .= " and $sql_my_team_mate";
		} else if($iscontesting == 0 || !isset($_GET["problem_id"])) {
			$cond .= " and user_id=".$user_id;
		} else if($_SESSION["userid"]) {
			$cond .= " and user_id=".$_SESSION["userid"];
			$user_id = $_SESSION["userid"];
		}
	}

	if ($_GET["result"] && ($iscontesting==0 || isset($_SESSION["userid"]) && $_SESSION["userid"]==$user_id)) {
		$result = check_string($_GET["result"]);
		$cond .= " and result='$result'";
	}

	if ($_GET["contest_id"]) {
		$sql = "select * from submission where contest_id=$contest_id and `submit_time`<'$end' and $cond order by submission_id desc";
	}
	else {
		$sql = "select * from submission where $cond order by submission_id desc";
	}

	if ($i_set_ordering) {
		$sql = "select * from (select * from submission where $cond order by $order_by) AS tmp_table group by user_id order by $order_by";
	}

	$r = mysql_query ($sql) or die ("Invalid query: $sql");
	$nsub = mysql_num_rows ($r);
	$total = floor (($nsub-1)/10)+1;

	$sql .= " $limit";
	$r = mysql_query ($sql) or die ("Invalid query: $sql");


	$n = 0;

	while ($submit[$n] = mysql_fetch_array ($r)) {
		$n++;
	}

	if ($contest_id) {
		$sql = "select * from problems where contest_id=$contest_id order by problem_id asc";
		$r = mysql_query ($sql) or die ("Invalid query: $sql");
		
		$i = 0;

		while ($problem = mysql_fetch_array ($r)) {
			$hash[$problem["problem_id"]] = sprintf ("%c", 65+$i);
			$i++;
		}

		$sql = "select * from contests where contest_id=$contest_id";
		$r = mysql_query ($sql) or die ("Invalid query: $sql");
		$contest = mysql_fetch_array ($r);
	}

	$ordering.=$admin_set_team_name;
?>
<html>

<head>

<?php include_once ("html_header.php"); ?>

</head>

<body>
	<!-- mean -->
	<?php
		if ($_GET["contest_id"])
			require ("menu_contest.php");
		else
			require ("menu.php");
	?>
    <!-- page -->
    <div class="container jumbotron" style="padding-top: 3%;"> 
		<h2 class="text-center">Judge Status</h2>
		<div style="margin-top: 20px; margin-bottom: 1%;">
			<table class="table table-hover">
				<tr> <!-- &nbsp; = spaec -->
					<th><?php if($i_set_ordering){?>Rank<?php }else{ ?> SID&nbsp; <?php } ?></th>
					<th>Problem&nbsp;</th>
					<th>User&nbsp;</th>
					<th>Team&nbsp;</th>
					<th>Status&nbsp;</th>
					<th>Lang&nbsp;</th>
					<th>
						<?php
						//if select Accepted, can sort, not in contest mode.
							if($iscontesting==0 && $_GET["problem_id"] && $_GET["result"]=="Accepted") {
								echo "<a href=status.php?page=1&user_id=.$user_id&problem_id=$problem_id&contest_id=$contest_id&result=$result";
								if($i_set_ordering == 0)
									echo "&orderbytime";
								else if($i_set_ordering==1)
									echo "&orderbytime&desc";
								echo ">Time ";
								if($i_set_ordering == 1)
									echo " ▼";
								else if($i_set_ordering == 2)
									echo " ▲";
								else
									echo " ►";
								echo "</a>";
							}
							else echo "Time ►";
						?>
					</th>
					<!-- <td>Memory</td> -->
					<th>Submit</th>
					<?php if( $_SESSION["group"] == "admin" ) { ?>
						<th>RJ</th>
					<?php } ?>
				</tr>
					<?php for ($i=0 ;$i<$n ;$i++) { ?>
						<?php
							//tmt: when contest less than 10hr, can't see judge status.
							$tmppid = $submit[$i]["problem_id"];

							if(!isset($newhash[$tmppid])) {
								$sql = "select contest_id from problems where problem_id=$tmppid";
								$r = mysql_query($sql) or die("Invalid query $sql");
								$tmp = mysql_fetch_array($r);
								$sql = "select contest_id, personal, start_time, end_time from contests where contest_id={$tmp["contest_id"]}";
								$r = mysql_query($sql) or die("Invalid query $r");
								$newhash[$tmppid] = mysql_fetch_array($r);
							}
						?>
						<?php
							// no submit
							if($_SESSION["group"]!="admin" && ($newhash[$tmppid]["personal"] =='N' && $my_team_name != get_team_name($submit[$i]["user_id"]) || $newhash[$tmppid]["personal"] == 'Y' && $_SESSION["userid"] != $submit[$i]["user_id"]) && strtotime($newhash[$tmppid]["end_time"]) >= time() && strtotime($newhash[$tmppid]["end_time"]) - strtotime($newhash[$tmppid]["start_time"]) <= 36000) {
									if ($submit[$i]["result"] == "waiting")
										echo "<tr class=\"warning\">";
									else if ($submit[$i]["result"] == "Running")
										echo "<tr class=\"success\">";
					   				else
						   				echo "<tr";
						?>
									<td><?php echo$submit[$i]["submission_id"]?></td>
									<td>-----</td>
									<td>-----</td>
									<td>-----</td>
									<td>-----</td>
									<td>-----</td>
									<td>-----</td>
									<td>-----</td>
								</tr>
						<?php
							} else {
								if (!$hash[$submit[$i]["problem_id"]]) {
									$hash[$submit[$i]["problem_id"]]=sprintf ("%04d", $submit[$i]["problem_id"]);
								}
								if ($submit[$i]["result"] == "waiting")
									echo "<tr class=\"warning\">";
								else if ($submit[$i]["result"] == "Running")
									echo "<tr class=\"success\">";
				  				else
				    				echo "<tr>";
					    ?>
					    <!-- Send ID -->
								<td>
						<?php
								if($i_set_ordering) {
									if($i_set_ordering==1) {
										echo $page*10+$i-9;
									} else {
										$nsub-$page*10+10-$i;
									}
								} else {
									echo$submit[$i]["submission_id"]."&nbsp;";
								}
						?>
								</td>
						<!-- problem id -->
								<td>
									<a href="<?php if($contest_id) echo "contest_view.php?id="; else echo "problem.php?id="; echo $submit[$i]["problem_id"]; if($contest_id) echo "&contest_id=$contest_id"; ?>">
										<?php echo$hash[$submit[$i]["problem_id"]]." - ".get_problem_name($submit[$i]["problem_id"])?>
									</a>
								</td>
						 <!-- User -->
								<td>	
						<?php
								if ($contest_id && $contest["personal"]=="N") {
									//tmt: admin can view who submit it
									if($_SESSION["group"] == "admin" || $contest_id && get_team_name($submit[$i]["user_id"])==$my_team_name) {
										if($_SESSION["group"]=="admin") {
											echo "<a href=status.php?page=$page&user_id={$submit[$i]["user_id"]}&problem_id=$problem_id&contest_id=$contest_id&result=$result>";
										}
											echo get_username($submit[$i]["user_id"]);
										if($_SESSION["group"]=="admin") {
											echo "</a>&nbsp;&nbsp;";
										}
									}	
								} else {
									echo "<a href=\"user_info.php?user_id=".$submit[$i]["user_id"]."\">";
										echo get_username($submit[$i]["user_id"]);	
									echo "</a>";
								}
						?>
								</td>
						<!-- Team -->
								<td>
						<?php 
								if ($contest_id && $contest["personal"]=="N") {
									if($_SESSION["group"]=="admin") {
										$tmp_teamname = rawurlencode(get_team_name($submit[$i]["user_id"]));
										echo "<a href=\"status.php?page=$page&user_id={$submit[$i]["user_id"]}&problem_id=$problem_id&contest_id=$contest_id&result=$result&teamname=$tmp_teamname\">";
									}
										echo htmlspecialchars(get_team_name($submit[$i]["user_id"]));
									if($_SESSION["group"]=="admin") {
										echo "</a>";
									}										
								} else {
									echo "---";
								}
						?>		
								</td>
						<!-- Status -->
								<td>
						<?php 
								if ( ($submit[$i]["error_msg"]!="" ) &&
									 ($_SESSION["group"] == "admin" || $contest_id && get_team_name($submit[$i]["user_id"])==$my_team_name || $_SESSION["userid"]==$submit[$i]["user_id"]) &&
									 ($submit[$i]["result"]!="waiting" and $submit[$i]["result"]!="Running")
								   )
								{
									echo "<a href=\"view_error.php?id=".$submit[$i]["submission_id"]."\" target=_blank>";
										if ($submit[$i]["result"]=="Accepted") {
											echo "<b><u><font color=green>";
										}
										else if (
												$submit[$i]["result"]=="Wrong Answer" ||
												$submit[$i]["result"]=="Time Limit Exceeded" ||
												$submit[$i]["result"]=="Runtime Error" ||
												$submit[$i]["result"]=="Memory Limit Exceeded"
												) {
											echo "<b><u><font color=red>";
										}
										else if ($submit[$i]["result"]=="Compile Error") {
											echo "<b><u><font color=#ADAD00>"; //#ADAD00 is darkyellow
										}
										else {
											echo "<b><u><font>";
										}
											echo $submit[$i]["result"];
										echo "</u></b></font>";		
									echo "</a>";
								} else {
									if ($submit[$i]["result"]=="Accepted") {
										echo "<b><font color=green>";
									}
									else if (
											$submit[$i]["result"]=="Wrong Answer" ||
											$submit[$i]["result"]=="Time Limit Exceeded" ||
											$submit[$i]["result"]=="Runtime Error" ||
											$submit[$i]["result"]=="Memory Limit Exceeded"
											) {
										echo "<b><font color=red>";
									}
									else if ($submit[$i]["result"]=="Compile Error") {
										echo "<b><font color=#ADAD00>"; //#ADAD00 is darkyellow
									}
									else {
										echo "<b><font>";
									}
									echo $submit[$i]["result"];
									echo "</b></font>";
								}
						?>
								</td>
						<!-- Lang -->
								<td>
						<?php
								$can_view_code = ($_SESSION["group"]=="admin") ||
												 /*if team name can change, this must be removed*, or can see other team's code*/
												 ($contest_id && get_team_name($submit[$i]["user_id"]) == $my_team_name) || 
												 ($_SESSION["userid"]==$submit[$i]["user_id"]);
								if( $can_view_code ) {
									if($_SESSION["group"]=="admin") {
										echo "<a href=\"admin/view_code.php?id=".$submit[$i]["submission_id"]."\" target=\"_blank\">";	
									} else {
										if( $contest_id ) {
											echo "<a href=\"view_code.php?id=".$submit[$i]["submission_id"]."&contest_id=".$contest_id."\" target=\"_blank\">";
										}
										else {
											echo "<a href=\"view_code.php?id=".$submit[$i]["submission_id"]."\" target=\"_blank\">";
										}
									}
								}
								echo $submit[$i]["language"];
								if( $can_view_code ){ echo "</a>"; }
						?>
								</td>
						<!-- Time -->
								<td><?php echo$submit[$i]["time"]?></td>
						<!-- memory -->
							<!-- <td><?php echo$submit[$i]["memory"]?></td> -->
						<!-- Submit -->
								<td><?php echo$submit[$i]["submit_time"]?></td>
						<?php } ?>
						<!-- RJ -->					
						<?php if( $_SESSION["group"] == "admin" ) { ?>
							<td><a href="status.php?rejudge=<?=$submit[$i]["submission_id"]?>">RJ</a></td>
						<?php } ?>
					<?php } ?>
				</tr>

			</table>
			<div id="pageNumder" class="text-center">
				<?php
					$last = floor (($page-1)/10)*10;
					$next = floor (($page-1)/10)*10+11;
					$prevone = $page-1;
					$nextone = $page+1;

					if ($last>0)
						echo "<b><a href=\"status.php?page=$last&user_id=$user_id&problem_id=$problem_id&contest_id=$contest_id&result=$result$ordering\">&lt;&lt;</a></b> &nbsp; ";
					else
						echo "<font color=#AAAAAA><b>&lt;&lt;</b></font> &nbsp; ";
					if ($prevone>0)
						echo "<b><a href=\"status.php?page=$prevone&user_id=$user_id&problem_id=$problem_id&contest_id=$contest_id&result=$result$ordering\">&lt; </a></b> &nbsp; ";
					else
						echo "<font color=#AAAAAA><b>&lt; </b></font> &nbsp; ";

					for ($i=1 ;$i<=$total ;$i++) {
						if (floor (($i-1)/10)!=floor(($page-1)/10))
							continue;
						echo "<b>";
						if ($i==$page)
							echo "<font size=5><b><u>$i</u></b></font> ";
						else
							echo "<a href=\"status.php?page=$i&user_id=$user_id&problem_id=$problem_id&contest_id=$contest_id&result=$result$ordering\"><font onmouseover='this.size=5' onmouseout='this.size=3'> ".$i." </font></a>";
						echo "</b>";
					}
					
					if ($nextone <= $total)
						echo "&nbsp; <b><a href=\"status.php?page=$nextone&user_id=$user_id&problem_id=$problem_id&contest_id=$contest_id&result=$result$ordering\">&gt; </a></b> &nbsp;";
					else
						echo "&nbsp; <font color=#AAAAAA><b>&gt; </b></font> &nbsp;";

					if ($next<=$total)
						echo "<b><a href=\"status.php?page=$next&user_id=$user_id&problem_id=$problem_id&contest_id=$contest_id&result=$result$ordering\">&gt;&gt;</a></b>";
						else
						echo "&nbsp; <font color=#AAAAAA><b>&gt;&gt;</b></font>";
				?>			
			</div>		
		</div>
    </div>
   	<!-- footer -->
	<?php require ("footer.php") ?>
</body>

</html>
