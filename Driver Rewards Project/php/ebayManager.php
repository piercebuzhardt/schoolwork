<?php

class ebayManager
{
	
	public $endpoint;
	
	public $version;
	public $appid;
	public $globalid;
	public $ebay_query = "";
	public $safe_ebay_query = "";
	public $apicall;
	public $api;
	
	function __construct($endpoint,$api,$version,$appid,$globalid)
	{
		$this->endpoint = $endpoint;
		$this->version = $version;
		$this->appid = $appid;
		$this->globalid = $globalid;
		$this->api = $api;
	}
	
	function findItems($ebay_query)
	{
		$this->safe_ebay_query = urlencode($ebay_query);
		
		$apicall = "$this->endpoint?";
		$apicall .= "OPERATION-NAME=findItemsByKeywords";
		$apicall .= "&SERVICE-VERSION=$this->version";
		$apicall .= "&SECURITY-APPNAME=$this->appid";
		$apicall .= "&GLOBAL-ID=$this->globalid";
		$apicall .= "&keywords=$this->safe_ebay_query";
		$apicall .= "&paginationInput.entriesPerPage=25";
		

		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$apicall);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		$output = curl_exec($ch);
		curl_close($ch);
		$resp = simplexml_load_string($output);
		
		
		//$resp = simplexml_load_file($apicall);
		//echo $resp->ack;
		if($resp->ack == "Success")
		{
			return $resp;
		}
		
		return -1;
		
	}
	
	function findItemById($item_id)
	{
		
		$apicall = "$this->api?";
		$apicall .= "callname=GetSingleItem";
		$apicall .= "&responseencoding=XML";
		$apicall .= "&appid=$this->appid";
		$apicall .= "&siteid=0";
		$apicall .= "&version=967";
		$apicall .= "&ItemID=$item_id";
		
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$apicall);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		$output = curl_exec($ch);
		curl_close($ch);
		$resp = simplexml_load_string($output);
		
		
		//$resp = simplexml_load_file($apicall);
		//var_dump($resp);
			
		if($resp->Ack == "Success")
		{
			return $resp;
		}
		
		return -1;
		
	}
	
	
	
}

$ebayManager = new ebayManager($endpoint,$api,$version,$appid,$globalid);

?>