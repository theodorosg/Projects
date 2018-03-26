<html>
<meta http-equiv="Content-Type" content="text/html;charset=utf8">
<?php
 
	function my_decode_url($string, $quote_style = ENT_COMPAT, $charset = "utf-8"){
		$string  = urldecode($string);
		$string = html_entity_decode($string, $quote_style, $charset);
		$string = preg_replace_callback('~&#x([0-9a-fA-F]+);~i', "chr_utf8_callback", $string);
		$string = preg_replace_callback('~[\\\\]x([0-9a-fA-F][0-9a-fA-F])~', "chr_utf8_callback1", $string);
		$string = preg_replace('~&#([0-9]+);~e', 'chr_utf8("\\1")', $string);
		return $string; 
	}
	
	function chr_utf8_callback($matches){ 
		return chr_utf8(hexdec($matches[1])); 
	}

	function chr_utf8_callback1($matches) {
		return chr(hexdec($matches[1]));
	}
	
	function chr_utf8($num){
		if ($num < 128) return chr($num);
		if ($num < 2048) return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
		if ($num < 65536) return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
		if ($num < 2097152) return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
		return '';
	}
	//Function that find the days that passed from a log in
	function find_days($string1,$string2){
		$seconds = strtotime($string1) - strtotime($string2);
		return $seconds;
	}
 
	//Function that calculates the time difference between 2 dates
	function difference_date($string1,$string2){
		$seconds = strtotime($string1) - strtotime($string2);
		$days    = floor($seconds / 86400);
		$hours   = floor(($seconds - ($days * 86400))/3600);
		$minutes = floor(($seconds - ($days * 86400) - ($hours * 3600))/60);
		$seconds = floor(($seconds - ($days * 86400) - ($hours * 3600) - ($minutes*60)));
		$fix = "Days : ".$days." Time : ".$hours.':'.$minutes.':'.$seconds;
		
		return $fix;
	}
	//Function that calculates the period of the semester
	function calc_semester($timestamp){

		$date = explode(' ',$timestamp);
		$semester = explode('-',$date[0]);
		$fin = $semester[0].'-'.$semester[1].'-'.$semester[2];
		$win_sem_s = $semester[0].'-9-24';
		$n_year = $semester[0]+1;
		$win_sem_e = $n_year.'-2-14';
		if((strtotime($fin)> strtotime($win_sem_s))&& (strtotime($fin)< strtotime($win_sem_e))){
			$period = 'Winter';
		}else{
			$period = 'Summer';
		}				
		return $period;
	}
	//Function that calculates how many times a user did view or downloaded a file
	function print_browsing($filename,$array,$id){
		if(($id ==1 )||($id == 2)){
			$file = '-';
			$temp = array();
			$temp = array_keys(array_flip($array));
			$count = count($temp);
			$count_view = count($array);
			$counter = array();
			$pointer = 0;
			for($x=0;$x<$count;$x++){
					$parts = explode(' ',$temp[$x]);
					$ctr = 0;
					$file ='';
					$cnt = count($parts);
					for($j=10;$j<$cnt;$j++){
						$file= $file.' '.$parts[$j];
					}
					for($i=0;$i<$count_view;$i++){
						$file_view='';
						$parts_view = explode(' ',$array[$i]);
						$cnt_view = count($parts_view);
						for($j_view=10;$j_view<$cnt_view;$j_view++){
							$file_view = $file_view.' '.$parts_view[$j_view];
						}
						if(($parts[0] == $parts_view[0])&& ($file_view == $file)){
								$ctr = $ctr +1;
						}
					}
					$sub = substr($parts[0],0,32);
					$test = $sub."\t".$parts[1]."\t".$parts[2]."\t".$file."\t".$ctr."\t".$parts[5]."\t".$parts[8]."\t".$parts[9]."\n".'123!@#'.$parts[0].'123!@#'.$file;
					array_push($counter,$test);
				
			}
			$count = count($counter);
			$tofile='';
			$keep_file = array();
			for($x=0;$x<$count;$x++){
				$parts=explode('123!@#',$counter[$x]);
				$keep = $parts[0];
				if($x+1<$count){
					for($j=$x+1;$j<$count;$j++){
						$parts_p = explode('123!@#',$counter[$j]);
						if(($parts_p[1]==$parts[1])&&($parts_p[2]==$parts[2])){
							$keep = $parts_p[0];
						}
					}
				}
				array_push($keep_file,$keep);
			}
			$keep_file = array_values(array_unique($keep_file));
			$count = count($keep_file);
			for($x=0;$x<$count;$x++){
				$tofile=$tofile.$keep_file[$x];
			}
			if($id==1){
				$file = 'view_file-'.$filename.'.csv';
			}else{
				$file = 'download_file-'.$filename.'.csv';
			}
			file_put_contents($file,$tofile);
			
		}else{
			echo "Wrong id given into the function ! 1 or 2 is the correct <br>";
		}
	}

	//Connecting to the server
	ini_set('memory_limit', '2048M');
	set_time_limit(0);
	$host = 'localhost';
	$user = 'root';
	$pass = '';
	$db = 'eclass';
	$con = mysql_connect($host,$user,$pass) or die("Could not connect to the server\n");
	mysql_select_db($db);
	mysql_query("SET NAMES 'utf8'");
	
	$myDir = '../../apache/logs/http-logs';
	$openDirectory = opendir($myDir) or die($php_errormsg);
	
	while(false !== ($filename = readdir($openDirectory))){
		if(!preg_match('/ssl/',$filename)){
			$partsFname = explode('.',$filename);
			if($filename != '.' && $filename != '..'){
				if(end($partsFname) == "gz"){
					$readGZ = gzopen($myDir.'/'.$filename,"r");
					$contents = '';
					while(!gzeof($readGZ)){					
						$contents .= gzread($readGZ,2048);
					}
					gzclose($readGZ);				
				}else{
					$contents = '';
					$openFile = fopen($myDir.'/'.$filename,'r');
					while(!feof($openFile)){
						$contents .= fgets($openFile);
					}
					fclose($openFile);
				}
				$results = array();
				$line = explode("\n",$contents);
				for($x=0; $x<count($line); $x++){
					$parts = explode(' ',$line[$x]."         ");
					$data = array($parts[0],$parts[3],$parts[5],$parts[6],$parts[8]);//Parts[8] response from Server
					array_push($results,$data);
				}
				echo "Processing file ".$partsFname[0]."<br>";
				$array_timestamps = array();
				$array_timestamps_IP = array();
				
				$tmp = count($results);
			
				$IP = array();
				$date = array();
				$correctTime = array();
				$temp = array();
				$mode = array();
				$path = array();
			
				//Get Date & Tine
				for($x=0;$x<$tmp;$x++){
					$parts = explode('[', $results[$x][1]."[");//date&time
					$parts_ip = $results[$x][0];
					$parts_path = explode('"',$results[$x][2].$results[$x][3].'"'); //path Get/Post.(path)
					array_push($IP,$parts_ip); // IP
					array_push($temp,$parts[1]);//Date&Time after [
					array_push($path,$parts_path[1]); // Path Get/post .. response from server $results[$x][4]
				}
				
				//Fixing Date & Time
			
				//Splitting date and time
				$count = count($temp);
				for($x=0;$x<$count-1;$x++){
					
					$parts = explode(':',$temp[$x].':::');
					array_push($date,$parts[0]);
					$time = $parts[1].':'.$parts[2].':'.$parts[3];
					array_push($correctTime,$time);
					
				}
				
				//Changing Month into a number then pushing it into an array
				$count = count($date);
				$correctDate = array();
				for($x=0;$x<$count;$x++){
					$month = 00;
					$parts = explode('/',$date[$x].'///');
					$month = date('m', strtotime($parts[1]));
					$fullDate = $parts[2].'-'.$month.'-'.$parts[0];
					array_push($correctDate,$fullDate);
				}
				
				//Making a correct string that can be processed into the future
				$correctDateTime = array();
				for($x=0;$x<$count;$x++){
					$correct = $correctDate[$x]." ".$correctTime[$x];
					array_push($correctDateTime,$correct); // Date&Time
				}
				//End of fixing Date & Time
				
				$documents = array();//without POST 
				$line = array(); // to see the IP
				$documents_POST = array();
				$line_POST = array();// To find the time that user is logged in! and who it is
				$line_GET = array();
				for($x=0;$x<$count;$x++){
					$parts = explode("/",$path[$x]."///");
					if($parts[0] == 'GET'){
						if($parts[1].'/'.$parts[2].'/' == 'modules/document/'){ 
							if($parts[3] != 'img'){//dont include the img from documents
								$part = $parts[3].' '.$parts[4].' '.$parts[5];
								array_push($documents, $part);
								array_push($line,$x);
							}
						}
						if($results[$x][4] == '302' ){
							array_push($line_POST,$x);
							$y = $x+1;
							array_push($line_GET,$y);
			
						}else if($parts[1].'/'.$parts[2].'/'.$parts[3] == 'modules/auth/newuser.php' && $results[$x][4]== '200'){//New users login
							array_push($line_POST,$x);
							$y = $x+1;
							array_push($line_GET,$y);
						}
					}else if($parts[0] == 'POST'){
						if($results[$x][4] == '302' ){
							array_push($line_POST,$x);
							$y = $x+1;
							array_push($line_GET,$y);
			
						}else if($parts[1].'/'.$parts[2].'/'.$parts[3] == 'modules/auth/newuser.php' && $results[$x][4]== '200'){//New users login
							array_push($line_POST,$x);
							$y = $x+1;
							array_push($line_GET,$y);
						}
					}
				}
				//Splitting to arrays the information $dtime_pointer(Pointer to the line that a download happened),$dtime_sawFile(Pointer to the line that a file was viewed)
				$count = count($documents);
				$pointer = array();
				$sawFile = array();
				$dtime_pointer = array();
				$dtime_sawFile = array();
				for($x=0;$x<$count;$x++){
					if(strpos($documents[$x],'=')){
						array_push($pointer,$x);
						array_push($dtime_pointer,$line[$x]);
					}else {
						array_push($sawFile,$x);
						array_push($dtime_sawFile,$line[$x]);
					}
				}
			
				//Process of the logs that user downloaded a file
				$count = count($pointer);
				$countGet = count($line_GET);
				$user_download = array();
				$user_view = array();

				for($x=0;$x<$count;$x++){				
					$parts = explode('&',$documents[$pointer[$x]].'&');
					if($parts[1]!= NULL && $results[$dtime_pointer[$x]][4] != '302' && $results[$dtime_pointer[$x]][4] !=  '404'){
						$lesson = '';
						$file = '';
						$dtime = '';
						$diff_upload = '';
						$id = '';
						$username = '';
						$surname = '';
						$name = '';
						$find_file = '';
						$flag = 1;
						$parts_lesson = explode("=",$parts[0]);
						
						$sql_lesson_name = mysql_query("SELECT cours.intitule FROM `cours` WHERE cours.code='$parts_lesson[1]'");
						while($row = mysql_fetch_array($sql_lesson_name)){
							$lesson = $row{'intitule'};
						}
			
						if($lesson != ''){
							if(strpos($parts[1],"download= ")!== FALSE){
								$find_file = explode("download= ",$parts[1]);
								$path = '/'.$find_file[1];
								$sql_file_name = mysql_query("SELECT document.filename,document.date FROM `document` WHERE document.path = '$path'");
			
								while($row = mysql_fetch_array($sql_file_name)){
									$file = $row{'filename'};
									$dtime = $row{'date'};
								}

							}else{
								$flag = 2;
								if(strpos($parts[1],"openDir= ")!== FALSE){
									$find_file = explode("openDir= ",$parts[1]);
									$path = str_replace(" ","/",$find_file[1]);
									$path = '/'.$path;
							
									$sql_file_name = mysql_query("SELECT document.filename,document.date FROM `document` WHERE document.path = '$path'");
									while($row = mysql_fetch_array($sql_file_name)){
										$file = $row{'filename'};
										$dtime = $row{'date'};
									}
								}
							}
							
							if($file != ''){
								$diff_upload = difference_date($correctDateTime[$dtime_pointer[$x]],$dtime);
								$time = $correctDateTime[$dtime_pointer[$x]];
								$sql = mysql_query("SELECT user.nom,user.prenom,user.username,loginout.id_user, loginout.action, loginout.when FROM `user`,`loginout` WHERE user.user_id = loginout.id_user AND loginout.action != 'LOGOUT' AND loginout.when <= '$time' ORDER BY loginout.when DESC");
								while($row = mysql_fetch_array($sql)){
									if($row{"action"}!= 'LOGOUT'){									
										$username = $row{'username'};
										$surname = $row{'nom'};
										$name = $row{'prenom'};
										$diff = find_days($row{"when"},$time);
										if(abs($diff) < 86400){
											break;
										}
										
									}
								}							
								if($username != ''){
									$semester = calc_semester($correctDateTime[$dtime_pointer[$x]]);
									if($flag == 1){
										array_push($user_download, $username.' '.$surname.' '.$name.' '.$diff_upload.' '.$semester.' '.$parts_lesson[1].' '.$file);
									}else{
										array_push($user_view, $username.' '.$surname.' '.$name.' '.$diff_upload.' '.$semester.' '.$parts_lesson[1].' '.$file);
									}
								}
							}	
						}	
					}	
				}

				//End of process of the logs that user downloaded a file
				//Process of the logs that the user saw a file only
			
				$count = count($sawFile); //Pointer  to logs that a user just viewed the file
				$countGet = count($line_GET);

				for($x=0; $x<$count; $x++){
					
					if($results[$dtime_sawFile[$x]][4] != '302' && $results[$dtime_sawFile[$x]][4] !=  '404'){
						$lesson = '';
						$file = '';
						$dtime = '';
						$retstr = '';
						$diff_upload = '';
						$time = '';
						$username = '';
						$surname = '';
						$name = '';
						$semester = '';
						$parts = explode(' ',$documents[$sawFile[$x]].' ');
						$hex = explode(".",$parts[2]);		
			
						//checking if hex 
						$hex_check = str_replace(' ','',$hex[0]);
						$hex_check = str_replace('+','20',$hex_check);
						$hex_check = str_replace('(','28',$hex_check);
						$hex_check = str_replace(')','29',$hex_check);
						$hex_check = str_replace('\x', '', $hex_check);
						if(preg_match("/#?[0-9A-F]{6}/i", $hex_check)){
							$retstr = my_decode_url($hex[0]);
						}

						$sql_lesson = mysql_query("SELECT cours.intitule FROM `cours` WHERE cours.code='$parts[1]'");
						if($retstr == ''){		
							$sql_file = mysql_query("SELECT document.filename, document.date FROM `document` WHERE document.filename = '$parts[2]'");
						}else{

							$sql_file = mysql_query("SELECT document.filename, document.date FROM `document` WHERE document.filename = '$retstr'");
						}

						while($row = mysql_fetch_array($sql_lesson)){
							$lesson = $row{'intitule'};
						}
						while($row = mysql_fetch_array($sql_file)){
							$file = $row{'filename'};
							$dtime = $row{'date'};
						}
						if($file != ''){
							$diff_upload = difference_date($correctDateTime[$dtime_sawFile[$x]],$dtime);							
							$time = $correctDateTime[$dtime_sawFile[$x]];
							$sql = mysql_query("SELECT user.nom,user.prenom,user.username,loginout.id_user, loginout.action, loginout.when FROM `user`,`loginout` WHERE user.user_id = loginout.id_user AND loginout.action != 'LOGOUT' AND loginout.when <= '$time' ORDER BY loginout.when DESC");
							while($row = mysql_fetch_array($sql)){
								if($row{"action"}!= 'LOGOUT'){
									$username = $row{'username'};
									$surname = $row{'nom'};
									$name = $row{'prenom'};
									$diff = find_days($row{"when"},$time);
									if(abs($diff) < 86400){
										break;
									}
									
								}
							}
								
							if($username != ''){
								$semester = calc_semester($correctDateTime[$dtime_sawFile[$x]]);
								array_push($user_view, $username.' '.$surname.' '.$name.' '.$diff_upload.' '.$semester.' '.$parts[1].' '.$file);
							}
									
						}
					}
				}
				print_browsing($partsFname[0],$user_view,1);
				echo "<br>";
				print_browsing($partsFname[0],$user_download,2);
				echo "Process done"."<br>";
			}
		}
	}
?>
</html>