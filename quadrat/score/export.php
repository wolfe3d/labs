<?php
require_once ("../connectdb.php");
$teacherID = $mysqlConn->real_escape_string($_POST['teacher']) ;
$passCode = $mysqlConn->real_escape_string($_POST['passMe']) ;


/*Check Teacher Passcode*/
$query = "SELECT * FROM `wolfe_teachers` WHERE `email` LIKE '$teacherID' AND `passcode` LIKE '$passCode' AND `access` LIKE 'quadrat' ";
$result = $mysqlConn->query($query) or error_log("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");
if (mysqli_num_rows($result)>0)
{
	header('Content-Description: File Transfer');
	header("Content-Type: application/csv") ;
	header("Content-Disposition: attachment; filename=Report.csv");
	$out = fopen('php://output', 'w');

	fputcsv($out, array('last','first','class','teacher','percentage','grade','width', 'height', 'black diamonds', 'red circles', 'blue squares', 'Simpsons Index', 'quadrat side length', 'number of quadrats', 'student black', 'student red', 'student blue', 'student Simpson', 'Simpson Diversity based on Student Numbers', '%diff Black Diamonds', '%diff Red Circles', '%diff Blue Squares','%diff Total'));

	/*search for teacherId*/
	$query = "select * from `wolfe_fieldgenerator` where `teacher`='$teacherID'";
	$result = $mysqlConn->query($query) or error_log("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");
	if (mysqli_num_rows($result)>0) {
		while ($row = $result->fetch_assoc()):
			if ($row['black']>0)
			{
				$percentBlackCircles = abs($row['studentBlack'] - $row['black'])/$row['black'] *100;
				$percentRedCircles  = abs($row['studentRed']- $row['red'])/$row['red'] *100;
				$percentBlueRectangles  = abs($row['studentBlue'] - $row['blue'])/$row['blue'] *100;

				$percentDiff = round(($percentBlackCircles + $percentRedCircles  + $percentBlueRectangles )/3,0);


				$total = $row['black'] + $row['red'] + $row['blue'];
				$stuTotal = $row['studentBlack'] + $row['studentRed'] + $row['studentBlue'];
				//actual diversity index
				if ($total>0)
				{
					$speciesDiversityActual = round(1 - ((($row['black']*($row['black']-1))+($row['red']*($row['red']-1))+($row['blue']*($row['blue']-1)))/($total*($total-1))),2);
				} 			
				
				$percentage=0;
				
				//calculates Simson's index using student values
				$speciesDiversityStudent =0;
				$speciesDiversityPercentDiff = 100;
				if ($stuTotal>0)
				{
					$speciesDiversityStudent = round(1 - ((($row['studentBlack'] *($row['studentBlack'] -1))+($row['studentRed']*($row['studentRed']-1))+($row['studentBlue'] *($row['studentBlue'] -1)))/($stuTotal*($stuTotal-1))),2);
					if($percentDiff<50)
					{
						$percentage += (100-$percentDiff)*.80;
					}
					else
					{
						$percentage += 50*.80; //give half credit for trying
					}
				} 
				if(!empty($row['studentSimpson']))
				{
					$speciesDiversityPercentDiff = (abs($row['studentSimpson'] - $speciesDiversityStudent)/$speciesDiversityStudent) *100;
					if($speciesDiversityPercentDiff<50)
					{
						$percentage += (100-$speciesDiversityPercentDiff)*.20;
					}
					else
					{
						$percentage += 50*.20; //give half credit for trying
					}
				}

				$gradePoints = 30; //determined by teacher
				$grade = round(($percentage/100)*$gradePoints,0);
				
				$percentBlackCircles = round($percentBlackCircles,1);
				$percentRedCircles  = round($percentRedCircles,1);
				$percentBlueRectangles  = round($percentBlueRectangles,1);
				fputcsv($out, array($row['last'],$row['first'],$row['class'],$row['teacher'],$percentage, $grade,$row['width'],$row['height'],$row['black'],$row['red'],$row['blue'],$speciesDiversityActual,$row['sideLengthQuadrat'],$row['numQuadrats'],$row['studentBlack'],$row['studentRed'],$row['studentBlue'],$row['studentSimpson'], $speciesDiversityStudent, $percentBlackCircles, $percentRedCircles, $percentBlueRectangles, $percentDiff ));
			}
		endwhile;
	}
	else
	{
		echo "error";
	}
	fclose($out);
}
else
{
	echo "0";
}
?>