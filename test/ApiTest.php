<?php

require_once dirname(__FILE__).'/../Bigcommerce/Api.php';

Bigcommerce_Api::configure(array(
    'store_url' => TEST_STORE_URL,
    'username'  => TEST_STORE_USER,
    'api_key'   => TEST_STORE_API_KEY,
));

Bigcommerce_Api::failOnError(true);

class Bigcommerce_Api_TimeTest extends PHPUnit_Framework_TestCase
{

	public function testTimestampPing()
	{
		$time = Bigcommerce_Api::getTime();
		$this->assertTrue($time instanceOf DateTime);
	}

}

class Bigcommerce_Api_ProductTest extends PHPUnit_Framework_TestCase
{

	public function testProductCollection()
	{
		$products = Bigcommerce_Api::getProducts();
		$this->assertTrue(is_array($products));
	}

	public function testProductResource()
	{
		$product = Bigcommerce_Api::getProduct(1);
		$this->assertEquals(1, $product->id);
	}

}