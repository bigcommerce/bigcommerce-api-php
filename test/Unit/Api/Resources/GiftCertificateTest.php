<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\Coupon;
use Bigcommerce\Api\Client;

class GiftCertificateTest extends ResourceTestBase
{
    public function testCreatePassesThroughToConnection()
    {
        $giftCertificate = new GiftCertificate();
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/gift_certificates', $giftCertificate->getCreateFields());

        $giftCertificate->create();
    }

    public function testUpdatePassesThroughToConnection()
    {
        $giftCertificate = new GiftCertificate((object)(array('id' => 1)));
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/gift_certificates/1', $giftCertificate->getUpdateFields());

        $giftCertificate->update();
    }
}