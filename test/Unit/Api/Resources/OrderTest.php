<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\Order;
use Bigcommerce\Api\Client;

class OrderTest extends ResourceTestBase
{
    public function testUpdatePassesThroughToConnection()
    {
        $order = new Order((object)(array('id' => 1, 'status_id' => 5, 'is_deleted' => false)));
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/orders/1', (object)array('status_id' => 5, 'is_deleted' => false));

        $order->update();
    }

    public function testShipmentsPassesThroughToConnection()
    {
        $order = new Order((object)array('id' => 1));
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/orders/1/shipments', false)
            ->will($this->returnValue(array(array(), array())));

        foreach ($order->shipments as $shipment) {
            $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\Shipment', $shipment);
        }
    }

    public function testProductsPassesThroughToConnection()
    {
        $order = new Order((object)array('products' => (object)array('resource' => '/orders/1/products')));
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/orders/1/products')
            ->will($this->returnValue(array(array(), array())));

        foreach ($order->products as $product) {
            $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\OrderProduct', $product);
        }
    }

    public function testShippingAddressesPassesThroughToConnection()
    {
        $order = new Order((object)array(
            'shipping_addresses' => (object)array('resource' => '/orders/1/shippingaddresses')
        ));
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/orders/1/shippingaddresses')
            ->will($this->returnValue(array(array(), array())));

        foreach ($order->shipping_addresses as $address) {
            $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\Address', $address);
        }
    }

    public function testCouponsPassesThroughToConnection()
    {
        $order = new Order((object)array('coupons' => (object)array('resource' => '/orders/1/coupons')));
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/orders/1/coupons')
            ->will($this->returnValue(array(array(), array())));

        foreach ($order->coupons as $coupon) {
            $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\Coupon', $coupon);
        }
    }
}
