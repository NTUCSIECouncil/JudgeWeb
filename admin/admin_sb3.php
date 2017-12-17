<?php
session_start ();

require_once ("../lib/php/dblink.php");
require_once ("../lib/php/func.php");
require_once ("../lib/php/security.php");


if ($_SESSION["group"]!="admin"){
	header ("Location: ../index.php");
	exit (0);
}

$contest_id = mysql_real_escape_string(check_numeric($_GET["contest_id"]));

$sql = "select * from contests where contest_id=$contest_id";
$r = mysql_query ($sql) or die ("Invalid query");


if(!mysql_num_rows($r)){
   	header("Location: ../index.php");
	exit;
}

$contest = mysql_fetch_array ($r);

if (get_full_date()<$contest["start_time"]){
	header ("Location: ../index.php");
	exit;
}

$sql = "select * from problems where contest_id=$contest_id order by problem_id asc";
$r = mysql_query ($sql);
$prob_n = 0;

while ($prob[$prob_n] = mysql_fetch_array ($r))
{
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
<link href="../lib/css/sb3.css" type="text/css" rel="stylesheet">
<script src="../lib/javascript/jquery.js"></script>
</head>

<body style="overflow:auto; background-color: #4c5870;">
	<center>
	<table id="bigtable" style="width:<?php echo $adjust_width?>; background-color: #eee;">
		<tr>
			<td align="center"><h1 style="color: black; font-size: 3em;">Admin score Board</h1></td>
		</tr>
		<tr>
			<td id=bigtd style="padding: 10px" align=center valign=top>
				<b>
				<?php
					$h=$m=$s=-1;
					$time = strtotime ($contest["end_time"]) - strtotime (get_full_date());
					if ($time <= 0) {
						echo "Final Standing";
					} else {
						$s = $time%60;
						$time = floor ($time/60);
						$m = $time%60;
						$h = floor ($time/60);

						echo "<font id=\"font0\">Count Down: <span id=\"timer0\">".sprintf ("%02d:%02d:%02d", $h, $m, $s)."</span></font>";
						echo "<br>Last Cache Count Down: ".sprintf("%02d:%02d:%02d",$h,$m,$s);
					}
				?>
				</b>

				<?php
					if (get_full_date()>=get_date_after($contest["end_time"], -1) && get_full_date()<$contest["end_time"] && $_SESSION["group"]!="admin"){
						echo "<div><font size=2>Scoreboard is not updating until contest over</font></div>\n";
					}
				?><br/><span class=note id=boardstatus style='float:right'></span>
				<hr>
				<div id=board style='overflow:hidden;width:100%;height:400px'></div>
				<div style='z-index:200'>
					<span id=firstboard style='z-index:200;cursor:pointer;'>
						<img align=middle src='../lib/img/sb_first.png' width=40px alt="[FIRST]"/></span>
					<span id=prevboard style='z-index:200;cursor:pointer;'>
						<img align=middle src='../lib/img/sb_prev.png' width=40px alt="[PREV]"/></span> 
					<span id=nextboard style='z-index:200;cursor:pointer;'>
						<img align=middle src='../lib/img/sb_next.png' width=40px alt="[NEXT]"/></span>
					<span id=stepboard style='z-index:200;cursor:pointer;'>
						<img align=middle src='../lib/img/sb_step.png' width=40px alt="[STEP]"/></span>
					<span id=freezeboard style='z-index:200;cursor:pointer;'>
						<img align=middle src='../lib/img/sb_freeze.png' width=40px alt="[FREEZE]"/></span>
					<span id=latestboard style='z-index:200;cursor:pointer;'>
						<img align=middle src='../lib/img/sb_latest.png' width=40px alt="[LATEST]"/></span>
				</div>
				<div style='background-color: #EEE; width:100%; height:10px; text-align:left'>
					<canvas width="1000px" height="10px" id="canvas" style='width:0%; height:10px;'>XDD</canvas>
					<div id="minute" style="font-weight:bold;position:absolute;"></div>
				</div>
				<div style="height:30px;width:100%"></div>
			</td>
		</tr>
		<tr>
			<td align="center">
				<button onclick="closewin();" style="width: 80%; height: auto; font-size: 2em; margin-bottom: 2%;">Close</button>
			</td>
		</tr>
	</table>
	</center>

</body>

<script type="text/javascript">
	function closewin() {
		window.open('', '_self', ''); window.close();
	}
</script>

<script>
	var context = $('#canvas')[0].getContext('2d');
	var lg = context.createLinearGradient(0,0,0,10);
	lg.addColorStop(0, '#337');
	lg.addColorStop(0.4, '#59F');
	lg.addColorStop(0.6, '#59F');
	lg.addColorStop(1, '#337');
	context.fillStyle = lg;
	context.fillRect(0,0,1000,10);

    h=<?php echo $h?>;
    m=<?php echo $m?>;
    s=<?php echo $s?>;
    if(h == 0) {
        font0 = document.getElementById('font0');
        font0.color = 'red';
    }
	function timer(){
		if(--s<0)s+=60,m--;
		if(m<0)m+=60,h--;
		if(h<0) {timer0.innerHTML = 'Contest Ended';}
		if(timer0!=undefined){
			if(h==0)
			{
				font0 = document.getElementById('font0');
				font0.color = 'red';
			}
			timer0.innerHTML=''+(h<10?'0':'')+h+':'+(m<10?'0':'')+m+':'+(s<10?'0':'')+s;
		}
	}
	if (h >= 0)
		setInterval(timer, 1000);
	timer0 = document.getElementById("timer0");
</script>
<script language='javascript'>
var transforming = 0;
function fadeInTeamName(tmp) {
	if (transforming == 0) {
		transforming = 1;
		jQuery('#'+tmp).fadeIn('fast', function(){transforming = 0;});
	}
}
function fadeOutTeamName(tmp) {
	if (transforming == 0) {
		transforming = 1;
		jQuery('#'+tmp).fadeOut('fast', function(){transforming = 0;});
	}
}

var timestamp;
var lastsubmittime;
var wantendtime;
var onlynext;
var updateTimer;
$(document).ready(
	function() {
		timestamp = 0;
		lastsubmittime = 0;
		wantendtime = 0;
		onlynext = 0;
		$("#boardstatus").css('paddingLeft', '3px');
		updateBoard();
		onlynext = 1;
	}
);

var isfreezing = 0;
var nextupdatetime = 5000;
function updateBoard() {
	$("#boardstatus").html('Updating...').fadeIn('fast');
	$.ajax(
			{ url: "admin_sb3_ajax.php"
			, type: "POST"
			, data: {"next": onlynext, "time": parseInt(lastsubmittime)+1, "endtime": wantendtime, "contest_id": '<?php echo $contest_id?>' }
			, success: function(xml, status, req) {
				$("#boardstatus").fadeOut('slow');
				updateXML(xml, status, req);
				if(isfreezing == 0) {
					clearTimeout(updateTimer);
					updateTimer = setTimeout("updateBoard()", nextupdatetime);
				}
			}
			, dataType: "xml"
			}
	);
}

var HEADER_HEIGHT = 32;
var ROW_HEIGHT = 24;
var totalteams = 0;
function updateXML(xml, status, req) {
	//skip
	timestamp = $("time", xml).text();
	skip = $("skip", xml).text();
	if (skip == 1) {
		nextupdatetime = 5000;
		return;
	}
	//refresh
	needrefresh = $("refresh", xml).text();
	if (needrefresh == 1) {
		location.reload();
		return;
	}

	var myboard = $("#board");
	lastsubmittime = $("last", xml).text();
	myminute = $("myminute", xml).text();
	$('#canvas').css('width',myminute/300*100+'%');
	$('#minute').html(parseInt(myminute/60)  + ':' + parseInt(myminute%60/10) + '' + parseInt(myminute%10));
	totalprobs = $("numprob", xml).text();
	updateHeader();
	totalteams = 0;
	var team_id_arr = new Array();
	$(".sbTeam").each(function(id, elem) { team_id_arr[elem.id] = 1; });
	$("team", xml).each(function(id, elem) {
			++totalteams;
			team = elem;
			teamid = $("id", team).text();
			team_id_arr[teamid] = 0;
			teamname = $("name", team).text();
			var flag = 0, isnew = 0;
			if ($("#"+teamid).length == 0) {
				isnew = 1;
				myboard.append("<div id='" + teamid + "' class=sbTeam style='display:none;position:absolute'></div>");
				myteam = $("#"+teamid);
				myteam.css('width', myteam.parent().width());
				myteam.animate({top: myteam.parent().offset().top + HEADER_HEIGHT + id * ROW_HEIGHT + 800}, 'slow',
				   	function(){this.style.border='';});
				myteam.css('height', ROW_HEIGHT);
				myteam.delay(id*20).fadeIn('fast');
				myteam.fadeIn('fast');
			} else {
				myteam = $("#"+teamid);
				if(myteam.css('display') == 'none')
					myteam.animate({top: myteam.parent().offset().top + HEADER_HEIGHT + id * ROW_HEIGHT + 800}, 'fast',
					   	function(){this.style.border='';}).fadeIn('fast');
			}
			isbetter = updateTeam(team);

			rank = parseInt($("rank",team).text());
			var topv = myteam.parent().offset().top + HEADER_HEIGHT + (rank-1) * ROW_HEIGHT;
			if (topv != myteam.offset().top) {
				if(isbetter == 1) {
					var myleft = myteam.offset().left;
					myteam.css('zIndex', '100').delay('slow')
						.animate({top: parseInt(myteam.offset().top)-5}, 'fast');
					myteam.animate({top: topv}, 'slow',
							function(){this.style.border='';this.style.background='';});
				} else if (isnew == 1) {
					myteam.css('zIndex', '1');
					myteam.animate({top: topv}, 'slow',
							function(){this.style.border='';this.style.background='';});
				} else {
					myteam.css('zIndex', '1');
					myteam.delay('slow').delay('fast').animate({top: topv}, 'slow',
							function(){this.style.border='';this.style.background='';});
				}
			}
		} );
	$(".sbTeam").each(function(id, elem) { if(team_id_arr[elem.id]==1) $(elem).fadeOut('fast'); });
	
	myboard.css('height', HEADER_HEIGHT + totalteams * ROW_HEIGHT);
}

function updateTeam(teamXML) {
	var better = 0, slvchange = 0;
	tid = $("id", team).text();
	tname = $("name", team).text();
	trank = parseInt($("rank", team).text());
	tsolved = $("solved", team).text();
	tpenalty = $("penalty", team).text();
	myteam = $("#" + tid);
	if ($("#" + tid + "rank").length == 0)
		myteam.append("<div class=cellRank id='" + tid + "rank' style='width:25px'>" + trank + "</div>");
	else {
		orank = parseInt($("#" + tid + "rank").html());
		if (orank > trank)
			better = 1;
		$("#" + tid + "rank").html(trank);
	}

	if ($("#" + tid + "name").length == 0) {
		myteam.append("<div class=cellTeam id='" + tid + "name' align=left style='width:130px'>" + tname + "</div>");
		$("#" + tid + "name").css('overflow', 'hidden');
	}
	
	if ($("#" + tid + "slv").length == 0)
		myteam.append("<div class=cellSolved id='" + tid + "slv' style='width:50px'>" + tsolved + "</div>");
	else {
		oslv = parseInt($("#" + tid + "slv").html());
		if (oslv != tsolved)
			slvchange = 1;
		$("#" + tid + "slv").html(tsolved);
	}

	if ($("#" + tid + "time").length == 0)
		myteam.append("<div class=cellPenalty id='" + tid + "time' align=right style='width:50px'>" + tpenalty + "</div>");
	else
		$("#" + tid + "time").html(tpenalty);

<?php for ($i = 0; $i < $prob_n; $i++) { 
	if ($prob[$i]["effective"] != "Y") continue;
?>
	pb = $("p<?php echo $i?>", team);
	if (pb.length != 0) {
		pbatt = $("att", pb).text();
		pbpty = $("pty", pb).text();
		refresh = 0;
		if ($("#" + tid + "p<?php echo $i?>").length == 0) {
			myteam.append("<div id='" + tid + "p<?php echo $i?>' align=left style='width:60px'>" + pbatt + " / " + pbpty + "</div>");
			refresh = 1;
		}
		else {
			var str = pbatt + " / " + pbpty;
			if ($("#" + tid + "p<?php echo $i?>").html() != str) {
				refresh = 2;
				$("#" + tid + "p<?php echo $i?>").html(str);
			}
		}
		if (refresh == 2) {
			var cell = "#"+tid+"p<?php echo $i?>";
			var status = (pbpty != '--'? 'AC': (pbatt > 0)? 'WA' : 'NONE');
			if(status == 'AC') {
				$(cell).animate({opacity: 0.0}, 'fast', function(){
					$(this).removeClass('cellAC cellWA cellNONE').addClass('cellAC');
				}).animate({opacity:1.0}, 'fast');
			} else if (status == 'WA') {
				$(cell).animate({opacity: 0.0}, 'fast', function(){
					$(this).removeClass('cellAC cellWA cellNONE').addClass('cellWA');
				}).animate({opacity:1.0}, 'fast');
			} else {
				$(cell).animate({opacity: 0.0}, 'fast', function(){
					$(this).removeClass('cellAC cellWA cellNONE').addClass('cellNONE');
				}).animate({opacity:1.0}, 'fast');
			}
		} else if (refresh == 1) {
			var cell = "#"+tid+"p<?php echo $i?>";
			var status = (pbpty != '--'? 'AC': (pbatt > 0)? 'WA' : 'NONE');
			$(cell).addClass('cell'+status);
		}
	}
<?php } ?>
	if (better == 1) {
		$("#" + tid).css('borderBottom', '3px solid #999');
		$("#" + tid).css('borderRight', '3px solid #AAA');
		$("#" + tid).css('backgroundColor', '#CCC');
	}
	if (slvchange == 1) {
		$("#" + tid + "rank").animate({opacity: 0.3}, 'fast').animate({opacity: 1.0}, 'slow');
		$("#" + tid + "name").animate({opacity: 0.3}, 'fast').animate({opacity: 1.0}, 'slow');
		$("#" + tid + "slv").animate({opacity: 0.3}, 'fast').animate({opacity: 1.0}, 'slow');
		$("#" + tid + "time").animate({opacity: 0.3}, 'fast').animate({opacity: 1.0}, 'slow');
	}
	return better;
}

function changeCell(stat, id) {
	if(stat == 'AC')
		$(id).removeClass('cellWA cellNONE').addClass('cellAC');
	else if(stat == 'WA')
		$(id).removeClass('cellAC cellNONE').addClass('cellWA');
	else
		$(id).removeClass('cellAC cellWA').addClass('cellNONE');
}

function updateHeader() {
	var myboard = $("#board");
	var flag = 0;
	if ($("#boardheader").length == 0) {
		var s = "<div id=boardheader class=sbHeader style='display:none;position:absolute'>";
		s = s + "<div id=headerRank style='width:25px'><b>R</b></div>";
	    s = s + "<div id=headerName style='width:130px'><b>Team</b></div>";
		s = s + "<div id=headerSolved style='width:50px'><b>S</b></div>";
		s = s + "<div id=headerPenalty style='width:50px'><b>Time</b></div>";
		var mywidth = 25+130+50+50+4*8;
		<?php
			for($i=0;$i<$prob_n;$i++) {
				if ($prob[$i]["effective"] != "Y") continue;
				?>
				s = s + "<div id=header<?php echo $i?> style='width:60px'><b><?php echo $prob[$i]["short"]?></b></div>";
				mywidth += 60+8;
				<?php
			}
		?>
		s = s + "</div>";
		myboard.prepend(s);
		$("#boardheader").css('width', mywidth);
		w = parseInt($("#bigtable").css('width'));
		if (w <= 300 + mywidth) {
			$("#bigtable").css('width', 170 + mywidth);
			$("#bigtd").css('width', 50 + mywidth);
		}
		$("#board").css('width', mywidth + 'px');
		flag = 1;
	}
	var myheader = $("#boardheader");
	
	if (flag==1)
		myheader.delay(600).fadeIn('fast');
}

$(document).ready(
	function() {
		$("#freezeboard").click( function() { clearTimeout(updateTimer); isfreezing = 1;} );
		$("#firstboard").click( function() { clearTimeout(updateTimer); nextupdatetime = 1000; isfreezing = 0; 
			wantendtime = 0; timestamp = 0; onlynext = 0; updateBoard(); onlynext = 1; wantendtime=-1; });
		$("#latestboard").click( function() { clearTimeout(updateTimer); nextupdatetime = 5000; isfreezing = 0; 
			wantendtime = -1; timestamp = 0; onlynext = 0; updateBoard(); } );
		$("#prevboard").click( function() {
			if (lastsubmittime > 0) {
				$("#freezeboard").click();
				onlynext = 0;
				wantendtime = (parseInt(lastsubmittime) - 1);
				timestamp = 0;
				updateBoard();
				clearTimeout(updateTimer);
			}
		} );
		$("#nextboard").click( function() {
			onlynext = 1;
			wantendtime = -1;
			timestamp = (parseInt(lastsubmittime)+1);
			// $("#freezeboard").click();
			updateBoard();
		} );
		$("#stepboard").click( function() {
			onlynext = 1;
			wantendtime = -1;
			clearTimeout(updateTimer);
			timestamp = (parseInt(lastsubmittime)+1);
			isfreezing = 0;
			nextupdatetime = 5000;
			updateBoard();
		} );
	}
);
</script>

</html>
