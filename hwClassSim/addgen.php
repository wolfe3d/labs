<?php
require_once  ("../../connectdb.php");

$userID = intval($_REQUEST["userID"]);
$trial = intval($_REQUEST["trial"]);
$classID = intval($_REQUEST["classID"]);
$groupID = intval($_REQUEST["groupID"]);

$gen = checkTrial($trial);

//echo "$userID, $trial, $classID, groupID $groupID, $gen";

if($gen < 11) //Do not add more than 10 generations
{
	if($gen == 0)
	{
		$queryInsert = "INSERT INTO `wolfe_generations` (`gen`, `genotype`, `user1`, `user2`, `trial`, `classID`, `groupID`) VALUES ('0', '1', '$userID', NULL, '$trial', '$classID', '$groupID');";
		if ($mysqlConn->query($queryInsert) === TRUE)
		{
			//New record created successfully
			$genID = $mysqlConn->insert_id;
			//echo "<tr><td>".$gen."</td><td>".getGenotypeString(1)."</td></tr>";
			$allData = array("genID"=>$genID, "gen"=> $gen, "genotype"=>$genotype[0], "user2"=>"none", "trial"=>$trial, "tries"=>"0", "classID"=> $classID, "groupID"=> $groupID);
			echo json_encode($allData);
			}
		else
		{
			echo json_encode(array("error"=>"Error_addGen1: $queryInsert $mysqlConn->error"));
		}
	}
	else{
		//generation that exists has already been added
		$randomMate = getRandomMate($trial, $gen-1);
		if (is_null($randomMate))
		{
			echo json_encode(array("error"=>"Oops No Mate...You cannot reproduce with yourself."));
		}
		else
		{
			//echo "randomMate $randomMate[0], $randomMate[1]";
			$genotype = determineGenotype(getGenotype($trial, $gen-1), $randomMate[1], $trial, 1);
			$queryInsert = "INSERT INTO `wolfe_generations` (`gen`, `genotype`, `user1`, `user2`, `trial`, `tries`, `classID`, `groupID`) VALUES ($gen, $genotype[0], $userID, $randomMate[0], $trial, $genotype[1], $classID, $groupID);";
			if ($mysqlConn->query($queryInsert) === TRUE)
			{
					$genID = $mysqlConn->insert_id;
					$allData = array("genID"=>$genID, "gen"=> $gen, "genotype"=>$genotype[0], "user2"=>$user2, "trial"=>$trial, "tries"=>$genotype[1], "classID"=> $classID, "groupID"=> $groupID);
					echo json_encode($allData);
					//echo "<tr><td>".$gen."</td><td>".getGenotypeString($genotype)."</td><td>".getMateName($randomMate[0])."</td></tr>";
					//echo "New record created successfully. Last inserted ID is: " . $userID;
			}
			else
			{
				echo json_encode(array("error"=>"Error_addGen2: $queryInsert $mysqlConn->error "));
			}
		}
	}
}

