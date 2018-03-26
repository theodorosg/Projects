<?php
/**
 * Created by PhpStorm.
 * User: Theo
 * Date: 16/06/2016
 * Time: 18:12
 */

	# Get process id
	$pid = getmypid();
	# Starting clock
	$start = microtime(true);
	# Initialize the path of installation
	$path = "/neutapps/";

	# The ls command has a parameter -t to sor by time
	# We get the newest directory created with head -1
	$larsoft_vInstalled = shell_exec("ls -t $path | head -1");

	# Creating the message for the e-mail
	$message = "";
	$email_message = "";
	$message = "------------------------------------------\n\nCurrent version: ".$larsoft_vInstalled."\n";
	$email_message .= "Current version: .$larsoft_vInstalled.<br>" ;
	echo $message;
	echo "------------------------------------------\nSearching...\n";
	ini_set('max_execution_time', 86400);

	# Getting the html code
	$html = file_get_contents('https://cdcvs.fnal.gov/redmine/projects/larsoft/wiki/LArSoft_release_list');
	# Create a DOM parser object
	$dom = new DOMDocument();
	# Parse the HTML from "http://scisoft.fnal.gov/scisoft/bundles/larsoft/"
	# The @ before the method call suppresses any warnings that
	# loadHTML might throw because of invalid HTML in the page.
	@$dom ->loadHTML($html);

	# Getting main table from https://cdcvs.fnal.gov/redmine/projects/larsoft/wiki/LArSoft_release_list
	$gettingTheTable = $dom -> getElementsByTagName('table');
	foreach($gettingTheTable as $table){
		$test = $dom -> saveHTML($table);
		$td_parts = explode("</td>",$test);
		$newestVersion =$td_parts[5];
		$pointer = 5;
		$flag = 0;
		# For loop to find nearest version that does not have rc
		for(;;){
			if($flag == 1) break;
			if(preg_match('/rc/',$newestVersion)){
				$pointer = $pointer + 5;
				$newestVersion = $td_parts[$pointer];
			}else{
				$flag = 1;
			}
		}
	}

	# Replacing . character with _
	# Versions at "http://scisoft.fnal.gov/scisoft/bundles/larsoft/"
	# have the following format vXX_YY_ZZ not vXX.YY.ZZ
	$removeDot = str_replace('.','_',$newestVersion);

	# Strips a string from HTML
	$removeTags = strip_tags($removeDot);
	# Removing extra spaces
	$removeSpace = preg_replace('/\s+/', '',$removeTags);
	# Removing spaces from the start and the end of the string
	$version = trim($removeSpace);

	# Check if there is a directory there
	$checkURL = "http://scisoft.fnal.gov/scisoft/bundles/larsoft/".$version."/";
	$handle = curl_init($checkURL);
	curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
	$response = curl_exec($handle);
	$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
	curl_close($handle);
	$makeDir = "dune-".$version;

	$message = "Found version: $makeDir\n";
	echo $message;
	$email_message .= $message ."<br>";

	if($argc > 1){
		if($argv[1] == '1'){
			$destination_pp = $path . $makeDir."_installation";
			$destination = $path . $makeDir;
			$cmd = "cd $destination_pp; ./pullProducts -p $destination slf7 $makeDir e9 prof";
			echo "------------------------------------------\nArgument 1 found!\nChecking if pullProducts exists...\n";
			$check_pullProducts = shell_exec("ls $destination_pp | grep pullProducts");
			if($check_pullProducts != ''){
				$output = array();
				exec($cmd,$output);
				$to_send = "";
				for($x=0;$x<count($output);$x++){
					echo $output[$x]."\n";
					$temp = preg_replace("/\s+/","",$output[$x]);
					$to_send .= $temp."\n" ;
				}
				$version_manifest = shell_exec("ls $destination_pp | grep dune-");
				$path_to_manifest = $destination_pp."/$version_manifest";
				$items_downloaded = preg_replace("/\s+/","---",$to_send);
				$output = shell_exec("php findErrors.php $items_downloaded $path_to_manifest");
				echo "$output";

			}else{
				echo "------------------------------------------\nDownloading pullProducts...\n";
				echo shell_exec("cd $destination_pp; wget http://scisoft.fnal.gov/scisoft/bundles/tools/pullProducts; chmod +x ./pullProducts");
				$output = array();
				exec($cmd,$output);
				$to_send = "";
				for($x=0;$x<count($output);$x++){
					echo $output[$x]."\n";
					$temp = preg_replace("/\s+/","",$output[$x]);
					$to_send .= $temp."\n" ;
				}
				$version_manifest = shell_exec("ls $destination_pp | grep dune-");
				$path_to_manifest = $destination_pp."/$version_manifest";
				$items_downloaded = preg_replace("/\s+/","---",$to_send);
				$output = shell_exec("php findErrors.php $items_downloaded $path_to_manifest");
				echo "$output";

			}
		}else{
			echo "------------------------------------------\nAcceptable arguemts: 1 or none\n------------------------------------------\n";
		}
	}else{
		# Checking if the version exists
		# If not check the response from the server
		# Create the directory
		# The download new version via pullProducts
		if(trim($larsoft_vInstalled) == $makeDir){
			$message = "------------------------------------------\nNothing to do\n------------------------------------------\n";
			echo $message;
			$email_message .= $message ."<br>" ;
		}else{
			# Downloading only if response of "http://scisoft.fnal.gov/scisoft/bundles/larsoft/$version"
			# has 200 message from the server
			if($httpCode == 200){
				# Directory of the LArSoft version
				$message = "------------------------------------------\nMaking directory...\n";
				echo $message;
				$email_message .= $message ."<br>";
				if(!file_exists($makeDir)){
					shell_exec("mkdir $path/$makeDir");
					$message = "The directory $makeDir was successfully created.\n";
					echo $message;
					$email_message .= $message ."<br>";
					if(!file_exists($makeDir.'_installation')){
						mkdir($path . $makeDir.'_installation');
						$message = "The directory $makeDir"."_installation was successfully created.\n";
						echo $message;
						$email_message .= $message ."<br>" ;
						# Downloading and configuring pullProducts
						# at the directory /neutapps/larsoft-vxx_zz_yy_installation
						$message = "------------------------------------------\nDownloading pullProducts...\n";
						echo $message;
						$email_message .= $message ."<br>";
						$destination_pp = $path . $makeDir."_installation";
						$pull_Products = shell_exec("cd $destination_pp; wget http://scisoft.fnal.gov/scisoft/bundles/tools/pullProducts; chmod +x ./pullProducts");
						echo $pull_Products;
						$email_message .= $pull_Products ."<br>" ;
						$destination = $path . $makeDir;
						echo "Downloading the DUNE version...\n";
						$downloading = array();
						$cmd = "cd $destination_pp; ./pullProducts -p $destination slf7 $makeDir e9 prof";
						exec($cmd,$downloading);
						$to_send = "";
						for($x=0;$x<count($downloading);$x++){
							$email_message .= $downloading[$x]. "<br>";
							$temp = preg_replace("/\s+/","",$downloading[$x]);
							$to_send .= $temp."\n" ;
						}
						$email_message .= "<br>" ;
						$message = "\n\nInstallation done\n------------------------------------------\n";
						echo $message;
						$email_message .= $message ."<br>" ;

						$version_manifest = shell_exec("ls $destination_pp | grep dune-");
						$path_to_manifest = $destination_pp."/$version_manifest";
						$items_downloaded = preg_replace("/\s+/","---",$to_send);
						$output = shell_exec("php findErrors.php $items_downloaded $path_to_manifest");
						echo $output."\n";
						$email_message .= $output ."<br>" ;
					}else{
						$message = "The directory $makeDir_installation exists.\n";
						echo $message;
						$email_message .= $message ."<br>" ;
					}
				}else{
					$message = "The directory $makeDir exists.\n";
					echo $message;
					$email_message .= $message ."<br>" ;
				}
			}else{
				$message = "------------------------------------------\nFound new version: $removeSpace\nBut got this response from the server: $httpCode\n------------------------------------------\n";
				echo $message;
				$email_message .= $message ."<br>" ;
			}
		}
	}

	if($argc > 1){
		if($argv[1]=='1'){

			$end = microtime(true);
			# Show the first 2 decimals
			$time = number_format(($end - $start), 2);
			echo "Execution time: $time seconds\n";
			echo shell_exec("ps -p $pid -o %cpu,%mem,cmd\n");
		}
	}else{
		$end = microtime(true);
		# Show the first 2 decimals
		$time = number_format(($end - $start), 2);
		$message = "Execution time: $time seconds\n";
		echo $message;
		$email_message .= $message ."<br>" ;
		$message = shell_exec("ps -p $pid -o %cpu,%mem,cmd\n");
		echo $message;
		$email_message .= $message ."<br>" ;

		#Construction e-mail & sending
		$to = "theodoros.giannakopoulos@cern.ch";
		$subject = "New version of DUNE";
		$header = "From:neutplatform.support@cern.ch  \r\n";
		$header .= "MIME-Version: 1.0\r\n";
		$header .= "Content-type: text/html\r\n";
		$retval = mail ($to,$subject,$email_message,$header);

		if( $retval == true ) {
			echo "Message sent successfully...\n";
		}else {
			echo "Message could not be sent...\n";
		}
	}
