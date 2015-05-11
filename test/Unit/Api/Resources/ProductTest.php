<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\Product;
use Bigcommerce\Api\Client;

class ProductTest extends ResourceTestBase
{
    public function testCreatePassesThroughToConnection()
    {
        $product = new Product((object)array('id' => 1));
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/products', (object)array('id' => 1));

        $product->create();
    }

    public function testUpdatePassesThroughToConnection()
    {
        $product = new Product((object)array('id' => 1));
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/products/1', (object)array());

        $product->update();
    }

    public function testDeletePassesThroughToConnection()
    {
        $product = new Product((object)array('id' => 1));
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/products/1');

        $product->delete();
    }

    public function propertyCollections()
    {
        return array(
            array('images', 'ProductImage'),
            array('skus', 'Sku'),
            array('rules', 'Rule'),
            array('videos', 'ProductVideo'),
            array('custom_fields', 'ProductCustomField'),
            array('configurable_fields', 'ProductConfigurableField'),
            array('discount_rules', 'DiscountRule'),
            array('options', 'ProductOption')
        );
    }

    /**
     * @dataProvider propertyCollections
     */
    public function testPropertyCollectionsPassThroughToTheConnection($property, $className)
    {
        $url = '/products/1/' . $property;
        $product = new Product((object)array('id' => 1, $property => (object)array('resource' => $url)));
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . $url)
            ->will($this->returnValue(array(array(), array())));

        $collection = $product->$property;
        $this->assertInternalType('array', $collection);
        foreach ($collection as $value) {
            $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\' . $className, $value);
        }
    }

    public function properties()
    {
        return array(
            array('brand', 'Brand'),
            array('option_set', 'OptionSet'),
            array('tax_class', 'TaxClass')
        );
    }

    /**
     * @dataProvider properties
     */
    public function testPropertiesPassThroughToTheConnection($property, $className)
    {
        $url = '/products/1/' . $property;
        $product = new Product((object)array($property => (object)array('resource' => $url)));
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . $url)
            ->will($this->returnValue(array(array())));

        $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\' . $className, $product->$property);
    }
}
