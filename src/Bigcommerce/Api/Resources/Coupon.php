<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

class Coupon extends Resource
{
	protected $ignoreOnCreate = array(
		'id',
		'num_uses',
	);

	protected $ignoreOnUpdate = array(
		'id',
		'num_uses',
	);

	public function create()
	{
		return Client::createCoupon($this->getCreateFields());
	}

	public function update()
	{
		return Client::updateCoupon($this->id, $this->getUpdateFields());
	}
}
