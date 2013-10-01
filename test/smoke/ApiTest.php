<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Bigcommerce\Api\Client as Bigcommerce;

/**
 * @group integration
 */
class ApiTest extends PHPUnit_Framework_TestCase
{

	public static function setUpBeforeClass()
	{
		Bigcommerce::configure(array(
		    'store_url' => getenv('TEST_STORE_URL'),
		    'username'  => getenv('TEST_STORE_USER'),
		    'api_key'   => getenv('TEST_STORE_API_KEY'),
		));

		Bigcommerce::setCipher('RC4-SHA');
		Bigcommerce::failOnError(true);
	}

	public function testTimestampPing()
	{
		$time = Bigcommerce::getTime();
		$this->assertTrue($time instanceOf DateTime);
	}
	
	public function testBasicResource()
	{
		$resource = Bigcommerce::getResource('/store');
		
		$this->assertInstanceOf('Bigcommerce\\Api\\Resource', $resource);
	}

	public function testProductCollection()
	{
		$products = Bigcommerce::getProducts();
		$this->assertTrue(is_array($products));
		
		return $products[0]->id;
	}

	/**
	 * @depends testProductCollection
	 */
	public function testProductsResource($id)
	{
		$product = Bigcommerce::getProduct($id);
		$this->assertEquals($id, $product->id);
	}

}
