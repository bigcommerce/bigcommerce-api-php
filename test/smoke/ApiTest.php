<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Bigcommerce\Api\Client as Bigcommerce;

class ApiTest extends PHPUnit_Framework_TestCase
{

	public static function setUpBeforeClass()
	{
		Bigcommerce::configure(array(
		    'store_url' => getenv('TEST_STORE_URL'),
		    'username'  => getenv('TEST_STORE_USER'),
		    'api_key'   => getenv('TEST_STORE_API_KEY'),
		));

		Bigcommerce::failOnError(true);		
	}

	public function testTimestampPing()
	{
		$time = Bigcommerce::getTime();
		$this->assertTrue($time instanceOf DateTime);
	}

	public function testProductCollection()
	{
		$products = Bigcommerce::getProducts();
		$this->assertTrue(is_array($products));
	}

	public function testProductsResource()
	{
		$product = Bigcommerce::getProduct(1);
		$this->assertEquals(1, $product->id);
	}

}