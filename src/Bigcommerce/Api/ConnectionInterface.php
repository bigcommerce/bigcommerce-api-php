<?php

namespace Bigcommerce\Api;

interface ConnectionInterface
{
    /**
     * Throw an exception if the request encounters an HTTP error condition.
     *
     * <p>An error condition is considered to be:</p>
     *
     * <ul>
     *    <li>400-499 - Client error</li>
     *    <li>500-599 - Server error</li>
     * </ul>
     *
     * <p><em>Note that this doesn't use the builtin CURL_FAILONERROR option,
     * as this fails fast, making the HTTP body and headers inaccessible.</em></p>
     *
     * @param bool $option the new state of this feature
     */
    public function failOnError($option);

    /**
     * Controls whether requests and responses should be treated
     * as XML. Defaults to false (using JSON).
     *
     * @param bool $option the new state of this feature
     */
    public function useXml($option);

    /**
     * @param boolean
     */
    public function verifyPeer($option);

    /**
     * Add a custom header to the request.
     *
     * @param string $header
     * @param string $value
     */
    public function addHeader($header, $value);

    /**
     * Remove a header from the request.
     *
     * @param string $header
     */
    public function removeHeader($header);

    /**
     * Return an representation of an error returned by the last request, or false
     * if the last request was not an error.
     */
    public function getLastError();

    /**
     * Make an HTTP GET request to the specified endpoint.
     *
     * @param string $url URL to retrieve
     * @param array|bool $query Optional array of query string parameters
     *
     * @return mixed
     */
    public function get($url, $query);

    /**
     * Make an HTTP POST request to the specified endpoint.
     *
     * @param string $url URL to which we send the request
     * @param mixed $body Data payload (JSON string or raw data)
     *
     * @return mixed
     */
    public function post($url, $body);

    /**
     * Make an HTTP HEAD request to the specified endpoint.
     *
     * @param string $url URL to which we send the request
     * @return mixed
     */
    public function head($url);

    /**
     * Make an HTTP PUT request to the specified endpoint.
     *
     * Requires a tmpfile() handle to be opened on the system, as the cURL
     * API requires it to send data.
     *
     * @param string $url URL to which we send the request
     * @param mixed $body Data payload (JSON string or raw data)
     * @return mixed
     */
    public function put($url, $body);

    /**
     * Make an HTTP DELETE request to the specified endpoint.
     *
     * @param string $url URL to which we send the request
     * @return mixed
     */
    public function delete($url);

    /**
     * Access given header from the response.
     *
     * @param string $header Header name to retrieve
     *
     * @return string|void
     */
    public function getHeader($header);

    /**
     * Return the full list of response headers
     */
    public function getHeaders();
}
