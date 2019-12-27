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