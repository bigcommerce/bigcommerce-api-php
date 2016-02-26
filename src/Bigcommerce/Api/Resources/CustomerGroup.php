<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

class CustomerGroup extends Resource
{
    protected $ignoreOnCreate = array(
        'id',
    );

    protected $ignoreOnUpdate = array(
        'id',
    );
}
