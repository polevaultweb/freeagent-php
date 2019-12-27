<?php

namespace Polevaultweb\FreeAgent;

class BankTransaction extends ApiResource {

	const OBJECT_NAME = 'bank_transaction';

	use ApiOperations\All {
		all as traitAll;
	}
	use ApiOperations\Retrieve;

	public static function allExplained( $bank_account, $params = array(), $opts = null ) {
		return self::all( $bank_account, 'explained', $params, $opts );
	}

	public static function allUnexplained( $bank_account, $params = array(), $opts = null ) {
		return self::all( $bank_account, 'unexplained', $params, $opts );
	}

	public static function all( $bank_account, $view = 'all', $params = array(), $opts = null ) {
		$default = array(
			'bank_account' => $bank_account,
			'view'         => $view,
		);

		$params = array_merge( $default, $params );

		return self::traitAll( $params, $opts );
	}
}