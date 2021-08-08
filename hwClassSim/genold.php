<?php
$mysqlConn= mysqli_connect('localhost', 'ggUs3963!er', '3DP2PuMsHwzXRpXR', 'wolfescience');
/* check connection */
if ($mysqlConn->connect_errno) {
   printf("Connect failed: %s\n", $mysqlConn->connect_error);
   exit();
}
$userID = intval($_REQUEST["userID"]);
$groupID=-1;
if($userID)
{
	$query = "SELECT * FROM `wolfe_users` WHERE `id` = $userID ";
	$result = $mysqlConn->query($query) or error_log("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");
	$row = $result->fetch_assoc();
		if($row)
		{
			$firstN = $row["first"];
			$lastN = $row["last"];
			$emailN = $row["email"];
			$earwax = $row["earwax"];
			$classID = $row["classID"];
			$groupID = $row["groupID"];
			$query = "SELECT * FROM `wolfe_classes` WHERE `id` LIKE '$classID' ";
			$result = $mysqlConn->query($query) or error_log("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");
			$row2 = $result->fetch_assoc();
			if($row2)
			{
				$className = $row2["name"];
				$wetSchool = $row2['wet'];
				$drySchool = $row2['dry'];
			}			
			$trial= intval($_REQUEST["trial"]);
			$action = $mysqlConn->real_escape_string(strip_tags(trim($_REQUEST["action"])));
			if ($action == "add")
			{
				addGen($trial);
			}
	}
}
else
{
	$firstN = $mysqlConn->real_escape_string(strip_tags(trim($_REQUEST["firstname"])));
	$lastN = $mysqlConn->real_escape_string(strip_tags(trim($_REQUEST["lastname"])));
	$emailN = $mysqlConn->real_escape_string(strip_tags(trim($_REQUEST["email"])));
	$earwax = intval($_REQUEST["earwax"]);
	$classCode = $mysqlConn->real_escape_string(strip_tags(trim($_REQUEST["classCode"])));
	$className = "";

	$personID = -1;
	/*check to see if classID exists*/
	if($classCode)
	{
		$query = "SELECT * FROM `wolfe_classes` WHERE `code` LIKE '$classCode' ";
		$result = $mysqlConn->query($query) or error_log("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");
		$row = $result->fetch_assoc();
		if($row)
		{
			$className = $row["name"];
			$classID = $row["id"];
			if($firstN && $lastN && $emailN && $classID)
			{
				$groupID = rand(0,2);  //randomly assign each person to a different group
				$queryInsert = "INSERT INTO `wolfe_users` (`id`, `first`, `last`, `email`, `identity`, `earwax`, `classID`, `groupID`) VALUES (NULL, '$firstN', '$lastN', '$emailN', '2', $earwax, '$classID', '$groupID');";
				if ($mysqlConn->query($queryInsert) === TRUE) {
					$personID = $mysqlConn->insert_id;
					header("Location:genold.php?userID=$personID");
					//echo "New record created successfully. Last inserted ID is: " . $userID;
				} else {
					echo "Error_classCode";
					exit();
				}
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
	if ($row) 
	{
		return [$row['user1'], $row['genotype']];
	} 
	echo "<div style='color:red'>Oops No Mate...You cannot reproduce with yourself.</div>";
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
$trialGen=[0,0,0,0];
function dataGen($trial)
{
	global $mysqlConn, $userID, $trialGen;
	$query = "SELECT * FROM `wolfe_generations` WHERE `trial` = $trial AND `user1` = $userID ORDER BY `gen`;";
	$result = $mysqlConn->query($query) or error_log("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");
	$outputStr = "";
	while ($row = $result->fetch_assoc()):
		$genotypeSt = "Aa";
		if($row['genotype']==0)
		{
			$genotypeSt = "AA";
		}
		else if($row['genotype']==2)
		{
			$genotypeSt = "aa";
		}
		$mateName = getMateName($row['user2']);
		$outputStr .= "<tr><td>".$row['gen']."</td><td>".$genotypeSt."</td><td>".$mateName."</td></tr>";
		$trialGen[$trial] = $row['gen'];
	endwhile;
	return $outputStr;
}
function dataTrial($trial, $gen)
{
	global $mysqlConn, $userID, $classID, $groupID;
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
	return "<div style='color:blue'><div>Population at Generation $gen</div><div> AA:".$A.", Aa:".$B.", aa:".$C.", total:".$total."</div></div>";
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
function determineGenotype($genotype1, $genotype2, $trial)
{
	if($trial==0 || $trial==3)
	{
		if ($genotype1==$genotype2 && $genotype1!=1)
		{
			//if parents are AA and AA, then offspring will be AA OR if parents are aa and aa, then offspring will be aa
			return $genotype1;
		}
		else if ($genotype1==$genotype2)
		{
			return rand(0,1) + rand(0,1); //each parent can give either a 0/1.  If both give a 0/0, then the child is AA.  If one give a 0 and the other 1, then the child is Aa.  If both give a 1, then the child is aa.
		}
		else
			return 1; //if you have AA and aa, then all offspring will be Aa
	}
	else if($trial ==1) //selection
	{
		if ($genotype1==$genotype2 && $genotype1==0)
		{
			//if parents are AA and AA, then offspring will be AA 
			return $genotype1;
		}
		else if ($genotype1==$genotype2)
		{
			$selection = rand(0,1) + rand(0,1); //each parent can give either a 0/1.  If both give a 0/0, then the child is AA.  If one give a 0 and the other 1, then the child is Aa.  If both give a 1, then the child is aa.
			while($selection==2):
			{
				$selection = rand(0,1) + rand(0,1);
			}
			endwhile;
			return $selection;
		}
		else
			return rand(0,1); //if you have AA and Aa, then 50% chance of either
	}
	else if($trial ==2) //heterozygote advantage
	{
		if ($genotype1==$genotype2 && $genotype1==0)
		{
			//if parents are AA and AA, then offspring will be AA 
			return $genotype1;
		}
		else if ($genotype1==$genotype2)
		{
			//if parents both are Aa
			return fiftyPercentInfantDeathHetero();
		}
		else
			return fiftyPercentInfantDeath(); //if you have AA and Aa, then 50% chance of either, only 50% chance AA will survive
	}
}
function fiftyPercentInfantDeathHetero()
{
	$genotype = rand(0,1) + rand(0,1); //each parent can give either a 0/1.  If both give a 0/0, then the child is AA.  If one give a 0 and the other 1, then the child is Aa.  If both give a 1, then the child is aa.
	if ($genotype==0) //if AA, then 50% chance to live
	{
		if(rand(0,1))
		{
			return 0;
		}
		else{
			return fiftyPercentInfantDeathHetero();
		}
	}
	else if ($genotype ==2) //if aa, infant dies
	{
		return fiftyPercentInfantDeathHetero();
	}
	else
		return $genotype;
}
function fiftyPercentInfantDeath()
{
	$genotype = rand(0,1); //if you have AA and Aa, then 50% chance of either.
	if ($genotype==0) //if AA, then 50% chance to live
	{
		if(rand(0,1))
		{
			return 0;
		}
		else{
			return fiftyPercentInfantDeath();
		}
	}
	else
	return $genotype;
}
function addGen($trial)
{
	global $mysqlConn, $userID, $classID, $groupID;
	$gen = checkTrial($trial);
	if($gen < 11) //Do not add more than 10 generations
	{
		if($gen == 0)
		{
			$queryInsert = "INSERT INTO `wolfe_generations` (`gen`, `genotype`, `user1`, `user2`, `trial`, `classID`, `groupID`) VALUES ('0', '1', '$userID', NULL, '$trial', '$classID', '$groupID');";
			if ($mysqlConn->query($queryInsert) === TRUE) 
			{
					$genID = $mysqlConn->insert_id;
					//echo "New record created successfully. Last inserted ID is: " . $userID;
				}
				else 
				{
					echo "<div style='color:red'>Error_addGen1: " . $queryInsert . "<br>" . $mysqlConn->error . "</div>";
				}
		}
		else{
			//generation that exists has already been added
			$randomMate = getRandomMate($trial, $gen-1);
			$genotype = determineGenotype(getGenotype($trial, $gen-1), $randomMate[1], $trial);
			$queryInsert = "INSERT INTO `wolfe_generations` (`gen`, `genotype`, `user1`, `user2`, `trial`, `classID`, `groupID`) VALUES ($gen, $genotype, '$userID', $randomMate[0], '$trial', '$classID', '$groupID');";
			if ($mysqlConn->query($queryInsert) === TRUE) 
			{
					$genID = $mysqlConn->insert_id;
					//echo "New record created successfully. Last inserted ID is: " . $userID;
			}
			else 
			{
				echo "<div style='color:red'>Error_addGen2: " . $queryInsert . "<br>" . $mysqlConn->error . "</div>";
			}
		}
	}
}
function getEarWax($wax)
{
	if($wax)
	{
		return "dry";
	}
	return "wet";
}
function getWaxes()
{
	global $mysqlConn, $classID;
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
	return "<td>".$wet."</td><td>".$dry."</td>";
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<style>
		table, th, td {
		  border: 1px solid black;
		}
		</style>
		<title>Hardy-Weinberg Class Simulator </title>
	</head>
	<body style="background-image: url('lib/chick.jpeg'); background-repeat: no-repeat;background-color: #a88c76;background-position: center; ">
<?php if($userID){ ?>
	<div>Name: <?=$firstN?> <?=$lastN?></div>
	<div>Person ID: <?=$userID?></div>
	<div>Class ID: <?=$classID?></div>
	<div>Class Name: <?=$className?></div>

	<div style="padding:10px; max-width: 400px; ">
	<div style="background-color: rgba(255,255,255,0.8); padding: 10px; border: 1px solid black;">
	<h1>Ear Wax</h1>
	<div>Your Type: <?=getEarWax($earwax)?></div>
	<h2>Class Results</h2>
	<table style="">
		<tr>
			<th>Wet</th>
			<th>Dry</th>
		</tr>
		<tr>
			<?=getWaxes()?>
		</tr>
	</table>
	<h2>Frequency in School Population</h2>
	<table style="">
		<tr>
			<th>Wet</th>
			<th>Dry</th>
		</tr>
		<tr>
			<td><?=$wetSchool?></td>
			<td><?=$drySchool?></td>
		</tr>
	</table>
	</div>
	</div>
	
	<br>
	
	<table style="border: 0px;"><tr><td style="background-color: rgba(255,255,255,0.8); padding: 10px;">
	<h1>Case 1 - Ideal</h1>
	<?php if(!checkTrial(0))
	{
		?>
	<a href='genold.php?userID=<?=$userID?>&action=add&trial=0');">Generate Initial Genotype</a>
	<?php
	}
	else
	{
		?>
		<table style="">
		<tr>
			<th>Generation</th>
			<th>Genotype</th>
			<th>Mate</th>
		</tr>
		<?=dataGen(0);?>
		</table>
		<?=dataTrial(0,$trialGen[0]);?>
	<a href='genold.php?userID=<?=$userID?>&action=add&trial=0');">Reproduce</a>		
	<?php
	}
	?>
	</td><td style="background-color: rgba(255,255,255,0.8); padding: 10px;">
	<h1>Case 2 - Selection</h1>
	<?php if(!checkTrial(1))
	{
		?>
	<a href='genold.php?userID=<?=$userID?>&action=add&trial=1');">Generate Initial Genotype</a>
	<?php
	}
	else
	{
		?>
		<table style="">
		<tr>
			<th>Generation</th>
			<th>Genotype</th>
			<th>Mate</th>
		</tr>
		<?=dataGen(1);?>
		</table>
		<?=dataTrial(1,$trialGen[1]);?>
	<a href='genold.php?userID=<?=$userID?>&action=add&trial=1');">Reproduce</a>		
	<?php
	}
	?>

</td><td style="background-color: rgba(255,255,255,0.8); padding: 10px;">
	<h1>Case 3 - Heterozygote Advantage</h1>
	<?php
	if(!checkTrial(2))
	{
		?>
	<a href='genold.php?userID=<?=$userID?>&action=add&trial=2');">Generate Initial Genotype</a>
	<?php
	}
	else
	{
		?>
		<table style="">
		<tr>
			<th>Generation</th>
			<th>Genotype</th>
			<th>Mate</th>
		</tr>
		<?=dataGen(2);?>
		</table>
		<?=dataTrial(2,$trialGen[2]);?>
	<a href='genold.php?userID=<?=$userID?>&action=add&trial=2');">Reproduce</a>		
	<?php
	}
	?>
	</td><td style="background-color: rgba(255,255,255,0.8); padding: 10px;">
	<h1>Case 4 - Genetic Drift (Group: <?=$groupID?>)</h1>
	<?php
	if(!checkTrial(3))
	{
		?>
	<a href='genold.php?userID=<?=$userID?>&action=add&trial=3');">Generate Initial Genotype</a>
	<?php
	}
	else
	{
		?>
		<table style="">
		<tr>
			<th>Generation</th>
			<th>Genotype</th>
			<th>Mate</th>
		</tr>
		<?=dataGen(3);?>
		</table>
		<?=dataTrial(3,$trialGen[3]);?>
	<a href='genold.php?userID=<?=$userID?>&action=add&trial=3');">Reproduce</a>		
	<?php
	}
	?>
	</td></tr></table>
	<br><div><a href='genold.php?userID=<?=$userID?>' style='font-size: 175%;'>UPDATE DATA</a></div>
<?php
}
else
{ 
?>
 <p>Try resigning in. <a href="index.html">Go Back</a></p>
<?php
}
?>
  </body>
</html>            