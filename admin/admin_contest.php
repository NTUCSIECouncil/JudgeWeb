<?php
session_start ();
if ($_SESSION["group"]!="admin") {
	header ("Location: ../index.php");
	exit (0);
}

require_once ("../lib/php/func.php");
require_once ("../lib/php/dblink.php");

if ($_GET["del"]) {
	$id = $_GET["del"];
	$sql = "delete from contests where contest_id=$id";
	mysql_query ($sql) or die ("Invalid query: $sql");

	$sql = "delete from problems where contest_id=$id";
	mysql_query ($sql) or die ("Invalid query: $sql");

	$sql = "delete from submission where contest_id=$id";
	mysql_query ($sql) or die ("Invalid query: $sql");
}

if ($_POST["submit"]=="123") {
	$QQ["date"] = $_POST["date"];
}

if ($_POST["submit"]=="新增比賽") {
	$contest["title"] = $_POST["title"];
	$contest["start_time"] = $_POST["s_year"]."-".$_POST["s_month"]."-".$_POST["s_day"]." ".$_POST["s_hour"].":".$_POST["s_min"].":00";
	$contest["end_time"] = $_POST["e_year"]."-".$_POST["e_month"]."-".$_POST["e_day"]." ".$_POST["e_hour"].":".$_POST["e_min"].":00";
	$contest["effective"] = "Y";
	$contest["virtual"] = $_POST["virtual"];
	$contest["personal"] = $_POST["personal"];
	$contest["duration"] = $_POST["duration"];

	$sql = insert_into_table ("contests", $contest);
	mysql_query ($sql) or die ("Invalid query: $sql");
}

$sql = "select * from contests order by start_time desc";
$r = mysql_query ($sql) or die ("Invalid query: $sql");

$n = 0;

while ($contests[$n] = mysql_fetch_array ($r)) {
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
		<h2 class="text-center">Contests List</h2>
		<div style="margin-bottom: 1%; margin-top: 20px;">
			<table class="table table-hover">
				<thead>
				<tr>
					<th>比賽標題</th>
					<th>開始時間</th>
					<th>結束時間</th>
					<th>有效</th>
					<th>即時</th>
					<th>型態</th>
					<th>刪除</th>
					<th>SB3</th>
				</tr>
				</thead>
				<tbody>
				<?php for ($i=0 ;$i<$n ;$i++) { ?>
				<?php $id = $contests[$i]["contest_id"]	?>
				<tr>
					<!-- 比賽標題 -->
					<td><a href="admin_contest_index.php?contest_id=<?=$id?>"><?=$contests[$i]["title"]?></a></td>
					<!-- 開始時間 -->
					<td><?php echo $contests[$i]["start_time"]?></td>
					<!-- 結束時間 -->
					<td><?php echo $contests[$i]["end_time"]?></td>
					<!-- 有效 -->
					<td><?php echo $contests[$i]["effective"]?></td>
					<td>
						<?php
							if ($contests[$i]["virtual"]=="N")
								echo "Y";
							else
								echo "N (".$contests[$i]["duration"].")";
						?>
					</td>
					<td>
						<?php	if ($contests[$i]["personal"]=='Y')
								echo "個人";
							else
								echo "隊伍";
						?>
					</td>
					<td><a href="admin_contest.php?del=<?=$id?>" onClick="return confirm('Delete contest <?=$contests[$i]["title"]?>?');">DEL</a></td>
					<td><a href="admin_sb3.php?contest_id=<?=$id?>" target="_blank">Entry</a></td>
				</tr>
				</tbody>
				<?php } ?>
			</table>
		</div><hr>
		<form role="form" action="admin_contest.php" method="post">
			<div class="text-center">
				<span>現在時間: <?=get_full_date()?></span>
			</div>
			<div class="form-group col-sm-12 col-sm-offset-1">
				<p>比賽標題</p>
				<div class="col-sm-10">
					<input class="form-control" type=text name=title value="Practice <?=get_date()?>"></div>
			</div>
			<div class="form-group col-sm-12 col-sm-offset-1">
				<p>開始時間 yy/mm/dd - hh/mm</p>
				<div class="col-sm-2">
					<input class="form-control" type="text" name="s_year" value="<?=substr(get_date(), 0, 4)?>"></div>
				<div class="col-sm-2">
					<input class="form-control" type="text" name="s_month" value="<?=substr(get_date(), 5, 2)?>"></div>
				<div class="col-sm-2">
					<input class="form-control" type="text" name="s_day" value="<?=substr(get_date(), 8, 2)?>"></div>
				<div class="col-sm-2">
					<input class="form-control" type="text" name="s_hour" value="<?=substr(get_full_date(), 11, 2)?>"></div>
				<div class="col-sm-2">
					<input class="form-control" type="tex"t name="s_min" value="<?=substr(get_full_date(), 14, 2)?>"></div>	
			</div>
			<div class="form-group col-sm-12 col-sm-offset-1">
				<p>結束時間 yy/mm/dd - hh/mm</p>
				<div class="col-sm-2">
					<input class="form-control" type="text" name="e_year" value="<?=substr(get_date(), 0, 4)?>"></div>
				<div class="col-sm-2">
					<input class="form-control" type="text" name="e_month" value="<?=substr(get_date(), 5, 2)?>"></div>
				<div class="col-sm-2">
					<input class="form-control" type="text" name="e_day" value="<?=substr(get_date(), 8, 2)?>"></div>
				<div class="col-sm-2">
					<input class="form-control" type="text" name=e_hour value="<?=substr(get_full_date(), 11, 2)+5?>"></div>
				<div class="col-sm-2">
					<input class="form-control" type="text" name=e_min value="<?=substr(get_full_date(), 14, 2)?>"></div>	
			</div>
			<div class="form-group col-sm-12 col-sm-offset-1">
				<p>時間長度</p>
				<div class="col-sm-10"><input class="form-control" type="text" name="duration" value=5></div>
			</div>
			<div class="form-group col-sm-12 col-sm-offset-1">
				<div class="col-sm-6">
					<p>比賽類型</p>
					<div class="col-sm-5"><input type="radio" name="virtual" value='N' checked>&nbsp;&nbsp;即時</div>
					<div class="col-sm-5"><input type="radio" name="virtual" value='Y'>&nbsp;&nbsp;非即時(作業)</div>	
				</div>
				<div class="col-sm-6">
					<p>隊伍型態</p>
					<div class="col-sm-5"><input type="radio" name="personal" value='Y'>&nbsp;&nbsp;個人</div>
					<div class="col-sm-5"><input type="radio" name="personal" value='N' checked>&nbsp;&nbsp;隊伍</div>	
				</div>				
			</div>

			<center><button type="submit" class="btn btn-default" name=submit value=新增比賽>新增比賽</button></center>
		</form>
	</div>
	<!-- footer -->
	<?php require ("../footer.php") ?>
</body>

</html>
