<?php

namespace Bigcommerce\Api;

/**
 * HTTP connection.
 */
class Connection
{

	/**
	 * @var cURL resource
	 */
	private $curl;

	/**
	 * @var hash of HTTP request headers
	 */
	private $headers = array();

	/**
	 * @var hash of headers from HTTP response
	 */
	private $responseHeaders = array();

	/**
	 * The status line of the response.
	 * @var string
	 */
	private $responseStatusLine;

	/**
	 * @var hash of headers from HTTP response
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
	 * @var mixed
	 */
	private $lastError = false;

	/**
	 * Current cURL error code.
	 */
	private $errorCode;

	/**
	 * Determines whether requests and responses should be treated
	 * as XML. Defaults to false (using JSON).
	 */
	private $useXml = false;

	/**
	 * Initializes the connection object.
	 */
	public function __construct()
	{
		$this->curl = curl_init();
		curl_setopt($this->curl, CURLOPT_HEADERFUNCTION, array($this, 'parseHeader'));
		curl_setopt($this->curl, CURLOPT_WRITEFUNCTION, array($this, 'parseBody'));

		// Bigcommerce only supports RC4-SHA (rsa_rc4_128_sha)
		$this->setCipher('rsa_rc4_128_sha');

		if (!ini_get("open_basedir")) {
			curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
		} else {
			$this->followLocation = true;
		}
	}

	/**
	 * Controls whether requests and responses should be treated
	 * as XML. Defaults to false (using JSON).
	 */
	public function useXml($option=true) {
		$this->useXml = $option;
	}

	/**
	 * Throw an exception if the request encounters an HTTP error condition.
	 *
	 * <p>An error condition is considered to be:</p>
	 *
	 * <ul>
	 * 	<li>400-499 - Client error</li>
	 *	<li>500-599 - Server error</li>
	 * </ul>
	 *
	 * <p><em>Note that this doesn't use the builtin CURL_FAILONERROR option,
	 * as this fails fast, making the HTTP body and headers inaccessible.</em></p>
	 */
	public function failOnError($option = true)
	{
		$this->failOnError = $option;
	}

	/**
	 * Sets the HTTP basic authentication.
	 */
	public function authenticate($username, $password)
	{
		curl_setopt($this->curl, CURLOPT_USERPWD, "$username:$password");
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
	 */
	public function useProxy($server, $port=false)
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
	public function verifyPeer($option=false)
	{
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, $option);
	}

	/**
	 * Set which cipher to use during SSL requests.
	 * @param string $cipher the name of the cipher
	 */
	public function setCipher($cipher='rsa_rc4_128_sha')
	{
		curl_setopt($this->curl, CURLOPT_SSL_CIPHER_LIST, $cipher);
	}

	/**
	 * Add a custom header to the request.
	 */
	public function addHeader($header, $value)
	{
		$this->headers[$header] = "$header: $value";
	}

	/**
	 * Get the MIME type that should be used for this request.
	 */
	private function getContentType()
	{
		return ($this->useXml) ? 'application/xml' : 'application/json';
	}

	/**
	 * Clear previously cached request data and prepare for
	 * making a fresh request.
	 */
	private function initializeRequest()
	{
		$this->isComplete = false;
		$this->responseBody = '';
		$this->responseHeaders = array();
		$this->lastError = false;
		$this->addHeader('Accept', $this->getContentType());
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

		$body = ($this->useXml) ? $this->getBody() : json_decode($this->getBody());

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
	 * Recursively follow redirect until an OK response is recieved or
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
	 */
	public function get($url, $query=false)
	{
		$this->initializeRequest();

		if (is_array($query)) {
			$url .= '?' . http_build_query($query);
		}

		curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($this->curl, CURLOPT_URL, $url);
		curl_setopt($this->curl, CURLOPT_HTTPGET, true);
		curl_exec($this->curl);

		return $this->handleResponse();
	}

	/**
	 * Make an HTTP POST request to the specified endpoint.
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
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $body);
		curl_exec($this->curl);

		return $this->handleResponse();
	}

	/**
	 * Make an HTTP HEAD request to the specified endpoint.
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
		curl_setopt($this->curl, CURLOPT_PUT, true);
		curl_exec($this->curl);

		return $this->handleResponse();
	}

	/**
	 * Make an HTTP DELETE request to the specified endpoint.
	 */
	public function delete($url)
	{
		$this->initializeRequest();

		curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt($this->curl, CURLOPT_URL, $url);
		curl_exec($this->curl);

		return $this->handleResponse();
	}

	/**
	 * Callback method collects body content from the response.
	 */
	private function parseBody($curl, $body)
	{
		$this->responseBody .= $body;
		return strlen($body);
	}

	/**
	 * Callback methods collects header lines from the response.
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
	 */
	public function getStatus()
	{
		return curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
	}

	/**
	 * Access the message string from the status line of the response.
	 */
	public function getStatusMessage()
	{
		return $this->responseStatusLine;
	}

	/**
	 * Access the content body of the response
	 */
	public function getBody()
	{
		return $this->responseBody;
	}

	/**
	 * Access given header from the response.
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
