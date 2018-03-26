<?php
/**
 * Created by PhpStorm.
 * User: Theo
 * Date: 09/07/2016
 * Time: 19:34
 */

	# $argv[1] is the list of the product that has been downloaded
	# $argv[2] is the MANIFEST of the product
	$parts = explode("---",$argv[1]);
	$keep = array();
	# Avoiding information about where the product is installed
	for($x=7;$x<count($parts)-1;$x++){
		array_push($keep,$parts[$x]);
	}

	# Process of the manifest
	$versions2 = array();
	$file = fopen("$argv[2]","r");
	while(! feof($file)){
		$removeSpace = preg_replace('/\s+/', ' ',fgets($file));
		$parts = explode(" ",$removeSpace);
		if(count($parts) > 1){
			array_push($versions2,$parts[0]);

		}
	}
	fclose($file);

	# if the two numbers are equal then everything is fine
	if(count($versions2) == count($keep)){
		echo "Everything looks fine\n";
	}else{
		echo "Something is wrong! Please check again!\n";
	}
