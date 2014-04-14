<?php

class IMatCartItemApiClient
{
	private $_apiCode;
	private $_apiUrl;
	
	public function __construct($apiUrl, $apiCode)
	{
		$this->_apiUrl = $apiUrl;
		$this->_apiCode = $apiCode;
	}
	
	public function Post($request, $requestContentType, $modelFilePath)
	{
		$bodyPartsDelimiter = $this->CreateDalimiter();
		$body = $this->CreatePostBody($request, $requestContentType, $modelFilePath, $bodyPartsDelimiter);
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
	
	private function CreatePostBody($request, $requestContentType, $filePath, $delimiter)
	{
		$filePart = $this->CreateFilePart($filePath);
		$requestPart = $this->CreateRequestPart($request, $requestContentType);
		$body = $this->CombineParts(array($filePart, $requestPart), $delimiter);
		return $body;
	}
	
	private function CreateFilePart($filePath)
	{
		$fileData = file_get_contents($filePath);
		
		$fileInfo = pathinfo($filePath);
		$fileName = $fileInfo["basename"];
		
		$data= "Content-Disposition: form-data; name=\"file[]\"; filename=\"$fileName\"\r\n";
		$data.= "Content-Type: application/octet-stream\r\n\r\n";
		$data.= $fileData;
		
		return $data;
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
   cartItems:[
      {
         "toolID":"[Tool Id here]", 
         "MyCartItemReference":"some reference",
         "modelID":"",
         "modelFileName":"_porsche.stl",
         "fileUnits":"mm",
         "fileScaleFactor":"1",
         "materialID":"035f4772-da8a-400b-8be4-2dd344b28ddb",
         "finishID":"bba2bebb-8895-4049-aeb0-ab651cee2597",
         "quantity":"2",
         "xDimMm":"12",
         "yDimMm":"12",
         "zDimMm":"12",
         "volumeCm3":"2.0",
         "surfaceCm2":"100.0",
         "iMatAPIPrice": "25.0",
         "mySalesPrice": "26.0",
      }
   ],
   "currency":"EUR"
}';


$filePath = "<<path to model file>>";

$client = new IMatCartItemApiClient("https://i.materialise.com/web-api/cartitems/register","[API Code here]");

$result = $client->Post($requestData, "text/json", $filePath);

echo(htmlspecialchars($result));
?>