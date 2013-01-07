<?php

class Bigcommerce_Api_Order extends Bigcommerce_Api_Resource
{

    public function shipments()
    {
        return Bigcommerce_Api::getCollection('/orders/'. $this->id . '/shipments', 'Shipment');
    }

    public function products()
    {
        return Bigcommerce_Api::getCollection($this->fields->products->resource, 'OrderProduct');
    }

    public function shipping_addresses()
    {
        return Bigcommerce_Api::getCollection($this->fields->shipping_addresses->resource, 'Address');
    }

    public function coupons()
    {
        return Bigcommerce_Api::getCollection($this->fields->coupons->resource, 'Coupon');
    }

    public function update()
    {
        $order = new stdClass;
        $order->status_id = $this->status_id;
        $order->is_deleted = $this->is_deleted;

        Bigcommerce_Api::updateResource('/orders/' . $this->id, $order);
    }

}
