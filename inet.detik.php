<?php
include('./simple_html_dom.php');

function filter($string = null) {
    $string = trim($string);
    $back   = stripslashes(strip_tags(htmlspecialchars($string, ENT_QUOTES)));
    return $back;
}

$html = file_get_html('http://inet.detik.com');

$data = array();
$l = 0;
foreach($html->find('ul.list_berita_1') as $ul){
    foreach($ul->find('li') as $li){
    	if($l <= 5){
	    	foreach ($li->find('a') as $a) {
	    		$part = explode("/", $a->href);
	    		$id   = $part[8];
	    		if($part[3] == 'read'){
	    			$link[$id] = array('url' => $a->href, 'title' => $a->innertext);

	    			foreach ($li->find('img') as $img) {
	    				$summary = trim(substr(strip_tags($li->outertext), 32));
	    				$pecah   = explode(" ", $summary);
	    				$tags    = implode(",", array_filter($pecah, function($v){ return trim($v) != null; }));
			    		$data[$id] = array(
			    			'thumb' 	=> $img->src,
			    			'title' 	=> strip_tags($a->innertext),
			    			'summary' 	=> $summary,
			    			'tags'		=> trim(str_replace("'", "", $tags))
			    			);
			    	}
	    		}
	    	}
    	}
    	$l++;
    }
}

//print_r($data);

$news = array();

foreach ($link as $key => $val) {
	$content = file_get_html($val['url']);

	foreach($content->find('div.content_detail') as $detail){

		foreach($detail->find('div.text_detail') as $text){
			$path    = explode(". ", strip_tags($text->innertext));
			$arrText = array('text' => implode(".<p></p>", $path) );
		}

		$arrImage  = $detail->find('div.pic_artikel');
		$arrImage2 = $detail->find('div.pic_artikel_2');
		$arrImage3 = $detail->find('div.pic_artikel_3');
		if(count($arrImage) > 0){
			foreach($arrImage as $img){
				foreach($img->find('img') as $i)
					$arrImg = array('img' => $i->src);
			}
		}elseif(count($arrImage2) > 0){
			foreach($arrImage2 as $img){
				foreach($img->find('img') as $i)
					$arrImg = array('img' => $i->src);
			}
		}elseif(count($arrImage3) > 0){
			foreach($arrImage3 as $img){
				foreach($img->find('img') as $i)
					$arrImg = array('img' => $i->src);
			}
		}

		$news[$key] = array_merge($data[$key], $arrImg, $arrText);
	}
}


if(isset($news) && count($news) > 0){

	$connect    = mysql_connect('localhost', 'root', 'root') or die("Koneksi gagal"); 
	$select_db  = mysql_select_db('silvanix_db', $connect) or die("Database tidak dapat dibuka");

	$no  = 0;
	$now = date("Y-m-d H:i:s");
	foreach ($news as $key => $value) {
		$query = mysql_query("SELECT id FROM news WHERE id=$key ");
		$num   = mysql_num_rows($query);
		if($num == 0){
			$q   = "INSERT INTO news VALUES ($key, '".str_replace("'", "", $value['title'])."', '".str_replace("'", "", $value['summary'])."', '".str_replace("'", "", $value['text'])."', '".$value['img']."', '".$value['thumb']."', '".$value['tags']."', 'detik.com', 'admin', '$now', '$now' ) ";
			$sql = mysql_query($q);
			//echo $q;
			if(mysql_affected_rows() > 0){
		        $no++;
		    }else{
		    	echo mysql_error($sql);
		        break;
		    }
		}else{
			$no++;
		}
		
	}
}

if($no == count($news)){
	echo "import successed.";
}else{
	echo "import failed.";
}
