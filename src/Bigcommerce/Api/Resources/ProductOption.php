<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

/**
 * Relationship between a product and an option applied from an option set.
 */
class ProductOption extends Resource
{

	public function option()
	{
		return self::getResource('/options/' . $this->option_id, 'Option');
	}

}


