<?php

namespace Polevaultweb\FreeAgent;

/**
 * Class ApiRequestor
 */
class ApiRequestor
{
	/**
	 * @var string|null
	 */
	private $_accessToken;

	/**
	 * @var string
	 */
	private $_apiBase;

	/**
	 * @var HttpClient\ClientInterface
	 */
	private static $_httpClient;

	/**
	 * ApiRequestor constructor.
	 *
	 * @param string|null $accessToken
	 * @param string|null $apiBase
	 */
	public function __construct($accessToken = null, $apiBase = null)
	{
		$this->_accessToken = $accessToken;
		if (!$apiBase) {
			$apiBase = FreeAgent::$apiBase;
		}
		$this->_apiBase = $apiBase;
	}

	/**
	 * @static
	 *
	 * @param ApiResource|bool|array|mixed $d
	 *
	 * @return ApiResource|array|string|mixed
	 */
	private static function _encodeObjects($d)
	{
		if ($d instanceof ApiResource) {
			return Util\Util::utf8($d->id);
		} elseif ($d === true) {
			return 'true';
		} elseif ($d === false) {
			return 'false';
		} elseif (is_array($d)) {
			$res = [];
			foreach ($d as $k => $v) {
				$res[$k] = self::_encodeObjects($v);
			}
			return $res;
		} else {
			return Util\Util::utf8($d);
		}
	}

	/**
	 * @param string     $method
	 * @param string     $url
	 * @param array|null $params
	 * @param array|null $headers
	 *
	 * @return array tuple containing (ApiReponse, API key)
	 *
	 * @throws Exception\ApiErrorException
	 */
	public function request($method, $url, $params = null, $headers = null)
	{
		$params = $params ?: [];
		$headers = $headers ?: [];
		list($rbody, $rcode, $rheaders, $myAccessToken) =
			$this->_requestRaw($method, $url, $params, $headers);
		$json = $this->_interpretResponse($method, $rbody, $rcode, $rheaders);
		$resp = new ApiResponse($rbody, $rcode, $rheaders, $json);
		return [$resp, $myAccessToken];
	}

	/**
	 * @param string $rbody A JSON string.
	 * @param int $rcode
	 * @param array $rheaders
	 * @param array $resp
	 *
	 * @throws Exception\UnexpectedValueException
	 * @throws Exception\ApiErrorException
	 */
	public function handleErrorResponse($rbody, $rcode, $rheaders, $resp)
	{
		if (!is_array($resp) || !isset($resp['error'])) {
			$msg = "Invalid response object from API: $rbody "
			       . "(HTTP response code was $rcode)";
			throw new Exception\UnexpectedValueException($msg);
		}

		$errorData = $resp['error'];

		$error = null;
		if (is_string($errorData)) {
			$error = self::_specificOAuthError($rbody, $rcode, $rheaders, $resp, $errorData);
		}
		if (!$error) {
			$error = self::_specificAPIError($rbody, $rcode, $rheaders, $resp, $errorData);
		}

		throw $error;
	}

