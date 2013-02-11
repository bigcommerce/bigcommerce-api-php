<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

class OptionSet extends Resource
{

	protected $ignoreOnCreate = array(
		'id',
	);

	protected $ignoreOnUpdate = array(
		'id',
	);

	public function options()
	{
		return Client::getCollection($this->fields->options->resource, 'OptionSetOption');
	}

	public function create()
	{
		return Client::createResource('/optionsets', $this->getCreateFields());
	}

	public function update()
	{
		Client::updateResource('/optionsets/' . $this->id, $this->getUpdateFields());
	}

}