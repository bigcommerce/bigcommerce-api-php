<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

class Category extends Resource
{

	protected $ignoreOnCreate = array(
		'id',
		'parent_category_list',
	);

	protected $ignoreOnUpdate = array(
		'id',
		'parent_category_list',
	);

	public function create()
	{
		return Client::createCategory($this->getCreateFields());
	}

	public function update()
	{
		return Client::updateCategory($this->id, $this->getUpdateFields());
	}

}