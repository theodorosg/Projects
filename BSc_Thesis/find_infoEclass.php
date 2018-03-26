<html>
<meta http-equiv="Content-Type" content="text/html;charset=utf8">
<?php

	$myFile = 'grades.csv';
	$openFile = fopen($myFile,'r') or die("Could not open the file\n");
	
	$results = array();
	while(!feof($openFile)){
		$parts = explode("\t",fgets($openFile));
		array_push($results,$parts[0]);
	}
	
	$results = array_unique($results);
	$eclass_id = array();
	foreach($results as $key => $value){
		if($value != ''){
			array_push($eclass_id,$value);
		}
	}
	mysql_connect('localhost','root','') or die("Could not connect to the root\n");
	
	$toAnnonces = '';
	$toVideos = '';
	$toAgenda = '';
	$toForums = '';
	$toCoursDescr = '';
	$toEbook = '';
	$toGlossary = '';
	$toLink = '';
	$toVideosLink = '';
	
	mysql_select_db('eclass');
	mysql_query("SET NAMES 'utf8'");
	
	for($i=0; $i<count($eclass_id); $i++){
		
		$annonces = array();
		$videos = array();
		$agenda = array();
		$forums = array();
		$coursDescr = array();
		$ebook = array();
		$glossary = array();
		$link = array();
		$videosLink = array();
		$id = '';
		
		$sql = mysql_query("SELECT course.id FROM course WHERE course.code = '$eclass_id[$i]'");
		while($row = mysql_fetch_array($sql)){
			$id = $row{'id'};
			
			$sqlinfo = mysql_query("SELECT annonces.id FROM `annonces` WHERE annonces.cours_id= '$id'");		
			while($rowinfo = mysql_fetch_array($sqlinfo)){
				array_push($annonces,$rowinfo{'id'});
			}
			
			$sqlinfo = mysql_query("SELECT agenda.id FROM `agenda` WHERE agenda.lesson_code = '$eclass_id[$i]'");
			while($rowinfo = mysql_fetch_array($sqlinfo)){
				array_push($agenda,$rowinfo{'id'});
			}
			
			$sqlinfo = mysql_query("SELECT course_description.id FROM `course_description` WHERE course_description.course_id = '$id'");
			while($rowinfo = mysql_fetch_array($sqlinfo)){
				array_push($coursDescr,$rowinfo{'id'});
			}
			
			$sqlinfo = mysql_query("SELECT ebook.id FROM `ebook` WHERE ebook.course_id = '$id'");
			while($rowinfo = mysql_fetch_array($sqlinfo)){
				array_push($ebook,$rowinfo{'id'});
			}
			
			$sqlinfo = mysql_query("SELECT forum.id, forum.name FROM `forum` WHERE forum.course_id = '$id'");
			while($rowinfo = mysql_fetch_array($sqlinfo)){
				if($rowinfo{'name'}!= 'Δοκιμαστική περιοχή συζητήσεων'){
					array_push($forums,$row{'id'});
				}

			}
			
			$sqlinfo = mysql_query("SELECT glossary.id FROM `glossary` WHERE glossary.course_id = '$id'");
			while($rowinfo = mysql_fetch_array($sqlinfo)){
				array_push($glossary,$rowinfo{'id'});
			}
			
			$sqlinfo = mysql_query("SELECT link.id FROM `link` WHERE link.course_id = '$id'");
			while($rowinfo = mysql_fetch_array($sqlinfo)){
				array_push($link,$rowinfo{'id'});
			}
			
			$sqlinfo = mysql_query("SELECT video.id FROM `video` WHERE video.course_id = '$id'");
			while($rowinfo = mysql_fetch_array($sqlinfo)){
				array_push($videos,$rowinfo{'id'});
			}
			
			$sqlinfo = mysql_query("SELECT videolink.id FROM `videolink` WHERE videolink.course_id = '$id'");
			while($rowinfo = mysql_fetch_array($sqlinfo)){
				array_push($videosLink, $rowinfo{'id'});
			}
		}
		
		$toAnnonces .= $eclass_id[$i]."\t".count($annonces)."\n";
		$toAgenda .= $eclass_id[$i]."\t".count($agenda)."\n";
		$toCoursDescr .= $eclass_id[$i]."\t".count($coursDescr)."\n";
		$toEbook .= $eclass_id[$i]."\t".count($ebook)."\n";
		$toForums .= $eclass_id[$i]."\t".count($forums)."\n";
		$toGlossary .= $eclass_id[$i]."\t".count($glossary)."\n";
		$toLink .= $eclass_id[$i]."\t".count($link)."\n";
		$toVideos .= $eclass_id[$i]."\t".count($videos)."\n";
		$toVideosLink .= $eclass_id[$i]."\t".count($videosLink)."\n";
	}

	$file = 'annoncesEclass.csv';
	file_put_contents($file,$toAnnonces);
	echo "Annnonces done"."<br>";
	
	$file = 'agendaEclass.csv';
	file_put_contents($file,$toAgenda);
	echo "Agenda done"."<br>";
	
	$file = 'coursDescrEclass.csv';
	file_put_contents($file,$toCoursDescr);
	echo "CoursDescr done"."<br>";
	
	$file = 'ebookEclass.csv';
	file_put_contents($file,$toEbook);
	echo "Ebook done"."<br>";
	
	$file = 'forumsEclass.csv';
	file_put_contents($file,$toForums);
	echo "Forums done"."<br>";
	
	$file = 'glossaryEclass.csv';
	file_put_contents($file,$toGlossary);
	echo "Glossary done"."<br>";

	$file = 'linkEclass.csv';
	file_put_contents($file,$toLink);
	echo "Link done"."<br>";
	
	$file = 'videosEclass.csv';
	file_put_contents($file,$toVideos);
	echo "Videos done"."<br>";
	
	$file = 'videosLinkEclass.csv';
	file_put_contents($file,$toVideosLink);
	echo "VideosLink done"."<br>";
?>