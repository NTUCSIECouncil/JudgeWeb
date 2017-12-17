<?php
session_start ();

require_once ("lib/php/dblink.php");
require_once ("lib/php/func.php");
require_once ("lib/php/security.php");

$msg = "";

if ($_GET["contest_id"]) {
	$contest_id = check_numeric($_GET["contest_id"]);
}

if ($_POST["submit"]=="submit" and $_POST["problem_id"]) {
	// set problem_id
	$problem_id = $_POST["problem_id"];
	if (Ord($problem_id)>=65 && Ord($problem_id)<=Ord('Z') && $contest_id) {
		$offset = Ord($problem_id) - 65;

		$sql = "select problem_id from problems where contest_id=$contest_id order by problem_id asc";
		$r = mysql_query ($sql) or die ("Invalid query: $sql");

		$i = 0;
		while ($prob = mysql_fetch_array ($r)) {
			if ($i==$offset) {
				$problem_id = $prob["problem_id"];
				break;
			}
			$i++;
		}
	}
	$submit["problem_id"] = $problem_id;

	$submit["user_id"] = $_SESSION["userid"];	
	$submit["time"] = "0.00";
	$submit["memory"] = "0";
	$submit["submit_time"] = get_full_date ();
	$submit["result"] = "waiting";
	$submit["language"] = $_POST["language"];
	$submit["IP"] = $_SERVER["REMOTE_ADDR"];

	$submit["error_msg"] = "";

	$sql = "select contest_id from problems where effective='Y' and problem_id='$problem_id'";
	$r = mysql_query ($sql) or die ("Invalid query: $sql");
	if ( mysql_num_rows($r) == 0 ) {
		$msg = "找不到此問題。";
	} else {
		$problem = mysql_fetch_array ($r);
		$contest_id = $problem["contest_id"];

		if ($contest_id > 0) {
			$sql = "select start_time from contests where contest_id=$contest_id";
			$r = mysql_query ($sql) or die ("Invalid query: $sql");
			list ($start_time) = mysql_fetch_row ($r);

			if (get_full_date()<$start_time)
				$msg = "找不到此問題。";
		}

		if ($msg=="") {
			if ( $submit["language"]=="" ) {
				$msg = "請選擇程式語言。";
			}
			else if ( $_POST["code"]=="" ) {
				$msg = "請輸入程式碼。";
			}
		}

		if ( $msg=="" ) {
			$sql = "select contest_id from problems where effective='Y' and problem_id='$problem_id'";
			$r = mysql_query ($sql) or die ("Invalid query: $sql");
			list ($submit["contest_id"]) = mysql_fetch_row ($r);

			$contest_id = $submit["contest_id"];

			if ( $_FILES["code_file"]["size"] == 0 ) {
				$submit["code"] = $_POST["code"];
			}
			// else {
			// 	$fd = fopen($_FILES["code_file"]["tmp_name"], "r") or die ("Can not open tmp file");

			// 	$submit["code"] = join ('', file ($_FILES["code_file"]["tmp_name"]));
			// 	fclose ($fd);
			// }

			$sql = insert_into_table ("submission", $submit);
			mysql_query ($sql) or die ("Invalid query: $sql");
			$msg = "傳送成功！";

			if ($contest_id) {
				$sql = "select * from contests where contest_id=$contest_id";
				$r = mysql_query ($sql) or die ("Invalid query: $sql");
				$contest = mysql_fetch_array ($r);

				if (get_full_date ()>$contest["end_time"])
					header ("Location:status.php?user_id=".$_SESSION["userid"]);
				else
					header ("Location:status.php?user_id=".$_SESISON["userid"]."&contest_id=$contest_id");
			} else
				header ("Location:status.php?user_id=".$_SESSION["userid"]);
		}
	}
}

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
    <div class="container jumbotron" style="padding: 3%;">
    	<h2 class="text-center">Submit Code</h2><hr>
		<?php	if ($_GET["contest_id"]) {	?>
			<form action=submit.php?contest_id=<?=$_GET["contest_id"]?> method="post" enctype="multipart/form-data">
		<?php	} else {	?>
			<form action="submit.php" method="post" enctype="multipart/form-data">
		<?php }	?>
		<h3 class="text-center" style="color: red;"><?=$msg?></h3>
				<div class="form-group">
					<label for="problemid" style="font-size: 1.4em;">Problem ID</label>
					<input type="text"  name="problem_id" class="form-control" id="problemid" value=<?=$_GET["id"]?> >
				</div>
			  	<div class="form-group">
			  		<label for="language" style="font-size: 1.4em;">Language</label>
					<label class="radio-inline" style="text-indent: 1em;">
						<input type="radio" name="language" id="language" value="C"> C
					</label>
					<label class="radio-inline" style="text-indent: 1em;">
						<input type="radio" name="language" id="language" value="C++"> C++
					</label>
			<!-- 		
					<label class="radio-inline">
						<input type="radio" name="language" id="inlineRadio2" value="Java"> Java
					</label>
			 -->
				</div>
			<!-- 
				<div class="form-group">
					<label for="submitbyfile">上傳檔案</label>
					<input type="file" name="code_file" id="submitbyfile">
				</div>
			 -->
				<div class="checkbox">
					<textarea style="resize: none;" class="form-control" name="code" rows="30" placeholder="Paste your Code here"></textarea>
				</div>
				<div class="text-center">
					<?php
						if ($_SESSION["group"] == "admin") {
							echo "<font color=red>Note: You are admin right now.</font>";
					} ?>
				</div>
				<div class="text-center" style="margin-top: 2%;">
					<button type="submit" class="btn btn-default" name="submit" value="submit">Submit</button>
				</div>
			</form>
			<div style="margin-left: 5%; margin-top: 2%;">
				<ul>
					<li>不要短時間連續餵我吃太多 code 噢，我會吃不下，送很多 CE 的</li>
					<li>不要玩弄我，否則詛咒你一輩子被我所有哥哥姐姐們送 WA！</li>
				</ul>
			</div>
    </div>
   	<!-- footer -->
	<?php require ("footer.php") ?>
</body>

</html>
