<?php

namespace Bigcommerce\Api;

/**
 * Builds a query to filter the results of a collection request.
 */
class Filter
{
    private $parameters;

    /**
     * Factory method, creates an instance of a filter.
     * Used to build URLs to collection endpoints.
     * @param array $filter
     * @return $this
     */
    public static function create($filter = array())
    {
        if ($filter instanceof self) {
            return $filter;
        }

        if (is_int($filter)) {
            $filter = array('page' => $filter);
        }

        return new self($filter);
    }

    public function __construct($filter = array())
    {
        $this->parameters = ($filter) ? $filter : array();
    }

    public function __set($parameter, $value)
    {
        $this->parameters[$parameter] = $value;
    }

    /**
     * Converts the filter into a URL querystring that can be
     * applied as GET parameters.
     *
     * @return string
     */
    public function toQuery()
    {
        $query = http_build_query($this->parameters);

        return ($query) ? '?' . $query : '';
    }
}
