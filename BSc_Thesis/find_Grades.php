<html>
<meta http-equiv="Content-Type" content="text/html;charset=utf8">
<?php
 
	function proccess_string($string){
		
		$greek = array('Α','Β','Ε','Η','Ι','Κ','Μ','Ν','Ο','Ρ','Τ','Χ','Υ','Ζ');
		$latin = array('A','B','E','H','I','K','M','N','O','P','T','X','Y','Z');
		$letters_upper_accent = array('Ά', 'Έ', 'Ή', 'Ί', 'Ό', 'Ύ', 'Ώ');
		$letters_upper_accent_to_uppercase = array('Α', 'Ε', 'Η', 'Ι', 'Ο', 'Υ', 'Ω');
		$letters_upper_solvents = array('Ϊ', 'Ϋ');
		$letters_upper_solvents_to_upper = array('Ι','Υ');
		
		$str_and = str_replace('&',' και ',$string);// replace & with και 
		$rem_punctuation = preg_replace('/[^\p{L}\p{N}]/u', ' ', $str_and);//replace any character that is not a letter and not a digit by a space
		$str =(mb_strtoupper($rem_punctuation, 'UTF-8' )); //mb making all greek letters uppercase
		$rep_str = preg_replace('/\s+/u', ' ',$str);//removing extra spaces , added /u , problem with coding 
		$upper = str_replace($latin,$greek,$rep_str);//changing latin to greek letters
		$rem_accent = str_replace($letters_upper_accent,$letters_upper_accent_to_uppercase,$upper);//remove accents
		$rem_solvents = str_replace($letters_upper_solvents,$letters_upper_solvents_to_upper,$rem_accent);//remove solvents
		$final = trim($rem_solvents);//remove spaces at he start and at the end
		
		return $final;
	}
	set_time_limit(0);
	//Connecting to the server
	$host = 'localhost';
	$user = 'root';
	$pass = '';
	$con = mysql_connect($host,$user,$pass) or die("Could not connect to the server\n");

	$db = 'secr';
	mysql_select_db($db);	
	mysql_query("SET NAMES 'utf8'");
	//matching lessons 
	$info = array();
	$sql = mysql_query("SELECT departments.eclassdeptid,departments.deptid,course_data.courseid,course_data.coursetitle,course_data.deptid FROM `course_data`,`departments` WHERE departments.deptid = course_data.deptid");

	
	while($row = mysql_fetch_array($sql)){
		$ec_deptid = $row{'eclassdeptid'};
		$c_courseid = $row{'courseid'};
		$c_title = $row{'coursetitle'};
		$final = proccess_string($c_title);
		$c_deptid = $row{'deptid'};
		$to_array = $ec_deptid.','.$c_courseid.','.$final.','.$final;//change $c_title -> $final
		array_push($info,$to_array);
	}
	//fixing the titles from eclass
	$db = 'eclass';
	mysql_select_db($db);
	$sql = mysql_query("SELECT cours.cours_id,cours.code,cours.intitule FROM `cours`");
	$eclass_lessons = array();
	$eclass_lessons_ids = array();
	while($row = mysql_fetch_array($sql)){
		$id = '';
		$code = '';
		$intitule = '';
		$id = $row{'cours_id'};
		$code = $row{'code'};
		$intitule = $row{'intitule'};
		if(($intitule != '')&&($id != '')&&($code != '')){
			$to_array = proccess_string($intitule);
			$to_array_ids = $id.','.$code;
			array_push($eclass_lessons,$to_array);
			array_push($eclass_lessons_ids,$to_array_ids);
		}
	}
	
	$count = count($info);
	$found_lessons = array();
	for($i=0;$i<$count;$i++){
		$parts = explode(',',$info[$i]);
		if(false !== $key =array_search($parts[2],$eclass_lessons)){
			$parts_ids = explode(',',$eclass_lessons_ids[$key]);
			$to_array = $parts[1].','.$parts[2].','.$parts_ids[0].','.$parts_ids[1];
			array_push($found_lessons,$to_array);
		}
	}
	
	$sql = mysql_query("SELECT user.username FROM `user`");
	$users = array();
	while($row = mysql_fetch_array($sql)){
		$usr = $row{'username'};
		$sub = substr($usr,0,32);
		array_push($users,$sub);
	}
	
	$db='secr';
	mysql_select_db($db);
	$count = count($users);
	$count_lessons = count($found_lessons);
	$tofile ='';
	$ctr = 0;
	for($x=0;$x<$count_lessons;$x++){
		$parts = explode(',',$found_lessons[$x]);
		$sql = mysql_query("SELECT * FROM `student_grades_anon` WHERE student_grades_anon.courseid = '$parts[0]'");
		while($row = mysql_fetch_array($sql)){
			$id = $parts[0];
			$grade = $row{'grade'};
			$year = $row{'year'};
			$student = $row{'student'};
			$eclassid = $parts[3];
			$eclass_name = $parts[1];
			for($i=0;$i<$count;$i++){
				if($student == $users[$i]){
					if($grade == NULL){$grade = 'Δ/Π';};
					$ctr ++;
					$tmp = $eclassid."\t".$id."\t".$grade."\t".$student."\t".$eclass_name."\t".$year;
					$tofile= $tofile.$tmp."\n";
					break;
				}
			}
			
		}
	}
	$file = 'grades.csv';
	file_put_contents($file,$tofile);
	echo "Process is done"."<br>";
	echo $ctr;

?>
</html>