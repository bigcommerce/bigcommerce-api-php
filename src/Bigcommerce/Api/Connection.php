<?php

namespace Bigcommerce\Api;

/**
 * HTTP connection.
 */
class Connection
{
    /**
     * XML media type.
     */
    const MEDIA_TYPE_XML = 'application/xml';
    /**
     * JSON media type.
     */
    const MEDIA_TYPE_JSON = 'application/json';
    /**
     * Default urlencoded media type.
     */
    const MEDIA_TYPE_WWW = 'application/x-www-form-urlencoded';

    /**
     * @var resource cURL resource
     */
    private $curl;

    /**
     * @var array Hash of HTTP request headers.
     */
    private $headers = array();

    /**
     * @var array Hash of headers from HTTP response
     */
    private $responseHeaders = array();

    /**
     * The status line of the response.
     * @var string
     */
    private $responseStatusLine;

    /**
     * @var string response body
     */
    private $responseBody;

    /**
     * @var boolean
     */
    private $failOnError = false;

    /**
     * Manually follow location redirects. Used if CURLOPT_FOLLOWLOCATION
     * is unavailable due to open_basedir restriction.
     * @var boolean
     */
    private $followLocation = false;

    /**
     * Maximum number of redirects to try.
     * @var int
     */
    private $maxRedirects = 20;

    /**
     * Number of redirects followed in a loop.
     * @var int
     */
    private $redirectsFollowed = 0;

    /**
     * Deal with failed requests if failOnError is not set.
     * @var string|false
     */
    private $lastError = false;

    /**
     * Determines whether the response body should be returned as a raw string.
     */
    private $rawResponse = false;

    /**
     * Determines the default content type to use with requests and responses.
     */
    private $contentType;

    /**
     * Initializes the connection object.
     */
    public function __construct()
    {
        if (!defined('STDIN')) {
            define('STDIN', fopen('php://stdin', 'r'));
        }
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_HEADERFUNCTION, array($this, 'parseHeader'));
        curl_setopt($this->curl, CURLOPT_WRITEFUNCTION, array($this, 'parseBody'));

        // Set to a blank string to make cURL include all encodings it can handle (gzip, deflate, identity) in the 'Accept-Encoding' request header and respect the 'Content-Encoding' response header
        curl_setopt($this->curl, CURLOPT_ENCODING, '');

        if (!ini_get("open_basedir")) {
            curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
        } else {
            $this->followLocation = true;
        }