	/**
	 * @static
	 *
	 * @param string $rbody
	 * @param int    $rcode
	 * @param array  $rheaders
	 * @param array  $resp
	 * @param array  $errorData
	 *
	 * @return Exception\ApiErrorException
	 */
	private static function _specificAPIError($rbody, $rcode, $rheaders, $resp, $errorData)
	{
		$msg = isset($errorData['message']) ? $errorData['message'] : null;
		$param = isset($errorData['param']) ? $errorData['param'] : null;
		$code = isset($errorData['code']) ? $errorData['code'] : null;
		$type = isset($errorData['type']) ? $errorData['type'] : null;
		$declineCode = isset($errorData['decline_code']) ? $errorData['decline_code'] : null;

		switch ($rcode) {
			case 400:
				// 'rate_limit' code is deprecated, but left here for backwards compatibility
				// for API versions earlier than 2015-09-08
				if ($code == 'rate_limit') {
					return Exception\RateLimitException::factory($msg, $rcode, $rbody, $resp, $rheaders, $code, $param);
				}
				if ($type == 'idempotency_error') {
					return Exception\IdempotencyException::factory($msg, $rcode, $rbody, $resp, $rheaders, $code);
				}

			// no break
			case 404:
				return Exception\InvalidRequestException::factory($msg, $rcode, $rbody, $resp, $rheaders, $code, $param);
			case 401:
				return Exception\AuthenticationException::factory($msg, $rcode, $rbody, $resp, $rheaders, $code);
			case 402:
				return Exception\CardException::factory($msg, $rcode, $rbody, $resp, $rheaders, $code, $declineCode, $param);
			case 403:
				return Exception\PermissionException::factory($msg, $rcode, $rbody, $resp, $rheaders, $code);
			case 429:
				return Exception\RateLimitException::factory($msg, $rcode, $rbody, $resp, $rheaders, $code, $param);
			default:
				return Exception\UnknownApiErrorException::factory($msg, $rcode, $rbody, $resp, $rheaders, $code);
		}
	}

	/**
	 * @static
	 *
	 * @param string|bool $rbody
	 * @param int         $rcode
	 * @param array       $rheaders
	 * @param array       $resp
	 * @param string      $errorCode
	 *
	 * @return Exception\OAuth\OAuthErrorException
	 */
	private static function _specificOAuthError($rbody, $rcode, $rheaders, $resp, $errorCode)
	{
		$description = isset($resp['error_description']) ? $resp['error_description'] : $errorCode;

		switch ($errorCode) {
			case 'invalid_client':
				return Exception\OAuth\InvalidClientException::factory($description, $rcode, $rbody, $resp, $rheaders, $errorCode);
			case 'invalid_grant':
				return Exception\OAuth\InvalidGrantException::factory($description, $rcode, $rbody, $resp, $rheaders, $errorCode);
			case 'invalid_request':
				return Exception\OAuth\InvalidRequestException::factory($description, $rcode, $rbody, $resp, $rheaders, $errorCode);
			case 'invalid_scope':
				return Exception\OAuth\InvalidScopeException::factory($description, $rcode, $rbody, $resp, $rheaders, $errorCode);
			case 'unsupported_grant_type':
				return Exception\OAuth\UnsupportedGrantTypeException::factory($description, $rcode, $rbody, $resp, $rheaders, $errorCode);
			case 'unsupported_response_type':
				return Exception\OAuth\UnsupportedResponseTypeException::factory($description, $rcode, $rbody, $resp, $rheaders, $errorCode);
			default:
				return Exception\OAuth\UnknownOAuthErrorException::factory($description, $rcode, $rbody, $resp, $rheaders, $errorCode);
		}
	}

	/**
	 * @static
	 *
	 * @param null|array $appInfo
	 *
	 * @return null|string
	 */
	private static function _formatAppInfo($appInfo)
	{
		if ($appInfo !== null) {
			$string = $appInfo['name'];
			if ($appInfo['version'] !== null) {
				$string .= '/' . $appInfo['version'];
			}
			if ($appInfo['url'] !== null) {
				$string .= ' (' . $appInfo['url'] . ')';
			}
			return $string;
		} else {
			return null;
		}
	}

	/**
	 * @static
	 *
	 * @param string $accessToken
	 *
	 * @return array
	 */
	private static function _defaultHeaders($accessToken)
	{
		$uaString = 'FreeAgent/v' . FreeAgent::$apiVersion .' PhpBindings/' . FreeAgent::VERSION;

		$appInfo = FreeAgent::getAppInfo();

		if ($appInfo !== null) {
			$uaString .= ' ' . self::_formatAppInfo($appInfo);
			$ua['application'] = $appInfo;
		}

		$defaultHeaders = [
			'User-Agent' => $uaString,
			'Authorization' => 'Bearer ' . $accessToken,
		];
		return $defaultHeaders;
	}

