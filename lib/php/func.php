<?php

require_once ("dblink.php");

function fetch_all ($sql)
{
	$r = mysql_query ($sql) or die ("Invalid query: $sql");
	$n = 0;

	while ($ary = mysql_fetch_array ($r))
	{
		$ans[$n++] = $ary;
	}

	return $ans;
}

function count_num ($sql)
{
	$r = mysql_query ($sql) or die ("Invalid query: $sql");
	return mysql_num_rows ($r);
}


function get_date_after ($base, $hour)
{
	$today = getdate(strtotime ($base)+$hour*3600);
	$year = $today["year"];
	$month = $today["mon"];
	$day = $today["mday"];
	$hour = $today["hours"];
	$min = $today["minutes"];
	$sec = $today["seconds"];

	if ($hour<10)
		$hour = "0".$hour;
	if ($min<10)
		$min = "0".$min;
	if ($sec<10)
		$sec = "0".$sec;
	if ($month <10)
		$month = "0".$month;
	if ($day < 10)
		$day = "0".$day;

    return $year."-".$month."-".$day." ".$hour.":".$min.":".$sec;
}

function get_date ()
{
    $today = getdate();
    $year = $today["year"];
    $month = $today["mon"];
    $day = $today["mday"];
    $hour = $today["hours"];
    $min = $today["minutes"];
    $sec = $today["seconds"];

    if ($hour<10)
        $hour = "0".$hour;
    if ($min<10)
        $min = "0".$min;
    if ($sec<10)
        $sec = "0".$sec;
    if ($month <10)
        $month = "0".$month;
    if ($day < 10)
        $day = "0".$day;

    return $year."-".$month."-".$day;
}

function get_full_date ()
{
    $today = getdate();
    $year = $today["year"];
    $month = $today["mon"];
    $day = $today["mday"];
    $hour = $today["hours"];
    $min = $today["minutes"];
    $sec = $today["seconds"];

    if ($hour<10)
        $hour = "0".$hour;
    if ($min<10)
        $min = "0".$min;
    if ($sec<10)
        $sec = "0".$sec;
    if ($month <10)
        $month = "0".$month;
    if ($day < 10)
        $day = "0".$day;

    return $year."-".$month."-".$day." ".$hour.":".$min.":".$sec;
}

function quote_smart($value)
{
    // Stripslashes
    if (get_magic_quotes_gpc()) {
        $value = stripslashes($value);
    }
    // Quote if not integer
//    if (!is_numeric($value)) {
        $value = "'" . mysql_real_escape_string($value) . "'";
//    }
    return $value;
}

function insert_into_table ($table, $data)
{
	$sql = "insert into $table (";
	$first = 1;
	foreach ($data as $key=>$value)
	{
		if ( !$first ) { $sql .= ", "; }
		$first = 0;
		$sql .= "`$key`";
	}
	$sql .= ") values (";
	
	$first = 1;
	foreach ($data as $key=>$value)
	{
		if ( !$first ) { $sql .= ", "; }
		$first = 0;
		$sql .= quote_smart ($value);
	}
	$sql .= ")";
	
	return $sql;
}

function update_table ($table, $data, $condition)
{
	$sql = "update $table set ";
	$first = 1;
	foreach ($data as $key=>$value)
	{
		if (!$first) { $sql .= ", "; }
		$first = 0;
		$sql .= "`$key`=".quote_smart ($value);
	}
	$sql .= " where $condition";
	
	return $sql;
}

function gen_radio_html ($name, $value, $currvalue, $text)
{
	$ans = "<input type='radio' name='$name' value='$value'";
	if ($currvalue==$value)
		$ans .= " checked";
	$ans .= ">$text";
	return $ans;
}

function gen_option_html ($value, $currvalue, $text)
{
	if ($value==$currvalue)
		$selected = " selected";
	$ans = "<option value=\"$value\"$selected>$text</option>";
	return $ans;
}

