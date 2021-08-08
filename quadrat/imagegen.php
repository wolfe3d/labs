<?php
function makeImage($db, $fieldWidth, $fieldHeight, $numRedCircles, $numBlackCircles, $numBlueRectangles)
{
$dir = "../../fields/";

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

$bytes = random_bytes(20);
$filename = bin2hex($bytes) . ".png";

//TODO: Check to make sure no other files have the same filename in the database

// output the picture
/*header('Content-Disposition: Attachment;filename=image.png');
header('Content-type: image/png');*/
imagepng($image, $dir.$filename);
imagedestroy($image);
return  $filename;
}
?>
