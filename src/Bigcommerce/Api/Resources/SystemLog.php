<?php

namespace Bigcommerce\Api\Resources;

use Bigcommerce\Api\Resource;
use Bigcommerce\Api\Client;

class SystemLog extends Resource
{
    public function systemLogs()
    {
        return Client::getCollection('/private/storelogs', 'SystemLog');
    }
}

