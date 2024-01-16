<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\Order;
use Bigcommerce\Api\Client;

class OrderTest extends ResourceTestBase
{
    public function testUpdatePassesThroughToConnection()
    {
        $order = new Order((object)(['id' => 1, 'status_id' => 5, 'is_deleted' => false]));
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/orders/1', (object)['status_id' => 5, 'is_deleted' => false]);

        $order->update();
    }

    public function testShipmentsPassesThroughToConnection()
    {
        $order = new Order((object)['id' => 1]);
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/orders/1/shipments', false)
            ->will($this->returnValue([[], []]));

        foreach ($order->shipments as $shipment) {
            $this->assertInstanceOf(\Bigcommerce\Api\Resources\Shipment::class, $shipment);
        }
    }

    public function testProductsPassesThroughToConnection()
    {
        $order = new Order((object)['products' => (object)['resource' => '/orders/1/products']]);
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/orders/1/products')
            ->will($this->returnValue([[], []]));

        foreach ($order->products as $product) {
            $this->assertInstanceOf(\Bigcommerce\Api\Resources\OrderProduct::class, $product);
        }
    }

    public function testShippingAddressesPassesThroughToConnection()
    {
        $order = new Order((object)['shipping_addresses' => (object)['resource' => '/orders/1/shippingaddresses']]);
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/orders/1/shippingaddresses')
            ->will($this->returnValue([[], []]));

        foreach ($order->shipping_addresses as $address) {
            $this->assertInstanceOf(\Bigcommerce\Api\Resources\Address::class, $address);
        }
    }

    public function testCouponsPassesThroughToConnection()
    {
        $order = new Order((object)['coupons' => (object)['resource' => '/orders/1/coupons']]);
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/orders/1/coupons')
            ->will($this->returnValue([[], []]));

        foreach ($order->coupons as $coupon) {
            $this->assertInstanceOf(\Bigcommerce\Api\Resources\Coupon::class, $coupon);
        }
    }
}
