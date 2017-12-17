<?php
session_start ();

require_once ("../lib/php/dblink.php");
require_once ("../lib/php/func.php");
require_once ("../lib/php/security.php");

$result_penalty["Compile Error"] = 0;
$result_penalty["Runtime Error"] = 20;
$result_penalty["Time Limit Exceeded"] = 20;
$result_penalty["Memory Limit Exceeded"] = 20;
$result_penalty["Output Limit Exceeded"] = 20;
$result_penalty["Wrong Answer"] = 20;

function Errortoheader($to) {
	header("Location: ".$to);
	exit;
}

$contest_id = mysql_real_escape_string(check_numeric($_POST["contest_id"]));
$sql = "select * from contests where contest_id=$contest_id";
$r = mysql_query ($sql) or die ("Invalid query");

if(!mysql_num_rows($r)) {
	Errortoheader("../index.php");
}

$contest = mysql_fetch_array ($r);

if (get_full_date() < $contest["start_time"]) {
	Errortoheader("../index.php");
}

$timestamp = mysql_real_escape_string($_POST["time"]);
$endtimestamp = mysql_real_escape_string($_POST["endtime"]);
$onlynext = mysql_real_escape_string($_POST["next"]);

//xml starts here
header("Content-Type: text/xml");
echo "<?phpxml version=\"1.0\"?>\n";
echo "<Response>\n";


$sql = "select * from problems where contest_id=$contest_id order by problem_id asc";
$r = mysql_query ($sql);
$prob_n = 0; $okprob_n = 0;

while ($prob[$prob_n] = mysql_fetch_array ($r)) {
	$hash[$prob[$prob_n]["problem_id"]] = $prob[$prob_n]["effective"];
	$prob[$prob_n]["short"] = sprintf ("%c", 65+$prob_n);
	$prob_n++;
	if ($prob[$prob_n]["effective"] == "Y") {
		++$okprob_n;
	}
}

