<!DOCTYPE HTML>
<?php
if (empty ($_POST['field']))
{
	exit ("No field to measure!");
}
$fieldId=intval($_POST['field']);

require_once  ("../../connectdb.php");

/*check to see if id exists*/
$query = "select * from `wolfe_fieldgenerator` where `field` = $fieldId";
$result = $mysqlConn->query($query) or error_log("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");

if($result)
{
	$row = $result->fetch_assoc();
	if (!$row)
	{
		exit( "Failed to find record!" );
	}
}
if(!empty($row['passcode']))
{
	if (empty ($_POST['pass']))
	{
		exit ("Unknown error: 111."); //No passcode entered
	}
	else {
		if($_POST['pass']!=$row['passcode'])
		{
				exit ("Unknown error: 112.");  //Passcode entered does not match passcode
		}
	}
}
$first = isset($row['first'])?$row['first']:"";
$last = isset($row['last'])?$row['last']:"";
$teacher = isset($row['teacher'])?$row['teacher']:"";
$class = isset($row['class'])?$row['class']:"";
$width = isset($row['width'])?$row['width']:"";
$height = isset($row['height'])?$row['height']:"";
$field = isset($row['field'])?$row['field']:"";
$studentRed = isset($row['studentRed'])?$row['studentRed']:"";
$studentBlue = isset($row['studentBlue'])?$row['studentBlue']:"";
$studentBlack = isset($row['studentBlack'])?$row['studentBlack']:"";
$studentSimpson = isset($row['studentSimpson'])?$row['studentSimpson']:"";
$numQuadrats = isset($row['numQuadrats'])?$row['numQuadrats']:"";
$sideLengthQuadrat = isset($row['sideLengthQuadrat'])?$row['sideLengthQuadrat']:"";
$fieldimage = isset($row['fieldimage'])?$row['fieldimage']:"";
?>
<html lang="en">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta http-equiv="Pragma" content="no-cache">
		<script src="../lib/jquery.js"></script>
	<script src="../lib/jquery.validate.min.js"></script>
  <title>Field Generator</title>
	<style>
		body {
		margin: 0px;
		padding: 0px;
		}
		.error {
		color: red;
		background-color: #acf;
		}
	</style>
  <script type="text/javascript">
	$().ready(function() {
		// validate signup form on keyup and submit
		$("#postResults").validate({
			rules: {
				black: {
					min: 0,
					required:true,
					digits:true,
				},
				red: {
					min: 0,
					required:true,
					digits:true,
				},
				blue: {
					min: 0,
					required:true,
					digits:true,
				},
				simpsons: {
					min: 0,
					required:true,
					number:true,
				}
			},
			messages: {
				black: "Calculate the population for the black diamonds. Give your answer as a whole number.",
				red: "Calculate the population for the red circles. Give your answer as a whole number.",
				blue: "Calculate the population for the blue squares. Give your answer as a whole number.",
				simpsons: "You must enter a value here.  Give your answer as a decimal to the nearest hundredth.  Make sure your value is positive.  If you got a negative answer, make sure you are subtracting from 1.",
			},
			submitHandler: function(form) {
				form.submit();
            }
		});
	});
</script>
  </head>
  <body>

<h1>Quadrat Sampling</h1>
<h2>Your information</h2>
<div style="text-indent:40px">
	<div>Name: <?=$first?> <?=$last?></div>
	<div>Teacher: <?=$teacher?></div>
	<div>Class: <?=$class?></div>
</div>

<h2>Field Information</h2>
<div style="text-indent:40px">
	<div>Width = <?=$width?> m</div>
	<div>Height = <?=$height?> m</div>
</div>

<h2>Procedure</h2>
<div>Select a length of a side of quadrat and then click where the quadrat should be placed.  When you have finished estimating the size of each of the organism's population and calculating the Simpson's index, enter your values below.</div>
<div style="text-indent:40px"><br>
	<div>Length of Side of Quadrat <input type="text" id="quadrantSize" name="quadrantSize" value="0"> m</div>
	<div>Number of Quadrats used <span id="numQuadrats">0</span></div>
	<div>Mouse location in field: (<span id="mX"></span>,<span id="mY"></span>)</div>
	<div><a href="#" onclick="clearCanvas();">Clear All Quadrats</a>
 </div>
