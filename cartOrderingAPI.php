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
	
	public function Post($request, $deliveryNoteFilePath, $invoiceFilePath, $requestContentType)
	{

		$bodyPartsDelimiter = $this->CreateDalimiter();
		$body = $this->CreatePostBody($request, $deliveryNoteFilePath, $invoiceFilePath, $requestContentType, $bodyPartsDelimiter);
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
	
	private function CreatePostBody($request, $deliveryNoteFilePath, $invoiceFilePath, $requestContentType, $delimiter)
	{
		$requestPart = $this->CreateRequestPart($request, $requestContentType);
		$deliveryNoteFilePart = $this->CreateFilePart("MyDeliveryNoteFile", $deliveryNoteFilePath);
		$invoiceFilePart = $this->CreateFilePart("MyInvoiceFile", $invoiceFilePath);
		
		$body = $this->CombineParts(array($requestPart, $deliveryNoteFilePart, $invoiceFilePart), $delimiter);
		
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
	
	private function CreateFilePart($name, $filePath)
	{
	
		$fileData = file_get_contents($filePath);
		
		$fileInfo = pathinfo($filePath);
		
		$fileName = $fileInfo["basename"];

		$data= "Content-Disposition: form-data; name=\"".$name."\"; filename=\"".$fileName."\"\r\n";
		$data.= "Content-Type: application/octet-stream\r\n\r\n";
		$data.= $fileData;
		return $data;
	}	
}

$requestData = '
{
	"cartID":"[cart id here]", 
	"myOrderReference":"some reference",
	"directMailingAllowed":"false",
	"shipmentService":"",
	"myInvoiceLink":"",
    "myDeliveryNoteLink":"",
	"remarks":""
}';

$deliveryNoteFilePath = "[File Path here]";//file path or empty string
$invoiceFilePath = "[File Path here]";//file path or empty string

$client = new IMatOrderApiClient("https://imatsandbox.materialise.net/web-api/order/post","[API Code here]");

$result = $client->Post($requestData, $deliveryNoteFilePath, $invoiceFilePath, "application/json");

echo(htmlspecialchars($result));
?>