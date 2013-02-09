<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

class Brand extends Resource
{

	protected $ignoreOnCreate = array(
		'id',
	);

	protected $ignoreOnUpdate = array(
		'id',
	);

	public function create()
	{
		return Client::createBrand($this->getCreateFields());
	}

	public function update()
	{
		return Client::updateBrand($this->id, $this->getUpdateFields());
	}

}