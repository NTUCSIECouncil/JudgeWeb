<?php
	session_start ();
	require_once ("lib/php/dblink.php");
	require_once ("lib/php/func.php");
	require_once ("lib/php/security.php");

	$id = check_numeric($_GET["id"]);

	$sql = "select * from submission where submission_id=$id";
	$r = mysql_query ($sql) or die ("Invalid query: $sql");
	$code = mysql_fetch_array ($r);

	if ($_SESSION["userid"]!=$code["user_id"] and $_SESSION["group"]!="admin") {
		header ("Location: status.php");
		exit (0);
	}

	header ("Content-Type: text/plain; charset=utf-8");
	if($code["language"]=="C") {
		header("Content-disposition: attachment; filename=code.c");
	} else if ($code["language"]=="C++") {
		header("Content-disposition: attachment; filename=code.cpp");
	} else if ($code["language"]=="Java") {
		header("Content-disposition: attachment; filename=code.java");
	}

	echo $code["code"];
?>

