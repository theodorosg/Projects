<html>
<meta http-equiv="Content-Type" content="text/html;charset=utf8">
<?php
	
	set_time_limit(0);
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
	
	$toPosts = '';
	$toPosts_text = '';
	$toQuestions = '';
	$toReponses = '';
	$toTopics = '';
	$toWiki_pages = '';
	$toWiki_pages_content = '';
	
	for($i=0; $i<count($eclass_id); $i++){

		$posts = array();
		$posts_text = array();
		$questions = array();
		$reponses = array();
		$topics = array();
		$wiki_pages = array();
		$wiki_pages_content = array();
		
		mysql_select_db($eclass_id[$i]);
		mysql_query("SET NAMES 'utf8'");
		
		$sql = mysql_query("SELECT posts.post_id FROM posts");
		while($row = mysql_fetch_array($sql)){
			array_push($posts,$row{'post_id'});

		}
		
		$sql = mysql_query("SELECT posts_text.post_text, posts_text.post_id FROM posts_text");
		while($row = mysql_fetch_array($sql)){
			if($row{'post_text'} != 'Όταν διαγράψετε τη δοκιμαστική περιοχή συζητήσεων, θα διαγραφτεί και το παρόν μήνυμα.'){
				array_push($posts_text,$row{'post_id'});
			}	
		}
		
		$sql = mysql_query("SELECT questions.question, questions.id FROM questions");
		while($row = mysql_fetch_array($sql)){
			if($row{'question'}!= 'Η Σωκρατική ειρωνεία είναι...'){
				array_push($questions,$row{'id'});
			}
		}			
		
		$sql = mysql_query("SELECT reponses.reponse, reponses.id FROM reponses");
		while($row = mysql_fetch_array($sql)){
			if($row{'reponse'}!= 'Γελοιοποίηση του συνομιλητή σας προκειμένου να παραδεχτεί ότι κάνει λάθος.' && $row{'reponse'}!= 'Παραδοχή των δικών σας σφαλμάτων ώστε να ενθαρρύνετε το συνομιλητή σας να κάνει το ίδιο.' && $row{'reponse'}!= 'Εξώθηση του συνομιλητή σας, με μια σειρά ερωτήσεων και υποερωτήσεων, να παραδεχτεί ότι δεν ξέρει ό,τι ισχυρίζεται πως ξέρει.' && $row{'reponse'}!= 'Χρήση της αρχής της αποφυγής αντιφάσεων προκειμένου να οδηγήσετε τον συνομιλητή σας σε αδιέξοδο.'){
				array_push($reponses,$row{'id'});
			}
		}
		
		$sql = mysql_query("SELECT topics.topic_title, topics.topic_id FROM topics");
		while($row = mysql_fetch_array($sql)){
			if($row{'topic_title'}!= 'Παράδειγμα Μηνύματος'){
				array_push($topics,$row{'topic_id'});
			}
		}
		
		$sql = mysql_query("SELECT wiki_pages.id FROM wiki_pages");
		while($row = mysql_fetch_array($sql)){
			array_push($wiki_pages,$row{'id'});
		}
		
		$sql = mysql_query("SELECT wiki_pages_content.id FROM wiki_pages_content");
		while($row = mysql_fetch_array($sql)){
			array_push($wiki_pages_content,$row{'id'});
		}

		$toPosts .= $eclass_id[$i]."\t".count($posts)."\n";
		$toPosts_text .= $eclass_id[$i]."\t".count($posts_text)."\n";
		$toQuestions .= $eclass_id[$i]."\t".count($questions)."\n";
		$toReponses .= $eclass_id[$i]."\t".count($reponses)."\n";
		$toTopics .= $eclass_id[$i]."\t".count($topics)."\n";
		$toWiki_pages .= $eclass_id[$i]."\t".count($wiki_pages)."\n";
		$toWiki_pages_content .= $eclass_id[$i]."\t".count($wiki_pages_content)."\n";
		
	}
	
	$file = 'wiki_pages.csv';
	file_put_contents($file,$toWiki_pages);
	echo "Wiki_pages done"."<br>";
	
	$file = 'wiki_content.csv';
	file_put_contents($file,$toWiki_pages_content);
	echo "Wiki_pages_content done"."<br>";

	$file = 'posts.csv';
	file_put_contents($file,$toPosts);
	echo "Posts done"."<br>";
	
	$file = 'posts_text.csv';
	file_put_contents($file,$toPosts_text);
	echo "Posts_text done"."<br>";
	
	$file = 'questions.csv';
	file_put_contents($file,$toQuestions);
	echo "Questions done"."<br>";
	
	$file = 'reponses.csv';
	file_put_contents($file,$toReponses);
	echo "Reponses done"."<br>";
	
	$file = 'topics.csv';
	file_put_contents($file,$toTopics);
	echo "Topics done"."<br>";
	
?>
</html>