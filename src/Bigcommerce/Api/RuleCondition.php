<?php

/**
 * Conditions that will be applied to a product based on the rule.
 */
class Bigcommerce_Api_RuleCondition extends Bigcommerce_Api_Resource
{

    protected $ignoreOnCreate = array(
        'id',
    );

    protected $ignoreOnUpdate = array(
        'id',
        'rule_id',
    );

    public $product_id;

    public function create()
    {
        return Bigcommerce_Api::createResource('/products/' . $this->product_id . '/rules/' . $this->rule_id . '/conditions' , $this->getCreateFields());
    }

    public function update()
    {
        Bigcommerce_Api::updateResource('/products/' . $this->product_id . '/rules/' . $this->rule_id . '/conditions/' .$this->id , $this->getUpdateFields());
    }
}
