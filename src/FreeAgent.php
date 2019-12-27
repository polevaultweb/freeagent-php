<?php

namespace Polevaultweb\FreeAgent;

/**
 * Class FreeAgent

 */
class FreeAgent
{

	/**
	 * @var string
	 */
	public static $accessToken;

	/**
	 * The base URL for the FreeAgent API.
	 * @var string
	 */
	public static $apiBase = 'https://api.freeagent.com';

	public static $apiVersion = '2';

	// @var string Path to the CA bundle used to verify SSL certificates
	public static $caBundlePath = null;

	// @var boolean Defaults to true.
	public static $verifySslCerts = true;

	public static $appInfo = null;

	// @var int Maximum number of request retries
	public static $maxNetworkRetries = 0;

	// @var boolean Whether client telemetry is enabled. Defaults to true.
	public static $enableTelemetry = true;

	// @var float Maximum delay between retries, in seconds
	private static $maxNetworkRetryDelay = 2.0;

	// @var float Maximum delay between retries, in seconds, that will be respected from the Stripe API
	private static $maxRetryAfter = 60.0;

	// @var float Initial delay between retries, in seconds
	private static $initialNetworkRetryDelay = 0.5;

	public static $logger = null;

	const VERSION = '1.0.0';

	/**
	 * @return string The Access tokenused for requests.
	 */
	public static function getAccessToken()
	{
		return self::$accessToken;
	}

	/**
	 * Sets the API key to be used for requests.
	 *
	 * @param string $accessToken
	 */
	public static function setAccessToken($accessToken)
	{
		self::$accessToken = $accessToken;
	}

	/**
	 * @return Util\LoggerInterface The logger to which the library will
	 *   produce messages.
	 */
	public static function getLogger()
	{
		if (self::$logger == null) {
			return new Util\DefaultLogger();
		}
		return self::$logger;
	}

	/**
	 * @param Util\LoggerInterface $logger The logger to which the library
	 *   will produce messages.
	 */
	public static function setLogger($logger)
	{
		self::$logger = $logger;
	}


	/**
	 * @return string
	 */
	private static function getDefaultCABundlePath()
	{
		return realpath(dirname(__FILE__) . '/../data/ca-certificates.crt');
	}

	/**
	 * @return string
	 */
	public static function getCABundlePath()
	{
		return self::$caBundlePath ?: self::getDefaultCABundlePath();
	}

	/**
	 * @param string $caBundlePath
	 */
	public static function setCABundlePath($caBundlePath)
	{
		self::$caBundlePath = $caBundlePath;
	}

	/**
	 * @return boolean
	 */
	public static function getVerifySslCerts()
	{
		return self::$verifySslCerts;
	}

	/**
	 * @param boolean $verify
	 */
	public static function setVerifySslCerts($verify)
	{
		self::$verifySslCerts = $verify;
	}

	/**
	 * @return array | null The application's information
	 */
	public static function getAppInfo()
	{
		return self::$appInfo;
	}

	/**
	 * @param string $appName The application's name
	 * @param string $appVersion The application's version
	 * @param string $appUrl The application's URL
	 */
	public static function setAppInfo($appName, $appVersion = null, $appUrl = null)
	{
		self::$appInfo = self::$appInfo ?: [];
		self::$appInfo['name'] = $appName;
		self::$appInfo['url'] = $appUrl;
		self::$appInfo['version'] = $appVersion;
	}

	/**
	 * @return int Maximum number of request retries
	 */
	public static function getMaxNetworkRetries()
	{
		return self::$maxNetworkRetries;
	}

	/**
	 * @param int $maxNetworkRetries Maximum number of request retries
	 */
	public static function setMaxNetworkRetries($maxNetworkRetries)
	{
		self::$maxNetworkRetries = $maxNetworkRetries;
	}

	/**
	 * @return float Maximum delay between retries, in seconds
	 */
	public static function getMaxNetworkRetryDelay()
	{
		return self::$maxNetworkRetryDelay;
	}

	/**
	 * @return float Maximum delay between retries, in seconds, that will be respected from the Stripe API
	 */
	public static function getMaxRetryAfter()
	{
		return self::$maxRetryAfter;
	}

	/**
	 * @return float Initial delay between retries, in seconds
	 */
	public static function getInitialNetworkRetryDelay()
	{
		return self::$initialNetworkRetryDelay;
	}
}