// TODO: virtual contest
if ($contest["virtual"] == "N") {
	$sql = "SELECT * FROM users";
	$r = mysql_query($sql);
	$user_n = 0;
	while ($user[$user_n] = mysql_fetch_array($r)) {
		$userid2team[ $user[$user_n]["user_id"] ] = $user[$user_n]["teamname"];
		$user_n++;
}

$start = date("Y-m-d H:i:s", $timestamp);
$end = $contest["end_time"];
if (get_full_date() < $contest["end_time"] && $_SESSION["group"]!="admin") {
	$end = get_date_after ($contest["end_time"], -1);
}
if ($endtimestamp != -1) {
	$backend = date("Y-m-d H:i:s", $endtimestamp);
} else {
	$backend = $end;
}
if ($backend < $end) {
	$end = $backend;
}

if ($onlynext != 0) {
	$sql = "SELECT submission_id, submit_time FROM submission WHERE contest_id=$contest_id AND submit_time>='$start'
	   	AND result<>'waiting' AND result<>'Running'
	   	AND submit_time<'$end' ORDER BY submit_time ASC LIMIT 1";
	$r = mysql_query($sql);
	$t = mysql_fetch_array($r);
	if ($t) {
		$end = date("Y-m-d H:i:s", (strtotime($t['submit_time'])+1));
	} else {
		echo "<skip>1</skip>\n";
		$nowtime = date("Y-m-d H:i:s", time()+1);
		if($nowtime > $end) $nowtime = $end;
		echo "<time>".$nowtime."</time>";
		echo "</Response>";
		exit(0);
	}
}
	//echo "<skip2>$timestamp, $start, $end, ".$t['submit_time']."</skip2>";

//check skip
$skip = 0;
$sql = "SELECT COUNT(*) FROM submission WHERE
		contest_id=".$contest_id." 
	   	AND result<>'waiting' AND result<>'Running'
		AND submit_time>='$start' AND submit_time<'$end'";
$r = mysql_query ($sql);
$t = mysql_fetch_row($r);
if (0 && $t[0] == 0) {
	$skip = 1;
	echo "<skip>1</skip>";
	$nowtime = date("Y-m-d H:i:s", time()+1);
	if($nowtime > $end) $nowtime = $end;
	echo "<time>".$nowtime."</time>";
	echo "</Response>\n";
	exit(0);
}

//find last submission time
$sql = "SELECT submit_time FROM submission WHERE contest_id=$contest_id 
	   	AND result<>'waiting' AND result<>'Running'
		AND submit_time<'$end'
		ORDER BY submit_time DESC LIMIT 1";
$r = mysql_query ($sql);
if ($r) {
	$t = mysql_fetch_row($r);
	$lastsubmittime = strtotime($t[0]);
	$myminute = ($lastsubmittime - strtotime($contest['start_time'])) / 60;
	echo "<last>$lastsubmittime</last>";
	echo "<myminute>$myminute</myminute>";
} else {
	echo "<last>-1</last>";
	echo "<myminute>0</myminute>";
}


$sql = "SELECT submission.user_id, problem_id, submit_time, result FROM submission
		JOIN users ON submission.user_id = users.user_id
		WHERE
		contest_id=".$contest_id." 
	   	AND result<>'waiting' AND result<>'Running'
		AND submit_time<'$end'
		AND users.group <> 'admin'
		ORDER BY submit_time ASC";
$r = mysql_query ($sql);

while ($submit = mysql_fetch_array($r)) {
	//the problem is not available
	if ($hash[$submit["problem_id"]]=="N")
		continue;

	//decide indexing
	if ($contest["personal"] == "N") {
		$index = $userid2team[ $submit["user_id"] ];
	} else {
		$index = $submit["user_id"];
	}
	if (!$appear[$index]) {
		$appear[$index] = 1;
		$rec[$index]["solved"] = 0;
		$rec[$index]["penalty"] = 0;
	}

	//if already AC, skip
	if ($rec[$index][$submit["problem_id"]]["AC"])
		continue;
	//if not judged yet, skip
	if ($submit["result"] == "waiting" || $submit["result"] == "Running") {
		continue;
	} else if ($submit["result"]=="Accepted") {
		$rec[$index][$submit["problem_id"]]["AC"]=1;
		$rec[$index]["solved"] += 1;
		$rec[$index]["penalty"] += $rec[$index][$submit["problem_id"]]["penalty"];
		
		if ($contest["virtual"]=="Y") {
			if ($contest["personal"]=="Y")
				$base = get_start_by_user ($index, $contest_id);
			else
				$base = get_start_by_team ($index, $contest_id);
		} else {
			$base = $contest["start_time"];
		}
		$rec[$index][$submit["problem_id"]]["time"] = floor ((strtotime ($submit["submit_time"]) - strtotime ($base))/60);
		$rec[$index]["penalty"] += $rec[$index][$submit["problem_id"]]["time"];
	} else if ( $submit["result"]=="Compile Error" ||
				$submit["result"]=="Runtime Error" ||
				$submit["result"]=="Time Limit Exceeded" ||
				$submit["result"]=="Memory Limit Exceeded" ||
				$submit["result"]=="Output Limit Exceeded" ||
				$submit["result"]=="Wrong Answer" )
	{
		$rec[$index][$submit["problem_id"]]["penalty"] += $result_penalty[ $submit["result"] ];
		if ( $result_penalty[ $submit["result"] ] ) {
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


} else {
	echo "service unavailable for virtual contest!";
	exit(0);
}
foreach($data as $key=>$value) {
	$teamid[$value["index"]] = sprintf("%08x", crc32($value["index"]));
}

array_multisort ($solved, SORT_DESC, $penalty, SORT_ASC, $name, SORT_ASC, $data);
echo "<numprob>$okprob_n</numprob>";
echo "<time>".time()."</time>";

$i = 0;
foreach($data as $key=>$value) {
	if (!$appear [$value["index"]])
		continue;
	++$i;
	echo "<team>";
	echo "\t<id>team".$teamid[$value["index"]]."</id>\n";
	echo "\t<name>".htmlspecialchars($value["index"])."</name>\n";
	echo "\t<rank>$i</rank>\n";
	echo "\t<solved>".$value["solved"]."</solved>\n";
	echo "\t<penalty>".$value["penalty"]."</penalty>\n";
	for ($j = 0; $j < $prob_n; ++$j) {
		if ($prob[$j]["effective"]=="N") continue;
		$att = $rec[$value["index"]][$prob[$j]["problem_id"]]["numof_times"]/1;
		if ($rec[$value["index"]][$prob[$j]["problem_id"]]["AC"]){
			$pty = $rec[$value["index"]][$prob[$j]["problem_id"]]["time"];
		} else {
			$pty = "--";
		}
		echo "\t\t<p$j><att>$att</att><pty>$pty</pty></p$j>\n";
	}
	echo "</team>\n\n";
}
echo "</Response>";
?>

