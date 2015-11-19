<?php
include('./simple_html_dom.php');

function filter($string = null) {
    $string = trim($string);
    $back   = stripslashes(strip_tags(htmlspecialchars($string, ENT_QUOTES)));
    return $back;
}

$feed = file_get_contents('http://sindikasi.okezone.com/index.php/rss/16/RSS2.0');

$data = array();

$news = new SimpleXMLElement($feed);

foreach ($news->channel->item as $value) {
	$pecah   = explode("/", $value->link);
	$id 	 = $pecah[8];
	$summary = $value->description;
	$descrip = explode(" ", $summary);
	$tags    = implode(",", array_filter($descrip, function($v){ return trim($v) != null; }));
	$data[$id] = array(
		'thumb' 	=> (string) $value->image->url,
		'title' 	=> strip_tags($value->title),
		'summary' 	=> (string) $summary,
		'tags'		=> trim(str_replace("'", "", $tags)),
		'link'		=> (string) $value->link
		);
}



foreach ($data as $key => $val) {
	$content = file_get_html($val['link']);

	foreach($content->find('div.detail-content') as $row){

		foreach($row->find('div.detail-img') as $text){
			$path    = explode(". ", strip_tags(trim($text->innertext)));
			$arrText = array('text' => implode(".<p></p>", $path) );
		}

		

	}

	foreach ($content->find('div#imageCheckWidth') as $image) {
		foreach($image->find('img') as $i)
			$arrImg = array('img' => $i->src);
	}

	$data[$key] = array_merge($data[$key], $arrImg, $arrText);

}

if(isset($data) && count($data) > 0){

	$connect    = mysql_connect('localhost', 'root', 'root') or die("Koneksi gagal"); 
	$select_db  = mysql_select_db('silvanix_db', $connect) or die("Database tidak dapat dibuka");

	$no  = 0;
	
	foreach ($data as $key => $value) {
		$query = mysql_query("SELECT id FROM news WHERE id=$key ");
		$num   = mysql_num_rows($query);
		if($num == 0){
			$now = date("Y-m-d H:i:s");
			$q   = "INSERT INTO news VALUES ($key, '".str_replace("'", "", $value['title'])."', '".str_replace("'", "", $value['summary'])."', '".str_replace("'", "", $value['text'])."', '".$value['img']."', '".$value['thumb']."', '".$value['tags']."', 'okezone.com', 'admin', '$now', '$now' ) ";
			$sql = mysql_query($q);
			//echo $q;
			if(mysql_affected_rows() > 0){
		        $no++;
		    }else{
		    	echo mysql_error($sql);
		        break;
		    }
		}else{
			$q  = "UPDATE news SET image ='".str_replace("'", "", $value['img'])."' WHERE id =$key ";
			$sql = mysql_query($q);
			//echo $q;
			if(mysql_affected_rows() > 0){
		        $no++;
		    }else{
		    	echo mysql_error($sql);
		        break;
		    }
		}
		
	}
}

if($no == count($data)){
	echo "import successed.";
}else{
	echo "import failed.";
}
