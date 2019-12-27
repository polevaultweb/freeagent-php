<?php

namespace Polevaultweb\FreeAgent;

class BankAccount extends ApiResource {

	const OBJECT_NAME = 'bank_account';

	use ApiOperations\All {
		all as traitAll;
	}
	use ApiOperations\Retrieve;

	public static function allStandard( $params = array(), $opts = null ) {
		return self::all( 'standard_bank_accounts', $params, $opts );
	}

	public static function allPayPal( $params = array(), $opts = null ) {
		return self::all( 'paypal_accounts', $params, $opts );
	}

	public static function allCreditCard( $params = array(), $opts = null ) {
		return self::all( 'credit_card_accounts', $params, $opts );
	}

	public static function all( $view = null, $params = array(), $opts = null ) {
		$default = array();
		if( $view ) {
			$default['view'] = $view;
		}

		$params = array_merge( $default, $params );

		return self::traitAll( $params, $opts );
	}
}