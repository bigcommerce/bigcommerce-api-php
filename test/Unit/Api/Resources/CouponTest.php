<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\Coupon;
use Bigcommerce\Api\Client;

class CouponTest extends ResourceTestBase
{
    public function testCreatePassesThroughToConnection()
    {
        $coupon = new Coupon();
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/coupons', $coupon->getCreateFields());

        $coupon->create();
    }

    public function testUpdatePassesThroughToConnection()
    {
        $coupon = new Coupon((object)(array('id' => 1)));
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/coupons/1', $coupon->getUpdateFields());

        $coupon->update();
    }
}
