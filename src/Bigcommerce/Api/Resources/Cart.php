<?php


namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;
use Exception;

/**
 * Represents a single Cart.
 */
class Cart extends Resource
{
    protected $ignoreOnCreate = array(
        'id'
    );

    protected $ignoreOnUpdate = array(
        'id'
    );

    public $urls = array(
        "v3" => "/carts"
    );

    public function create($version = "v3")
    {
        return Client::createCart($this->getCreateFields(), $version);
    }

    /** Note only Customer Id can be updated using Cart Update *
     * @param $customer_id
     * @param string $version
     * @return mixed
     * @throws Exception
     */
    public function update($customer_id, $version = "v3")
    {
        return Client::updateCartCustomerId($this->id, $customer_id, $version);
    }

    /** Add Line Items to Cart *
     * @param $items
     * @param array $filter
     * @param string $version
     * @return mixed
     * @throws Exception
     */
    public function addItems($items, $filter = array(), $version = "v3")
    {
        return Client::createCartLineItems($this->id, $items, $filter, $version);
    }

    public function delete($version = "v3")
    {
        return Client::deleteCart($this->id, $version);
    }

}