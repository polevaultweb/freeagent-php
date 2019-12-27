<?php

namespace Polevaultweb\FreeAgent\ApiOperations;

trait Retrieve {

	/**
	 * @param array|string $id The ID of the API resource to retrieve,
	 *     or an options array containing an `id` key.
	 * @param array|string|null $opts
	 *
	 * @throws \Polevaultweb\FreeAgent\Exception\ApiErrorException if the request fails
	 *
	 * @return static
	 */
	public static function retrieve($id, $opts = null)
	{
		$opts = \Polevaultweb\FreeAgent\Util\RequestOptions::parse($opts);
		$instance = new static($id, $opts);
		$instance->refresh();
		return $instance;
	}
}