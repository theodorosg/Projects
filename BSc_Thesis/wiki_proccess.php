<html>
<meta http-equiv="Content-Type" content="text/html;charset=utf8">
<?php

	// Function to calculate square of value - mean
	function sd_square($x, $mean){ 
		return pow($x - $mean,2); 
	}

	// Function to calculate standard deviation (uses sd_square)    
	function sd($array){
		// square root of sum of squares devided by N-1
		return sqrt(array_sum(array_map("sd_square", $array, array_fill(0,count($array), (array_sum($array) / count($array)) ) ) ) / (count($array)-1) );
	}
	
	function normArray($avg,$array){//normalize the values of an array
		$nArray=array();
		for($x=0;$x<count($array);$x++){
			array_push($nArray,$array[$x]/$avg);
		}
		return $nArray;
	}

	function is_multi($a) { //check if the array is multidimensional 
		foreach ($a as $v) {
			if (is_array($v)){
				return true;
			} 
		}
		return false;
	}

	function calc_days($timestamp,$timestamp0){
		$seconds = strtotime($timestamp) - strtotime($timestamp0);
		$days    = floor($seconds / 86400);
		return abs($days);
	}
	
	function standard_dev($array){
		if(count($array)!=0){
			$parts=explode("-",$array[0]);
			if($parts[1].'-'.$parts[2] > '09-24'){
				$sem_s = '09-24';
				$sem_e = '02-13';
			}else if($parts[1].'-'.$parts[2] < '02-14'){
				$sem_s = '09-24';
				$sem_e = '02-13';
			}else{
				$sem_s = '02-14';
				$sem_e = '09-23';
			}
			$vectors=array();
			$tmstp = $parts[0].'-'.$parts[1].'-'.$parts[2];

			//------------
			$pointers=array();
			for($x=1;$x<count($array);$x++){
				if(((int)abs((strtotime($array[$x-1]) - strtotime($array[$x]))/(60*60*24*30))) > 6){
					array_push($pointers,$x);
				}
			}
			//------------
			$disToSem=array();
			if(count($pointers)!=0){
				for($x=0;$x<count($pointers);$x++){
					$tmp=array();
					if($x==0){//start
						for($i=0;$i<$pointers[$x];$i++){
							array_push($tmp,$array[$i]);
						}
						array_push($disToSem,$tmp);
					}else{//middle
						for($i=$pointers[$x-1];$i<$pointers[$x];$i++){
							array_push($tmp,$array[$i]);
						}
						array_push($disToSem,$tmp);
					}
					if($x+1==count($pointers)){//end
						for($i=$pointers[$x];$i<count($array);$i++){
							array_push($tmp,$array[$i]);
						}
						array_push($disToSem,$tmp);
					}

				}
				
				for($x=0;$x<count($disToSem);$x++){
					$tmp=array();
					$parts=explode("-",$disToSem[$x][0]);
					array_push($tmp,calc_days($parts[0].'-'.$sem_s,$disToSem[$x][0]));//1st value from the start of the semester
					
					for($i=1;$i<count($disToSem[$x]);$i++){				
						if($i!=(count($disToSem[$x]))-1){//size of the array							
							array_push($tmp,calc_days($disToSem[$x][$i-1],$disToSem[$x][$i]));
						}else{
							$parts=explode("-",$disToSem[$x][$i]);
							array_push($tmp,calc_days($parts[0].'-'.$sem_e,$disToSem[$x][$i]));
						}
					}
					array_push($vectors,$tmp);
				}
			}else{
				array_push($vectors,calc_days($parts[0].'-'.$sem_s,$tmstp));
				for($x=1;$x<count($array);$x++){
					if($x!=(count($array)-1)){
						$parts=explode("-",$array[$x]);
						array_push($vectors,calc_days($array[$x-1],$array[$x]));
					}else{
						$parts=explode("-",$array[$x]);
						array_push($vectors,calc_days($parts[0].'-'.$sem_e,$array[$x]));
					}
				}
			}
			
			if(is_multi($vectors)){
				$cnt=0;
				$sdSum=0;
				for($x=0;$x<count($vectors);$x++){
					$sum=0;
					for($i=0;$i<count($vectors[$x]);$i++){
						$sum = $sum + $vectors[$x][$i];
					}
					$avg = $sum/count($vectors[$x]);
					$tmp=array();
					$tmp=normArray($avg,$vectors[$x]);
					$sdSum = $sdSum + sd($tmp);
					$cnt++;
				}
				
				return $sdSum/$cnt;
			}else{
				$sum=0;
				for($x=0;$x<count($vectors);$x++){
					$sum = $sum + $vectors[$x];
				}
				$avg = $sum/count($vectors);
				$tmp=array();
				$tmp=normArray($avg,$vectors);
				return sd($tmp);
			}
		}
	}
	
	function get_wiki_contents($db){
		mysql_connect('localhost','root','') or die("Could not connect to the root\n");
		mysql_select_db($db);
		mysql_query("SET NAMES 'utf8'");
		
		$times = array();
		$sql = mysql_query("SELECT wiki_pages_content.mtime FROM wiki_pages_content ORDER BY wiki_pages_content.mtime ASC");
		while($row = mysql_fetch_array($sql)){
			$parts = explode(" ",$row{'mtime'}); //getting only the date
			array_push($times,$parts[0]);
		}
		return standard_dev($times);
			
	}


	$myfile = 'wiki_content.csv';
	
	$contents = file_get_contents(''.$myfile);
	$toFile='';
	$parts = explode("\n",$contents);
	for($x=0;$x<count($parts);$x++){
		$numWiki = explode("\t",$parts[$x]);
		if(count($numWiki)!=1){
			if($numWiki[1]!=0){
				$toFile .= $numWiki[0]."\t".get_wiki_contents($numWiki[0])."\n";
			}else{
				$toFile .= $numWiki[0]."\t"."0\n";
			}
		}
	}
	echo "Wiki standard deviation done!<br>";
	file_put_contents('wiki_dev.csv',$toFile);
?>
</html>