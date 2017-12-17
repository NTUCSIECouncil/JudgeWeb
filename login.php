<?php
	session_start ();
	require_once ("lib/php/dblink.php");
	require_once ("lib/php/func.php");

	$msg = "";

	if ($_POST["submit"]=="LOGIN")
	{
		$username = $_POST["username"];
		$password = $_POST["password"];

		$sql = "select * from users where username='$username'";
		$r = mysql_query ($sql) or die ("Invalid query: $sql");

		if ($_POST["username"] == "" || $_POST["password"] == "") {
			$msg = "請輸入使用者名稱或密碼";
		} else {
			$ary = mysql_fetch_array ($r);

			if (!$ary || md5 ($password)!=$ary["password"]) {
				$msg = "無此使用者或密碼錯誤";
			} else if ($ary["group"]=="guest") {
				$msg = "帳號尚未通過審核";
			} else {
				$_SESSION["userid"] = $ary["user_id"];
				$_SESSION["username"] = $ary["username"];
				$_SESSION["group"] = $ary["group"];

				log_action ("login");

				header ("Location: index.php");
			}
		}
	}
	else
		header ("Location: index.php");
?>

<html>

<head>

<?php include_once ("html_header.php"); ?>

</head>

<body>
	<!-- mean -->
	<?php require ("menu.php") ?>
    <!-- page -->
    <div class="container jumbotron text-center" style="padding: 2%;"> 
		<h2><?php echo $msg?><h2>
		<button type="button" class="btn btn-default btn-lg" onclick="javascript: history.go(-1);">離開</button>	
    </div>
   	<!-- footer -->
	<?php require ("footer.php") ?>
</body>

</html>
