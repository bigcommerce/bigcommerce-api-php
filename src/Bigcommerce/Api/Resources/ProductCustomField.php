<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

/**
 * A custom field on a product.
 */
class ProductCustomField extends Resource
{

	protected $ignoreOnCreate = array(
		'id',
		'product_id'
	);

	protected $ignoreOnUpdate = array(
		'id',
		'product_id'
	);

	public function create()
	{
	    return Client::createResource('/products/' . $this->product_id . '/custom_fields', $this->getCreateFields());
	}

	public function update()
	{
	    Client::updateResource('/products/' . $this->product_id . '/custom_fields/' . $this->id, $this->getUpdateFields());
	}

	public function delete()
	{
	    Client::deleteResource('/products/' . $this->product_id . '/custom_fields/' . $this->id);
	}
}

