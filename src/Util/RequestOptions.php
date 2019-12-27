<?php

namespace Polevaultweb\FreeAgent\Util;

use Polevaultweb\FreeAgent\Exception;

class RequestOptions
{

	public $headers;
	public $accessToken;
	public $apiBase;

	public function __construct($accessToken = null, $headers = [], $base = null)
	{
		$this->accessToken = $accessToken;
		$this->headers = $headers;
		$this->apiBase = $base;
	}

	public function __debugInfo()
	{
		return [
			'accessToken' => $this->accessToken,
			'headers' => $this->headers,
			'apiBase' => $this->apiBase,
		];
	}

	/**
	 * Unpacks an options array and merges it into the existing RequestOptions
	 * object.
	 * @param array|string|null $options a key => value array
	 *
	 * @return RequestOptions
	 */
	public function merge($options)
	{
		$other_options = self::parse($options);
		if ($other_options->accessToken === null) {
			$other_options->accessToken = $this->accessToken;
		}
		if ($other_options->apiBase === null) {
			$other_options->apiBase = $this->apiBase;
		}
		$other_options->headers = array_merge($this->headers, $other_options->headers);
		return $other_options;
	}

	/**
	 * Unpacks an options array into an RequestOptions object
	 * @param array|string|null $options a key => value array
	 *
	 * @return RequestOptions
	 */
	public static function parse($options)
	{
		if ($options instanceof self) {
			return $options;
		}

		if (is_null($options)) {
			return new RequestOptions(null, [], null);
		}

		if (is_string($options)) {
			return new RequestOptions($options, [], null);
		}

		if (is_array($options)) {
			$headers = [];
			$accessToken = null;
			$base = null;
			if (array_key_exists('acccess_token', $options)) {
				$accessToken = $options['acccess_token'];
			}
			if (array_key_exists('api_base', $options)) {
				$base = $options['api_base'];
			}
			return new RequestOptions($accessToken, $headers, $base);
		}

		$message = 'The second argument to FreeAgent API method calls is an '
		           . 'optional per-request accessToken, which must be a string, or '
		           . 'per-request options, which must be an array. (HINT: you can set '
		           . 'a global accessToken by "FreeAgent::setAccessToken(<accessToken>)")';
		throw new Exception\InvalidArgumentException($message);
	}
}
