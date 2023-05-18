<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

class Customer extends Resource
{
    /** @var string[] */
    protected $ignoreOnCreate = array(
        'id',
    );

    /** @var string[] */
    protected $ignoreOnUpdate = array(
        'id',
    );

    /**
     * @return array|mixed|string
     */
    public function addresses()
    {
        return Client::getCollection($this->fields->addresses->resource, 'Address');
    }

    /**
     * @return mixed
     */
    public function create()
    {
        return Client::createCustomer($this->getCreateFields());
    }

    /**
     * @return mixed
     */
    public function update()
    {
        return Client::updateCustomer($this->id, $this->getUpdateFields());
    }

    /**
     * @return mixed
     */
    public function delete()
    {
        return Client::deleteCustomer($this->id);
    }

    /**
     * @return string
     */
    public function getLoginToken()
    {
        return Client::getCustomerLoginToken($this->id);
    }
}
