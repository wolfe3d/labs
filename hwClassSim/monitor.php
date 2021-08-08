<?php
require_once  ("../../connectdb.php");

if(is_null($_REQUEST["classID"]))
{
	$query = "SELECT * FROM `wolfe_classes`";
	$result = $mysqlConn->query($query) or error_log("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");
	$outputStr = "<!DOCTYPE html><html lang='en'><head></head><body>";
	while ($row = $result->fetch_assoc()):
	{
		$outputStr .="<div><a href='monitor.php?classID=".$row['id']."'>Class Code: ".$row['code']."</a></div>";
	}
	endwhile;
	//echo "No class Id";
	$outputStr .= "</body></html>";
	echo $outputStr;
	exit();
}
$classID = intval($_REQUEST["classID"]);
$query = "SELECT * FROM `wolfe_classes` WHERE `id` = $classID ";
$result = $mysqlConn->query($query) or error_log("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");
$row = $result->fetch_assoc();
if($row)
{
	$className = $row["name"];
	$user = $row["user"];
	$code = $row["code"];
	$wetSchool = $row['wet'];
	$drySchool = $row['dry'];
}
else
{
	echo "Invalid Class Id";
	exit();
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
$totalStudents = 0;
function getStudents()
{
	global $mysqlConn, $classID, $totalStudents;
	$query = "SELECT * FROM `wolfe_users` WHERE `classID` = $classID ORDER BY `last`;";
	$result = $mysqlConn->query($query) or error_log("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");
	$tableStr = "";
	while ($row = $result->fetch_assoc()):
		$tableStr .= "<tr><td>".$row['id']."</td><td>".$row['first']."</td><td>".$row['last']."</td><td>".$row['email']."</td><td>".$row['identity']."</td><td>".getEarWax($row['earwax'])."</td><td>".$row['groupID']."</td><td><a href='gen.php?userID=".$row['id']."'>userpage</a></td></tr>";
		$totalStudents +=1;
	endwhile;
	return $tableStr;
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
function sumGenotypes($genotype, $generation)
{
	if($genotype==1)
	{
		$generation[1] +=1;
	}
	else if ($genotype==2)
	{
		$generation[2] +=1;
	}
	else
	{
		$generation[0] +=1;
	}
	return $generation;
}
function dataGenCalc($generation)
{
	$totalGenotypes = array_sum($generation);
	$outputStr = "";
	if($totalGenotypes)
	{
		$outputStr .="<tr><th>AA</th><th>Aa</th><th>aa</th><th>total</th></tr>";
		$outputStr .="<tr><td>".$generation[0]."</td><td>".$generation[1]."</td><td>".$generation[2]."</td><td>".$totalGenotypes."</td></tr>";
		$outputStr .="<tr><td>".$generation[0]/$totalGenotypes."</td><td>".$generation[1]/$totalGenotypes."</td><td>".$generation[2]/$totalGenotypes."</td></tr>";
		$outputStr .="<tr><th>p</th><td>".($generation[0]*2+$generation[1])/($totalGenotypes*2)."</td><th>q</th><td>".($generation[2]*2+$generation[1])/($totalGenotypes*2)."</td></tr>";
	}
	return $outputStr;
}
//TODO: Use data collected to draw graphs
function dataGen($trial)
{
	//Use this for Trial 4
	global $mysqlConn, $classID, $trialGen;
	$trialFour = "";
	if($trial==3)
	{
		$trialFour = "`groupID`, ";
	}
	$query = "SELECT * FROM `wolfe_generations` WHERE `trial` = $trial AND `classID` = $classID ORDER BY $trialFour`gen`;";
	$result = $mysqlConn->query($query) or error_log("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");
	$outputStr = "";
	$gen = -1;
	$group = -1;
	$generationGenotypes = [0,0,0];
	while ($row = $result->fetch_assoc()):
		if($trial==3)
		{
			if($group!=intval($row['groupID']))
			{
				if($group>-1)
				{
					//end of last generation, so output the total data
					$outputStr .= dataGenCalc($generationGenotypes);
				}
				$generationGenotypes = [0,0,0];
				$group = intval($row['groupID']);
				$outputStr .="<tr><th colspan='4' style='font-size: 175%; color: #426cf5;'>Group ".$group."</th></tr>";
			}
		}
		if($gen!=intval($row['gen']))
		{
			$gen = intval($row['gen']);
			if($gen>0)
			{
				//end of last generation, so output the total data
				$outputStr .= dataGenCalc($generationGenotypes);
			}
			//write new Generation Title
			$generationGenotypes = [0,0,0];
			$outputStr .="<tr><th colspan='4' style='font-size: 150%;'>Generation ".$gen."</th></tr>";
			$outputStr .="<tr><th>Id</th><th>User</th><th>Genotype</th><th>Mate</th><th>Mating Attempts</th></tr>";
		}
		$generationGenotypes = sumGenotypes(intval($row['genotype']), $generationGenotypes);
		$outputStr .= "<tr><td>".$row['id']."</td><td>".getMateName($row['user1'])."</td><td>".getGenotypeString(intval($row['genotype']))."</td><td>".getMateName($row['user2'])."</td><td>".$row['tries']."</td></tr>";
	endwhile;
	$outputStr .= dataGenCalc($generationGenotypes);
	echo $outputStr;
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<style>
		table, th, td {
		  border: 1px solid black;
		}
		.center {
			margin-left: auto;
			margin-right: auto;
		}
		</style>
		<title>Hardy-Weinberg Class Simulator </title>
	</head>
	<body style="background-image: url('lib/chick.jpeg'); background-repeat: no-repeat;background-color: #a88c76;background-position: center; ">
		<div>Class Name: <?=$className?></div>
		<div>User ID: <?=$user?></div>
		<div>Class Code: <?=$code?></div>

		<div style="padding:10px; max-width: 1000px; display: block; margin-left: auto; margin-right: auto">
		<div style="background-color: rgba(255,255,255,0.8); padding: 10px; border: 1px solid black;">
		<h1>Students in Class</h1>
		<table class="center">
			<tr>
				<th>ID</th>
				<th>First</th>
				<th>Last</th>
				<th>Email</th>
				<th>Identity</th>
				<th>Earwax</th>
				<th>GroupID</th>
				<th>Link</th>
			</tr>
			<?=getStudents();?>
		</table>
		<div>Total Students: <?=$totalStudents?></div>
		</div>
		</div>


		<div style="padding:10px; max-width: 1000px; display: block; margin-left: auto; margin-right: auto">
		<div style="background-color: rgba(255,255,255,0.8); padding: 10px; border: 1px solid black;">
		<h1>Ear Wax</h1>
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
		<h2>School Population</h2>
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

		<div style="padding:10px; max-width: 1000px; display: block; margin-left: auto; margin-right: auto">
		<div style="background-color: rgba(255,255,255,0.8); padding: 10px; border: 1px solid black;">
		<h1>Case 1 - Ideal</h1>
		<table class="center">
			<?=dataGen(0)?>
		</table>
		</div>
		</div>

		<div style="padding:10px; max-width: 1000px; display: block; margin-left: auto; margin-right: auto">
		<div style="background-color: rgba(255,255,255,0.8); padding: 10px; border: 1px solid black;">
		<h1>Case 2 - Selection</h1>
		<table class="center">
			<?=dataGen(1)?>
		</table>
		</div>
		</div>

		<div style="padding:10px; max-width: 1000px; display: block; margin-left: auto; margin-right: auto">
		<div style="background-color: rgba(255,255,255,0.8); padding: 10px; border: 1px solid black;">
		<h1>Case 3 - Heterozygote Advantage</h1>
		<table class="center">
			<?=dataGen(2)?>
		</table>
		</div>
		</div>

		<div style="padding:10px; max-width: 1000px; display: block; margin-left: auto; margin-right: auto">
		<div style="background-color: rgba(255,255,255,0.8); padding: 10px; border: 1px solid black;">
		<h1>Case 4 - Genetic Drift</h1>
		<table class="center">
			<?=dataGen(3)?>
		</table>
		</div>
		</div>
	</body>
</html>
