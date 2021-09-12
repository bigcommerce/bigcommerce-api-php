<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

/**
 * Conditions that will be applied to a product based on the rule.
 */
class RuleCondition extends Resource
{
    protected $ignoreOnCreate = array(
        'id',
    );

    protected $ignoreOnUpdate = array(
        'id',
        'rule_id',
    );

    public $product_id;

    public $urls = array(
        "v2" => "/products"
    );


    public function create()
    {
        return Client::createResource('/' . $this->product_id . '/rules/' . $this->fields->rule_id . '/conditions', $this->getCreateFields(), 'RuleCondition', "v2");
    }

    public function update()
    {
        Client::updateResource('/' . $this->product_id . '/rules/' . $this->fields->rule_id . '/conditions/' . $this->id, $this->getUpdateFields(), 'RuleCondition', 'v3');
    }
}
