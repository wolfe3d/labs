<?php
require_once  ("../../connectdb.php");

$teacherID= $mysqlConn->real_escape_string($_POST['teacherID']);
if(empty($teacherID))
{
	echo 0;
	exit();
}
/*check to see if id exists*/
$query = "SELECT * FROM `wolfe_teachers` WHERE `email` LIKE '$teacherID' AND `access` LIKE 'quadrat' ";
$result = $mysqlConn->query($query) or error_log("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");
if (mysqli_num_rows($result)==0) {
	echo 0;
}
else
{
	echo 1;
}

?>
