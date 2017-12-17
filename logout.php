<?php
	session_start ();
	$_SESSION["userid"] = 0;
	$_SESSION["username"] = "";
	$_SESSION["group"] = "";

	header ("Location: index.php ");
?>
