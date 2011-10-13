<?php

class Application_Router {

	protected $app;
	protected $params = array();
	protected $request;
	protected $routes;
	protected $ignore;

	public function __construct($routes, $app=NULL) {

		$this->app = $app;
		$this->request = new Modules_Request_HTTP();
		$this->addRoutes($routes);

		return $this;

	}

	public function addRoutes($file) {
		$this->routes[$file] = true;
		return $this;
	}

	public function addParams($params) {

		foreach($params AS $key => $value) {
			$this->params[$key] = $value;
		}

	}

	public function ignoreFolder($partName) {
		$this->ignore[] = $partName;
	}

	public function getParams() {

		return is_array($this->params) ? $this->params : array();

	}

	protected function parseRouteFile($file) {

		$url 		= $this->request->getRequestURL();
		$host 		= $this->request->getURLPart($url, 'host');
		$path 		= $this->request->extractPathFromURL($url);
		$urlParts	= explode('/', $path);
		$pattern	= '/' . rtrim(join('/', $urlParts), ' /');
	#	$query		= $this->request->getURLPart($url, 'query');
	#	$pattern	.= ($query !== NULL) ? '?' . $this->request->getURLPart($url, 'query') : '';

		if(isset($this->ignore) && is_array($this->ignore)) {
			foreach($this->ignore AS $folder) {
				$path = ltrim($path, '/');
				if(strpos($path, $folder) === 0) {
					$path = trim(substr($path, strlen($folder)), '/');
				}
			}
		}

		if(function_exists('apache_getenv')) {
			$env	= apache_getenv('ENV');
		}


		$dynRoutes = Modules_Filesys::read($file);

		$this->routeXML = new Modules_XML();
		$this->routeXML->loadXML($dynRoutes);

		$paths = $this->routeXML->XPath()->query("//domain[(@name='" . $host . "' or @name='*') and ((@env = '" . $env . "') or ('".$env."' = '' and not(@env)))]/path/pattern");

		$shortcuts = array(':num'=>'\d+', ':word'=>'\w+', ':params'=>'(?<params>.*)');

		for($i=0; $i< $paths->length; $i++) {

			$path 	= str_replace(array_keys($shortcuts), $shortcuts, $paths->item($i)->textContent);
			$path 	= str_replace('@', '\@', $path);

			foreach($urlParts AS $cnt => $part) {
				$path = Modules_Functions::patternReplace($path, array($cnt=>$part));
			}

/*
echo "<hr />\n";
echo "CleanPath: @$path$@U\n";
echo "<br />Pattern: $pattern <br />\n";
var_dump(preg_match("@$path$@", $pattern));
*/

			if(preg_match("@" . $path . "$@U", $pattern, $hits)) {

				$data['controller'] = $this->routeXML->XPath()->query("../controller", $paths->item($i));
				$data['controller'] = $data['controller']->item(0)->textContent;

				$data['action'] = $this->routeXML->XPath()->query("../action", $paths->item($i));
				$data['action'] = $data['action']->item(0)->textContent;

				foreach($hits AS $key => $value) {

					$data['controller']	= Modules_Functions::patternReplace($data['controller'], array($key=>ucfirst($hits[$key])));
					$data['action']		= Modules_Functions::patternReplace($data['action'], array($key=>$hits[$key]));
					#$data['params']		= $hits['params'];
					$data[$key] 		= (!$data[$key]) ? $value : $data[$key];
					$data[$key]			= Modules_Functions::patternReplace($data[$key], array($key=>$hits[$key]));
					
				}
/*
echo "Controller: " . $data['controller'] . " <br />\n";
echo "Action: " . $data['action'] . " <br />\n";
*/

				if(class_exists($data['controller']) && method_exists($data['controller'], $data['action']) 
				&& is_callable(array($data['controller'], $data['action']), true)) {

					$options = $this->routeXML->XPath()->query("../options/*", $paths->item($i));
					foreach($options AS $node) {
						$option[$node->localName] = $node->textContent;
					}

					if(isset($option['dontSplitParams']) && isset($data['params'])) {
						$data['params'] = array($data['params']);
					} else if(!isset($option['dontSplitParams']) && isset($data['params'])) {
						$data['params'] = explode('/', $data['params']);
					}

					$paramList['paramList'] = $this->routeXML->XPath()->query("../params/param", $paths->item($i));

					if($paramList['paramList']->length > 0) {

						foreach($paramList['paramList'] AS $param) {

							if($param->getAttribute('name')) {
								$params[] = $hits[$param->getAttribute('name')];
							}

						}

						$data['params'] = isset($data['params']) ? array_merge($data['params'], $params) : $params;

					}

					//$obj = Application_Base::executeObjectCallback($controller, $action, $rawParams);
					Application_Base::setController($data['controller']);
					Application_Base::setAction($data['action']);

					//$constructorParams = $this->getParams();
					$app = array($this->app);
					$params = array_merge($app, array($this->getParams()));
					

					//$obj = Application_Base::newObject($data['controller'], $constructorParams);
					$obj = Application_Base::newObject($data['controller'], $params);
					if(is_array($params)) {

						foreach($params AS $member => $value) {

							$obj->$member = $value;

						}

					}

					Application_Base::executeObjectCallback($obj, $data['action'], $data['params']);
					//break;
					return;

				}

			}

		}

		Application_Error::error404();

	}

	public function execute() {

		foreach($this->routes AS $file => $bool) {
			$this->parseRouteFile($file);
		}

	}

}