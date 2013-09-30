<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

class Order extends Resource
{

	public function shipments()
	{
		return Client::getCollection('/orders/'. $this->id . '/shipments', 'Shipment');
	}

	public function products()
	{
		return Client::getCollection($this->fields->products->resource, 'OrderProduct');
	}

	public function shipping_addresses()
	{
		return Client::getCollection($this->fields->shipping_addresses->resource, 'Address');
	}

	public function coupons()
	{
		return Client::getCollection($this->fields->coupons->resource, 'Coupon');
	}

	public function update()
	{
		$order = new \stdClass; // to use stdClass in global namespace use this...
		$order->status_id = $this->status_id;
		$order->is_deleted = $this->is_deleted;

		Client::updateResource('/orders/' . $this->id, $order);
	}

}
