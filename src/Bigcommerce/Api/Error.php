<?php

namespace Bigcommerce\Api;

/**
 * Base class for API exceptions. Used if failOnError is true.
 */
class Error extends \Exception
{
    /**
     * @var array
     */
    private $headers;

    /**
     * @param $message
     * @param $code
     * @param string[] $headers
     */
    public function __construct($message, $code, array $headers = [])
    {
        if (is_array($message)) {
            $message = $message[0]->message;
        }

        parent::__construct($message, $code);
        $this->headers = $headers;
    }

    /**
     * @return string[]
     */
    public function getResponseHeaders(): array
    {
        return $this->headers;
    }

}
