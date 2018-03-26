<?php
/**
 * Created by PhpStorm.
 * User: Theo
 * Date: 12/10/2016
 * Time: 14:15
 */

	function process_Versions($array){
		$keep_versions = array();
		foreach($array as $key => $value){
			$parts = explode(" ",$value);
			$removing_spaces = preg_replace('/\s+/', '', $parts[1]);
			$keep_nameVersion = str_replace('"','',trim($removing_spaces));
			if(!preg_match('/rc/',$keep_nameVersion)){
				array_push($keep_versions, $keep_nameVersion);
			}
		}
		return $keep_versions;
	}
	ini_set('max_execution_time', 86400);
	echo "------------------------------------------\nChecking connectivity...\n";
	# Paths for dune & fermilab
	$path_dune = "/cvmfs/dune.opensciencegrid.org/products/dune/";
	$path_fermilab = "/cvmfs/fermilab.opensciencegrid.org/products/larsoft/";
	# Commands to list the files
	$check_connection_d = "ls -l $path_dune";
	$check_connection_f = "ls -l $path_fermilab";
	# Arrays to store the outcome of the commands above
	$outcome_d = array();
	$outcome_f = array();
	# Executing the commands
	exec($check_connection_d, $outcome_d);
	exec($check_connection_f, $outcome_f);
	# Checking if everything with the connection is OK with dune & fermilab
	# If there is a problem the process will stop
	if(empty($outcome_d)){
		echo "Can't access dune files!\nPlease contact Neutrino Platform Support at: neutplatform.support@cern.ch\n";
		exit(0);
	}else{
		echo "Connection for dune... OK\n";
	}
	if(empty($outcome_f)){
		echo "Can't access fermilab files!\nPlease contact Neutrino Platform Support at: neutplatform.support@cern.ch\n";
		exit(0);
	}else{
			echo "Connection for fermilab... OK\n------------------------------------------\n";
	}

	# Start
	$ups_dunetpc = "./ups_dunetpc.sh";
	$outcome_ups_dunetpc = array();
	exec($ups_dunetpc, $outcome_ups_dunetpc);
	$versions_dunetpc = process_Versions($outcome_ups_dunetpc);
	$unique_versions_dunetpc = array_unique($versions_dunetpc);
	# End

 	$ups_larsoft = "./ups_larsoft.sh";
	$outcome_ups_larsoft = array();
	exec($ups_larsoft, $outcome_ups_larsoft);
	$versions_larsfot = process_Versions($outcome_ups_larsoft);
	# Removing duplicates with array_unique
	# New keys with array_values
	$unique_versions_larsoft = array_values(array_unique($versions_larsfot));
	echo "------------------------------------------\nSearching versions....\n";
	echo "Versions found!\n------------------------------------------\n\nLast 5 available versions:\n";
	$sliced_versions_larsoft = array_slice($unique_versions_larsoft, -5, 5, true);
	$oldest = 0;
	foreach($sliced_versions_larsoft as $key => $value){
		echo $key.'. dune-'.$value."\n";
		$oldest = $key;
	}

	# List all the versions if the user wants
	for(;;){
		echo "\nDo you want to list all LArSoft/DUNE versions?(y/n): ";
		$answer_user_gave = trim(strtolower (fgets(STDIN)));
		if($answer_user_gave == "y" || $answer_user_gave == "yes"){
			foreach($unique_versions_larsoft as $key => $value){
				echo $key.'. dune-'.$value."\n";
			}
			break;
		}else if($answer_user_gave == "n" || $answer_user_gave == "no"){
			break;
		}else{
			echo "Please give as answer y/n/yes/no\n";
		}
	}

	echo "\nWhat version you want to use?\n";
	$pointer_version_user_gave = "";
	# Endless for-loop until the user gives a correct index of a version!
	# variable $pointer is used to convert the string to integer
	# The method intval(strval($string)) is converting string to integer
	# Example:
	# $n="19.99";
	# print intval($n*100); // prints 1998
	# print intval(strval($n*100)); // prints 1999
	for(;;){
		echo "Please give the number of the version you want to use (e.g 0): ";
		$index = fgets(STDIN);
		if(preg_match('/^\d+$/',$index)) {
			if($index >= 0){
				if($index > $oldest){
					echo "Please give an index that does not exceed the boundaries!\n";
				}else{
					$pointer_version_user_gave = intval(strval($index));
					break;
				}
			}
		}else{
			echo "Please give an integer from the options that are displayed above!\n";
		}
	}

	# User must give an existing directory in order to continue the installation process
	$path_user_gave = "";
	for(;;){
		echo "\nPlease give the path you want the DUNE to be installed at: ";
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
			echo "Please give as answer y/n/yes/no\n";
		}
	}


	#$part_version = $unique_versions_larsoft[$pointer_version_user_gave];
	$version = $unique_versions_larsoft[$pointer_version_user_gave];
	$path_to_version = $path_fermilab.'-'.$version;
	if($flag == 0){
		if($pointer_version_user_gave == count($unique_versions_larsoft) -1){
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
		if($pointer_version_user_gave == count($unique_versions_larsoft) -1){

			$cmd = "./install_new_repositories.sh $path_to_version $users_path $to_pass";
			passthru($cmd);

		}else{

			$cmd = "./install_old_repositories.sh $path_to_version $users_path $to_pass";
			passthru($cmd);

		}
	}


	echo "\nPlease now log-out and log-in and type the following:\n";
	echo "source /cvmfs/dune.opensciencegrid.org/products/dune/setup_dune.sh\n";
	echo "source /cvmfs/fermilab.opensciencegrid.org/products/larsoft/setups\n";
	echo "setup git\nsetup gitflow\nsetup mrb\n";
	echo "source ". trim($path_user_gave)."/larsoft_$version/localProducts_larsoft_".$version."_e9_prof/setup\n";
	echo "mrbslp\n";
