<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

/**
 * Represents a single product.
 */
class Product extends Resource
{
    protected $ignoreOnCreate = array(
        'date_created',
        'date_modified',
    );

    /**
     * @see https://developer.bigcommerce.com/display/API/Products#Products-ReadOnlyFields
     * @var array
     */
    protected $ignoreOnUpdate = array(
        'id',
        'rating_total',
        'rating_count',
        'date_created',
        'date_modified',
        'date_last_imported',
        'number_sold',
        'brand',
        'images',
        'discount_rules',
        'configurable_fields',
        'custom_fields',
        'videos',
        'skus',
        'rules',
        'option_set',
        'options',
        'tax_class',
    );

    public $urls = array(
        "v2" => "/products",
        "v3" => "/catalog/products"
    );

    protected $ignoreIfZero = array(
        'tax_class_id',
    );

    protected function getProductId()
    {
        return $this->id;
    }

    public function brand($version = null)
    {
        return Client::getBrand($this->brand_id, $version);
    }

    public function images($id = null, $filter = array(), $version = null)
    {
        if (is_null($id)) {
            return Client::getProductImages($this->id, $filter, $version);
        } else {
            return Client::getProductImage($this->id, $id, $version);
        }
    }

    public function skus($filter = array(), $version = "v2")
    {
        return Client::getProductSkus($this->id, $filter, $version);
    }

    public function rules($id, $version = "v2")
    {
        if (is_null($id)) {
            return Client::getProductRules($this->id, $version);
        } else {
            return Client::getProductRule($this->id, $id, $version);
        }
    }

    public function videos($id = null, $filter = array(), $version = null)
    {
        if (is_null($id)) {
            return Client::getProductVideos($this->id, $filter, $version);
        } else {
            return Client::getProductVideo($this->id, $id, $version);
        }
    }

    public function bulk_pricing_rules($id = null, $filter = array(), $version = 'v3')
    {
        if (is_null($id)) {
            return Client::getProductBulkPricingRules($this->id, $filter, $version);
        } else {
            return Client::getProductBulkPricingRule($this->id, $id, $filter, $version);
        }
    }

    public function complex_rules($id = null, $filter = array(), $version = 'v3')
    {
        if (is_null($id)) {
            return Client::getProductComplexRules($this->id, $filter, $version);
        } else {
            return Client::getProductComplexRule($this->id, $id, $filter, $version);
        }
    }

    public function variants($id = null, $filter = array(), $version = 'v3')
    {
        if (is_null($id)) {
            return Client::getProductVariants($this->id, $filter, $version);
        } else {
            return Client::getProductVariant($this->id, $id, $filter, $version);
        }
    }

    public function custom_fields($id = null, $version = null)
    {
        if (is_null($id)) {
            return Client::getProductCustomFields($this->id, $version);
        } else {
            return Client::getProductCustomField($this->id, $id, $version);
        }
    }

    public function configurable_fields()
    {
        return Client::getCollection($this->fields->configurable_fields->resource, 'ProductConfigurableField', "v2");
    }

    public function discount_rules()
    {
        return Client::getCollection($this->fields->discount_rules->resource, 'DiscountRule', "v2");
    }

    public function option_set()
    {
        return Client::getResource($this->fields->option_set->resource, 'OptionSet', "v2");
    }

    public function options($id, $filter = array(), $version = "v3")
    {
        if (is_null($id)) {
            return Client::getProductOptions($this->id, $filter, $version);
        } else {
            return Client::getProductOption($this->id, $id, $filter, $version);
        }
    }

    public function reviews($version = null)
    {
        return Client::getProductReviews('/' . $this->id . '/reviews', $version);
    }

    public function create($version = null)
    {
        return Client::createProduct($this->getCreateFields(), $version);
    }

    public function update($version = null)
    {
        return Client::updateProduct($this->id, $this->getUpdateFields(), $version);
    }

    public function delete($version = null)
    {
        return Client::deleteProduct($this->id, $version);
    }

    public function tax_class()
    {
        return Client::getResource($this->fields->tax_class->resource, 'TaxClass');
    }
}