<div id="paint" style="padding:20px;width:<?=$row['width']?>px;height:<?=$height?>px;">
 		<canvas id="myCanvas"></canvas>
</div>

    <script>
var numSites = 0;
var canvas = document.getElementById('myCanvas');
var ctx = canvas.getContext('2d');

var painting = document.getElementById('paint');
var paint_style = getComputedStyle(painting);
canvas.width = parseInt(paint_style.getPropertyValue('width'));
canvas.height = parseInt(paint_style.getPropertyValue('height'));
var spanX = document.getElementById("mX");
var spanY = document.getElementById("mY");
var mouse = {x: 0, y: 0};

var background = new Image();
var dir = "../../fields/";
background.src = dir + '<?=$fieldimage?>';
// Make sure the image is loaded first otherwise nothing will draw.
background.onload = function(){
    ctx.drawImage(background,0,0);
}

ctx.lineWidth = 1;
ctx.rect(0, 0, canvas.width, canvas.height);
ctx.stroke();
ctx.closePath();
ctx.beginPath();

canvas.addEventListener('mousemove', function(e) {
  mouse.x = e.pageX - this.offsetLeft;
  mouse.y = e.pageY - this.offsetTop;
  spanX.textContent = mouse.x;
  spanY.textContent = mouse.y;
}, false);


canvas.addEventListener('mousedown', function(e) {
	var quadratLength = parseInt(document.getElementById("quadrantSize").value);
	if(quadratLength==0)
	{
		alert("You must change the length of the side of a quadrat.");
	}
	else
	{
		$("#size").attr('value',quadratLength);
		if(mouse.x + quadratLength <= canvas.width && mouse.y + quadratLength <= canvas.height)
		{
			ctx.beginPath();
			ctx.rect(mouse.x,  mouse.y, quadratLength, quadratLength);
			if(quadratLength>0)
			{
				numSites += 1;
				$("#numQuadrats").html(numSites);
				$("#number").attr('value',numSites);
			}
			ctx.stroke();
			ctx.closePath();
		}
	}
}, false);
function clearCanvas()
{
	//begin new paths
	ctx.beginPath();
	ctx.clearRect(0, 0, canvas.width, canvas.height);
	ctx.drawImage(background,0,0);
	ctx.rect(0, 0, canvas.width, canvas.height);
	ctx.stroke();
	ctx.closePath();
	numSites = 0;
	$("#numQuadrats").html(numSites);
	$("#number").attr('value',numSites);
}
</script>

 <form id="postResults" method="post" action="submission.php">
		<fieldset>
			<legend>Results</legend>
			<p>
				<label for="field">Field ID</label>
				<input id="field" name="field" type="text" readonly style="background: grey;" value="<?=$field?>">
			</p>
			<p>
				<label for="size">Length (m) of Side of Quadrat Used</label>
				<input id="size" name="size" type="text" readonly style="background: grey;" value="<?=$sideLengthQuadrat?>">
			</p>
			<p>
				<label for="number">Number of Quadrats</label>
				<input id="number" name="number" type="text" readonly style="background: grey;" value="<?=$numQuadrats?>">
			</p>
			<div>Enter your calculations of the number of each organism in the entire field.</div>
			<p>
				<label for="black">Number of Black Diamonds</label>
				<input id="black" name="black" type="text" value="<?=$studentBlack?>">
			</p>
			<p>
				<label for="red">Number of Red Circles</label>
				<input id="red" name="red" type="text" value="<?=$studentRed?>">
			</p>
			<p>
				<label for="blue">Number of Blue Squares</label>
				<input id="blue" name="blue" type="text" value="<?=$studentBlue?>">
			</p>
			<div>Calculate the diversity index.  Give your answer to the nearest hundredth (2 decimal places).</div>
			<p>
				<label for="simpsons">Simpson's Index</label>
				<input id="simpsons" name="simpsons" type="text" value="<?=$studentSimpson?>">
			</p>
		</fieldset>
		<p>
			<input class="submit" type="submit" value="Submit">
		</p>
		</fieldset>
</form>
  </body>
</html>
