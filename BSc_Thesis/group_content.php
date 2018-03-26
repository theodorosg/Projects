<html>
<meta http-equiv="Content-Type" content="text/html;charset=utf8">
<?php

	$file="wiki_pages.csv";
	$contents_wiki_pages = file_get_contents("".$file);
	$file="wiki_content.csv";
	$contents_wiki_content = file_get_contents("".$file);
	$file="posts.csv";
	$contents_posts = file_get_contents("".$file);
	$file="posts_text.csv";
	$contents_posts_text = file_get_contents("".$file);
	$file="questions.csv";
	$contents_questions = file_get_contents("".$file);
	$file="reponses.csv";
	$contents_reponses = file_get_contents("".$file);
	$file="topics.csv";
	$contents_topics = file_get_contents("".$file);
	$file="annoncesEclass.csv";
	$contents_annon = file_get_contents("".$file);
	$file="agendaEclass.csv";
	$contents_agenda = file_get_contents("".$file);
	$file="coursDescrEclass.csv";
	$contents_coursDesc = file_get_contents("".$file);
	$file = "ebookEclass.csv";
	$contents_book = file_get_contents("".$file);
	$file="forumsEclass.csv";
	$contents_forums = file_get_contents("".$file);
	$file="glossaryEclass.csv";
	$contents_glossary = file_get_contents("".$file);
	$file="linkEclass.csv";
	$contents_link = file_get_contents("".$file);
	$file="videosEclass.csv";
	$contents_videos = file_get_contents("".$file);
	$file="videosLinkEclass.csv";
	$contents_vLink = file_get_contents("".$file);
	
	$parts_wpages=explode("\n",$contents_wiki_pages);
	$parts_wcontent=explode("\n",$contents_wiki_content);
	$parts_posts=explode("\n",$contents_posts);
	$parts_poststxt=explode("\n",$contents_posts_text);
	$parts_quest=explode("\n",$contents_questions);
	$parts_repon=explode("\n",$contents_reponses);
	$parts_topics=explode("\n",$contents_topics);
	$parts_annon=explode("\n",$contents_annon);
	$parts_agenda=explode("\n",$contents_agenda);
	$parts_coursDescr=explode("\n",$contents_coursDesc);
	$parts_book=explode("\n",$contents_book);
	$parts_forums=explode("\n",$contents_forums);
	$parts_glossary=explode("\n",$contents_glossary);
	$parts_link=explode("\n",$contents_link);
	$parts_videos=explode("\n",$contents_videos);
	$parts_vLink=explode("\n",$contents_vLink);
	
	$tofile="id"."\t"."wiki content"."\t"."posts"."\t"."posts text"."\t"."questions"."\t"."reponses"."\t"."topics"."\t"."announces"."\t"."agenda"."\t"."coursDescr"."\t"."ebook"."\t"."forums"."\t"."glossary"."\t"."link"."\t"."videos"."\t"."videos links"."\n";
	for($x=0;$x<count($parts_wpages);$x++){
		$wpages=explode("\t",$parts_wpages[$x]);
		if(count($wpages)!=1){
			$wcontent=explode("\t",$parts_wcontent[$x]);
			$posts=explode("\t",$parts_posts[$x]);
			$poststxt=explode("\t",$parts_poststxt[$x]);
			$quest=explode("\t",$parts_quest[$x]);
			$repon=explode("\t",$parts_repon[$x]);
			$topics=explode("\t",$parts_topics[$x]);
			$annon=explode("\t",$parts_annon[$x]);
			$agenda=explode("\t",$parts_agenda[$x]);
			$coursDescr=explode("\t",$parts_coursDescr[$x]);
			$book=explode("\t",$parts_book[$x]);
			$forums=explode("\t",$parts_forums[$x]);
			$glossary=explode("\t",$parts_glossary[$x]);
			$link=explode("\t",$parts_link[$x]);
			$videos=explode("\t",$parts_videos[$x]);
			$vLink=explode("\t",$parts_vLink[$x]);
			
			$tofile .= $wpages[0]."\t".$wpages[1]."\t".$wcontent[1]."\t".$posts[1]."\t".$poststxt[1]."\t".$quest[1]."\t".$repon[1]."\t".$topics[1]."\t".$annon[1]."\t".$agenda[1]."\t".$coursDescr[1]."\t".$book[1]."\t".$forums[1]."\t".$glossary[1]."\t".$link[1]."\t".$videos[1]."\t".$vLink[1]."\n";
		}
	}
	echo "Grouping: done";
	file_put_contents("grouped_contents.csv",$tofile);
?>
</html>