<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

/**
 * An option.
 */
class Option extends Resource
{

	public function values()
	{
		return Client::getCollection($this->fields->values->resource, 'OptionValue');
	}

}


