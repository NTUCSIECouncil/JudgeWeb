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

$result_penalty = [
    "Compile Error" 		=> 0,
    "Runtime Error" 		=> 20,
    "Time Limit Exceeded" 	=> 20,
    "Memory Limit Exceeded" => 20,
    "Output Limit Exceeded" => 20,
    "Wrong Answer" 			=> 20,
];

$contest_id = mysql_real_escape_string(check_numeric($_POST["contest_id"]));
$sql = "SELECT * FROM `contests`
		WHERE `contest_id`=$contest_id";
$r = mysql_query ($sql) or die ("Invalid query");

if(!mysql_num_rows($r)) {
	Errortoheader("../index.php");
}

$contest = mysql_fetch_array ($r);
if (get_full_date() < $contest["start_time"]) {
	Errortoheader("../index.php");
}

//json starts here
header("Content-Type: application/json; charset=utf-8");


$sql = "SELECT * FROM `problems`
		WHERE `contest_id`=$contest_id
		ORDER BY `problem_id` ASC";
$r = mysql_query ($sql);
$prob_n = 0;
while ($prob[$prob_n] = mysql_fetch_array ($r)) {
	$hash[$prob[$prob_n]["problem_id"]] = $prob[$prob_n]["effective"];
	$prob[$prob_n]["short"] = sprintf ("%c", 65+$prob_n);
	$prob_n++;
}


$sql = "SELECT * FROM `users`
		WHERE EXISTS (
			SELECT * FROM `submission`
			WHERE `users`.`user_id` = `submission`.`user_id`
			AND `submission`.`contest_id`=$contest_id
		)";
$r = mysql_query($sql);
$user_n = 0;
while ($user[$user_n] = mysql_fetch_array($r)) {
	$userid2team[ $user[$user_n]["user_id"] ] = $user[$user_n]["teamname"];
	$user_n++;
}

$timestamp_offset = mysql_real_escape_string($_POST["timestamp_offset"]);
$start = date("Y-m-d H:i:s",strtotime($contest["start_time"]) + $timestamp_offset*60);
$end = $contest["end_time"];
if ( get_full_date() < $contest["end_time"] && $_SESSION["group"]!="admin" ) {
	$end = get_date_after ($contest["end_time"], -1);
}
if ( $start > $end ) { $start = $end; }
$sql = "SELECT `submission`.`user_id`, `problem_id`, `submit_time`, `result` FROM `submission`
		JOIN `users` ON `submission`.`user_id` = `users`.`user_id`
		WHERE `contest_id`=$contest_id
			AND `result`<>'waiting' AND `result`<>'Running'
			AND `submit_time`<='$start'
			AND `users`.`group` <> 'admin'
		ORDER BY `submit_time` ASC";
$r = mysql_query ($sql);
while ($submit = mysql_fetch_array($r)) {
	//the problem is not available
	if ($hash[$submit["problem_id"]]=="N") { continue; }

	//decide indexing
	if ($contest["personal"] == "N") {
		$index = $userid2team[ $submit["user_id"] ];
	} else {
		$index = $submit["user_id"];
	}
	if (!isset($appear[$index])) {
		$appear[$index] = 1;
		$rec[$index]["solved"] = 0;
		$rec[$index]["penalty"] = 0;
	}

	//if already AC, skip
	if ($rec[$index][$submit["problem_id"]]["AC"]) { continue; }

	if ($submit["result"] == "waiting" || $submit["result"] == "Running") {
		continue;
	} else if ($submit["result"]=="Accepted") {
		$rec[$index][$submit["problem_id"]]["AC"] = 1;
		$rec[$index]["solved"] += 1;
		$rec[$index]["penalty"] += $rec[$index][$submit["problem_id"]]["penalty"];

		$base = $contest["start_time"];
		$rec[$index][$submit["problem_id"]]["time"] = floor ((strtotime($submit["submit_time"]) - strtotime($base))/60);
		$rec[$index]["penalty"] += $rec[$index][$submit["problem_id"]]["time"];
	} else if ( $submit["result"]=="Compile Error" ||
				$submit["result"]=="Runtime Error" ||
				$submit["result"]=="Time Limit Exceeded" ||
				$submit["result"]=="Memory Limit Exceeded" ||
				$submit["result"]=="Output Limit Exceeded" ||
				$submit["result"]=="Wrong Answer" )
	{
		if ( $result_penalty[$submit["result"]] ) {
			$rec[$index][$submit["problem_id"]]["penalty"] += $result_penalty[$submit["result"]];
			$rec[$index][$submit["problem_id"]]["numof_times"] += 1;
		}	
	}
}

foreach ($appear as $index=>$value) {
	$data[$index] = array("solved" => $rec[$index]["solved"], "penalty" => $rec[$index]["penalty"], "index" => $index);
	$solved[$index] = $rec[$index]["solved"];
	$penalty[$index] = $rec[$index]["penalty"];
	$name[$index] = $index;
}
array_multisort ($solved, SORT_DESC, $penalty, SORT_ASC, $name, SORT_ASC, $data);

$rank = 0;
foreach($data as $key=>$value) {
	$result[$rank]["team"] = $value["index"];
	$result[$rank]["solved"] = $value["solved"];
	$result[$rank]["penalty"] = $value["penalty"];
	for ($i = 0; $i < $prob_n; $i++) {
		if ($prob[$j]["effective"]=="N") { continue; }
		$att = $rec[$value["index"]][$prob[$i]["problem_id"]]["numof_times"]/1;
		if ($rec[$value["index"]][$prob[$i ]["problem_id"]]["AC"]) {
			$pty = $rec[$value["index"]][$prob[$i ]["problem_id"]]["time"];
		} else {
			$pty = "--";
		}
		$result[$rank][$i] = $att."/".$pty;
	}
	$rank++;
}

echo json_encode($result);