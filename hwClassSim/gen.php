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
	}
	else
	{
			echo "User does not exist.";
			exit();
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
					header("Location:gen.php?userID=$personID");
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
		//return "block";
	} 
	else
	return 0;
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
$trialGen=[0,0,0,0];
function dataGen($trial)
{
	global $mysqlConn, $userID, $trialGen;
	$query = "SELECT * FROM `wolfe_generations` WHERE `trial` = $trial AND `user1` = $userID ORDER BY `gen`;";
	$result = $mysqlConn->query($query) or error_log("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");
	$outputStr = "";
	while ($row = $result->fetch_assoc()):
		$mateName = getMateName($row['user2']);
		$outputStr .= "<tr><td>".$row['gen']."</td><td>".getGenotypeString($row['genotype'])."</td><td>".$mateName."</td></tr>";
		$trialGen[$trial] = $row['gen'];
	endwhile;
	return $outputStr;
}

function getEarWax($wax)
{
	if($wax)
	{
		return "dry";
	}
	return "wet";
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="Pragma" content="no-cache">
		<script src="lib/jquery.js"></script>
		<script type="text/javascript">
			
			$().ready(function() {
				$("#trialbutton0").click(function(e) {
					e.preventDefault();
					addGen("0");
				});
				$("#trialbutton1").click(function(e) {
					e.preventDefault();
					addGen("1");
				});
				$("#trialbutton2").click(function(e) {
					e.preventDefault();
					addGen("2");
				});
				$("#trialbutton3").click(function(e) {
					e.preventDefault();
					addGen("3");
				});
				$("#trialSummarybutton0").click(function(e) {
					e.preventDefault();
					updateTrial("0");
				});
				$("#trialSummarybutton1").click(function(e) {
					e.preventDefault();
					updateTrial("1");
				});
				$("#trialSummarybutton2").click(function(e) {
					e.preventDefault();
					updateTrial("2");
				});
				$("#trialSummarybutton3").click(function(e) {
					e.preventDefault();
					updateTrial("3");
				});
				$("#earbutton").click(function(e) {
					e.preventDefault();
					// validate signup form on keyup and submit
					var request = $.ajax({
						url: "earData.php",
						cache: false,
						method: "POST",
						data: { classID: '<?=$classID?>'},
						dataType: "html"
					});

					request.done(function( html ) {
						$( "#earData").html(html);
					});

					request.fail(function( jqXHR, textStatus ) {
						alert( "Request failed: " + textStatus );
					});
				});
			});
			function addGen(trialNumber)
			{
				// validate signup form on keyup and submit
				var request = $.ajax({
					url: "addgen.php",
					cache: false,
					method: "POST",
					data: { trial : trialNumber, userID: '<?=$userID?>', classID: '<?=$classID?>', groupID: '<?=$groupID?>' },
					dataType: "html"
				});

				request.done(function( msg ) {
					
					alert (msg);
					var outputMsg = JSON.parse(msg);
					if(outputMsg.error!==undefined)
					{
						alert(outputMsg.error);
					}
					else
					{
						alert (outputMsg.genID);
						var outputStr = ""<tr><td>"+outputMsg.gen+"</td><td></td><td></td></tr>";
						
						$( "#trial"+trialNumber ).show();
						$( "#trialbutton"+trialNumber).html('Reproduce');
						if (outputMsg.gen==10)
						{
							$("#trialbutton"+trialNumber).hide();
						}
						$( "#trial"+trialNumber ).append(outputStr);
						updateTrial(trialNumber);
					}
				});

				request.fail(function( jqXHR, textStatus ) {
					alert( "Request failed: " + textStatus );
				});
			}
			function updateTrial(trialNumber)
			{
				// validate signup form on keyup and submit
				var request = $.ajax({
					url: "checkGen.php",
					cache: false,
					method: "POST",
					data: { trial : trialNumber, userID: '<?=$userID?>', classID: '<?=$classID?>', groupID: '<?=$groupID?>' },
					dataType: "html"
				});

				request.done(function( html ) {
					$( "#trialSummary"+trialNumber ).html(html);
					$( "#trialSummary"+trialNumber ).show();
				});

				request.fail(function( jqXHR, textStatus ) {
					alert( "Request failed: " + textStatus );
				});
			}
		</script>
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
		<tr id="earData">
			<?php include 'earData.php';?>
		</tr>
	</table>
	<button id="earbutton" value="val_ear" name="earbutton">Update Data</button>
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
	
	<table style="border: 0px;"><tbody><tr>
	<?php 
	$trialNames = ["Case 1 - Ideal", "Case 2 - Selection", "Case 3 - Heterozygote Advantage", "Case 4 - Genetic Drift (Group $groupID)"];
	for ($trial=0; $trial<=3; $trial++)
	{
	$currentGen = checkTrial($trial); //calling the same database calls in checkGen. TODO: Remove one call.
	$buttonVis = "block";
	if($currentGen)
	{
		$tableVis="block";
		$buttonText = "Reproduce";
	}
	else
	{
		$tableVis="none";
		$buttonText = "Generate Initial Genotype";
	}
	if($currentGen>10)
	{
		$buttonVis = "none";
	}
	?>	
	<td style="background-color: rgba(255,255,255,0.8); padding: 10px;">
	<h1><?=$trialNames[$trial]?></h1>
	<table style="display: <?=$tableVis?>" id='trial<?=$trial?>'>
		<tr>
			<th>Generation</th>
			<th>Genotype</th>
			<th>Mate</th>
		</tr>
		<?=dataGen($trial);?>
	</table>
	<button id="trialbutton<?=$trial?>" value="val_<?=$trial?>" name="trialbutton<?=$trial?>" style="display: <?=$buttonVis?>" ><?=$buttonText?></button>
	<div id="trialSummary<?=$trial?>" style="color:blue; display: <?=$tableVis?>" ><?php include 'checkGen.php';?></div>
	<button id="trialSummarybutton<?=$trial?>" value="val_<?=$trial?>" name="trialSummarybutton<?=$trial?>" style="display: <?=$tableVis?>" >Update Generation Data</button>
	</td>
	
	<?php 
	}
	?>
	
	</td></tr></tbody></table>
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