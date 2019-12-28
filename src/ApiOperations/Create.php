<?php

namespace Polevaultweb\FreeAgent\ApiOperations;

trait Create {

	/**
	 * @param array|null $params
	 * @param array|string|null $options
	 *
	 * @throws \Polevaultweb\FreeAGent\Exception\ApiErrorException if the request fails
	 *
	 * @return static The created resource.
	 */
	public static function create($params = null, $options = null)
	{
		self::_validateParams( $params );
		$url = static::classUrl();

		$params = array( self::OBJECT_NAME => $params );

		list($response, $opts) = static::_staticRequest('post', $url, $params, $options);
		$obj = \Polevaultweb\FreeAgent\Util\Util::convertToFreeAgentObject($response->json, $opts);
		$obj->setLastResponse($response);
		return $obj;
	}
}