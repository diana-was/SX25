<?php
header('Content-type: image/png'); 

$items = $_GET;
foreach($items as $k=>$v){	
	$itm[] = $v;
	$itmKey[] = $k;
}
$itct = count($items);

	
//-------------------------------- pie chart -------------------------------------
if($itct<=4){
		$total = array_sum($itm);
	if($total==0)
		$total=1;
	$tv = 0;
	foreach($itm as $k=>$v){
		$per = round ($v / $total * 360);
		$pers[] = $per;
		if($v==0||$v==null)
			$itct = $itct-1;
	}
	
	foreach($pers as $k=>$v){
		$tv += $v; 
		$tvs[] = $tv;
	}
	$offset = $itct * 20;
	$handle = imagecreate(220, 180+$offset); 
	$background = imagecolorallocate($handle, 255, 255, 255); 
	
	$white  = imagecolorallocate($handle, 0xC0, 0xC0, 0xC0);	
	$red = imagecolorallocate($handle, 255, 0, 0); 
	$green = imagecolorallocate($handle, 0, 255, 0); 
	$blue = imagecolorallocate($handle, 0, 0, 255); 
	$lights = array($red, $green, $blue, $white);
	
	$gray     = imagecolorallocate($handle, 0xC0, 0xC0, 0xC0);
	$darkred = imagecolorallocate($handle, 150, 0, 0); 
	$darkblue = imagecolorallocate($handle, 0, 0, 150); 
	$darkgreen = imagecolorallocate($handle, 0, 150, 0); 
	$darks = array($darkred, $darkgreen, $darkblue, $gray,  $darkred);/**/// 3D look 
	for ($i = 85; $i > 75; $i--) { 
		imagefilledarc($handle, 110, $i, 190, 75, 0, $tvs[0], $darks[0], IMG_ARC_PIE); 
		for($j=1; $j<($itct-1); $j++){
			imagefilledarc($handle, 110, $i, 190, 75, $tvs[$j-1], $tvs[$j], $darks[$j], IMG_ARC_PIE);
		}
		imagefilledarc($handle, 110, $i, 190, 75, $tvs[$itct-2],  $tvs[$itct-1], $darks[$itct-1], IMG_ARC_PIE);
	} 	
	imagefilledarc($handle, 110, $i, 190, 75, 0, $tvs[0], $lights[0], IMG_ARC_PIE); 
	for($j=1; $j<($itct-1); $j++){
		imagefilledarc($handle, 110, 75, 190, 70, $tvs[$j-1], $tvs[$j], $lights[$j], IMG_ARC_PIE);
	} 
	imagefilledarc($handle, 110, $i, 190, 75, $tvs[$itct-2],  $tvs[$itct-1], $lights[$itct-1], IMG_ARC_PIE);	
	
	$y3 = 150;
	for($i=0;$i<count($items);$i++){
		$ptg = number_format($itm[$i]/$total*100, 1).'%';
		
 		imagefilledrectangle($handle, 15, 150+$i*15, 25, 160+$i*15, $darks[$i]);
		
		imagestring($handle, 2, 20, $y3, ($i+1).'. '.$itmKey[$i].' '.$ptg, $darkblue);
		$y3 += 15;	
	}
	imagepng($handle);	

}
else{                               
	//---------- Bar Graph ------
	$total = array_sum($itm);
	if($total==0)
		$total=1;	
// This array of values is just here for the example.
    $values = $itm; 
// Get the total number of columns we are going to plot
    $columns  = count($values);

// Get the height and width of the final image
    $width = 220;
    $height = 130;
	$offset = $columns * 20;
// Set the amount of space between each column
    $padding = 5;
// Get the width of 1 column
    $column_width = $width / $columns ;

// Generate the image variables
    $im        = imagecreate($width,$height+$offset);
    $gray      = imagecolorallocate ($im,0xcc,0xcc,0xcc);
    $gray_lite = imagecolorallocate ($im,0xee,0xee,0xee);
    $gray_dark = imagecolorallocate ($im,0x7f,0x7f,0x7f);
    $white     = imagecolorallocate ($im,0xff,0xff,0xff);
    	
	$red = imagecolorallocate($im, 255, 0, 0); 
	$green = imagecolorallocate($im, 0, 255, 0); 
	$blue = imagecolorallocate($im, 0, 0, 255); 
	
	$darkred = imagecolorallocate($im, 150, 0, 0); 
	$darkblue = imagecolorallocate($im, 0, 0, 150); 
	$darkgreen = imagecolorallocate($im, 0, 150, 0); 
	$colors = array($red, $gray,$green, $blue, $darkred, $darkblue, $darkgreen, $gray_lite, $red, $gray,$green, $blue, $darkred, $darkblue, $darkgreen, $gray_lite);
// Fill in the background of the image

   imagefilledrectangle($im,0,0,$width,$height+$offset,$white);    
   $maxv = 0;
// Calculate the maximum value we are going to plot

    for($i=0;$i<$columns;$i++)
		$maxv = max($values[$i],$maxv);

// Now plot each column  
    for($i=0;$i<$columns;$i++)
    {
        $column_height = ($height / 100) * (( $values[$i] / $maxv) *100);
        $x1 = $i*$column_width;
        $y1 = $height-$column_height;
        $x2 = (($i+1)*$column_width)-$padding;
        $y2 = $height;
		imagefilledrectangle($im,$x1,$y1,$x2,$y2,$colors[$i]);		
		imagestring($im, 1, $x1+5, $y2+10, $i+1, $darkred);

// This part is just for 3D effect
        imageline($im,$x1,$y1,$x1,$y2,$gray_lite);
        imageline($im,$x1,$y2,$x2,$y2,$gray_lite);
        imageline($im,$x2,$y1,$x2,$y2,$gray_dark);
    }
	
	$y3 = $height+25;
	for($i=0;$i<$columns;$i++){
		$ptg = number_format($itm[$i]/$total*100, 1).'%';
		imagefilledrectangle($im, 15, 155+$i*15, 25, 165+$i*15, $colors[$i]);
		//imagestring($im, 2, 17, $y3, ($i+1).'. '.$itmKey[$i], $darkblue);
		imagestring($im, 2, 20, $y3, ($i+1).'. '.$itmKey[$i].' '.$ptg, $darkblue);
		$y3 += 15;	
	}
    imagepng($im);
}


imagedestroy($image);
?>