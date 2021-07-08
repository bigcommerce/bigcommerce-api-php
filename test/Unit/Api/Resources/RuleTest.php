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

    public function testRuleHasConditions()
    {
        $rule = new Rule((object)array(
            'product_id' => 1,
            'conditions' => array(
                array('option_value_id' => 1, 'product_option_id' => 1)
            )
        ));

        $this->assertInstanceOf('Bigcommerce\Api\Resources\RuleCondition', $rule->conditions[0]);

        $this->assertEquals(1, $rule->conditions[0]->option_value_id);
        $this->assertEquals(1, $rule->conditions[0]->product_option_id);
    }

    public function testRuleHasNoConditions()
    {
        $rule = new Rule((object)array(
            'product_id' => 1
        ));

        $this->assertEmpty($rule->conditions);
    }
}
