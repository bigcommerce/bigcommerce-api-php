<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

/**
 * A product option rule.
 */
class Rule extends Resource
{
    protected $ignoreOnCreate = array(
        'id',
        'product_id',
    );

    protected $ignoreOnUpdate = array(
        'id',
        'product_id',
    );

    public $urls = array(
        "v2" => "/products"
    );

    public function conditions()
    {
        $conditions = Client::getCollection($this->fields->conditions->resource, 'RuleCondition');

        foreach ($conditions as $condition) {
            $condition->product_id = $this->product_id;
        }

        return $conditions;
    }

    public function create()
    {
        return Client::createResource('/' . $this->fields->product_id . '/rules', $this->getCreateFields(), 'Rule', 'v2');
    }

    public function update()
    {
        Client::updateResource('/' . $this->fields->product_id . '/rules/' . $this->fields->id, $this->getUpdateFields(), 'Rule', 'v2');
    }
}
