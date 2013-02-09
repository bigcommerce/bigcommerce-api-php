<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

class OptionSetOption extends Resource
{

	protected $ignoreOnCreate = array(
		'id',
		'option_set_id',
	);

	protected $ignoreOnUpdate = array(
		'id',
		'option_set_id',
		'option_id',
	);

	public function option()
	{
		return Client::getCollection($this->fields->option->resource);
	}

	public function create()
	{
		return Client::createResource('/optionsets/options', $this->getCreateFields());
	}

	public function update()
	{
		Client::updateResource('/optionsets/options/' . $this->id, $this->getUpdateFields());
	}

}
