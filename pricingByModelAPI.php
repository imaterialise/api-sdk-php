<?php

class IMatPricingByModelApiClient
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
		$headers = $this->CreateHeaders($request, $requestContentType);
		$result = $this->DoPost($this->_apiUrl, $headers, $request);
		
		return $result;
	}	
	
	private function CreateHeaders($body, $acceptContentType)
	{
		$headers = array (
			"Content-Type: application/json",
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
}

$requestData = '
{
   "models":[
      {
         "modelID":"[Model Id here]",
         "materialID":"035f4772-da8a-400b-8be4-2dd344b28ddb",
         "finishID":"bba2bebb-8895-4049-aeb0-ab651cee2597",
         "quantity":"2",
         "scale":"2.0"
      }
   ],
   "shipmentInfo": 
    {
        "countryCode": "BE",
        "stateCode": "",
        "city": "Leuven",
        "zipCode":  "3001"
    },
    "currency":"EUR"
}';

$client = new IMatPricingByModelApiClient("https://imatsandbox.materialise.net/web-api/pricing/model","[API Code here]");

$result = $client->Post($requestData, "application/json");

echo(htmlspecialchars($result));
?>