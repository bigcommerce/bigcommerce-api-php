<?php

class BigCommerce_Api2_Connection
{
	private $use_xml = false;
	
	public function useXml()
	{
		$this->use_xml = true;
	}
	
	public function authenticate($username, $password)
	{
		//base64_encode($username . ":" . $api_key);
	}
	
	public function get($url)
	{
		if ($use_xml) {
			
		}
	}
	
}

class BigCommerce_Api2_Error extends Exception
{
	
}

class BigCommerce_Api2_ClientError extends Exception
{
	
}

class BigCommerce_Api2_ServerError extends Exception
{
	
}