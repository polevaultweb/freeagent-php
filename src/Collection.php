<?php

namespace Polevaultweb\FreeAgent;

/**
 * Class Collection
 *
 * @property string $object
 * @property string $url
 * @property mixed $data
 * @property mixed $objectPlural
 */
class Collection extends FreeAgentObject implements \IteratorAggregate
{
    const OBJECT_NAME = 'list';

    use ApiOperations\Request;

    /** @var array */
    protected $filters = [];

    /**
     * @return string The base URL for the given class.
     */
    public static function baseUrl()
    {
        return FreeAgent::$apiBase;
    }

    /**
     * Returns the filters.
     *
     * @return array The filters.
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Sets the filters, removing paging options.
     *
     * @param array $filters The filters.
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;
    }

    public function offsetGet($k)
    {
        if (is_string($k)) {
            return parent::offsetGet($k);
        } else {
            $msg = "You tried to access the {$k} index, but Collection " .
                   "types only support string keys. (HINT: List calls " .
                   "return an object with a `data` (which is the data " .
                   "array). You likely want to call ->data[{$k}])";
            throw new Exception\InvalidArgumentException($msg);
        }
    }

    public function all($params = null, $opts = null)
    {
        self::_validateParams($params);
        list($url, $params) = $this->extractPathAndUpdateParams($params);

        list($response, $opts) = $this->_staticRequest('get', $url, $params, $opts);
	    $response->json['object'] = 'list';
	    $response->json['objects'] = $this->object;
        $obj = Util\Util::convertToFreeAgentObject($response->json, $opts);
        if (!($obj instanceof Collection)) {
            throw new \Polevaultweb\FreeAgent\Exception\UnexpectedValueException(
                'Expected type ' . Collection::class . ', got "' . get_class($obj) . '" instead.'
            );
        }
	    $obj->data = $obj->{$this->objectPlural};
	    $obj->objectPlural = $this->objectPlural;
	    $obj->object = $this->object;
        $obj->setFilters($params);
	    $obj->setLastResponse($response);
        return $obj;
    }

    public function create($params = null, $opts = null)
    {
        self::_validateParams($params);
        list($url, $params) = $this->extractPathAndUpdateParams($params);

        list($response, $opts) = $this->_request('post', $url, $params, $opts);
        return Util\Util::convertToFreeAgentObject($response, $opts);
    }

    public function retrieve($id, $params = null, $opts = null)
    {
        self::_validateParams($params);
        list($url, $params) = $this->extractPathAndUpdateParams($params);

        $id = Util\Util::utf8($id);
        $extn = urlencode($id);
        list($response, $opts) = $this->_request(
            'get',
            "$url/$extn",
            $params,
            $opts
        );
        return Util\Util::convertToFreeAgentObject($response, $opts);
    }

    /**
     * @return \ArrayIterator An iterator that can be used to iterate
     *    across objects in the current page.
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * @return \ArrayIterator An iterator that can be used to iterate
     *    backwards across objects in the current page.
     */
    public function getReverseIterator()
    {
        return new \ArrayIterator(array_reverse($this->data));
    }

    /**
     * @return \Generator|FreeAgentObject[] A generator that can be used to
     *    iterate across all objects across all pages. As page boundaries are
     *    encountered, the next page will be fetched automatically for
     *    continued iteration.
     */
    public function autoPagingIterator()
    {
        $page = $this;

        while (true) {
	        foreach ( $page as $item ) {
		        yield $item;
	        }
	        $page = $page->nextPage();


	        if ( $page->isEmpty() ) {
		        break;
	        }
        }
    }

    /**
     * Returns an empty collection. This is returned from {@see nextPage()}
     * when we know that there isn't a next page in order to replicate the
     * behavior of the API when it attempts to return a page beyond the last.
     *
     * @param array|string|null $opts
     * @return Collection
     */
    public static function emptyCollection($opts = null)
    {
        return Collection::constructFrom(['data' => []], $opts);
    }

    /**
     * Returns true if the page object contains no element.
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->data);
    }

    protected function getNextUrl() {
	    if ( ! $this->getLastResponse()->headers->offsetExists('link') ) {
	    	return false;
	    }

	    $links = $this->getLastResponse()->headers->offsetGet('link');

	    $links = explode( ',', $links );

	    foreach( $links as $link ) {
			if ( strpos( $link, "'next'")) {
				return trim( str_replace( array( '<', ">; rel='next'" ), '', $link ) );
			}
	    }

	    return false;
    }

    /**
     * Fetches the next page in the resource list (if there is one).
     *
     * This method will try to respect the limit of the current page. If none
     * was given, the default limit will be fetched again.
     *
     * @param array|null $params
     * @param array|string|null $opts
     * @return Collection
     */
    public function nextPage($params = null, $opts = null)
    {
	    if (!$this->getNextUrl()) {
            return static::emptyCollection($opts);
        }

	    $url    = $this->getNextUrl();
	    $params = explode( '?', $url );
	    $this->url = $params[0];
	    parse_str( $params[1], $params );

        $params = array_merge(
            $this->filters ?: [],
            $params ?: []
        );

        return $this->all($params, $opts);
    }

    /**
     * Fetches the previous page in the resource list (if there is one).
     *
     * This method will try to respect the limit of the current page. If none
     * was given, the default limit will be fetched again.
     *
     * @param array|null $params
     * @param array|string|null $opts
     * @return Collection
     */
    public function previousPage($params = null, $opts = null)
    {
        if (!$this->has_more) {
            return static::emptyCollection($opts);
        }

        $firstId = $this->data[0]->id;

        $params = array_merge(
            $this->filters ?: [],
            ['ending_before' => $firstId],
            $params ?: []
        );

        return $this->all($params, $opts);
    }

    private function extractPathAndUpdateParams($params)
    {
        $url = parse_url($this->url);
        if (!isset($url['path'])) {
            throw new Exception\UnexpectedValueException("Could not parse list url into parts: $url");
        }

        if (isset($url['query'])) {
            // If the URL contains a query param, parse it out into $params so they
            // don't interact weirdly with each other.
            $query = [];
            parse_str($url['query'], $query);
            $params = array_merge($params ?: [], $query);
        }

        return [$url['path'], $params];
    }
}
