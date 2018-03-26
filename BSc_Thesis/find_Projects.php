<html>
<meta http-equiv="Content-Type" content="text/html;charset=utf8">
<?php
 
	function difference_date($string1,$string2){
		$seconds = strtotime($string1) - strtotime($string2);
		$days    = floor($seconds / 86400);
		$hours   = floor(($seconds - ($days * 86400)) / 3600);
		$minutes = floor(($seconds - ($days * 86400) - ($hours * 3600))/60);
		$seconds = floor(($seconds - ($days * 86400) - ($hours * 3600) - ($minutes*60)));
		$fix = $days.'-'.$hours.':'.$minutes.':'.$seconds;
		
		return $fix;
	}
 
	$myfile = 'grades.csv';
	$openFile = fopen($myfile,'r') or die($php_errormsg);
	$results = array();
	
	if(file_exists($myfile)){
		while(!feof($openFile)){
			$line = fgets($openFile);	
			$parts = explode("\t",$line);
			array_push($results,$parts[0]);
		}
		fclose($openFile);
	}else{
		echo "Cannot open file : ".$myfile ;	
	}
	set_time_limit(0);
	$host = 'localhost';
	$user = 'root';
	$pass = '';
	$con = mysql_connect($host,$user,$pass) or die("Could not connect to the server\n");
	mysql_query("SET NAMES 'utf8'");
	
	
	$results = array_unique($results);
	$eclass_id = array();
	foreach($results as $key => $value){
		if($value != ''){
			array_push($eclass_id,$value);
		}
	}

	$info = array();
	$count = count($eclass_id);
	for($i=0;$i<$count;$i++){
		$uid = '-';
		$a_id = '-';
		$sub_date = '-';
		$grade = '-';
		$deadline = '-';
		$a_sub_date = '-';
		
		$eclass = $eclass_id[$i];
		mysql_select_db($eclass);
		
		$sql = mysql_query("SELECT assignment_submit.uid, assignment_submit.assignment_id, assignment_submit.submission_date, assignment_submit.grade, assignments.deadline, assignments.submission_date AS a_date FROM `assignment_submit`, `assignments` WHERE assignment_submit.assignment_id = assignments.id");
	
		while($row = mysql_fetch_array($sql)){
			$uid = $row{'uid'};
			$a_id = $row{'assignment_id'};
			$sub_date = $row{'submission_date'};
			$grade = $row{'grade'};
			$deadline = $row{'deadline'};
			$a_sub_date = $row{'a_date'};
			
			$diff_deadline = difference_date($deadline,$sub_date);
			
			if($grade == ''){ $grade = '-';}

			$to_array = $uid.' '.$grade.' '.$diff_deadline.' '.$eclass_id[$i];
			if($uid != '-'){
				array_push($info,$to_array);
			}
		}
	}

	mysql_select_db('eclass'); //getting users
	$count = count($info);
	$tofile = '';
	for($i=0;$i<$count;$i++){
		$parts = explode(' ',$info[$i]);
		$username = '-';
		
		$sql = mysql_query("SELECT user.username FROM `user` WHERE user.user_id = '$parts[0]'");
		while($row = mysql_fetch_array($sql)){
			$username = $row{'username'};
			$sub = substr($username,0,32);
		}
		
		if($username != '-'){
			$tmp = $parts[3]."\t".$sub."\t".$parts[1]."\t".$parts[2];
			$tofile = $tofile.$tmp."\n";
		}
	}
	
	$file = 'projects.csv';
	file_put_contents($file,$tofile);
	echo "Process is done"."<br>";
?>
</html>