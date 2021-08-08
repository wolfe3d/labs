<?php
require_once  ("../../connectdb.php");

if(!$classID)
{
	$classID = intval($_REQUEST["classID"]);
}

$query = "SELECT * FROM `wolfe_users` WHERE `classID` = $classID;";
$result = $mysqlConn->query($query) or error_log("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");
$wet = 0;
$dry = 0;
while ($row = $result->fetch_assoc()):
	if($row['earwax']==0)
	{
		$wet +=1;
	}
	else
	{
		$dry +=1;
	}
endwhile;
echo "<td>".$wet."</td><td>".$dry."</td>";
?>
