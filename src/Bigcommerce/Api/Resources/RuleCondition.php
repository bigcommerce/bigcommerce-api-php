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

	public function create()
	{
		return Client::createResource('/products/' . $this->product_id . '/rules/' . $this->rule_id . '/conditions' , $this->getCreateFields());
	}

	public function update()
	{
		Client::updateResource('/products/' . $this->product_id . '/rules/' . $this->rule_id . '/conditions/' .$this->id , $this->getUpdateFields());
	}
}