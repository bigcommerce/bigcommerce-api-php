<?php
namespace Bigcommerce\Test\Unit\Api\Resources;

use Bigcommerce\Api\Resources\Rule;
use Bigcommerce\Api\Client;

class RuleTest extends ResourceTestBase
{
    public function testCreatePassesThroughToConnection()
    {
        $rule = new Rule((object)['id' => 1, 'product_id' => 1]);
        $this->connection->expects($this->once())
            ->method('post')
            ->with($this->basePath . '/products/1/rules', (object)[]);

        $rule->create();
    }

    public function testUpdatePassesThroughToConnection()
    {
        $rule = new Rule((object)['id' => 1, 'product_id' => 1]);
        $this->connection->expects($this->once())
            ->method('put')
            ->with($this->basePath . '/products/1/rules/1', (object)[]);

        $rule->update();
    }

    public function testConditionsPassesThroughToConnection()
    {
        $rule = new Rule((object)['product_id' => 1, 'conditions' => (object)['resource' => '/products/1/rules/1/conditions']]);
        $this->connection->expects($this->once())
            ->method('get')
            ->with($this->basePath . '/products/1/rules/1/conditions')
            ->will($this->returnValue([[], []]));

        $collection = $rule->conditions;
        $this->assertIsArray($collection);
        foreach ($collection as $condition) {
            $this->assertInstanceOf(\Bigcommerce\Api\Resources\RuleCondition::class, $condition);
        }
    }
}
