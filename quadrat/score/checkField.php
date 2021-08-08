<!DOCTYPE HTML>
<?php
$fieldId = intval ($_POST['fieldId']) ;
$stuBlackCircles = intval ($_POST['stuBlackCircles']) ;
$stuRedCircles = intval ($_POST['stuRedCircles']) ;
$stuBlueRectangles = intval ($_POST['stuBlueRectangles']) ;

require_once ("../connectdb.php");
}
/*check to see if id exists*/
$query = "select `field`, `date`, `width`, `height`, `bunnies`, `cats`, `dogs` from `wolfe_fieldgenerator` where `field` = $fieldId";
$result = $mysqlConn->query($query) or error_log("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");
if (mysqli_num_rows($result)>0) {
	$row = $result->fetch_assoc();
	/*Read old values into variables*/
	$fieldWidth = $row['width'];
	$fieldHeight = $row['height'];
	$numBlackCircles = $row['bunnies'];
	$numRedCircles = $row['cats'];
	$numBlueRectangles = $row['dogs'];
}
else
{
	$errorCode="Field ID does not exist";
}
if ($numBlackCircles>0)
$percentBlackCircles = abs($stuBlackCircles - $numBlackCircles)/$numBlackCircles *100;
if ($numRedCircles>0)
$percentRedCircles  = abs($stuRedCircles - $numRedCircles)/$numRedCircles *100;
if ($numBlueRectangles>0)
$percentBlueRectangles  = abs($stuBlueRectangles - $numBlueRectangles)/$numBlueRectangles *100;

$percentDiff = round(($percentBlackCircles + $percentRedCircles  + $percentBlueRectangles )/3,2);


$stuTotal = $stuBlackCircles + $stuRedCircles + $stuBlueRectangles;
if ($stuTotal >0)
{
	$speciesDiversityStudent = round(1 - ((($stuBlackCircles*($stuBlackCircles-1))+($stuRedCircles*($stuRedCircles-1))+($stuBlueRectangles*($stuBlueRectangles-1)))/($stuTotal*($stuTotal-1))),2);
}
$total = $numBlackCircles + $numRedCircles + $numBlueRectangles;
if ($stuTotal >0)
{
	$speciesDiversityActual = round(1 - ((($numBlackCircles*($numBlackCircles-1))+($numRedCircles*($numRedCircles-1))+($numBlueRectangles*($numBlueRectangles-1)))/($total*($total-1))),2);
}
	?>
<html>
  <head>
  <title>Field Generator</title>
  <meta name="robots" content="noindex">
    <style>
      body {
        margin: 0px;
        padding: 0px;
      }
    </style>
  </head>
  <body>

 <h1>Field</h1>
  <div style="text-indent:40px">
  <form action='checkField.php' method='post'>
	<div>ID: <input type="text" id="fieldId" name="fieldId" value="<?=$fieldId?>"></div>
	<div>Student Number of Small Black Circles: <input type="text" id="stuBlackCircles" name="stuBlackCircles" value="<?=$stuBlackCircles?>"></div>
	<div>Student Number of Medium Blue Rectangles: <input type="text" id="stuBlueRectangles" name="stuBlueRectangles" value="<?=$stuBlueRectangles?>"></div>
	<div>Student Number of Large Red Circles: <input type="text" id="stuRedCircles" name="stuRedCircles" value="<?=$stuRedCircles?>"></div>
	<div><input type="submit" value="Submit"></div>
  </form>
  <br>
  <div>ID = <?=$fieldId?>  
  <span style='color:red'><?=$errorCode?></span>
  </div>
	<div>Width = <?=$fieldWidth?> m</div>
	<div>Height = <?=$fieldHeight?> m</div>
    <div>Number of Small Black Circles = <?=$numBlackCircles?></div>
	<div>Number of Medium Blue Rectangles = <?=$numBlueRectangles?></div>
	<div>Number of Large Red Circles = <?=$numRedCircles?></div>
	<br>
	<div>Percent difference of Small Black Circles = <?=round($percentBlackCircles,2)?>%</div>
	<div>Percent difference of Medium Blue Rectangles = <?=round($percentBlueRectangles ,2)?>%</div>
	<div>Percent difference of Large Red Circles = <?=round($percentRedCircles ,2)?>%</div>
	<br>
	<div>Average Percent difference = <?=$percentDiff?>%</div>
	<br>
	<div>Simpson's index of diversity (based on student data) = <?=$speciesDiversityStudent?></div>
	<div>Simpson's index of diversity (based on actual data) = <?=$speciesDiversityActual?></div>
  </div>
  </body>
</html>            