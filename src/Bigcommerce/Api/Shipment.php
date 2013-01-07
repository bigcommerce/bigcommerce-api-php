<?php

class Bigcommerce_Api_Shipment extends Bigcommerce_Api_Resource
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
        return Bigcommerce_Api::createResource('/orders/' . $this->order_id . '/shipments', $this->getCreateFields());
    }

    public function update()
    {
        return Bigcommerce_Api::createResource('/orders/' . $this->order_id . '/shipments' . $this->id, $this->getCreateFields());
    }

}
