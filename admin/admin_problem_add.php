<?php
session_start ();
if ($_SESSION["group"]!="admin") {
	header ("Location: ../index.php");
	exit (0);
}

require_once ("../lib/php/dblink.php");
require_once ("../lib/php/func.php");

if ($_POST["submit"]=="save") {
	$prob["title"] = $_POST["title"];
	$prob["contest_id"] = $_POST["contest_id"];
	$prob["source"] = $_POST["source"];
	$_SESSION["source"] = $prob["source"];
	$prob["time_limit"] = $_POST["time_limit"];
	$prob["memory_limit"] = $_POST["memory_limit"] * 1024;
	$prob["output_limit"] = $_POST["output_limit"] * 2;
	$prob["execute_type"] = $_POST["execute_type"];
	$prob["judge_type"] = $_POST["judge_type"];
	$prob["description"] = $_POST["description"];
	$prob["input"] = $_POST["input"];
	$prob["output"] = $_POST["output"];
	$prob["sample_input"] = $_POST["sample_input"];
	$prob["sample_output"] = $_POST["sample_output"];
	$prob["hint"] = $_POST["hint"];
	$prob["effective"] = "Y";

	$sql = insert_into_table ("problems", $prob);
	mysql_query ($sql) or die ("Invalid query: $sql");

	$sql = "select problem_id from problems order by problem_id desc limit 1";
	$r = mysql_query ($sql) or die ("Invalid query: $sql");

	list ($id) = mysql_fetch_row ($r);

	for($i = 0; $i < 10; $i++) {
		if ($_FILES["input_file".$i]["size"] > 0) {
			$file = $_FILES["input_file".$i];
			$filename = "subtest".$i."/".$id.".in";
			$fullpath = "../testdata/$filename";
			copy ($file["tmp_name"], $fullpath);
			exec ("dos2unix $fullpath");
		} else {
			break;
		}	
	}

	for($i = 0; $i < 10; $i++) {
		if ($_FILES["output_file".$i]["size"] > 0) {
			$file = $_FILES["input_file".$i];
			$filename = "subtest".$i."/".$id.".out";
			$fullpath = "../testdata/$filename";
			copy ($file["tmp_name"], $fullpath);
			exec ("dos2unix $fullpath");
		} else {
			break;
		}	
	}

	header ("Location: admin_problem.php");
}

?>
<html>

<head>

<?php include_once ("admin_html_header.php"); ?>

</head>