function checkTrial($trial)
{
	global $mysqlConn, $userID;
	$query = "SELECT * FROM `wolfe_generations` WHERE `user1` = $userID AND `trial` = $trial ORDER BY `gen` DESC;";
	$result = $mysqlConn->query($query) or error_log("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");
	$row = $result->fetch_assoc();
	if ($row)
	{
		return intval($row['gen'])+1;
	}
	else
	return 0;
}
function getRandomMate($trial, $gen)
{
	global $mysqlConn, $userID, $classID, $groupID;
	if($trial==3)
	{
		$queryGroup = " AND `groupID`=$groupID";
	}
	$query = "SELECT * FROM `wolfe_generations` WHERE `trial`=$trial AND `classID`=$classID AND `gen`=$gen".$queryGroup." AND `user1`!=$userID order by RAND() LIMIT 1;";
	$result = $mysqlConn->query($query) or error_log("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");
	$row = $result->fetch_assoc();
	if($row)
	{
		return [$row['user1'], $row['genotype']];
	}
	return Null;
}
function getMateName($user)
{
	global $mysqlConn;
	if($user)
	{
		$query = "SELECT * FROM `wolfe_users` WHERE `id` = $user;";
		$result = $mysqlConn->query($query) or error_log("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");
		if ($row = $result->fetch_assoc())
		{
			$fullName = $row['first'] . " " . $row['last'];
			return substr($fullName, 0, 50);
		}
	}
}
function getGenotypeString($genotype)
{
	if($genotype==0)
	{
		return "AA";
	}
	else if($genotype==2)
	{
		return "aa";
	}
	return "Aa";
}
function determineGenotype($genotype1, $genotype2, $trial, $tries)
{
	//default for $tries is 1
	if($trial==0 || $trial==3)
	{
		if ($genotype1==$genotype2 && $genotype1!=1)
		{
			//if parents are AA and AA, then offspring will be AA OR if parents are aa and aa, then offspring will be aa
			return [$genotype1, $tries];  //array has the $genotype and then number of tries
		}
		else if ($genotype1==$genotype2)
		{
			return [rand(0,1) + rand(0,1), $tries]; //each parent can give either a 0/1.  If both give a 0/0, then the child is AA.  If one give a 0 and the other 1, then the child is Aa.  If both give a 1, then the child is aa.
		}
		else
			return [1, $tries]; //if you have AA and aa, then all offspring will be Aa
	}
	else if($trial ==1) //selection
	{
		if ($genotype1==$genotype2 && $genotype1==0)
		{
			//if parents are AA and AA, then offspring will be AA
			return [$genotype1, $tries];
		}
		else if ($genotype1==$genotype2)
		{
			$selection = 2;
			$n=0;
			while($selection==2):
			{
				if ($n>0)
				{
					$tries +=1;
				}
				$n+=1;
				$selection = rand(0,1) + rand(0,1);  //each parent can give either a 0/1.  If both give a 0/0, then the child is AA.  If one give a 0 and the other 1, then the child is Aa.  If both give a 1, then the child is aa.
			}
			endwhile;
			return [$selection, $tries];
		}
		else
		{
			return [rand(0,1), $tries]; //if you have AA and Aa, then 50% chance of either
		}
	}
	else if($trial ==2) //heterozygote advantage
	{
		if ($genotype1==$genotype2 && $genotype1==0)
		{
			//if parents are AA and AA, then offspring will be AA
			return [$genotype1, $tries];
		}
		else if ($genotype1==$genotype2)
		{
			//if parents both are Aa
			//TODO implement tries and return array
			return fiftyPercentInfantDeathHetero($tries);
		}
		else
		{
				//TODO implement tries and return array
			return fiftyPercentInfantDeath($tries); //if you have AA and Aa, then 50% chance of either, only 50% chance AA will survive
		}
	}
}
function fiftyPercentInfantDeathHetero($tries)
{
	$genotype = rand(0,1) + rand(0,1); //each parent can give either a 0/1.  If both give a 0/0, then the child is AA.  If one give a 0 and the other 1, then the child is Aa.  If both give a 1, then the child is aa.
	if ($genotype==0) //if AA, then 50% chance to live
	{
		if(rand(0,1))
		{
			return [0, $tries];
		}
		else{
			return fiftyPercentInfantDeathHetero($tries+1);
		}
	}
	else if ($genotype ==2) //if aa, infant dies
	{
		return fiftyPercentInfantDeathHetero($tries+1);
	}
	else
		return [$genotype, $tries];
}
function fiftyPercentInfantDeath($tries)
{
	$genotype = rand(0,1); //if you have AA and Aa, then 50% chance of either.
	if ($genotype==0) //if AA, then 50% chance to live
	{
		if(rand(0,1))
		{
			return [0, $tries];
		}
		else{
			return fiftyPercentInfantDeath($tries+1);
		}
	}
	else
	return [$genotype, $tries];
}
function getGenotype($trial, $gen)
{
	//Get genotype of current user and generation
	global $mysqlConn, $userID;
	$query = "SELECT * FROM `wolfe_generations` WHERE `trial` = $trial AND `user1` = $userID ORDER BY `gen` DESC;";
	$result = $mysqlConn->query($query) or error_log("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");
	$row = $result->fetch_assoc();
	if ($row)
	{
		return intval($row['genotype']);
	}
}
?>
