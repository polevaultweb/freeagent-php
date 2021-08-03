<?php

namespace Polevaultweb\FreeAgent;

class Invoice extends ApiResource {

	const OBJECT_NAME = 'invoice';

	use ApiOperations\All {
		all as traitAll;
	}
	use ApiOperations\Retrieve;

	public static function allOpen($params = array(), $opts = null ) {
		return self::all('open', $params, $opts );
	}

	public static function allOverdue( $params = array(), $opts = null ) {
		return self::all( 'overdue', $params, $opts );
	}

	public static function allUnpaid( $params = array(), $opts = null ) {
		return self::all( 'open_or_overdue', $params, $opts );
	}

	public static function recentUnpaid( $params = array(), $opts = null ) {
		return self::all( 'recent_open_or_overdue', $params, $opts );
	}

	public static function allPaid( $params = array(), $opts = null ) {
		return self::all( 'paid', $params, $opts );
	}

	public static function allDraft( $params = array(), $opts = null ) {
		return self::all( 'draft', $params, $opts );
	}

	public static function all( $view = 'all', $params = array(), $opts = null ) {
		$default = array(
			'view'         => $view,
		);

		$params = array_merge( $default, $params );

		return self::traitAll( $params, $opts );
	}
}