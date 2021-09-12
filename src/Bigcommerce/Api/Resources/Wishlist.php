<?php


namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

class Wishlist extends Resource
{
    protected $ignoreOnCreate = array(
        'id',
    );

    protected $ignoreOnUpdate = array(
        'id',
    );

    public $urls = array(
        "v3" => "/wishlists"
    );

    public function create($version = "v3")
    {
        return Client::createWishlist($this->getCreateFields(), $version);
    }

    public function update($version = "v3")
    {
        return Client::updateWishlist($this->id, $this->getUpdateFields(), $version);
    }

    public function delete($version = "v3")
    {
        return Client::deleteWishlist($this->id, $version);
    }

    public function addItems($object, $version = "v3")
    {
        return Client::createWishlistItems($this->id, $object, $version);
    }

    public function toJson()
    {
        return parent::toJson();
    }
}