function get_contest_name ($id)
{
	if ($id==0)
		return "";
	$sql = "select title from contests where contest_id=$id";
	$r = mysql_query ($sql) or die ("Invalid query: $sql");
	list ($name) = mysql_fetch_row ($r);
	return $name;
}


function get_username ($id)
{
	$sql = "select username from users where user_id=$id";
	$r = mysql_query ($sql) or die ("Invalid query: $sql");
	list ($name) = mysql_fetch_row ($r);
	return $name;
}

function get_name ($id)
{
	$sql = "select name from users where user_id=$id";
	$r = mysql_query ($sql) or die ("Invalid query: $sql");
	list ($name) = mysql_fetch_row ($r);
	return $name;
}

function get_team_name ($id)
{
	$sql = "select teamname from users where user_id=$id";
	$r = mysql_query ($sql) or die ("Invalid query: $sql");
	list ($name) = mysql_fetch_row ($r);
	return $name;
}
function get_problem_name ($id)
{
	$sql = "select title from problems where problem_id=$id";
	$r = mysql_query ($sql) or die ("Invalid query: $sql");
	list ($name) = mysql_fetch_row ($r);
	return $name;
}
function get_problem_execute_type ($id)
{
	$sql = "select execute_type from problems where problem_id=$id";
	$r = mysql_query ($sql) or die ("Invalid query: $sql");
	list ($name) = mysql_fetch_row ($r);
	return $name;
}
function get_problem_judge_type ($id)
{
	$sql = "select judge_type from problems where problem_id=$id";
	$r = mysql_query ($sql) or die ("Invalid query: $sql");
	list ($name) = mysql_fetch_row ($r);
	return $name;
}

function log_action ($action)
{
	$data["user_id"] = $_SESSION["userid"];
	$data["username"] = $_SESSION["username"];
	$data["time"] = get_full_date ();
	$data["action"] = $action;
	$data["IP"] = $_SERVER["REMOTE_ADDR"];

	$sql = insert_into_table ("log", $data);
	mysql_query ($sql) or die ("Invalid query: $sql");
}

function get_start_by_user ($user_id, $contest_id)
{
	$sql = "select start_time from virtual_contest_record where user_id=$user_id and contest_id=$contest_id";
	$r = mysql_query ($sql) or die ("Invalid query: $sql");

	if (mysql_num_rows ($r)==0)
		return "";
	else
	{
		list ($start_time) = mysql_fetch_row ($r);
		return $start_time;
	}
}

function get_start_by_team ($teamname, $contest_id)
{
	$teamname = quote_smart ($teamname);
	$sql = "select start_time from virtual_contest_record where teamname=$teamname and contest_id=$contest_id";
	$r = mysql_query ($sql) or die ("Invalid query: $sql");

	if (mysql_num_rows ($r)==0)
		return "";
	else
	{
		list ($start_time) = mysql_fetch_row ($r);
		return $start_time;
	}
}

function record_start_by_user ($user_id, $contest_id)
{
	if (get_start_by_user ($user_id, $contest_id)!="")
		return;

	log_action ("start virtual contest ".$_SESSION["contest"]);

	$data["user_id"] = $user_id;
	$data["contest_id"] = $contest_id;
	$data["start_time"] = get_full_date ();
	$sql = insert_into_table ("virtual_contest_record", $data);
	//$sql = "insert into virtual_contest_record (user_id, contest_id, start_time) values ($user_id, $contest_id, '$now')";
	mysql_query ($sql) or die ("Invalid query: $sql");
}

function record_start_by_team ($teamname, $contest_id)
{
	if (get_start_by_team ($teamname, $contest_id)!="")
		return;

	log_action ("start virtual contest".$_SESSION["contest"]);

	$data["teamname"] = $teamname;
	$data["contest_id"] = $contest_id;
	$data["start_time"] = get_full_date ();
	$sql = insert_into_table ("virtual_contest_record", $data);
//	$sql = "insert into virtual_contest_record (teamname, contest_id, start_time) values ('$teamname', $contest_id, '$now')";
	mysql_query ($sql) or die ("Invalid query: $sql");
}

?>
