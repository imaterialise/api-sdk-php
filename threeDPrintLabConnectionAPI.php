<?php

class IMat3DPrintLabApiClient
{
	private $_apiUrl;
	
	public function __construct($apiUrl)
	{
		$this->_apiUrl = $apiUrl;
	}
	
	public function Post($toolID, $useAjax, $forceEmbedding, $filePath, $fileUrl, $scale, $materialID, $finishID, $memberID, $reference)
	{
		$bodyPartsDelimiter = $this->CreateDelimiter();
		
		$body = $this->CreatePostBody($toolID, $useAjax, $forceEmbedding, $filePath, $fileUrl, $scale, $materialID, $finishID, $memberID, $reference, $bodyPartsDelimiter);
		$headers = $this->CreateHeaders($body, $bodyPartsDelimiter);
		$result = $this->DoPost($this->_apiUrl, $headers, $body);
		
		return $result;
	}	
		

	private function CreateHeaders($body, $delimiter)
	{
		$headers = array (
			"Content-Type: multipart/form-data; boundary=".$delimiter,
			"Content-Length:".strlen($body)
		);
		return $headers;
	}
		
	private function CreatePostBody($toolID, $useAjax, $forceEmbedding, $filePath, $fileUrl, $scale, $materialID, $finishID, $memberID, $reference, $delimiter)
	{
		$tooIDPart = $this->CreateParameterPart("plugin", $toolID);
		$useAjaxPart = $this->CreateParameterPart("useAjax", $useAjax);
		$forceEmbeddingPart = $this->CreateParameterPart("forceEmbedding", $forceEmbedding);
		$fileUrlPart = $this->CreateParameterPart("fileUrl", $fileUrl);
		$scalePart = $this->CreateParameterPart("scale", $scale);
		$materialIDPart = $this->CreateParameterPart("materialID", $materialID);
		$finishIDPart = $this->CreateParameterPart("finishID", $finishID);
		$memberIDPart = $this->CreateParameterPart("memberID", $memberID);
		$referencePart = $this->CreateParameterPart("reference", $reference);

		$filePath = $this->CreateFilePart($filePath);
		
		$all = array($tooIDPart, $useAjaxPart, $forceEmbeddingPart, $fileUrlPart, $filePath, $scalePart, $materialIDPart, $finishIDPart, $memberIDPart, $referencePart);
		$body = $this->CombineParts($all, $delimiter);
		
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

	private function CreateParameterPart($paramName, $paramValue)
	{
		$data= "Content-Disposition: form-data; name=\"".$paramName."\"\r\n";
		$data.= "Content-Type: text/html\r\n\r\n";
		$data.= $paramValue;
		
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

		curl_setopt($curl,CURLOPT_FOLLOWLOCATION, false);		
		curl_setopt($curl,CURLOPT_HEADER, true);
		curl_setopt($curl,CURLOPT_POSTFIELDS , $body);
		curl_setopt($curl,CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl,CURLOPT_POST, true);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER , true);
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);


		$result = curl_exec($curl);
		curl_close($curl);
				
		$headers = $this->http_response_headers($result);
		foreach ($headers as $header) {
			$str = http_response_code($header);
			$hdr_arr = $this->http_response_header_lines($header);
			if (isset($hdr_arr['Location'])) {
				return $hdr_arr['Location'];
			}
		}
		
		return "Error. Location header was found in response.";
	}
	
	private function http_response_header_lines($hdr_str)
	{
		$lines = explode("\n", $hdr_str);
		$hdr_arr['status_line'] = trim(array_shift($lines));
		foreach ($lines as $line) {
			list($key, $val) = explode(':', $line, 2);
			$hdr_arr[trim($key)] = trim($val);
		}
		return $hdr_arr;
	}
	
	private function http_response_headers($ret_str)
	{
		$hdrs = array();
		$arr = explode("\r\n\r\n", $ret_str);
		foreach ($arr as $each) {
			if (substr($each, 0, 4) == 'HTTP') {
				$hdrs[] = $each;
			}
		}
		return $hdrs;
	}
}

$toolID = "[tool ID here]";
$useAjax = "false";
$forceEmbedding = "false";
//use filePath or fileUrl, but not both at the same time
$filePath = "[File path here]";
$fileUrl = ""; 
$scale = "100";
$materialID = "";
$finishID = "";
$memberID = "";
$reference = "";


$client = new IMat3DPrintLabApiClient("https://imatsandbox.materialise.net/Upload");

$result = $client->Post($toolID, $useAjax, $forceEmbedding, $filePath, $fileUrl, $scale, $materialID, $finishID, $memberID, $reference);

echo($result);
?>