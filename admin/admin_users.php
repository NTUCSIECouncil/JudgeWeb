<?php
session_start ();
if ($_SESSION["group"]!="admin") {
	header ("Location: ../index.php");
	exit (0);
}

require_once ("../lib/php/func.php");
require_once ("../lib/php/dblink.php");

if ($_GET["ajax"]=="yes") {
	if (isset($_GET["selected"])) {
		$uid = $_GET["user_id"];
		$select = $_GET["selected"];
		$sql = "UPDATE users SET selected=$select WHERE user_id=$uid";
		mysql_query ($sql) or die ("Invalid query: $sql");
		exit;
	}
}

if ($_GET["orderby"])
	$order = "order by `".$_GET["gorderby"]."` asc";
else
	$order = "order by `group`,`username` asc";

if ($_GET["del"])
{
	$id = $_GET["del"];
	$sql = "delete from users where user_id=$id";
	mysql_query ($sql) or die ("Invalid query: $sql");
}

if ($_GET["hide"])
{
	$id = $_GET["hide"];
	$sql = "update users set hide='Y' where user_id=$id";
	mysql_query ($sql) or die ("Invalid query: $sql");
}

if ($_GET["reset"])
{
	$id = $_GET["reset"];
	$sql = "update users set password='".md5 ("1234")."' where user_id=$id";
	mysql_query ($sql) or die ("Invalid query: $sql");
}

if ($_POST["submit"]=="save")
{
	foreach ($_POST["group"] as $user_id=>$group)
	{
		$sql = "update users set `group`='$group' where user_id=$user_id";
		mysql_query ($sql) or die ("Invalid query: $sql");
	}
}

if ($_GET["mode"]=="all")
	$cond = "1";
else
	$cond = "hide='N'";

if ($_GET["mode"]=="selected")
	$cond .= " AND ( selected=1 OR `group`='admin' )";

$sql = "select * from users where $cond $order";
$r = mysql_query ($sql) or die ("Invalid query: $sql");

$n = 0;
$usr_string = "";
while ($users[$n] = mysql_fetch_array ($r)) {
	if ($users[$n]["group"]=="user" || $users[$n]["group"]=="admin")
		$usr_string .= ", ".$users[$n]["email"]."<br>";
		//$usr_string .= ", \"".$users[$n]["name"]."\" &lt;".$users[$n]["email"]."&gt;";
	$n++;
}

?>
<html>

<head>

<?php include_once ("admin_html_header.php"); ?>
<script>
	function sent_select (obj, id) {
		if (obj.checked)
			value = 1;
		else
			value = 0;

		jQuery.ajax ({
			url: "admin_users.php",
			data: {ajax: 'yes', user_id: id, selected: value},
			dataType: 'text',
			beforeSend: function (){
				//jQuery('#'+did).fadeIn();
			},
			complete: function (){
				//jQuery('#'+did).fadeOut();
				jQuery('#row_'+id).hide();
				jQuery('#row_'+id).fadeIn();
			},
			success: function(response){
			}
		});
	}
</script>
</head>

<body>
	<!-- mean -->
	<?php require ("menu.php") ?>
	<!-- page -->
	<div class="container jumbotron" style="padding-top: 3%;">
		<h2 class="text-center">Users List</h2><hr>
		<div>
			<p class="text-center">
				<< &nbsp; <a href="admin_users.php?mode=all">All</a>&nbsp; || &nbsp;
				<a href=admin_users.php?mode=selected>Selected</a> &nbsp; >>
			</p>
			<form action=admin_users.php method=post>
				<table class="table table-hover">
					<tr>
						<th>帳號</th>
						<th>隊名</th>
						<th>權限</th>
						<th>Action</th>
					</tr>

					<?php $last=0; $color='#FFFFFF'; ?>
					<?php for ($i=0 ;$i<$n ;$i++)	{	?>
					<?php 	$id = $users[$i]["user_id"]; ?>
					<tr>
						<td><a href=admin_user_edit.php?user_id=<?=$id?>><?=$users[$i]["username"]?></a></td>
						<td><?=$users[$i]["teamname"]?></td>
						<td>
							<select name="group[<?=$id?>]">
							<?=gen_option_html ("guest", $users[$i]["group"], "guest")?>
							<?=gen_option_html ("user", $users[$i]["group"], "user")?>
							<?=gen_option_html ("admin", $users[$i]["group"], "admin")?>
							</select>
						</td>
						<td>
							<a href="admin_users.php?del=<?=$id?>" onClick="return confirm('delete user <?=$users[$i]["username"]?>?');">Del</a> 
							<font color=red>|</font> 
							<a href="admin_users.php?hide=<?=$id?>" onClick="return confirm('hide user <?=$users[$i]["username"]?>?');">Hide</a>
							<font color=red>|</font> 
							<a href="admin_users.php?reset=<?=$id?>" onClick="return confirm('reset user <?=$users[$i]["username"]?> password to 1234?');">Reset</a>
							<font color=red>|</font> 
							<input type="checkbox" name="selected[<?=$id?>]" <?php if ($users[$i]['selected']) echo "checked";?> onClick="sent_select(this, <?=$id?>);">
						</td>
					</tr>
					<?php } ?>
				</table>
				<center>
					<input type=submit name=submit value=save>				
				</center>
			</form>				
		</div>
	</div>
	<!-- footer -->
	<?php require ("../footer.php") ?>s
</body>

</html>
