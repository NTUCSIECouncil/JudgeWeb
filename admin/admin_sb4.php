<?php
session_start ();

function Errortoheader($to) {
	header("Location: ".$to);
	exit(0);
}

if ($_SESSION["group"]!="admin") {
	Errortoheader("../index.php");
}

require_once ("../lib/php/dblink.php");
require_once ("../lib/php/func.php");
require_once ("../lib/php/security.php");

$contest_id = mysql_real_escape_string(check_numeric($_GET["contest_id"]));

$sql = "SELECT * FROM `contests`
		WHERE `contest_id`=$contest_id";
$r = mysql_query ($sql) or die ("Invalid query");
if(!mysql_num_rows($r)){
	Errortoheader("../index.php");
}

$contest = mysql_fetch_array ($r);
if (get_full_date() < $contest["start_time"]){
	Errortoheader("../index.php");
}

$sql = "SELECT * FROM `problems`
		WHERE `contest_id`=$contest_id
		ORDER BY `problem_id` ASC";
$r = mysql_query ($sql) or die ("Invalid query");
$prob_n = 0;
while ($prob[$prob_n] = mysql_fetch_array ($r)) {
	$hash[$prob[$prob_n]["problem_id"]] = $prob[$prob_n]["effective"];
	$prob[$prob_n]["short"] = sprintf ("%c", 65+$prob_n);
	$prob_n++;
}

$settimeout_value = 59000;
?>

<!DOCTYPE text/html>
<html>

<head>
<title>NTU Online Judge</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<?php include_once ("admin_html_header.php"); ?>
<script src="../lib/javascript/sb4.js"></script>
</head>

<body>
	<!-- mean -->
	<?php require ("menu.php") ?>
	<!-- page -->
	<div class="container jumbotron" style="padding-top: 3%;">
		<h2 class="text-center">Admin score Board</h2>
		<div style="margin-bottom: 1%; margin-top: 20px;">
			<table class="table">
				<thead>
				<tr>
					<th style="text-align:center">Rank</th>
					<th style="text-align:center">Team</th>
					<th style="text-align:center">Solved</th>
					<th style="text-align:center; border-right: 2px dashed black;">Penalty</th>
					<?php for ($i=0 ;$i<$prob_n ;$i++) { ?>
						<?php if ($prob[$i]["effective"]=="N") { continue; } ?>
						<th style="text-align:center"><?=$prob[$i]["short"]?></th>
					<?php } ?>
				</tr>
				</thead>
				<tbody id="Ranktable">
					
				</tbody>
				<tfoot>
					
				</tfoot>
			</table>
	</div>

</body>
<script>
var prob_n = <?=$prob_n?>;
window.onload = function(){
	var contest_id = <?=$contest_id?>;
	Req_data( contest_id , 300);
}
</script>
</html>
