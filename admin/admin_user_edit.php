<?php
session_start ();
if ($_SESSION["group"]!="admin") {
	header ("Location: ../index.php");
	exit (0);
}
require_once ("../lib/php/dblink.php");
require_once ("../lib/php/func.php");

$user_id = $_GET["user_id"];

if ($_POST["submit"]=="save")
{
	$data["teamname"] = $_POST["teamname"];
	$data["department"] = $_POST["department"];
	$data["ID"] = $_POST["ID"];
	$sql = update_table ("users", $data, "user_id=$user_id");
	mysql_query ($sql) or die ("Invalid query: $sql");

	header ("Location: admin_users.php");
}


$sql = "select * from users where user_id=$user_id";
$r = mysql_query ($sql) or die ("Invalid query: $sql");
$user = mysql_fetch_array ($r);
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
		<h2 class="text-center">Edit User</h2><hr>
		<form action=admin_user_edit.php?user_id=<?=$user_id?> method=post>
			<div class="form-group">
				<div class="col-sm-6" style="padding-left: 0; margin-bottom: 15px;">
					<label for="username">帳號</label>
					<input type="text" name="username" class="form-control" id="username" value="<?=$user["username"]?>" disabled>
				</div>
				<div class="col-sm-6" style="padding-right: 0; margin-bottom: 15px;">
					<label for="teamname">隊名</label>
					<input type="text" name="teamname" class="form-control" id="teamname" value="<?=$user["teamname"]?>">
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-6" style="padding-left: 0; margin-bottom: 15px;">
					<label for="name">姓名</label>
					<input type="text" name="name" class="form-control" id="name" value="<?=$user["name"]?>" disabled>
				</div>
				<div class="col-sm-6" style="padding-right: 0; margin-bottom: 15px;">
					<label for="ptt2_ID">PTT2 ID</label>
					<input type="text" name="ptt2_ID" class="form-control" id="ptt2_ID" value="<?=$user["ptt2_ID"]?>" disabled>
				</div>
			</div>
			<div class="form-group">
				<label for="email">E-Mail</label>
				<input type="text" name="email" class="form-control" id="email" value="<?=$user["email"]?>" disabled>
			</div>
			<div class="form-group">
				<div class="col-sm-6" style="padding-left: 0; margin-bottom: 15px;">
					<label for="department">系級</label>
					<input type="text" name="department" class="form-control" id="department" value="<?=$user["department"]?>" disabled>
				</div>
				<div class="col-sm-6" style="padding-right: 0; margin-bottom: 15px;">
					<label for="ID">學號</label>
					<input type="text" name="ID" class="form-control" id="ID" value="<?=$user["name"]?>" disabled>
				</div>
			</div>
			<div class="form-group">
				<label for="cellphone">手機</label>
				<input type="text" name="cellphone" class="form-control" id="cellphone" value="<?=$user["cellphone"]?>" disabled>
			</div>
			<div class="form-group">
				<label for="reg_date">註冊日</label>
				<input type="text" name="reg_date" class="form-control" id="reg_date" value="<?=$user["reg_date"]?>" disabled>
			</div>
			<center>
				<button type="submit" class="btn btn-default" name="submit" value="save">送出</button>
			</center>
		</form>
	</div>
	<!-- footer -->
	<?php require ("../footer.php") ?>
</body>

</html>
