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

	public function conditions()
	{
		$conditions = Client::getCollection($this->fields->conditions->resource, 'RuleCondition');

		foreach($conditions as $condition) {
			$condition->product_id = $this->product_id;
		}

		return $conditions;
	}

	public function create()
	{
		return Client::createResource('/products/' . $this->product_id . '/rules', $this->getCreateFields());
	}

	public function update()
	{
		Client::updateResource('/products/' . $this->product_id . '/rules/' . $this->id, $this->getUpdateFields());
	}

}