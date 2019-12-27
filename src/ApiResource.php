<?php

namespace Polevaultweb\FreeAgent;

abstract class ApiResource extends FreeAgentObject {
	use ApiOperations\Request;

	/**
	 * @return string The base URL for the given class.
	 */
	public static function baseUrl()
	{
		return FreeAgent::$apiBase . '/v' .FreeAgent::$apiVersion;
	}

	/**
	 * @return string The full API URL for this API resource.
	 */
	public function instanceUrl()
	{
		return static::resourceUrl($this['id']);
	}

	/**
	 * @return string The instance endpoint URL for the given class.
	 */
	public static function resourceUrl($id)
	{
		if ($id === null) {
			$class = get_called_class();
			$message = "Could not determine which URL to request: "
			           . "$class instance has invalid ID: $id";
			throw new Exception\UnexpectedValueException($message);
		}
		$id = Util\Util::utf8($id);
		$base = static::classUrl();
		$extn = urlencode($id);
		return "$base/$extn";
	}

	/**
	 * @return ApiResource The refreshed resource.
	 *
	 * @throws Exception\ApiErrorException
	 */
	public function refresh()
	{
		$requestor = new ApiRequestor($this->_opts->accessToken, static::baseUrl());
		$url = $this->instanceUrl();

		list($response, $this->_opts->accessToken) = $requestor->request(
			'get',
			$url,
			$this->_retrieveOptions,
			$this->_opts->headers
		);
		$this->setLastResponse($response);
		$this->refreshFrom($response->json[static::OBJECT_NAME], $this->_opts);
		return $this;
	}

	/**
	 * @return string The endpoint URL for the given class.
	 */
	public static function classUrl() {
		return "/" . static::objectPlural();
	}

	public static function objectPlural() {
		$base = str_replace( '.', '/', static::OBJECT_NAME );

		return "${base}s";
	}
}