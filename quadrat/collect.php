<!DOCTYPE HTML>
<?php
$fieldId=intval($_REQUEST['field']);

require_once  ("../../connect_db.php");

/*check to see if id exists*/
$query = "select * from `wolfe_fieldgenerator` where `field` = $fieldId";
$result = $mysqlConn->query($query) or error_log("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");

if($result)
{
	$row = $result->fetch_assoc();
	if (!$row)
	{
		echo "Failed to find record!";
		exit;
	}
}

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
				black: "required",
				red: "required",
				blue: "required",
				simpsons: "required",
			},
			messages: {
				black: "Calculate the population for the black diamonds.",
				red: "Calculate the population for the red circles.",
				blue: "Calculate the population for the blue squares.",
				simpsons: "Calculate the Simpsons Index of diversity for this field.",
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
	<div>Name: <?=$row['first']?> <?=$row['last']?></div>
	<div>Teacher: <?=$row['teacher']?></div>
	<div>Class: <?=$row['class']?></div>
</div>

<h2>Field Information</h2>
<div style="text-indent:40px">
	<div>Width = <?=$row['width']?> m</div>
	<div>Height = <?=$row['height']?> m</div>
</div>

<h2>Procedure</h2>
<div>Select a length of a side of quadrat and then click where the quadrat should be placed.  When you have finished estimating the size of each of the organism's population and calculating the Simpson's index, enter your values below.</div>
<div style="text-indent:40px"><br>
	<div>Length of Side of Quadrat <input type="text" id="quadrantSize" name="quadrantSize" value="0"> m</div>
	<div>Number of Quadrats used <span id="numQuadrats">0</span></div>
	<div>Mouse location in field: (<span id="mX"></span>,<span id="mY"></span>)</div>
	<div><a href="#" onclick="clearCanvas();">Clear All Quadrats</a>
 </div>
<div id="paint" style="padding:20px;width:<?=$row['width']?>px;height:<?=$row['height']?>px;">
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
background.src = "imagegen.php?field=<?=$row['field']?>";
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
				<label for="size">Length (m) of Side of Quadrat Used</label>
				<input id="size" name="size" type="text" readonly style="background: grey;">
			</p>
			<p>
				<label for="number">Number of Quadrats</label>
				<input id="number" name="number" type="text" readonly style="background: grey;">
			</p>
			<div>Enter your calculations of the number of each organism in the entire field.</div>
			<p>
				<label for="black">Number of Black Diamonds</label>
				<input id="black" name="black" type="text">
			</p>
			<p>
				<label for="red">Number of Red Circles</label>
				<input id="red" name="red" type="text">
			</p>
			<p>
				<label for="blue">Number of Blue Squares</label>
				<input id="blue" name="blue" type="text">
			</p>
			<div>Calculate the diversity index.  Give your answer to the nearest hundredth (2 decimal places).</div>
			<p>
				<label for="simpsons">Simpson's Index</label>
				<input id="simpsons" name="simpsons" type="text">
			</p>
		</fieldset>
		<p>
			<input class="submit" type="submit" value="Submit">
		</p>
		</fieldset>
</form>
  </body>
</html>
