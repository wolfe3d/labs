<!DOCTYPE HTML>
<?php

//For simplicity sake I named the different shapes bunnies, cats and dogs.
$fieldId = intval ($_POST['fieldId']) ;
$stuBlackCircles = intval ($_POST['stuBlackCircles']) ;
$stuRedCircles = intval ($_POST['stuRedCircles']) ;
$stuBlueRectangles = intval ($_POST['stuBlueRectangles']) ;
$stuIndexDiversity = floatval ($_POST['stuIndexDiversity']) ;

//TODO: Only temporary do not use this login on production!!!
$mysqlConn= mysqli_connect('localhost', 'hari', 'rani', 'wolfescience');

/* check connection */
if ($mysqlConn->connect_errno) {
   printf("Connect failed: %s\n", $mysqlConn->connect_error);
   exit();
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
	$numBlueRectangles = $row['cats'];
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
$percentDiffOff = $percentDiff-7;
if ($percentDiffOff>10){
	$percentDiffOff = 10;
}
if ($percentDiffOff<0){
	$percentDiffOff = 0;
}
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
$percentDiffIndex = round($stuIndexDiversity - $speciesDiversityStudent, 2);
$percentDiffIndexOff = round(abs($stuIndexDiversity - $speciesDiversityStudent)*10, 0);
if ($percentDiffIndexOff>2){
	$percentDiffIndexOff = 2;
}
$pointsEarned = round(20 - $percentDiffOff - $percentDiffIndexOff, 0);
if($errorCode=="Field ID does not exist"){
	$pointsEarned = 8;
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
<div>There was an error in my code for students in 2020 that made the red circles and blue rectangles have the same number</div>
 <h1>Field</h1>
  <div style="text-indent:40px">
  <form action='checkField2020.php' method='post'>
	<div>ID: <input type="text" id="fieldId" name="fieldId" value="<?=$fieldId?>"></div>
	<div>Student Number of Small Black Circles: <input type="text" id="stuBlackCircles" name="stuBlackCircles" value="<?=$stuBlackCircles?>"></div>
	<div>Student Number of Medium Blue Rectangles: <input type="text" id="stuBlueRectangles" name="stuBlueRectangles" value="<?=$stuBlueRectangles?>"></div>
	<div>Student Number of Large Red Circles: <input type="text" id="stuRedCircles" name="stuRedCircles" value="<?=$stuRedCircles?>"></div>
	<br>
	<div>Student's Index of diversity: <input type="text" id="stuIndexDiversity" name="stuIndexDiversity" value="<?=$stuIndexDiversity?>"></div>
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
	<br>
	<div>Difference of Index based on Student Data (based on student data) = <?=$percentDiffIndex?></div>
	<br>
	<div>Points earned (out of 20pt) = <?=$pointsEarned?></div>

  </div>
  </body>
</html>            