<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

/**
 * An image which is displayed on the storefront for a product.
 */
class ProductImage extends Resource
{

	protected $ignoreOnCreate = array(
		'id',
		'date_created',
		'product_id',
	);

	protected $ignoreOnUpdate = array(
		'id',
		'date_created',
		'product_id',
	);

	

	public function create()
	{
		return Client::createResource('/products/' . $this->product_id . '/images' , $this->getCreateFields());
	}

	public function update()
	{
		Client::updateResource('/products/' . $this->product_id . '/images/' . $this->id , $this->getUpdateFields());
	}

}