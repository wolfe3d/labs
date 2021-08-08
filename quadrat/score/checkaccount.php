<?php
require_once ("../connectdb.php");
$teacherID = $mysqlConn->real_escape_string($_POST['teacher']) ;
$passCode = $mysqlConn->real_escape_string($_POST['passMe']) ;


/*Check Teacher Passcode*/
$query = "SELECT * FROM `wolfe_teachers` WHERE `email` LIKE '$teacherID' AND `passcode` LIKE '$passCode' AND `access` LIKE 'quadrat' ";
$result = $mysqlConn->query($query) or error_log("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");
if (mysqli_num_rows($result)>0)
{
	echo "1";
}
else
{
	echo "0";
}
?>