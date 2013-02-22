<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

class Customer extends Resource
{

	protected $ignoreOnCreate = array(
		'id',
	);

	protected $ignoreOnUpdate = array(
		'id',
	);

	public function addresses()
	{
		return Client::getCollection($this->fields->addresses->resource, 'Address');
	}
	
	public function create()
	{
		return Client::createCustomer($this->getCreateFields());
	}
	
	public function update()
	{
		return Client::updateCustomer($this->id, $this->getUpdateFields());
	}
	
	public function delete()
	{
		return Client::deleteCustomer($this->id);
	}

}