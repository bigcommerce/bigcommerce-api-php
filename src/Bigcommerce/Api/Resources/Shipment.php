<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

class Shipment extends Resource
{

	protected $ignoreOnCreate = array(
		'id',
		'order_id',
		'date_created',
		'customer_id',
		'shipping_method',
	);

	protected $ignoreOnUpdate = array(
		'id',
		'order_id',
		'date_created',
		'customer_id',
		'shipping_method',
		'items',
	);

	public function create()
	{
		return Client::createResource('/orders/' . $this->order_id . '/shipments', $this->getCreateFields());
	}

	public function update()
	{
		return Client::createResource('/orders/' . $this->order_id . '/shipments' . $this->id, $this->getCreateFields());
	}

}