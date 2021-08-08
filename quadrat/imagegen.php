<?php
require_once  ("../../connect_db.php");

$fieldId=intval($_GET['field']);
/*check to see if id exists*/
$query = "select * from `wolfe_fieldgenerator` where `field` = $fieldId";
$result = $mysqlConn->query($query) or error_log("\n<br />Warning: query failed:$query. " . $mysqlConn->error. ". At file:". __FILE__ ." by " . $_SERVER['REMOTE_ADDR'] .".");
if (mysqli_num_rows($result)>0) {
	$row = $result->fetch_assoc();
	/*Read old values into variables*/
	$fieldWidth = $row['width'];
	$fieldHeight = $row['height'];
	$numRedCircles = $row['red'];
	$numBlackCircles = $row['black'];
	$numBlueRectangles = $row['blue'];
}

// create a blank image
$image = imagecreatetruecolor($fieldWidth, $fieldHeight);

imagealphablending($image, true);
imagesavealpha($image, true);
// fill the background color
$bg = imagecolorallocatealpha($image, 0, 0, 0, 127);
imagefill($image, 0, 0, $bg);

//$bg = imagecolorallocate($image, 0, 100, 0);

// choose a color for the ellipse
$col_ellipse = imagecolorallocate($image, 0, 0, 0); //black

for ($i = 0; $i < $numBlackCircles; $i++)
{
	$randomX = rand(0,$fieldWidth);
	$randomY = rand(0,$fieldHeight);
	// draw the ellipse
	imageellipse($image, $randomX, $randomY, 4, 4, $col_ellipse);
}

$col_ellipse = imagecolorallocate($image, 255, 0, 0);  //red
for ($i = 0; $i < $numRedCircles; $i++)
{
	$randomX = rand(0,$fieldWidth);
	$randomY = rand(0,$fieldHeight);
	// draw the ellipse
	imageellipse($image, $randomX, $randomY, 8, 8, $col_ellipse);
}

$col_ellipse = imagecolorallocate($image, 0, 0, 255); //blue
$rectwidth = 3;
for ($i = 0; $i < $numBlueRectangles; $i++)
{
	$randomX = rand(0,$fieldWidth-$rectwidth );
	$randomY = rand(0,$fieldHeight-$rectwidth );
	// draw the ellipse
	imagerectangle($image, $randomX, $randomY, $randomX+$rectwidth, $randomY+$rectwidth, $col_ellipse);
}


// output the picture
header('Content-Disposition: Attachment;filename=image.png');
header('Content-type: image/png');
imagepng($image);
imagedestroy($image);
?>
