<?php

/**
 * A product option rule.
 */
class Bigcommerce_Api_Rule extends Bigcommerce_Api_Resource
{

    protected $ignoreOnCreate = array(
        'id',
        'product_id',
    );

    protected $ignoreOnUpdate = array(
        'id',
        'product_id',
    );

    public function conditions()
    {
        $conditions = Bigcommerce_Api::getCollection($this->fields->conditions->resource, 'RuleCondition');

        foreach ($conditions as $condition) {
            $condition->product_id = $this->product_id;
        }

        return $conditions;
    }

    public function create()
    {
        return Bigcommerce_Api::createResource('/products/' . $this->product_id . '/rules', $this->getCreateFields());
    }

    public function update()
    {
        Bigcommerce_Api::updateResource('/products/' . $this->product_id . '/rules/' . $this->id, $this->getUpdateFields());
    }

}
