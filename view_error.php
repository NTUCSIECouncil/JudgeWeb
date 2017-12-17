<?php
session_start ();
require_once ("lib/php/dblink.php");
require_once ("lib/php/func.php");
require_once ("lib/php/security.php");

$id = check_numeric($_GET["id"]);

$sql = "select error_msg from submission where submission_id=$id";
$r = mysql_query ($sql) or die ("Invalid query: $sql");
list ($error_msg) = mysql_fetch_row ($r);

header ("Content-type: text/plain; charset=utf-8");
echo $error_msg;

?>
