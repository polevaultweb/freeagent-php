<?php

namespace Polevaultweb\FreeAgent\ApiOperations;

trait Delete {

	/**
	 * @param array|null $params
	 * @param array|string|null $opts
	 *
	 * @throws \Polevaultweb\FreeAgent\Exception\ApiErrorException if the request fails
	 *
	 * @return static The deleted resource.
	 */
	public function delete($params = [], $opts = null)
	{
		self::_validateParams($params);

		$url = $this->instanceUrl();
		list($response, $opts) = $this->_request('delete', $url, $params, $opts);
		$this->refreshFrom($response, $opts);
		return $this;
	}
}