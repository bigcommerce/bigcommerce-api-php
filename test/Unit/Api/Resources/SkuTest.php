<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\Sku;
use Bigcommerce\Api\Client;
use Bigcommerce\Api\Resources\SkuOption;

class SkuTest extends ResourceTestBase
{
    public function testCreatePassesThroughToConnection()
    {
        $sku = $this->getSimpleSku();
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/products/1/skus', (object)array('id' => 1));

        $sku->create();
    }

    public function testUpdatePassesThroughToConnection()
    {
        $sku = $sku = $this->getSimpleSku();
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/products/1/skus/1', (object)array());

        $sku->update();
    }

    public function testSkuHasOptions()
    {
        $sku = new Sku((object)array('id' => 1, 'product_id' => 1, 'options' => array(
            array('option_value_id' => 1, 'product_option_id' => 1)
        )));

        $this->assertInstanceOf('Bigcommerce\Api\Resources\SkuOption', $sku->options[0]);

        $this->assertEquals(1, $sku->options[0]->option_value_id);
        $this->assertEquals(1, $sku->options[0]->product_option_id);
    }

    public function testSkuHasNoOptions()
    {
        $sku = $this->getSimpleSku();
        $this->assertEmpty($sku->options);
    }

    private function getSimpleSku()
    {
        return new Sku((object)array('id' => 1, 'product_id' => 1));
    }
}
