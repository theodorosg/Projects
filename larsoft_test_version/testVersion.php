<?php
/**
 * Created by PhpStorm.
 * User: Theo
 * Date: 25/06/2016
 * Time: 16:16
 */

	# Get process id
	$pid = getmypid();
	# Starting clock
	$start = microtime(true);
	# The ls command has a parameter -t to sor by time
	# We get the newest directory created with head -1
	$path = "/neutapps/";
	$dune_vInstalled = shell_exec("ls -t $path | head -1");
	$email_message = "";
	echo "------------------------------------------\nTesting version: $dune_vInstalled\n\n";
	$email_message .= "Testing version: $dune_vInstalled <br>";

	if($argc > 1){
		# Argument 1 implies that ~/larsoft/ exists
		if($argv[1] == '1'){
			passthru("./set.sh");
		}
	}else{
		$makeDir = "larsoft";
		if(!file_exists($makeDir)){
			$message = "Creating directory to test the verion\n";
			echo $message;
			$email_message .= $message ."<br>" ;

			shell_exec("mkdir $makeDir");

			$message = "Directory $makeDir was created\n------------------------------------------\n";
			echo $message;
			$email_message .= "Directory $makeDir was created <br>" ;

		}else{
			$message = "Folder $makeDir already exists!\n";
			echo $message;
			$email_message .= $message ."<br>" ;

			$message = "Cleaning the folder..\n";
			echo $message;
			$email_message .= $message . "<br>";

			shell_exec("rm -rf larsoft/*");
			$message = "Folder cleaned\n------------------------------------------\n";
			echo $message;
			$email_message .= "Folder cleaned <br>";
		}

		$message = "\nStarting testing\n\n";
		echo $message;
		$email_message .= $message . "<br>";

		$testing = array();
		$cmd = "./set.sh";
		exec($cmd,$testing);
		for($x=0;$x<count($testing);$x++){
			$email_message .= $testing[$x]. "<br>";
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
		$subject = "Testing the new version of DUNE: $dune_vInstalled";
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