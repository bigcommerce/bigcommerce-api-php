<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\Customer;
use Bigcommerce\Api\Client;

class CustomerTest extends ResourceTestBase
{
    public function testAddressesPassesThroughToConnection()
    {
        $customer = new Customer((object)array('addresses' => (object)array('resource' => '/customers/1/addresses')));
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/customers/1/addresses')
            ->will($this->returnValue(array(array(), array())));

        foreach ($customer->addresses as $address) {
            $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\Address', $address);
        }
    }

    public function testCreatePassesThroughToConnection()
    {
        $customer = new Customer();
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/customers', $customer->getCreateFields());

        $customer->create();
    }

    public function testUpdatePassesThroughToConnection()
    {
        $customer = new Customer((object)(array('id' => 1)));
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/customers/1', $customer->getUpdateFields());

        $customer->update();
    }

    public function testDeletePassesThroughToConnection()
    {
        $customer = new Customer((object)(array('id' => 1)));
        $this->connection->expects($this->once())
            ->method('delete')
            ->with($this->basePath . '/customers/1');

        $customer->delete();
    }
}
