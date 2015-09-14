<?php

$url = 'http://www.klikbca.com/';

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

//lakukan looping
$row = array();
foreach( $dom->getElementsByTagName( 'tr' ) as $tr):

	$cells = array();
	foreach($tr->getElementsByTagName( 'td' ) as $td):
		
		foreach($td->getElementsByTagName( 'td' ) as $t):
			if($t->nodeValue > 0):
			$cells[] = $t->nodeValue;
			endif;
		endforeach;
		
	endforeach;
	$row[] = $cells;

endforeach;

//lihat hasil loopong
//echo "<pre>";
//print_r($row);

//$dom->loadHTML($row[0][9]);


//print_r($row[0][9]);

echo "<h1>Bank BCA</h1>";

echo "Harga Jual : Rp. ".$row[0][0];
echo "<br />";
echo "Harga Beli : Rp. ". $row[0][1];
