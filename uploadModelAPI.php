<?php

class IMatUploadModelApiClient
{
	private $_apiUrl;
	
	public function __construct($apiUrl)
	{
		$this->_apiUrl = $apiUrl;
	}
	
	public function Post($filePath, $fileUnits, $fileUrl)
	{
		$bodyPartsDelimiter = $this->CreateDelimiter();
		
		$body = $this->CreatePostBody($filePath, $fileUnits, $fileUrl, $bodyPartsDelimiter);
		$headers = $this->CreateHeaders($body, $requestContentType, $bodyPartsDelimiter);
		$result = $this->DoPost($this->_apiUrl, $headers, $body);
		
		return $result;
	}	
		

	private function CreateHeaders($body, $acceptContentType, $delimiter)
	{
		$headers = array (
			"Content-Type: multipart/form-data; boundary=".$delimiter,
			"Accept: ".$acceptContentType,
			"Content-Length: ".strlen($body)
		);
		return $headers;
	}
		
		
	private function CreatePostBody($filePath, $fileUnits, $fileUrl, $delimiter)
	{
		$fileUnitsPart = $this->CreateFileUnitsPart($fileUnits);
		$fileUrlPart = $this->CreateFileUrlPart($fileUrl);
		$filePart = $this->CreateFilePart($filePath);
		
		$body = $this->CombineParts(array($fileUnitsPart, $fileUrlPart, $filePart), $delimiter);
		
		return $body;
	}
		
	private function CombineParts($parts, $delimiter)
	{
		$result = "--$delimiter\r\n".implode("\r\n--$delimiter\r\n", $parts)."\r\n--$delimiter--\r\n";
		return $result;
	}
	
	private function CreateDelimiter()
	{
		$delimiter = "----------------".uniqid();	
		return $delimiter;
	}

	private function CreateFileUnitsPart($fileUnits)
	{
		$data= "Content-Disposition: form-data; name=\"fileUnits\"\r\n";
		$data.= "Content-Type: text/html\r\n\r\n";
		$data.= $fileUnits;
		
		return $data;
	}
	
	private function CreateFileUrlPart($fileUrl)
	{
		$data= "Content-Disposition: form-data; name=\"fileUrl\"\r\n";
		$data.= "Content-Type: text/html\r\n\r\n";
		$data.= $fileUrl;		
		return $data;
	}
		
	private function CreateFilePart($filePath)
	{
		$fileData = file_get_contents($filePath);
		
		$fileInfo = pathinfo($filePath);
		
		$fileName = $fileInfo["basename"];
		
		$data= "Content-Disposition: form-data; name=\"file\"; filename=\"$fileName\"\r\n";
		$data.= "Content-Type: application/octet-stream\r\n\r\n";
		$data.= $fileData;
	
		return $data;
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
}

$filePath = "[File Path here]";
$fileUnits = "mm";
$fileUrl = "";

$client = new IMatUploadModelApiClient("https://imatsandbox.materialise.net/web-api/tool/[ToolID here]/model");

$result = $client->Post($filePath, $fileUnits, $fileUrl);

echo(htmlspecialchars($result));
?>