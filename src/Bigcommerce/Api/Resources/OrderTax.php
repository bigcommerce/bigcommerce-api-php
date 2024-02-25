<?php

namespace Bigcommerce\Api\Resources;
;

use Bigcommerce\Api\Resource;

class OrderTax extends Resource
{
    protected $ignoreOnCreate = array(
        'id',
        'order_id',
    );

    protected $ignoreOnUpdate = array(
        'id',
        'order_id',
    );
}
