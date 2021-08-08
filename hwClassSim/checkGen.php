<?php
$mysqlConn= mysqli_connect('localhost', 'ggUs3963!er', '3DP2PuMsHwzXRpXR', 'wolfescience');
/* check connection */
if ($mysqlConn->connect_errno) {
   printf("Connect failed: %s\n", $mysqlConn->connect_error);
   exit();
}

if(!$userID)
{
	$userID = intval($_REQUEST["userID"]);
	$trial = intval($_REQUEST["trial"]);
	$classID = intval($_REQUEST["classID"]);
	$groupID = intval($_REQUEST["groupID"]);
}

//Find highest generation for the current user in a specific trial
$query = "SELECT * FROM `wolfe_generations` WHERE `user1` = $userID AND `trial` = $trial ORDER BY `gen` DESC;";
$result = $mysqlConn->query($query) or error_log("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");
$row = $result->fetch_assoc();
if ($row) 
{
	$gen = intval($row['gen']);
	//output the highest generation data for the current user's generation
	if($trial==3)
	{
		$queryGroup = " AND `groupID`=$groupID";
	}
	$query = "SELECT * FROM `wolfe_generations` WHERE `trial` = $trial AND `classID` = $classID AND `gen` = $gen".$queryGroup.";";
	$result = $mysqlConn->query($query) or error_log("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");
	$A = 0;
	$B = 0;
	$C = 0;
	while ($row = $result->fetch_assoc()):
		if($row['genotype']==0)
		{
			$A +=1;
		}
		else if($row['genotype']==1)
		{
			$B +=1;
		}
		else
		{
			$C +=1;
		}
	endwhile;
	$total = $A+$B+$C;
	echo "<div>Population at Generation $gen</div><div> AA:".$A.", Aa:".$B.", aa:".$C.", total:".$total."</div>";
} 

?>