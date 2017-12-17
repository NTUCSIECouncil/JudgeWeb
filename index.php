<?php
	session_start ();

	require_once ("lib/php/dblink.php");
	require_once ("lib/php/func.php");


//page
	$page_size=15;
	if ($_GET["page"])
		$page = check_numeric($_GET["page"]);
	else
		$page = 1;

	$start = ($page-1)*$page_size;
	$limit = "limit $start, $page_size";

	$sql = "select * from contests where effective='Y' order by start_time desc";
	$r = mysql_query ($sql) or die ("Invalid query: $sql");
	$n = mysql_num_rows($r);
	$total = floor (($n-1)/$page_size)+1;

	$sql .= " $limit";
	$r = mysql_query ($sql) or die ("Invalid query: $sql");

	$n = 0;
	while ($contest[$n] = mysql_fetch_array ($r)) {
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
		<div class="text-center">
			<h1 style="font-size: 3em;">台灣大學資訊工程學系。系學會學術部</h1>
		</div>
		<hr>

		<?php if (!$_SESSION["userid"]) { ?>
			<div class="text-center" style="margin-top: 2%;">
				<h2 style="font-size: 2em;">請先登入<br><br><small>帳號密碼均為：RookieTeam[TeamID]</small></h2>
			</div>				
			<center>
			<form class="form-horizontal" action="login.php" method="post" style="width: 70%;">
				<div class="form-group">
					<label for="username" class="col-sm-2 control-label">Username: </label>
					<div class="col-sm-9">
						<input type="text" name="username" class="form-control" id="username">
					</div>
				</div>
				<div class="form-group">
					<label for="password" class="col-sm-2 control-label">Password: </label>
					<div class="col-sm-9">
						<input type="password" name="password" class="form-control" id="password">
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-8">
						<button class="btn btn-default" type="submit" name="submit" value="LOGIN">LOGIN</button>
					</div>
				</div>
			</form>
			</center>
		<?php } else { ?>
			<h2 class="text-center">
				<span><?php echo date('Y-m-d', time()); ?></span>
				<?php
					$time = strtotime(date("Y-m-d H:i:s"))- strtotime(date('Y-m-d', time()). '00:00:00');

					$s = $time%60;
					$time = floor ($time/60);
					$m = $time%60;
					$h = floor ($time/60);
					printf ("<span id=\"timer0\">. %02d:%02d:%02d</span>", $h, $m, $s);
				?>
			</h2>
		<div style="margin-top: 20px; margin-bottom: 1%;">
			<table class="table table-hover">
				<thead>
					<tr>
						<th>Contest Name</th>
						<th>Start</th>
						<th>End</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>
					<?php
						for ($i=0 ;$i<$n ;$i++)	{
							$now = get_full_date ();
							$id = $contest[$i]["contest_id"];
							$start_time = $contest[$i]["start_time"];
							$end_time = $contest[$i]["end_time"];

							if ($now<$start_time)
								$status = "Pending";
							else if ($start_time<=$now && $now<$end_time)
								$status = "Running";
							else
								$status = "Over";
					?>
					<tr>
						<td>
							<?php
								if ($status!="Pending") {
									echo "<a href=\"contest_index.php?contest_id=".$id."\">".$contest[$i]["title"]."</a>";
								} else {
									echo $contest[$i]["title"];
								}
							?>
						</td>
						<td><?php echo $contest[$i]["start_time"]; ?></td>
						<td><?php echo $contest[$i]["end_time"]; ?></td>
						<td><?php echo $status; ?></td>
					</tr>
					<?php } ?>
				</tbody>	
			</table>
		</div>
		<?php }	?>	
    </div>
   	<!-- footer -->
	<?php require ("footer.php") ?>
</body>
<script>
	timer0=document.getElementById('timer0');
	h=<?=$h?>; m=<?=$m?>; s=<?=$s?>;
	//set time counter: special thanks to DarkKnight
	var counter_id = setInterval( function (){
		if(s++>=60) { s-=60; m++; }
		if(m  >=60) { m-=60; h++; }
		if(h  >=24) { location.reload(); }
		if(timer0!=undefined){
			timer0.innerHTML='. '+(h<10?'0':'')+h+':'+(m<10?'0':'')+m+':'+(s<10?'0':'')+s;
		}
	}, 1000);
</script>
</html>
