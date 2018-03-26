<?php
/**
 * Created by PhpStorm.
 * User: Theo
 * Date: 14/07/2016
 * Time: 12:32
 */

	ini_set('max_execution_time', 86400);
	echo "------------------------------------------\nSearching installed versions....\n";

	$path = "/mnt/nas00/software/";
	$cmd = "ls -t $path";
	$versions = array();
	# Storing the output of the $cmd at the array $versions
	exec($cmd, $versions);

	# Searching only for dune-vXX_YY_ZZ
	# Storing the found versions on the array $keep_versions
	$keep_versions = array();
	for($x = 0; $x < count($versions); $x ++){
		if(preg_match('/dune-/',$versions[$x])){
			array_push($keep_versions,$versions[$x]);
		}
	}

	echo "Versions found!\n------------------------------------------\n\nVersions that are available:\n";
	$oldest = "";
	for($x = 0; $x < count($keep_versions); $x ++){
		echo $x.". ".$keep_versions[$x]."\n";
		$oldest = $x;
	}

	# Endless for-loop until the user gives a correct index of a version!
	# variable $pointer is used to convert the string to integer
	# The method intval(strval($string)) is converting string to integer
	# Example:
	# $n="19.99";
	# print intval($n*100); // prints 1998
	# print intval(strval($n*100)); // prints 1999
	echo "What version you want to use?\n";
	$pointer_localVersion = "";
	for(;;){
		echo "Please give the number of the version you want to use (e.g 0): ";
		$index = fgets(STDIN);
		if(preg_match('/^\d+$/',$index)) {
			if($index >= 0){
				if($index > $oldest){
					echo "Please give an index that does not exceed the boundaries!\n";
				}else{
					$pointer_localVersion = intval(strval($index));
					break;
				}
			}
		}else{
			echo "Please give an integer from the options that are displayed above!\n";
		}
	}

	# Getting the html code
	$html = file_get_contents('https://cdcvs.fnal.gov/redmine/projects/larsoft/wiki/LArSoft_release_list');
	# Create a DOM parser object
	$dom = new DOMDocument();
	# Parse the HTML from "https://cdcvs.fnal.gov/redmine/projects/larsoft/wiki/LArSoft_release_list"
	# The @ before the method call suppresses any warnings that
	# loadHTML might throw because of invalid HTML in the page.
	@$dom ->loadHTML($html);
	# Getting main table from https://cdcvs.fnal.gov/redmine/projects/larsoft/wiki/LArSoft_release_list
	# Searching newest version from fnal to check if there is one so the repositories
	# are not tagged for the newest one
	$newestVersion = "";
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

	$path_user_gave = "";
	for(;;){
		echo "Please give the path you want the DUNE to be installed at: ";
		$path_user_gave = fgets(STDIN);
		#       $cmd = "./check.sh $path_user_gave";
		$cmd = "[[ -d $path_user_gave ]] && echo 'Directory found' || echo 'Directory $path_user_gave not found'";
		$output = array();
		exec($cmd, $output);
		if($output[0] == "Directory found"){
			break;
		}else{
			echo "Please give a directory that exists!\n";
		}
	}

	# The following code asks the user if he/she wants to install more than dunetpc repository
	# User can give answers y/yes , n/no, it does not matter if the answer is with capital letters or not
	# parameter $flag is 0 if the answer is yes (which means that the user wants only dunetpc repository)
	# parameter $flag changes to 1 if the answer is no
	$flag = 0;
	for(;;){
		echo "Do you want to install only dunetpc repository?(y/n): ";
		$answer_user_gave = trim(strtolower (fgets(STDIN)));
		if($answer_user_gave == "y" || $answer_user_gave == "yes"){
			break;
		}else if($answer_user_gave == "n" || $answer_user_gave == "no"){
			$flag = 1;
			break;
		}else{
			echo "Please give as answer y/n\n";
		}
	}


	$part_version = explode("-", $keep_versions[$pointer_localVersion]);
	$path_to_version = $path . $keep_versions[$pointer_localVersion];
	if($flag == 0){
		if($part_version[1] == $version){

			$cmd = "./install_new.sh $path_to_version $path_user_gave";
			passthru($cmd);

		}else{

			$cmd = "./install_old.sh $path_to_version $path_user_gave";
			passthru($cmd);

		}

	}else{

		# Getting the html code
		$html = file_get_contents('http://cdcvs.fnal.gov/projects/');
		# Create a DOM parser object
		$dom = new DOMDocument();
		# Parse the HTML from "http://cdcvs.fnal.gov/projects/
		# The @ before the method call suppresses any warnings that
		# loadHTML might throw because of invalid HTML in the page.
		@$dom ->loadHTML($html);

		# Array repositories used to store the output from the website
		$repositories = array();
		# Getting main table from http://cdcvs.fnal.gov/projects/
		$gettingTheTable = $dom -> getElementsByTagName('table');
		foreach($gettingTheTable as $table){
			$contents = $dom -> saveHTML($table);
			$td_parts = explode("</td>",$contents);
			# Starting from index 6 because we do not want Parent Directory
			for($x=6;$x<count($td_parts); $x = $x + 5){
				# Strips a string from HTML strip_tags($td_parts[$x]);
				# Removing character / from the directories
				$parts = explode("/",strip_tags($td_parts[$x]));
				# Removing extra spaces
				$removeSpace = preg_replace('/\s+/', '',$parts[0]);
				array_push($repositories,$removeSpace);
			}
		}
		echo "Please give the repository names separated by space!\n";

		$flag = 0;
		for(;;){
			$keep_repo = "";
			echo "Repositories you want to have: ";
			$repo = fgets(STDIN);
			echo "Checking repositories....\n\n";
			$removeSpace = preg_replace('/\s+/', ' ',$repo);
			$parts = explode(" ",trim($removeSpace));
			for($x=0;$x<count($parts);$x++){
				if(in_array(trim($parts[$x]),$repositories)){

					echo "Repository $parts[$x] exists!\n";
					$keep_repo = $keep_repo." ".$parts[$x];
					$flag = 0;

				}else{
					$flag = 1;
					echo "\nRepository ". trim($parts[$x]). " does not exists!\nSimilar repositories are:\n\n";
					for($i=0;$i<count($repositories);$i++){
						similar_text(trim($parts[$x]),$repositories[$i],$percent);
						if($percent > 50){
							echo $repositories[$i]."\n";
						}
					}
					echo "\n";
				}
			}
			if($flag == 0){
				echo "\nRepositories accepted!\nMoving to installation!\n";
				break;
			}else{
				echo "\nPlease give again the names of the repositories you want!\n";
			}
		}

		$to_pass = trim($keep_repo);
		$users_path = trim($path_user_gave);
		if($part_version[1] == $version){

			$cmd = "./install_new_repositories.sh $path_to_version $users_path $to_pass";
			passthru($cmd);

		}else{

			$cmd = "./install_old_repositories.sh $path_to_version $users_path $to_pass";
			passthru($cmd);

		}
	}

	$parts = explode("-", $keep_versions[$pointer_localVersion]);
	echo "\nPlease now log-out and log-in and type the following:\n";
	echo "source /mnt/nas00/software/$keep_versions[$pointer_localVersion]/setup\n";
	echo "setup git\nsetup gitflow\nsetup mrb\n";
	echo "source ". trim($path_user_gave)."/larsoft_$parts[1]/localProducts_larsoft_$parts[1]_e9_prof/setup\n";
	echo "mrbslp\n";