	/**
	 * @param string $method
	 * @param string $url
	 * @param array $params
	 * @param array $headers
	 *
	 * @return array
	 *
	 * @throws Exception\AuthenticationException
	 * @throws Exception\ApiConnectionException
	 */
	private function _requestRaw($method, $url, $params, $headers)
	{
		$myAccessToken = $this->_accessToken;
		if (!$myAccessToken) {
			$myAccessToken = FreeAgent::$accessToken;
		}

		if (!$myAccessToken) {
			$msg = 'No Access Token provided.  (HINT: set your Access token using '
			       . '"FreeAgent::setAccessToken(<API-KEY>)".';
			throw new Exception\AuthenticationException($msg);
		}

		$absUrl = $this->_apiBase.$url;
		$params = self::_encodeObjects($params);
		$defaultHeaders = $this->_defaultHeaders($myAccessToken);
		if (FreeAgent::$apiVersion) {
			$defaultHeaders['FreeAgent-Version'] = FreeAgent::$apiVersion;
		}

		$hasFile = false;
		foreach ($params as $k => $v) {
			if (is_resource($v)) {
				$hasFile = true;
				$params[$k] = self::_processResourceParam($v);
			} elseif ($v instanceof \CURLFile) {
				$hasFile = true;
			}
		}

		if ($hasFile) {
			$defaultHeaders['Content-Type'] = 'multipart/form-data';
		} else {
			$defaultHeaders['Content-Type'] = 'application/x-www-form-urlencoded';
		}

		$combinedHeaders = array_merge($defaultHeaders, $headers);
		$rawHeaders = [];

		foreach ($combinedHeaders as $header => $value) {
			$rawHeaders[] = $header . ': ' . $value;
		}

		list($rbody, $rcode, $rheaders) = $this->httpClient()->request(
			$method,
			$absUrl,
			$rawHeaders,
			$params,
			$hasFile
		);

		return [$rbody, $rcode, $rheaders, $myAccessToken];
	}

	/**
	 * @param resource $resource
	 *
	 * @return \CURLFile|string
	 *
	 * @throws Exception\InvalidArgumentException
	 */
	private function _processResourceParam($resource)
	{
		if (get_resource_type($resource) !== 'stream') {
			throw new Exception\InvalidArgumentException(
				'Attempted to upload a resource that is not a stream'
			);
		}

		$metaData = stream_get_meta_data($resource);
		if ($metaData['wrapper_type'] !== 'plainfile') {
			throw new Exception\InvalidArgumentException(
				'Only plainfile resource streams are supported'
			);
		}

		// We don't have the filename or mimetype, but the API doesn't care
		return new \CURLFile($metaData['uri']);
	}

	/**
	 * @param string $rbody
	 * @param int    $rcode
	 * @param array  $rheaders
	 *
	 * @return array
	 *
	 * @throws Exception\UnexpectedValueException
	 * @throws Exception\ApiErrorException
	 */
	private function _interpretResponse($method, $rbody, $rcode, $rheaders)
	{
		$resp = [];
		if ( $method !== 'delete' ) {
			$resp      = json_decode( $rbody, true );
			$jsonError = json_last_error();
			if ( $resp === null && $jsonError !== JSON_ERROR_NONE ) {
				$msg = "Invalid response body from API: $rbody " . "(HTTP response code was $rcode, json_last_error() was $jsonError)";
				throw new Exception\UnexpectedValueException( $msg, $rcode, $rbody );
			}
		}

		if ($rcode < 200 || $rcode >= 300) {
			$this->handleErrorResponse($rbody, $rcode, $rheaders, $resp);
		}
		return $resp;
	}

	/**
	 * @static
	 *
	 * @param HttpClient\ClientInterface $client
	 */
	public static function setHttpClient($client)
	{
		self::$_httpClient = $client;
	}

	/**
	 * @return HttpClient\ClientInterface
	 */
	private function httpClient()
	{
		if (!self::$_httpClient) {
			self::$_httpClient = HttpClient\CurlClient::instance();
		}
		return self::$_httpClient;
	}
}