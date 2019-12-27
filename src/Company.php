<?php

namespace Polevaultweb\FreeAgent;

class Company extends ApiResource {

	const OBJECT_NAME = 'company';

	use ApiOperations\Retrieve;

	public static function retrieve($opts = null)
	{
		$opts = \Polevaultweb\FreeAgent\Util\RequestOptions::parse($opts);
		$instance = new static($opts);
		$instance->refresh();
		return $instance;
	}

	public static function objectPlural() {
		return static::OBJECT_NAME;
	}
}