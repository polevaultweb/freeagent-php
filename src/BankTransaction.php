<?php

namespace Polevaultweb\FreeAgent;

class BankTransaction extends ApiResource {

	const OBJECT_NAME = 'bank_transaction';

	use ApiOperations\All {
		all as traitAll;
	}
	use ApiOperations\Retrieve;

	public static function all( $bank_account, $view = 'all', $params = array(), $opts = null ) {
		$default = array(
			'bank_account' => $bank_account,
			'view'         => $view,
		);

		$params = array_merge( $default, $params );

		return self::traitAll( $params, $opts );
	}
}