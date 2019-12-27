<?php

namespace Polevaultweb\FreeAgent\Exception;

// TODO: remove this check once we drop support for PHP 5
if ( interface_exists( \Throwable::class, false ) ) {
	/**
	 * The base interface for all Stripe exceptions.
	 *
	 * @package  Polevaultweb\FreeAgent\Exception
	 */
	interface ExceptionInterface extends \Throwable {

	}
} else {
	/**
	 * The base interface for all Stripe exceptions.
	 *
	 * @package  Polevaultweb\FreeAgent\Exception
	 */
	interface ExceptionInterface {

	}
}
