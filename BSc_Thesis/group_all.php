<html>
<meta http-equiv="Content-Type" content="text/html;charset=utf8">
<?php

	function find_action($array){
		
		$parts=explode("\n",$array);
		$id=array();
		for($x=0;$x<count($parts);$x++){
			$value=explode("\t",$parts[$x]);
			if(count($value)!=1){
				array_push($id,$value[1]);//eclassid
			}
			
		}
		
		$eid=array_keys(array_flip(array_unique($id)));
		$toReturn='';
		for($x=0;$x<count($eid);$x++){
			$sum=0;
			$dev='';
			for($i=0;$i<count($parts);$i++){
				$value=explode("\t",$parts[$i]);
				if(count($value)!=1){
					if($eid[$x]==$value[1]){
						$sum=$sum+$value[3];
						$dev=$value[11]."\t".$value[12];
					}
				}
			}
			$toReturn .= $eid[$x]."\t".$sum."\t".$dev."\n";
		}
		return $toReturn;
	}


	function difference_date($string1,$string2){
		$seconds = strtotime($string1) - strtotime($string2);
		return $seconds;
	}
	
	$file="final_view.csv";
	$contents_view = file_get_contents("".$file);
	$file="final_download.csv";
	$contents_download = file_get_contents("".$file);
	$file="grouped_contents.csv";
	$contents_gcontents = file_get_contents("".$file);
	$file="grades.csv";
	$contents_grades = file_get_contents("".$file);
	$file="wiki_dev.csv";
	$contents_wiki = file_get_contents("".$file);
	
	$parts_grades=explode("\n",$contents_grades);
	$id=array();
	for($x=0;$x<count($parts_grades);$x++){
		$parts=explode("\t",$parts_grades[$x]);
		if(count($parts)!=1){
			array_push($id,$parts[0]);
		}
	}
	
	$getProjects = array_keys(array_flip(array_unique($id)));
	mysql_connect("localhost","root","");
	$exe=array();
	for($x=0;$x<count($getProjects);$x++){
		mysql_select_db($getProjects[$x]);
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
			array_push($exe,$getProjects[$x].' '.$counter."\t".($sum_seconds/86400)/$sum_submit."\t".$sum_grades/$sum_submit."\t".$sum_submit);
		}else{
			array_push($exe,$getProjects[$x].' '.$counter."\t".($sum_seconds/86400)."\t".$sum_grades."\t".$sum_submit);
		}
		
	}
	
	$parts_contents=explode("\n",$contents_gcontents);
	$newgrades='';
	for($x=0;$x<count($parts_grades);$x++){
		$parts=explode("\t",$parts_grades[$x]);
		$pcontents='';
		$pexe='';
		if(count($parts)!=1){
			for($i=0;$i<count($exe);$i++){
				$id=explode(" ",$exe[$i]);
				
				if($parts[0]==$id[0]){
					$pexe=$id[1];
					break;
				}
			}
			for($i=1;$i<count($parts_contents);$i++){
				$id=explode("\t",$parts_contents[$i]);
					if($parts[0]==$id[0]){
						$pcontents=$id[1]."\t".$id[2]."\t".$id[3]."\t".$id[4]."\t".$id[5]."\t".$id[6]."\t".$id[6]."\t".$id[8]."\t".$id[9]."\t".$id[10]."\t".$id[11]."\t".$id[12]."\t".$id[13]."\t".$id[14]."\t".$id[15]."\t".$id[16];
						break;
					}
			}
			$newgrades .= $parts_grades[$x]."\t".$pexe."\t".$pcontents."\n";
		}
	}
	
	$finalGrades='';
	$parts=explode("\n",$newgrades);
	$parts_wiki=explode("\n",$contents_wiki);
	for($x=0;$x<count($parts);$x++){
		$values=explode("\t",$parts[$x]);
		$flag=0;
		if(count($values)!=1){
			for($i=0;$i<count($parts_wiki);$i++){
				$id=explode("\t",$parts_wiki[$i]);
				if($values[0]==$id[0]){
					$flag=1;
					$finalGrades .= $parts[$x]."\t".$id[1]."\n";
					break;
				}
			}
			if($flag==0){
				$finalGrades.= $parts[$x]."\t"."0"."\n";
			}
		}	
	}
	
	file_put_contents('fixed_grades.csv',$finalGrades);
	echo "New grades: Done<br>";
	
	$parts_from_download=find_action($contents_download);
	$parts_from_view=find_action($contents_view);
	
	$tofile='';
	$gr=explode("\n",$finalGrades);
	$pdl=explode("\n",$parts_from_download);
	for($x=0;$x<count($gr);$x++){
		$parts=explode("\t",$gr[$x]);
		$flag=0;
		if(count($parts)!=1){
			for($i=0;$i<count($pdl);$i++){
				$value=explode("\t",$pdl[$i]);
				if($parts[0]==$value[0]){
					$flag=1;
					$tofile.= $gr[$x]."\t".$value[1]."\t".$value[2]."\t".$value[3]."\n";
					break;
				}
			}
			if($flag==0){
				$tofile.= $gr[$x]."\t".'0'."\t".'0'."\t".'0'."\n";
			}
		}
	}
	
	file_put_contents('fixed_grades_download.csv',$tofile);
	echo "grades with download<br>";
	
	$tofile='';
	$pview=explode("\n",$parts_from_view);
	for($x=0;$x<count($gr);$x++){
		$parts=explode("\t",$gr[$x]);
		$flag=0;
		if(count($parts)!=1){
			for($i=0;$i<count($pview);$i++){
				$value=explode("\t",$pview[$i]);
				if($parts[0]==$value[0]){
					$flag=1;
					$tofile.= $gr[$x]."\t".$value[1]."\t".$value[2]."\t".$value[3]."\n";
					break;
				}
			}
			if($flag==0){
				$tofile.= $gr[$x]."\t".'0'."\t".'0'."\t".'0'."\n";
			}
		}
	}
	
	file_put_contents('fixed_grades_view.csv',$tofile);
	echo "grades with view<br>";
?>
</html>