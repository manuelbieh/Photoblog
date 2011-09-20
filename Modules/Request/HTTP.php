<?php

class Modules_Request_HTTP {

	public function __construct() {}

	public function getRequestURL() {

		$url = 'http';
		$url .= isset($_SERVER['HTTPS']) ? 's':'';
		$url .= '://';
		if(isset($_SERVER['PHP_AUTH_USER']) && !isset($_SERVER['PHP_AUTH_PW'])) {
			$url .= $_SERVER['PHP_AUTH_USER'] . '@';
		} else if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
			$url .= $_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW'] . '@';
		}
		$url .= $_SERVER['SERVER_NAME'];
		if(isset($_SERVER['SERVER_PORT'])) {
			$url .= ':' . $_SERVER['SERVER_PORT'];
		}
		$url .= $_SERVER['REQUEST_URI'];
		
		return $url;

	}


	public function extractPathFromURL($url) {
		$path = $this->getURLPart($url, 'path');
		return isset($path) ? trim($path, '/ ') : false;
	}


	public function getURLParts($url) {
		return parse_url($url);
	}


	public function getURLPart($url, $part) {

		$parts = array(
			'scheme'=>PHP_URL_SCHEME,
			'host'=>PHP_URL_HOST,
			'port'=>PHP_URL_PORT,
			'user'=>PHP_URL_USER,
			'pass'=>PHP_URL_PASS,
			'path'=>PHP_URL_PATH,
			'query'=>PHP_URL_QUERY,
			'fragment'=>PHP_URL_FRAGMENT
		);

		return parse_url($url, $parts[$part]);

	}


	public function appendSlash($string) {
		return rtrim($string, '/ ') . '/';
	}


	public function getPath($url, $segment=NULL) {

		if($segment === NULL) {
			return $this->extractPathFromURL($url);
		} else {
			$path = $this->extractPathFromURL($url);
			$path = explode("/", $path);
			return $path[(int) $segment];
		}

	}


	public function removeQueryParams($url, $params) {

		$queryParams = $this->getQueryParams($url);
		$url = $this->getURLParts($url);
		foreach((array) $params AS $key) {
			unset($queryParams[$key]);
		}

		$url['query'] = http_build_query($queryParams);
		return $this->buildURL($url);

	}


	public function setQueryParams($url, $params) {

		$queryParams = $this->getQueryParams($url);
		$url = $this->getURLParts($url);
		foreach((array) $params AS $key => $value) {
			$queryParams[$key] = $value;
		}

		$url['query'] = http_build_query($queryParams);
		return $this->buildURL($url);

	}


	public function getQueryParams($query) {

		if($this->getURLPart($query, 'query') != NULL) { //twad passed an URL instead of Querystring?
			$query = $this->getURLPart($query, 'query');
		}

		parse_str($query, $qry);
		return $qry;

	}


	public function buildURL($url) {
		$ret = NULL;

		if($url['scheme']) {
			$ret .= $url['scheme'] . '://';
		}

		if($url['user'] && !$url['pass']) {
			$ret .= $url['user'] . '@';
		} else if($url['user'] && $url['pass']) {
			$ret .= $url['user'] . ':' . $url['pass'] . '@';
		}

		if($url['host']) {
			$ret .= $url['host'];
		}

		if($url['port']) {
			$ret .= ':' . $url['port'];
		}

		$ret .= '/';
		$ret .= ltrim($url['path'], '/');

		if($url['query']) {
			$ret .= '?' . $url['query'];
		}

		if($url['fragment']) {
			$ret .= '#' . $url['fragment'];
		}

		return $ret;

	}


}