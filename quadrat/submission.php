<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="Pragma" content="no-cache">
<title>Field Submission</title>
</head>
<body>
<?php
require_once  ("../../connectdb.php");

if(isset($_REQUEST['field']))
{
	$fieldID=intval($_REQUEST['field']);
	echo "Your field ID: $fieldID . Write this down!<br>";
	//Use of !empty see https://stackoverflow.com/questions/4559925/why-check-both-isset-and-empty
	$passcode = bin2hex(random_bytes(5));
	echo "Your passcode to edit this submission: $passcode . Write this down!<br>";
	$sideLengthQuadrat = !empty($_REQUEST['size'])? intval($_REQUEST['size']): 0;
	echo "The length of a side of a quadrat: $sideLengthQuadrat <br>";
	$numQuadrats = !empty($_REQUEST['number'])? intval($_REQUEST['number']):0;
	echo "You used $numQuadrats quadrats .<br>";
	$studentRed = !empty($_REQUEST['red'])? intval($_REQUEST['red']):0;
	echo "Your estimated number of red circles: $studentRed<br>";
	$studentBlack = !empty($_REQUEST['black'])? intval($_REQUEST['black']):0;
	echo "Your estimated number of black diamonds: $studentBlack<br>";
	$studentBlue = !empty($_REQUEST['blue'])? intval($_REQUEST['blue']):0;
	echo "Your estimated number of blue squares: $studentBlue<br>";
	$studentSimpson =!empty($_REQUEST['simpsons'])?  floatval($_REQUEST['simpsons']):0;
	echo "Your calculated Simpson's index: $studentSimpson<br>";

	/*check to see if id exists*/
	$query = "SELECT * from `wolfe_fieldgenerator` WHERE `wolfe_fieldgenerator`.`field` = $fieldID;";
	$check = $mysqlConn->query($query) or error_log("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");
	if($check)
	{
		$row = $check->fetch_assoc();
		if (!$row)
		{
			exit( "Failed to find record!" );
		}
		if ($row['black']>0)
		$percentBlack = abs($studentBlack - $row['black'])/$row['black'] *100;
		if ($row['red']>0)
		$percentRed = abs($studentRed - $row['red'])/$row['red'] *100;
		if ($row['blue']>0)
		$percentBlue = abs($studentBlue - $row['blue'])/$row['blue'] *100;

		$percentDiff = round(($percentBlack + $percentRed  + $percentBlue )/3,2);
		$color = "green";
		if ($percentDiff>5)
		{
			$color = "red";
		}

		echo "<strong>Your average percentage difference from the actual number of organisms is: <span style='color:$color'>$percentDiff %</span> </strong><br>";

		$query = "UPDATE `wolfe_fieldgenerator` SET `passcode` = '$passcode', `numQuadrats` = '$numQuadrats', `sideLengthQuadrat` = '$sideLengthQuadrat', `studentRed` = '$studentRed', `studentBlack` = '$studentBlack ', `studentBlue` = '$studentBlue', `studentSimpson` = '$studentSimpson' WHERE `wolfe_fieldgenerator`.`field` = $fieldID; ";
		$result = $mysqlConn->query($query) or error_log("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");
		if($result)
		{
			exit( "<strong>Your results have been submitted to your teacher.  Have a great day!</strong>");
		}
	}
	else {
		exit( "No field found!");
	}
}
echo "Something went wrong.  Go back and check your submission";
?>
</body>
</html>
