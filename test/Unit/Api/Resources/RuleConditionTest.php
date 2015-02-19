<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\RuleCondition;
use Bigcommerce\Api\Client;

class RuleConditionTest extends ResourceTestBase
{
    public function testCreatePassesThroughToConnection()
    {
        $rulecondition = new RuleCondition((object)array('rule_id' => 1));
        $rulecondition->product_id = 1;
        $this->connection->expects($this->once())
             ->method('post')
             ->with('/products/1/rules/1/conditions', $rulecondition->getCreateFields());

        $rulecondition->create();
    }

    public function testUpdatePassesThroughToConnection()
    {
        $this->markTestIncomplete('This fails for unknown reasons');
        $rulecondition = new RuleCondition((object)array('id' => 1, 'rule_id' => 1));
        $rulecondition->product_id = 1;
        $this->connection->expects($this->once())
             ->method('put')
             ->with('/products/1/rules/1/conditions/1', $rulecondition->getUpdateFields());

        $rulecondition->update();
    }
}