<body>
	<!-- mean -->
	<?php require ("menu.php") ?>
	<!-- page -->
	<div class="container jumbotron" style="padding-top: 3%;">
    	<h2 class="text-center">Add Porblem</h2><hr>
		<form action=admin_problem_add.php method=post enctype="multipart/form-data">
			<div class="form-group">
				<label for="title">題目標題</label>
				<input type="text" name="title" class="form-control" id="title" placeholder="輸入題目標題">
			</div>
			<div class="form-group">
				<label for="contest_id">Contest</label>
				<select class="form-control" name="contest_id" id="contest_id">
					<option value="0">none</option>
					<?php
						$now = get_full_date ();
						$sql = "select * from contests where end_time>'$now' order by start_time asc";
						$r = mysql_query ($sql) or die ("Invalid query: $sql");

						while ($contest = mysql_fetch_array ($r)) {
							echo "<option value=".$contest["contest_id"].">".$contest["title"]."</option>";
						}
						$now = get_full_date ();
						$sql = "select * from contests where not end_time>'$now' order by start_time asc";
						$r = mysql_query ($sql) or die ("Invalid query: $sql");

						while ($contest = mysql_fetch_array ($r)) {
							echo "<option value=".$contest["contest_id"].">[old]".$contest["title"]."</option>";
						}
					?>
				</select>
			</div>
			<div class="form-group">
				<label for="source">Source</label>
				<input type="text" name="source" class="form-control" id="source" value="<?=$_SESSION['source']?>">
			</div>
			<div class="form-group">
				<div class="col-sm-6" style="padding-left: 0; margin-bottom: 15px;">
					<label for="time_limit">Time Limit (sec)</label>
					<input type="text" name="time_limit" class="form-control" id="time_limit" value="10">
				</div>
				<div class="col-sm-6" style="padding-right: 0; margin-bottom: 15px;">
					<label for="memory_limit">Memory Limit (KB)</label>
					<input type="text" name="memory_limit" class="form-control" id="memory_limit" value="65536">
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-6" style="padding-left: 0; margin-bottom: 15px;">
					<label for="judge_type">Judge Type</label>
					<select name="judge_type" class="form-control" >
						<option value="0" selected>
							0 - Default diff -q -b -B</option>
						<option value="1">
							1 - Special Judge (Not done yet)</option>
						<option value="13">
							13 - Floating Point Judger 1e-3</option>
						<option value="14">
							14 - Floating Point Judger 1e-4</option>
						<option value="15">
							15 - Floating Point Judger 1e-5</option>
						<option value="16">
							16 - Floating Point Judger 1e-6</option>
					</select>
				</div>
				<div class="col-sm-6" style="padding-right: 0; margin-bottom: 15px;">
					<label for="execute_type">Execute Type</label><br>
					<div class="radio-inline">
						<label style="padding: 5px;">
							<input type="radio" name="execute_type" value="0" id="execute_type" checked>0 - Batch
						</label>
					</div>
					<div class="radio-inline">
						<label style="padding: 5px;">
							<input type="radio" name="execute_type" value="1" id="execute_type">1 - Interactive
						</label>					
					</div>
				</div>
			</div>
  			<div class="form-group">
				<label for="output_limit">Output Limit (KB)</label>
				<input type="text" name="output_limit" class="form-control" id="output_limit" value="100">
  			</div>
			<div class="form-group">
				<label for="description">Description</label>
				<textarea style="resize: none;" class="form-control" rows="10" name="description" id="description" placeholder="type problem description here"></textarea>
			</div>
			<div class="form-group">
				<label for="input">Input</label>
				<textarea style="resize: none;" class="form-control" rows="10" name="input" id="input" placeholder="type input info here"></textarea>
			</div>
			<div class="form-group">
				<label for="Output">output</label>
				<textarea style="resize: none;" class="form-control" rows="10" name="output" id="output" placeholder="type output info here"></textarea>
			</div>
			<div class="form-group">
				<label for="sample_input">Sample Input</label>
				<textarea style="resize: none;" class="form-control" rows="10" name="sample_input" id="sample_input" placeholder="type sample input here"></textarea>
			</div>
			<div class="form-group">
				<label for="sample_output">Sample Output</label>
				<textarea style="resize: none;" class="form-control" rows="10" name="sample_output" id="sample_output" placeholder="type sample output here"></textarea>
			</div>
			<div class="form-group">
				<label for="hint">Hint</label>
				<textarea style="resize: none;" class="form-control" rows="5" name="hint" id="hint" placeholder="type Problem hint here"></textarea>
			</div>
			<div class="form-group">
				<div class="col-sm-6" style="padding-left: 0; margin-bottom: 15px;">
					<label for="input_file">Input File</label>
					<?php for($i = 0; $i < 10; $i++) { ?>
						<input style="padding-left: 10%; padding-top: 5px;" type="file" id="input_file" name="input_file<?=$i?>">
					<?php }?>
				</div>
				<div class="col-sm-6" style="padding-right: 0; margin-bottom: 15px;">
					<label for="output_file">Output File</label>
					<?php for($i = 0; $i < 10; $i++) { ?>
						<input style="padding-left: 10%; padding-top: 5px;" type="file" id="output_file" name="output_file<?=$i?>">
					<?php }?>
				</div>
				<p style="font-size: 1.2em;" class="text-center">2016/12/12 新增多組測資。最多 10 組測資，
				新增題目後記得要在 judge.pl 檔設定測資數量，否則預設是只會測一組測資</p>
			</div>
			<center>
				<button type="submit" class="btn btn-default" name="submit" value="save">送出</button>
			</center>
		</form>
	</div>
	<!-- footer -->
	<?php require ("../footer.php") ?>


</body>

</html>
