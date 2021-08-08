<?php
require_once  ("../../connect_db.php");

$last = $mysqlConn->real_escape_string($_POST['last']);
$first = $mysqlConn->real_escape_string($_POST['first']);
$teacher = $mysqlConn->real_escape_string($_POST['teacher']);
$class = $mysqlConn->real_escape_string($_POST['class']);

/*Generate new values*/
$fieldWidth = rand(1000,2000);
$fieldHeight = rand(1000,2000);
$numRedCircles = rand(500,1500);
$numBlackCircles = rand(1000,4000);
$numBlueRectangles = rand(100,800);
/*insert new field*/
$query = "INSERT INTO `wolfe_fieldgenerator` (`width`, `height`, `red`, `black`, `blue`, `first`, `last`,`teacher`, `class`) VALUES ('$fieldWidth', '$fieldHeight', '$numRedCircles', '$numBlackCircles', '$numBlueRectangles', '$first', '$last', '$teacher', '$class');";
$result = $mysqlConn->query($query) or print("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");
//echo $query;
if($result)
{
	echo $mysqlConn->insert_id;
}
else
{
	exit;
}
?>
