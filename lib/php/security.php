<?php
function check_numeric ($inputstr) {
	if(strpos($inputstr, '&') !== false || strpos($inputstr, '(')!==false || !is_numeric($inputstr)){
		/*header ("Location: http://council.csie.ntu.edu.tw/ttcacm/");
		exit (0);*/
	}
	return intval($inputstr);
}
function check_teamname ($inputstr) {
	if(strpos($inputstr, '&') !== false || strpos($inputstr, '|')!==false) {
		header ("Location: http://council.csie.ntu.edu.tw/ttcacm/");
		exit (0);
	}
	return $inputstr;
}
function check_string ($inputstr) {
	return check_teamname($inputstr);
}
function check_selected ($usruid) {
	$sql = "SELECT selected FROM users where user_id=$usruid";
	$r = mysql_query($sql);
	if ($usrdata = mysql_fetch_array($r)) {
		if ($usrdata["selected"] != 1) {
			header ("Location: http://council.csie.ntu.edu.tw/ttcacm/");
			exit(0);
		}
	} else {
		header ("Location: http://council.csie.ntu.edu.tw/ttcacm/");
		exit(0);
	}
}
?>
