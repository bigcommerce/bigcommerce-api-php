<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\Rule;
use Bigcommerce\Api\Client;

class RuleTest extends ResourceTestBase
{
    public function testCreatePassesThroughToConnection()
    {
        $rule = new Rule((object)array('id' => 1, 'product_id' => 1));
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/products/1/rules', (object)array());

        $rule->create();
    }

    public function testUpdatePassesThroughToConnection()
    {
        $rule = new Rule((object)array('id' => 1, 'product_id' => 1));
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/products/1/rules/1', (object)array());

        $rule->update();
    }

    public function testConditionsPassesThroughToConnection()
    {
        $rule = new Rule((object)array(
            'product_id' => 1,
            'conditions' => (object)array('resource' => '/products/1/rules/1/conditions')
        ));
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/products/1/rules/1/conditions')
            ->will($this->returnValue(array(array(), array())));

        $collection = $rule->conditions;
        $this->assertInternalType('array', $collection);
        foreach ($collection as $condition) {
            $this->assertInstanceOf('Bigcommerce\\Api\\Resources\\RuleCondition', $condition);
        }
    }
}
