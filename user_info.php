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
	$user_id = check_numeric($_GET["user_id"]);
	$teamname = check_teamname($_GET["teamname"]);

	if( !$user_id && !$teamname ){
		header ("Location: index.php");
		exit (0);
	}

	if ($_POST["submit"]=="更改資料" && $user_id && $user_id==$_SESSION["userid"])
	{
		$sql = "select * from users where user_id=$user_id";
		$r = mysql_query ($sql) or die ("Invalid query: $sql");
		$user = mysql_fetch_array ($r);

		if( !$_POST["old_pwd"] || !$_POST["new_pwd"] || !$_POST["chk_pwd"] ) {
			$msg = "不得缺少任一欄位";
		} else {
			if ( md5($_POST["old_pwd"])!=$user["password"] ) // remove passwd check
				$msg = "舊密碼錯誤";
			else if ($_POST["new_pwd"]!=$_POST["chk_pwd"])
				$msg = "新密碼和確認密碼不相同";
			else if ($_POST["old_pwd"] == $_POST["new_pwd"])
				$msg = "舊密碼和新密碼相同";
			else {
				if ($_POST["new_pwd"]) {
					$password = md5 ($_POST["new_pwd"]);
					$sql = "update users set password='$password' where user_id=$user_id";
					mysql_query ($sql) or die ("Invalid query: $sql");
					$msg = "密碼更新成功";

					log_action ("change password");
				}
			}
		}
	}

	if ($user_id)
		$sql = "select * from users where user_id=$user_id and `group`!='guest'";
	else if ($teamname)
		$sql = "select * from users where teamname='$teamname' and `group`!='guest'";

	$r = mysql_query ($sql) or die ("Invalid query: $sql");
	$n = 0;

	while ($user[$n] = mysql_fetch_array ($r))
	{
		$n++;
	}

	//tmt: if contesting, skip this cell.
		$now_time = get_full_date();
		$sql = "SELECT start_time, end_time FROM contests WHERE start_time<='$now_time' AND
				end_time>='$now_time'";
		$r = mysql_query($sql) or die ("Invalid query: $sql");
		while($contest = mysql_fetch_array($r)){
			$duration = strtotime($contest['end_time']) - strtotime($contest['start_time']);
			if($duration <= 36000){
				$is_contesting = 1;
				break;
			}
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
		<h2 class="text-center">Account Info</h2>
		<?php	for ($i=0 ;$i<$n ;$i++)	{	?>
			<hr>
			<div class="row" style="padding-right: 10%; padding-left: 10%; padding-top: 3%;">
				<div class="col-sm-5">
					<p><b>User</b></p>
					<p style="text-indent: 2em;">
						<a href="user_info.php?user_id=<?=$user[$i]["user_id"]?>"><?=$user[$i]["username"]?></a>
						&nbsp;--&nbsp;
						[<a href="user_info.php?teamname=<?=$user[$i]["teamname"]?>"><?=htmlspecialchars($user[$i]["teamname"])?></a>]
					</p>
					<p><b>Name</b></p>
					<p style="text-indent: 2em;"><?=$user[$i]["name"]?></p>
				</div>
				<div class="col-sm-7">
					<p><b>Solved</b></p>
					<p style="padding-left: 2em;">
						<?php

							if((!isset($_SESSION["userid"]) || get_team_name($_SESSION["userid"])!=$user[$i]['teamname']) && $is_contesting == 1) {
								echo "比賽進行中...";
							} else {
								$sql = "SELECT p.problem_id FROM problems AS p
										LEFT JOIN submission AS s On s.problem_id=p.problem_id
										WHERE s.user_id=".$user[$i]["user_id"]." AND s.result='Accepted' AND p.effective='Y'
										GROUP BY p.problem_id
										ORDER BY p.problem_id ASC";

								$r = mysql_query ($sql) or die ("Invalid query: $sql");

								while (list ($id) = mysql_fetch_row ($r))
									printf ("<font style=\"font-size: 1em;\"><a href='problem.php?id=%d'>%04d</a></font> ", $id, $id);
							}
						?>
					</p>
				</div>
			</div>
		<?php	}	?>
		<?php	if ($user_id && $user_id==$_SESSION["userid"]) { ?>
			<h2 class="text-center">Password</h2>
			<div style="padding-top: 3%;">
				<center>
					<form class="form-inline" action="user_info.php?user_id=<?=$user_id?>" method=post>
						<div class="form-group">
							<label class="sr-only" for="old_pwd">舊密碼</label>
							<input type="password" name="old_pwd" class="form-control" id="old_pwd" placeholder="輸入舊密碼">
						</div>
						<div class="form-group">
							<label class="sr-only" for="new_pwd">新密碼</label>
							<input type="password" name="new_pwd" class="form-control" id="new_pwd" placeholder="輸入新密碼">
						</div>
						<div class="form-group">
							<label class="sr-only" for="chk_pwd">新密碼確認</label>
							<input type="password" name="chk_pwd" class="form-control" id="chk_pwd">
						</div>
						<button type="submit" class="btn btn-default"  name="submit" value="更改資料">更改密碼</button>	
					</form>
					<span><font color=red><?=$msg?></font></span>
				</center>					
			</div>
		<?php	}	?>
	</div>
	<!-- footer -->
	<?php require ("footer.php") ?>
</body>

</html>
