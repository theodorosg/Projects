<?php
/**
 * Created by PhpStorm.
 * User: Theo
 * Date: 08/07/2016
 * Time: 13:16
 */

	if($argc > 2){

		$name_of_versions = array();
		$versions = array();
		$file = fopen("$argv[1]","r");
		while(! feof($file)){
			$removeSpace = preg_replace('/\s+/', ' ',fgets($file));
			$parts = explode(" ",$removeSpace);
			if(count($parts) > 1){

				array_push($name_of_versions,$parts[0]);
				array_push($versions,$parts[1]);
			}
		}
		fclose($file);

		$versions2 = array();
		$file = fopen("$argv[2]","r");
		while(! feof($file)){
			$removeSpace = preg_replace('/\s+/', ' ',fgets($file));
			$parts = explode(" ",$removeSpace);
			if(count($parts) > 1){
				array_push($versions2,$parts[1]);
			}
		}
		fclose($file);

		$result_of_comparison = array();
		for($x = 0; $x < count($versions); $x++){
			if(strcmp($versions[$x],$versions2[$x]) ){
			}
		}

		for($x = 0; $x < count($result_of_comparison); $x++){
			echo $result_of_comparison[$x];
		}

	}