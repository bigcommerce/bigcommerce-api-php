<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\RuleCondition;
use Bigcommerce\Api\Client;

class RuleConditionTest extends ResourceTestBase
{
    public function testCreatePassesThroughToConnection()
    {
        $ruleCondition = new RuleCondition((object)array('rule_id' => 1));
        $ruleCondition->product_id = 1;
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/products/1/rules/1/conditions', $ruleCondition->getCreateFields());

        $ruleCondition->create();
    }

    public function testUpdatePassesThroughToConnection()
    {
        $ruleCondition = new RuleCondition((object)array('id' => 1, 'rule_id' => 1));
        $ruleCondition->product_id = 1;
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/products/1/rules/1/conditions/1', $ruleCondition->getUpdateFields());

        $ruleCondition->update();
    }
}
