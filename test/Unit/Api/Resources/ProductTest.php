<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\Product;
use Bigcommerce\Api\Client;

class ProductTest extends ResourceTestBase
{
    public function testCreatePassesThroughToConnection()
    {
        $product = new Product((object)['id' => 1]);
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/products', (object)['id' => 1]);

        $product->create();
    }

    public function testUpdatePassesThroughToConnection()
    {
        $product = new Product((object)['id' => 1]);
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/products/1', (object)[]);

        $product->update();
    }

    public function testDeletePassesThroughToConnection()
    {
        $product = new Product((object)['id' => 1]);
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/products/1');

        $product->delete();
    }

    public static function propertyCollections()
    {
        return [['images', 'ProductImage'], ['skus', 'Sku'], ['rules', 'Rule'], ['videos', 'ProductVideo'], ['custom_fields', 'ProductCustomField'], ['configurable_fields', 'ProductConfigurableField'], ['discount_rules', 'DiscountRule'], ['options', 'ProductOption']];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('propertyCollections')]
    public function testPropertyCollectionsPassThroughToTheConnection($property, $className)
    {
        $url = '/products/1/' . $property;
        $product = new Product((object)['id' => 1, $property => (object)['resource' => $url]]);
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . $url)
            ->will($this->returnValue([[], []]));

        $collection = $product->$property;
        $this->assertIsArray($collection);
        foreach ($collection as $value) {
            $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\' . $className, $value);
        }
    }

    public static function properties()
    {
        return [['brand', 'Brand'], ['option_set', 'OptionSet'], ['tax_class', 'TaxClass']];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('properties')]
    public function testPropertiesPassThroughToTheConnection($property, $className)
    {
        $url = '/products/1/' . $property;
        $product = new Product((object)[$property => (object)['resource' => $url]]);
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . $url)
            ->will($this->returnValue([[]]));

        $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\' . $className, $product->$property);
    }
}
