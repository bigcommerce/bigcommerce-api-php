<?php


namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Client;
use Bigcommerce\Api\Resource;

class ProductVariant extends Resource
{
    protected $ignoreOnCreate = array(
        'id',
        'sku_id',
        'calculated_price',
        'calculated_weight',
        'map_price'
    );

    protected $ignoreOnUpdate = array(
        'id',
        'product_id',
        'option_values',
        'sku_id',
        'calculated_price',
        'calculated_weight',
        'image_url',
        'map_price'
    );

    public $urls = array(
        "v3" => "/catalog/products"
    );

    public function meta_fields($id = null, $filter = array(), $version = "v3")
    {
        if (is_null($id)) {
            return Client::getProductVariantMetafields($this->product_id, $this->id, $filter, $version);
        } else {
            return Client::getProductVariantMetafield($this->product_id, $this->id, $id, $filter, $version);
        }
    }

    public function create($filter = array(), $version = "v3")
    {
        return Client::createProductVariant($this->product_id, $this->getCreateFields(), $version);
    }

    public function create_image($image_url, $version = "v3")
    {
        return Client::createProductVariantImage($this->product_id, $this->id, $image_url, $version);
    }

    public function update($version = "v3")
    {
        return Client::updateProductVariant($this->product_id, $this->id, $this->getUpdateFields(), $version);
    }

    public function delete($version = "v3")
    {
        return Client::deleteProductVariant($this->product_id, $this->id, $version);
    }
}
