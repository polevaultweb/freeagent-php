<?php

namespace Polevaultweb\FreeAgent\ApiOperations;

trait Request {

	/**
	 * @param array|null|mixed $params The list of parameters to validate
	 *
	 * @throws \Polevaultweb\FreeAgent\Exception\InvalidArgumentException if $params exists and is not an array
	 */
	protected static function _validateParams($params = null)
	{
		if ($params && !is_array($params)) {
			$message = "You must pass an array as the first argument to FreeAgent API "
			           . "method calls.  (HINT: an example call to create a charge "
			           . "would be: \"Polevaultweb\\FreeAgent\\Invoice::create(['total_value' => 100, "
			           . "'currency' => 'usd'])\")";
			throw new \Polevaultweb\FreeAgent\Exception\InvalidArgumentException($message);
		}
	}

	/**
	 * @param string $method HTTP method ('get', 'post', etc.)
	 * @param string $url URL for the request
	 * @param array $params list of parameters for the request
	 * @param array|string|null $options
	 *
	 * @throws \Polevaultweb\FreeAgent\Exception\ApiErrorException if the request fails
	 *
	 * @return array tuple containing (the JSON response, $options)
	 */
	protected function _request($method, $url, $params = [], $options = null)
	{
		$opts = $this->_opts->merge($options);
		list($resp, $options) = static::_staticRequest($method, $url, $params, $opts);
		$this->setLastResponse($resp);
		return [$resp->json, $options];
	}

	/**
	 * @param string $method HTTP method ('get', 'post', etc.)
	 * @param string $url URL for the request
	 * @param array $params list of parameters for the request
	 * @param array|string|null $options
	 *
	 * @throws \Polevaultweb\FreeAgent\Exception\ApiErrorException if the request fails
	 *
	 * @return array tuple containing (the JSON response, $options)
	 */
	protected static function _staticRequest($method, $url, $params, $options)
	{
		$opts = \Polevaultweb\FreeAgent\Util\RequestOptions::parse($options);
		$baseUrl = isset($opts->apiBase) ? $opts->apiBase : static::baseUrl();
		$requestor = new \Polevaultweb\FreeAgent\ApiRequestor($opts->accessToken, $baseUrl);
		list($response, $opts->accessToken) = $requestor->request($method, $url, $params, $opts->headers);
		return [$response, $opts];
	}
}