<?php

$url = 'http://www.bca.co.id/id/kurs-sukubunga/kurs_counter_bca/kurs_counter_bca_landing.jsp';

if(! function_exists("curl_init")):
	die('CURL tidak ada, setting di php.ini anda');
endif;

$chp = curl_init();

curl_setopt($chp, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($chp, CURLOPT_URL, $url);
$content = curl_exec($chp);

//menghilangkan error karena markup yang tidak lengkap saat
//menggunakan DOMDocument
libxml_use_internal_errors(true);

// akses DOM bawaan PHP
$dom = new DOMDocument;

//load HTML hasil CURL
$dom->loadHTML($content);

//echo '<pre>';
//echo $dom->getElementsByTagName( 'table' )->item(1)->getElementsByTagName('tr')->item(1)->getElementsByTagName('td')->item(0)->nodeValue;
//lakukan looping
$row = array();
foreach ($dom->getElementsByTagName( 'tbody' ) as $table) {
	$i = 1;
	$cells = array();
	foreach( $table->getElementsByTagName( 'tr' ) as $tr):
		if($tr->getElementsByTagName( 'td' )->item(0)->nodeValue > 0){
			//echo $i;
			$kurs = $dom->getElementsByTagName( 'table' )->item(1)->getElementsByTagName('tr')->item($i)->getElementsByTagName('td')->item(0)->nodeValue;
			$cells = array(
				'jual' => $tr->getElementsByTagName( 'td' )->item(0)->nodeValue,
				'beli' => $tr->getElementsByTagName( 'td' )->item(1)->nodeValue
			);
			$row[$kurs] = $cells;
			$i += 1;
		}
		
	endforeach;
	
	break;
}

$path   = "bca.json";

$data['date'] = array('created_at' => date("Y-m-d H:i:s"));

$data['data']   = $row;	

$json = json_encode($data);

if ($json) {
	if (file_put_contents($path, $json)) {
		echo "copy ".$path." File success \n";
	} else {
		echo "copy ".$path." File failed \n";
	}
} else {
	echo "get ".$json." File failed \n";
}
