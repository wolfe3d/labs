<?php
require_once  ("../../connectdb.php");

$fieldId=intval($_POST['field']);
$sideLengthQuadrat = $mysqlConn->real_escape_string($_REQUEST['size']);
$numQuadrats = $mysqlConn->real_escape_string($_REQUEST['number']);
$studentRed = $mysqlConn->real_escape_string($_REQUEST['red']);
$studentBlack = $mysqlConn->real_escape_string($_REQUEST['black']);
$studentBlue = $mysqlConn->real_escape_string($_REQUEST['blue']);
$studentSimpson = $mysqlConn->real_escape_string($_REQUEST['simpsons']);


/*check to see if id exists*/
$query = "SELECT * from `wolfe_fieldgenerator`";
$check = $mysqlConn->query($query) or error_log("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");
if($check)
{
	$query = "UPDATE `wolfe_fieldgenerator` SET `numQuadrats` = '$numQuadrats', `sideLengthQuadrat` = '$sideLengthQuadrat', `studentRed` = '$studentRed', `studentBlack` = '$studentBlack ', `studentBlue` = '$studentBlue', `studentSimpson` = '$studentSimpson' WHERE `wolfe_fieldgenerator`.`field` = 999205; ";
	$result = $mysqlConn->query($query) or error_log("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");
	if($result)
	{
		echo "Your results have been submitted to your teacher.  Have a great day!";
		exit;
	}
}
echo "Something went wrong.  Go back and check your submission";
?>
