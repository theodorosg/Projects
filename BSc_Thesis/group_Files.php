<html>
<meta http-equiv="Content-Type" content="text/html;charset=utf8">
<?php
	
	function difference_date($string1,$string2){
		$seconds = strtotime($string1) - strtotime($string2);

		return $seconds;
	}
	
	// Function to calculate square of value - mean
	function sd_square($x, $mean){ 
		return pow($x - $mean,2); 
	}

	// Function to calculate standard deviation (uses sd_square)    
	function sd($array){
		// square root of sum of squares devided by N-1
		return sqrt(array_sum(array_map("sd_square", $array, array_fill(0,count($array), (array_sum($array) / count($array)) ) ) ) / (count($array)-1) );
	}
	
	
	function calc($file){

		$parts=explode("\n",$file);
		$eclassid=array();
		for($x=0;$x<count($parts);$x++){
			$values=explode("\t",$parts[$x]);
			if(count($values)!=1){
				array_push($eclassid,$values[1]);

			}
			
		}

		$tmp = array_keys(array_flip(array_unique($eclassid)));

		mysql_connect("localhost","root","");
		$exe=array();
		for($x=0;$x<count($tmp);$x++){
			mysql_select_db($tmp[$x]);
			mysql_query("SET NAMES 'utf8'");
			$counter=0;
			$sum_grades=0;
			$sum_seconds=0;
			$sum_submit=0;
			$query=mysql_query("SELECT assignments.title FROM assignments");
			while($row = mysql_fetch_array($query)){
				$counter++;
			}
			
			$sql = mysql_query("SELECT assignment_submit.assignment_id, assignment_submit.submission_date, assignment_submit.grade, assignments.deadline FROM `assignment_submit`, `assignments` WHERE assignment_submit.assignment_id = assignments.id");
			while($row = mysql_fetch_array($sql)){
				$sub_date = $row{'submission_date'};
				$grade = $row{'grade'};
				$deadline = $row{'deadline'};
				
				$sum_submit++;
				$sum_seconds=$sum_seconds+	difference_date($deadline,$sub_date);
				$sum_grades=$sum_grades+$grade;

			}
			if($sum_submit!=0){
				array_push($exe,$tmp[$x].' '.$counter."\t".($sum_seconds/86400)/$sum_submit."\t".$sum_grades/$sum_submit."\t".$sum_submit);
			}else{
				array_push($exe,$tmp[$x].' '.$counter."\t".($sum_seconds/86400)."\t".$sum_grades."\t".$sum_submit);
			}
			
		}

		$toCalcDev=array();
		for($x=0;$x<count($tmp);$x++){
			$sum=0;
			$counter=0;
			$deviation=array();
			for($i=0;$i<count($parts);$i++){
				$values=explode("\t",$parts[$i]);
				if(count($values)!=1){
					if($values[1]==$tmp[$x]){
						$temp=strtotime($values[4])+strtotime($values[5]);
						$sum=$sum+$temp;
						array_push($deviation,$sum);
						$counter++;
					}
				}
			}
			if($counter!=1){
				array_push($toCalcDev,floor(($sum/86400)/$counter)."\t".floor((sd($deviation)/86400)/$counter));
			}else{
				array_push($toCalcDev,floor(($sum/86400)/$counter)."\t".floor(($sum/86400)/$counter));
			}
			
		}
		
		$toReturn='';
		for($x=0;$x<count($parts);$x++){
			$values=explode("\t",$parts[$x]);
			if(count($values)!=1){
				for($i=0;$i<count($exe);$i++){
					$id=explode(" ",$exe[$i]);
					if($values[1]==$id[0]){
						echo $parts[$x].' '.$id[1].' '.$toCalcDev[$i]."<br>";
						$toReturn .= $parts[$x]."\t".$id[1]."\t".$toCalcDev[$i]."\n";
						break;
					}
				}
			}		
		}

		echo "<br><br>";
		return $toReturn;
	}
	
	
	
	function fix_file($file){
		$toReturn = '';
		$parts = explode("\n",$file); //each line of the file
		for($j=0;$j<count($parts);$j++){
			$info = explode("\t",$parts[$j]);
			if(count($info)!=1){//each files ends with a blank line and causes some notices
				$file ='';
				$lesson ='';
				for($x=0;$x<count($info);$x++){					
					if($x==3){
						$tmp = trim($info[3]);
						$split = explode(' ',$tmp);
						$lesson = $split[0];

						for($i=1;$i<count($split);$i++){
							$file = $file.' '.$split[$i];
						}
	
					}
				}
				
				$toReturn .= $info[0]."\t".$lesson."\t".$file."\t".$info[4]."\t".$info[5]."\t".$info[6]."\t".$info[7]."\n";
			}
		}
		return $toReturn;
	}
	
	$myDir = '../../../csvx';
	
	$download = '';
	$view = '';
	ini_set('memory_limit', '2048M');
	set_time_limit(0);
	if(is_dir($myDir)){
		if($dh = opendir($myDir)){
			while(($file = readdir($dh)) !== false){
				if(preg_match('/csv/',$file)){
					if(preg_match('/download/',$file)){
						$download .= file_get_contents('../../../csvx/' . $file);
					}else if(preg_match('/view/',$file)){
						$view .= file_get_contents('../../../csvx/' . $file);
					}		
				}
			}
			closedir($dh);
		}
	}
	
	echo 'Grouping done'."<br>";
	$fix_download = fix_file($download);
	$fix_view = fix_file($view);
	file_put_contents('fixed_download.csv',$fix_download);
	file_put_contents('fixed_view.csv',$fix_view);
	file_put_contents('final_view.csv',calc($fix_view));
	file_put_contents('final_download.csv',calc($fix_download));
	

?>
</html>