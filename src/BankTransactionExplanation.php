<?php

namespace Polevaultweb\FreeAgent;

class BankTransactionExplanation extends ApiResource {

	const OBJECT_NAME = 'bank_transaction_explanation';

	use ApiOperations\All {
		all as traitAll;
	}
	use ApiOperations\Retrieve;
	use ApiOperations\Create;
	use ApiOperations\Update;

	public static function all( $bank_account, $params = array(), $opts = null ) {
		$default = array(
			'bank_account' => $bank_account,
		);

		$params = array_merge( $default, $params );

		return self::traitAll( $params, $opts );
	}
}