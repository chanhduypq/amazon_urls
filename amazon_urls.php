<?php
require_once('include/simple_html_dom.php');
set_time_limit(0);


$amu = new amazonUrls;
$amu->getAllData();


class amazonUrls {

 	public $domain 	= "" ;
 	public $allData = array();
 	
 	function __construct() 	{
 		
 	}

 	// @getAllData
 	function getAllData(){

		$url = "https://www.amazon.com/Best-Sellers-Home-Kitchen/zgbs/home-garden/"; 
		$this->getCatUrls($url);

		print_r("all size: ".sizeof($this->allData)."\n");

		// create csv
		$file = fopen("allData.csv", "w");
		fputcsv($file, array('id','url', 'name'));
		foreach ($this->allData as $k => $row) {
			$row = array_map("utf8_decode", $row);
			fputcsv($file, $row);
		}
		fclose($file);

		// fwrite json data
		// $fp = fopen("allData.json", 'w');
		// fwrite($fp, json_encode($this->allData));
		// fclose($fp);

 			
 	}

 	// @getCatUrls
 	function getCatUrls($url){

 		// print_r
 		print_r("size: ".sizeof($this->allData)."\n");
		$html = $this->curl_getContent($url);
		$html_base = new simple_html_dom();
		$html_base->load($html);

		$tmp = $html_base->find("#zg_browseRoot li span.zg_selected");
		if( isset($tmp[0]) && isset($tmp[0]->parent()->tag) && isset($tmp[0]->parent()->next_sibling()->tag) && trim($tmp[0]->plaintext)!='' && $tmp[0]->parent()->tag=='li' && trim($tmp[0]->parent()->next_sibling()->tag=='ul' ) ) {
				
			foreach ($tmp[0]->parent()->next_sibling()->find("a") as $node) {

				//data
				$data = array( 'id'=>'', 'url'=>'', 'name'=>'');
				//explore
				$s_tmp 	= explode('/ref=', $node->href);
				$data['url'] 	= trim($s_tmp[0]);
				$data['name'] 	= trim($node->plaintext);
				$s_tmp 	= explode('/', $s_tmp[0]);
				$data['id'] = trim(end($s_tmp));
				$this->allData[$data['id']]= $data;
				//get more sub
				$this->getCatUrls($data['url']);
			}
		}

		// clear html_base
		$html_base->clear();
		unset($html_base);

 	}

 	// @curl_getContent
 	function curl_getContent($url) {
		
		$headers = array();
		$headers[] = 'Host: www.amazon.com' ;
		$headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64; rv:55.0) Gecko/20100101 Firefox/55.0' ;
		$headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' ;
		$headers[] = 'Accept-Language: vi-VN,vi;q=0.8,en-US;q=0.5,en;q=0.3' ;
		$headers[] = 'Accept-Encoding: gzip, deflate, br' ;
		$headers[] = 'Connection: keep-alive' ;
		$headers[] = 'Upgrade-Insecure-Requests: 1';

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_ENCODING, "gzip");
		$content = curl_exec($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		print_r("Status: ".$status."\n");

		if($status==0){
			sleep(30);
			return $this->curl_getContent($url);
		}

		return $content;
	}




}


?>