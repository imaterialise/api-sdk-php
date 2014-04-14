<?php

class IMatCartRegistrationAPIClient
{
	private $_apiUrl;
	
	public function __construct($apiUrl)
	{
		$this->_apiUrl = $apiUrl;
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
    "MyCartReference": "My cart",
    "Currency": "USD",
    "ReturnUrl": "http://mysite.com/success.html",
    "OrderConfirmationUrl": "http://mysite.com/confirm.html",
    "FailureUrl": "http://mysite.com/failure.html",
    "CartItems":[
       { 
          "CartItemID": "[Cart Id here]"
       }],
    "ShippingInfo": {
      "FirstName": "John",
      "LastName": "Smith",
      "Email": "test@test.com",
      "Phone": "1234567",
      "Company": "No company",
      "Line1": "North Street",
      "CountryCode": "US",
      "StateCode":"NY",
      "ZipCode": "10001",
      "City": "New York"
    },
    "BillingInfo": {
      "FirstName": "John",
      "LastName": "Smith",
      "Email": "test@test.com",
      "Phone": "1234567",
      "Company": "No company",
      "Line1": "North Street",
      "CountryCode": "US",
      "StateCode":"NY",
      "ZipCode": "10001",
      "City": "New York"
    }
}';

$client = new IMatCartRegistrationAPIClient(""https://imatsandbox.materialise.net/web-api/cart/post");

$result = $client->Post($requestData, "application/json");

echo(htmlspecialchars($result));
?>