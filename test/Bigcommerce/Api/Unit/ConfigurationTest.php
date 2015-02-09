<?php
namespace Tests\Bigcommerce\Api\Unit;

use Bigcommerce\Api\Client;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
	public function testApiPathConfiguration()
	{
		Client::configure(array(
			'store_url' => 'https://localhost',
			'user_id' => 'admin',
			'token'   => 'onetwothreefour',
		));
		
		$this->assertEquals('https://localhost', Client::$api_path);
	}
	
	public function testApiPathConfigurationWithPortAndTrailingSlash()
	{
		Client::configure(array(
			'store_url' => 'https://127.0.0.1:3000/',
			'user_id' => 'admin',
			'token'   => 'onetwothreefour',
		));
		
		$this->assertEquals('https://127.0.0.1:3000', Client::$api_path);
	}
}
