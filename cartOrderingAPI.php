<?php

class IMatOrderApiClient
{
	private $_apiCode;
	private $_apiUrl;
	
	public function __construct($apiUrl, $apiCode)
	{
		$this->_apiUrl = $apiUrl;
		$this->_apiCode = $apiCode;
	}
	
	public function Post($request, $requestContentType)
	{
		$bodyPartsDelimiter = $this->CreateDalimiter();
		$body = $this->CreatePostBody($request, $requestContentType, $bodyPartsDelimiter);
		$headers = $this->CreateHeaders($body, $requestContentType, $bodyPartsDelimiter);
		$result = $this->DoPost($this->_apiUrl, $headers, $body);
		
		return $result;
	}	
	
	private function CreateDalimiter()
	{
		$delimiter = "----------------".uniqid();	
		return $delimiter;
	}
	
	private function CreateHeaders($body, $acceptContentType, $delimiter)
	{
		$headers = array (
			"Content-Type: multipart/form-data; boundary=".$delimiter,
			"Accept: ".$acceptContentType,
			"ApiCode: ".$this->_apiCode,
			"Content-Length: ".strlen($body)
		);
		return $headers;
	}

	private function DoPost($apiUrl, $headers, $body)
	{
		$curl = curl_init($apiUrl);
		
		curl_setopt($curl,CURLOPT_POSTFIELDS , $body);
		curl_setopt($curl,CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl,CURLOPT_POST, true);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER , true);
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);

		$result = curl_exec($curl);
			
		curl_close($curl);
		
		return $result;
	}
	
	private function CreatePostBody($request, $requestContentType, $delimiter)
	{
		$requestPart = $this->CreateRequestPart($request, $requestContentType);
		$body = $this->CombineParts(array($requestPart), $delimiter);
		return $body;
	}
		
	private function CreateRequestPart($request, $contentType)
	{
		$data= "Content-Disposition: form-data; name=\"request\"\r\n";
		$data.= "Content-Type: $contentType\r\n\r\n";
		$data.= $request;
		return $data;
	}
	
	private function CombineParts($parts, $delimiter)
	{
		$result = "--$delimiter\r\n".implode("\r\n--$delimiter\r\n", $parts)."\r\n--$delimiter--\r\n";
		return $result;
	}
}

$requestData = '
{
	"cartID":"46030c8b-7eb7-49a9-a78e-bf01c7421441", 
	"myOrderReference":"some reference",
	"directMailingAllowed":"false",
	"shipmentService":"Standard"
}';

$client = new IMatOrderApiClient("https://imatsandbox.materialise.net/web-api/order/post","4f58e2cb-202f-41a9-ab9d-8ca4d3e674fd");

$result = $client->Post($requestData, "application/json");

echo(htmlspecialchars($result));
?>