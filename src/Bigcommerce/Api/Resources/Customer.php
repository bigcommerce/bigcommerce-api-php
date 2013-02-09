<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

class Customer extends Resource
{

	public function addresses()
	{
		return Client::getCollection($this->fields->addresses->resource, 'Address');
	}

}