        $this->setTimeout(60);
    }

    /**
     * Controls whether requests and responses should be treated
     * as XML. Defaults to false (using JSON).
     *
     * @param bool $option the new state of this feature
     */
    public function useXml($option = true)
    {
        if ($option) {
            $this->contentType = self::MEDIA_TYPE_XML;
            $this->rawResponse = true;
        } else {
            $this->contentType = self::MEDIA_TYPE_JSON;
            $this->rawResponse = false;
        }
    }

    /**
     * Controls whether requests or responses should be treated
     * as urlencoded form data.
     *
     * @param bool $option the new state of this feature
     */
    public function useUrlEncoded($option = true)
    {
        if ($option) {
            $this->contentType = self::MEDIA_TYPE_WWW;
        }
    }

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
    public function failOnError($option = true)
    {
        $this->failOnError = $option;
    }

    /**
     * Sets the HTTP basic authentication.
     *
     * @param string $username
     * @param string $password
     */
    public function authenticateBasic($username, $password)
    {
        curl_setopt($this->curl, CURLOPT_USERPWD, "$username:$password");
    }

    /**
     * Sets Oauth authentication headers
     *
     * @param string $clientId
     * @param string $authToken
     */
    public function authenticateOauth($clientId, $authToken)
    {
        $this->addHeader('X-Auth-Client', $clientId);
        $this->addHeader('X-Auth-Token', $authToken);
    }

    /**
     * Set a default timeout for the request. The client will error if the
     * request takes longer than this to respond.
     *
     * @param int $timeout number of seconds to wait on a response
     */
    public function setTimeout($timeout)
    {
        curl_setopt($this->curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, $timeout);
    }

    /**
     * Set a proxy server for outgoing requests to tunnel through.
     *
     * @param string $server
     * @param int|bool $port optional port number
     */
    public function useProxy($server, $port = false)
    {
        curl_setopt($this->curl, CURLOPT_PROXY, $server);

        if ($port) {
            curl_setopt($this->curl, CURLOPT_PROXYPORT, $port);
        }
    }

    /**
     * @todo may need to handle CURLOPT_SSL_VERIFYHOST and CURLOPT_CAINFO as well
     * @param boolean
     */
    public function verifyPeer($option = false)
    {
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, $option);
    }

    /**
     * Add a custom header to the request.
     *
     * @param string $header
     * @param string $value
     */
    public function addHeader($header, $value)
    {
        $this->headers[$header] = "$header: $value";
    }

    /**
     * Remove a header from the request.
     *
     * @param string $header
     */
    public function removeHeader($header)
    {
        unset($this->headers[$header]);
    }

    /**
     * Get the MIME type that should be used for this request.
     *
     * Defaults to application/json
     */
    private function getContentType()
    {
        return ($this->contentType) ? $this->contentType : self::MEDIA_TYPE_JSON;
    }

    /**
     * Clear previously cached request data and prepare for
     * making a fresh request.
     */
    private function initializeRequest()
    {
        $this->responseBody = '';
        $this->responseHeaders = array();
        $this->lastError = false;
        $this->addHeader('Accept', $this->getContentType());

        curl_setopt($this->curl, CURLOPT_POST, false);
        curl_setopt($this->curl, CURLOPT_PUT, false);
        curl_setopt($this->curl, CURLOPT_HTTPGET, false);

        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->headers);
    }

    /**
     * Check the response for possible errors and handle the response body returned.
     *
     * If failOnError is true, a client or server error is raised, otherwise returns false
     * on error.
     */
    private function handleResponse()
    {
        if (curl_errno($this->curl)) {
            throw new NetworkError(curl_error($this->curl), curl_errno($this->curl));
        }

        $body = ($this->rawResponse) ? $this->getBody() : json_decode($this->getBody());

        $status = $this->getStatus();

        if ($status >= 400 && $status <= 499) {
            if ($this->failOnError) {
                throw new ClientError($body, $status);
            } else {
                $this->lastError = $body;
                return false;
            }
        } elseif ($status >= 500 && $status <= 599) {
            if ($this->failOnError) {
                throw new ServerError($body, $status);
            } else {
                $this->lastError = $body;
                return false;
            }
        }

        if ($this->followLocation) {
            $this->followRedirectPath();
        }

        return $body;
    }

    /**
     * Return an representation of an error returned by the last request, or false
     * if the last request was not an error.
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Recursively follow redirect until an OK response is received or
     * the maximum redirects limit is reached.
     *
     * Only 301 and 302 redirects are handled. Redirects from POST and PUT requests will
     * be converted into GET requests, as per the HTTP spec.
     */
    private function followRedirectPath()
    {
        $this->redirectsFollowed++;

        if ($this->getStatus() == 301 || $this->getStatus() == 302) {
            if ($this->redirectsFollowed < $this->maxRedirects) {
                $location = $this->getHeader('Location');
                $forwardTo = parse_url($location);

                if (isset($forwardTo['scheme']) && isset($forwardTo['host'])) {
                    $url = $location;
                } else {
                    $forwardFrom = parse_url(curl_getinfo($this->curl, CURLINFO_EFFECTIVE_URL));
                    $url = $forwardFrom['scheme'] . '://' . $forwardFrom['host'] . $location;
                }

                $this->get($url);

            } else {
                $errorString = "Too many redirects when trying to follow location.";
                throw new NetworkError($errorString, CURLE_TOO_MANY_REDIRECTS);
            }
        } else {
            $this->redirectsFollowed = 0;
        }
    }

    /**
     * Make an HTTP GET request to the specified endpoint.
     *
     * @param string $url URL to retrieve
     * @param array|bool $query Optional array of query string parameters
     *
     * @return mixed
     */
    public function get($url, $query = false)
    {
        $this->initializeRequest();

        if (is_array($query)) {
            $url .= '?' . http_build_query($query);
        }

        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_POST, false);
        curl_setopt($this->curl, CURLOPT_PUT, false);
        curl_setopt($this->curl, CURLOPT_HTTPGET, true);
        curl_exec($this->curl);

        return $this->handleResponse();
    }

    /**
     * Make an HTTP POST request to the specified endpoint.
     *
     * @param string $url URL to which we send the request
     * @param mixed $body Data payload (JSON string or raw data)
     *
     * @return mixed
     */
    public function post($url, $body)
    {
        $this->addHeader('Content-Type', $this->getContentType());

        if (!is_string($body)) {
            $body = json_encode($body);
        }

        $this->initializeRequest();

        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_PUT, false);
        curl_setopt($this->curl, CURLOPT_HTTPGET, false);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $body);
        curl_exec($this->curl);

        return $this->handleResponse();
    }

    /**
     * Make an HTTP HEAD request to the specified endpoint.
     *
     * @param string $url URL to which we send the request
     * @return mixed
     */
    public function head($url)
    {
        $this->initializeRequest();

        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'HEAD');
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_NOBODY, true);
        curl_exec($this->curl);

        return $this->handleResponse();
    }

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
    public function put($url, $body)
    {
        $this->addHeader('Content-Type', $this->getContentType());

        if (!is_string($body)) {
            $body = json_encode($body);
        }

        $this->initializeRequest();

        $handle = tmpfile();
        fwrite($handle, $body);
        fseek($handle, 0);
        curl_setopt($this->curl, CURLOPT_INFILE, $handle);
        curl_setopt($this->curl, CURLOPT_INFILESIZE, strlen($body));

        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_HTTPGET, false);
        curl_setopt($this->curl, CURLOPT_POST, false);
        curl_setopt($this->curl, CURLOPT_PUT, true);
        curl_exec($this->curl);

        fclose($handle);
        curl_setopt($this->curl, CURLOPT_INFILE, STDIN);

        return $this->handleResponse();
    }

    /**
     * Make an HTTP DELETE request to the specified endpoint.
     *
     * @param string $url URL to which we send the request
     * @return mixed
     */
    public function delete($url)
    {
        $this->initializeRequest();

        curl_setopt($this->curl, CURLOPT_PUT, false);
        curl_setopt($this->curl, CURLOPT_HTTPGET, false);
        curl_setopt($this->curl, CURLOPT_POST, false);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_exec($this->curl);

        return $this->handleResponse();
    }

    /**
     * Method that appears unused, but is in fact called by curl
     *
     * @param resource $curl
     * @param string $body
     * @return int
     */
    private function parseBody($curl, $body)
    {
        $this->responseBody .= $body;
        return strlen($body);
    }

    /**
     * Method that appears unused, but is in fact called by curl
     *
     * @param resource $curl
     * @param string $headers
     * @return int
     */
    private function parseHeader($curl, $headers)
    {
        if (!$this->responseStatusLine && strpos($headers, 'HTTP/') === 0) {
            $this->responseStatusLine = $headers;
        } else {
            $parts = explode(': ', $headers);
            if (isset($parts[1])) {
                $this->responseHeaders[$parts[0]] = trim($parts[1]);
            }
        }
        return strlen($headers);
    }

    /**
     * Access the status code of the response.
     *
     * @return mixed
     */
    public function getStatus()
    {
        return curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
    }

    /**
     * Access the message string from the status line of the response.
     *
     * @return string
     */
    public function getStatusMessage()
    {
        return $this->responseStatusLine;
    }

    /**
     * Access the content body of the response
     *
     * @return string
     */
    public function getBody()
    {
        return $this->responseBody;
    }

    /**
     * Access given header from the response.
     *
     * @param string $header Header name to retrieve
     *
     * @return string|void
     */
    public function getHeader($header)
    {
        if (array_key_exists($header, $this->responseHeaders)) {
            return $this->responseHeaders[$header];
        }
    }

    /**
     * Return the full list of response headers
     */
    public function getHeaders()
    {
        return $this->responseHeaders;
    }

    /**
     * Close the cURL resource when the instance is garbage collected
     */
    public function __destruct()
    {
        curl_close($this->curl);
    }
}
