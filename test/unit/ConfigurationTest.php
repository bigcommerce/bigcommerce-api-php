<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Bigcommerce\Api\Client;

class ConfigurationTest extends PHPUnit_Framework_TestCase
{
	public function testApiPathConfiguration()
	{
		Client::configure(array(
			'store_url' => 'https://localhost',
			'username' => 'admin',
			'api_key' => 'onetwothreefour',
		));
		
		$this->assertEquals('https://localhost/api/v2', Client::$api_path);
	}
	
	public function testApiPathConfigurationWithPortAndTrailingSlash()
	{
		Client::configure(array(
			'store_url' => 'https://127.0.0.1:3000/',
			'username' => 'admin',
			'api_key' => 'onetwothreefour',
		));
		
		$this->assertEquals('https://127.0.0.1:3000/api/v2', Client::$api_path);
	}
}