<?php

namespace Polevaultweb\FreeAgent\ApiOperations;

trait All {

	/**
	 * @param array|null $params
	 * @param array|string|null $opts
	 *
	 * @return \Polevaultweb\FreeAgent\Collection of ApiResources
	 */
	public static function all($params = null, $opts = null)
	{
		self::_validateParams($params);
		$url = static::classUrl();

		$defaults = array( 'page' => 1, 'per_page' => 25 );

		$params = array_merge( $defaults, $params );

		if ( $params['per_page'] > 100 ) {
			$params['per_page'] = 100;
		}

		list($response, $opts) = static::_staticRequest('get', $url, $params, $opts);
		$response->json['object'] = 'list';
		$obj = \Polevaultweb\FreeAgent\Util\Util::convertToFreeAgentObject($response->json, $opts);
		if (!($obj instanceof \Polevaultweb\FreeAgent\Collection)) {
			throw new \Polevaultweb\FreeAgent\Exception\UnexpectedValueException(
				'Expected type ' . \Polevaultweb\FreeAgent\Collection::class . ', got "' . get_class($obj) . '" instead.'
			);
		}

		$objectPlural = static::objectPlural();
		$obj->data = $obj->{$objectPlural};
		$obj->objectPlural = $objectPlural;

		$obj->setLastResponse($response);
		$obj->setFilters($params);
		return $obj;
	}
}